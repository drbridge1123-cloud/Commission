<?php
/**
 * Backward compatibility shim for ChongDashboard.php
 * Redirects to attorney_cases.php with Chong's attorney_id
 */
$_GET['attorney_id'] = $_GET['attorney_id'] ?? 2;
require_once __DIR__ . '/attorney_cases.php';
