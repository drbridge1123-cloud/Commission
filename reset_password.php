<?php
/**
 * Password Reset - Admin Only
 * Allows administrators to reset user passwords securely
 */
require_once 'includes/auth.php';

// Require admin authentication
requireAdmin();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } else {
        try {
            $pdo = getDB();

            $username = sanitizeString($_POST['username'] ?? '', 50);
            $newPassword = $_POST['password'] ?? '';

            if (empty($username) || empty($newPassword)) {
                throw new Exception('Username and password are required.');
            }

            // Validate password policy
            $passwordErrors = validatePassword($newPassword);
            if (!empty($passwordErrors)) {
                throw new Exception(implode(' ', $passwordErrors));
            }

            // Get old user data for audit
            $stmt = $pdo->prepare("SELECT id, username, display_name FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new Exception("User '{$username}' not found.");
            }

            // Hash and update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password = ?, is_active = 1 WHERE username = ?");
            $stmt->execute([$hashedPassword, $username]);

            if ($stmt->rowCount() > 0) {
                $message = "Password for '{$username}' has been reset successfully.";
                $messageType = 'success';

                // Log the action
                logAudit('password_reset', 'users', $user['id'], null, [
                    'reset_by' => getCurrentUser()['display_name'],
                    'target_user' => $username
                ]);
            } else {
                throw new Exception("Failed to update password.");
            }

        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get users list (exclude current admin from password reset)
try {
    $pdo = getDB();
    $currentUser = getCurrentUser();
    $stmt = $pdo->prepare("SELECT username, display_name, role, is_active FROM users WHERE id != ? ORDER BY display_name");
    $stmt->execute([$currentUser['id']]);
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $users = [];
}

// Generate CSRF token
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - Admin</title>
    <link rel="stylesheet" href="assets/css/common.css">
</head>
<body class="db-page-bg">
    <!-- Header -->
    <header class="admin-header">
        <div class="admin-header-content">
            <div>
                <h1 class="admin-header-title">Password Reset</h1>
                <p class="admin-header-subtitle">Admin Panel</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="admin-header-user">Logged in as: <strong><?= htmlspecialchars($currentUser['display_name']) ?></strong></span>
                <a href="admin.php" class="btn-back">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </header>

    <main class="admin-main">
        <div class="admin-card">
            <h2 class="admin-card-title">Reset User Password</h2>

            <?php if ($message): ?>
                <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-error' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Password Policy Info -->
            <div class="policy-box">
                <h3 class="policy-box-title">Password Requirements:</h3>
                <ul>
                    <li>Minimum 8 characters</li>
                    <li>At least one uppercase letter (A-Z)</li>
                    <li>At least one lowercase letter (a-z)</li>
                    <li>At least one number (0-9)</li>
                </ul>
            </div>

            <form method="POST" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="form-group">
                    <label class="form-label">Select User</label>
                    <select name="username" required class="form-input">
                        <option value="">-- Select a user --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= htmlspecialchars($user['username']) ?>">
                                <?= htmlspecialchars($user['display_name']) ?>
                                (<?= htmlspecialchars($user['username']) ?>)
                                - <?= ucfirst($user['role']) ?>
                                <?= !$user['is_active'] ? ' [INACTIVE]' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" required minlength="8"
                        class="form-input"
                        placeholder="Enter new password">
                </div>

                <button type="submit" class="btn-purple">
                    Reset Password
                </button>
            </form>
        </div>

        <!-- User List -->
        <div class="admin-card">
            <h3 class="admin-card-subtitle">User Accounts</h3>
            <div class="overflow-x-auto">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Display Name</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="username"><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['display_name']) ?></td>
                                <td>
                                    <span class="role-badge <?= $user['role'] === 'admin' ? 'admin' : 'employee' ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="<?= $user['is_active'] ? 'status-badge-active' : 'status-badge-inactive' ?>">
                                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
