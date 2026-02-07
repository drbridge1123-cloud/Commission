<?php
require_once 'includes/auth.php';
requireAdmin();

$user = getCurrentUser();

// Get user initial for avatar
$userInitial = strtoupper(substr($user['display_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Commission Calculator</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/common.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/steel-minimal.css?v=<?= filemtime('assets/css/steel-minimal.css') ?>">
    <link rel="stylesheet" href="assets/css/admin-inline.css?v=<?= filemtime('assets/css/admin-inline.css') ?>">
</head>
<body class="steel-minimal">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-mark">B</div>
            <span class="brand-name">Bridge Law</span>
        </div>

        <nav class="sidebar-nav">
            <!-- Review -->
            <div class="nav-group">
                <div class="nav-group-title">Review</div>
                <a class="nav-link active" data-tab="pending">
                    <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Pending</span>
                    <span class="nav-count" id="pendingBadge">0</span>
                </a>
                <a class="nav-link" data-tab="deadline-requests">
                    <svg viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Deadline Requests</span>
                    <span class="nav-count" id="deadlineRequestBadge" style="display:none;">0</span>
                </a>
            </div>

            <!-- Cases -->
            <div class="nav-group">
                <div class="nav-group-title">Cases</div>
                <a class="nav-link" data-tab="all">
                    <svg viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>All Cases</span>
                </a>
                <a class="nav-link" data-tab="traffic">
                    <svg viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Traffic Cases</span>
                    <span class="nav-count muted" id="trafficRequestBadge" style="display:none;">0</span>
                </a>
            </div>

            <!-- Analytics -->
            <div class="nav-group">
                <div class="nav-group-title">Analytics</div>
                <a class="nav-link" data-tab="dashboard">
                    <svg viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>
                <a class="nav-link" data-tab="report">
                    <svg viewBox="0 0 24 24"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Reports</span>
                </a>
                <a class="nav-link" data-tab="performance">
                    <svg viewBox="0 0 24 24"><path d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Performance</span>
                </a>
                <a class="nav-link" data-tab="goals">
                    <svg viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Goals</span>
                </a>
            </div>

            <!-- Admin -->
            <div class="nav-group">
                <div class="nav-group-title">Admin</div>
                <a class="nav-link" data-tab="admin-control">
                    <svg viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Admin Control</span>
                </a>
                <a class="nav-link" data-tab="history">
                    <svg viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>History</span>
                </a>
                <a class="nav-link" data-tab="database">
                    <svg viewBox="0 0 24 24"><path d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                    <span>Database</span>
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
                <div class="user-role"><?php echo htmlspecialchars($user['role']); ?></div>
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
                <div class="page-title" id="pageTitle">Pending Review</div>
                <div class="page-subtitle"><?php echo date('F j, Y'); ?> · <?php echo date('l'); ?></div>
            </div>
            <div class="header-actions">
                <div class="sz-btn" data-width="50" onclick="setWidth('50')">50</div>
                <div class="sz-btn" data-width="75" onclick="setWidth('75')">75</div>
                <div class="sz-btn active" data-width="100" onclick="setWidth('100')">100</div>
            </div>
        </div>

        <div class="page-content w-100" id="mainContent">
        <?php include 'app/views/admin/tabs/pending.php'; ?>
        <?php include 'app/views/admin/tabs/deadline-requests.php'; ?>
        <?php include 'app/views/admin/modals/deadline-review.php'; ?>
        <?php include 'app/views/admin/tabs/all.php'; ?>
        <?php include 'app/views/admin/modals/case-detail.php'; ?>
        <?php include 'app/views/admin/modals/edit-case.php'; ?>
        <?php include 'app/views/admin/tabs/dashboard.php'; ?>
        <?php include 'app/views/admin/tabs/report.php'; ?>
        <?php include 'app/views/admin/tabs/history.php'; ?>
        <?php include 'app/views/admin/modals/history-detail.php'; ?>
        <?php include 'app/views/admin/tabs/goals.php'; ?>
        <?php include 'app/views/admin/tabs/admin-control.php'; ?>
        <?php include 'app/views/admin/tabs/traffic.php'; ?>
        <?php include 'app/views/admin/modals/traffic-edit.php'; ?>
        <?php include 'app/views/admin/modals/user-form.php'; ?>
        <?php include 'app/views/admin/tabs/notifications.php'; ?>
        <?php include 'app/views/admin/modals/compose-message.php'; ?>
        <?php include 'app/views/admin/modals/view-message.php'; ?>
        <?php include 'app/views/admin/modals/send-message.php'; ?>
        <?php include 'app/views/admin/modals/message-case-detail.php'; ?>
        <?php include 'app/views/admin/modals/delete-confirm.php'; ?>
        <?php include 'app/views/admin/tabs/performance.php'; ?>
        <?php include 'app/views/admin/tabs/database.php'; ?>
        </div><!-- /page-content -->
    </div><!-- /main -->

    <script src="assets/js/shared/utils.js?v=<?= filemtime('assets/js/shared/utils.js') ?>"></script>
    <script>let csrfToken = '<?= generateCSRFToken() ?>';</script>
    <script src="assets/js/admin/state.js?v=<?= filemtime('assets/js/admin/state.js') ?>"></script>
    <script src="assets/js/admin/pending.js?v=<?= filemtime('assets/js/admin/pending.js') ?>"></script>
    <script src="assets/js/admin/deadline-requests.js?v=<?= filemtime('assets/js/admin/deadline-requests.js') ?>"></script>
    <script src="assets/js/admin/all-cases.js?v=<?= filemtime('assets/js/admin/all-cases.js') ?>"></script>
    <script src="assets/js/admin/dashboard.js?v=<?= filemtime('assets/js/admin/dashboard.js') ?>"></script>
    <script src="assets/js/admin/report.js?v=<?= filemtime('assets/js/admin/report.js') ?>"></script>
    <script src="assets/js/admin/notifications.js?v=<?= filemtime('assets/js/admin/notifications.js') ?>"></script>
    <script src="assets/js/admin/history.js?v=<?= filemtime('assets/js/admin/history.js') ?>"></script>
    <script src="assets/js/admin/admin-control.js?v=<?= filemtime('assets/js/admin/admin-control.js') ?>"></script>
    <script src="assets/js/admin/traffic.js?v=<?= filemtime('assets/js/admin/traffic.js') ?>"></script>
    <script src="assets/js/admin/performance.js?v=<?= filemtime('assets/js/admin/performance.js') ?>"></script>
    <script src="assets/js/admin/goals.js?v=<?= filemtime('assets/js/admin/goals.js') ?>"></script>
    <script src="assets/js/admin/init.js?v=<?= filemtime('assets/js/admin/init.js') ?>"></script>
</body>
</html>
