<?php
require_once 'includes/auth.php';

// Check if run from CLI or authorized admin session
if (php_sapi_name() !== 'cli' && !isAdmin()) {
    die('Unauthorized access');
}

$pdo = getDB();
$newPassword = '1234';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE 1");
    $stmt->execute([$hashedPassword]);
    echo "All user passwords have been reset to: $newPassword\n";
} catch (Exception $e) {
    echo "Error resetting passwords: " . $e->getMessage() . "\n";
}
?>
