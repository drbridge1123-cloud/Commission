<?php
/**
 * Manager Team API
 * Manage which employees are assigned to which manager
 * Admin only
 */
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

if (!isAdmin()) {
    jsonResponse(['error' => 'Admin access required'], 403);
}

$pdo = getDB();
$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// GET - Get team members for a manager
if ($method === 'GET') {
    $managerId = intval($_GET['manager_id'] ?? 0);
    if ($managerId <= 0) {
        jsonResponse(['error' => 'Manager ID required'], 400);
    }

    $stmt = $pdo->prepare("
        SELECT mt.employee_id, u.display_name, u.username
        FROM manager_team mt
        JOIN users u ON mt.employee_id = u.id AND u.is_active = 1
        WHERE mt.manager_id = ?
        ORDER BY u.display_name
    ");
    $stmt->execute([$managerId]);
    $members = $stmt->fetchAll();

    jsonResponse(['members' => $members, 'csrf_token' => generateCSRFToken()]);
}

// POST - Set team members (replace all)
if ($method === 'POST') {
    requireCSRFToken();

    $data = json_decode(file_get_contents('php://input'), true);
    $managerId = intval($data['manager_id'] ?? 0);
    $employeeIds = $data['employee_ids'] ?? [];

    if ($managerId <= 0) {
        jsonResponse(['error' => 'Manager ID required'], 400);
    }

    // Verify manager exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND is_manager = 1 AND is_active = 1");
    $stmt->execute([$managerId]);
    if (!$stmt->fetch()) {
        jsonResponse(['error' => 'Manager not found'], 404);
    }

    // Clear existing team
    $stmt = $pdo->prepare("DELETE FROM manager_team WHERE manager_id = ?");
    $stmt->execute([$managerId]);

    // Insert new team members
    if (!empty($employeeIds)) {
        $insertStmt = $pdo->prepare("INSERT INTO manager_team (manager_id, employee_id) VALUES (?, ?)");
        foreach ($employeeIds as $empId) {
            $empId = intval($empId);
            if ($empId > 0 && $empId !== $managerId) {
                $insertStmt->execute([$managerId, $empId]);
            }
        }
    }

    logAudit('update_team', 'manager_team', $managerId, null, ['employee_ids' => $employeeIds]);
    jsonResponse(['success' => true, 'csrf_token' => generateCSRFToken()]);
}

// DELETE - Remove a single team member
if ($method === 'DELETE') {
    requireCSRFToken();

    $managerId = intval($_GET['manager_id'] ?? 0);
    $employeeId = intval($_GET['employee_id'] ?? 0);

    if ($managerId <= 0 || $employeeId <= 0) {
        jsonResponse(['error' => 'Manager ID and Employee ID required'], 400);
    }

    $stmt = $pdo->prepare("DELETE FROM manager_team WHERE manager_id = ? AND employee_id = ?");
    $stmt->execute([$managerId, $employeeId]);

    logAudit('remove_team_member', 'manager_team', $managerId, null, ['employee_id' => $employeeId]);
    jsonResponse(['success' => true, 'csrf_token' => generateCSRFToken()]);
}
?>
