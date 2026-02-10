<?php
/**
 * Attorney Cases API
 * Demand/Litigation lifecycle management with custom commission rules
 * Accessible by attorneys (is_attorney=1) or Admin with attorney_id parameter
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
requireRateLimit('api_attorney_cases', 60, 60);

$pdo = getDB();
$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// Determine attorney ID
$isAdminUser = isAdmin();
$isAttorneyUser = isAttorney();

if ($isAdminUser) {
    // Admin must specify attorney_id
    $attorneyId = intval($_GET['attorney_id'] ?? $_POST['attorney_id'] ?? 0);
    if ($attorneyId <= 0 && $method === 'GET') {
        // Check POST body for non-GET
        $body = json_decode(file_get_contents('php://input'), true);
        $attorneyId = intval($body['attorney_id'] ?? 0);
    }
    if ($attorneyId <= 0) {
        jsonResponse(['error' => 'attorney_id parameter required'], 400);
    }
} elseif ($isAttorneyUser) {
    // Attorney uses their own ID
    $attorneyId = $user['id'];
} else {
    jsonResponse(['error' => 'Access denied. Attorney access only.'], 403);
}

// GET - Fetch attorney's cases with filters
if ($method === 'GET') {
    // Stats only request
    if (isset($_GET['stats'])) {
        $stats = getAttorneyStats($pdo, $attorneyId);
        jsonResponse(['stats' => $stats, 'csrf_token' => generateCSRFToken()]);
    }

    // Urgent cases only
    if (isset($_GET['urgent'])) {
        $urgentCases = getUrgentCases($pdo, $attorneyId);
        jsonResponse(['cases' => $urgentCases, 'csrf_token' => generateCSRFToken()]);
    }

    // Filter parameters
    $phase = sanitizeString($_GET['phase'] ?? 'all', 20);
    $status = sanitizeString($_GET['status'] ?? 'all', 20);
    $month = sanitizeString($_GET['month'] ?? 'all', 20);
    $year = sanitizeString($_GET['year'] ?? 'all', 10);

    $sql = "SELECT c.*, u.display_name as counsel_name,
                   DATEDIFF(c.demand_deadline, CURDATE()) as days_until_deadline,
                   ta.display_name as top_offer_assignee_name
            FROM cases c
            JOIN users u ON c.user_id = u.id
            LEFT JOIN users ta ON c.top_offer_assignee_id = ta.id
            WHERE c.user_id = ? AND c.deleted_at IS NULL";
    $params = [$attorneyId];

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
                WHEN c.phase = 'demand' AND c.demand_deadline < CURDATE() AND c.top_offer_date IS NULL THEN 0
                WHEN c.phase = 'demand' AND c.demand_deadline <= DATE_ADD(CURDATE(), INTERVAL 14 DAY) AND c.top_offer_date IS NULL THEN 1
                WHEN c.phase = 'demand' AND c.assigned_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 2
                ELSE 3
              END ASC,
              CASE WHEN c.phase = 'demand' AND c.demand_deadline IS NOT NULL THEN c.demand_deadline END ASC,
              c.submitted_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cases = $stmt->fetchAll();

    // Add deadline status to each case
    foreach ($cases as &$case) {
        if ($case['phase'] === 'demand' && $case['demand_deadline']) {
            if (!empty($case['top_offer_date'])) {
                $case['deadline_status'] = [
                    'class' => 'deadline-complete',
                    'message' => 'Completed',
                    'days' => null,
                    'urgent' => false
                ];
            } else {
                $case['deadline_status'] = getDeadlineStatus($case['demand_deadline']);
            }
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
        $attorneyId,
        sanitizeString($data['case_type'] ?? 'Auto', 50),
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
    $stmt->execute([$caseId, $attorneyId]);
    $case = $stmt->fetch();

    if (!$case) {
        jsonResponse(['error' => 'Case not found'], 404);
    }

    $data = json_decode(file_get_contents('php://input'), true) ?? [];

    // Non-admin cannot edit paid cases
    if (!$isAdminUser && $case['status'] === 'paid') {
        jsonResponse(['error' => 'Cannot edit a paid case'], 403);
    }

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
            return updateAttorneyCase($pdo, $case, $data, $oldData);
    }
}

// DELETE - Soft delete case
if ($method === 'DELETE') {
    $caseId = intval($_GET['id'] ?? 0);

    if ($caseId <= 0) {
        jsonResponse(['error' => 'Invalid case ID'], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM cases WHERE id = ? AND user_id = ? AND deleted_at IS NULL");
    $stmt->execute([$caseId, $attorneyId]);
    $case = $stmt->fetch();

    if (!$case) {
        jsonResponse(['error' => 'Case not found'], 404);
    }

    // Non-admin can only delete in_progress or unpaid cases
    if (!$isAdminUser && !in_array($case['status'], ['in_progress', 'unpaid'])) {
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

function getAttorneyStats($pdo, $attorneyId) {
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
    $stmt->execute([$attorneyId]);
    $counts = $stmt->fetch();

    // This month commission
    $currentMonth = date('M. Y');
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(commission), 0) as month_commission
        FROM cases
        WHERE user_id = ? AND month = ? AND deleted_at IS NULL AND status != 'rejected'
    ");
    $stmt->execute([$attorneyId, $currentMonth]);
    $monthCommission = $stmt->fetch()['month_commission'];

    // YTD commission
    $currentYear = date('Y');
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(commission), 0) as ytd_commission
        FROM cases
        WHERE user_id = ? AND month LIKE ? AND deleted_at IS NULL AND status != 'rejected'
    ");
    $stmt->execute([$attorneyId, "%. $currentYear"]);
    $ytdCommission = $stmt->fetch()['ytd_commission'];

    // Overdue count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as overdue_count
        FROM cases
        WHERE user_id = ? AND phase = 'demand' AND demand_deadline < CURDATE() AND deleted_at IS NULL
    ");
    $stmt->execute([$attorneyId]);
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

function getUrgentCases($pdo, $attorneyId) {
    $stmt = $pdo->prepare("
        SELECT c.*, DATEDIFF(c.demand_deadline, CURDATE()) as days_until_deadline
        FROM cases c
        WHERE c.user_id = ?
          AND c.phase = 'demand'
          AND c.demand_deadline IS NOT NULL
          AND c.deleted_at IS NULL
          AND c.demand_deadline <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)
          AND c.top_offer_date IS NULL
        ORDER BY c.demand_deadline ASC
        LIMIT 10
    ");
    $stmt->execute([$attorneyId]);
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

    // Fee rate override (e.g. 33.33% → 40% or vice versa)
    $overrideFeeRate = isset($data['fee_rate_override']) ? sanitizeNumber($data['fee_rate_override'], 0, 100) : null;

    // If fee rate is overridden, note is required
    if ($overrideFeeRate !== null) {
        $note = trim(sanitizeString($data['note'] ?? '', 1000));
        if (empty($note)) {
            jsonResponse(['error' => 'Note is required when fee rate is overridden'], 400);
        }
    }

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
        $manualFeeRate,
        $overrideFeeRate
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

    $auditData = [
        'action' => 'settle_litigation',
        'phase' => 'settled',
        'resolution_type' => $resolutionType,
        'commission' => $chongResult['commission']
    ];
    if ($overrideFeeRate !== null) {
        $auditData['fee_rate_override'] = $overrideFeeRate;
    }
    logAudit('update', 'cases', $case['id'], $oldData, $auditData);

    jsonResponse(['success' => true, 'commission' => $chongResult['commission'], 'result' => $chongResult]);
}

function updateAttorneyCase($pdo, $case, $data, $oldData) {
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

    // Quick stage+date toggle (inline checkbox from demand table)
    if (isset($data['action']) && $data['action'] === 'stage_date_toggle') {
        $field = sanitizeString($data['field'] ?? '', 30);
        $validFields = ['demand_out_date', 'negotiate_date'];

        if (!in_array($field, $validFields)) {
            jsonResponse(['error' => 'Invalid field'], 400);
        }

        $stageMap = [
            'demand_out_date' => 'demand_sent',
            'negotiate_date' => 'negotiate'
        ];

        if (!empty($data['date'])) {
            $date = sanitizeString($data['date'], 20);
            $stage = $stageMap[$field];
            $stmt = $pdo->prepare("UPDATE cases SET `$field` = ?, stage = ? WHERE id = ?");
            $stmt->execute([$date, $stage, $case['id']]);

            logAudit('update', 'cases', $case['id'], $oldData, [
                $field => $date,
                'stage' => $stage
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE cases SET `$field` = NULL WHERE id = ?");
            $stmt->execute([$case['id']]);

            logAudit('update', 'cases', $case['id'], $oldData, [
                $field => null
            ]);
        }

        jsonResponse(['success' => true]);
    }

    // Top Offer submit (from demand table Top button)
    if (isset($data['action']) && $data['action'] === 'submit_top_offer') {
        $amount = sanitizeNumber($data['top_offer_amount'] ?? 0, 0, 999999999.99);
        $assigneeId = intval($data['assignee_id'] ?? 0);
        $offerNote = sanitizeString($data['note'] ?? '', 1000);
        $today = date('Y-m-d');

        if ($amount <= 0) {
            jsonResponse(['error' => 'Top offer amount is required'], 400);
        }

        // Validate assignee exists
        $stmt = $pdo->prepare("SELECT id, display_name FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$assigneeId]);
        $assignee = $stmt->fetch();
        if (!$assignee) {
            jsonResponse(['error' => 'Invalid assignee'], 400);
        }

        // Update case with top offer data
        $stmt = $pdo->prepare("UPDATE cases SET top_offer_amount = ?, top_offer_date = ?, top_offer_assignee_id = ?, top_offer_note = ? WHERE id = ?");
        $stmt->execute([$amount, $today, $assigneeId, $offerNote ?: null, $case['id']]);

        // Send message to assignee
        $subject = "Top Offer Received - {$case['case_number']}";
        $msgBody = "Top offer of $" . number_format($amount, 2) . " received for case {$case['case_number']} ({$case['client_name']}).";
        if ($offerNote) {
            $msgBody .= "\n\nNote: " . $offerNote;
        }

        $stmt = $pdo->prepare("INSERT INTO messages (from_user_id, to_user_id, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$case['user_id'], $assigneeId, $subject, $msgBody]);

        logAudit('update', 'cases', $case['id'], $oldData, [
            'top_offer_amount' => $amount,
            'top_offer_date' => $today,
            'top_offer_assignee_id' => $assigneeId
        ]);

        jsonResponse(['success' => true, 'top_offer_date' => $today]);
    }

    // Standard case update
    $caseNumber = sanitizeString($data['case_number'] ?? $case['case_number'], 50);
    $clientName = sanitizeString($data['client_name'] ?? $case['client_name'], 200);
    $caseType = sanitizeString($data['case_type'] ?? $case['case_type'], 50);
    $resolutionType = sanitizeString($data['resolution_type'] ?? $case['resolution_type'], 100);
    $settled = sanitizeNumber($data['settled'] ?? $case['settled'], 0, 999999999.99);
    $presuitOffer = sanitizeNumber($data['presuit_offer'] ?? $case['presuit_offer'], 0, 999999999.99);
    $legalFee = sanitizeNumber($data['legal_fee'] ?? $case['legal_fee'], 0, 999999999.99);
    $discountedLegalFee = sanitizeNumber($data['discounted_legal_fee'] ?? $case['discounted_legal_fee'], 0, 999999999.99);
    $commission = sanitizeNumber($data['commission'] ?? $case['commission'], 0, 999999999.99);
    $month = sanitizeString($data['month'] ?? $case['month'], 20);
    $status = sanitizeString($data['status'] ?? $case['status'], 20);
    $note = sanitizeString($data['note'] ?? $case['note'], 1000);
    $checkReceived = isset($data['check_received']) ? (!empty($data['check_received']) ? 1 : 0) : $case['check_received'];
    $demandOutDate = array_key_exists('demand_out_date', $data)
        ? (!empty($data['demand_out_date']) ? sanitizeString($data['demand_out_date'], 20) : null)
        : $case['demand_out_date'];
    $negotiateDate = array_key_exists('negotiate_date', $data)
        ? (!empty($data['negotiate_date']) ? sanitizeString($data['negotiate_date'], 20) : null)
        : $case['negotiate_date'];
    $topOfferAmount = array_key_exists('top_offer_amount', $data)
        ? sanitizeNumber($data['top_offer_amount'] ?? 0, 0, 999999999.99)
        : $case['top_offer_amount'];
    $topOfferDate = array_key_exists('top_offer_date', $data)
        ? (!empty($data['top_offer_date']) ? sanitizeString($data['top_offer_date'], 20) : null)
        : $case['top_offer_date'];
    $topOfferAssigneeId = array_key_exists('top_offer_assignee_id', $data)
        ? ($data['top_offer_assignee_id'] ? intval($data['top_offer_assignee_id']) : null)
        : $case['top_offer_assignee_id'];
    $topOfferNote = array_key_exists('top_offer_note', $data)
        ? sanitizeString($data['top_offer_note'] ?? '', 1000)
        : $case['top_offer_note'];

    $stmt = $pdo->prepare("
        UPDATE cases SET
            case_number = ?,
            client_name = ?,
            case_type = ?,
            resolution_type = ?,
            settled = ?,
            presuit_offer = ?,
            legal_fee = ?,
            discounted_legal_fee = ?,
            commission = ?,
            month = ?,
            status = ?,
            note = ?,
            check_received = ?,
            demand_out_date = ?,
            negotiate_date = ?,
            top_offer_amount = ?,
            top_offer_date = ?,
            top_offer_assignee_id = ?,
            top_offer_note = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $caseNumber,
        $clientName,
        $caseType,
        $resolutionType,
        $settled,
        $presuitOffer,
        $legalFee,
        $discountedLegalFee,
        $commission,
        $month,
        $status,
        $note,
        $checkReceived,
        $demandOutDate,
        $negotiateDate,
        $topOfferAmount,
        $topOfferDate,
        $topOfferAssigneeId,
        $topOfferNote ?: null,
        $case['id']
    ]);

    logAudit('update', 'cases', $case['id'], $oldData, [
        'case_number' => $caseNumber,
        'client_name' => $clientName
    ]);

    jsonResponse(['success' => true]);
}
?>
