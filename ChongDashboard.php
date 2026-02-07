<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

// Redirect admin to admin page
if (isAdmin()) {
    header('Location: admin.php');
    exit;
}

$user = getCurrentUser();

// Only Chong (user_id = 2) can access
if ($user['id'] != 2) {
    header('Location: BridgeLaw.php');
    exit;
}

$userInitial = strtoupper(substr($user['display_name'], 0, 1));
$csrfToken = generateCSRFToken();

// Get resolution types for dropdowns
$resolutionTypes = getChongResolutionTypes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chong Dashboard - Commission Calculator</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/common.css?v=<?= filemtime('assets/css/common.css') ?>">
    <link rel="stylesheet" href="assets/css/admin.css?v=<?= filemtime('assets/css/admin.css') ?>">
    <link rel="stylesheet" href="assets/css/steel-minimal.css?v=<?= filemtime('assets/css/steel-minimal.css') ?>">
    <link rel="stylesheet" href="assets/css/chong-inline.css?v=<?= filemtime('assets/css/chong-inline.css') ?>"
</head>
<body class="steel-minimal">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-mark">B</div>
            <span class="brand-name">Bridge Law</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-group">
                <div class="nav-group-title">Overview</div>
                <a class="nav-link active" data-tab="dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a class="nav-link" data-tab="commissions">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Commissions
                </a>
            </div>
            <div class="nav-group">
                <div class="nav-group-title">Cases</div>
                <a class="nav-link" data-tab="demand">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Demand
                    <span class="nav-count" id="demandBadge" style="display:none;">0</span>
                </a>
                <a class="nav-link" data-tab="litigation">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    Litigation
                </a>
                <a class="nav-link" data-tab="traffic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Traffic
                    <span class="nav-count" id="trafficBadge" style="display:none;">0</span>
                </a>
            </div>
            <div class="nav-group">
                <div class="nav-group-title">Communication</div>
                <a class="nav-link" data-tab="notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notifications
                    <span class="nav-count" id="notifBadge" style="display:none;">0</span>
                </a>
                <a class="nav-link" data-tab="reports">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Reports
                </a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <div class="avatar-sm"><?php echo $userInitial; ?></div>
            <div class="meta">
                <div class="name"><?php echo htmlspecialchars($user['display_name']); ?></div>
                <div class="role">Employee</div>
            </div>
            <a href="api/logout.php" class="logout-btn" title="Logout">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </a>
        </div>
    </aside>

    <!-- Main -->
    <div class="main">
        <div class="page-header">
            <div>
                <div class="page-title" id="pageTitle">Dashboard</div>
                <div class="page-subtitle"><?php echo date('F j, Y'); ?> &middot; <?php echo date('l'); ?></div>
            </div>
            <div class="header-actions">
                <div class="sz-btn" data-width="50" onclick="setWidth('50')">50</div>
                <div class="sz-btn" data-width="75" onclick="setWidth('75')">75</div>
                <div class="sz-btn active" data-width="100" onclick="setWidth('100')">100</div>
            </div>
        </div>
        <div class="page-content w-100" id="mainContent">

        <!-- Dashboard Tab -->
        <?php include 'app/views/chong/tabs/dashboard.php'; ?>

        <!-- Demand Cases Tab -->
        <?php include 'app/views/chong/tabs/demand.php'; ?>

        <!-- Litigation Cases Tab -->
        <?php include 'app/views/chong/tabs/litigation.php'; ?>

        <!-- Commissions Tab -->
        <?php include 'app/views/chong/tabs/commissions.php'; ?>

        <!-- Notifications Tab -->
        <?php include 'app/views/chong/tabs/notifications.php'; ?>

        <!-- Reports Tab -->
        <?php include 'app/views/chong/tabs/reports.php'; ?>

        <!-- Traffic Cases Tab -->
        <?php include 'app/views/chong/tabs/traffic.php'; ?>

        </div><!-- /.page-content -->
    </div><!-- /.main -->

    <?php include 'app/views/chong/modals/new-demand.php'; ?>

    <?php include 'app/views/chong/modals/settle-demand.php'; ?>

    <?php include 'app/views/chong/modals/to-litigation.php'; ?>

    <?php include 'app/views/chong/modals/settle-litigation.php'; ?>

    <?php include 'app/views/chong/modals/traffic-form.php'; ?>

    <?php include 'app/views/chong/modals/edit-case.php'; ?>

    <?php include 'app/views/chong/modals/edit-commission.php'; ?>

    <!-- Shared JS -->
    <script src="assets/js/shared/utils.js?v=<?= filemtime('assets/js/shared/utils.js') ?>"></script>
    <script src="assets/js/shared/shell.js?v=<?= filemtime('assets/js/shared/shell.js') ?>"></script>

    <!-- PHP-dependent variables (must be inline) -->
    <script>
        let csrfToken = '<?= $csrfToken ?>';
    </script>

    <!-- Chong JS -->
    <script src="assets/js/chong/state.js?v=<?= filemtime('assets/js/chong/state.js') ?>"></script>
    <script src="assets/js/chong/dashboard.js?v=<?= filemtime('assets/js/chong/dashboard.js') ?>"></script>
    <script src="assets/js/chong/demand.js?v=<?= filemtime('assets/js/chong/demand.js') ?>"></script>
    <script src="assets/js/chong/litigation.js?v=<?= filemtime('assets/js/chong/litigation.js') ?>"></script>
    <script src="assets/js/chong/commissions.js?v=<?= filemtime('assets/js/chong/commissions.js') ?>"></script>
    <script src="assets/js/chong/edit-case.js?v=<?= filemtime('assets/js/chong/edit-case.js') ?>"></script>
    <script src="assets/js/chong/notifications.js?v=<?= filemtime('assets/js/chong/notifications.js') ?>"></script>
    <script src="assets/js/chong/reports.js?v=<?= filemtime('assets/js/chong/reports.js') ?>"></script>
    <script src="assets/js/chong/traffic.js?v=<?= filemtime('assets/js/chong/traffic.js') ?>"></script>
    <script src="assets/js/chong/init.js?v=<?= filemtime('assets/js/chong/init.js') ?>"></script>
</body>
</html>
