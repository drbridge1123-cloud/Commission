<?php
/**
 * Attorney Progress API
 * Read-only view of attorney's cases for managers/admin
 * Excludes all financial/commission data
 */
error_reporting(0);
ini_set('display_errors', 0);

require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

if (!isAdmin() && !isManager()) {
    jsonResponse(['error' => 'Access denied. Manager or admin access only.'], 403);
}

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$attorneyId = intval($_GET['attorney_id'] ?? 0);
if ($attorneyId <= 0) {
    jsonResponse(['error' => 'attorney_id parameter required'], 400);
}

$type = sanitizeString($_GET['type'] ?? '', 20);

if ($type === 'demand') {
    $stmt = $pdo->prepare("
        SELECT c.id, c.case_number, c.client_name, c.case_type,
               c.stage, c.assigned_date, c.demand_deadline,
               c.demand_out_date, c.negotiate_date,
               c.top_offer_date, c.top_offer_amount,
               c.status, c.note,
               DATEDIFF(c.demand_deadline, CURDATE()) as days_until_deadline
        FROM cases c
        WHERE c.user_id = ? AND c.phase = 'demand' AND c.deleted_at IS NULL
        ORDER BY
            CASE
                WHEN c.demand_deadline < CURDATE() AND c.top_offer_date IS NULL THEN 0
                WHEN c.demand_deadline <= DATE_ADD(CURDATE(), INTERVAL 14 DAY) AND c.top_offer_date IS NULL THEN 1
                WHEN c.assigned_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 2
                ELSE 3
            END ASC,
            c.demand_deadline ASC,
            c.submitted_at DESC
    ");
    $stmt->execute([$attorneyId]);
    $cases = $stmt->fetchAll();

    // Add deadline status
    foreach ($cases as &$case) {
        if ($case['demand_deadline']) {
            if (!empty($case['top_offer_date'])) {
                $case['deadline_status'] = [
                    'class' => 'deadline-complete',
                    'message' => 'Completed',
                    'days' => null,
                    'urgent' => false
                ];
            } else {
                $case['deadline_status'] = getDeadlineStatus($case['demand_deadline']);
            }
        } else {
            $case['deadline_status'] = null;
        }
    }

    jsonResponse(['cases' => $cases]);

} else if ($type === 'litigation') {
    $stmt = $pdo->prepare("
        SELECT c.id, c.case_number, c.client_name, c.case_type,
               c.resolution_type, c.litigation_start_date,
               c.status, c.note
        FROM cases c
        WHERE c.user_id = ? AND c.phase = 'litigation' AND c.deleted_at IS NULL
        ORDER BY c.litigation_start_date DESC
    ");
    $stmt->execute([$attorneyId]);
    $cases = $stmt->fetchAll();

    jsonResponse(['cases' => $cases]);

} else if ($type === 'traffic') {
    $stmt = $pdo->prepare("
        SELECT tc.id, tc.client_name, tc.case_number, tc.court,
               tc.charge, tc.court_date, tc.citation_issued_date,
               tc.noa_sent_date, tc.discovery, tc.disposition,
               tc.status, tc.referral_source
        FROM traffic_cases tc
        WHERE tc.user_id = ?
        ORDER BY tc.created_at DESC
    ");
    $stmt->execute([$attorneyId]);
    $cases = $stmt->fetchAll();

    jsonResponse(['cases' => $cases]);

} else if ($type === 'stats') {
    // Summary stats
    $stmt = $pdo->prepare("
        SELECT
            SUM(CASE WHEN phase = 'demand' THEN 1 ELSE 0 END) as demand_count,
            SUM(CASE WHEN phase = 'litigation' THEN 1 ELSE 0 END) as litigation_count
        FROM cases
        WHERE user_id = ? AND deleted_at IS NULL AND phase IN ('demand', 'litigation')
    ");
    $stmt->execute([$attorneyId]);
    $caseCounts = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as traffic_count
        FROM traffic_cases
        WHERE user_id = ? AND status = 'active'
    ");
    $stmt->execute([$attorneyId]);
    $trafficCount = $stmt->fetch()['traffic_count'];

    jsonResponse([
        'demand_count' => (int)($caseCounts['demand_count'] ?? 0),
        'litigation_count' => (int)($caseCounts['litigation_count'] ?? 0),
        'traffic_count' => (int)$trafficCount
    ]);

} else {
    jsonResponse(['error' => 'Invalid type. Use: demand, litigation, traffic, or stats'], 400);
}
