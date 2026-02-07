<?php
/**
 * Users API
 * User management and password changes
 */
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

// Rate limiting
requireRateLimit('api_users', 30, 60);

$pdo = getDB();
$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// GET - Get user list
if ($method === 'GET') {
    if (isAdmin()) {
        // Admin can see all users with full details
        $stmt = $pdo->query("
            SELECT id, username, display_name, role, commission_rate, uses_presuit_offer, permissions, is_active, created_at
            FROM users
            ORDER BY role, display_name
        ");
        $users = $stmt->fetchAll();
        foreach ($users as &$u) {
            $u['permissions'] = json_decode($u['permissions'] ?? '{}', true) ?: [];
        }
        jsonResponse(['users' => $users, 'current_user_id' => $user['id'], 'csrf_token' => generateCSRFToken()]);
    } else {
        // Employee can only see admin users (for sending messages)
        $stmt = $pdo->query("SELECT id, username, display_name, role FROM users WHERE role = 'admin' AND is_active = 1");
        $users = $stmt->fetchAll();
        jsonResponse(['users' => $users, 'current_user_id' => $user['id'], 'csrf_token' => generateCSRFToken()]);
    }
}

// Require CSRF for state-changing operations
requireCSRFToken();

// POST - Change own password OR Create new user (admin only)
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if this is a user creation request (admin only)
    if (isset($data['username']) && isset($data['display_name'])) {
        if (!isAdmin()) {
            jsonResponse(['error' => 'Admin access required'], 403);
        }

        $username = trim($data['username']);
        $displayName = trim($data['display_name']);
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? 'employee';
        $commissionRate = floatval($data['commission_rate'] ?? 0);

        if (empty($username) || empty($displayName) || empty($password)) {
            jsonResponse(['error' => 'Username, display name, and password are required'], 400);
        }

        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            jsonResponse(['error' => 'Username already exists'], 400);
        }

        // Validate password
        $passwordErrors = validatePassword($password);
        if (!empty($passwordErrors)) {
            jsonResponse(['error' => implode(' ', $passwordErrors)], 400);
        }

        // Create new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $defaultPermissions = json_encode(['can_request_traffic' => false]);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, display_name, role, commission_rate, permissions, is_active)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$username, $hashedPassword, $displayName, $role, $commissionRate, $defaultPermissions]);

        logAudit('create_user', 'users', $pdo->lastInsertId(), null, [
            'created_by' => $user['display_name'],
            'username' => $username,
            'role' => $role
        ]);

        jsonResponse(['success' => true, 'message' => 'User created successfully', 'user_id' => $pdo->lastInsertId()]);
    }

    // Otherwise, this is a password change request
    $currentPassword = $data['current_password'] ?? '';
    $newPassword = $data['new_password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword)) {
        jsonResponse(['error' => 'Current and new password are required'], 400);
    }

    if ($newPassword !== $confirmPassword) {
        jsonResponse(['error' => 'New passwords do not match'], 400);
    }

    // Validate password policy
    $passwordErrors = validatePassword($newPassword);
    if (!empty($passwordErrors)) {
        jsonResponse(['error' => implode(' ', $passwordErrors)], 400);
    }

    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $userData = $stmt->fetch();

    if (!password_verify($currentPassword, $userData['password'])) {
        logAudit('password_change_failed', 'users', $user['id'], null, ['reason' => 'incorrect_current_password']);
        jsonResponse(['error' => 'Current password is incorrect'], 400);
    }

    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $user['id']]);

    logAudit('password_changed', 'users', $user['id'], null, null);

    jsonResponse(['success' => true, 'message' => 'Password updated successfully']);
}

// PUT - Update user (admin only)
if ($method === 'PUT') {
    if (!isAdmin()) {
        jsonResponse(['error' => 'Admin access required'], 403);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    // Get user ID from query string
    $userId = intval($_GET['id'] ?? 0);
    
    if ($userId <= 0) {
        jsonResponse(['error' => 'Invalid user ID'], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $targetUser = $stmt->fetch();

    if (!$targetUser) {
        jsonResponse(['error' => 'User not found'], 404);
    }

    // Store old data for audit
    $oldData = [
        'username' => $targetUser['username'],
        'display_name' => $targetUser['display_name'],
        'role' => $targetUser['role'],
        'commission_rate' => $targetUser['commission_rate'],
        'uses_presuit_offer' => $targetUser['uses_presuit_offer'],
        'permissions' => json_decode($targetUser['permissions'] ?? '{}', true),
        'is_active' => $targetUser['is_active']
    ];

    $changes = [];
    $updates = [];
    $params = [];

    // Update username
    if (isset($data['username'])) {
        $newUsername = trim($data['username']);
        // Check if new username already exists (for other users)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$newUsername, $userId]);
        if ($stmt->fetch()) {
            jsonResponse(['error' => 'Username already exists'], 400);
        }
        $updates[] = "username = ?";
        $params[] = $newUsername;
        $changes['username'] = $newUsername;
    }

    // Update display name
    if (isset($data['display_name'])) {
        $updates[] = "display_name = ?";
        $params[] = trim($data['display_name']);
        $changes['display_name'] = $data['display_name'];
    }

    // Update role
    if (isset($data['role'])) {
        $updates[] = "role = ?";
        $params[] = $data['role'];
        $changes['role'] = $data['role'];
    }

    // Update commission rate
    if (isset($data['commission_rate'])) {
        $commissionRate = sanitizeNumber($data['commission_rate'], 0, 100);
        $updates[] = "commission_rate = ?";
        $params[] = $commissionRate;
        $changes['commission_rate'] = $commissionRate;
    }

    // Update uses_presuit_offer
    if (isset($data['uses_presuit_offer'])) {
        $usesPresuitOffer = !empty($data['uses_presuit_offer']) ? 1 : 0;
        $updates[] = "uses_presuit_offer = ?";
        $params[] = $usesPresuitOffer;
        $changes['uses_presuit_offer'] = $usesPresuitOffer;
    }

    // Update permissions
    if (isset($data['permissions']) && is_array($data['permissions'])) {
        $allowedPermissions = ['can_request_traffic'];
        $existing = json_decode($targetUser['permissions'] ?? '{}', true) ?: [];
        foreach ($allowedPermissions as $perm) {
            if (isset($data['permissions'][$perm])) {
                $existing[$perm] = !empty($data['permissions'][$perm]);
            }
        }
        $updates[] = "permissions = ?";
        $params[] = json_encode($existing);
        $changes['permissions'] = $existing;
    }

    // Toggle active status
    if (isset($data['is_active'])) {
        // Prevent deactivating yourself
        if ($userId === $user['id'] && empty($data['is_active'])) {
            jsonResponse(['error' => 'Cannot deactivate your own account'], 400);
        }

        $isActive = !empty($data['is_active']) ? 1 : 0;
        $updates[] = "is_active = ?";
        $params[] = $isActive;
        $changes['is_active'] = $isActive;
    }

    // Update password if provided
    if (isset($data['password']) && trim($data['password']) !== '') {
        $passwordErrors = validatePassword($data['password']);
        if (!empty($passwordErrors)) {
            jsonResponse(['error' => implode(' ', $passwordErrors)], 400);
        }
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $updates[] = "password = ?";
        $params[] = $hashedPassword;
        $changes['password_reset'] = true;

        logAudit('admin_password_reset', 'users', $userId, null, [
            'reset_by' => $user['display_name'],
            'target_user' => $targetUser['display_name']
        ]);
    }

    // Reset password (generate random temporary password)
    if (!empty($data['reset_password'])) {
        // Generate a random temporary password
        $tempPassword = generateTempPassword(); // More secure temp password
        $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
        $updates[] = "password = ?";
        $params[] = $hashedPassword;
        $changes['password_reset'] = true;

        logAudit('admin_password_reset', 'users', $userId, null, [
            'reset_by' => $user['display_name'],
            'target_user' => $targetUser['display_name']
        ]);

        // Execute update
        if (!empty($updates)) {
            $params[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            logAudit('update_user', 'users', $userId, $oldData, $changes);
        }

        // Return the temporary password to admin
        jsonResponse([
            'success' => true,
            'temp_password' => $tempPassword,
            'message' => "Password reset. Temporary password: {$tempPassword}"
        ]);
    }

    // Execute update if there are changes
    if (!empty($updates)) {
        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        logAudit('update_user', 'users', $userId, $oldData, $changes);
    }

    jsonResponse(['success' => true, 'message' => 'User updated successfully']);
}

// DELETE - Delete user (admin only)
if ($method === 'DELETE') {
    if (!isAdmin()) {
        jsonResponse(['error' => 'Admin access required'], 403);
    }

    $userId = intval($_GET['id'] ?? 0);

    if ($userId <= 0) {
        jsonResponse(['error' => 'Invalid user ID'], 400);
    }

    // Prevent deleting yourself
    if ($userId === $user['id']) {
        jsonResponse(['error' => 'Cannot delete your own account'], 400);
    }

    // Get user info for audit
    $stmt = $pdo->prepare("SELECT username, display_name, role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $targetUser = $stmt->fetch();

    if (!$targetUser) {
        jsonResponse(['error' => 'User not found'], 404);
    }

    // Don't allow deleting admin users (optional safety measure)
    if ($targetUser['role'] === 'admin') {
        jsonResponse(['error' => 'Cannot delete admin users'], 400);
    }

    // Delete the user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);

    logAudit('delete_user', 'users', $userId, $targetUser, [
        'deleted_by' => $user['display_name']
    ]);

    jsonResponse(['success' => true, 'message' => 'User deleted successfully']);
}
?>
