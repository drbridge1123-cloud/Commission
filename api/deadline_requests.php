<?php
/**
 * Deadline Extension Requests API
 * Handles requests for deadline changes that require admin approval
 */
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

// Rate limiting
requireRateLimit('api_deadline_requests', 30, 60);

$pdo = getDB();
$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// GET - Fetch deadline requests
if ($method === 'GET') {
    // Admin sees all pending requests, employees see their own
    if (isAdmin()) {
        $status = sanitizeString($_GET['status'] ?? 'pending', 20);

        $sql = "SELECT dr.*,
                       c.case_number, c.client_name, c.phase,
                       u.display_name as requester_name,
                       r.display_name as reviewer_name
                FROM deadline_extension_requests dr
                JOIN cases c ON dr.case_id = c.id
                JOIN users u ON dr.user_id = u.id
                LEFT JOIN users r ON dr.reviewed_by = r.id";

        if ($status !== 'all') {
            $sql .= " WHERE dr.status = ?";
            $sql .= " ORDER BY dr.created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$status]);
        } else {
            $sql .= " ORDER BY dr.created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }

        $requests = $stmt->fetchAll();

        // Get pending count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM deadline_extension_requests WHERE status = 'pending'");
        $stmt->execute();
        $pendingCount = $stmt->fetch()['count'];

        jsonResponse([
            'requests' => $requests,
            'pending_count' => $pendingCount,
            'csrf_token' => generateCSRFToken()
        ]);
    } else {
        // Employee sees their own requests
        $stmt = $pdo->prepare("
            SELECT dr.*,
                   c.case_number, c.client_name,
                   r.display_name as reviewer_name
            FROM deadline_extension_requests dr
            JOIN cases c ON dr.case_id = c.id
            LEFT JOIN users r ON dr.reviewed_by = r.id
            WHERE dr.user_id = ?
            ORDER BY dr.created_at DESC
        ");
        $stmt->execute([$user['id']]);
        $requests = $stmt->fetchAll();

        // Get pending count for this user
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM deadline_extension_requests WHERE user_id = ? AND status = 'pending'");
        $stmt->execute([$user['id']]);
        $pendingCount = $stmt->fetch()['count'];

        jsonResponse([
            'requests' => $requests,
            'pending_count' => $pendingCount,
            'csrf_token' => generateCSRFToken()
        ]);
    }
}

// Require CSRF for state-changing operations
requireCSRFToken();

// POST - Create new deadline extension request
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $caseId = intval($data['case_id'] ?? 0);
    $requestedDeadline = sanitizeString($data['requested_deadline'] ?? '', 20);
    $reason = sanitizeString($data['reason'] ?? '', 2000);

    if ($caseId <= 0 || empty($requestedDeadline) || empty($reason)) {
        jsonResponse(['error' => 'Case ID, requested deadline, and reason are required'], 400);
    }

    // Verify case belongs to user
    $stmt = $pdo->prepare("SELECT id, user_id, demand_deadline, client_name, case_number FROM cases WHERE id = ?");
    $stmt->execute([$caseId]);
    $case = $stmt->fetch();

    if (!$case) {
        jsonResponse(['error' => 'Case not found'], 404);
    }

    if ($case['user_id'] != $user['id'] && !isAdmin()) {
        jsonResponse(['error' => 'Not authorized to modify this case'], 403);
    }

    $currentDeadline = $case['demand_deadline'];

    // Check if there's already a pending request for this case
    $stmt = $pdo->prepare("SELECT id FROM deadline_extension_requests WHERE case_id = ? AND status = 'pending'");
    $stmt->execute([$caseId]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'A pending request already exists for this case'], 400);
    }

    // Create the request
    $stmt = $pdo->prepare("
        INSERT INTO deadline_extension_requests
        (case_id, user_id, current_deadline, requested_deadline, reason)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $caseId,
        $user['id'],
        $currentDeadline,
        $requestedDeadline,
        $reason
    ]);

    $newId = $pdo->lastInsertId();

    // Audit log
    logAudit('create', 'deadline_extension_requests', $newId, null, [
        'case_number' => $case['case_number'],
        'client_name' => $case['client_name'],
        'current_deadline' => $currentDeadline,
        'requested_deadline' => $requestedDeadline,
        'reason' => $reason
    ]);

    jsonResponse([
        'success' => true,
        'id' => $newId,
        'message' => 'Deadline extension request submitted. Waiting for admin approval.'
    ]);
}

// PUT - Admin approve/reject request
if ($method === 'PUT') {
    if (!isAdmin()) {
        jsonResponse(['error' => 'Admin access required'], 403);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $requestId = intval($data['id'] ?? 0);
    $action = sanitizeString($data['action'] ?? '', 20); // 'approve' or 'reject'
    $adminNote = sanitizeString($data['admin_note'] ?? '', 1000);

    if ($requestId <= 0 || !in_array($action, ['approve', 'reject'])) {
        jsonResponse(['error' => 'Request ID and valid action (approve/reject) are required'], 400);
    }

    // Get the request
    $stmt = $pdo->prepare("
        SELECT dr.*, c.case_number, c.client_name
        FROM deadline_extension_requests dr
        JOIN cases c ON dr.case_id = c.id
        WHERE dr.id = ?
    ");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();

    if (!$request) {
        jsonResponse(['error' => 'Request not found'], 404);
    }

    if ($request['status'] !== 'pending') {
        jsonResponse(['error' => 'Request has already been ' . $request['status']], 400);
    }

    $newStatus = $action === 'approve' ? 'approved' : 'rejected';

    // Update the request
    $stmt = $pdo->prepare("
        UPDATE deadline_extension_requests
        SET status = ?, admin_note = ?, reviewed_by = ?, reviewed_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$newStatus, $adminNote, $user['id'], $requestId]);

    // If approved, update the case deadline
    if ($action === 'approve') {
        $stmt = $pdo->prepare("
            UPDATE cases
            SET demand_deadline = ?, assigned_date = DATE_SUB(?, INTERVAL 90 DAY)
            WHERE id = ?
        ");
        $stmt->execute([
            $request['requested_deadline'],
            $request['requested_deadline'],
            $request['case_id']
        ]);
    }

    // Audit log
    logAudit($action . '_deadline_request', 'deadline_extension_requests', $requestId,
        ['status' => 'pending'],
        [
            'status' => $newStatus,
            'case_number' => $request['case_number'],
            'admin_note' => $adminNote
        ]
    );

    jsonResponse([
        'success' => true,
        'message' => 'Request has been ' . $newStatus
    ]);
}

// DELETE - Cancel pending request (user can cancel their own pending requests)
if ($method === 'DELETE') {
    $requestId = intval($_GET['id'] ?? 0);

    if ($requestId <= 0) {
        jsonResponse(['error' => 'Request ID is required'], 400);
    }

    // Get the request
    $stmt = $pdo->prepare("SELECT * FROM deadline_extension_requests WHERE id = ?");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();

    if (!$request) {
        jsonResponse(['error' => 'Request not found'], 404);
    }

    // Only the requester or admin can delete, and only if pending
    if ($request['user_id'] != $user['id'] && !isAdmin()) {
        jsonResponse(['error' => 'Not authorized'], 403);
    }

    if ($request['status'] !== 'pending') {
        jsonResponse(['error' => 'Can only cancel pending requests'], 400);
    }

    $stmt = $pdo->prepare("DELETE FROM deadline_extension_requests WHERE id = ?");
    $stmt->execute([$requestId]);

    logAudit('cancel_deadline_request', 'deadline_extension_requests', $requestId, $request, null);

    jsonResponse(['success' => true, 'message' => 'Request cancelled']);
}
?>
