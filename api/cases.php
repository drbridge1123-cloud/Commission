<?php
/**
 * Cases API
 * CRUD operations for commission cases with security features
 */
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Require authentication
if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

// Rate limiting for API calls
requireRateLimit('api_cases', 60, 60); // 60 requests per minute

$pdo = getDB();
$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// GET - Fetch cases
if ($method === 'GET') {
    $isAdmin = isAdmin();

    if ($isAdmin) {
        // Admin sees all cases
        $status = sanitizeString($_GET['status'] ?? 'all', 20);
        $counsel = sanitizeString($_GET['counsel'] ?? 'all', 50);
        $month = sanitizeString($_GET['month'] ?? 'all', 20);

        $sql = "SELECT c.*, u.display_name as counsel_name
                FROM cases c
                JOIN users u ON c.user_id = u.id
                WHERE c.deleted_at IS NULL";
        $params = [];

        if ($status !== 'all') {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }
        if ($counsel !== 'all') {
            $sql .= " AND u.username = ?";
            $params[] = $counsel;
        }
        if ($month !== 'all') {
            $sql .= " AND c.month = ?";
            $params[] = $month;
        }

        $sql .= " ORDER BY c.submitted_at DESC";

    } else {
        // Employee sees only their cases
        $sql = "SELECT c.*, u.display_name as counsel_name
                FROM cases c
                JOIN users u ON c.user_id = u.id
                WHERE c.user_id = ? AND c.deleted_at IS NULL
                ORDER BY c.submitted_at DESC";
        $params = [$user['id']];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cases = $stmt->fetchAll();

    jsonResponse(['cases' => $cases, 'csrf_token' => generateCSRFToken()]);
}

// Require CSRF for state-changing operations
requireCSRFToken();

// POST - Create new case
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        jsonResponse(['error' => 'Invalid data'], 400);
    }

    // Validate required fields
    $caseNumber = sanitizeString($data['case_number'] ?? '', 50);
    $clientName = sanitizeString($data['client_name'] ?? '', 200);
    $month = sanitizeString($data['month'] ?? '', 20);

    if (empty($caseNumber) || empty($clientName) || empty($month)) {
        jsonResponse(['error' => 'Case number, client name, and month are required'], 400);
    }

    // Validate and sanitize numeric fields
    $settled = sanitizeNumber($data['settled'] ?? 0, 0, 999999999.99);
    $presuitOffer = sanitizeNumber($data['presuit_offer'] ?? 0, 0, 999999999.99);
    $feeRate = sanitizeNumber($data['fee_rate'] ?? 33.33, 0, 100);
    $discountedLegalFee = sanitizeNumber($data['discounted_legal_fee'] ?? 0, 0, 999999999.99);

    // Validate presuit_offer <= settled
    if ($presuitOffer > $settled) {
        jsonResponse(['error' => 'Presuit offer cannot exceed settled amount'], 400);
    }

    // Calculate commission using centralized function
    $financials = calculateCaseFinancials(
        $settled,
        $presuitOffer,
        $feeRate,
        $user['commission_rate'],
        $user['uses_presuit_offer']
    );

    $difference = $financials['difference'];
    $legalFee = $financials['legal_fee'];

    if ($discountedLegalFee <= 0) {
        $discountedLegalFee = $financials['discounted_legal_fee'];
    }

    // Chong (user_id = 2) uses special commission rules
    $commissionType = null;
    $phase = sanitizeString($data['phase'] ?? 'demand', 20);
    $resolutionType = sanitizeString($data['resolution_type'] ?? '', 100);

    if ($user['id'] == 2) {
        // Use Chong-specific commission calculation
        $manualCommissionRate = sanitizeNumber($data['manual_commission_rate'] ?? 0, 0, 100);
        $manualFeeRate = sanitizeNumber($data['manual_fee_rate'] ?? 0, 0, 100);

        $chongResult = calculateChongCommission(
            $phase,
            $resolutionType,
            $settled,
            $presuitOffer,
            $discountedLegalFee,
            $manualCommissionRate,
            $manualFeeRate
        );

        $commission = $chongResult['commission'];
        $commissionType = $chongResult['commission_type'];
        $difference = $chongResult['difference'] ?: $difference;
        $legalFee = $chongResult['legal_fee'] ?: $legalFee;
        $feeRate = $chongResult['fee_rate'] ?: $feeRate;
    } else {
        $commission = calculateCommission($discountedLegalFee, $user['commission_rate']);
    }

    // Chong-specific fields
    $assignedDate = null;
    $demandDeadline = null;
    if ($user['id'] == 2 && !empty($data['assigned_date'])) {
        $assignedDate = sanitizeString($data['assigned_date'], 20);
        $demandDeadline = calculateDemandDeadline($assignedDate);
    }

    // Auto-calculate status based on conditions
    $checkReceived = !empty($data['check_received']) ? 1 : 0;
    $autoStatus = calculateAutoStatus($settled, $checkReceived);

    $stmt = $pdo->prepare("
        INSERT INTO cases (
            user_id, case_type, case_number, client_name, resolution_type,
            fee_rate, month, settled, presuit_offer, difference,
            legal_fee, discounted_legal_fee, commission, commission_type,
            phase, assigned_date, demand_deadline,
            note, check_received, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $user['id'],
        sanitizeString($data['case_type'] ?? 'Auto Accident', 50),
        $caseNumber,
        $clientName,
        $resolutionType,
        $feeRate,
        $month,
        $settled,
        $presuitOffer,
        $difference,
        $legalFee,
        $discountedLegalFee,
        $commission,
        $commissionType,
        $phase,
        $assignedDate,
        $demandDeadline,
        sanitizeString($data['note'] ?? '', 1000),
        $checkReceived,
        $autoStatus
    ]);

    $newId = $pdo->lastInsertId();

    // Audit log
    logAudit('create', 'cases', $newId, null, [
        'case_number' => $caseNumber,
        'client_name' => $clientName,
        'commission' => $commission
    ]);

    jsonResponse(['success' => true, 'id' => $newId]);
}

// PUT - Update case (only pending cases by owner, or admin)
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $caseId = intval($data['id'] ?? 0);

    if ($caseId <= 0) {
        jsonResponse(['error' => 'Invalid case ID'], 400);
    }

    // Check ownership and status
    $stmt = $pdo->prepare("SELECT * FROM cases WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$caseId]);
    $case = $stmt->fetch();

    if (!$case) {
        jsonResponse(['error' => 'Case not found'], 404);
    }

    // Only owner can edit in_progress/unpaid cases, admin can edit any
    if (!isAdmin() && ($case['user_id'] != $user['id'] || !in_array($case['status'], ['in_progress', 'unpaid']))) {
        jsonResponse(['error' => 'Cannot edit this case'], 403);
    }

    // Store old data for audit
    $oldData = [
        'case_number' => $case['case_number'],
        'client_name' => $case['client_name'],
        'settled' => $case['settled'],
        'commission' => $case['commission']
    ];

    // Recalculate commission
    $settled = sanitizeNumber($data['settled'] ?? $case['settled'], 0, 999999999.99);
    $presuitOffer = sanitizeNumber($data['presuit_offer'] ?? $case['presuit_offer'], 0, 999999999.99);
    $feeRate = sanitizeNumber($data['fee_rate'] ?? $case['fee_rate'], 0, 100);
    $discountedLegalFee = sanitizeNumber($data['discounted_legal_fee'] ?? 0, 0, 999999999.99);

    // Get user's commission settings
    $stmtUser = $pdo->prepare("SELECT commission_rate, uses_presuit_offer FROM users WHERE id = ?");
    $stmtUser->execute([$case['user_id']]);
    $caseOwner = $stmtUser->fetch();

    // Calculate commission using centralized function
    $financials = calculateCaseFinancials(
        $settled,
        $presuitOffer,
        $feeRate,
        $caseOwner['commission_rate'],
        $caseOwner['uses_presuit_offer']
    );

    $difference = $financials['difference'];
    $legalFee = $financials['legal_fee'];

    if ($discountedLegalFee <= 0) {
        $discountedLegalFee = $financials['discounted_legal_fee'];
    }

    // Chong (user_id = 2) uses special commission rules
    $commissionType = $case['commission_type'] ?? null;
    $phase = sanitizeString($data['phase'] ?? $case['phase'] ?? 'demand', 20);
    $resolutionType = sanitizeString($data['resolution_type'] ?? $case['resolution_type'], 100);

    if ($case['user_id'] == 2) {
        // Use Chong-specific commission calculation
        $manualCommissionRate = sanitizeNumber($data['manual_commission_rate'] ?? 0, 0, 100);
        $manualFeeRate = sanitizeNumber($data['manual_fee_rate'] ?? 0, 0, 100);

        $chongResult = calculateChongCommission(
            $phase,
            $resolutionType,
            $settled,
            $presuitOffer,
            $discountedLegalFee,
            $manualCommissionRate,
            $manualFeeRate
        );

        $commission = $chongResult['commission'];
        $commissionType = $chongResult['commission_type'];
        $difference = $chongResult['difference'] ?: $difference;
        $legalFee = $chongResult['legal_fee'] ?: $legalFee;
        $feeRate = $chongResult['fee_rate'] ?: $feeRate;
    } else {
        $commission = calculateCommission($discountedLegalFee, $caseOwner['commission_rate']);
    }

    // Handle status - auto-calculate or admin override
    $newCheckReceived = isset($data['check_received']) ? (!empty($data['check_received']) ? 1 : 0) : $case['check_received'];

    // Admin can manually set 'rejected' status (for pay structure changes)
    if (isAdmin() && isset($data['status']) && $data['status'] === 'rejected') {
        $newStatus = 'rejected';
    } else {
        // Auto-calculate status based on conditions
        // Pass current status to preserve 'rejected' if already set by admin
        $newStatus = calculateAutoStatus($settled, $newCheckReceived, $case['status']);
    }

    // Handle Chong-specific date fields
    $assignedDate = $case['assigned_date'];
    $demandDeadline = $case['demand_deadline'];
    $demandSettledDate = $case['demand_settled_date'];
    $litigationStartDate = $case['litigation_start_date'];
    $litigationSettledDate = $case['litigation_settled_date'];

    if ($case['user_id'] == 2) {
        if (!empty($data['assigned_date'])) {
            $assignedDate = sanitizeString($data['assigned_date'], 20);
            $demandDeadline = calculateDemandDeadline($assignedDate);
        }
        if (!empty($data['demand_settled_date'])) {
            $demandSettledDate = sanitizeString($data['demand_settled_date'], 20);
        }
        if (!empty($data['litigation_start_date'])) {
            $litigationStartDate = sanitizeString($data['litigation_start_date'], 20);
        }
        if (!empty($data['litigation_settled_date'])) {
            $litigationSettledDate = sanitizeString($data['litigation_settled_date'], 20);
        }
    }

    // Calculate duration days for Chong
    $demandDurationDays = null;
    $litigationDurationDays = null;
    $totalDurationDays = null;

    if ($case['user_id'] == 2) {
        if ($assignedDate && $demandSettledDate) {
            $demandDurationDays = calculateDaysBetween($assignedDate, $demandSettledDate);
        }
        if ($litigationStartDate && $litigationSettledDate) {
            $litigationDurationDays = calculateDaysBetween($litigationStartDate, $litigationSettledDate);
        }
        if ($assignedDate && ($demandSettledDate || $litigationSettledDate)) {
            $endDate = $litigationSettledDate ?: $demandSettledDate;
            $totalDurationDays = calculateDaysBetween($assignedDate, $endDate);
        }
    }

    // Handle stage field for demand phase
    $stage = null;
    if ($phase === 'demand' && isset($data['stage'])) {
        $stage = sanitizeString($data['stage'], 50);
    }

    $stmt = $pdo->prepare("
        UPDATE cases SET
            case_type = ?, case_number = ?, client_name = ?, resolution_type = ?,
            fee_rate = ?, month = ?, settled = ?, presuit_offer = ?, difference = ?,
            legal_fee = ?, discounted_legal_fee = ?, commission = ?, commission_type = ?,
            phase = ?, stage = ?, assigned_date = ?, demand_deadline = ?, demand_settled_date = ?,
            litigation_start_date = ?, litigation_settled_date = ?,
            demand_duration_days = ?, litigation_duration_days = ?, total_duration_days = ?,
            note = ?, check_received = ?,
            status = ?, reviewed_at = CASE WHEN ? != ? THEN NOW() ELSE reviewed_at END,
            reviewed_by = CASE WHEN ? != ? THEN ? ELSE reviewed_by END
        WHERE id = ?
    ");

    $newCaseNumber = sanitizeString($data['case_number'] ?? $case['case_number'], 50);
    $newClientName = sanitizeString($data['client_name'] ?? $case['client_name'], 200);

    $stmt->execute([
        sanitizeString($data['case_type'] ?? $case['case_type'], 50),
        $newCaseNumber,
        $newClientName,
        $resolutionType,
        $feeRate,
        sanitizeString($data['month'] ?? $case['month'], 20),
        $settled,
        $presuitOffer,
        $difference,
        $legalFee,
        $discountedLegalFee,
        $commission,
        $commissionType,
        $phase,
        $stage,
        $assignedDate,
        $demandDeadline,
        $demandSettledDate,
        $litigationStartDate,
        $litigationSettledDate,
        $demandDurationDays,
        $litigationDurationDays,
        $totalDurationDays,
        sanitizeString($data['note'] ?? $case['note'], 1000),
        $newCheckReceived,
        $newStatus,
        $newStatus, $case['status'], // For reviewed_at CASE
        $newStatus, $case['status'], $user['id'], // For reviewed_by CASE
        $caseId
    ]);

    // Audit log
    logAudit('update', 'cases', $caseId, $oldData, [
        'case_number' => $newCaseNumber,
        'client_name' => $newClientName,
        'settled' => $settled,
        'commission' => $commission,
        'status' => $newStatus
    ]);

    jsonResponse(['success' => true]);
}

// DELETE - Soft delete case (only pending by owner, or admin)
if ($method === 'DELETE') {
    $caseId = intval($_GET['id'] ?? 0);

    if ($caseId <= 0) {
        jsonResponse(['error' => 'Invalid case ID'], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM cases WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$caseId]);
    $case = $stmt->fetch();

    if (!$case) {
        jsonResponse(['error' => 'Case not found'], 404);
    }

    if (!isAdmin() && ($case['user_id'] != $user['id'] || !in_array($case['status'], ['in_progress', 'unpaid']))) {
        jsonResponse(['error' => 'Cannot delete this case'], 403);
    }

    // Soft delete instead of hard delete
    $stmt = $pdo->prepare("UPDATE cases SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$caseId]);

    // Audit log
    logAudit('delete', 'cases', $caseId, [
        'case_number' => $case['case_number'],
        'client_name' => $case['client_name']
    ], null);

    jsonResponse(['success' => true]);
}
?>
