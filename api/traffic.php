<?php
/**
 * Traffic Cases API
 * CRUD operations for traffic cases (Chong only)
 */
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Require authentication
if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$pdo = getDB();
$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// Only attorneys or admin can access traffic cases
if (!isAttorney() && !isAdmin()) {
    jsonResponse(['error' => 'Access denied'], 403);
}

// GET - Fetch traffic cases
if ($method === 'GET') {
    $status = sanitizeString($_GET['status'] ?? 'all', 20);

    $sql = "SELECT t.*, u.display_name as requester_name
            FROM traffic_cases t
            LEFT JOIN users u ON t.requested_by = u.id
            WHERE t.user_id = ?";
    $params = [$user['id']];

    // Admin can see all (read-only view)
    if (isAdmin()) {
        $sql = "SELECT t.*, u.display_name as counsel_name, u2.display_name as requester_name
                FROM traffic_cases t
                JOIN users u ON t.user_id = u.id
                LEFT JOIN users u2 ON t.requested_by = u2.id
                WHERE 1=1";
        $params = [];
    }

    if ($status !== 'all') {
        $sql .= " AND status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY court_date ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cases = $stmt->fetchAll();

    jsonResponse(['cases' => $cases, 'csrf_token' => generateCSRFToken()]);
}

// Require CSRF for state-changing operations
requireCSRFToken();

// POST - Create new traffic case
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        jsonResponse(['error' => 'Invalid data'], 400);
    }

    // Calculate commission based on disposition
    $commission = 0;
    if (isset($data['disposition'])) {
        if ($data['disposition'] === 'dismissed') {
            $commission = 150;
        } else if ($data['disposition'] === 'amended') {
            $commission = 100;
        }
    }

    // Auto-resolve when disposition is dismissed or amended
    if (in_array($data['disposition'] ?? '', ['dismissed', 'amended'])) {
        $data['status'] = 'resolved';
    }

    $stmt = $pdo->prepare("
        INSERT INTO traffic_cases (user_id, client_name, client_phone, court, court_date, charge, case_number, prosecutor_offer, disposition, commission, discovery, status, resolved_at, note, referral_source, paid)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $courtDate = !empty($data['court_date']) ? $data['court_date'] : null;
    $resolvedAtPost = ($data['status'] ?? 'active') === 'resolved' ? date('Y-m-d H:i:s') : null;

    $stmt->execute([
        $user['id'],
        sanitizeString($data['client_name'] ?? '', 200),
        sanitizeString($data['client_phone'] ?? '', 50),
        sanitizeString($data['court'] ?? '', 100),
        $courtDate,
        sanitizeString($data['charge'] ?? '', 200),
        sanitizeString($data['case_number'] ?? '', 50),
        sanitizeString($data['prosecutor_offer'] ?? '', 1000),
        sanitizeString($data['disposition'] ?? 'pending', 20),
        $commission,
        isset($data['discovery']) ? ($data['discovery'] ? 1 : 0) : 0,
        sanitizeString($data['status'] ?? 'active', 20),
        $resolvedAtPost,
        sanitizeString($data['note'] ?? '', 2000),
        sanitizeString($data['referral_source'] ?? '', 100),
        isset($data['paid']) ? ($data['paid'] ? 1 : 0) : 0
    ]);

    jsonResponse(['success' => true, 'id' => $pdo->lastInsertId()]);
}

// PUT - Update traffic case
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['id'])) {
        jsonResponse(['error' => 'Invalid data'], 400);
    }

    // Mark Paid action (admin only) - supports single id or bulk ids[]
    if (isset($data['action']) && $data['action'] === 'mark_paid') {
        if (!isAdmin()) {
            jsonResponse(['error' => 'Admin only'], 403);
        }
        $paid = isset($data['paid']) ? ($data['paid'] ? 1 : 0) : 1;
        $paidAt = $paid ? date('Y-m-d H:i:s') : null;
        $ids = isset($data['ids']) ? $data['ids'] : [$data['id']];
        $stmt = $pdo->prepare("UPDATE traffic_cases SET paid = ?, paid_at = CASE WHEN ? = 1 AND paid_at IS NULL THEN ? WHEN ? = 0 THEN NULL ELSE paid_at END WHERE id = ?");
        foreach ($ids as $caseId) {
            $stmt->execute([$paid, $paid, $paidAt, $paid, $caseId]);
        }
        jsonResponse(['success' => true]);
    }

    // Verify ownership
    $stmt = $pdo->prepare("SELECT user_id FROM traffic_cases WHERE id = ?");
    $stmt->execute([$data['id']]);
    $case = $stmt->fetch();

    if (!$case || ($case['user_id'] != $user['id'] && !isAdmin())) {
        jsonResponse(['error' => 'Not found or access denied'], 404);
    }

    // Calculate commission based on disposition
    $commission = 0;
    if (isset($data['disposition'])) {
        if ($data['disposition'] === 'dismissed') {
            $commission = 150;
        } else if ($data['disposition'] === 'amended') {
            $commission = 100;
        }
    }

    // Auto-resolve when disposition is dismissed or amended
    if (in_array($data['disposition'] ?? '', ['dismissed', 'amended'])) {
        $data['status'] = 'resolved';
    }

    // Set resolved_at if status changed to resolved
    $resolvedAt = null;
    if (isset($data['status']) && $data['status'] === 'resolved') {
        $resolvedAt = date('Y-m-d H:i:s');
    }

    $courtDate = !empty($data['court_date']) ? $data['court_date'] : null;

    // Admin can now edit traffic cases
    // (Previously: Admin can only view, not edit)

    $noaSentDate = isset($data['noa_sent_date']) && !empty($data['noa_sent_date']) ? $data['noa_sent_date'] : null;
    $citationIssuedDate = isset($data['citation_issued_date']) && !empty($data['citation_issued_date']) ? $data['citation_issued_date'] : null;

    $paidVal = isset($data['paid']) ? ($data['paid'] ? 1 : 0) : 0;

    $stmt = $pdo->prepare("
        UPDATE traffic_cases
        SET client_name = ?, client_phone = ?, court = ?, court_date = ?, charge = ?,
            case_number = ?, prosecutor_offer = ?, disposition = ?, commission = ?,
            discovery = ?, status = ?, note = ?, resolved_at = COALESCE(?, resolved_at),
            referral_source = ?, paid = ?,
            paid_at = CASE WHEN ? = 1 AND paid_at IS NULL THEN NOW() WHEN ? = 0 THEN NULL ELSE paid_at END,
            noa_sent_date = ?, citation_issued_date = ?
        WHERE id = ?
    ");

    $stmt->execute([
        sanitizeString($data['client_name'] ?? '', 200),
        sanitizeString($data['client_phone'] ?? '', 50),
        sanitizeString($data['court'] ?? '', 100),
        $courtDate,
        sanitizeString($data['charge'] ?? '', 200),
        sanitizeString($data['case_number'] ?? '', 50),
        sanitizeString($data['prosecutor_offer'] ?? '', 1000),
        sanitizeString($data['disposition'] ?? 'pending', 20),
        $commission,
        isset($data['discovery']) ? ($data['discovery'] ? 1 : 0) : 0,
        sanitizeString($data['status'] ?? 'active', 20),
        sanitizeString($data['note'] ?? '', 2000),
        $resolvedAt,
        sanitizeString($data['referral_source'] ?? '', 100),
        $paidVal,
        $paidVal,
        $paidVal,
        $noaSentDate,
        $citationIssuedDate,
        $data['id']
    ]);

    jsonResponse(['success' => true]);
}

// DELETE - Delete traffic case
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['id'])) {
        jsonResponse(['error' => 'Invalid data'], 400);
    }

    // Verify ownership
    $stmt = $pdo->prepare("SELECT user_id FROM traffic_cases WHERE id = ?");
    $stmt->execute([$data['id']]);
    $case = $stmt->fetch();

    if (!$case || ($case['user_id'] != $user['id'] && !isAdmin())) {
        jsonResponse(['error' => 'Not found or access denied'], 404);
    }

    $stmt = $pdo->prepare("DELETE FROM traffic_cases WHERE id = ?");
    $stmt->execute([$data['id']]);

    jsonResponse(['success' => true]);
}
