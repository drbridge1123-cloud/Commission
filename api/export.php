<?php
/**
 * Export API
 * Exports case data to CSV format
 */
require_once '../includes/auth.php';

// Only allow GET requests for export
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Require login
if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

// Rate limiting for exports
requireRateLimit('export', 10, 60); // 10 exports per minute

$pdo = getDB();
$user = getCurrentUser();
$isAdmin = isAdmin();

// Get export parameters
$type = $_GET['type'] ?? 'all';
$status = $_GET['status'] ?? 'all';
$employee = $_GET['employee'] ?? 'all';
$month = $_GET['month'] ?? 'all';
$search = sanitizeString($_GET['search'] ?? '', 100);
$filter = $_GET['filter'] ?? 'all'; // For backward compatibility

try {
    // Build query based on user role
    if ($isAdmin) {
        $sql = "SELECT c.*, u.display_name as counsel_name
                FROM cases c
                JOIN users u ON c.user_id = u.id
                WHERE c.deleted_at IS NULL";
        $params = [];

        // Apply filters
        if ($status !== 'all') {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }

        if ($employee !== 'all') {
            $sql .= " AND u.display_name = ?";
            $params[] = $employee;
        }

        if ($month !== 'all') {
            $sql .= " AND c.month = ?";
            $params[] = $month;
        }

        if ($search) {
            $sql .= " AND (c.case_number LIKE ? OR c.client_name LIKE ? OR c.note LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        // Handle legacy filter parameter
        if ($type === 'history' || $filter !== 'all') {
            if ($filter === 'year' || ($type === 'history' && $filter === 'year')) {
                $sql .= " AND YEAR(c.reviewed_at) = YEAR(CURRENT_DATE)";
            } elseif ($filter === 'month' || ($type === 'history' && $filter === 'month')) {
                $sql .= " AND YEAR(c.reviewed_at) = YEAR(CURRENT_DATE) AND MONTH(c.reviewed_at) = MONTH(CURRENT_DATE)";
            }
        }

    } else {
        // Employee can only export their own data
        $sql = "SELECT c.*, u.display_name as counsel_name
                FROM cases c
                JOIN users u ON c.user_id = u.id
                WHERE c.user_id = ? AND c.deleted_at IS NULL";
        $params = [$user['id']];

        if ($status !== 'all') {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }

        if ($month !== 'all') {
            $sql .= " AND c.month = ?";
            $params[] = $month;
        }

        // Handle legacy filter
        if ($filter === 'year') {
            $sql .= " AND YEAR(c.reviewed_at) = YEAR(CURRENT_DATE)";
        } elseif ($filter === 'month') {
            $sql .= " AND YEAR(c.reviewed_at) = YEAR(CURRENT_DATE) AND MONTH(c.reviewed_at) = MONTH(CURRENT_DATE)";
        }
    }

    $sql .= " ORDER BY c.submitted_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cases = $stmt->fetchAll();

    if (empty($cases)) {
        // Return empty CSV with headers
        $cases = [];
    }

    // Log export action
    logAudit('export', 'cases', null, null, [
        'type' => $type,
        'status' => $status,
        'employee' => $employee,
        'month' => $month,
        'count' => count($cases)
    ]);

    // Generate CSV
    $filename = 'commission_export_' . date('Y-m-d_His') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // CSV Headers
    $headers = [
        'ID',
        'Employee',
        'Case Number',
        'Client Name',
        'Case Type',
        'Resolution Type',
        'Month',
        'Fee Rate',
        'Settled Amount',
        'Presuit Offer',
        'Difference',
        'Legal Fee',
        'Discounted Legal Fee',
        'Commission',
        'Status',
        'Check Received',
        'Note',
        'Submitted At',
        'Reviewed At'
    ];

    fputcsv($output, $headers);

    // Data rows
    foreach ($cases as $case) {
        $row = [
            $case['id'],
            $case['counsel_name'] ?? '',
            $case['case_number'],
            $case['client_name'],
            $case['case_type'] ?? '',
            $case['resolution_type'] ?? '',
            $case['month'],
            $case['fee_rate'] . '%',
            number_format($case['settled'], 2),
            number_format($case['presuit_offer'], 2),
            number_format($case['difference'], 2),
            number_format($case['legal_fee'], 2),
            number_format($case['discounted_legal_fee'], 2),
            number_format($case['commission'], 2),
            ucfirst($case['status']),
            $case['check_received'] ? 'Yes' : 'No',
            $case['note'] ?? '',
            $case['submitted_at'],
            $case['reviewed_at'] ?? ''
        ];

        fputcsv($output, $row);
    }

    // Add summary row
    if (!empty($cases)) {
        $totalSettled = array_sum(array_column($cases, 'settled'));
        $totalCommission = array_sum(array_column($cases, 'commission'));

        fputcsv($output, []); // Empty row
        fputcsv($output, ['SUMMARY']);
        fputcsv($output, ['Total Cases:', count($cases)]);
        fputcsv($output, ['Total Settled:', '$' . number_format($totalSettled, 2)]);
        fputcsv($output, ['Total Commission:', '$' . number_format($totalCommission, 2)]);
        fputcsv($output, ['Export Date:', date('Y-m-d H:i:s')]);
        fputcsv($output, ['Exported By:', $user['display_name']]);
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    error_log("Export error: " . $e->getMessage());
    jsonResponse(['error' => 'Export failed. Please try again.'], 500);
}
?>
