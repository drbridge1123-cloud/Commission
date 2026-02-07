<?php
/**
 * Import Traffic Cases from CSV
 * Run this once to import data from the Master List Traffic Cases.csv
 */
require_once 'includes/auth.php';

// Only allow admin or Chong (user_id = 2)
if (!isLoggedIn()) {
    die('Unauthorized');
}

$user = getCurrentUser();
if ($user['id'] != 2 && !isAdmin()) {
    die('Access denied');
}

$pdo = getDB();
$chongUserId = 2; // Chong's user ID

// Data parsed from CSV
$activeCases = [
    ['name' => 'Sung, Hye Yeon', 'court' => 'Lynnwood Muni', 'date' => '2026-01-08 13:30:00', 'charge' => 'fail to obey traffic device', 'case_number' => '5A0780351', 'offer' => 'DDS1 and dismiss', 'disposition' => 'amended', 'status' => 'active'],
    ['name' => 'Kim, Jaewan', 'court' => 'KCDC - Issaquah', 'date' => '2026-01-09 08:45:00', 'charge' => 'phone while driving', 'case_number' => '5A0691463', 'offer' => '', 'disposition' => 'pending', 'status' => 'active'],
    ['name' => 'Kim, Christina', 'court' => 'Kent Municipal', 'date' => '2026-03-06 14:30:00', 'charge' => 'inattentive driving', 'case_number' => '5A0773205', 'offer' => '', 'disposition' => 'pending', 'status' => 'active'],
    ['name' => 'Kim, Su Jung', 'court' => 'Mill Creek Violation Bureau', 'date' => '2026-02-09 11:00:00', 'charge' => 'speeding', 'case_number' => '5A0909422', 'offer' => '', 'disposition' => 'pending', 'status' => 'active'],
    ['name' => 'Choi, Yongjun', 'court' => 'KCDC - Seattle', 'date' => null, 'charge' => '', 'case_number' => '5A0939864', 'offer' => '', 'disposition' => 'pending', 'status' => 'active'],
];

$resolvedCases = [
    ['name' => 'Han, Sang Ho', 'court' => 'SCD - Everett', 'date' => '2025-02-26 09:30:00', 'charge' => '', 'case_number' => '4A0737400', 'offer' => '', 'disposition' => 'dismissed'],
    ['name' => 'Yi, Ye Ja', 'court' => 'SCD - South', 'date' => '2025-02-27 09:00:00', 'charge' => '', 'case_number' => '4A0783305', 'offer' => '', 'disposition' => 'dismissed'],
    ['name' => 'Shim, In Sook', 'court' => 'SCD - Everett', 'date' => '2025-03-05 10:30:00', 'charge' => '', 'case_number' => '4A0722592', 'offer' => '', 'disposition' => 'dismissed'],
    ['name' => 'Lee, Jong Min', 'court' => 'SCD - Cascade', 'date' => '2025-03-17 14:00:00', 'charge' => '', 'case_number' => '4A0848334', 'offer' => '', 'disposition' => 'dismissed'],
    ['name' => 'Bae, Ashton', 'court' => 'KCDC - Issaquah', 'date' => '2025-03-21 10:15:00', 'charge' => '', 'case_number' => '4A0733565', 'offer' => '', 'disposition' => 'pending'],
    ['name' => 'Bin, Jang Dong', 'court' => 'SCD - Everett', 'date' => '2025-04-03 14:30:00', 'charge' => '', 'case_number' => '5A0163986', 'offer' => 'DDS1 and dismiss', 'disposition' => 'amended'],
    ['name' => 'Park, Chul Woo', 'court' => 'Brier Violation Bureau', 'date' => '2025-04-14 10:00:00', 'charge' => '', 'case_number' => '', 'offer' => '', 'disposition' => 'dismissed'],
    ['name' => 'Lee, Jay Hyung', 'court' => 'SCD - South', 'date' => '2025-04-15 10:30:00', 'charge' => '', 'case_number' => '', 'offer' => '', 'disposition' => 'dismissed'],
    ['name' => 'Kim, Jungeun', 'court' => 'SCD - South', 'date' => '2025-04-15 10:30:00', 'charge' => '', 'case_number' => '', 'offer' => '', 'disposition' => 'dismissed'],
    ['name' => 'Hong, Judy Juhee', 'court' => 'KCDC - Shoreline', 'date' => '2025-05-09 08:45:00', 'charge' => '', 'case_number' => '5A0076640', 'offer' => '', 'disposition' => 'dismissed'],
    ['name' => 'Jeong, Won Don', 'court' => 'KCDC - Shoreline', 'date' => '2025-05-23 13:15:00', 'charge' => '', 'case_number' => '', 'offer' => '', 'disposition' => 'dismissed'],
    ['name' => 'Yoon, Dahye', 'court' => 'KCDC - Bellevue', 'date' => '2025-05-23 13:15:00', 'charge' => '', 'case_number' => '5A0060579', 'offer' => '', 'disposition' => 'dismissed'],
    ['name' => 'Lim, Jeen', 'court' => 'KCDC - Issaquah', 'date' => '2025-06-25 08:45:00', 'charge' => '', 'case_number' => '5A0028458', 'offer' => '', 'disposition' => 'dismissed'],
    ['name' => 'Oh, Se C', 'court' => 'KCDC - Burien', 'date' => '2025-07-22 13:30:00', 'charge' => '', 'case_number' => '5A0075520', 'offer' => '', 'disposition' => 'dismissed'],
    ['name' => 'Jung, Anthony Ilho', 'court' => 'SCD - South', 'date' => '2025-07-31 09:00:00', 'charge' => '', 'case_number' => '5A0219123', 'offer' => '$150 amend to seatbelt', 'disposition' => 'amended'],
    ['name' => 'Jang, Junghwan', 'court' => 'Lynnwood Muni', 'date' => '2025-09-18 00:00:00', 'charge' => '', 'case_number' => '5A0547957', 'offer' => 'DDS2 and dismiss', 'disposition' => 'amended'],
    ['name' => 'Kim, Daniel', 'court' => 'SCD - Everett', 'date' => '2025-10-30 14:00:00', 'charge' => 'inattentive driving', 'case_number' => '5A0387416', 'offer' => 'DDS1 to dismiss', 'disposition' => 'amended'],
    ['name' => 'Kim, Jin Ho', 'court' => 'Issaquah Muni', 'date' => '2025-11-13 15:00:00', 'charge' => 'speeding', 'case_number' => '5A0693416', 'offer' => 'DDS1 to amend $139', 'disposition' => 'amended'],
    ['name' => 'Jung, Allicia', 'court' => 'KCDC - Shoreline', 'date' => '2025-11-21 08:45:00', 'charge' => 'speeding, hov', 'case_number' => '5A0656306', 'offer' => '', 'disposition' => 'dismissed'],
    ['name' => 'Moon, Yong Chul', 'court' => 'SCD - South', 'date' => '2025-12-04 09:00:00', 'charge' => 'speeding', 'case_number' => 'T00014027', 'offer' => '', 'disposition' => 'dismissed'],
    ['name' => 'Jeong, Won Don', 'court' => 'SCD - Evergreen', 'date' => '2025-12-08 09:30:00', 'charge' => 'speeding', 'case_number' => '5A0615047', 'offer' => '', 'disposition' => 'dismissed'],
    ['name' => 'Noh, Joo-Han', 'court' => 'SCD - Everett', 'date' => '2025-12-17 14:30:00', 'charge' => 'speeding', 'case_number' => '5A0785133', 'offer' => '', 'disposition' => 'dismissed'],
];

// Calculate commission
function getCommission($disposition) {
    if ($disposition === 'dismissed') return 150;
    if ($disposition === 'amended') return 100;
    return 0;
}

// Insert cases
$inserted = 0;
$errors = [];

$stmt = $pdo->prepare("
    INSERT INTO traffic_cases (user_id, client_name, court, court_date, charge, case_number, prosecutor_offer, disposition, commission, status, resolved_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// Insert active cases
foreach ($activeCases as $case) {
    try {
        $commission = getCommission($case['disposition']);
        $stmt->execute([
            $chongUserId,
            $case['name'],
            $case['court'],
            $case['date'],
            $case['charge'],
            $case['case_number'],
            $case['offer'],
            $case['disposition'],
            $commission,
            'active',
            null
        ]);
        $inserted++;
    } catch (Exception $e) {
        $errors[] = "Error inserting {$case['name']}: " . $e->getMessage();
    }
}

// Insert resolved cases
foreach ($resolvedCases as $case) {
    try {
        $commission = getCommission($case['disposition']);
        $stmt->execute([
            $chongUserId,
            $case['name'],
            $case['court'],
            $case['date'],
            $case['charge'],
            $case['case_number'],
            $case['offer'],
            $case['disposition'],
            $commission,
            'resolved',
            $case['date'] // resolved_at = court_date for resolved cases
        ]);
        $inserted++;
    } catch (Exception $e) {
        $errors[] = "Error inserting {$case['name']}: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Import Traffic Cases</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 40px; max-width: 800px; margin: 0 auto; }
        h1 { color: #0f4c81; }
        .success { background: #d1fae5; border: 1px solid #10b981; padding: 16px; border-radius: 8px; margin: 20px 0; }
        .error { background: #fee2e2; border: 1px solid #ef4444; padding: 16px; border-radius: 8px; margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #0f4c81; color: white; text-decoration: none; border-radius: 6px; margin-top: 20px; }
        .btn:hover { background: #1a5a96; }
    </style>
</head>
<body>
    <h1>Import Traffic Cases</h1>

    <?php if ($inserted > 0): ?>
    <div class="success">
        <strong>Success!</strong> Imported <?= $inserted ?> traffic cases.
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="error">
        <strong>Errors:</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <p>Active cases imported: <?= count($activeCases) ?></p>
    <p>Resolved cases imported: <?= count($resolvedCases) ?></p>
    <p>Total commission imported: $<?=
        array_sum(array_map(fn($c) => getCommission($c['disposition']), $activeCases)) +
        array_sum(array_map(fn($c) => getCommission($c['disposition']), $resolvedCases))
    ?></p>

    <a href="BridgeLaw.php" class="btn">Go to Dashboard</a>

    <p style="margin-top: 40px; color: #6b7280; font-size: 14px;">
        <strong>Note:</strong> This import script should only be run once. Delete this file after import to prevent duplicate entries.
    </p>
</body>
</html>
