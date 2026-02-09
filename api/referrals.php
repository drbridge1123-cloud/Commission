<?php
/**
 * Referrals API
 * CRUD operations for referral entries (manager + admin)
 */
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

// Only managers and admins can access referrals
if (!isManager() && !isAdmin()) {
    jsonResponse(['error' => 'Access denied'], 403);
}

requireRateLimit('api_referrals', 60, 60);

$pdo = getDB();
$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// GET - Fetch referral entries
if ($method === 'GET') {
    $action = sanitizeString($_GET['action'] ?? 'list', 20);

    if ($action === 'list') {
        $year = intval($_GET['year'] ?? date('Y'));
        $caseManagerId = $_GET['case_manager_id'] ?? 'all';
        $month = intval($_GET['month'] ?? 0);

        $sql = "SELECT r.*, u.display_name as case_manager_name, cr.display_name as created_by_name
                FROM referral_entries r
                LEFT JOIN users u ON r.case_manager_id = u.id
                LEFT JOIN users cr ON r.created_by = cr.id
                WHERE r.deleted_at IS NULL";
        $params = [];

        if ($year > 0) {
            $sql .= " AND YEAR(r.signed_date) = ?";
            $params[] = $year;
        }
        if ($month > 0) {
            $sql .= " AND MONTH(r.signed_date) = ?";
            $params[] = $month;
        }
        if ($caseManagerId !== 'all' && $caseManagerId !== '') {
            $sql .= " AND r.case_manager_id = ?";
            $params[] = intval($caseManagerId);
        }

        $sql .= " ORDER BY r.signed_date DESC, r.row_number DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $entries = $stmt->fetchAll();

        jsonResponse([
            'entries' => $entries,
            'csrf_token' => generateCSRFToken()
        ]);
    }

    if ($action === 'summary') {
        $year = intval($_GET['year'] ?? date('Y'));
        $currentMonth = intval(date('n'));

        // Total entries this year
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM referral_entries WHERE deleted_at IS NULL AND YEAR(signed_date) = ?");
        $stmt->execute([$year]);
        $totalYear = (int)$stmt->fetch()['total'];

        // This month
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM referral_entries WHERE deleted_at IS NULL AND YEAR(signed_date) = ? AND MONTH(signed_date) = ?");
        $stmt->execute([$year, $currentMonth]);
        $totalMonth = (int)$stmt->fetch()['total'];

        // By case manager this year
        $stmt = $pdo->prepare("
            SELECT u.display_name as name, COUNT(*) as count
            FROM referral_entries r
            LEFT JOIN users u ON r.case_manager_id = u.id
            WHERE r.deleted_at IS NULL AND YEAR(r.signed_date) = ?
            GROUP BY r.case_manager_id
            ORDER BY count DESC
        ");
        $stmt->execute([$year]);
        $byManager = $stmt->fetchAll();

        // By referral source this year (top 10)
        $stmt = $pdo->prepare("
            SELECT referred_by, COUNT(*) as count
            FROM referral_entries
            WHERE deleted_at IS NULL AND YEAR(signed_date) = ? AND referred_by IS NOT NULL AND referred_by != ''
            GROUP BY referred_by
            ORDER BY count DESC
            LIMIT 10
        ");
        $stmt->execute([$year]);
        $bySource = $stmt->fetchAll();

        jsonResponse([
            'total_year' => $totalYear,
            'total_month' => $totalMonth,
            'by_manager' => $byManager,
            'by_source' => $bySource,
            'csrf_token' => generateCSRFToken()
        ]);
    }

    jsonResponse(['error' => 'Unknown action'], 400);
}

// Require CSRF for state-changing operations
requireCSRFToken();

// POST - Create new referral entry
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        jsonResponse(['error' => 'Invalid data'], 400);
    }

    $clientName = sanitizeString($data['client_name'] ?? '', 300);
    if (empty($clientName)) {
        jsonResponse(['error' => 'Client name is required'], 400);
    }

    $signedDate = sanitizeString($data['signed_date'] ?? date('Y-m-d'), 20);
    $entryMonth = date('M. Y', strtotime($signedDate));

    // Calculate next row_number for the month
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(row_number), 0) + 1 as next_row FROM referral_entries WHERE entry_month = ? AND deleted_at IS NULL");
    $stmt->execute([$entryMonth]);
    $nextRow = (int)$stmt->fetch()['next_row'];

    $caseManagerId = !empty($data['case_manager_id']) ? intval($data['case_manager_id']) : null;

    $stmt = $pdo->prepare("
        INSERT INTO referral_entries (
            row_number, signed_date, file_number, client_name, date_of_loss,
            referred_by, referred_to_provider, referred_to_body_shop,
            referral_type, case_manager_id, remark, entry_month, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $nextRow,
        $signedDate,
        sanitizeString($data['file_number'] ?? '', 50),
        $clientName,
        !empty($data['date_of_loss']) ? sanitizeString($data['date_of_loss'], 20) : null,
        sanitizeString($data['referred_by'] ?? '', 200),
        sanitizeString($data['referred_to_provider'] ?? '', 200),
        sanitizeString($data['referred_to_body_shop'] ?? '', 200),
        sanitizeString($data['referral_type'] ?? '', 100),
        $caseManagerId,
        sanitizeString($data['remark'] ?? '', 1000),
        $entryMonth,
        $user['id']
    ]);

    $newId = $pdo->lastInsertId();
    logAudit('create', 'referral_entries', $newId, null, [
        'client_name' => $clientName,
        'file_number' => $data['file_number'] ?? ''
    ]);

    jsonResponse(['success' => true, 'id' => $newId, 'csrf_token' => generateCSRFToken()]);
}

// PUT - Update referral entry
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $entryId = intval($data['id'] ?? $_GET['id'] ?? 0);
    if ($entryId <= 0) {
        jsonResponse(['error' => 'Invalid entry ID'], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM referral_entries WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$entryId]);
    $entry = $stmt->fetch();
    if (!$entry) {
        jsonResponse(['error' => 'Entry not found'], 404);
    }

    $caseManagerId = isset($data['case_manager_id'])
        ? (!empty($data['case_manager_id']) ? intval($data['case_manager_id']) : null)
        : $entry['case_manager_id'];

    $stmt = $pdo->prepare("
        UPDATE referral_entries SET
            signed_date = ?, file_number = ?, client_name = ?, date_of_loss = ?,
            referred_by = ?, referred_to_provider = ?, referred_to_body_shop = ?,
            referral_type = ?, case_manager_id = ?, remark = ?
        WHERE id = ?
    ");

    $stmt->execute([
        sanitizeString($data['signed_date'] ?? $entry['signed_date'], 20),
        sanitizeString($data['file_number'] ?? $entry['file_number'], 50),
        sanitizeString($data['client_name'] ?? $entry['client_name'], 300),
        !empty($data['date_of_loss']) ? sanitizeString($data['date_of_loss'], 20) : $entry['date_of_loss'],
        sanitizeString($data['referred_by'] ?? $entry['referred_by'], 200),
        sanitizeString($data['referred_to_provider'] ?? $entry['referred_to_provider'], 200),
        sanitizeString($data['referred_to_body_shop'] ?? $entry['referred_to_body_shop'], 200),
        sanitizeString($data['referral_type'] ?? $entry['referral_type'], 100),
        $caseManagerId,
        sanitizeString($data['remark'] ?? $entry['remark'], 1000),
        $entryId
    ]);

    logAudit('update', 'referral_entries', $entryId, $entry, $data);
    jsonResponse(['success' => true, 'csrf_token' => generateCSRFToken()]);
}

// DELETE - Soft delete referral entry
if ($method === 'DELETE') {
    $entryId = intval($_GET['id'] ?? 0);
    if ($entryId <= 0) {
        jsonResponse(['error' => 'Invalid entry ID'], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM referral_entries WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$entryId]);
    $entry = $stmt->fetch();
    if (!$entry) {
        jsonResponse(['error' => 'Entry not found'], 404);
    }

    $stmt = $pdo->prepare("UPDATE referral_entries SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$entryId]);

    logAudit('delete', 'referral_entries', $entryId, ['client_name' => $entry['client_name']], null);
    jsonResponse(['success' => true, 'csrf_token' => generateCSRFToken()]);
}
?>
