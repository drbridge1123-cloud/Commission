<?php
/**
 * Chong Cases API
 * Demand/Litigation lifecycle management with custom commission rules
 * Only accessible by Chong (user_id = 2) or Admin
 */

// Suppress HTML errors - return JSON only
error_reporting(0);
ini_set('display_errors', 0);

require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Custom error handler for JSON responses
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    jsonResponse(['error' => 'Server error: ' . $errstr], 500);
    exit;
});

set_exception_handler(function($e) {
    jsonResponse(['error' => 'Server error: ' . $e->getMessage()], 500);
    exit;
});

// Require authentication
if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

// Rate limiting
requireRateLimit('api_chong_cases', 60, 60);

$pdo = getDB();
$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// Only Chong (id=2) or Admin can access
$isChong = ($user['id'] == 2);
$isAdminUser = isAdmin();

if (!$isChong && !$isAdminUser) {
    jsonResponse(['error' => 'Access denied. This API is for Chong only.'], 403);
}

// Chong user_id constant
define('CHONG_USER_ID', 2);

// GET - Fetch Chong's cases with filters
if ($method === 'GET') {
    // Stats only request
    if (isset($_GET['stats'])) {
        $stats = getChongStats($pdo);
        jsonResponse(['stats' => $stats, 'csrf_token' => generateCSRFToken()]);
    }

    // Urgent cases only
    if (isset($_GET['urgent'])) {
        $urgentCases = getUrgentCases($pdo);
        jsonResponse(['cases' => $urgentCases, 'csrf_token' => generateCSRFToken()]);
    }

    // Filter parameters
    $phase = sanitizeString($_GET['phase'] ?? 'all', 20);
    $status = sanitizeString($_GET['status'] ?? 'all', 20);
    $month = sanitizeString($_GET['month'] ?? 'all', 20);
    $year = sanitizeString($_GET['year'] ?? 'all', 10);

    $sql = "SELECT c.*, u.display_name as counsel_name,
                   DATEDIFF(c.demand_deadline, CURDATE()) as days_until_deadline
            FROM cases c
            JOIN users u ON c.user_id = u.id
            WHERE c.user_id = ? AND c.deleted_at IS NULL";
    $params = [CHONG_USER_ID];

    if ($phase !== 'all') {
        if ($phase === 'demand_settled') {
            $sql .= " AND c.phase = 'settled' AND c.resolution_type = 'Demand Settle'";
        } elseif ($phase === 'litigation_settled') {
            $sql .= " AND c.phase = 'settled' AND c.resolution_type != 'Demand Settle'";
        } else {
            $sql .= " AND c.phase = ?";
            $params[] = $phase;
        }
    }

    if ($status !== 'all') {
        $sql .= " AND c.status = ?";
        $params[] = $status;
    }

    if ($month !== 'all') {
        $sql .= " AND c.month = ?";
        $params[] = $month;
    }

    if ($year !== 'all') {
        $sql .= " AND c.month LIKE ?";
        $params[] = "%. $year";
    }

    $sql .= " ORDER BY
              CASE
                WHEN c.phase = 'demand' AND c.demand_deadline IS NOT NULL THEN c.demand_deadline
                ELSE c.submitted_at
              END ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cases = $stmt->fetchAll();

    // Add deadline status to each case
    foreach ($cases as &$case) {
        if ($case['phase'] === 'demand' && $case['demand_deadline']) {
            $case['deadline_status'] = getDeadlineStatus($case['demand_deadline']);
        } else {
            $case['deadline_status'] = null;
        }
    }

    jsonResponse([
        'cases' => $cases,
        'csrf_token' => generateCSRFToken()
    ]);
}

// Require CSRF for state-changing operations
requireCSRFToken();

// POST - Create new Demand case
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        jsonResponse(['error' => 'Invalid data'], 400);
    }

    // Validate required fields
    $caseNumber = sanitizeString($data['case_number'] ?? '', 50);
    $clientName = sanitizeString($data['client_name'] ?? '', 200);

    if (empty($caseNumber) || empty($clientName)) {
        jsonResponse(['error' => 'Case number and client name are required'], 400);
    }

    // Auto-set assigned date to today if not provided
    $assignedDate = sanitizeString($data['assigned_date'] ?? date('Y-m-d'), 20);
    $demandDeadline = calculateDemandDeadline($assignedDate);

    // Optional settlement fields (for immediate settlement)
    $settled = sanitizeNumber($data['settled'] ?? 0, 0, 999999999.99);
    $discountedLegalFee = sanitizeNumber($data['discounted_legal_fee'] ?? 0, 0, 999999999.99);

    // Calculate commission if settled
    $commission = 0;
    $commissionType = null;
    $phase = 'demand';
    $demandSettledDate = null;
    $status = 'in_progress';  // Default status for new cases

    if ($settled > 0 && $discountedLegalFee > 0) {
        $chongResult = calculateChongCommission(
            'demand',
            'Demand Settle',
            $settled,
            0,  // No pre-suit offer for demand
            $discountedLegalFee
        );
        $commission = $chongResult['commission'];
        $commissionType = $chongResult['commission_type'];
        $phase = 'settled';
        $demandSettledDate = date('Y-m-d');
        $status = 'unpaid';  // Settled cases become unpaid
    }

    // Get stage (default to demand_review for new demand cases)
    $stage = sanitizeString($data['stage'] ?? 'demand_review', 50);

    $stmt = $pdo->prepare("
        INSERT INTO cases (
            user_id, case_type, case_number, client_name, resolution_type,
            fee_rate, month, settled, presuit_offer, difference,
            legal_fee, discounted_legal_fee, commission, commission_type,
            phase, stage, assigned_date, demand_deadline, demand_settled_date,
            note, check_received, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $demandDurationDays = null;
    if ($demandSettledDate) {
        $demandDurationDays = calculateDaysBetween($assignedDate, $demandSettledDate);
    }

    $stmt->execute([
        CHONG_USER_ID,
        sanitizeString($data['case_type'] ?? 'Auto Accident', 50),
        $caseNumber,
        $clientName,
        $settled > 0 ? 'Demand Settle' : '',
        0,  // fee_rate not used for demand
        sanitizeString($data['month'] ?? date('M. Y'), 20),
        $settled,
        0,  // No pre-suit offer for demand
        0,  // No difference for demand
        0,  // legal_fee
        $discountedLegalFee,
        $commission,
        $commissionType,
        $phase,
        $stage,
        $assignedDate,
        $demandDeadline,
        $demandSettledDate,
        sanitizeString($data['note'] ?? '', 1000),
        !empty($data['check_received']) ? 1 : 0,
        $status
    ]);

    $newId = $pdo->lastInsertId();

    // Update duration if settled
    if ($demandDurationDays !== null) {
        $pdo->prepare("UPDATE cases SET demand_duration_days = ?, total_duration_days = ? WHERE id = ?")
            ->execute([$demandDurationDays, $demandDurationDays, $newId]);
    }

    // Audit log
    logAudit('create', 'cases', $newId, null, [
        'case_number' => $caseNumber,
        'client_name' => $clientName,
        'phase' => $phase,
        'commission' => $commission
    ]);

    jsonResponse(['success' => true, 'id' => $newId]);
}

// PUT - Update case or perform actions
if ($method === 'PUT') {
    $caseId = intval($_GET['id'] ?? 0);
    $action = sanitizeString($_GET['action'] ?? '', 50);

    if ($caseId <= 0) {
        jsonResponse(['error' => 'Invalid case ID'], 400);
    }

    // Fetch the case
    $stmt = $pdo->prepare("SELECT * FROM cases WHERE id = ? AND user_id = ? AND deleted_at IS NULL");
    $stmt->execute([$caseId, CHONG_USER_ID]);
    $case = $stmt->fetch();

    if (!$case) {
        jsonResponse(['error' => 'Case not found'], 404);
    }

    $data = json_decode(file_get_contents('php://input'), true) ?? [];

    // Store old data for audit
    $oldData = [
        'phase' => $case['phase'],
        'status' => $case['status'],
        'commission' => $case['commission']
    ];

    switch ($action) {
        case 'settle_demand':
            // Settle case in Demand phase
            return settleDemandCase($pdo, $case, $data, $oldData);

        case 'to_litigation':
            // Move case to Litigation phase
            return moveToLitigation($pdo, $case, $data, $oldData);

        case 'settle_litigation':
            // Settle case in Litigation phase
            return settleLitigationCase($pdo, $case, $data, $oldData);

        default:
            // Standard update
            return updateChongCase($pdo, $case, $data, $oldData);
    }
}

// DELETE - Soft delete case
if ($method === 'DELETE') {
    $caseId = intval($_GET['id'] ?? 0);

    if ($caseId <= 0) {
        jsonResponse(['error' => 'Invalid case ID'], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM cases WHERE id = ? AND user_id = ? AND deleted_at IS NULL");
    $stmt->execute([$caseId, CHONG_USER_ID]);
    $case = $stmt->fetch();

    if (!$case) {
        jsonResponse(['error' => 'Case not found'], 404);
    }

    // Only unpaid cases can be deleted by employee
    if (!$isAdminUser && $case['status'] !== 'unpaid') {
        jsonResponse(['error' => 'Cannot delete this case'], 403);
    }

    $stmt = $pdo->prepare("UPDATE cases SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$caseId]);

    logAudit('delete', 'cases', $caseId, [
        'case_number' => $case['case_number'],
        'phase' => $case['phase']
    ], null);

    jsonResponse(['success' => true]);
}

// ============================================
// Helper Functions
// ============================================

function getChongStats($pdo) {
    // Active cases count by phase
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_active,
            SUM(CASE WHEN phase = 'demand' THEN 1 ELSE 0 END) as demand_count,
            SUM(CASE WHEN phase = 'litigation' THEN 1 ELSE 0 END) as litigation_count,
            SUM(CASE WHEN phase = 'demand' AND demand_deadline < DATE_ADD(CURDATE(), INTERVAL 14 DAY) THEN 1 ELSE 0 END) as urgent_count
        FROM cases
        WHERE user_id = ? AND deleted_at IS NULL AND phase IN ('demand', 'litigation')
    ");
    $stmt->execute([CHONG_USER_ID]);
    $counts = $stmt->fetch();

    // This month commission
    $currentMonth = date('M. Y');
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(commission), 0) as month_commission
        FROM cases
        WHERE user_id = ? AND month = ? AND deleted_at IS NULL AND status != 'rejected'
    ");
    $stmt->execute([CHONG_USER_ID, $currentMonth]);
    $monthCommission = $stmt->fetch()['month_commission'];

    // YTD commission
    $currentYear = date('Y');
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(commission), 0) as ytd_commission
        FROM cases
        WHERE user_id = ? AND month LIKE ? AND deleted_at IS NULL AND status != 'rejected'
    ");
    $stmt->execute([CHONG_USER_ID, "%. $currentYear"]);
    $ytdCommission = $stmt->fetch()['ytd_commission'];

    // Overdue count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as overdue_count
        FROM cases
        WHERE user_id = ? AND phase = 'demand' AND demand_deadline < CURDATE() AND deleted_at IS NULL
    ");
    $stmt->execute([CHONG_USER_ID]);
    $overdueCount = $stmt->fetch()['overdue_count'];

    return [
        'total_active' => (int)$counts['total_active'],
        'demand_count' => (int)$counts['demand_count'],
        'litigation_count' => (int)$counts['litigation_count'],
        'urgent_count' => (int)$counts['urgent_count'],
        'overdue_count' => (int)$overdueCount,
        'month_commission' => (float)$monthCommission,
        'ytd_commission' => (float)$ytdCommission
    ];
}

function getUrgentCases($pdo) {
    $stmt = $pdo->prepare("
        SELECT c.*, DATEDIFF(c.demand_deadline, CURDATE()) as days_until_deadline
        FROM cases c
        WHERE c.user_id = ?
          AND c.phase = 'demand'
          AND c.demand_deadline IS NOT NULL
          AND c.deleted_at IS NULL
          AND c.demand_deadline <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)
        ORDER BY c.demand_deadline ASC
        LIMIT 10
    ");
    $stmt->execute([CHONG_USER_ID]);
    $cases = $stmt->fetchAll();

    foreach ($cases as &$case) {
        $case['deadline_status'] = getDeadlineStatus($case['demand_deadline']);
    }

    return $cases;
}

function settleDemandCase($pdo, $case, $data, $oldData) {
    if ($case['phase'] !== 'demand') {
        jsonResponse(['error' => 'Case is not in Demand phase'], 400);
    }

    $settled = sanitizeNumber($data['settled'] ?? 0, 0, 999999999.99);
    $discountedLegalFee = sanitizeNumber($data['discounted_legal_fee'] ?? 0, 0, 999999999.99);

    if ($settled <= 0 || $discountedLegalFee <= 0) {
        jsonResponse(['error' => 'Settled amount and Discounted Legal Fee are required'], 400);
    }

    $chongResult = calculateChongCommission(
        'demand',
        'Demand Settle',
        $settled,
        0,
        $discountedLegalFee
    );

    $demandSettledDate = date('Y-m-d');
    $demandDurationDays = calculateDaysBetween($case['assigned_date'], $demandSettledDate);

    $stmt = $pdo->prepare("
        UPDATE cases SET
            phase = 'settled',
            status = 'unpaid',
            resolution_type = 'Demand Settle',
            settled = ?,
            legal_fee = ?,
            discounted_legal_fee = ?,
            commission = ?,
            commission_type = ?,
            demand_settled_date = ?,
            demand_duration_days = ?,
            total_duration_days = ?,
            month = ?,
            check_received = ?,
            note = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $settled,
        $chongResult['legal_fee'],
        $discountedLegalFee,
        $chongResult['commission'],
        $chongResult['commission_type'],
        $demandSettledDate,
        $demandDurationDays,
        $demandDurationDays,
        sanitizeString($data['month'] ?? date('M. Y'), 20),
        !empty($data['check_received']) ? 1 : 0,
        sanitizeString($data['note'] ?? $case['note'], 1000),
        $case['id']
    ]);

    logAudit('update', 'cases', $case['id'], $oldData, [
        'action' => 'settle_demand',
        'phase' => 'settled',
        'commission' => $chongResult['commission']
    ]);

    jsonResponse(['success' => true, 'commission' => $chongResult['commission']]);
}

function moveToLitigation($pdo, $case, $data, $oldData) {
    try {
        if ($case['phase'] !== 'demand') {
            jsonResponse(['error' => 'Case is not in Demand phase'], 400);
        }

        $litigationStartDate = sanitizeString($data['litigation_start_date'] ?? date('Y-m-d'), 20);
        $presuitOffer = sanitizeNumber($data['presuit_offer'] ?? 0, 0, 999999999.99);

        $stmt = $pdo->prepare("
            UPDATE cases SET
                phase = 'litigation',
                litigation_start_date = ?,
                presuit_offer = ?,
                note = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $litigationStartDate,
            $presuitOffer,
            sanitizeString($data['note'] ?? $case['note'], 1000),
            $case['id']
        ]);

        logAudit('update', 'cases', $case['id'], $oldData, [
            'action' => 'to_litigation',
            'phase' => 'litigation',
            'litigation_start_date' => $litigationStartDate
        ]);

        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

function settleLitigationCase($pdo, $case, $data, $oldData) {
    if ($case['phase'] !== 'litigation') {
        jsonResponse(['error' => 'Case is not in Litigation phase'], 400);
    }

    $resolutionType = sanitizeString($data['resolution_type'] ?? '', 100);
    if (empty($resolutionType)) {
        jsonResponse(['error' => 'Resolution type is required'], 400);
    }

    $settled = sanitizeNumber($data['settled'] ?? 0, 0, 999999999.99);
    $presuitOffer = sanitizeNumber($data['presuit_offer'] ?? $case['presuit_offer'], 0, 999999999.99);
    $discountedLegalFee = sanitizeNumber($data['discounted_legal_fee'] ?? 0, 0, 999999999.99);
    $manualCommissionRate = sanitizeNumber($data['manual_commission_rate'] ?? 0, 0, 100);
    $manualFeeRate = sanitizeNumber($data['manual_fee_rate'] ?? 0, 0, 100);

    if ($settled <= 0 || $discountedLegalFee <= 0) {
        jsonResponse(['error' => 'Settled amount and Discounted Legal Fee are required'], 400);
    }

    $chongResult = calculateChongCommission(
        'litigation',
        $resolutionType,
        $settled,
        $presuitOffer,
        $discountedLegalFee,
        $manualCommissionRate,
        $manualFeeRate
    );

    $litigationSettledDate = date('Y-m-d');
    $litigationDurationDays = calculateDaysBetween($case['litigation_start_date'], $litigationSettledDate);
    $totalDurationDays = calculateDaysBetween($case['assigned_date'], $litigationSettledDate);

    $stmt = $pdo->prepare("
        UPDATE cases SET
            phase = 'settled',
            status = 'unpaid',
            resolution_type = ?,
            settled = ?,
            presuit_offer = ?,
            difference = ?,
            fee_rate = ?,
            legal_fee = ?,
            discounted_legal_fee = ?,
            commission = ?,
            commission_type = ?,
            litigation_settled_date = ?,
            litigation_duration_days = ?,
            total_duration_days = ?,
            month = ?,
            check_received = ?,
            note = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $resolutionType,
        $settled,
        $presuitOffer,
        $chongResult['difference'],
        $chongResult['fee_rate'],
        $chongResult['legal_fee'],
        $discountedLegalFee,
        $chongResult['commission'],
        $chongResult['commission_type'],
        $litigationSettledDate,
        $litigationDurationDays,
        $totalDurationDays,
        sanitizeString($data['month'] ?? date('M. Y'), 20),
        !empty($data['check_received']) ? 1 : 0,
        sanitizeString($data['note'] ?? $case['note'], 1000),
        $case['id']
    ]);

    logAudit('update', 'cases', $case['id'], $oldData, [
        'action' => 'settle_litigation',
        'phase' => 'settled',
        'resolution_type' => $resolutionType,
        'commission' => $chongResult['commission']
    ]);

    jsonResponse(['success' => true, 'commission' => $chongResult['commission'], 'result' => $chongResult]);
}

function updateChongCase($pdo, $case, $data, $oldData) {
    // Check if this is just a status update
    if (isset($data['status']) && count($data) <= 3) {  // status, id, csrf_token
        $newStatus = sanitizeString($data['status'], 20);
        $validStatuses = ['in_progress', 'unpaid', 'paid', 'rejected'];

        if (!in_array($newStatus, $validStatuses)) {
            jsonResponse(['error' => 'Invalid status'], 400);
        }

        $stmt = $pdo->prepare("UPDATE cases SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $case['id']]);

        logAudit('update', 'cases', $case['id'], $oldData, [
            'status' => $newStatus
        ]);

        jsonResponse(['success' => true]);
    }

    // Standard case update
    $caseNumber = sanitizeString($data['case_number'] ?? $case['case_number'], 50);
    $clientName = sanitizeString($data['client_name'] ?? $case['client_name'], 200);
    $caseType = sanitizeString($data['case_type'] ?? $case['case_type'], 50);
    $note = sanitizeString($data['note'] ?? $case['note'], 1000);
    $checkReceived = isset($data['check_received']) ? (!empty($data['check_received']) ? 1 : 0) : $case['check_received'];

    $stmt = $pdo->prepare("
        UPDATE cases SET
            case_number = ?,
            client_name = ?,
            case_type = ?,
            note = ?,
            check_received = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $caseNumber,
        $clientName,
        $caseType,
        $note,
        $checkReceived,
        $case['id']
    ]);

    logAudit('update', 'cases', $case['id'], $oldData, [
        'case_number' => $caseNumber,
        'client_name' => $clientName
    ]);

    jsonResponse(['success' => true]);
}
?>
