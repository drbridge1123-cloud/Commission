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

// Only managers can access
if (!isManager()) {
    header('Location: BridgeLaw.php');
    exit;
}

$userInitial = strtoupper(substr($user['display_name'], 0, 1));
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - Commission Calculator</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Mono:wght@400;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/common.css?v=<?= filemtime('assets/css/common.css') ?>">
    <link rel="stylesheet" href="assets/css/admin.css?v=<?= filemtime('assets/css/admin.css') ?>">
    <link rel="stylesheet" href="assets/css/steel-minimal.css?v=<?= filemtime('assets/css/steel-minimal.css') ?>">
    <link rel="stylesheet" href="assets/css/employee-inline.css?v=<?= filemtime('assets/css/employee-inline.css') ?>">
    <link rel="stylesheet" href="assets/css/manager-inline.css?v=<?= filemtime('assets/css/manager-inline.css') ?>">
    <link rel="stylesheet" href="assets/css/traffic-v3.css?v=<?= filemtime('assets/css/traffic-v3.css') ?>">
</head>
<body class="steel-minimal">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-mark">B</div>
            <span class="brand-name">Bridge Law</span>
        </div>

        <nav class="sidebar-nav">
            <!-- Referral Management -->
            <div class="nav-group">
                <div class="nav-group-title">Referral Management</div>
                <a class="nav-link active" data-tab="referrals">
                    <svg viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Referrals</span>
                </a>
            </div>

            <!-- Case Management -->
            <div class="nav-group">
                <div class="nav-group-title">Case Management</div>
                <a class="nav-link" data-tab="attorney-progress">
                    <svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Attorney Progress</span>
                </a>
                <a class="nav-link" data-tab="cases">
                    <svg viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>My Cases</span>
                </a>
                <a class="nav-link" data-tab="history">
                    <svg viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>History</span>
                </a>
            </div>

            <!-- Analytics -->
            <div class="nav-group">
                <div class="nav-group-title">Analytics</div>
                <a class="nav-link" data-tab="reports">
                    <svg viewBox="0 0 24 24"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Reports</span>
                </a>
                <a class="nav-link" data-tab="goals">
                    <svg viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Team Goals</span>
                </a>
            </div>

            <!-- Communication -->
            <div class="nav-group">
                <div class="nav-group-title">Communication</div>
                <a class="nav-link" data-tab="notifications">
                    <svg viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span>Notifications</span>
                    <span class="nav-count" id="notificationBadge" style="display:none;">0</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="avatar-sm"><?php echo $userInitial; ?></div>
            <div class="user-meta">
                <div class="user-name"><?php echo htmlspecialchars($user['display_name']); ?></div>
                <div class="user-role">Manager</div>
            </div>
            <a href="api/logout.php" class="logout-btn" title="Logout">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main">
        <div class="page-header">
            <div>
                <div class="page-title" id="pageTitle">Referrals</div>
                <div class="page-subtitle"><?php echo date('F j, Y'); ?> &middot; <?php echo date('l'); ?></div>
            </div>
            <div class="header-actions">
                <div class="sz-btn" data-width="50" onclick="setWidth('50')">50</div>
                <div class="sz-btn" data-width="75" onclick="setWidth('75')">75</div>
                <div class="sz-btn active" data-width="100" onclick="setWidth('100')">100</div>
            </div>
        </div>

        <div class="page-content w-100" id="mainContent">
            <?php include 'app/views/manager/tabs/referrals.php'; ?>
            <?php include 'app/views/employee/tabs/cases.php'; ?>
            <?php include 'app/views/employee/tabs/history.php'; ?>
            <?php include 'app/views/employee/tabs/reports.php'; ?>
            <?php include 'app/views/manager/tabs/attorney-progress.php'; ?>
            <?php include 'app/views/manager/tabs/team-goals.php'; ?>
            <?php include 'app/views/employee/tabs/notifications.php'; ?>
        </div><!-- /page-content -->
    </div><!-- /main -->

    <!-- Reuse employee modals -->
    <?php include 'app/views/employee/modals/message.php'; ?>
    <?php include 'app/views/employee/modals/compose-message.php'; ?>
    <?php include 'app/views/employee/modals/case-detail.php'; ?>
    <?php include 'app/views/employee/modals/case-form.php'; ?>
    <?php include 'app/views/employee/modals/delete-confirm.php'; ?>

    <!-- Manager modals -->
    <?php include 'app/views/manager/modals/referral-form.php'; ?>
    <?php include 'app/views/manager/modals/demand-request-form.php'; ?>

    <!-- Shared JS -->
    <script src="assets/js/shared/utils.js?v=<?= filemtime('assets/js/shared/utils.js') ?>"></script>
    <script src="assets/js/shared/api.js?v=<?= filemtime('assets/js/shared/api.js') ?>"></script>
    <script src="assets/js/shared/shell.js?v=<?= filemtime('assets/js/shared/shell.js') ?>"></script>
    <script src="assets/js/shared/table-sort.js?v=<?= filemtime('assets/js/shared/table-sort.js') ?>"></script>

    <!-- PHP-dependent variables -->
    <script>
        const USER = <?= json_encode($user) ?>;
        let csrfToken = '<?= $csrfToken ?>';
        STORAGE_WIDTH_KEY = 'managerWidth';
    </script>

    <!-- Employee JS (reused for cases, history, reports, notifications) -->
    <script src="assets/js/employee/state.js?v=<?= filemtime('assets/js/employee/state.js') ?>"></script>
    <script src="assets/js/employee/cases.js?v=<?= filemtime('assets/js/employee/cases.js') ?>"></script>
    <script src="assets/js/employee/reports.js?v=<?= filemtime('assets/js/employee/reports.js') ?>"></script>
    <script src="assets/js/employee/history.js?v=<?= filemtime('assets/js/employee/history.js') ?>"></script>
    <script src="assets/js/employee/notifications.js?v=<?= filemtime('assets/js/employee/notifications.js') ?>"></script>

    <!-- Manager JS -->
    <script src="assets/js/manager/state.js?v=<?= filemtime('assets/js/manager/state.js') ?>"></script>
    <script src="assets/js/manager/referrals.js?v=<?= filemtime('assets/js/manager/referrals.js') ?>"></script>
    <script src="assets/js/manager/team-goals.js?v=<?= filemtime('assets/js/manager/team-goals.js') ?>"></script>
    <script src="assets/js/manager/attorney-progress.js?v=<?= filemtime('assets/js/manager/attorney-progress.js') ?>"></script>
    <script src="assets/js/manager/init.js?v=<?= filemtime('assets/js/manager/init.js') ?>"></script>
</body>
</html>
