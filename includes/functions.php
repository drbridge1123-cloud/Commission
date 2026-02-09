<?php
/**
 * Common Functions
 * Shared utility functions used across the application
 */

require_once __DIR__ . '/constants.php';

// ============================================
// Commission Calculation Functions
// ============================================

/**
 * Calculate legal fee based on settlement and fee rate
 *
 * @param float $base The base amount (settled - presuit_offer or just settled)
 * @param float $feeRate The fee rate percentage (33.33 or 40)
 * @return float The calculated legal fee
 */
function calculateLegalFee($base, $feeRate) {
    if ($base <= 0) {
        return 0;
    }

    // Standard rate uses exact 1/3 calculation
    if ($feeRate == FEE_RATE_STANDARD) {
        return round($base / 3, 2);
    }

    // Other rates (like 40%) use percentage calculation
    return round($base * ($feeRate / 100), 2);
}

/**
 * Calculate commission amount
 *
 * @param float $legalFee The legal fee (or discounted legal fee if applicable)
 * @param float $commissionRate The commission rate percentage
 * @return float The calculated commission
 */
function calculateCommission($legalFee, $commissionRate) {
    if ($legalFee <= 0 || $commissionRate <= 0) {
        return 0;
    }

    return round(($legalFee * $commissionRate) / 100, 2);
}

/**
 * Calculate all case financials at once
 *
 * @param float $settled Settlement amount
 * @param float $presuitOffer Pre-suit offer amount
 * @param float $feeRate Fee rate percentage
 * @param float $commissionRate Commission rate percentage
 * @param bool $usesPresuitOffer Whether to use pre-suit offer in calculation
 * @return array Array containing all calculated values
 */
function calculateCaseFinancials($settled, $presuitOffer, $feeRate, $commissionRate, $usesPresuitOffer = true) {
    // Calculate difference (base for legal fee)
    $difference = $usesPresuitOffer ? max(0, $settled - $presuitOffer) : $settled;

    // Calculate legal fee
    $legalFee = calculateLegalFee($settled, $feeRate);

    // Calculate discounted legal fee (based on difference)
    $discountedLegalFee = calculateLegalFee($difference, $feeRate);

    // Calculate commission (based on discounted legal fee if using presuit offer)
    $commissionBase = $usesPresuitOffer ? $discountedLegalFee : $legalFee;
    $commission = calculateCommission($commissionBase, $commissionRate);

    return [
        'difference' => $difference,
        'legal_fee' => $legalFee,
        'discounted_legal_fee' => $discountedLegalFee,
        'commission' => $commission
    ];
}

/**
 * Calculate Chong-specific commission
 * Chong has unique commission rules based on phase and resolution type
 *
 * @param string $phase 'demand' or 'litigation'
 * @param string $resolutionType The resolution type for litigation cases
 * @param float $settled Settlement amount
 * @param float $presuitOffer Pre-suit offer amount
 * @param float $discountedLegalFee The discounted legal fee
 * @param float $manualCommissionRate Optional manual commission rate for variable types
 * @param float $manualFeeRate Optional manual fee rate for variable types
 * @param float|null $overrideFeeRate Optional fee rate override (e.g. 33.33→40 or 40→33.33)
 * @return array Commission calculation result with all details
 */
function calculateChongCommission($phase, $resolutionType, $settled, $presuitOffer, $discountedLegalFee, $manualCommissionRate = 0, $manualFeeRate = 0, $overrideFeeRate = null) {
    // Default result structure
    $result = [
        'commission' => 0,
        'commission_type' => '',
        'commission_rate' => 0,
        'fee_rate' => 0,
        'difference' => 0,
        'legal_fee' => 0,
        'description' => ''
    ];

    // ── DEMAND Phase ──
    // Demand has NO Pre-Suit Offer, Commission = Discounted Legal Fee x 5%
    // Legal Fee = Settled x 1/3 (33.33%)
    if ($phase === 'demand' || in_array($resolutionType, ['Demand Settle', 'Demand Settled'])) {
        $legalFee = round($settled / 3, 2);
        $result['commission'] = round($discountedLegalFee * 0.05, 2);
        $result['commission_type'] = 'demand_5pct';
        $result['commission_rate'] = 5;
        $result['fee_rate'] = 33.33;
        $result['difference'] = 0;
        $result['legal_fee'] = $legalFee;
        $result['description'] = 'Demand (5% of Disc. Legal Fee)';
        return $result;
    }

    // ── FEE RATE OVERRIDE ──
    // When user manually overrides the fee rate (e.g. 33.33% → 40% or vice versa)
    // Keep original resolution type's deduction behavior
    if ($overrideFeeRate !== null && $phase === 'litigation') {
        $difference = $settled - $presuitOffer;
        $presuitDeductedGroup = ['No Offer Settle', 'File and Bump', 'Post Deposition Settle', 'Mediation', 'Settled Post Arbitration', 'Settlement Conference'];
        $base = in_array($resolutionType, $presuitDeductedGroup) ? $difference : $settled;

        if ($overrideFeeRate == 40) {
            $legalFee = round($base * 0.40, 2);
        } else {
            $legalFee = round($base / 3, 2);
        }
        $result['commission'] = round($discountedLegalFee * 0.20, 2);
        $result['commission_type'] = 'litigation_override';
        $result['commission_rate'] = 20;
        $result['fee_rate'] = $overrideFeeRate;
        $result['difference'] = $difference;
        $result['legal_fee'] = $legalFee;
        $result['description'] = "Litigation {$overrideFeeRate}% (Override, 20% of Disc. Legal Fee)";
        return $result;
    }

    // ── LITIGATION: 40% Group (No Pre-Suit Offer deduction) ──
    // Arbitration Award, Beasley
    $fortyPctGroup = ['Arbitration Award', 'Beasley'];
    if (in_array($resolutionType, $fortyPctGroup)) {
        $legalFee = round($settled * 0.40, 2);
        $result['commission'] = round($discountedLegalFee * 0.20, 2);
        $result['commission_type'] = 'litigation_40pct';
        $result['commission_rate'] = 20;
        $result['fee_rate'] = 40;
        $result['difference'] = $settled - $presuitOffer; // Reference only
        $result['legal_fee'] = $legalFee;
        $result['description'] = 'Litigation 40% (20% of Disc. Legal Fee)';
        return $result;
    }

    // ── LITIGATION: 33.3333% Group ──
    // File and Bump, Post Deposition Settle, Mediation, Settled Post Arbitration, Settlement Conference
    // Legal Fee = (Settled - PreSuit) / 3
    $thirtyThreePctGroup = [
        'No Offer Settle',
        'File and Bump',
        'Post Deposition Settle',
        'Mediation',
        'Settled Post Arbitration',
        'Settlement Conference'
    ];
    if (in_array($resolutionType, $thirtyThreePctGroup)) {
        $difference = $settled - $presuitOffer;
        $legalFee = round($difference / 3, 2);  // Legal Fee based on Difference
        $result['commission'] = round($discountedLegalFee * 0.20, 2);
        $result['commission_type'] = 'litigation_33pct';
        $result['commission_rate'] = 20;
        $result['fee_rate'] = 33.33;
        $result['difference'] = $difference;
        $result['legal_fee'] = $legalFee;
        $result['description'] = 'Litigation 33.33% (20% of Disc. Legal Fee)';
        return $result;
    }

    // ── VARIABLE Group (Manual input) ──
    // Co-Counsel, Other - All manual entry
    $variableGroup = ['Co-Counsel', 'Other'];
    if (in_array($resolutionType, $variableGroup) || $manualCommissionRate > 0) {
        $difference = $settled - $presuitOffer;
        $legalFee = $manualFeeRate > 0 ? round($settled * ($manualFeeRate / 100), 2) : 0;
        $commission = $manualCommissionRate > 0 ? round($discountedLegalFee * ($manualCommissionRate / 100), 2) : 0;

        $result['commission'] = $commission;
        $result['commission_type'] = 'variable';
        $result['commission_rate'] = $manualCommissionRate;
        $result['fee_rate'] = $manualFeeRate;
        $result['difference'] = $difference;
        $result['legal_fee'] = $legalFee;
        $result['description'] = 'Variable (Manual Entry)';
        return $result;
    }

    // ── Default fallback ──
    $result['commission_type'] = 'unknown';
    $result['description'] = 'Unknown commission type';
    return $result;
}

/**
 * Get Chong's resolution types grouped by fee structure
 *
 * @return array Array of resolution types by group
 */
function getChongResolutionTypes() {
    return [
        'demand' => ['Demand Settle'],
        'litigation_33pct' => [
            'No Offer Settle',
            'File and Bump',
            'Post Deposition Settle',
            'Mediation',
            'Settled Post Arbitration',
            'Settlement Conference'
        ],
        'litigation_40pct' => [
            'Arbitration Award',
            'Beasley'
        ],
        'variable' => [
            'Co-Counsel',
            'Other'
        ]
    ];
}

/**
 * Calculate days between two dates
 *
 * @param string $startDate Start date
 * @param string $endDate End date (defaults to today)
 * @return int Number of days
 */
function calculateDaysBetween($startDate, $endDate = null) {
    if (empty($startDate)) {
        return 0;
    }

    $start = new DateTime($startDate);
    $end = $endDate ? new DateTime($endDate) : new DateTime();

    return $start->diff($end)->days;
}

/**
 * Calculate demand deadline (assigned_date + 90 days)
 *
 * @param string $assignedDate The assigned date
 * @return string The deadline date in Y-m-d format
 */
function calculateDemandDeadline($assignedDate) {
    $date = new DateTime($assignedDate);
    $date->add(new DateInterval('P90D')); // Add 90 days (3 months)
    return $date->format('Y-m-d');
}

/**
 * Get deadline status based on days remaining
 *
 * @param string $deadline The deadline date
 * @return array Status info with class and message
 */
function getDeadlineStatus($deadline) {
    if (empty($deadline)) {
        return ['class' => '', 'message' => '', 'days' => null, 'urgent' => false];
    }

    $today = new DateTime();
    $deadlineDate = new DateTime($deadline);
    $diff = $today->diff($deadlineDate);
    $days = $diff->invert ? -$diff->days : $diff->days;

    if ($days < 0) {
        return [
            'class' => 'deadline-overdue',
            'message' => abs($days) . ' days overdue',
            'days' => $days,
            'urgent' => true
        ];
    } elseif ($days <= 14) {
        return [
            'class' => 'deadline-critical',
            'message' => $days . ' days left',
            'days' => $days,
            'urgent' => true
        ];
    } elseif ($days <= 30) {
        return [
            'class' => 'deadline-warning',
            'message' => $days . ' days left',
            'days' => $days,
            'urgent' => false
        ];
    } else {
        return [
            'class' => 'deadline-safe',
            'message' => $days . ' days left',
            'days' => $days,
            'urgent' => false
        ];
    }
}

/**
 * Calculate automatic status based on case conditions
 *
 * Status Logic:
 * - in_progress: Case created, not yet settled
 * - pending: Case settled (has settlement amount), waiting for commission payment
 * - paid: Commission received (check_received = true)
 * - rejected: Admin manually sets (pay structure changed)
 *
 * @param float $settled Settlement amount
 * @param bool $checkReceived Whether commission check was received
 * @param string|null $currentStatus Current status (for preserving 'rejected')
 * @return string The calculated status
 */
function calculateAutoStatus($settled, $checkReceived, $currentStatus = null) {
    // Preserve 'rejected' status - only admin can set/unset this
    if ($currentStatus === 'rejected') {
        return 'rejected';
    }

    // If already paid (admin approved), stay paid
    if ($currentStatus === 'paid') {
        return 'paid';
    }

    // check_received is just a flag - does NOT change status to paid
    // Only admin approval (api/approve.php) can set status to 'paid'

    // Has settlement = unpaid (waiting for admin approval)
    if ($settled > 0) {
        return 'unpaid';
    }

    // Default = in_progress (working on case)
    return 'in_progress';
}

// ============================================
// Formatting Functions
// ============================================

/**
 * Format currency for display
 *
 * @param float $amount The amount to format
 * @param string $prefix Currency prefix (default: $)
 * @return string Formatted currency string
 */
function formatCurrency($amount, $prefix = '$') {
    return $prefix . number_format((float)$amount, 2);
}

/**
 * Format percentage for display
 *
 * @param float $value The percentage value
 * @param int $decimals Number of decimal places
 * @return string Formatted percentage string
 */
function formatPercentage($value, $decimals = 2) {
    return number_format((float)$value, $decimals) . '%';
}

/**
 * Format date for display
 *
 * @param string $date The date string
 * @param string $format The output format (default: DATE_FORMAT_DISPLAY)
 * @return string Formatted date string
 */
function formatDate($date, $format = null) {
    if (empty($date)) {
        return '';
    }

    $format = $format ?? DATE_FORMAT_DISPLAY;
    $timestamp = strtotime($date);

    return $timestamp ? date($format, $timestamp) : '';
}

// ============================================
// Status Helper Functions
// ============================================

/**
 * Get status badge HTML class
 *
 * @param string $status The status value
 * @return string CSS class for the status badge
 */
function getStatusClass($status) {
    switch ($status) {
        case CASE_STATUS_PAID:
            return 'status-paid';
        case CASE_STATUS_REJECTED:
            return 'status-rejected';
        case CASE_STATUS_PENDING:
        default:
            return 'status-pending';
    }
}

/**
 * Get status display text
 *
 * @param string $status The status value
 * @return string Display text for the status
 */
function getStatusText($status) {
    return CASE_STATUSES[$status] ?? ucfirst($status);
}

// ============================================
// Month Helper Functions
// ============================================

/**
 * Get array of month options for dropdowns
 * Returns months from Jan 2024 to Dec of next year
 *
 * @return array Array of month strings
 */
function getMonthOptions() {
    $months = [];
    $startYear = 2024;
    $endYear = (int)date('Y') + 1;

    for ($year = $startYear; $year <= $endYear; $year++) {
        for ($month = 1; $month <= 12; $month++) {
            $months[] = date('M. Y', mktime(0, 0, 0, $month, 1, $year));
        }
    }

    return $months;
}

/**
 * Get current month in display format
 *
 * @return string Current month (e.g., "Dec. 2025")
 */
function getCurrentMonth() {
    return date(DATE_FORMAT_DISPLAY);
}

// ============================================
// Validation Functions
// ============================================

/**
 * Validate fee rate value
 *
 * @param float $feeRate The fee rate to validate
 * @return bool True if valid
 */
function isValidFeeRate($feeRate) {
    return in_array($feeRate, [FEE_RATE_STANDARD, FEE_RATE_PREMIUM]);
}

/**
 * Validate commission rate value
 *
 * @param float $commissionRate The commission rate to validate
 * @return bool True if valid
 */
function isValidCommissionRate($commissionRate) {
    return $commissionRate >= COMMISSION_RATE_MIN && $commissionRate <= COMMISSION_RATE_MAX;
}

/**
 * Validate settlement amount
 *
 * @param float $amount The amount to validate
 * @return bool True if valid
 */
function isValidSettlementAmount($amount) {
    return $amount >= MIN_SETTLED_AMOUNT && $amount <= MAX_SETTLED_AMOUNT;
}

// ============================================
// Array/Data Helper Functions
// ============================================

/**
 * Safe array get with default value
 *
 * @param array $array The array to search
 * @param string $key The key to look for
 * @param mixed $default Default value if key not found
 * @return mixed The value or default
 */
function arrayGet($array, $key, $default = null) {
    return $array[$key] ?? $default;
}

/**
 * Generate temporary password
 *
 * @return string Generated temporary password
 */
function generateTempPassword() {
    return bin2hex(random_bytes(TEMP_PASSWORD_LENGTH / 2));
}

/**
 * Get employee IDs managed by a manager user
 * Uses manager_team table set via Admin Control
 *
 * @param int $managerId The manager's user ID
 * @return array Array of employee user IDs
 */
function getManagedEmployeeIds($managerId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT mt.employee_id
        FROM manager_team mt
        JOIN users u ON mt.employee_id = u.id AND u.is_active = 1
        WHERE mt.manager_id = ?
    ");
    $stmt->execute([$managerId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
