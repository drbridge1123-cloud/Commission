<?php
/**
 * Case Approval API
 * Admin-only case approval and dashboard stats
 */
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

if (!isAdmin()) {
    jsonResponse(['error' => 'Admin access required'], 403);
}

// Rate limiting
requireRateLimit('api_approve', 60, 60);

$pdo = getDB();
$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// GET - Get stats for admin dashboard
if ($method === 'GET') {
    $stats = [];

    // Unpaid count (exclude soft deleted)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cases WHERE status = 'unpaid' AND deleted_at IS NULL");
    $stats['pending_count'] = $stmt->fetch()['count'];

    // Total cases and commission (all time)
    $stmt = $pdo->query("
        SELECT COUNT(*) as count, COALESCE(SUM(commission), 0) as total_commission
        FROM cases WHERE deleted_at IS NULL
    ");
    $totalStats = $stmt->fetch();
    $stats['total_cases'] = $totalStats['count'];
    $stats['total_commission'] = $totalStats['total_commission'];
    $stats['avg_commission'] = $totalStats['count'] > 0 ? $totalStats['total_commission'] / $totalStats['count'] : 0;

    // Check received rate
    $stmt = $pdo->query("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN check_received = 1 THEN 1 ELSE 0 END) as received
        FROM cases WHERE deleted_at IS NULL AND status IN ('paid', 'unpaid')
    ");
    $checkStats = $stmt->fetch();
    $stats['check_received_rate'] = $checkStats['total'] > 0 ? round(($checkStats['received'] / $checkStats['total']) * 100, 1) : 0;

    // This month stats
    $currentMonth = date('M. Y');
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count,
               COALESCE(SUM(commission), 0) as total_commission,
               SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as approved_count
        FROM cases
        WHERE month = ? AND deleted_at IS NULL
    ");
    $stmt->execute([$currentMonth]);
    $thisMonthStats = $stmt->fetch();
    $stats['this_month'] = [
        'name' => $currentMonth,
        'cases' => $thisMonthStats['count'],
        'commission' => $thisMonthStats['total_commission'],
        'approved' => $thisMonthStats['approved_count']
    ];
    $stats['month_cases'] = $thisMonthStats['count'];
    $stats['month_commission'] = $thisMonthStats['total_commission'];

    // Last month stats
    $lastMonth = date('M. Y', strtotime('-1 month'));
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count,
               COALESCE(SUM(commission), 0) as total_commission,
               SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as approved_count
        FROM cases
        WHERE month = ? AND deleted_at IS NULL
    ");
    $stmt->execute([$lastMonth]);
    $lastMonthStats = $stmt->fetch();
    $stats['last_month'] = [
        'name' => $lastMonth,
        'cases' => $lastMonthStats['count'],
        'commission' => $lastMonthStats['total_commission'],
        'approved' => $lastMonthStats['approved_count']
    ];

    // Optional year filter for by_counsel and by_month
    $filterYear = sanitizeString($_GET['year'] ?? '', 10);
    $yearCondition = '';
    $yearParam = [];
    if ($filterYear && preg_match('/^\d{4}$/', $filterYear)) {
        $yearCondition = " AND c.month LIKE ?";
        $yearParam = ["%. $filterYear"];
    }

    // By counsel
    $sql = "
        SELECT u.id as user_id, u.display_name, u.username,
               COUNT(c.id) as case_count,
               COALESCE(SUM(c.commission), 0) as total_commission,
               COALESCE(SUM(c.settled), 0) as settled_amount,
               SUM(CASE WHEN c.settled > 0 THEN 1 ELSE 0 END) as settled_count,
               SUM(CASE WHEN c.status = 'unpaid' THEN 1 ELSE 0 END) as pending_count
        FROM users u
        LEFT JOIN cases c ON u.id = c.user_id AND c.deleted_at IS NULL{$yearCondition}
        WHERE u.role = 'employee' AND u.is_active = 1
        GROUP BY u.id
        ORDER BY total_commission DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($yearParam);
    $stats['by_counsel'] = $stmt->fetchAll();

    // By month
    $byMonthYearCond = '';
    $byMonthParams = [];
    if ($filterYear && preg_match('/^\d{4}$/', $filterYear)) {
        $byMonthYearCond = " AND month LIKE ?";
        $byMonthParams = ["%. $filterYear"];
    }
    $sql = "
        SELECT month as month_name,
               COUNT(*) as case_count,
               COALESCE(SUM(commission), 0) as total_commission,
               COALESCE(SUM(settled), 0) as settled_amount,
               SUM(CASE WHEN settled > 0 THEN 1 ELSE 0 END) as settled_count,
               COALESCE(SUM(discounted_legal_fee), 0) as total_disc_fee
        FROM cases
        WHERE deleted_at IS NULL AND month IS NOT NULL{$byMonthYearCond}
        GROUP BY month
        ORDER BY STR_TO_DATE(CONCAT('01 ', REPLACE(month, '.', '')), '%d %b %Y') DESC
        LIMIT 12
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($byMonthParams);
    $stats['by_month'] = $stmt->fetchAll();

    // Cases by status
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count
        FROM cases
        WHERE deleted_at IS NULL
        GROUP BY status
    ");
    $statusCounts = $stmt->fetchAll();
    $stats['by_status'] = [];
    foreach ($statusCounts as $s) {
        $stats['by_status'][$s['status']] = $s['count'];
    }

    // Top 5 highest commission cases
    $stmt = $pdo->query("
        SELECT c.id, c.case_number, c.client_name, c.commission, c.month, c.status,
               u.display_name as counsel_name
        FROM cases c
        JOIN users u ON c.user_id = u.id
        WHERE c.deleted_at IS NULL AND c.commission > 0
        ORDER BY c.commission DESC
        LIMIT 5
    ");
    $stats['top_cases'] = $stmt->fetchAll();

    // Upcoming deadlines (next 14 days)
    $stmt = $pdo->query("
        SELECT c.id, c.case_number, c.client_name, c.demand_deadline, c.assigned_date, c.phase,
               u.display_name as counsel_name,
               DATEDIFF(c.demand_deadline, CURDATE()) as days_until
        FROM cases c
        JOIN users u ON c.user_id = u.id
        WHERE c.deleted_at IS NULL
          AND c.demand_deadline IS NOT NULL
          AND c.demand_deadline >= CURDATE()
          AND c.status IN ('in_progress', 'unpaid')
          AND c.phase = 'demand'
        ORDER BY c.demand_deadline ASC
        LIMIT 10
    ");
    $stats['upcoming_deadlines'] = $stmt->fetchAll();

    // Past due deadlines
    $stmt = $pdo->query("
        SELECT c.id, c.case_number, c.client_name, c.demand_deadline, c.assigned_date,
               u.display_name as counsel_name,
               DATEDIFF(CURDATE(), c.demand_deadline) as days_overdue
        FROM cases c
        JOIN users u ON c.user_id = u.id
        WHERE c.deleted_at IS NULL
          AND c.demand_deadline IS NOT NULL
          AND c.demand_deadline < CURDATE()
          AND c.status IN ('in_progress', 'unpaid')
          AND c.phase = 'demand'
        ORDER BY c.demand_deadline ASC
        LIMIT 5
    ");
    $stats['past_due'] = $stmt->fetchAll();

    // Unreceived checks
    $stmt = $pdo->query("
        SELECT COUNT(*) as count, COALESCE(SUM(commission), 0) as total
        FROM cases
        WHERE status = 'paid' AND check_received = 0 AND deleted_at IS NULL
    ");
    $stats['unreceived'] = $stmt->fetch();

    $stats['csrf_token'] = generateCSRFToken();

    jsonResponse($stats);
}

// Require CSRF for state-changing operations
requireCSRFToken();

// POST - Approve or Reject case
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $caseId = intval($data['case_id'] ?? 0);
    $action = sanitizeString($data['action'] ?? '', 10);

    if ($caseId <= 0) {
        jsonResponse(['error' => 'Invalid case ID'], 400);
    }

    if (!in_array($action, ['approve', 'reject'])) {
        jsonResponse(['error' => 'Invalid action. Use "approve" or "reject"'], 400);
    }

    // Check if case exists and is unpaid
    $stmt = $pdo->prepare("SELECT * FROM cases WHERE id = ? AND status = 'unpaid' AND deleted_at IS NULL");
    $stmt->execute([$caseId]);
    $case = $stmt->fetch();

    if (!$case) {
        jsonResponse(['error' => 'Case not found or already processed'], 404);
    }

    // Check received is required before approving
    if ($action === 'approve' && !$case['check_received']) {
        jsonResponse(['error' => 'Cannot approve: settlement check has not been received yet'], 400);
    }

    $newStatus = ($action === 'approve') ? 'paid' : 'rejected';

    $stmt = $pdo->prepare("
        UPDATE cases
        SET status = ?, reviewed_at = NOW(), reviewed_by = ?
        WHERE id = ?
    ");
    $stmt->execute([$newStatus, $user['id'], $caseId]);

    // Audit log
    logAudit($action . '_case', 'cases', $caseId, ['status' => 'unpaid'], [
        'status' => $newStatus,
        'case_number' => $case['case_number'],
        'client_name' => $case['client_name'],
        'commission' => $case['commission']
    ]);

    jsonResponse(['success' => true, 'status' => $newStatus]);
}

// PUT - Bulk approve/reject
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);

    $caseIds = $data['case_ids'] ?? [];
    $action = sanitizeString($data['action'] ?? '', 10);

    // Validate
    if (!is_array($caseIds) || empty($caseIds)) {
        jsonResponse(['error' => 'No cases selected'], 400);
    }

    if (!in_array($action, ['approve', 'reject'])) {
        jsonResponse(['error' => 'Invalid action'], 400);
    }

    // Limit bulk operations
    if (count($caseIds) > 100) {
        jsonResponse(['error' => 'Cannot process more than 100 cases at once'], 400);
    }

    // Sanitize IDs
    $caseIds = array_map('intval', $caseIds);
    $caseIds = array_filter($caseIds, function($id) { return $id > 0; });

    if (empty($caseIds)) {
        jsonResponse(['error' => 'No valid case IDs provided'], 400);
    }

    $newStatus = ($action === 'approve') ? 'paid' : 'rejected';

    // Use transaction for bulk update
    $pdo->beginTransaction();

    try {
        $placeholders = str_repeat('?,', count($caseIds) - 1) . '?';

        // For approve: only approve cases where check has been received
        $checkCondition = ($action === 'approve') ? ' AND check_received = 1' : '';

        $stmt = $pdo->prepare("
            UPDATE cases
            SET status = ?, reviewed_at = NOW(), reviewed_by = ?
            WHERE id IN ($placeholders) AND status = 'unpaid' AND deleted_at IS NULL{$checkCondition}
        ");

        $params = array_merge([$newStatus, $user['id']], $caseIds);
        $stmt->execute($params);
        $updated = $stmt->rowCount();

        // Count how many were skipped due to check not received
        $skipped = 0;
        if ($action === 'approve') {
            $skipped = count($caseIds) - $updated;
        }

        $pdo->commit();

        // Audit log
        logAudit('bulk_' . $action, 'cases', null, null, [
            'case_ids' => $caseIds,
            'count' => $updated,
            'skipped' => $skipped
        ]);

        $response = ['success' => true, 'updated' => $updated];
        if ($skipped > 0) {
            $response['skipped'] = $skipped;
            $response['warning'] = "{$skipped} case(s) skipped: settlement check not yet received";
        }
        jsonResponse($response);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Bulk approve error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to process cases'], 500);
    }
}
?>
