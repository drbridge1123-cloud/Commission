<?php
/**
 * Traffic Requests API
 * Admin can request traffic cases, Chong can accept/deny
 */
error_reporting(0); // Suppress PHP warnings in JSON output
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
    $type = sanitizeString($_GET['type'] ?? 'all', 20);

    // Admin sees requests they made
    // Chong (user_id=2) sees requests assigned to them
    if (isAdmin()) {
        if ($type === 'sent') {
            // Admin's sent requests
            $stmt = $pdo->prepare("
                SELECT tr.*, u.display_name as assigned_to_name
                FROM traffic_requests tr
                JOIN users u ON tr.assigned_to = u.id
                WHERE tr.requested_by = ?
                ORDER BY tr.created_at DESC
            ");
            $stmt->execute([$user['id']]);
        } else {
            // All requests (for admin overview)
            $stmt = $pdo->prepare("
                SELECT tr.*,
                       u1.display_name as requester_name,
                       u2.display_name as assigned_to_name
                FROM traffic_requests tr
                JOIN users u1 ON tr.requested_by = u1.id
                JOIN users u2 ON tr.assigned_to = u2.id
                ORDER BY tr.created_at DESC
            ");
            $stmt->execute();
        }
    } else if ($user['id'] == 2) {
        // Chong sees requests assigned to them
        $status = sanitizeString($_GET['status'] ?? 'all', 20);

        $sql = "
            SELECT tr.*, u.display_name as requester_name
            FROM traffic_requests tr
            JOIN users u ON tr.requested_by = u.id
            WHERE tr.assigned_to = ?
        ";
        $params = [$user['id']];

        if ($status !== 'all') {
            $sql .= " AND tr.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY tr.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else if (hasPermission('can_request_traffic')) {
        // Employee with traffic permission: see their own sent requests
        $stmt = $pdo->prepare("
            SELECT tr.*, u.display_name as assigned_to_name
            FROM traffic_requests tr
            JOIN users u ON tr.assigned_to = u.id
            WHERE tr.requested_by = ?
            ORDER BY tr.created_at DESC
        ");
        $stmt->execute([$user['id']]);
    } else {
        jsonResponse(['error' => 'Access denied'], 403);
    }

    $requests = $stmt->fetchAll();
    jsonResponse(['requests' => $requests, 'csrf_token' => generateCSRFToken()]);
}

// Require CSRF for state-changing operations
requireCSRFToken();

// POST - Create new request (Admin or permitted employees)
if ($method === 'POST') {
    if (!hasPermission('can_request_traffic')) {
        jsonResponse(['error' => 'You do not have permission to create traffic requests'], 403);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || empty($data['client_name'])) {
        jsonResponse(['error' => 'Client name is required'], 400);
    }

    $courtDate = !empty($data['court_date']) ? $data['court_date'] : null;
    $citationIssuedDate = !empty($data['citation_issued_date']) ? $data['citation_issued_date'] : null;

    $stmt = $pdo->prepare("
        INSERT INTO traffic_requests (requested_by, assigned_to, client_name, client_phone, client_email, court, court_date, charge, case_number, note, citation_issued_date, referral_source)
        VALUES (?, 2, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $user['id'],
        sanitizeString($data['client_name'], 200),
        sanitizeString($data['client_phone'] ?? '', 50),
        sanitizeString($data['client_email'] ?? '', 200),
        sanitizeString($data['court'] ?? '', 100),
        $courtDate,
        sanitizeString($data['charge'] ?? '', 200),
        sanitizeString($data['case_number'] ?? '', 50),
        sanitizeString($data['note'] ?? '', 2000),
        $citationIssuedDate,
        sanitizeString($data['referral_source'] ?? '', 100)
    ]);

    $requestId = $pdo->lastInsertId();

    // Create notification for Chong
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, data, created_at)
        VALUES (2, 'traffic_request', 'New Traffic Case Request', ?, ?, NOW())
    ");

    $notificationData = json_encode([
        'request_id' => $requestId,
        'client_name' => $data['client_name'],
        'requester_id' => $user['id'],
        'requester_name' => $user['display_name']
    ]);

    $stmt->execute([
        "New traffic case request from {$user['display_name']}: {$data['client_name']}",
        $notificationData
    ]);

    jsonResponse(['success' => true, 'id' => $requestId]);
}

// PUT - Accept or Deny request (Chong only)
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['id']) || !isset($data['action'])) {
        jsonResponse(['error' => 'Invalid data'], 400);
    }

    // Only Chong can accept/deny
    if ($user['id'] != 2) {
        jsonResponse(['error' => 'Only assigned user can respond'], 403);
    }

    // Verify request exists and is assigned to this user
    $stmt = $pdo->prepare("SELECT * FROM traffic_requests WHERE id = ? AND assigned_to = ?");
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
            UPDATE traffic_requests SET status = 'accepted', responded_at = NOW() WHERE id = ?
        ");
        $stmt->execute([$data['id']]);

        // Create traffic case from request
        $stmt = $pdo->prepare("
            INSERT INTO traffic_cases (user_id, client_name, client_phone, client_email, court, court_date, charge, case_number, note, request_id, requested_by, status, disposition, citation_issued_date, referral_source)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 'pending', ?, ?)
        ");

        $stmt->execute([
            $user['id'],
            $request['client_name'],
            $request['client_phone'],
            $request['client_email'],
            $request['court'],
            $request['court_date'],
            $request['charge'],
            $request['case_number'],
            $request['note'],
            $request['id'],
            $request['requested_by'],
            $request['citation_issued_date'],
            $request['referral_source']
        ]);

        $caseId = $pdo->lastInsertId();

        // Notify admin that request was accepted
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, data, created_at)
            VALUES (?, 'traffic_accepted', 'Traffic Request Accepted', ?, ?, NOW())
        ");

        $notificationData = json_encode([
            'request_id' => $request['id'],
            'case_id' => $caseId,
            'client_name' => $request['client_name']
        ]);

        $stmt->execute([
            $request['requested_by'],
            "Your traffic case request for {$request['client_name']} has been accepted",
            $notificationData
        ]);

        jsonResponse(['success' => true, 'case_id' => $caseId]);

    } else if ($data['action'] === 'deny') {
        // Deny reason is required
        if (empty($data['deny_reason'])) {
            jsonResponse(['error' => 'Deny reason is required'], 400);
        }

        // Update request status
        $stmt = $pdo->prepare("
            UPDATE traffic_requests SET status = 'denied', deny_reason = ?, responded_at = NOW() WHERE id = ?
        ");
        $stmt->execute([
            sanitizeString($data['deny_reason'], 1000),
            $data['id']
        ]);

        // Notify admin that request was denied
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, data, created_at)
            VALUES (?, 'traffic_denied', 'Traffic Request Denied', ?, ?, NOW())
        ");

        $notificationData = json_encode([
            'request_id' => $request['id'],
            'client_name' => $request['client_name'],
            'deny_reason' => $data['deny_reason']
        ]);

        $stmt->execute([
            $request['requested_by'],
            "Your traffic case request for {$request['client_name']} was denied: {$data['deny_reason']}",
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

    // Verify request exists and belongs to this admin
    $stmt = $pdo->prepare("SELECT * FROM traffic_requests WHERE id = ? AND requested_by = ?");
    $stmt->execute([$data['id'], $user['id']]);
    $request = $stmt->fetch();

    if (!$request) {
        jsonResponse(['error' => 'Request not found'], 404);
    }

    $stmt = $pdo->prepare("DELETE FROM traffic_requests WHERE id = ?");
    $stmt->execute([$data['id']]);

    jsonResponse(['success' => true]);
}
