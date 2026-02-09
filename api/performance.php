<?php
/**
 * Performance Analytics API
 * Owner-level analytics for employee performance evaluation
 * Admin only access
 */
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Require authentication
if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

// Admin only
if (!isAdmin()) {
    jsonResponse(['error' => 'Admin access required'], 403);
}

// Rate limiting
requireRateLimit('api_performance', 60, 60);

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// GET - Fetch analytics data
if ($method === 'GET') {
    $action = sanitizeString($_GET['action'] ?? 'summary', 50);
    $employeeId = intval($_GET['employee_id'] ?? 0);
    $month = sanitizeString($_GET['month'] ?? '', 10);
    $year = sanitizeString($_GET['year'] ?? date('Y'), 10);

    switch ($action) {
        case 'summary':
            // Overall summary across all employees or specific employee
            $result = getSummary($pdo, $employeeId, $year);
            break;

        case 'attorney':
        case 'chong': // backward compat
            $attorneyId = intval($_GET['attorney_id'] ?? 0);
            if ($attorneyId <= 0) {
                jsonResponse(['error' => 'attorney_id required'], 400);
            }
            $result = getAttorneyAnalytics($pdo, $attorneyId, $year, $month);
            break;

        case 'by_employee':
            // Performance breakdown by employee
            $result = getByEmployee($pdo, $year);
            break;

        case 'by_month':
            // Monthly trends
            $result = getByMonth($pdo, $employeeId, $year);
            break;

        case 'kpi':
            // Key Performance Indicators for specific employee
            $result = getKPIs($pdo, $employeeId, $month ?: date('Y-m'));
            break;

        case 'capacity':
            // Capacity analysis
            $result = getCapacityAnalysis($pdo, $employeeId);
            break;

        default:
            jsonResponse(['error' => 'Invalid action'], 400);
    }

    jsonResponse(array_merge($result, ['csrf_token' => generateCSRFToken()]));
}

// POST - Create performance snapshot
if ($method === 'POST') {
    requireCSRFToken();

    $data = json_decode(file_get_contents('php://input'), true);
    $action = sanitizeString($data['action'] ?? '', 50);

    if ($action === 'create_snapshot') {
        $employeeId = intval($data['employee_id'] ?? 0);
        $snapshotMonth = sanitizeString($data['month'] ?? date('Y-m'), 10);

        $result = createSnapshot($pdo, $employeeId, $snapshotMonth);
        jsonResponse($result);
    }

    jsonResponse(['error' => 'Invalid action'], 400);
}

// ============================================
// Analytics Functions
// ============================================

function getSummary($pdo, $employeeId, $year) {
    $params = [];
    $whereClause = "WHERE c.deleted_at IS NULL AND c.month LIKE ?";
    $params[] = "%. $year";

    if ($employeeId > 0) {
        $whereClause .= " AND c.user_id = ?";
        $params[] = $employeeId;
    }

    // Total cases and commission
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_cases,
            SUM(CASE WHEN c.status = 'paid' THEN 1 ELSE 0 END) as paid_cases,
            SUM(CASE WHEN c.status = 'unpaid' THEN 1 ELSE 0 END) as pending_cases,
            COALESCE(SUM(c.commission), 0) as total_commission,
            COALESCE(SUM(CASE WHEN c.status != 'rejected' THEN c.commission ELSE 0 END), 0) as valid_commission,
            COALESCE(AVG(CASE WHEN c.commission > 0 THEN c.commission END), 0) as avg_commission
        FROM cases c
        $whereClause
    ");
    $stmt->execute($params);
    $summary = $stmt->fetch();

    // This month vs last month comparison
    $currentMonth = date('M. Y');
    $lastMonthDate = strtotime('-1 month');
    $lastMonth = date('M. Y', $lastMonthDate);

    $stmt = $pdo->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN month = ? THEN commission ELSE 0 END), 0) as current_month,
            COALESCE(SUM(CASE WHEN month = ? THEN commission ELSE 0 END), 0) as last_month,
            COUNT(CASE WHEN month = ? THEN 1 END) as current_month_cases,
            COUNT(CASE WHEN month = ? THEN 1 END) as last_month_cases
        FROM cases
        WHERE deleted_at IS NULL AND status != 'rejected'
        " . ($employeeId > 0 ? "AND user_id = ?" : "")
    );

    $momParams = [$currentMonth, $lastMonth, $currentMonth, $lastMonth];
    if ($employeeId > 0) $momParams[] = $employeeId;
    $stmt->execute($momParams);
    $mom = $stmt->fetch();

    // Calculate MoM change
    $momChange = 0;
    if ($mom['last_month'] > 0) {
        $momChange = (($mom['current_month'] - $mom['last_month']) / $mom['last_month']) * 100;
    }

    return [
        'summary' => [
            'total_cases' => (int)$summary['total_cases'],
            'paid_cases' => (int)$summary['paid_cases'],
            'pending_cases' => (int)$summary['pending_cases'],
            'total_commission' => (float)$summary['total_commission'],
            'valid_commission' => (float)$summary['valid_commission'],
            'avg_commission' => round((float)$summary['avg_commission'], 2)
        ],
        'month_over_month' => [
            'current_month' => $currentMonth,
            'current_commission' => (float)$mom['current_month'],
            'current_cases' => (int)$mom['current_month_cases'],
            'last_month' => $lastMonth,
            'last_commission' => (float)$mom['last_month'],
            'last_cases' => (int)$mom['last_month_cases'],
            'change_percent' => round($momChange, 1)
        ],
        'year' => $year
    ];
}

function getAttorneyAnalytics($pdo, $attorneyId, $year, $month = '') {
    $whereClause = "WHERE c.user_id = ? AND c.deleted_at IS NULL";
    $params = [$attorneyId];

    if ($month) {
        $whereClause .= " AND c.month = ?";
        $params[] = $month;
    } else {
        $whereClause .= " AND c.month LIKE ?";
        $params[] = "%. $year";
    }

    // Phase breakdown
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_cases,
            SUM(CASE WHEN phase = 'demand' THEN 1 ELSE 0 END) as demand_active,
            SUM(CASE WHEN phase = 'litigation' THEN 1 ELSE 0 END) as litigation_active,
            SUM(CASE WHEN phase = 'settled' THEN 1 ELSE 0 END) as settled,
            SUM(CASE WHEN commission_type = 'demand_5pct' THEN 1 ELSE 0 END) as demand_settled,
            SUM(CASE WHEN commission_type LIKE 'litigation%' THEN 1 ELSE 0 END) as litigation_settled,
            COALESCE(SUM(commission), 0) as total_commission,
            COALESCE(SUM(CASE WHEN commission_type = 'demand_5pct' THEN commission ELSE 0 END), 0) as demand_commission,
            COALESCE(SUM(CASE WHEN commission_type LIKE 'litigation%' THEN commission ELSE 0 END), 0) as litigation_commission
        FROM cases c
        $whereClause
    ");
    $stmt->execute($params);
    $breakdown = $stmt->fetch();

    // Duration metrics (settled cases only)
    $stmt = $pdo->prepare("
        SELECT
            AVG(demand_duration_days) as avg_demand_days,
            AVG(litigation_duration_days) as avg_litigation_days,
            AVG(total_duration_days) as avg_total_days,
            MIN(demand_duration_days) as min_demand_days,
            MAX(demand_duration_days) as max_demand_days
        FROM cases
        WHERE user_id = ? AND deleted_at IS NULL AND phase = 'settled'
        AND demand_duration_days IS NOT NULL
    ");
    $stmt->execute([$attorneyId]);
    $duration = $stmt->fetch();

    // Deadline compliance
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_demand_settled,
            SUM(CASE WHEN demand_settled_date <= demand_deadline THEN 1 ELSE 0 END) as on_time,
            SUM(CASE WHEN demand_settled_date > demand_deadline THEN 1 ELSE 0 END) as overdue
        FROM cases
        WHERE user_id = ? AND deleted_at IS NULL
        AND demand_settled_date IS NOT NULL AND demand_deadline IS NOT NULL
    ");
    $stmt->execute([$attorneyId]);
    $compliance = $stmt->fetch();

    $complianceRate = 0;
    if ($compliance['total_demand_settled'] > 0) {
        $complianceRate = ($compliance['on_time'] / $compliance['total_demand_settled']) * 100;
    }

    // Resolution rate (settled in demand vs went to litigation)
    $demandResolutionRate = 0;
    $totalSettled = (int)$breakdown['demand_settled'] + (int)$breakdown['litigation_settled'];
    if ($totalSettled > 0) {
        $demandResolutionRate = ((int)$breakdown['demand_settled'] / $totalSettled) * 100;
    }

    // Current urgent cases
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as urgent
        FROM cases
        WHERE user_id = ? AND phase = 'demand' AND deleted_at IS NULL
        AND demand_deadline <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)
    ");
    $stmt->execute([$attorneyId]);
    $urgent = $stmt->fetch()['urgent'];

    return [
        'attorney_analytics' => [
            'phase_breakdown' => [
                'demand_active' => (int)$breakdown['demand_active'],
                'litigation_active' => (int)$breakdown['litigation_active'],
                'settled' => (int)$breakdown['settled']
            ],
            'settlement_breakdown' => [
                'demand_settled' => (int)$breakdown['demand_settled'],
                'litigation_settled' => (int)$breakdown['litigation_settled'],
                'demand_resolution_rate' => round($demandResolutionRate, 1)
            ],
            'commission_breakdown' => [
                'total' => (float)$breakdown['total_commission'],
                'from_demand' => (float)$breakdown['demand_commission'],
                'from_litigation' => (float)$breakdown['litigation_commission']
            ],
            'efficiency' => [
                'avg_demand_days' => round((float)$duration['avg_demand_days'], 1),
                'avg_litigation_days' => round((float)$duration['avg_litigation_days'], 1),
                'avg_total_days' => round((float)$duration['avg_total_days'], 1)
            ],
            'time_management' => [
                'deadline_compliance_rate' => round($complianceRate, 1),
                'on_time_count' => (int)$compliance['on_time'],
                'overdue_count' => (int)$compliance['overdue']
            ],
            'current_status' => [
                'urgent_cases' => (int)$urgent,
                'active_cases' => (int)$breakdown['demand_active'] + (int)$breakdown['litigation_active']
            ]
        ],
        'year' => $year,
        'month' => $month ?: 'all'
    ];
}

function getByEmployee($pdo, $year) {
    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.username,
            u.display_name,
            COUNT(c.id) as total_cases,
            SUM(CASE WHEN c.status = 'paid' THEN 1 ELSE 0 END) as paid_cases,
            COALESCE(SUM(c.commission), 0) as total_commission,
            COALESCE(AVG(CASE WHEN c.commission > 0 THEN c.commission END), 0) as avg_commission
        FROM users u
        LEFT JOIN cases c ON u.id = c.user_id AND c.deleted_at IS NULL AND c.month LIKE ?
        WHERE u.role = 'employee' AND u.is_active = 1
        GROUP BY u.id
        ORDER BY total_commission DESC
    ");
    $stmt->execute(["%. $year"]);
    $employees = $stmt->fetchAll();

    // Calculate percentages
    $totalCommission = array_sum(array_column($employees, 'total_commission'));
    foreach ($employees as &$emp) {
        $emp['commission_percent'] = $totalCommission > 0
            ? round(($emp['total_commission'] / $totalCommission) * 100, 1)
            : 0;
    }

    return [
        'by_employee' => $employees,
        'total_commission' => $totalCommission,
        'year' => $year
    ];
}

function getByMonth($pdo, $employeeId, $year) {
    $whereClause = "WHERE c.deleted_at IS NULL AND c.month LIKE ?";
    $params = ["%. $year"];

    if ($employeeId > 0) {
        $whereClause .= " AND c.user_id = ?";
        $params[] = $employeeId;
    }

    $stmt = $pdo->prepare("
        SELECT
            c.month,
            COUNT(*) as cases_count,
            COALESCE(SUM(c.commission), 0) as commission
        FROM cases c
        $whereClause AND c.status != 'rejected'
        GROUP BY c.month
        ORDER BY STR_TO_DATE(CONCAT('01 ', c.month), '%d %b. %Y')
    ");
    $stmt->execute($params);
    $monthly = $stmt->fetchAll();

    return [
        'by_month' => $monthly,
        'year' => $year,
        'employee_id' => $employeeId
    ];
}

function getKPIs($pdo, $employeeId, $month) {
    if ($employeeId <= 0) {
        return ['error' => 'Employee ID required'];
    }

    // Get employee info
    $stmt = $pdo->prepare("SELECT id, username, display_name FROM users WHERE id = ?");
    $stmt->execute([$employeeId]);
    $employee = $stmt->fetch();

    if (!$employee) {
        return ['error' => 'Employee not found'];
    }

    // Current month stats
    $currentMonthDisplay = date('M. Y', strtotime($month . '-01'));
    $lastMonthDisplay = date('M. Y', strtotime($month . '-01 -1 month'));

    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as cases,
            COALESCE(SUM(commission), 0) as commission
        FROM cases
        WHERE user_id = ? AND month = ? AND deleted_at IS NULL AND status != 'rejected'
    ");

    $stmt->execute([$employeeId, $currentMonthDisplay]);
    $current = $stmt->fetch();

    $stmt->execute([$employeeId, $lastMonthDisplay]);
    $last = $stmt->fetch();

    // Calculate changes
    $casesChange = (int)$current['cases'] - (int)$last['cases'];
    $commissionChange = (float)$current['commission'] - (float)$last['commission'];
    $commissionChangePercent = $last['commission'] > 0
        ? (($current['commission'] - $last['commission']) / $last['commission']) * 100
        : 0;

    return [
        'kpis' => [
            'employee' => $employee,
            'month' => $month,
            'current' => [
                'cases' => (int)$current['cases'],
                'commission' => (float)$current['commission']
            ],
            'previous' => [
                'cases' => (int)$last['cases'],
                'commission' => (float)$last['commission']
            ],
            'changes' => [
                'cases' => $casesChange,
                'commission' => $commissionChange,
                'commission_percent' => round($commissionChangePercent, 1)
            ]
        ]
    ];
}

function getCapacityAnalysis($pdo, $employeeId) {
    if ($employeeId <= 0) {
        return ['error' => 'Employee ID required'];
    }

    // Current active cases
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as active
        FROM cases
        WHERE user_id = ? AND deleted_at IS NULL
        AND (phase IN ('demand', 'litigation') OR status = 'unpaid')
    ");
    $stmt->execute([$employeeId]);
    $active = $stmt->fetch()['active'];

    // Historical max (by month)
    $stmt = $pdo->prepare("
        SELECT MAX(case_count) as max_concurrent
        FROM (
            SELECT month, COUNT(*) as case_count
            FROM cases
            WHERE user_id = ? AND deleted_at IS NULL
            GROUP BY month
        ) as monthly
    ");
    $stmt->execute([$employeeId]);
    $maxConcurrent = $stmt->fetch()['max_concurrent'] ?? 0;

    // Monthly average
    $stmt = $pdo->prepare("
        SELECT AVG(case_count) as avg_monthly
        FROM (
            SELECT month, COUNT(*) as case_count
            FROM cases
            WHERE user_id = ? AND deleted_at IS NULL AND status != 'rejected'
            GROUP BY month
        ) as monthly
    ");
    $stmt->execute([$employeeId]);
    $avgMonthly = $stmt->fetch()['avg_monthly'] ?? 0;

    // Capacity utilization
    $utilization = $maxConcurrent > 0 ? ($active / $maxConcurrent) * 100 : 0;

    // Capacity score (simplified version)
    // Based on: active cases vs historical max, recent performance
    $capacityScore = min(100, max(0, 100 - ($utilization - 80)));

    return [
        'capacity' => [
            'current_active' => (int)$active,
            'historical_max' => (int)$maxConcurrent,
            'avg_monthly' => round((float)$avgMonthly, 1),
            'utilization_percent' => round($utilization, 1),
            'capacity_score' => round($capacityScore, 0),
            'status' => $capacityScore >= 70 ? 'Good' : ($capacityScore >= 50 ? 'Average' : 'Needs Attention')
        ],
        'employee_id' => $employeeId
    ];
}

function createSnapshot($pdo, $employeeId, $snapshotMonth) {
    if ($employeeId <= 0) {
        return ['error' => 'Employee ID required'];
    }

    $monthDisplay = date('M. Y', strtotime($snapshotMonth . '-01'));

    // Gather all metrics
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as cases_settled,
            SUM(CASE WHEN commission_type = 'demand_5pct' THEN 1 ELSE 0 END) as demand_settled,
            SUM(CASE WHEN commission_type LIKE 'litigation%' THEN 1 ELSE 0 END) as litigation_settled,
            COALESCE(SUM(commission), 0) as total_commission,
            AVG(demand_duration_days) as avg_demand_days,
            AVG(litigation_duration_days) as avg_litigation_days,
            AVG(total_duration_days) as avg_total_days
        FROM cases
        WHERE user_id = ? AND month = ? AND deleted_at IS NULL AND status != 'rejected'
    ");
    $stmt->execute([$employeeId, $monthDisplay]);
    $metrics = $stmt->fetch();

    // Insert or update snapshot
    $stmt = $pdo->prepare("
        INSERT INTO performance_snapshots (
            employee_id, snapshot_month, cases_settled, demand_settled, litigation_settled,
            total_commission, avg_demand_days, avg_litigation_days, avg_total_days
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            cases_settled = VALUES(cases_settled),
            demand_settled = VALUES(demand_settled),
            litigation_settled = VALUES(litigation_settled),
            total_commission = VALUES(total_commission),
            avg_demand_days = VALUES(avg_demand_days),
            avg_litigation_days = VALUES(avg_litigation_days),
            avg_total_days = VALUES(avg_total_days)
    ");

    $stmt->execute([
        $employeeId,
        $snapshotMonth,
        $metrics['cases_settled'],
        $metrics['demand_settled'],
        $metrics['litigation_settled'],
        $metrics['total_commission'],
        $metrics['avg_demand_days'],
        $metrics['avg_litigation_days'],
        $metrics['avg_total_days']
    ]);

    return ['success' => true, 'month' => $snapshotMonth];
}
?>
