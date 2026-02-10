<?php
/**
 * Application Constants
 * Centralized location for all magic numbers and configuration values
 */

// ============================================
// Fee Rates (Attorney Fee Percentages)
// ============================================
define('FEE_RATE_STANDARD', 33.33);      // Standard fee rate (1/3)
define('FEE_RATE_PREMIUM', 40.00);        // Premium/Litigation fee rate

// ============================================
// Commission Rates (Employee Commission)
// ============================================
define('COMMISSION_RATE_DEFAULT', 10.00);
define('COMMISSION_RATE_MIN', 5.00);
define('COMMISSION_RATE_MAX', 20.00);
define('MARKETING_COMMISSION_RATE', 5.00);  // Rate for Google/marketing-sourced cases

// ============================================
// Case Types
// ============================================
define('CASE_TYPES', [
    'PI' => 'Personal Injury',
    'MVA' => 'Motor Vehicle Accident',
    'WC' => 'Workers Compensation',
    'SD' => 'Slip and Fall',
    'Other' => 'Other'
]);

// ============================================
// Resolution Types
// ============================================
define('RESOLUTION_TYPES', [
    'Settlement' => 'Settlement',
    'Trial Verdict' => 'Trial Verdict',
    'Arbitration' => 'Arbitration',
    'Mediation' => 'Mediation'
]);

// ============================================
// Case Status
// ============================================
define('CASE_STATUS_PENDING', 'pending');
define('CASE_STATUS_PAID', 'paid');
define('CASE_STATUS_REJECTED', 'rejected');

define('CASE_STATUSES', [
    CASE_STATUS_PENDING => 'Pending',
    CASE_STATUS_PAID => 'Paid',
    CASE_STATUS_REJECTED => 'Rejected'
]);

// ============================================
// User Roles
// ============================================
define('ROLE_ADMIN', 'admin');
define('ROLE_EMPLOYEE', 'employee');

// ============================================
// Pagination
// ============================================
define('ITEMS_PER_PAGE', 25);
define('MAX_ITEMS_PER_PAGE', 100);

// ============================================
// Date Formats
// ============================================
define('DATE_FORMAT_DISPLAY', 'M. Y');        // e.g., "Dec. 2025"
define('DATE_FORMAT_DB', 'Y-m-d H:i:s');      // Database format
define('DATE_FORMAT_EXPORT', 'Y-m-d');        // Export format

// ============================================
// Validation Limits
// ============================================
define('MAX_CASE_NUMBER_LENGTH', 50);
define('MAX_CLIENT_NAME_LENGTH', 200);
define('MAX_NOTE_LENGTH', 5000);
define('MAX_SETTLED_AMOUNT', 999999999.99);
define('MIN_SETTLED_AMOUNT', 0);

// ============================================
// Temporary Password Settings
// ============================================
define('TEMP_PASSWORD_LENGTH', 12);  // Characters for temp password (was 8, now more secure)

// ============================================
// Export Settings
// ============================================
define('EXPORT_CSV_BOM', "\xEF\xBB\xBF");  // UTF-8 BOM for Excel compatibility
?>
