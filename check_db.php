<?php
/**
 * Database Backup & Restore Page
 * Admin-only page for database management
 */
require_once 'includes/auth.php';
requireAdmin();

$user = getCurrentUser();
$message = null;
$messageType = null;

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please refresh and try again.';
        $messageType = 'error';
    } else {
        $action = $_POST['action'];
        $pdo = getDB();

        if ($action === 'backup') {
            try {
                // Get ALL tables dynamically
                $tableStmt = $pdo->query("SHOW TABLES");
                $tables = $tableStmt->fetchAll(PDO::FETCH_COLUMN);
                $backup = "-- Commission DB Backup\n";
                $backup .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
                $backup .= "-- Database: " . DB_NAME . "\n\n";
                $backup .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

                foreach ($tables as $table) {
                    $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
                    $row = $stmt->fetch(PDO::FETCH_NUM);
                    $backup .= "DROP TABLE IF EXISTS `$table`;\n";
                    $backup .= $row[1] . ";\n\n";

                    $stmt = $pdo->query("SELECT * FROM `$table`");
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($rows) > 0) {
                        $columns = array_keys($rows[0]);
                        $columnList = '`' . implode('`, `', $columns) . '`';

                        foreach ($rows as $row) {
                            $values = array_map(function($val) use ($pdo) {
                                if ($val === null) return 'NULL';
                                return $pdo->quote($val);
                            }, array_values($row));
                            $backup .= "INSERT INTO `$table` ($columnList) VALUES (" . implode(', ', $values) . ");\n";
                        }
                        $backup .= "\n";
                    }
                }

                $backup .= "SET FOREIGN_KEY_CHECKS=1;\n";

                // Log backup action
                logAudit('database_backup', null, null, null, ['timestamp' => date('Y-m-d H:i:s')]);

                $filename = 'commission_backup_' . date('Y-m-d_His') . '.sql';
                header('Content-Type: application/sql');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . strlen($backup));
                echo $backup;
                exit;

            } catch (Exception $e) {
                $message = 'Backup failed: ' . $e->getMessage();
                $messageType = 'error';
            }
        }

        if ($action === 'restore' && isset($_FILES['backup_file'])) {
            $file = $_FILES['backup_file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $message = 'File upload failed.';
                $messageType = 'error';
            } elseif ($file['size'] > 10 * 1024 * 1024) {
                $message = 'File too large. Maximum 10MB.';
                $messageType = 'error';
            } else {
                $sql = file_get_contents($file['tmp_name']);

                if (strpos($sql, '-- Commission DB Backup') === false) {
                    $message = 'Invalid backup file. Please use a file created by this system.';
                    $messageType = 'error';
                } else {
                    try {
                        $pdo->exec($sql);
                        $message = 'Database restored successfully!';
                        $messageType = 'success';
                        logAudit('database_restore', null, null, null, ['filename' => $file['name']]);
                    } catch (Exception $e) {
                        $message = 'Restore failed: ' . $e->getMessage();
                        $messageType = 'error';
                    }
                }
            }
        }

        // Clean old audit logs (30+ days)
        if ($action === 'clean_logs') {
            try {
                $stmt = $pdo->prepare("DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
                $stmt->execute();
                $deleted = $stmt->rowCount();
                $message = "Deleted $deleted old audit log(s).";
                $messageType = 'success';
                logAudit('clean_audit_logs', null, null, null, ['deleted_count' => $deleted]);
            } catch (Exception $e) {
                $message = 'Failed to clean logs: ' . $e->getMessage();
                $messageType = 'error';
            }
        }

        // Optimize tables
        if ($action === 'optimize_tables') {
            try {
                $tableStmt = $pdo->query("SHOW TABLES");
                $tables = $tableStmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($tables as $table) {
                    $pdo->exec("OPTIMIZE TABLE `$table`");
                }
                $message = 'All tables optimized successfully!';
                $messageType = 'success';
                logAudit('optimize_tables', null, null, null, []);
            } catch (Exception $e) {
                $message = 'Failed to optimize tables: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Get database info
$dbStatus = [];
$recentActivity = [];
$dbError = null;
try {
    $pdo = getDB();
    $dbStatus['connection'] = true;

    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $dbStatus['database'] = $stmt->fetch()['db_name'];

    $stmt = $pdo->query("SELECT VERSION() as version");
    $dbStatus['mysql_version'] = $stmt->fetch()['version'];

    // Get table counts
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $dbStatus['tables'] = [];
    $totalRows = 0;

    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
        $count = $stmt->fetch()['count'];
        $dbStatus['tables'][$table] = $count;
        $totalRows += $count;
    }
    $dbStatus['total_rows'] = $totalRows;

    // Recent Activity
    // Last case
    $stmt = $pdo->query("SELECT case_number, client_name, submitted_at as created_at FROM cases ORDER BY submitted_at DESC LIMIT 1");
    $recentActivity['last_case'] = $stmt->fetch();

    // Last login (from audit logs)
    $stmt = $pdo->query("SELECT u.display_name, a.created_at FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id WHERE a.action = 'login' ORDER BY a.created_at DESC LIMIT 1");
    $recentActivity['last_login'] = $stmt->fetch();

    // Last backup
    $stmt = $pdo->query("SELECT created_at FROM audit_logs WHERE action = 'database_backup' ORDER BY created_at DESC LIMIT 1");
    $recentActivity['last_backup'] = $stmt->fetch();

    // Old logs count (30+ days)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $dbStatus['old_logs_count'] = $stmt->fetch()['count'];

} catch (Exception $e) {
    $dbStatus['connection'] = false;
    $dbError = $e->getMessage();
}

$csrfToken = generateCSRFToken();

// Helper function for time ago
function timeAgo($datetime) {
    if (!$datetime) return 'Never';
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M j, Y', $time);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; background: #f0f1f3; padding: 20px; color: #3d3f4e; }
        .container { max-width: 900px; margin: 0 auto; }

        /* Quick Stats */
        .qs-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px; }
        .qs-card { background: #fff; border-radius: 8px; padding: 14px 16px; border: 1px solid #e2e4ea; }
        .qs-label { font-size: 10px; color: #8b8fa3; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
        .qs-val { font-size: 18px; font-weight: 700; color: #1a1a2e; margin-top: 4px; }
        .qs-val.green { color: #0d9488; }
        .qs-val.blue { color: #3b82f6; }
        .qs-val.amber { color: #d97706; }

        /* Cards */
        .card { background: #fff; border-radius: 10px; border: 1px solid #e2e4ea; margin-bottom: 16px; overflow: hidden; }
        .card-header { background: #1a1a2e; color: #fff; padding: 12px 16px; font-size: 13px; font-weight: 600; }
        .card-body { padding: 16px; }

        /* Grid */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        /* List Items */
        .list-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f0f1f3; }
        .list-item:last-child { border-bottom: none; }
        .list-title { font-size: 13px; font-weight: 600; color: #1a1a2e; }
        .list-sub { font-size: 11px; color: #8b8fa3; margin-top: 2px; }
        .list-time { font-size: 11px; color: #8b8fa3; }

        /* Badges */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-red { background: #fee2e2; color: #991b1b; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; font-family: 'Outfit', sans-serif; transition: all 0.12s; }
        .btn-primary { background: #1a1a2e; color: #fff; } .btn-primary:hover { background: #2d2d4a; }
        .btn-success { background: #0d9488; color: #fff; } .btn-success:hover { background: #0f766e; }
        .btn-danger { background: #dc2626; color: #fff; } .btn-danger:hover { background: #b91c1c; }
        .btn-secondary { background: #fff; color: #5c5f73; border: 1px solid #e2e4ea; } .btn-secondary:hover { background: #f5f5f7; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Alert */
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

        /* Form */
        .form-input { width: 100%; padding: 8px 12px; border: 1px solid #e2e4ea; border-radius: 6px; font-size: 12px; font-family: 'Outfit', sans-serif; }
        .form-input:focus { outline: none; border-color: #1a1a2e; }

        /* Table Badge */
        .tbl-badge { display: inline-block; padding: 2px 8px; background: #f0f1f3; border-radius: 4px; font-size: 11px; font-weight: 600; color: #5c5f73; }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($message): ?>
        <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div class="qs-grid">
            <div class="qs-card">
                <div class="qs-label">Status</div>
                <div class="qs-val <?= $dbStatus['connection'] ? 'green' : '' ?>"><?= $dbStatus['connection'] ? 'Connected' : 'Error' ?></div>
            </div>
            <div class="qs-card">
                <div class="qs-label">Tables</div>
                <div class="qs-val"><?= $dbStatus['connection'] ? count($dbStatus['tables']) : '-' ?></div>
            </div>
            <div class="qs-card">
                <div class="qs-label">Total Rows</div>
                <div class="qs-val blue"><?= $dbStatus['connection'] ? number_format($dbStatus['total_rows']) : '-' ?></div>
            </div>
            <div class="qs-card">
                <div class="qs-label">Old Logs</div>
                <div class="qs-val amber"><?= $dbStatus['connection'] ? number_format($dbStatus['old_logs_count']) : '-' ?></div>
            </div>
        </div>

        <?php if ($dbStatus['connection']): ?>
        <!-- Backup & Restore -->
        <div class="grid-2">
            <div class="card">
                <div class="card-header">Backup Database</div>
                <div class="card-body">
                    <p style="font-size: 12px; color: #8b8fa3; margin-bottom: 12px;">Download a complete backup of all tables and data.</p>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="action" value="backup">
                        <button type="submit" class="btn btn-success">Download Backup</button>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Restore Database</div>
                <div class="card-body">
                    <div class="alert alert-warning" style="margin-bottom: 12px;">
                        <strong>Warning:</strong> This will overwrite all current data!
                    </div>
                    <form method="POST" enctype="multipart/form-data" onsubmit="return validateRestore();">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="action" value="restore">
                        <input type="file" name="backup_file" accept=".sql" id="backupFile" class="form-input" style="margin-bottom: 12px;">
                        <button type="submit" class="btn btn-danger">Restore from Backup</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">Recent Activity</div>
            <div class="card-body">
                <div class="list-item">
                    <div>
                        <div class="list-title">Last Case Added</div>
                        <div class="list-sub">
                            <?php if ($recentActivity['last_case']): ?>
                                <?= htmlspecialchars($recentActivity['last_case']['case_number']) ?> - <?= htmlspecialchars($recentActivity['last_case']['client_name']) ?>
                            <?php else: ?>
                                No cases yet
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="list-time"><?= $recentActivity['last_case'] ? timeAgo($recentActivity['last_case']['created_at']) : '' ?></span>
                </div>
                <div class="list-item">
                    <div>
                        <div class="list-title">Last Login</div>
                        <div class="list-sub"><?= $recentActivity['last_login'] ? htmlspecialchars($recentActivity['last_login']['display_name']) : 'No login records' ?></div>
                    </div>
                    <span class="list-time"><?= $recentActivity['last_login'] ? timeAgo($recentActivity['last_login']['created_at']) : '' ?></span>
                </div>
                <div class="list-item">
                    <div>
                        <div class="list-title">Last Backup</div>
                        <div class="list-sub"><?= $recentActivity['last_backup'] ? 'Backup completed' : '<span style="color:#dc2626;">Never - Please backup now!</span>' ?></div>
                    </div>
                    <span class="list-time"><?= $recentActivity['last_backup'] ? timeAgo($recentActivity['last_backup']['created_at']) : '' ?></span>
                </div>
            </div>
        </div>

        <!-- Tables Info -->
        <div class="card">
            <div class="card-header">Database Tables</div>
            <div class="card-body">
                <?php foreach ($dbStatus['tables'] as $table => $count): ?>
                <div class="list-item">
                    <span class="list-title"><?= htmlspecialchars($table) ?></span>
                    <span class="tbl-badge"><?= number_format($count) ?> rows</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Data Management -->
        <div class="card">
            <div class="card-header">Data Management</div>
            <div class="card-body">
                <div class="list-item">
                    <div>
                        <div class="list-title">Clean Old Audit Logs</div>
                        <div class="list-sub">Delete audit logs older than 30 days</div>
                    </div>
                    <form method="POST" style="margin: 0;" onsubmit="return confirm('Delete all audit logs older than 30 days?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="action" value="clean_logs">
                        <button type="submit" class="btn btn-danger" <?= $dbStatus['old_logs_count'] == 0 ? 'disabled' : '' ?>>Delete (<?= $dbStatus['old_logs_count'] ?>)</button>
                    </form>
                </div>
                <div class="list-item">
                    <div>
                        <div class="list-title">Optimize Tables</div>
                        <div class="list-sub">Reclaim unused space and defragment data</div>
                    </div>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="action" value="optimize_tables">
                        <button type="submit" class="btn btn-secondary">Optimize</button>
                    </form>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-header">Connection Error</div>
            <div class="card-body">
                <div class="alert alert-error">
                    <?= htmlspecialchars($dbError ?? 'Unable to connect to database') ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function validateRestore() {
            const fileInput = document.getElementById('backupFile');
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('Please select a backup file first.');
                return false;
            }
            return confirm('Are you sure you want to restore? This will overwrite ALL current data!');
        }
    </script>
</body>
</html>
