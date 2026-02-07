<?php
/**
 * Employee Goals API
 * Manage annual goals (cases count, legal fee target) per employee
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

// GET - Fetch goals + progress
if ($method === 'GET') {
    $action = sanitizeString($_GET['action'] ?? 'single', 20);
    $year = intval($_GET['year'] ?? date('Y'));

    if ($action === 'summary') {
        // Admin only: all employees overview
        if (!isAdmin()) {
            jsonResponse(['error' => 'Admin access required'], 403);
        }

        $stmt = $pdo->prepare("
            SELECT
                u.id, u.display_name, u.username,
                COALESCE(g.target_cases, 50) as target_cases,
                COALESCE(g.target_legal_fee, 500000.00) as target_legal_fee,
                g.notes as goal_notes,
                COUNT(c.id) as actual_cases,
                COALESCE(SUM(c.legal_fee), 0) as actual_legal_fee
            FROM users u
            LEFT JOIN employee_goals g ON u.id = g.user_id AND g.year = ?
            LEFT JOIN cases c ON u.id = c.user_id AND c.deleted_at IS NULL AND c.status != 'rejected' AND c.month LIKE ?
            WHERE u.role = 'employee' AND u.is_active = 1
            GROUP BY u.id
            ORDER BY u.display_name
        ");
        $stmt->execute([$year, "%. $year"]);
        $employees = $stmt->fetchAll();

        // Calculate percentages
        foreach ($employees as &$emp) {
            $emp['cases_percent'] = $emp['target_cases'] > 0
                ? round(($emp['actual_cases'] / $emp['target_cases']) * 100, 1)
                : 0;
            $emp['legal_fee_percent'] = $emp['target_legal_fee'] > 0
                ? round(($emp['actual_legal_fee'] / $emp['target_legal_fee']) * 100, 1)
                : 0;
        }

        jsonResponse(['employees' => $employees, 'year' => $year, 'csrf_token' => generateCSRFToken()]);

    } else {
        // Single employee goals + progress
        $targetUserId = isAdmin() ? intval($_GET['user_id'] ?? $user['id']) : $user['id'];

        // Get goal (or defaults)
        $stmt = $pdo->prepare("SELECT * FROM employee_goals WHERE user_id = ? AND year = ?");
        $stmt->execute([$targetUserId, $year]);
        $goal = $stmt->fetch();

        $targetCases = $goal ? (int)$goal['target_cases'] : 50;
        $targetLegalFee = $goal ? (float)$goal['target_legal_fee'] : 500000.00;

        // Get actual progress
        $stmt = $pdo->prepare("
            SELECT
                COUNT(*) as actual_cases,
                COALESCE(SUM(legal_fee), 0) as actual_legal_fee
            FROM cases
            WHERE user_id = ? AND deleted_at IS NULL AND status != 'rejected'
            AND month LIKE ?
        ");
        $stmt->execute([$targetUserId, "%. $year"]);
        $progress = $stmt->fetch();

        $actualCases = (int)$progress['actual_cases'];
        $actualLegalFee = (float)$progress['actual_legal_fee'];

        // Monthly breakdown
        $stmt = $pdo->prepare("
            SELECT
                month,
                COUNT(*) as cases_count,
                COALESCE(SUM(legal_fee), 0) as legal_fee_total
            FROM cases
            WHERE user_id = ? AND deleted_at IS NULL AND status != 'rejected'
            AND month LIKE ?
            GROUP BY month
            ORDER BY STR_TO_DATE(CONCAT('01 ', REPLACE(month, '.', '')), '%d %b %Y')
        ");
        $stmt->execute([$targetUserId, "%. $year"]);
        $monthly = $stmt->fetchAll();

        jsonResponse([
            'goal' => [
                'user_id' => $targetUserId,
                'year' => $year,
                'target_cases' => $targetCases,
                'target_legal_fee' => $targetLegalFee,
                'notes' => $goal ? $goal['notes'] : null
            ],
            'progress' => [
                'actual_cases' => $actualCases,
                'actual_legal_fee' => $actualLegalFee,
                'cases_percent' => $targetCases > 0 ? round(($actualCases / $targetCases) * 100, 1) : 0,
                'legal_fee_percent' => $targetLegalFee > 0 ? round(($actualLegalFee / $targetLegalFee) * 100, 1) : 0
            ],
            'monthly' => $monthly,
            'csrf_token' => generateCSRFToken()
        ]);
    }
}

// POST - Set/Update goals (Admin only)
if ($method === 'POST') {
    requireCSRFToken();

    if (!isAdmin()) {
        jsonResponse(['error' => 'Admin access required'], 403);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || empty($data['user_id'])) {
        jsonResponse(['error' => 'User ID is required'], 400);
    }

    $targetUserId = intval($data['user_id']);
    $year = intval($data['year'] ?? date('Y'));
    $targetCases = max(1, intval($data['target_cases'] ?? 50));
    $targetLegalFee = max(0, floatval($data['target_legal_fee'] ?? 500000));
    $notes = sanitizeString($data['notes'] ?? '', 1000);

    // Verify user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$targetUserId]);
    if (!$stmt->fetch()) {
        jsonResponse(['error' => 'User not found'], 404);
    }

    // Upsert
    $stmt = $pdo->prepare("
        INSERT INTO employee_goals (user_id, year, target_cases, target_legal_fee, notes, created_by)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            target_cases = VALUES(target_cases),
            target_legal_fee = VALUES(target_legal_fee),
            notes = VALUES(notes),
            updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$targetUserId, $year, $targetCases, $targetLegalFee, $notes, $user['id']]);

    jsonResponse(['success' => true, 'csrf_token' => generateCSRFToken()]);
}
