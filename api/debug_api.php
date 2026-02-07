<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Starting debug...\n";

try {
    require_once '../includes/auth.php';
    echo "Auth included.\n";
    require_once '../includes/functions.php';
    echo "Functions included.\n";

    $pdo = getDB();
    echo "DB Connected.\n";

    $sql = "SELECT * FROM cases LIMIT 1";
    $stmt = $pdo->query($sql);
    $case = $stmt->fetch();
    echo "Query executed. Case ID: " . ($case['id'] ?? 'None') . "\n";

} catch (Throwable $t) {
    echo "Error: " . $t->getMessage() . "\n";
    echo "Trace: " . $t->getTraceAsString() . "\n";
}
?>