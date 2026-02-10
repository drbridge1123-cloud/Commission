<?php
/**
 * Demand Requests API
 * Admin/Manager can request demand cases, Chong (attorney) can accept/deny
 */
error_reporting(0);
ini_set('display_errors', 0);

require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$pdo = getDB();
$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// GET - Fetch requests
if ($method === 'GET') {
    $status = sanitizeString($_GET['status'] ?? 'all', 20);

    if (isAdmin() || isManager()) {
        // Admin/Manager sees requests they sent
        $sql = "
            SELECT dr.*, u.display_name as assigned_to_name
            FROM demand_requests dr
            JOIN users u ON dr.assigned_to = u.id
            WHERE dr.requested_by = ?
        ";
        $params = [$user['id']];

        if ($status !== 'all') {
            $sql .= " AND dr.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY dr.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

    } else if (isAttorney()) {
        // Attorney sees requests assigned to them
        $sql = "
            SELECT dr.*, u.display_name as requester_name
            FROM demand_requests dr
            JOIN users u ON dr.requested_by = u.id
            WHERE dr.assigned_to = ?
        ";
        $params = [$user['id']];

        if ($status !== 'all') {
            $sql .= " AND dr.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY dr.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

    } else {
        jsonResponse(['error' => 'Access denied'], 403);
    }

    $requests = $stmt->fetchAll();
    jsonResponse(['requests' => $requests, 'csrf_token' => generateCSRFToken()]);
}

// Require CSRF for state-changing operations
requireCSRFToken();

// POST - Create new request (Admin or Manager)
if ($method === 'POST') {
    if (!isAdmin() && !isManager()) {
        jsonResponse(['error' => 'Only admin or manager can create demand requests'], 403);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || empty($data['client_name'])) {
        jsonResponse(['error' => 'Client name is required'], 400);
    }

    // Determine assigned attorney (default to Chong, id=2)
    $assignedTo = intval($data['assigned_to'] ?? 2);
    if ($assignedTo <= 0) {
        $assignedTo = 2;
    }

    $stmt = $pdo->prepare("
        INSERT INTO demand_requests (requested_by, assigned_to, client_name, case_number, case_type, note)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $user['id'],
        $assignedTo,
        sanitizeString($data['client_name'], 200),
        sanitizeString($data['case_number'] ?? '', 50),
        sanitizeString($data['case_type'] ?? 'Auto', 50),
        sanitizeString($data['note'] ?? '', 2000)
    ]);

    $requestId = $pdo->lastInsertId();

    // Create notification for assigned attorney
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, data, created_at)
        VALUES (?, 'demand_request', 'New Demand Case Request', ?, ?, NOW())
    ");

    $notificationData = json_encode([
        'request_id' => $requestId,
        'client_name' => $data['client_name'],
        'requester_id' => $user['id'],
        'requester_name' => $user['display_name']
    ]);

    $stmt->execute([
        $assignedTo,
        "New demand case request from {$user['display_name']}: {$data['client_name']}",
        $notificationData
    ]);

    jsonResponse(['success' => true, 'id' => $requestId]);
}

// PUT - Accept or Deny request (Attorney only)
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['id']) || !isset($data['action'])) {
        jsonResponse(['error' => 'Invalid data'], 400);
    }

    if (!isAttorney()) {
        jsonResponse(['error' => 'Only assigned attorney can respond'], 403);
    }

    // Verify request exists and is assigned to this user
    $stmt = $pdo->prepare("SELECT * FROM demand_requests WHERE id = ? AND assigned_to = ?");
    $stmt->execute([$data['id'], $user['id']]);
    $request = $stmt->fetch();

    if (!$request) {
        jsonResponse(['error' => 'Request not found'], 404);
    }

    if ($request['status'] !== 'pending') {
        jsonResponse(['error' => 'Request already processed'], 400);
    }

    if ($data['action'] === 'accept') {
        // Update request status
        $stmt = $pdo->prepare("
            UPDATE demand_requests SET status = 'accepted', responded_at = NOW() WHERE id = ?
        ");
        $stmt->execute([$data['id']]);

        // Create demand case from request
        $assignedDate = date('Y-m-d');
        $demandDeadline = calculateDemandDeadline($assignedDate);

        $stmt = $pdo->prepare("
            INSERT INTO cases (
                user_id, case_number, client_name, case_type,
                phase, stage, status, assigned_date, demand_deadline,
                note, month
            ) VALUES (?, ?, ?, ?, 'demand', 'demand_review', 'in_progress', ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user['id'],
            $request['case_number'] ?: '',
            $request['client_name'],
            $request['case_type'] ?: 'Auto',
            $assignedDate,
            $demandDeadline,
            $request['note'] ?: '',
            date('M. Y')
        ]);

        $caseId = $pdo->lastInsertId();

        // Notify requester that request was accepted
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, data, created_at)
            VALUES (?, 'demand_accepted', 'Demand Request Accepted', ?, ?, NOW())
        ");

        $notificationData = json_encode([
            'request_id' => $request['id'],
            'case_id' => $caseId,
            'client_name' => $request['client_name']
        ]);

        $stmt->execute([
            $request['requested_by'],
            "Your demand case request for {$request['client_name']} has been accepted",
            $notificationData
        ]);

        jsonResponse(['success' => true, 'case_id' => $caseId]);

    } else if ($data['action'] === 'deny') {
        if (empty($data['deny_reason'])) {
            jsonResponse(['error' => 'Deny reason is required'], 400);
        }

        $stmt = $pdo->prepare("
            UPDATE demand_requests SET status = 'denied', deny_reason = ?, responded_at = NOW() WHERE id = ?
        ");
        $stmt->execute([
            sanitizeString($data['deny_reason'], 1000),
            $data['id']
        ]);

        // Notify requester that request was denied
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, data, created_at)
            VALUES (?, 'demand_denied', 'Demand Request Denied', ?, ?, NOW())
        ");

        $notificationData = json_encode([
            'request_id' => $request['id'],
            'client_name' => $request['client_name'],
            'deny_reason' => $data['deny_reason']
        ]);

        $stmt->execute([
            $request['requested_by'],
            "Your demand case request for {$request['client_name']} was denied: {$data['deny_reason']}",
            $notificationData
        ]);

        jsonResponse(['success' => true]);

    } else {
        jsonResponse(['error' => 'Invalid action'], 400);
    }
}

// DELETE - Delete request (Admin only)
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['id'])) {
        jsonResponse(['error' => 'Invalid data'], 400);
    }

    if (!isAdmin()) {
        jsonResponse(['error' => 'Only admin can delete requests'], 403);
    }

    $stmt = $pdo->prepare("SELECT * FROM demand_requests WHERE id = ?");
    $stmt->execute([$data['id']]);
    $request = $stmt->fetch();

    if (!$request) {
        jsonResponse(['error' => 'Request not found'], 404);
    }

    $stmt = $pdo->prepare("DELETE FROM demand_requests WHERE id = ?");
    $stmt->execute([$data['id']]);

    jsonResponse(['success' => true]);
}
