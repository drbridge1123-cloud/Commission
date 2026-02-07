<?php
require_once 'includes/auth.php';
requireLogin();

// Redirect admin to admin page
if (isAdmin()) {
    header('Location: admin.php');
    exit;
}

$user = getCurrentUser();

// Redirect Chong to ChongDashboard
if ($user['id'] == 2) {
    header('Location: ChongDashboard.php');
    exit;
}
$userInitial = strtoupper(substr($user['display_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Commission Calculator</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Mono:wght@400;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/steel-minimal.css">
    <style>
        /* Sidebar SVG Icons */
        .sidebar-nav .nav-link svg { stroke: currentColor; fill: none; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }

        /* INK COMPACT DESIGN SYSTEM */
        :root {
            --ink-bg: #f0f1f3;
            --ink-white: #fff;
            --ink-border: #e2e4ea;
            --ink-900: #1a1a2e;
            --ink-700: #3d3f4e;
            --ink-500: #5c5f73;
            --ink-400: #8b8fa3;
            --ink-teal: #0d9488;
            --ink-amber: #d97706;
        }

        /* Quick Stats Cards */
        .quick-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 16px; }
        .qs-card { background: #fff; border-radius: 8px; padding: 14px 16px; border: 1px solid #e2e4ea; display: flex; justify-content: space-between; align-items: center; }
        .qs-label { font-size: 11px; color: #8b8fa3; font-weight: 500; text-transform: uppercase; letter-spacing: 0.3px; font-family: 'Outfit', sans-serif; }
        .qs-val { font-size: 20px; font-weight: 700; font-variant-numeric: tabular-nums; color: #1a1a2e; font-family: 'Outfit', sans-serif; }
        .qs-val.green { color: #0d9488; }
        .qs-val.amber { color: #d97706; }
        .qs-val.blue { color: #3b82f6; }
        .qs-val.dim { color: #c4c7d0; }
        @media (max-width: 1200px) { .quick-stats { grid-template-columns: repeat(2, 1fr); } }

        /* Filters */
        .filters { display: flex; align-items: center; gap: 6px; margin-bottom: 12px; flex-wrap: wrap; }
        .f-chip { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; cursor: pointer; border: 1px solid #e2e4ea; background: #fff; color: #5c5f73; transition: all 0.12s; font-family: 'Outfit', sans-serif; }
        .f-chip:hover { background: #f5f5f7; }
        .f-chip.active { background: #1a1a2e; color: #fff; border-color: #1a1a2e; }
        .f-select { padding: 5px 28px 5px 10px; border: 1px solid #e2e4ea; border-radius: 20px; font-size: 12px; color: #5c5f73; background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%238b8fa3' viewBox='0 0 16 16'%3E%3Cpath d='M4.646 5.646a.5.5 0 01.708 0L8 8.293l2.646-2.647a.5.5 0 01.708.708l-3 3a.5.5 0 01-.708 0l-3-3a.5.5 0 010-.708z'/%3E%3C/svg%3E") right 8px center no-repeat; appearance: none; font-family: 'Outfit', sans-serif; cursor: pointer; width: auto; }
        .f-spacer { flex: 1; }
        .f-search { padding: 5px 12px; border: 1px solid #e2e4ea; border-radius: 20px; font-size: 12px; width: 180px; background: #fff; font-family: 'Outfit', sans-serif; color: #5c5f73; }
        .f-btn { padding: 5px 14px; border: 1px solid #e2e4ea; border-radius: 20px; background: #fff; font-size: 11px; font-weight: 600; color: #5c5f73; cursor: pointer; font-family: 'Outfit', sans-serif; }
        .f-btn:hover { background: #f5f5f7; }

        /* Ink Compact Table */
        .tbl-container { background: #fff; border-radius: 10px; overflow: hidden; border: 1px solid #e2e4ea; }
        .tbl-header { padding: 12px 16px; border-bottom: 1px solid #e2e4ea; display: flex; justify-content: space-between; align-items: center; }
        .tbl-title { font-size: 14px; font-weight: 600; color: #1a1a2e; font-family: 'Outfit', sans-serif; }
        .tbl { width: 100%; border-collapse: collapse; font-family: 'Outfit', sans-serif; }
        .tbl thead th { padding: 10px 12px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.6px; font-weight: 700; color: var(--text-500); background: var(--bg); border-bottom: 1px solid var(--border); text-align: left; white-space: nowrap; }
        .tbl thead th.r { text-align: right; }
        .tbl thead th.c { text-align: center; }
        .tbl thead th .th-sort { display: inline-flex; align-items: center; gap: 4px; cursor: pointer; padding: 2px 4px; margin: -2px -4px; border-radius: 3px; }
        .tbl thead th .th-sort:hover { background: rgba(255,255,255,0.1); }
        .tbl tbody tr { border-bottom: 1px solid #f0f1f3; transition: background 0.08s; cursor: pointer; }
        .tbl tbody tr:hover { background: #f5f8ff; }
        .tbl tbody td { padding: 10px 12px; font-size: 13px; color: #3d3f4e; white-space: nowrap; }
        .tbl tbody td.r { text-align: right; font-variant-numeric: tabular-nums; font-weight: 500; }
        .tbl tbody td.em { font-weight: 700; color: #0d9488; }
        .tbl tbody td.mute { color: #c4c7d0; }
        .tbl-foot { padding: 10px 12px; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; border-top: 1px solid #e2e4ea; font-family: 'Outfit', sans-serif; }
        .tbl-foot .left { font-size: 12px; color: #8b8fa3; }
        .tbl-foot .right { display: flex; gap: 16px; font-size: 12px; }
        .tbl-foot .ft { display: flex; align-items: center; gap: 4px; }
        .tbl-foot .ft-l { color: #8b8fa3; }
        .tbl-foot .ft-v { font-weight: 700; color: #1a1a2e; font-variant-numeric: tabular-nums; }
        .tbl-foot .ft-v.green { color: #0d9488; }
        .tbl-foot .ft-v.amber { color: #d97706; }

        /* Status Badges */
        .stat-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; font-family: 'Outfit', sans-serif; }
        .stat-badge.unpaid { background: #fef3c7; color: #b45309; }
        .stat-badge.paid { background: #d1fae5; color: #065f46; }
        .stat-badge.pending { background: #fef3c7; color: #92400e; }
        .stat-badge.in_progress { background: #dbeafe; color: #1d4ed8; }

        /* Buttons */
        .ink-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; font-family: 'Outfit', sans-serif; transition: all 0.12s; }
        .ink-btn-primary { background: #1a1a2e; color: #fff; }
        .ink-btn-primary:hover { background: #2d2d4a; }
        .ink-btn-secondary { background: #fff; color: #5c5f73; border: 1px solid #e2e4ea; }
        .ink-btn-secondary:hover { background: #f5f5f7; }
        .ink-btn-sm { padding: 5px 12px; font-size: 11px; }
        .act-link { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; border: none; background: #1a1a2e; color: #fff; font-family: 'Outfit', sans-serif; }
        .act-link:hover { background: #2d2d4a; }
        .act-link.danger { background: transparent; color: #dc2626; border: 1px solid #fecaca; }
        .act-link.danger:hover { background: #fef2f2; }

        /* Chart Container */
        .ink-chart-container { background: #fff; border-radius: 10px; padding: 20px 24px; border: 1px solid #e2e4ea; font-family: 'Outfit', sans-serif; }
        .ink-chart-container h3 { font-size: 14px; font-weight: 600; color: #1a1a2e; margin-bottom: 16px; }

        /* Notification Stats */
        .notif-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
        .notif-filters { display: flex; align-items: center; gap: 6px; margin-bottom: 12px; flex-wrap: wrap; }
        .f-btn-ghost { padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; cursor: pointer; border: 1px solid #e2e4ea; background: #fff; color: #5c5f73; font-family: inherit; transition: all 0.12s; }
        .f-btn-ghost:hover { background: #f5f5f7; }
        .f-btn-primary { padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; cursor: pointer; border: none; background: #1a1a2e; color: #fff; font-family: inherit; transition: all 0.12s; display: inline-flex; align-items: center; gap: 5px; }
        .f-btn-primary:hover { background: #2d2d4a; }

        /* Unread row */
        .tbl tbody tr.unread { background: #fafbff; }
        .tbl tbody tr.unread:hover { background: #eff6ff; }
        .tbl tbody tr.unread td { font-weight: 500; color: #1a1a2e; }
        .unread-dot { width: 7px; height: 7px; border-radius: 50%; background: #3b82f6; }

        /* Direction badges */
        .dir-badge { display: inline-block; padding: 2px 7px; border-radius: 3px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
        .dir-badge.sent { background: #e2e4ea; color: #5c5f73; }
        .dir-badge.received { background: #dbeafe; color: #1d4ed8; }
        .dir-badge.system-approved { background: #d1fae5; color: #065f46; }
        .dir-badge.system-rejected { background: #fee2e2; color: #991b1b; }

        /* Notification table extras */
        .td-subject { max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .tbl tbody tr.unread .td-subject { font-weight: 600; color: #1a1a2e; }
        .td-time { color: #8b8fa3; font-size: 12px; }
        .tbl tbody tr.unread .td-time { color: #3b82f6; font-weight: 600; }

        /* Action icons */
        .act-icon { width: 28px; height: 28px; border-radius: 6px; border: 1px solid #e2e4ea; background: #fff; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.1s; color: #8b8fa3; }
        .act-icon:hover { background: #f0f1f3; color: #1a1a2e; }
        .act-icon.danger { border-color: #fecaca; color: #dc2626; }
        .act-icon.danger:hover { background: #fef2f2; }
        .act-icon svg { width: 14px; height: 14px; }
        .action-group { display: flex; gap: 4px; justify-content: center; }

        /* Empty state */
        .notif-empty { padding: 48px 24px; text-align: center; }
        .notif-empty-icon { width: 48px; height: 48px; background: #f0f1f3; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; }
        .notif-empty-icon svg { width: 24px; height: 24px; color: #c4c7d0; }
        .notif-empty-title { font-size: 14px; font-weight: 600; color: #3d3f4e; margin-bottom: 4px; }
        .notif-empty-desc { font-size: 12px; color: #8b8fa3; }

        /* Modal Header */
        .modal-header { background: var(--text-900) !important; color: white !important; }
        .modal-header h2, .modal-header h3 { color: white !important; font-family: 'Outfit', sans-serif; }
        .modal-close { color: rgba(255,255,255,0.7) !important; }
        .modal-close:hover { color: white !important; }
    </style>
</head>
<body class="steel-minimal">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-mark">B</div>
            <span class="brand-name">Bridge Law</span>
        </div>

        <nav class="sidebar-nav">
            <!-- Overview -->
            <div class="nav-group">
                <div class="nav-group-title">Overview</div>
                <a class="nav-link active" data-tab="cases">
                    <svg viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>My Cases</span>
                </a>
                <a class="nav-link" data-tab="reports">
                    <svg viewBox="0 0 24 24"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Reports</span>
                </a>
                <a class="nav-link" data-tab="history">
                    <svg viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>History</span>
                </a>
                <a class="nav-link" data-tab="goals">
                    <svg viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Goals</span>
                </a>
            </div>

            <!-- Cases -->
            <?php if ($user['id'] == 2): // Chong only - Traffic Cases ?>
            <div class="nav-group">
                <div class="nav-group-title">Cases</div>
                <a class="nav-link" data-tab="traffic">
                    <svg viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Traffic</span>
                    <span class="nav-count muted" id="trafficBadge" style="display:none;">0</span>
                </a>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('can_request_traffic') && $user['id'] != 2): ?>
            <div class="nav-group">
                <div class="nav-group-title">Requests</div>
                <a class="nav-link" data-tab="traffic-requests">
                    <svg viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Traffic Request</span>
                </a>
            </div>
            <?php endif; ?>

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
                <div class="page-title" id="pageTitle">My Cases</div>
                <div class="page-subtitle"><?php echo date('F j, Y'); ?> · <?php echo date('l'); ?></div>
            </div>
            <div class="header-actions">
                <div class="sz-btn" data-width="50" onclick="setWidth('50')">50</div>
                <div class="sz-btn" data-width="75" onclick="setWidth('75')">75</div>
                <div class="sz-btn active" data-width="100" onclick="setWidth('100')">100</div>
            </div>
        </div>

        <div class="page-content w-100" id="mainContent">
        <!-- My Cases Tab - Ink Compact (Commissions Design) -->
        <div id="content-cases">
            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="qs-card">
                    <span class="qs-label">Cases</span>
                    <span class="qs-val" id="totalCases">0</span>
                </div>
                <div class="qs-card">
                    <span class="qs-label">Total</span>
                    <span class="qs-val" id="totalCommission">$0.00</span>
                </div>
                <div class="qs-card">
                    <span class="qs-label">Paid</span>
                    <span class="qs-val green" id="paidCommission">$0.00</span>
                </div>
                <div class="qs-card">
                    <span class="qs-label">Unpaid</span>
                    <span class="qs-val" style="color:#dc2626;" id="unpaidCommission">$0.00</span>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <button onclick="showAddForm()" class="ink-btn ink-btn-primary ink-btn-sm" style="margin-right: 4px;">+ New Case</button>
                <span class="f-chip active" id="statusChip-all" onclick="setStatusFilter('all', this)">All</span>
                <span class="f-chip" id="statusChip-paid" onclick="setStatusFilter('paid', this)">Paid</span>
                <span class="f-chip" id="statusChip-unpaid" onclick="setStatusFilter('unpaid', this)">Unpaid</span>
                <span style="width: 1px; height: 20px; background: #e2e4ea; margin: 0 8px;"></span>
                <select id="filterYear" onchange="renderCases()" class="f-select" style="width: 85px;">
                    <!-- Years populated by JavaScript -->
                </select>
                <select id="filterMonth" onchange="renderCases()" class="f-select" style="width: 110px;">
                    <option value="all">All Month</option>
                </select>
                <div class="f-spacer"></div>
                <input type="text" id="searchInput" class="f-search" placeholder="Search..." onkeyup="filterTable()">
                <button onclick="exportToExcel()" class="f-btn" style="background:#059669; color:#fff; border-color:#059669;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export
                </button>
            </div>

            <!-- Table -->
            <div class="tbl-container">
                <div style="overflow-x: auto;">
                    <table id="casesTable" class="tbl">
                        <thead>
                            <tr>
                                <th style="width:0;padding:0;border:none;"></th>
                                <th><span class="th-sort" onclick="sortCases('resolution_type')">Resolution Type <span class="sort-arrow">▼</span></span></th>
                                <th><span class="th-sort" onclick="sortCases('client_name')">Client Name <span class="sort-arrow">▼</span></span></th>
                                <th class="r"><span class="th-sort" onclick="sortCases('settled')">Settled <span class="sort-arrow">▼</span></span></th>
                                <th class="r"><span class="th-sort" onclick="sortCases('presuit_offer')">Pre Suit Offer <span class="sort-arrow">▼</span></span></th>
                                <th class="r"><span class="th-sort" onclick="sortCases('difference')">Difference <span class="sort-arrow">▼</span></span></th>
                                <th class="r"><span class="th-sort" onclick="sortCases('legal_fee')">Legal Fee <span class="sort-arrow">▼</span></span></th>
                                <th class="r"><span class="th-sort" onclick="sortCases('discounted_legal_fee')">Disc. Legal Fee <span class="sort-arrow">▼</span></span></th>
                                <th class="r"><span class="th-sort" onclick="sortCases('commission')">Commission <span class="sort-arrow">▼</span></span></th>
                                <th><span class="th-sort" onclick="sortCases('month')">Month <span class="sort-arrow">▼</span></span></th>
                                <th class="c"><span class="th-sort" onclick="sortCases('status')">Status <span class="sort-arrow">▼</span></span></th>
                                <th class="c">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="casesBody">
                            <tr><td colspan="12" style="text-align:center; padding: 40px; color: #8b8fa3;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="tbl-foot">
                    <span class="left" id="footerInfo">0 cases</span>
                    <div class="right">
                        <span class="ft"><span class="ft-l">Total</span> <span class="ft-v" id="footerTotal">$0.00</span></span>
                        <span class="ft"><span class="ft-l">Paid</span> <span class="ft-v green" id="footerPaid">$0.00</span></span>
                        <span class="ft"><span class="ft-l">Unpaid</span> <span class="ft-v" style="color:#dc2626;" id="footerUnpaid">$0.00</span></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reports Tab -->
        <div id="content-reports" class="hidden">
            <!-- Filters -->
            <div class="filters" style="margin-bottom: 16px;">
                <select id="reportType" onchange="generateReport()" class="f-select" style="min-width: 100px;">
                    <option value="monthly" selected>Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
                <select id="reportYear" onchange="generateReport()" class="f-select"></select>
                <select id="reportMonth" onchange="generateReport()" class="f-select" style="min-width: 115px;">
                    <option value="all" selected>All Months</option>
                </select>
            </div>

            <!-- Report Content -->
            <div id="reportContent">
                <p style="text-align: center; color: #8b8fa3; padding: 48px;">Loading...</p>
            </div>
        </div>
        
        <!-- History Tab - Ink Compact -->
        <div id="content-history" class="hidden">
            <!-- Filters -->
            <div class="filters" style="margin-bottom: 12px;">
                <select id="historyFilter" onchange="loadHistory()" class="f-select">
                    <option value="all">All Time</option>
                    <option value="year">This Year</option>
                    <option value="month">This Month</option>
                </select>
                <span class="f-spacer"></span>
                <button onclick="exportHistory()" class="f-btn">Export</button>
            </div>
            <div class="tbl-container">
                <div id="historyContent">
                    <!-- History will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Goals Tab -->
        <div id="content-goals" class="hidden">
            <!-- Filter -->
            <div style="margin-bottom: 16px;">
                <select id="myGoalsYearFilter" class="f-select" style="width:auto;" onchange="loadMyGoals()">
                </select>
            </div>

            <!-- Progress Cards -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <!-- Cases Goal Card -->
                <div class="qs-card" style="flex-direction: column; align-items: stretch; padding: 20px;">
                    <div class="qs-label" style="margin-bottom: 12px;">Cases Goal</div>
                    <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 8px;">
                        <span class="qs-val" id="goalCasesActual" style="font-size: 28px;">0</span>
                        <span style="font-size: 14px; color: #8b8fa3;">/ <span id="goalCasesTarget">50</span></span>
                    </div>
                    <div style="height: 8px; background: #f0f1f3; border-radius: 4px; overflow: hidden; margin-bottom: 6px;">
                        <div id="goalCasesBar" style="height: 100%; background: #0d9488; border-radius: 4px; width: 0%; transition: width 0.5s;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #8b8fa3; font-family: 'Outfit', sans-serif;">
                        <span id="goalCasesPercent">0% complete</span>
                        <span id="goalCasesPace">-</span>
                    </div>
                </div>

                <!-- Legal Fee Goal Card -->
                <div class="qs-card" style="flex-direction: column; align-items: stretch; padding: 20px;">
                    <div class="qs-label" style="margin-bottom: 12px;">Legal Fee Goal</div>
                    <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 8px;">
                        <span class="qs-val teal" id="goalFeeActual" style="font-size: 28px;">$0</span>
                        <span style="font-size: 14px; color: #8b8fa3;">/ <span id="goalFeeTarget">$500K</span></span>
                    </div>
                    <div style="height: 8px; background: #f0f1f3; border-radius: 4px; overflow: hidden; margin-bottom: 6px;">
                        <div id="goalFeeBar" style="height: 100%; background: #3b82f6; border-radius: 4px; width: 0%; transition: width 0.5s;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #8b8fa3; font-family: 'Outfit', sans-serif;">
                        <span id="goalFeePercent">0% complete</span>
                        <span id="goalFeePace">-</span>
                    </div>
                </div>
            </div>

            <!-- Monthly Breakdown -->
            <div class="tbl-container">
                <div class="tbl-header"><span class="tbl-title">Monthly Breakdown</span></div>
                <div id="goalMonthlyContent" style="padding: 0;">
                    <p style="text-align: center; padding: 40px; color: #8b8fa3; font-size: 12px;">Loading...</p>
                </div>
            </div>
        </div>

        <!-- Notifications Tab - Ink Compact -->
        <div id="content-notifications" class="hidden">
            <!-- Quick Stats -->
            <div class="notif-stats">
                <div class="qs-card">
                    <div><div class="qs-label">Total Messages</div><div class="qs-val" id="notifStatTotal">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Unread</div><div class="qs-val blue" id="notifStatUnread">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Sent</div><div class="qs-val" id="notifStatSent">0</div></div>
                </div>
            </div>

            <!-- Filters -->
            <div class="notif-filters">
                <span class="f-chip active" data-filter="all" onclick="filterNotifications('all')">All</span>
                <span class="f-chip" data-filter="unread" onclick="filterNotifications('unread')">Unread</span>
                <span class="f-chip" data-filter="sent" onclick="filterNotifications('sent')">Sent</span>
                <span class="f-chip" data-filter="read" onclick="filterNotifications('read')">Read</span>
                <div class="f-spacer"></div>
                <button class="f-btn-ghost" onclick="markAllRead()">Mark All Read</button>
                <button class="f-btn-primary" onclick="openComposeMessage()">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                    New Message
                </button>
            </div>

            <!-- Table -->
            <div class="tbl-container">
                <table class="tbl" id="notificationsTable">
                    <thead>
                        <tr>
                            <th style="width:24px;padding:10px 6px 10px 14px;"></th>
                            <th>Type</th>
                            <th>From / To</th>
                            <th>Subject</th>
                            <th>Time</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="notificationsBody">
                        <tr><td colspan="6" style="text-align:center; padding:40px; color:#8b8fa3;">Loading...</td></tr>
                    </tbody>
                </table>
                <div class="tbl-foot">
                    <span id="notifFootLeft">0 messages</span>
                    <span id="notifFootRight">0 unread</span>
                </div>
            </div>
        </div>
        <?php if ($user['id'] == 2): // Chong only - Traffic Cases Tab ?>
        <!-- Traffic Cases Tab -->
        <div id="content-traffic" class="hidden">
            <!-- Stats Cards -->
            <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
                <div class="stat-card">
                    <p class="stat-label">Active Cases</p>
                    <p class="stat-value stat-blue" id="trafficActive">0</p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Dismissed</p>
                    <p class="stat-value stat-green" id="trafficDismissed">0</p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Amended</p>
                    <p class="stat-value stat-amber" id="trafficAmended">0</p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Total Commission</p>
                    <p class="stat-value stat-green" id="trafficCommission">$0</p>
                </div>
            </div>

            <!-- Traffic Layout with Sidebar -->
            <div style="display: flex; gap: 16px; margin-top: 16px;">
                <!-- Sidebar - Minimal Flat Blue Accent Design -->
                <div class="traffic-sidebar" style="width: 300px; flex-shrink: 0;">
                    <!-- Pending Requests Section -->
                    <div id="pendingRequestsSection" style="background: #ffffff; border: 1px solid #e5e9f0; border-radius: 10px; overflow: hidden; margin-bottom: 12px; display: none;">
                        <div style="padding: 12px 16px; background: #fef3c7; border-bottom: 1px solid #fcd34d;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 13px; font-weight: 600; color: #92400e;">Pending Requests</span>
                                <span id="pendingRequestCount" style="background: #f59e0b; color: white; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 10px;">0</span>
                            </div>
                        </div>
                        <div id="pendingRequestsList" style="max-height: 250px; overflow-y: auto;">
                            <!-- Populated by JS -->
                        </div>
                    </div>

                    <!-- Sidebar Container -->
                    <div style="background: #ffffff; border: 1px solid #e5e9f0; border-radius: 10px; overflow: hidden;">
                        <!-- Tab Navigation -->
                        <div style="padding: 6px; background: #f7f9fc; display: flex; gap: 2px;">
                            <button onclick="switchSidebarTab('all')" id="sidebarTab-all" class="sidebar-tab-btn active" style="flex: 1; padding: 9px 6px; font-size: 11px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; transition: all 0.15s; font-family: Inter, -apple-system, sans-serif;">All</button>
                            <button onclick="switchSidebarTab('referral')" id="sidebarTab-referral" class="sidebar-tab-btn" style="flex: 1; padding: 9px 6px; font-size: 11px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; transition: all 0.15s; font-family: Inter, -apple-system, sans-serif;">Referral</button>
                            <button onclick="switchSidebarTab('court')" id="sidebarTab-court" class="sidebar-tab-btn" style="flex: 1; padding: 9px 6px; font-size: 11px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; transition: all 0.15s; font-family: Inter, -apple-system, sans-serif;">Court</button>
                            <button onclick="switchSidebarTab('year')" id="sidebarTab-year" class="sidebar-tab-btn" style="flex: 1; padding: 9px 6px; font-size: 11px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; transition: all 0.15s; font-family: Inter, -apple-system, sans-serif;">Year</button>
                        </div>

                        <!-- Sidebar Content List -->
                        <div id="sidebarContent" style="max-height: 350px; overflow-y: auto;">
                            <!-- Populated by JS -->
                        </div>

                        <!-- Stats Section -->
                        <div id="trafficQuickStats" style="padding: 14px 16px; background: #f7f9fc; border-top: 1px solid #e5e9f0;">
                            <!-- Populated by JS -->
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div style="flex: 1; min-width: 0;">
                    <div class="container-card" style="padding: 0;">
                        <div class="card-header flex justify-between items-center" style="padding: 12px 16px;">
                            <div>
                                <h2 class="card-title" style="font-size: 16px;">Traffic Cases</h2>
                                <p class="card-subtitle" id="trafficFilterLabel" style="font-size: 12px;">All Cases</p>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <input type="text" id="trafficSearch" placeholder="Search..." onkeyup="filterTrafficCases()" class="form-input" style="padding: 8px 12px; width: 150px; font-size: 13px;">
                                <button onclick="openAddTrafficModal()" class="btn-primary" style="padding: 8px 16px; white-space: nowrap; font-size: 13px;">+ New Case</button>
                            </div>
                        </div>

                        <div class="table-scroll-wrapper">
                            <table class="excel-table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th><div class="th-content">Client Name</div></th>
                                        <th><div class="th-content">Court</div></th>
                                        <th><div class="th-content">Court Date</div></th>
                                        <th><div class="th-content">Charge</div></th>
                                        <th><div class="th-content">Case #</div></th>
                                        <th><div class="th-content">Offer</div></th>
                                        <th><div class="th-content">Disposition</div></th>
                                        <th><div class="th-content">Referral</div></th>
                                        <th style="text-align: right;"><div class="th-content" style="justify-content: flex-end;">Commission</div></th>
                                        <th style="text-align: center;"><div class="th-content" style="justify-content: center;">Actions</div></th>
                                    </tr>
                                </thead>
                                <tbody id="trafficCasesBody">
                                    <tr><td colspan="10" style="padding: 32px 16px; text-align: center;" class="text-secondary">Loading traffic cases...</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer -->
                        <div class="table-footer" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-top: 1px solid #e5e7eb;">
                            <span id="trafficCaseCount" class="text-secondary">0 cases</span>
                            <span class="total-label">Total Commission: <span id="trafficTotalCommission" class="total-amount">$0.00</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden filters for compatibility -->
            <select id="trafficYearFilter" onchange="filterTrafficCases()" style="display: none;">
            </select>
            <select id="trafficStatusFilter" onchange="filterTrafficCases()" style="display: none;">
                <option value="all">All</option>
                <option value="active" selected>Active</option>
                <option value="resolved">Resolved</option>
            </select>

        </div>
        <?php endif; ?>

        <?php if (hasPermission('can_request_traffic') && $user['id'] != 2): ?>
        <div id="content-traffic-requests" class="hidden">
            <div style="display: grid; grid-template-columns: 320px 1fr; gap: 16px;">
                <!-- Request Form -->
                <div>
                    <div class="tbl-container">
                        <div style="padding: 10px 14px; background: #1a1a2e; border-radius: 10px 10px 0 0;">
                            <h3 style="font-size: 12px; font-weight: 600; color: #fff; font-family: 'Outfit', sans-serif; margin: 0;">Request New Traffic Case</h3>
                        </div>
                        <form id="empTrafficRequestForm" style="padding: 12px 14px; display: flex; flex-direction: column; gap: 8px;">
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Client Name *</label>
                                <input type="text" id="empReqClientName" required class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Phone</label>
                                    <input type="text" id="empReqClientPhone" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Email</label>
                                    <input type="email" id="empReqClientEmail" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Ticket #</label>
                                    <input type="text" id="empReqCaseNumber" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Court</label>
                                    <input type="text" id="empReqCourt" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                            </div>
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Charge</label>
                                <input type="text" id="empReqCharge" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Issued</label>
                                    <input type="date" id="empReqIssuedDate" class="ink-input" style="padding: 5px 6px; font-size: 11px;">
                                </div>
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Court Date</label>
                                    <input type="date" id="empReqCourtDate" class="ink-input" style="padding: 5px 6px; font-size: 11px;">
                                </div>
                            </div>
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Note</label>
                                <textarea id="empReqNote" rows="2" class="ink-input" style="padding: 6px 8px; font-size: 12px; resize: none;"></textarea>
                            </div>
                            <button type="submit" class="ink-btn ink-btn-primary" style="width: 100%; justify-content: center; padding: 8px 12px; font-size: 12px;">Submit Request</button>
                        </form>
                    </div>
                </div>

                <!-- My Requests List -->
                <div class="tbl-container" style="align-self: start;">
                    <div style="padding: 12px 16px; border-bottom: 1px solid #e2e4ea;">
                        <h3 style="font-size: 14px; font-weight: 600; color: #1a1a2e; font-family: 'Outfit', sans-serif; margin: 0;">My Requests</h3>
                    </div>
                    <div id="empMyTrafficRequests" style="max-height: 700px; overflow-y: auto;">
                        <p style="padding: 24px; text-align: center; color: #8b8fa3; font-size: 12px; font-family: 'Outfit', sans-serif;">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        </div><!-- /page-content -->
    </div><!-- /main -->

    <!-- Message Detail Modal -->
    <div id="messageModal" class="modal-overlay" onclick="if(event.target === this) closeMessageModal()">
        <div class="modal-content" style="max-width: 600px;" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 id="messageModalTitle" class="text-xl font-bold text-gray-900">Message</h2>
                <button onclick="closeMessageModal()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="messageFromContainer" style="margin-bottom: 20px;">
                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;" id="messageFromLabel">From:</div>
                    <div style="font-weight: 600; color: #374151;" id="messageFrom"></div>
                </div>
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">Subject:</div>
                    <div style="font-weight: 600; color: #374151; font-size: 16px;" id="messageSubject"></div>
                </div>
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">Date:</div>
                    <div style="color: #6b7280;" id="messageDate"></div>
                </div>
                <div>
                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 8px;">Message:</div>
                    <div style="background: #f9fafb; padding: 16px; border-radius: 8px; border: 1px solid #e5e7eb; white-space: pre-wrap; line-height: 1.6;" id="messageBody"></div>
                </div>
                <div class="modal-actions" style="margin-top: 24px;">
                    <button onclick="replyToMessage()" class="btn-primary">Reply</button>
                    <button id="viewCaseBtn" onclick="viewCaseFromMessage()" class="btn-primary" style="background: #059669; display: none;">View Case</button>
                    <button onclick="deleteCurrentMessage()" class="btn-secondary" style="background: #dc2626;">Delete</button>
                    <button onclick="closeMessageModal()" class="btn-secondary">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Compose Message Modal -->
    <div id="composeMessageModal" class="modal-overlay" onclick="if(event.target === this) closeComposeMessage()">
        <div class="modal-content" style="max-width: 600px;" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 class="text-xl font-bold text-gray-900">Send Message to Admin</h2>
                <button onclick="closeComposeMessage()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form onsubmit="sendEmployeeMessage(event)">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Subject:</label>
                        <input type="text" id="composeSubject" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Message:</label>
                        <textarea id="composeMessage" rows="6" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; resize: vertical;"></textarea>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Send Message</button>
                        <button type="button" onclick="closeComposeMessage()" class="btn-secondary">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Case Detail Modal -->
    <div id="caseDetailModal" class="modal-overlay" onclick="if(event.target === this) closeCaseDetail()">
        <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 680px; max-height: 90vh; border-radius: 12px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.12); overflow: hidden; display: flex; flex-direction: column; padding: 0;">
            <!-- Blue Header -->
            <div style="background: #18181b; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 3px; height: 18px; background: #22d3ee; border-radius: 2px;"></div>
                    <h2 style="font-size: 15px; font-weight: 600; color: white; margin: 0;">Case Details</h2>
                </div>
                <button onclick="closeCaseDetail()" style="width: 28px; height: 28px; background: rgba(255,255,255,0.15); border: none; border-radius: 6px; color: rgba(255,255,255,0.9); font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
            </div>

            <!-- Content Area (Scrollable) -->
            <div style="padding: 16px 20px; overflow-y: auto; flex: 1;">
                <div id="caseDetailContent"></div>
            </div>

            <!-- Footer (Fixed) -->
            <div style="background: #f8fafc; padding: 12px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; align-items: center; flex-shrink: 0; gap: 10px;">
                <button id="editCaseDetailBtn" onclick="editCaseFromDetail()" style="padding: 9px 16px; background: #18181b; color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: none;">Edit</button>
                <button onclick="closeCaseDetail()" style="padding: 9px 14px; background: white; color: #64748b; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;">Close</button>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="caseModal" class="modal-overlay" onclick="if(event.target === this) closeModal()">
        <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 680px; max-height: 90vh; border-radius: 12px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.12); overflow: hidden; display: flex; flex-direction: column; padding: 0;">
            <!-- Blue Header -->
            <div style="background: #18181b; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 3px; height: 18px; background: #22d3ee; border-radius: 2px;"></div>
                    <h2 id="modalTitle" style="font-size: 15px; font-weight: 600; color: white; margin: 0;">Add New Case</h2>
                </div>
                <button onclick="closeModal()" style="width: 28px; height: 28px; background: rgba(255,255,255,0.15); border: none; border-radius: 6px; color: rgba(255,255,255,0.9); font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
            </div>

            <form id="caseForm" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                <input type="hidden" id="caseId">

                <!-- Content Area (Scrollable) -->
                <div style="padding: 16px 20px; overflow-y: auto; flex: 1;">
                    <!-- Row 1: Client Name & Case Number -->
                    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Client Name</label>
                            <input type="text" id="clientName" required style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; outline: none;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Case Number</label>
                            <input type="text" id="caseNumber" required style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; outline: none;">
                        </div>
                    </div>

                    <!-- Row 2: Case Type & Resolution -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Case Type</label>
                            <select id="caseType" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;">
                                <option value="Auto Accident">Auto Accident</option>
                                <option value="Pedestrian">Pedestrian</option>
                                <option value="Motorcycle">Motorcycle</option>
                                <option value="Bicycle">Bicycle</option>
                                <option value="Dog Bite">Dog Bite</option>
                                <option value="Premise Liability">Premise Liability</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Resolution Type</label>
                            <select id="resolutionType" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;">
                                <option value="No Offer Settle">No Offer Settle</option>
                                <option value="File and Bump">File and Bump</option>
                                <option value="Post Deposition Settle">Post Deposition Settle</option>
                                <option value="Mediation">Mediation</option>
                                <option value="Settled Post Arbitration">Settled Post Arbitration</option>
                                <option value="Arbitration Award">Arbitration Award</option>
                                <option value="Beasley">Beasley</option>
                                <option value="Settlement Conference">Settlement Conference</option>
                                <option value="Non Litigation">Non Litigation</option>
                                <option value="Co-Counsel">Co-Counsel</option>
                                <option value="Ongoing Case">Ongoing Case</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 3: Year, Month & Fee Rate -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Year</label>
                            <select id="caseYear" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;"></select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Month</label>
                            <select id="caseMonth" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;"></select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Fee Rate</label>
                            <select id="feeRate" onchange="calculateFees()" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;">
                                <option value="33.33">1/3 (33.33%)</option>
                                <option value="40">40%</option>
                            </select>
                        </div>
                    </div>

                    <!-- Financial Details Section -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                            <div style="width: 26px; height: 26px; background: #0f4c81; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: white;">$</div>
                            <span style="font-size: 12px; font-weight: 600; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">Financial Details</span>
                        </div>

                        <!-- Settled & Pre-Suit -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Settled Amount</label>
                                <input type="number" step="0.01" id="settled" onchange="calculateFees()" required style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Pre-Suit Offer</label>
                                <input type="number" step="0.01" id="presuitOffer" onchange="calculateFees()" value="0" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none;">
                            </div>
                        </div>

                        <!-- Calculated fields -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Difference</label>
                                <input type="text" id="difference" readonly style="width: 100%; padding: 10px 12px; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 13px; color: #64748b; background: white; outline: none;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Legal Fee</label>
                                <input type="text" id="legalFee" readonly style="width: 100%; padding: 10px 12px; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 13px; color: #64748b; background: white; outline: none;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Disc. Legal Fee</label>
                                <input type="number" step="0.01" id="discountedLegalFee" onchange="calculateCommission()" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none;">
                            </div>
                        </div>

                        <!-- Commission Card -->
                        <div style="background: #18181b; border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 12px; font-weight: 500; color: rgba(255,255,255,0.9);">Your Commission (<?= $user['commission_rate'] ?>%)</span>
                            <span id="commission" style="font-size: 22px; font-weight: 700; color: #22d3ee;">$0.00</span>
                        </div>
                    </div>

                    <!-- Note -->
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Note</label>
                        <textarea id="note" rows="2" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; outline: none; resize: vertical;"></textarea>
                    </div>

                    <!-- Check Received -->
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="checkReceived" style="width: 16px; height: 16px; cursor: pointer;">
                        <label for="checkReceived" style="font-size: 13px; color: #374151; cursor: pointer;">Check Received</label>
                    </div>
                </div>

                <!-- Footer -->
                <div style="background: #f8fafc; padding: 12px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; align-items: center; flex-shrink: 0; gap: 10px;">
                    <button type="button" onclick="closeModal()" style="padding: 9px 16px; background: white; color: #64748b; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;">Cancel</button>
                    <button type="submit" style="padding: 9px 20px; background: #18181b; color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal-overlay" style="display: none;">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">Confirm Delete</h3>
                <button onclick="closeDeleteConfirmModal()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px; color: #374151; margin-bottom: 20px;">Are you sure you want to delete this notification? This action cannot be undone.</p>
                <div class="modal-actions">
                    <button onclick="confirmDeleteNotification()" class="btn-danger">Yes, Delete</button>
                    <button onclick="closeDeleteConfirmModal()" class="btn-secondary">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <?php if ($user['id'] == 2): // Chong only - Traffic Modal ?>
    <!-- Traffic Case Modal -->
    <div id="trafficModal" class="modal-overlay" onclick="if(event.target === this) closeTrafficModal()">
        <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 600px; max-height: 90vh; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; padding: 0;">
            <!-- Header -->
            <div style="background: #18181b; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 3px; height: 18px; background: #22d3ee; border-radius: 2px;"></div>
                    <h2 id="trafficModalTitle" style="font-size: 15px; font-weight: 600; color: white; margin: 0;">Add Traffic Case</h2>
                </div>
                <button onclick="closeTrafficModal()" style="width: 28px; height: 28px; background: rgba(255,255,255,0.15); border: none; border-radius: 6px; color: rgba(255,255,255,0.9); font-size: 18px; cursor: pointer;">&times;</button>
            </div>

            <!-- Content -->
            <form id="trafficForm" style="padding: 16px 20px; overflow-y: auto; flex: 1;">
                <input type="hidden" id="trafficCaseId">

                <!-- Row 1: Client Name & Phone -->
                <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Client Name</label>
                        <input type="text" id="trafficClientName" required style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Phone</label>
                        <input type="text" id="trafficClientPhone" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>

                <!-- Row 2: Court & Court Date -->
                <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Court</label>
                        <select id="trafficCourt" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="">Select Court</option>
                            <option value="KCDC - Issaquah">KCDC - Issaquah</option>
                            <option value="KCDC - Seattle">KCDC - Seattle</option>
                            <option value="KCDC - Shoreline">KCDC - Shoreline</option>
                            <option value="KCDC - Bellevue">KCDC - Bellevue</option>
                            <option value="KCDC - Burien">KCDC - Burien</option>
                            <option value="SCD - Everett">SCD - Everett</option>
                            <option value="SCD - South">SCD - South</option>
                            <option value="SCD - Cascade">SCD - Cascade</option>
                            <option value="SCD - Evergreen">SCD - Evergreen</option>
                            <option value="Lynnwood Muni">Lynnwood Muni</option>
                            <option value="Kent Municipal">Kent Municipal</option>
                            <option value="Mill Creek Violation Bureau">Mill Creek Violation Bureau</option>
                            <option value="Brier Violation Bureau">Brier Violation Bureau</option>
                            <option value="Issaquah Muni">Issaquah Muni</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Court Date</label>
                        <input type="datetime-local" id="trafficCourtDate" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>

                <!-- Row 3: Charge & Case Number -->
                <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Charge</label>
                        <select id="trafficCharge" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="">Select Charge</option>
                            <option value="speeding">Speeding</option>
                            <option value="phone while driving">Phone While Driving</option>
                            <option value="inattentive driving">Inattentive Driving</option>
                            <option value="fail to obey traffic device">Fail to Obey Traffic Device</option>
                            <option value="HOV violation">HOV Violation</option>
                            <option value="seatbelt">Seatbelt</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Case Number</label>
                        <input type="text" id="trafficCaseNumber" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>

                <!-- Row 4: Prosecutor Offer -->
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Prosecutor Offer</label>
                    <input type="text" id="trafficOffer" placeholder="e.g., DDS1 and dismiss" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                </div>

                <!-- Row 5: Disposition & Status -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Disposition</label>
                        <select id="trafficDisposition" onchange="updateTrafficCommission()" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="pending">Pending</option>
                            <option value="dismissed">Dismissed ($150)</option>
                            <option value="amended">Amended ($100)</option>
                            <option value="other">Other ($0)</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Status</label>
                        <select id="trafficStatus" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="active">Active</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                </div>

                <!-- Row 6: NOA Sent Date & Discovery -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">NOA Sent Date</label>
                        <input type="date" id="trafficNoaSentDate" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div style="display: flex; align-items: center; padding-top: 16px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="trafficDiscovery" style="width: 18px; height: 18px; cursor: pointer;">
                            <span style="font-size: 13px; font-weight: 500; color: #374151;">Discovery Received</span>
                        </label>
                    </div>
                </div>

                <!-- Row 7: Referral Source & Paid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Referral Source</label>
                        <select id="trafficReferralSource" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="">Select Referral</option>
                            <option value="Daniel">Daniel</option>
                            <option value="Dave">Dave</option>
                            <option value="Soyong">Soyong</option>
                            <option value="Jimi">Jimi</option>
                            <option value="Chloe">Chloe</option>
                            <option value="Chong">Chong</option>
                            <option value="Ella">Ella</option>
                            <option value="Office">Office</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div style="display: flex; align-items: center; padding-top: 16px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="trafficPaid" style="width: 18px; height: 18px; cursor: pointer;">
                            <span style="font-size: 13px; font-weight: 500; color: #374151;">Commission Paid</span>
                        </label>
                    </div>
                </div>

                <!-- Commission Display -->
                <div style="background: #18181b; border-radius: 8px; padding: 12px 16px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: rgba(255,255,255,0.8);">Commission</span>
                    <span id="trafficCommissionDisplay" style="font-size: 20px; font-weight: 700; color: #22d3ee;">$0.00</span>
                </div>

                <!-- Note -->
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Note</label>
                    <textarea id="trafficNote" rows="2" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; resize: vertical;"></textarea>
                </div>
            </form>

            <!-- Footer -->
            <div style="background: #f8fafc; padding: 12px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;">
                <button type="button" onclick="closeTrafficModal()" style="padding: 9px 14px; background: white; color: #64748b; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; cursor: pointer;">Cancel</button>
                <button type="button" onclick="saveTrafficCase()" style="padding: 9px 16px; background: #18181b; color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">Save</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        const USER = <?= json_encode($user) ?>;
        let allCases = [];
        let allMessages = [];
        let lastCheckedTime = new Date().toISOString();
        let sortColumn = null;
        let sortDirection = 'asc';
        let currentStatusFilter = 'all';
        let notifCurrentFilter = 'all';
        let csrfToken = '<?= generateCSRFToken() ?>';

        // Default widths for each tab (Employee page)
        const TAB_DEFAULT_WIDTHS = {
            'cases': '100',
            'reports': '100',
            'history': '100',
            'notifications': '100',
            'goals': '100',
            'traffic': '100'
        };

        // Traffic cases data
        let allTrafficCases = [];

        // Escape HTML helper
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // API call helper with CSRF token
        async function apiCall(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                }
            };

            const mergedOptions = {
                ...defaultOptions,
                ...options,
                headers: {
                    ...defaultOptions.headers,
                    ...(options.headers || {})
                }
            };

            const response = await fetch(url, mergedOptions);
            const data = await response.json();

            // Update CSRF token if returned
            if (data.csrf_token) {
                csrfToken = data.csrf_token;
            }

            if (!response.ok) {
                throw new Error(data.error || 'API call failed');
            }

            return data;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            initMonthDropdowns();
            loadCases();
            checkNotifications();
            // Check for new notifications every 30 seconds
            setInterval(checkNotifications, 30000);
            // Set initial width for cases tab (first tab) - 100%
            setWidth(TAB_DEFAULT_WIDTHS['cases'] || '100');
        });

        // Width control functions
        function setWidth(width) {
            const container = document.getElementById('mainContent');
            if (container) {
                container.className = 'page-content w-' + width;
            }

            // Update active button
            document.querySelectorAll('.sz-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.getAttribute('data-width') === width) {
                    btn.classList.add('active');
                }
            });

            // Save preference
            localStorage.setItem('dashboardWidth', width);
        }

        function loadWidthPreference() {
            const savedWidth = localStorage.getItem('dashboardWidth') || '100';
            setWidth(savedWidth);
        }

        // Page title mapping for sidebar navigation
        const pageTitles = {
            'cases': 'My Cases',
            'reports': 'Reports',
            'history': 'History',
            'notifications': 'Notifications',
            'goals': 'My Goals',
            'traffic': 'Traffic Cases',
            'traffic-requests': 'Traffic Requests'
        };

        function switchTab(tab) {
            // Hide all content
            document.querySelectorAll('[id^="content-"]').forEach(el => el.classList.add('hidden'));
            // Remove active from all nav links
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));

            // Show selected content
            document.getElementById(`content-${tab}`).classList.remove('hidden');
            // Activate nav link
            const navLink = document.querySelector(`.nav-link[data-tab="${tab}"]`);
            if (navLink) navLink.classList.add('active');

            // Update page title
            document.getElementById('pageTitle').textContent = pageTitles[tab] || tab;

            // Set default width for this tab
            const defaultWidth = TAB_DEFAULT_WIDTHS[tab] || '100';
            setWidth(defaultWidth);

            // Load data for tab
            if (tab === 'reports') {
                initReportDropdowns();
                generateReport();
            } else if (tab === 'history') {
                loadHistory();
            } else if (tab === 'notifications') {
                loadNotifications();
            } else if (tab === 'traffic') {
                initTrafficYearFilter();
                loadTrafficCases();
            } else if (tab === 'goals') {
                initMyGoalsYearFilter();
                loadMyGoals();
            } else if (tab === 'traffic-requests') {
                loadEmpTrafficRequests();
            }
        }

        // Sidebar navigation click handlers
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const tabName = this.dataset.tab;
                if (tabName) {
                    switchTab(tabName);
                }
            });
        });

        function initMonthDropdowns() {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const currentYear = new Date().getFullYear();
            const currentMonth = new Date().getMonth();

            // Case form dropdowns (separate Year and Month)
            const caseYear = document.getElementById('caseYear');
            const caseMonth = document.getElementById('caseMonth');

            // Year options (2021 ~ current year + 2 years)
            for (let y = 2021; y <= currentYear + 2; y++) {
                caseYear.innerHTML += `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`;
            }

            // Month options
            months.forEach((m, i) => {
                caseMonth.innerHTML += `<option value="${m}" ${i === currentMonth ? 'selected' : ''}>${m}</option>`;
            });
            caseMonth.innerHTML += `<option value="TBD">TBD</option>`;

            // Filter dropdown - month abbreviation
            const filterMonth = document.getElementById('filterMonth');
            filterMonth.innerHTML = '<option value="all">All Month</option>';
            months.forEach(m => {
                filterMonth.innerHTML += `<option value="${m}">${m}</option>`;
            });
        }

        function initYearFilter() {
            const filterYear = document.getElementById('filterYear');
            const currentYear = new Date().getFullYear();

            // Get unique years from cases data
            const years = [...new Set(allCases.map(c => {
                const match = (c.month || '').match(/\d{4}/);
                return match ? parseInt(match[0]) : null;
            }).filter(Boolean))];

            // Always include current year
            if (!years.includes(currentYear)) {
                years.push(currentYear);
            }

            // Sort descending (newest first)
            years.sort((a, b) => b - a);

            filterYear.innerHTML = '<option value="all">All</option>';
            years.forEach(y => {
                filterYear.innerHTML += `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`;
            });
        }


        async function loadCases() {
            try {
                const data = await apiCall('api/cases.php');
                allCases = data.cases || [];
                initYearFilter();
                renderCases();
                updateStats();
            } catch (err) {
                console.error('Error loading cases:', err);
            }
        }

        function sortCases(column) {
            // Toggle sort direction if clicking the same column
            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = column;
                sortDirection = 'asc';
            }

            // Update sort arrow indicators
            document.querySelectorAll('#casesTable .sort-arrow').forEach(arrow => {
                arrow.className = 'sort-arrow';
            });

            // Find the clicked header's arrow
            const clickedHeader = event.target.closest('th');
            if (clickedHeader) {
                const arrow = clickedHeader.querySelector('.sort-arrow');
                if (arrow) {
                    arrow.className = `sort-arrow ${sortDirection}`;
                }
            }

            renderCases();
        }

        function setStatusFilter(status, btn) {
            currentStatusFilter = status;
            document.querySelectorAll('#content-cases .f-chip[id^="statusChip-"]').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            renderCases();
        }

        function renderCases() {
            const year = document.getElementById('filterYear').value;
            const monthFilter = document.getElementById('filterMonth').value;
            const search = document.getElementById('searchInput').value.toLowerCase();

            let filtered = allCases;

            // Status filter from chips
            if (currentStatusFilter !== 'all') {
                filtered = filtered.filter(c => c.status === currentStatusFilter);
            }

            // Year filter
            if (year !== 'all') {
                filtered = filtered.filter(c => (c.month || '').includes(year));
            }

            // Month filter (match month abbreviation like "Jan", "Feb")
            if (monthFilter !== 'all') {
                filtered = filtered.filter(c => c.month && c.month.startsWith(monthFilter));
            }

            // Search
            if (search) {
                filtered = filtered.filter(c =>
                    (c.client_name || '').toLowerCase().includes(search) ||
                    (c.case_number || '').toLowerCase().includes(search) ||
                    (c.resolution_type || '').toLowerCase().includes(search)
                );
            }

            // Apply sorting
            if (sortColumn) {
                filtered.sort((a, b) => {
                    let aVal = a[sortColumn];
                    let bVal = b[sortColumn];

                    if (aVal == null) aVal = '';
                    if (bVal == null) bVal = '';

                    if (['settled', 'presuit_offer', 'difference', 'legal_fee', 'discounted_legal_fee', 'commission'].includes(sortColumn)) {
                        aVal = parseFloat(aVal) || 0;
                        bVal = parseFloat(bVal) || 0;
                    } else {
                        aVal = String(aVal).toLowerCase();
                        bVal = String(bVal).toLowerCase();
                    }

                    if (aVal < bVal) return sortDirection === 'asc' ? -1 : 1;
                    if (aVal > bVal) return sortDirection === 'asc' ? 1 : -1;
                    return 0;
                });
            }

            const tbody = document.getElementById('casesBody');

            // Calculate stats for filtered data
            const totalComm = filtered.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);
            const paidComm = filtered.filter(c => c.status === 'paid').reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);
            const unpaidComm = filtered.filter(c => c.status === 'unpaid').reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);

            // Update stats cards
            document.getElementById('totalCases').textContent = filtered.length;
            document.getElementById('totalCommission').textContent = formatCurrency(totalComm);
            document.getElementById('paidCommission').textContent = formatCurrency(paidComm);
            document.getElementById('unpaidCommission').textContent = formatCurrency(unpaidComm);

            // Update footer
            document.getElementById('footerInfo').textContent = `${filtered.length} cases`;
            document.getElementById('footerTotal').textContent = formatCurrency(totalComm);
            document.getElementById('footerPaid').textContent = formatCurrency(paidComm);
            document.getElementById('footerUnpaid').textContent = formatCurrency(unpaidComm);

            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="12" style="text-align:center; padding: 40px; color: #8b8fa3;">No cases found</td></tr>';
                return;
            }

            tbody.innerHTML = filtered.map(c => {
                const statusBadge = c.status === 'paid'
                    ? '<span class="stat-badge paid">PAID</span>'
                    : c.status === 'rejected'
                    ? '<span class="stat-badge" style="background:#fee2e2;color:#991b1b;">REJECTED</span>'
                    : '<span class="stat-badge unpaid">UNPAID</span>';

                const settled = parseFloat(c.settled || 0);
                const presuitOffer = parseFloat(c.presuit_offer || 0);
                const difference = parseFloat(c.difference || 0);
                const legalFee = parseFloat(c.legal_fee || 0);
                const discFee = parseFloat(c.discounted_legal_fee || 0);
                const commission = parseFloat(c.commission || 0);

                // Resolution type color dot
                const resType = c.resolution_type || '-';
                let dotColor = '#94a3b8';
                if (resType.toLowerCase().includes('demand')) dotColor = '#3b82f6';
                else if (resType.toLowerCase().includes('mediation')) dotColor = '#d97706';
                else if (resType.toLowerCase().includes('arb')) dotColor = '#8b5cf6';
                else if (resType.toLowerCase().includes('trial')) dotColor = '#dc2626';

                const canEdit = c.status === 'in_progress' || c.status === 'unpaid';

                return `
                    <tr onclick="viewCaseDetail(${c.id})" style="cursor:pointer;">
                        <td style="width:0;padding:0;border:none;"></td>
                        <td><span style="display:inline-block;width:8px;height:8px;background:${dotColor};border-radius:50%;margin-right:6px;"></span>${escapeHtml(resType)}</td>
                        <td>${escapeHtml(c.client_name)}</td>
                        <td class="r" style="font-weight:600;">${formatCurrency(settled)}</td>
                        <td class="r" style="color:#8b8fa3;">${presuitOffer > 0 ? formatCurrency(presuitOffer) : '—'}</td>
                        <td class="r">${difference > 0 ? formatCurrency(difference) : '—'}</td>
                        <td class="r">${formatCurrency(legalFee)}</td>
                        <td class="r">${formatCurrency(discFee)}</td>
                        <td class="r" style="font-weight:700; color:#0d9488;">${formatCurrency(commission)}</td>
                        <td>${escapeHtml(c.month || '-')}</td>
                        <td class="c">${statusBadge}</td>
                        <td class="c">
                            <div style="display:flex; gap:4px; justify-content:center;">
                                ${canEdit ? `<button class="act-link" onclick="event.stopPropagation(); editCase(${c.id})" title="Edit" style="padding:4px 6px;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>` : ''}
                                ${canEdit ? `<button class="act-link danger" onclick="event.stopPropagation(); deleteCase(${c.id})" title="Delete" style="padding:4px 6px;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function updateStats() {
            // Stats are now updated inside renderCases()
        }

        function filterTable() {
            renderCases();
        }

        function showAddForm() {
            document.getElementById('modalTitle').textContent = 'Add New Case';
            document.getElementById('caseForm').reset();
            document.getElementById('caseId').value = '';
            document.getElementById('caseModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('caseModal').classList.remove('show');
        }

        function viewCaseDetail(id) {
            const c = allCases.find(x => x.id == id);
            if (!c) return;

            const statusBadge = {
                'in_progress': '<span style="display: inline-flex; align-items: center; padding: 4px 10px; background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 4px; font-size: 11px; font-weight: 600;"><span style="width: 6px; height: 6px; background: #1e40af; border-radius: 50%; margin-right: 6px;"></span>In Progress</span>',
                'unpaid': '<span style="display: inline-flex; align-items: center; padding: 4px 10px; background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; border-radius: 4px; font-size: 11px; font-weight: 600;"><span style="width: 6px; height: 6px; background: #92400e; border-radius: 50%; margin-right: 6px;"></span>Unpaid</span>',
                'paid': '<span style="display: inline-flex; align-items: center; padding: 4px 10px; background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; border-radius: 4px; font-size: 11px; font-weight: 600;"><span style="width: 6px; height: 6px; background: #065f46; border-radius: 50%; margin-right: 6px;"></span>Paid</span>',
                'rejected': '<span style="display: inline-flex; align-items: center; padding: 4px 10px; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; border-radius: 4px; font-size: 11px; font-weight: 600;"><span style="width: 6px; height: 6px; background: #991b1b; border-radius: 50%; margin-right: 6px;"></span>Rejected</span>'
            };

            const content = document.getElementById('caseDetailContent');
            content.innerHTML = `
                <!-- Case Information Section -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Client Name</div>
                        <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; font-weight: 500;">${c.client_name}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Case Number</div>
                        <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; font-weight: 500;">${c.case_number}</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Case Type</div>
                        <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${c.case_type || '-'}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Resolution</div>
                        <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${c.resolution_type || '-'}</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div>
                        <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Month</div>
                        <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${c.month}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Fee Rate</div>
                        <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${c.fee_rate || '-'}%</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Status</div>
                        <div style="padding: 6px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">${statusBadge[c.status] || c.status}</div>
                    </div>
                </div>

                <!-- Financial Details Section -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                        <div style="width: 26px; height: 26px; background: #0f172a; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px;">💰</div>
                        <span style="font-size: 12px; font-weight: 600; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">Financial Details</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Settled Amount</div>
                            <div style="padding: 8px 12px; background: white; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; font-weight: 600;">${formatCurrency(c.settled)}</div>
                        </div>
                        <div>
                            <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Pre-Suit Offer</div>
                            <div style="padding: 8px 12px; background: white; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${formatCurrency(c.presuit_offer || 0)}</div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Difference</div>
                            <div style="padding: 8px 12px; background: white; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 13px; color: #64748b;">${formatCurrency(c.difference || 0)}</div>
                        </div>
                        <div>
                            <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Legal Fee</div>
                            <div style="padding: 8px 12px; background: white; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 13px; color: #64748b;">${formatCurrency(c.legal_fee || 0)}</div>
                        </div>
                        <div>
                            <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Disc. Legal Fee</div>
                            <div style="padding: 8px 12px; background: white; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; font-weight: 600;">${formatCurrency(c.discounted_legal_fee || 0)}</div>
                        </div>
                    </div>

                    <!-- Commission Card -->
                    <div style="background: #18181b; border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; position: relative; overflow: hidden;">
                        <div style="position: absolute; right: 0; top: 0; bottom: 0; width: 80px; background: linear-gradient(135deg, rgba(34, 211, 238, 0.1), rgba(168, 85, 247, 0.1));"></div>
                        <div>
                            <span style="font-size: 11px; font-weight: 500; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.5px;">Commission</span>
                            <div style="font-size: 11px; color: rgba(255,255,255,0.5); margin-top: 2px;">${c.check_received == 1 ? '✓ Check Received' : '⏳ Check Pending'}</div>
                        </div>
                        <span style="font-size: 22px; font-weight: 700; color: #22d3ee; position: relative; z-index: 1;">${formatCurrency(c.commission)}</span>
                    </div>
                </div>

                <!-- Note -->
                ${c.note ? `
                <div>
                    <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Note</div>
                    <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${c.note}</div>
                </div>
                ` : ''}
            `;

            // Show Edit button only for in_progress or unpaid cases
            const editBtn = document.getElementById('editCaseDetailBtn');
            if (c.status === 'in_progress' || c.status === 'unpaid') {
                editBtn.style.display = 'inline-block';
                window.currentDetailCaseId = c.id;
            } else {
                editBtn.style.display = 'none';
                window.currentDetailCaseId = null;
            }

            document.getElementById('caseDetailModal').classList.add('show');
        }

        function editCaseFromDetail() {
            const caseId = window.currentDetailCaseId;
            if (!caseId) return;

            closeCaseDetail();
            editCase(caseId);
        }

        function calculateFees() {
            const settled = parseFloat(document.getElementById('settled').value) || 0;
            const presuit = parseFloat(document.getElementById('presuitOffer').value) || 0;
            const feeRate = parseFloat(document.getElementById('feeRate').value);
            
            const base = USER.uses_presuit_offer ? (settled - presuit) : settled;
            const difference = settled - presuit;
            const legalFee = feeRate === 33.33 ? (base / 3) : (base * 0.4);
            
            document.getElementById('difference').value = formatCurrency(difference);
            document.getElementById('legalFee').value = formatCurrency(legalFee);
            document.getElementById('discountedLegalFee').value = legalFee.toFixed(2);
            
            calculateCommission();
        }

        function calculateCommission() {
            const discLegalFee = parseFloat(document.getElementById('discountedLegalFee').value) || 0;
            const commission = discLegalFee * (USER.commission_rate / 100);
            document.getElementById('commission').textContent = formatCurrency(commission);
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0);
        }

        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function formatRelativeTime(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
            if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
            if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
            return formatDate(dateString);
        }

        // Form submission
        document.getElementById('caseForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const caseId = document.getElementById('caseId').value;
            const caseMonthVal = document.getElementById('caseMonth').value;
            const caseYearVal = document.getElementById('caseYear').value;
            const monthValue = caseMonthVal === 'TBD' ? 'TBD' : `${caseMonthVal}. ${caseYearVal}`;
            const data = {
                case_type: document.getElementById('caseType').value,
                resolution_type: document.getElementById('resolutionType').value,
                case_number: document.getElementById('caseNumber').value,
                client_name: document.getElementById('clientName').value,
                month: monthValue,
                fee_rate: parseFloat(document.getElementById('feeRate').value),
                settled: parseFloat(document.getElementById('settled').value) || 0,
                presuit_offer: parseFloat(document.getElementById('presuitOffer').value) || 0,
                discounted_legal_fee: parseFloat(document.getElementById('discountedLegalFee').value) || 0,
                note: document.getElementById('note').value,
                check_received: document.getElementById('checkReceived').checked
            };
            
            if (caseId) {
                data.id = parseInt(caseId);
            }
            
            try {
                const result = await apiCall('api/cases.php', {
                    method: caseId ? 'PUT' : 'POST',
                    body: JSON.stringify(data)
                });

                if (result.success) {
                    closeModal();
                    loadCases();
                } else {
                    alert(result.error || 'Error saving case');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error saving case');
            }
        });

        function editCase(id) {
            const c = allCases.find(x => x.id == id);
            if (!c) return;
            
            document.getElementById('modalTitle').textContent = 'Edit Case';
            document.getElementById('caseId').value = c.id;
            document.getElementById('caseType').value = c.case_type;
            document.getElementById('resolutionType').value = c.resolution_type;
            document.getElementById('caseNumber').value = c.case_number;
            document.getElementById('clientName').value = c.client_name;

            // Parse month value (e.g., "Dec. 2025" or "TBD")
            if (c.month === 'TBD') {
                document.getElementById('caseMonth').value = 'TBD';
                document.getElementById('caseYear').value = new Date().getFullYear();
            } else {
                const parts = c.month.split('. ');
                document.getElementById('caseMonth').value = parts[0];
                document.getElementById('caseYear').value = parts[1];
            }
            document.getElementById('feeRate').value = c.fee_rate;
            document.getElementById('settled').value = c.settled;
            document.getElementById('presuitOffer').value = c.presuit_offer;
            document.getElementById('discountedLegalFee').value = c.discounted_legal_fee;
            document.getElementById('note').value = c.note || '';
            document.getElementById('checkReceived').checked = c.check_received == 1;
            
            calculateFees();
            
            document.getElementById('caseModal').classList.add('show');
        }

        async function deleteCase(id) {
            if (!confirm('Are you sure you want to delete this case?')) return;

            try {
                const result = await apiCall(`api/cases.php?id=${id}`, { method: 'DELETE' });

                if (result.success) {
                    loadCases();
                } else {
                    alert(result.error || 'Error deleting case');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error deleting case');
            }
        }

        function exportToExcel() {
            // Export currently filtered data
            const year = document.getElementById('filterYear').value;
            const monthFilter = document.getElementById('filterMonth').value;
            let filtered = allCases;

            if (currentStatusFilter !== 'all') {
                filtered = filtered.filter(c => c.status === currentStatusFilter);
            }
            if (year !== 'all') {
                filtered = filtered.filter(c => (c.month || '').includes(year));
            }
            if (monthFilter !== 'all') {
                filtered = filtered.filter(c => c.month && c.month.startsWith(monthFilter));
            }

            const data = filtered.map(c => ({
                'Resolution Type': c.resolution_type || '',
                'Client': c.client_name,
                'Settled': c.settled || 0,
                'Pre-Suit Offer': c.presuit_offer || 0,
                'Difference': c.difference || 0,
                'Legal Fee': c.legal_fee || 0,
                'Disc. Legal Fee': c.discounted_legal_fee || 0,
                'Commission': c.commission || 0,
                'Month': c.month || '',
                'Status': c.status
            }));

            if (data.length === 0) {
                alert('No data to export');
                return;
            }

            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'My Cases');
            XLSX.writeFile(wb, `my-cases-${new Date().toISOString().split('T')[0]}.xlsx`);
        }

        // Reports
        function initReportDropdowns() {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const currentYear = new Date().getFullYear();
            const reportMonth = document.getElementById('reportMonth');
            const reportYear = document.getElementById('reportYear');

            // Add years (2021 to current year), current year selected by default
            for (let y = currentYear; y >= 2021; y--) {
                reportYear.innerHTML += `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`;
            }

            // Add months (month name only)
            months.forEach(m => {
                reportMonth.innerHTML += `<option value="${m}">${m}</option>`;
            });
        }

        function generateReport() {
            const type = document.getElementById('reportType').value;
            const reportMonthSelect = document.getElementById('reportMonth');

            if (!type) {
                document.getElementById('reportContent').innerHTML = '<p style="text-align: center; color: #94a3b8; padding: 48px;">Select a report type to generate</p>';
                return;
            }

            // Show/hide month filter based on report type
            if (type === 'monthly') {
                reportMonthSelect.style.display = 'block';
            } else {
                reportMonthSelect.style.display = 'none';
            }

            const paid = allCases.filter(c => c.status === 'paid');
            let filtered = paid;
            let title = '';
            const year = document.getElementById('reportYear').value;

            if (type === 'monthly') {
                const month = document.getElementById('reportMonth').value;
                filtered = paid.filter(c => c.month.includes(year));

                if (month !== 'all') {
                    const fullMonth = `${month}. ${year}`;
                    filtered = filtered.filter(c => c.month === fullMonth);
                    title = fullMonth;
                } else {
                    title = `${year} All Months`;
                }
            } else if (type === 'yearly') {
                filtered = paid.filter(c => c.month.includes(year));
                title = `Year ${year}`;
            }

            if (filtered.length === 0) {
                document.getElementById('reportContent').innerHTML = `
                    <div style="text-align: center; padding: 80px 40px;">
                        <div style="width: 64px; height: 64px; background: #f1f5f9; border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center;">
                            <svg width="32" height="32" fill="none" stroke="#94a3b8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p style="color: #64748b; font-size: 14px;">No data available for this period</p>
                    </div>
                `;
                return;
            }

            // === Calculate Stats ===
            const totalCommission = filtered.reduce((sum, c) => sum + parseFloat(c.commission), 0);
            const totalCases = filtered.length;
            const avgCommission = totalCommission / totalCases;
            const totalSettled = filtered.reduce((sum, c) => sum + parseFloat(c.settled), 0);

            // Status Analysis
            const paidCount = filtered.filter(c => c.status === 'paid').length;
            const pendingCount = filtered.filter(c => c.status === 'unpaid').length;
            const checkReceivedCount = filtered.filter(c => c.check_received == 1).length;
            const checkPendingCount = filtered.filter(c => c.check_received == 0).length;

            // Case Type Analysis
            const byCaseType = {};
            filtered.forEach(c => {
                const ctype = c.case_type || 'Unknown';
                if (!byCaseType[ctype]) byCaseType[ctype] = { count: 0, commission: 0 };
                byCaseType[ctype].count++;
                byCaseType[ctype].commission += parseFloat(c.commission);
            });

            // Resolution Type Analysis
            const byResolution = {};
            filtered.forEach(c => {
                const res = c.resolution_type || 'Unknown';
                if (!byResolution[res]) byResolution[res] = { count: 0, settled: 0 };
                byResolution[res].count++;
                byResolution[res].settled += parseFloat(c.settled);
            });

            // Fee Rate Analysis
            const byFeeRate = {};
            filtered.forEach(c => {
                const rate = c.fee_rate + '%';
                if (!byFeeRate[rate]) byFeeRate[rate] = { count: 0, commission: 0 };
                byFeeRate[rate].count++;
                byFeeRate[rate].commission += parseFloat(c.commission);
            });

            // Group by Month
            const byMonth = {};
            filtered.forEach(c => {
                if (!byMonth[c.month]) {
                    byMonth[c.month] = { count: 0, commission: 0 };
                }
                byMonth[c.month].count++;
                byMonth[c.month].commission += parseFloat(c.commission);
            });

            // Sort months chronologically
            const sortedMonths = Object.entries(byMonth).sort((a, b) => {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const aMonth = a[0].split('. ')[0];
                const bMonth = b[0].split('. ')[0];
                const aYear = a[0].split('. ')[1] || '2025';
                const bYear = b[0].split('. ')[1] || '2025';
                if (aYear !== bYear) return aYear - bYear;
                return months.indexOf(aMonth) - months.indexOf(bMonth);
            });

            const maxCommission = Math.max(...sortedMonths.map(([, d]) => d.commission));

            // Case type colors
            const caseTypeColors = {
                'MVA': '#2563eb',
                'Slip & Fall': '#7c3aed',
                'Dog Bite': '#dc2626',
                'Wrongful Death': '#1e293b',
                'Medical Malpractice': '#0891b2',
                'Product Liability': '#ea580c',
                'Unknown': '#94a3b8'
            };

            // Build HTML with Ink Compact Design
            let html = `
                <!-- Quick Stats -->
                <div class="quick-stats" style="margin-bottom: 20px;">
                    <div class="qs-card">
                        <span class="qs-label">Total Cases</span>
                        <span class="qs-val">${totalCases}</span>
                    </div>
                    <div class="qs-card">
                        <span class="qs-label">Total Commission</span>
                        <span class="qs-val green">${formatCurrency(totalCommission)}</span>
                    </div>
                    <div class="qs-card">
                        <span class="qs-label">Avg / Case</span>
                        <span class="qs-val">${formatCurrency(avgCommission)}</span>
                    </div>
                    <div class="qs-card">
                        <span class="qs-label">Paid / Pending</span>
                        <span class="qs-val">${paidCount} <span style="color:#8b8fa3; font-size:14px;">/ ${pendingCount}</span></span>
                    </div>
                </div>

                <!-- Charts Row -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="tbl-container">
                        <div class="tbl-header">
                            <span class="tbl-title">Monthly Commission</span>
                        </div>
                        <div style="padding: 16px;">
                            <canvas id="monthlyChart" height="200"></canvas>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div class="tbl-container" style="flex: 1;">
                            <div class="tbl-header"><span class="tbl-title">Payment Status</span></div>
                            <div style="padding: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div style="background: #d1fae5; border-radius: 8px; padding: 12px; text-align: center;">
                                    <div style="font-size: 22px; font-weight: 700; color: #065f46;">${paidCount}</div>
                                    <div style="font-size: 11px; color: #065f46; margin-top: 2px;">Paid</div>
                                </div>
                                <div style="background: #fef3c7; border-radius: 8px; padding: 12px; text-align: center;">
                                    <div style="font-size: 22px; font-weight: 700; color: #b45309;">${pendingCount}</div>
                                    <div style="font-size: 11px; color: #b45309; margin-top: 2px;">Pending</div>
                                </div>
                            </div>
                        </div>
                        <div class="tbl-container" style="flex: 1;">
                            <div class="tbl-header"><span class="tbl-title">Check Status</span></div>
                            <div style="padding: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div style="background: #dbeafe; border-radius: 8px; padding: 12px; text-align: center;">
                                    <div style="font-size: 22px; font-weight: 700; color: #1e40af;">${checkReceivedCount}</div>
                                    <div style="font-size: 11px; color: #1e40af; margin-top: 2px;">Received</div>
                                </div>
                                <div style="background: #f3f4f6; border-radius: 8px; padding: 12px; text-align: center;">
                                    <div style="font-size: 22px; font-weight: 700; color: #6b7280;">${checkPendingCount}</div>
                                    <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">Pending</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Row -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="tbl-container">
                        <div class="tbl-header"><span class="tbl-title">By Case Type</span></div>
                        <div style="padding: 16px; display: flex; flex-direction: column; gap: 10px;">
                            ${Object.entries(byCaseType).sort((a, b) => b[1].commission - a[1].commission).map(([ctype, data]) => {
                                const color = caseTypeColors[ctype] || '#8b8fa3';
                                return `
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 10px; height: 10px; background: ${color}; border-radius: 3px; flex-shrink: 0;"></div>
                                        <span style="flex: 1; font-size: 13px; color: #1a1a2e;">${ctype}</span>
                                        <span style="font-size: 12px; color: #8b8fa3;">${data.count} cases</span>
                                        <span style="font-size: 13px; font-weight: 700; color: #0d9488;">${formatCurrency(data.commission)}</span>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                    <div class="tbl-container">
                        <div class="tbl-header"><span class="tbl-title">Monthly Breakdown</span></div>
                        <table class="excel-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th style="text-align: center;">Cases</th>
                                    <th style="text-align: right;">Commission</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${sortedMonths.slice().reverse().map(([month, data]) => `
                                    <tr>
                                        <td>${month}</td>
                                        <td style="text-align: center;">${data.count}</td>
                                        <td style="text-align: right; font-weight: 600; color: #0d9488;">${formatCurrency(data.commission)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;

            document.getElementById('reportContent').innerHTML = html;

            // Render Chart with Design Guide colors
            const ctx = document.getElementById('monthlyChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: sortedMonths.map(([m]) => m.split('. ')[0]),
                        datasets: [{
                            label: 'Commission',
                            data: sortedMonths.map(([, d]) => d.commission),
                            backgroundColor: '#1a1a2e',
                            borderRadius: { topLeft: 4, topRight: 4 },
                            barThickness: 24
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleFont: { size: 12 },
                                bodyFont: { size: 13, family: 'Outfit' },
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        return '$' + context.parsed.y.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: 11 },
                                    color: '#94a3b8'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { size: 11, family: 'JetBrains Mono' },
                                    color: '#94a3b8',
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // History
        function loadHistory() {
            const filter = document.getElementById('historyFilter')?.value || 'all';

            let paid = allCases.filter(c => c.status === 'paid');

            // Apply filter
            if (filter === 'year') {
                const currentYear = new Date().getFullYear();
                paid = paid.filter(c => {
                    const year = parseInt(c.month.split('. ')[1]);
                    return year === currentYear;
                });
            } else if (filter === 'month') {
                const currentMonth = new Date().getMonth();
                const currentYear = new Date().getFullYear();
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const currentMonthStr = `${monthNames[currentMonth]}. ${currentYear}`;
                paid = paid.filter(c => c.month === currentMonthStr);
            }

            // Sort by reviewed_at or submitted_at, newest first
            paid.sort((a, b) => {
                const aDate = a.reviewed_at || a.submitted_at;
                const bDate = b.reviewed_at || b.submitted_at;
                return new Date(bDate) - new Date(aDate);
            });

            const content = document.getElementById('historyContent');

            if (paid.length === 0) {
                content.innerHTML = '<p style="text-align: center; color: #8b8fa3; padding: 32px; font-size: 12px; font-family: Outfit, sans-serif;">No paid cases yet</p>';
                return;
            }

            const totalCommission = paid.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);

            const formatPaymentDate = (dateStr) => {
                if (!dateStr) return '<span class="mute">-</span>';
                const date = new Date(dateStr);
                return date.toLocaleString('en-US', { month: 'short', day: 'numeric', year: '2-digit' });
            };

            // Group cases by month
            const casesByMonth = {};
            paid.forEach(c => {
                const monthKey = c.month || 'Unknown';
                if (!casesByMonth[monthKey]) casesByMonth[monthKey] = [];
                casesByMonth[monthKey].push(c);
            });

            // Sort months from newest to oldest
            const sortedMonths = Object.keys(casesByMonth).sort((a, b) => {
                const parseMonth = (monthStr) => {
                    if (!monthStr || monthStr === 'Unknown') return new Date(0);
                    const parts = monthStr.split('. ');
                    if (parts.length !== 2) return new Date(0);
                    const [monthAbbr, year] = parts;
                    const monthMap = {'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'May': 4, 'Jun': 5, 'Jul': 6, 'Aug': 7, 'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11};
                    return new Date(parseInt(year), monthMap[monthAbbr] || 0);
                };
                return parseMonth(b) - parseMonth(a);
            });

            let tableHtml = `
                <table class="tbl" style="table-layout: auto;">
                    <thead>
                        <tr>
                            <th>Case #</th>
                            <th>Client</th>
                            <th>Resolution</th>
                            <th>Status</th>
                            <th class="r">Commission</th>
                            <th>Paid Date</th>
                            <th class="c">Check</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            sortedMonths.forEach(monthKey => {
                const cases = casesByMonth[monthKey];
                const caseCount = cases.length;
                const monthTotal = cases.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);

                tableHtml += `
                    <tr style="background: #f0f1f3;">
                        <td colspan="7" style="padding: 10px 12px; font-weight: 700; font-size: 12px; color: #1a1a2e; font-family: 'Outfit', sans-serif;">
                            ${monthKey}
                            <span style="float: right; color: #0d9488; font-size: 11px;">
                                ${caseCount} case${caseCount !== 1 ? 's' : ''} · $${monthTotal.toLocaleString('en-US', {minimumFractionDigits: 2})}
                            </span>
                        </td>
                    </tr>
                `;

                cases.forEach(c => {
                    tableHtml += `
                        <tr onclick="viewCaseDetail(${c.id})" style="cursor:pointer;">
                            <td style="font-weight: 600;">${c.case_number}</td>
                            <td>${c.client_name}</td>
                            <td style="font-size:11px;">${c.resolution_type || '-'}</td>
                            <td><span class="stat-badge paid">Paid</span></td>
                            <td class="r em">${formatCurrency(c.commission || 0)}</td>
                            <td>${formatPaymentDate(c.reviewed_at || c.submitted_at)}</td>
                            <td class="c">${c.check_received ? '<span style="display:inline-block;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:700;background:#d1fae5;color:#065f46;">Received</span>' : '<span style="display:inline-block;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:700;background:#fef3c7;color:#92400e;">Pending</span>'}</td>
                        </tr>
                    `;
                });
            });

            tableHtml += `</tbody></table>`;

            tableHtml += `
                <div class="tbl-foot">
                    <div class="left">${paid.length} case${paid.length !== 1 ? 's' : ''}</div>
                    <div class="right">
                        <div class="ft"><span class="ft-l">Total:</span><span class="ft-v green">$${totalCommission.toLocaleString('en-US', {minimumFractionDigits: 2})}</span></div>
                    </div>
                </div>
            `;

            content.innerHTML = tableHtml;
        }

        // Export history function
        function exportHistory() {
            const filter = document.getElementById('historyFilter')?.value || 'all';

            let paid = allCases.filter(c => c.status === 'paid');

            // Apply same filter as loadHistory
            if (filter === 'year') {
                const currentYear = new Date().getFullYear();
                paid = paid.filter(c => {
                    const year = parseInt(c.month.split('. ')[1]);
                    return year === currentYear;
                });
            } else if (filter === 'month') {
                const currentMonth = new Date().getMonth();
                const currentYear = new Date().getFullYear();
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const currentMonthStr = `${monthNames[currentMonth]}. ${currentYear}`;
                paid = paid.filter(c => c.month === currentMonthStr);
            }

            paid.sort((a, b) => {
                const aDate = a.reviewed_at || a.submitted_at;
                const bDate = b.reviewed_at || b.submitted_at;
                return new Date(bDate) - new Date(aDate);
            });

            if (paid.length === 0) {
                alert('No data to export');
                return;
            }

            // Create CSV content
            const headers = ['Case #', 'Client', 'Month', 'Status', 'Payment Date', 'Commission'];
            const rows = paid.map(c => [
                c.case_number,
                c.client_name,
                c.month,
                'Paid',
                c.reviewed_at || c.submitted_at,
                c.commission
            ]);

            const csvContent = [
                headers.join(','),
                ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
            ].join('\n');

            // Download CSV
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `commission-history-${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        // Notifications
        async function checkNotifications() {
            try {
                // Check for case updates
                const casesData = await apiCall('api/cases.php');
                const cases = casesData.cases || [];

                // Find recently reviewed cases (within last 7 days)
                const recentCases = cases.filter(c => {
                    if (!c.reviewed_at) return false;
                    const reviewedDate = new Date(c.reviewed_at);
                    const sevenDaysAgo = new Date();
                    sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
                    return reviewedDate > sevenDaysAgo && reviewedDate > new Date(lastCheckedTime);
                });

                // Check for new messages
                const messagesData = await apiCall('api/messages.php');
                const unreadMessages = messagesData.messages.filter(m => !m.is_read);

                // Find new messages since last check
                const newMessages = messagesData.messages.filter(m => {
                    const createdDate = new Date(m.created_at);
                    return createdDate > new Date(lastCheckedTime);
                });

                // Update notification badge with total count
                const totalNotifications = recentCases.length + unreadMessages.length;
                const badge = document.getElementById('notificationBadge');
                if (badge) {
                    if (totalNotifications > 0) {
                        badge.textContent = totalNotifications;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }

                // Show browser notifications
                if (Notification.permission === 'granted') {
                    // Case notifications
                    recentCases.forEach(c => {
                        new Notification(`Case ${c.status === 'paid' ? 'Paid' : 'Rejected'}`, {
                            body: `Case #${c.case_number} (${c.client_name}) has been ${c.status}`,
                            icon: '/favicon.ico'
                        });
                    });

                    // Message notifications
                    newMessages.forEach(m => {
                        new Notification(`New Message from ${m.from_name}`, {
                            body: m.subject,
                            icon: '/favicon.ico'
                        });
                    });
                }

                lastCheckedTime = new Date().toISOString();
            } catch (err) {
                console.error('Error checking notifications:', err);
            }
        }

        async function loadNotifications() {
            try {
                // Get dismissed notifications from localStorage
                const dismissed = JSON.parse(localStorage.getItem('dismissedNotifications') || '[]');

                // Get case notifications (not dismissed)
                const reviewed = allCases.filter(c => c.reviewed_at && !dismissed.includes(`case_${c.id}`))
                    .sort((a, b) => new Date(b.reviewed_at) - new Date(a.reviewed_at))
                    .slice(0, 50);

                // Get messages
                const msgData = await apiCall('api/messages.php');
                const messages = msgData.messages || [];
                allMessages = messages; // Store globally for modal access

                // Build unified items list: system notifications + messages
                const allItems = [];

                reviewed.forEach(c => {
                    allItems.push({
                        type: 'system',
                        subtype: c.status === 'paid' ? 'approved' : 'rejected',
                        direction: 'received',
                        is_read: true,
                        fromTo: `Case #${c.case_number}`,
                        subject: c.status === 'paid' ? `${escapeHtml(c.client_name)} - ${formatCurrency(c.commission)}` : `${escapeHtml(c.client_name)} - Rejected`,
                        time: c.reviewed_at,
                        onclick: `viewCaseDetail(${c.id})`,
                        deleteAction: `showDeleteConfirmModal('case', 'case_${c.id}')`,
                        id: `case_${c.id}`
                    });
                });

                messages.forEach(m => {
                    const isSent = m.direction === 'sent';
                    allItems.push({
                        type: 'message',
                        subtype: isSent ? 'sent' : 'received',
                        direction: m.direction,
                        is_read: isSent ? true : !!m.is_read,
                        fromTo: isSent ? 'To: ' + escapeHtml(m.to_name) : escapeHtml(m.from_name),
                        subject: escapeHtml(m.subject || '(No subject)'),
                        time: m.created_at,
                        onclick: `viewMessage(${m.id})`,
                        deleteAction: !isSent ? `showDeleteConfirmModal('message', ${m.id})` : null,
                        id: m.id
                    });
                });

                // Sort by time descending
                allItems.sort((a, b) => new Date(b.time) - new Date(a.time));

                // Store for filtering
                window._notifItems = allItems;

                renderNotifications(allItems, notifCurrentFilter);

                // Update badge
                const unreadCount = msgData.unread_count || 0;
                const notifBadge = document.getElementById('notificationBadge');
                if (notifBadge) {
                    if (unreadCount > 0) {
                        notifBadge.textContent = unreadCount;
                        notifBadge.classList.remove('hidden');
                    } else {
                        notifBadge.classList.add('hidden');
                    }
                }
            } catch (err) {
                console.error('Error loading notifications:', err);
            }
        }

        function renderNotifications(items, filter) {
            // Filter items
            let filtered = items;
            if (filter === 'unread') {
                filtered = items.filter(i => !i.is_read);
            } else if (filter === 'sent') {
                filtered = items.filter(i => i.direction === 'sent');
            } else if (filter === 'read') {
                filtered = items.filter(i => i.direction === 'received' && i.is_read);
            }

            // Stats (messages only for counts)
            const msgItems = items.filter(i => i.type === 'message');
            const totalCount = items.length;
            const unreadCount = items.filter(i => !i.is_read).length;
            const sentCount = items.filter(i => i.direction === 'sent').length;

            const statTotal = document.getElementById('notifStatTotal');
            const statUnread = document.getElementById('notifStatUnread');
            const statSent = document.getElementById('notifStatSent');
            if (statTotal) statTotal.textContent = totalCount;
            if (statUnread) {
                statUnread.textContent = unreadCount;
                statUnread.className = 'qs-val ' + (unreadCount > 0 ? 'blue' : 'dim');
            }
            if (statSent) {
                statSent.textContent = sentCount;
                statSent.className = 'qs-val ' + (sentCount === 0 ? 'dim' : '');
            }

            const tbody = document.getElementById('notificationsBody');

            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6">
                            <div class="notif-empty">
                                <div class="notif-empty-icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="notif-empty-title">No messages</div>
                                <div class="notif-empty-desc">You're all caught up!</div>
                            </div>
                        </td>
                    </tr>
                `;
            } else {
                let html = '';
                filtered.forEach(item => {
                    const isUnread = !item.is_read;
                    const rowClass = isUnread ? 'unread' : '';

                    // Direction badge
                    let dirBadge;
                    if (item.type === 'system') {
                        dirBadge = item.subtype === 'approved'
                            ? '<span class="dir-badge system-approved">Approved</span>'
                            : '<span class="dir-badge system-rejected">Rejected</span>';
                    } else {
                        dirBadge = item.direction === 'sent'
                            ? '<span class="dir-badge sent">Sent</span>'
                            : '<span class="dir-badge received">Received</span>';
                    }

                    const dot = isUnread ? '<div class="unread-dot"></div>' : '';
                    const timeStr = formatRelativeTime(item.time);

                    const deleteBtn = item.deleteAction ? `
                        <button class="act-icon danger" onclick="event.stopPropagation(); ${item.deleteAction}" title="Delete">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    ` : '';

                    html += `
                        <tr class="${rowClass}" onclick="${item.onclick}" style="cursor:pointer;">
                            <td style="width:24px;padding:10px 6px 10px 14px;">${dot}</td>
                            <td>${dirBadge}</td>
                            <td>${item.fromTo}</td>
                            <td class="td-subject">${item.subject}</td>
                            <td class="td-time">${timeStr}</td>
                            <td style="text-align:center;">
                                <div class="action-group">
                                    <button class="act-icon" onclick="event.stopPropagation(); ${item.onclick}" title="View">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    ${deleteBtn}
                                </div>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            }

            // Footer
            const footLeft = document.getElementById('notifFootLeft');
            const footRight = document.getElementById('notifFootRight');
            if (footLeft) footLeft.textContent = `${filtered.length} item${filtered.length !== 1 ? 's' : ''}`;
            if (footRight) footRight.textContent = `${unreadCount} unread`;
        }

        function filterNotifications(filter) {
            notifCurrentFilter = filter;

            // Update chip active state
            document.querySelectorAll('.notif-filters .f-chip').forEach(chip => {
                chip.classList.toggle('active', chip.dataset.filter === filter);
            });

            // Re-render with current data
            if (window._notifItems) {
                renderNotifications(window._notifItems, filter);
            }
        }

        async function viewMessage(messageId) {
            try {
                // Find the message
                const message = allMessages.find(m => m.id == messageId);
                if (!message) {
                    console.error('Message not found');
                    return;
                }

                // Show modal with message content
                showMessageModal(message);

                // Mark as read if unread
                if (!message.is_read) {
                    await apiCall('api/messages.php', {
                        method: 'PUT',
                        body: JSON.stringify({ id: messageId })
                    });

                    // Update local message status
                    message.is_read = 1;

                    // Reload notifications to update badge
                    checkNotifications();
                }
            } catch (err) {
                console.error('Error viewing message:', err);
            }
        }

        function showMessageModal(message) {
            currentViewingMessage = message; // Store current message for reply/delete

            const isSent = message.direction === 'sent';
            const otherName = isSent ? message.to_name : message.from_name;

            // Update modal title and labels based on direction
            document.getElementById('messageModalTitle').textContent = isSent ? `Message to ${otherName}` : `Message from ${otherName}`;
            document.getElementById('messageFromLabel').textContent = isSent ? 'To:' : 'From:';
            document.getElementById('messageFrom').textContent = otherName;
            document.getElementById('messageSubject').textContent = message.subject;
            document.getElementById('messageBody').textContent = message.message;

            const date = new Date(message.created_at);
            document.getElementById('messageDate').textContent = formatRelativeTime(message.created_at);

            // Check if subject contains a case number and show/hide View Case button
            const caseMatch = message.subject.match(/Case #(\d+)/i);
            const viewCaseBtn = document.getElementById('viewCaseBtn');
            if (caseMatch) {
                window.currentMessageCaseNumber = caseMatch[1];
                viewCaseBtn.style.display = 'inline-block';
            } else {
                window.currentMessageCaseNumber = null;
                viewCaseBtn.style.display = 'none';
            }

            document.getElementById('messageModal').style.display = 'flex';
        }

        async function viewCaseFromMessage() {
            const caseNumber = window.currentMessageCaseNumber;
            if (!caseNumber) {
                alert('No case number found in this message');
                return;
            }

            try {
                // Find the case by case_number
                const data = await apiCall('api/cases.php');
                const cases = data.cases || [];
                const targetCase = cases.find(c => c.case_number === caseNumber);

                if (!targetCase) {
                    alert(`Case #${caseNumber} not found`);
                    return;
                }

                // Close message modal and open case detail
                closeMessageModal();
                viewCaseDetail(targetCase.id);
            } catch (err) {
                console.error('Error:', err);
                alert('Error loading case details');
            }
        }

        function replyToMessage() {
            if (!currentViewingMessage) return;

            closeMessageModal();

            // Open compose modal with pre-filled subject
            const replySubject = currentViewingMessage.subject.startsWith('Re: ')
                ? currentViewingMessage.subject
                : 'Re: ' + currentViewingMessage.subject;

            document.getElementById('composeSubject').value = replySubject;
            document.getElementById('composeMessage').value = '';
            document.getElementById('composeMessageModal').style.display = 'flex';
        }

        async function deleteCurrentMessage() {
            if (!currentViewingMessage) return;

            if (!confirm('Are you sure you want to delete this message?')) {
                return;
            }

            try {
                const result = await apiCall(`api/messages.php?id=${currentViewingMessage.id}`, {
                    method: 'DELETE'
                });

                if (result.success) {
                    closeMessageModal();
                    loadNotifications(); // Reload notifications
                } else {
                    alert(result.error || 'Error deleting message');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error deleting message');
            }
        }

        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
            // Reload notifications to refresh the list
            loadNotifications();
        }

        async function markAllRead() {
            try {
                // Mark all messages as read via API
                const result = await apiCall('api/messages.php', {
                    method: 'PUT',
                    body: JSON.stringify({ mark_all: true })
                });

                if (result.success) {
                    // Reload notifications to update the display and badge
                    loadNotifications();
                } else {
                    alert('Error marking messages as read');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error marking messages as read');
            }
        }

        // Messages
        let currentViewingMessage = null;

        // Delete confirmation modal variables
        let pendingDeleteType = null; // 'case' or 'message'
        let pendingDeleteId = null;

        function showDeleteConfirmModal(type, id) {
            pendingDeleteType = type;
            pendingDeleteId = id;
            document.getElementById('deleteConfirmModal').style.display = 'flex';
        }

        function closeDeleteConfirmModal() {
            pendingDeleteType = null;
            pendingDeleteId = null;
            document.getElementById('deleteConfirmModal').style.display = 'none';
        }

        function confirmDeleteNotification() {
            if (pendingDeleteType === 'case') {
                dismissNotification(pendingDeleteId);
            } else if (pendingDeleteType === 'message') {
                deleteMessageConfirmed(pendingDeleteId);
            }
            closeDeleteConfirmModal();
        }

        function dismissNotification(notificationId) {
            const dismissed = JSON.parse(localStorage.getItem('dismissedNotifications') || '[]');
            dismissed.push(notificationId);
            localStorage.setItem('dismissedNotifications', JSON.stringify(dismissed));
            loadNotifications(); // Reload to hide the dismissed notification
        }

        async function deleteMessageConfirmed(messageId) {
            try {
                const result = await apiCall(`api/messages.php?id=${messageId}`, {
                    method: 'DELETE'
                });

                if (result.success) {
                    loadNotifications(); // Reload notifications
                } else {
                    alert(result.error || 'Error deleting message');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error deleting message');
            }
        }


        function closeCaseDetail() {
            document.getElementById('caseDetailModal').classList.remove('show');
        }

        function openComposeMessage() {
            document.getElementById('composeSubject').value = '';
            document.getElementById('composeMessage').value = '';
            document.getElementById('composeMessageModal').style.display = 'flex';
        }

        function closeComposeMessage() {
            document.getElementById('composeMessageModal').style.display = 'none';
        }

        async function sendEmployeeMessage(e) {
            e.preventDefault();

            const subject = document.getElementById('composeSubject').value;
            const message = document.getElementById('composeMessage').value;

            try {
                // Get admin user ID (assuming admin ID is 1 from setup.sql)
                const usersData = await apiCall('api/users.php');
                const adminUser = usersData.users?.find(u => u.role === 'admin');

                if (!adminUser) {
                    alert('Admin user not found');
                    return;
                }

                const result = await apiCall('api/messages.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        to_user_id: adminUser.id,
                        subject: subject,
                        message: message
                    })
                });

                if (result.success) {
                    alert('Message sent successfully!');
                    closeComposeMessage();
                    loadNotifications(); // Reload notifications
                } else {
                    alert(result.error || 'Error sending message');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error sending message');
            }
        }

        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // ==================== Traffic Cases Functions ====================
        <?php if ($user['id'] == 2): // Chong only - Traffic JS ?>

        function initTrafficYearFilter() {
            const yearSelect = document.getElementById('trafficYearFilter');
            if (!yearSelect) return;

            const currentYear = new Date().getFullYear();
            yearSelect.innerHTML = '<option value="all" selected>All</option>';

            // Years from current+2 down to 2019 (when traffic data starts)
            for (let y = currentYear + 2; y >= 2019; y--) {
                const option = document.createElement('option');
                option.value = y;
                option.textContent = y;
                yearSelect.appendChild(option);
            }
        }

        let trafficCasesInitialized = false;

        async function loadTrafficCases() {
            try {
                // Always fetch all cases for stats
                const data = await apiCall(`api/traffic.php?status=all`);

                if (data.csrf_token) {
                    csrfToken = data.csrf_token;
                }

                allTrafficCases = data.cases || [];

                // Set default filter to Active on first load
                if (!trafficCasesInitialized) {
                    trafficCasesInitialized = true;
                    currentSidebarFilter = { type: 'status', value: 'active' };
                    document.getElementById('trafficFilterLabel').textContent = 'Active Cases';
                }

                filterTrafficCases();
                updateTrafficBadge();
                // Update stats cards with ALL cases (not filtered)
                updateTrafficStatsCards(allTrafficCases);

                // Also load pending requests (for Chong)
                loadPendingTrafficRequests();
            } catch (err) {
                console.error('Error loading traffic cases:', err);
                document.getElementById('trafficCasesBody').innerHTML =
                    '<tr><td colspan="9" style="padding: 32px 16px; text-align: center; color: #dc2626;">Error loading cases</td></tr>';
            }
        }

        function filterTrafficCases() {
            const searchTerm = document.getElementById('trafficSearch')?.value.toLowerCase() || '';

            // Get base filtered cases from sidebar
            let baseFiltered = allTrafficCases;
            if (currentSidebarFilter) {
                const { type, value } = currentSidebarFilter;
                if (type === 'referral') {
                    baseFiltered = allTrafficCases.filter(c => (c.referral_source || 'Unknown') === value);
                } else if (type === 'court') {
                    baseFiltered = allTrafficCases.filter(c => c.court === value);
                } else if (type === 'year') {
                    baseFiltered = allTrafficCases.filter(c => {
                        if (!c.court_date) return false;
                        return new Date(c.court_date).getFullYear() === parseInt(value);
                    });
                } else if (type === 'status') {
                    baseFiltered = allTrafficCases.filter(c => c.status === value);
                }
            }

            // Apply search filter on top
            let filtered = baseFiltered;
            if (searchTerm) {
                filtered = baseFiltered.filter(c => {
                    const searchFields = [
                        c.client_name,
                        c.court,
                        c.charge,
                        c.case_number,
                        c.prosecutor_offer,
                        c.referral_source
                    ].filter(Boolean).join(' ').toLowerCase();
                    return searchFields.includes(searchTerm);
                });
            }

            renderTrafficCases(filtered);
            updateTrafficStats(filtered);
        }

        function renderTrafficCases(cases) {
            const tbody = document.getElementById('trafficCasesBody');

            if (!cases || cases.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" style="padding: 32px 16px; text-align: center;" class="text-secondary">No traffic cases found</td></tr>';
                document.getElementById('trafficCaseCount').textContent = '0 cases';
                document.getElementById('trafficTotalCommission').textContent = '$0.00';
                return;
            }

            // Sort by court date
            cases.sort((a, b) => {
                if (!a.court_date) return 1;
                if (!b.court_date) return -1;
                return new Date(a.court_date) - new Date(b.court_date);
            });

            let html = '';
            let totalCommission = 0;

            cases.forEach(c => {
                const commission = parseFloat(c.commission) || 0;
                totalCommission += commission;

                const courtDate = c.court_date ? formatTrafficDate(c.court_date) : '-';
                const dispositionClass = getDispositionClass(c.disposition);

                html += `
                    <tr>
                        <td style="font-weight: 500;">${escapeHtml(c.client_name || '')}</td>
                        <td>${escapeHtml(c.court || '-')}</td>
                        <td>${courtDate}</td>
                        <td>${escapeHtml(c.charge || '-')}</td>
                        <td>${escapeHtml(c.case_number || '-')}</td>
                        <td style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(c.prosecutor_offer || '')}">${escapeHtml(c.prosecutor_offer || '-')}</td>
                        <td><span class="status-badge ${dispositionClass}">${c.disposition || 'pending'}</span></td>
                        <td>${escapeHtml(c.referral_source || '-')}</td>
                        <td style="text-align: right; font-weight: 600; color: ${commission > 0 ? '#059669' : '#6b7280'};">$${commission.toFixed(2)}</td>
                        <td style="text-align: center;">
                            <button onclick="editTrafficCase(${c.id})" class="action-btn edit-btn" title="Edit">✏️</button>
                            <button onclick="deleteTrafficCase(${c.id})" class="action-btn delete-btn" title="Delete">🗑️</button>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            document.getElementById('trafficCaseCount').textContent = `${cases.length} case${cases.length !== 1 ? 's' : ''}`;
            document.getElementById('trafficTotalCommission').textContent = `$${totalCommission.toFixed(2)}`;
        }

        function formatTrafficDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            const month = date.toLocaleString('en-US', { month: 'short' });
            const day = date.getDate();
            const year = date.getFullYear();
            const time = date.toLocaleString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            return `${month} ${day}, ${year} ${time}`;
        }

        function getDispositionClass(disposition) {
            switch (disposition) {
                case 'dismissed': return 'status-paid';
                case 'amended': return 'status-pending';
                case 'other': return 'status-failed';
                default: return 'status-default';
            }
        }

        // Update top 4 stat cards (filtered data)
        function updateTrafficStats(cases) {
            const active = cases.filter(c => c.status === 'active').length;
            const dismissed = cases.filter(c => c.disposition === 'dismissed').length;
            const amended = cases.filter(c => c.disposition === 'amended').length;
            const totalCommission = cases.reduce((sum, c) => sum + (parseFloat(c.commission) || 0), 0);

            document.getElementById('trafficActive').textContent = active;
            document.getElementById('trafficDismissed').textContent = dismissed;
            document.getElementById('trafficAmended').textContent = amended;
            document.getElementById('trafficCommission').textContent = `$${totalCommission.toFixed(0)}`;
        }

        // Track sidebar state
        let currentSidebarTab = 'all';
        let currentSidebarFilter = null;

        // Switch sidebar tab
        function switchSidebarTab(tab) {
            currentSidebarTab = tab;

            // Update tab buttons - Minimal Flat Blue Accent style
            ['all', 'referral', 'court', 'year'].forEach(t => {
                const btn = document.getElementById(`sidebarTab-${t}`);
                if (btn) {
                    if (t === tab) {
                        btn.style.background = '#ffffff';
                        btn.style.color = '#0066ff';
                        btn.style.boxShadow = '0 1px 3px rgba(0,0,0,0.08)';
                    } else {
                        btn.style.background = 'transparent';
                        btn.style.color = '#a3acb9';
                        btn.style.boxShadow = 'none';
                    }
                }
            });

            // Clear filter when switching tabs
            if (tab === 'all') {
                currentSidebarFilter = null;
                document.getElementById('trafficFilterLabel').textContent = 'All Cases';
                renderTrafficCases(allTrafficCases);
                updateTrafficStats(allTrafficCases);
            }

            updateSidebarContent();
        }

        // Filter by sidebar item click
        function filterBySidebar(type, value, label) {
            if (currentSidebarFilter?.type === type && currentSidebarFilter?.value === value) {
                // Toggle off
                currentSidebarFilter = null;
                document.getElementById('trafficFilterLabel').textContent = 'All Cases';
                renderTrafficCases(allTrafficCases);
                updateTrafficStats(allTrafficCases);
            } else {
                currentSidebarFilter = { type, value };
                document.getElementById('trafficFilterLabel').textContent = label;

                let filtered = allTrafficCases;
                if (type === 'referral') {
                    filtered = allTrafficCases.filter(c => (c.referral_source || 'Unknown') === value);
                } else if (type === 'court') {
                    filtered = allTrafficCases.filter(c => c.court === value);
                } else if (type === 'year') {
                    filtered = allTrafficCases.filter(c => {
                        if (!c.court_date) return false;
                        return new Date(c.court_date).getFullYear() === parseInt(value);
                    });
                } else if (type === 'status') {
                    filtered = allTrafficCases.filter(c => c.status === value);
                }

                renderTrafficCases(filtered);
                updateTrafficStats(filtered);
            }

            updateSidebarContent();
        }

        // Update sidebar content based on current tab - Minimal Flat Blue Accent Design
        function updateSidebarContent() {
            const container = document.getElementById('sidebarContent');
            if (!container) return;

            let html = '';
            const cases = allTrafficCases;

            // Color palette
            const colors = {
                bg: '#f7f9fc',
                surface: '#ffffff',
                border: '#e5e9f0',
                textPrimary: '#1a1f36',
                textSecondary: '#5e6687',
                textMuted: '#a3acb9',
                accent: '#0066ff',
                accentLight: '#f0f6ff',
                green: '#00a67e',
                amber: '#d97706'
            };

            if (currentSidebarTab === 'all') {
                const activeCount = cases.filter(c => c.status === 'active').length;
                const resolvedCount = cases.filter(c => c.status === 'resolved').length;
                const activeCommission = cases.filter(c => c.status === 'active').reduce((s, c) => s + (parseFloat(c.commission) || 0), 0);
                const resolvedCommission = cases.filter(c => c.status === 'resolved').reduce((s, c) => s + (parseFloat(c.commission) || 0), 0);

                const isAllActive = !currentSidebarFilter;
                const isActiveActive = currentSidebarFilter?.type === 'status' && currentSidebarFilter?.value === 'active';
                const isResolvedActive = currentSidebarFilter?.type === 'status' && currentSidebarFilter?.value === 'resolved';

                html = `
                    <div onclick="currentSidebarFilter = null; document.getElementById('trafficFilterLabel').textContent = 'All Cases'; renderTrafficCases(allTrafficCases); updateTrafficStats(allTrafficCases); updateSidebarContent();"
                         style="padding: 12px 16px; cursor: pointer; border-bottom: 1px solid ${colors.border}; transition: background 0.15s; ${isAllActive ? `background: ${colors.accentLight}; border-left: 3px solid ${colors.accent}; padding-left: 13px;` : ''}"
                         onmouseover="this.style.background='${isAllActive ? colors.accentLight : colors.bg}'"
                         onmouseout="this.style.background='${isAllActive ? colors.accentLight : ''}'">
                        <div style="font-size: 14px; font-weight: 600; color: ${isAllActive ? colors.accent : colors.textPrimary};">All Cases</div>
                        <div style="font-size: 12px; color: ${colors.textMuted}; margin-top: 2px;">${cases.length} cases</div>
                    </div>
                    <div onclick="filterBySidebar('status', 'active', 'Active Cases')"
                         style="padding: 12px 16px; cursor: pointer; border-bottom: 1px solid ${colors.border}; transition: background 0.15s; ${isActiveActive ? `background: ${colors.accentLight}; border-left: 3px solid ${colors.accent}; padding-left: 13px;` : ''}"
                         onmouseover="this.style.background='${isActiveActive ? colors.accentLight : colors.bg}'"
                         onmouseout="this.style.background='${isActiveActive ? colors.accentLight : ''}'">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; font-weight: 600; color: ${isActiveActive ? colors.accent : colors.textPrimary};">Active</span>
                            <span style="font-size: 13px; font-weight: 600; color: ${colors.accent}; font-family: 'SF Mono', Menlo, monospace;">${activeCount}</span>
                        </div>
                        <div style="font-size: 12px; font-weight: 600; color: ${colors.green}; font-family: 'SF Mono', Menlo, monospace; margin-top: 2px;">$${activeCommission.toLocaleString()}</div>
                    </div>
                    <div onclick="filterBySidebar('status', 'resolved', 'Resolved Cases')"
                         style="padding: 12px 16px; cursor: pointer; border-bottom: 1px solid ${colors.border}; transition: background 0.15s; ${isResolvedActive ? `background: ${colors.accentLight}; border-left: 3px solid ${colors.accent}; padding-left: 13px;` : ''}"
                         onmouseover="this.style.background='${isResolvedActive ? colors.accentLight : colors.bg}'"
                         onmouseout="this.style.background='${isResolvedActive ? colors.accentLight : ''}'">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; font-weight: 600; color: ${isResolvedActive ? colors.accent : colors.textPrimary};">Resolved</span>
                            <span style="font-size: 13px; font-weight: 600; color: ${colors.green}; font-family: 'SF Mono', Menlo, monospace;">${resolvedCount}</span>
                        </div>
                        <div style="font-size: 12px; font-weight: 600; color: ${colors.green}; font-family: 'SF Mono', Menlo, monospace; margin-top: 2px;">$${resolvedCommission.toLocaleString()}</div>
                    </div>
                `;
            } else if (currentSidebarTab === 'referral') {
                const stats = {};
                cases.forEach(c => {
                    const ref = c.referral_source || 'Unknown';
                    if (!stats[ref]) stats[ref] = { count: 0, commission: 0, dismissed: 0, amended: 0 };
                    stats[ref].count++;
                    stats[ref].commission += parseFloat(c.commission) || 0;
                    if (c.disposition === 'dismissed') stats[ref].dismissed++;
                    if (c.disposition === 'amended') stats[ref].amended++;
                });
                const sorted = Object.entries(stats).sort((a, b) => b[1].commission - a[1].commission);

                sorted.forEach(([name, data]) => {
                    const isActive = currentSidebarFilter?.type === 'referral' && currentSidebarFilter?.value === name;
                    html += `
                        <div onclick="filterBySidebar('referral', '${name.replace(/'/g, "\\'")}', 'Referral: ${escapeHtml(name)}')"
                             style="padding: 12px 16px; cursor: pointer; border-bottom: 1px solid ${colors.border}; transition: background 0.15s; ${isActive ? `background: ${colors.accentLight}; border-left: 3px solid ${colors.accent}; padding-left: 13px;` : ''}"
                             onmouseover="this.style.background='${isActive ? colors.accentLight : colors.bg}'"
                             onmouseout="this.style.background='${isActive ? colors.accentLight : ''}'">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 14px; font-weight: 600; color: ${isActive ? colors.accent : colors.textPrimary}; flex: 1;">${escapeHtml(name)}</span>
                                <div style="display: flex; gap: 5px;">
                                    ${data.dismissed > 0 ? `<span style="font-size: 10px; font-weight: 600; font-family: 'SF Mono', Menlo, monospace; padding: 3px 7px; border-radius: 4px; background: ${isActive ? '#fff' : colors.bg}; color: ${colors.green};">D${data.dismissed}</span>` : ''}
                                    ${data.amended > 0 ? `<span style="font-size: 10px; font-weight: 600; font-family: 'SF Mono', Menlo, monospace; padding: 3px 7px; border-radius: 4px; background: ${isActive ? '#fff' : colors.bg}; color: ${colors.amber};">A${data.amended}</span>` : ''}
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                                <span style="font-size: 13px; font-weight: 600; color: ${colors.green}; font-family: 'SF Mono', Menlo, monospace;">$${data.commission.toLocaleString()}</span>
                                <span style="font-size: 10px; color: ${colors.textMuted};">${data.count} cases</span>
                            </div>
                        </div>
                    `;
                });
            } else if (currentSidebarTab === 'court') {
                const stats = {};
                cases.forEach(c => {
                    const court = c.court || 'Unknown';
                    if (!stats[court]) stats[court] = { count: 0, commission: 0, dismissed: 0, amended: 0 };
                    stats[court].count++;
                    stats[court].commission += parseFloat(c.commission) || 0;
                    if (c.disposition === 'dismissed') stats[court].dismissed++;
                    if (c.disposition === 'amended') stats[court].amended++;
                });
                const sorted = Object.entries(stats).sort((a, b) => b[1].commission - a[1].commission);

                sorted.forEach(([name, data]) => {
                    const isActive = currentSidebarFilter?.type === 'court' && currentSidebarFilter?.value === name;
                    html += `
                        <div onclick="filterBySidebar('court', '${name.replace(/'/g, "\\'")}', 'Court: ${escapeHtml(name)}')"
                             style="padding: 12px 16px; cursor: pointer; border-bottom: 1px solid ${colors.border}; transition: background 0.15s; ${isActive ? `background: ${colors.accentLight}; border-left: 3px solid ${colors.accent}; padding-left: 13px;` : ''}"
                             onmouseover="this.style.background='${isActive ? colors.accentLight : colors.bg}'"
                             onmouseout="this.style.background='${isActive ? colors.accentLight : ''}'">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 14px; font-weight: 600; color: ${isActive ? colors.accent : colors.textPrimary}; flex: 1;">${escapeHtml(name)}</span>
                                <div style="display: flex; gap: 5px;">
                                    ${data.dismissed > 0 ? `<span style="font-size: 10px; font-weight: 600; font-family: 'SF Mono', Menlo, monospace; padding: 3px 7px; border-radius: 4px; background: ${isActive ? '#fff' : colors.bg}; color: ${colors.green};">D${data.dismissed}</span>` : ''}
                                    ${data.amended > 0 ? `<span style="font-size: 10px; font-weight: 600; font-family: 'SF Mono', Menlo, monospace; padding: 3px 7px; border-radius: 4px; background: ${isActive ? '#fff' : colors.bg}; color: ${colors.amber};">A${data.amended}</span>` : ''}
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                                <span style="font-size: 13px; font-weight: 600; color: ${colors.green}; font-family: 'SF Mono', Menlo, monospace;">$${data.commission.toLocaleString()}</span>
                                <span style="font-size: 10px; color: ${colors.textMuted};">${data.count} cases</span>
                            </div>
                        </div>
                    `;
                });
            } else if (currentSidebarTab === 'year') {
                const stats = {};
                cases.forEach(c => {
                    if (c.court_date) {
                        const year = new Date(c.court_date).getFullYear();
                        if (!stats[year]) stats[year] = { count: 0, commission: 0, dismissed: 0, amended: 0 };
                        stats[year].count++;
                        stats[year].commission += parseFloat(c.commission) || 0;
                        if (c.disposition === 'dismissed') stats[year].dismissed++;
                        if (c.disposition === 'amended') stats[year].amended++;
                    }
                });
                const sorted = Object.entries(stats).sort((a, b) => b[0] - a[0]);

                sorted.forEach(([year, data]) => {
                    const isActive = currentSidebarFilter?.type === 'year' && currentSidebarFilter?.value === year;
                    html += `
                        <div onclick="filterBySidebar('year', '${year}', 'Year: ${year}')"
                             style="padding: 12px 16px; cursor: pointer; border-bottom: 1px solid ${colors.border}; transition: background 0.15s; ${isActive ? `background: ${colors.accentLight}; border-left: 3px solid ${colors.accent}; padding-left: 13px;` : ''}"
                             onmouseover="this.style.background='${isActive ? colors.accentLight : colors.bg}'"
                             onmouseout="this.style.background='${isActive ? colors.accentLight : ''}'">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="width: 44px; font-size: 14px; font-weight: 600; color: ${isActive ? colors.accent : colors.textPrimary};">${year}</span>
                                <div style="display: flex; gap: 5px; flex: 1;">
                                    ${data.dismissed > 0 ? `<span style="font-size: 10px; font-weight: 600; font-family: 'SF Mono', Menlo, monospace; padding: 3px 7px; border-radius: 4px; background: ${isActive ? '#fff' : colors.bg}; color: ${colors.green};">D${data.dismissed}</span>` : ''}
                                    ${data.amended > 0 ? `<span style="font-size: 10px; font-weight: 600; font-family: 'SF Mono', Menlo, monospace; padding: 3px 7px; border-radius: 4px; background: ${isActive ? '#fff' : colors.bg}; color: ${colors.amber};">A${data.amended}</span>` : ''}
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 13px; font-weight: 600; color: ${colors.green}; font-family: 'SF Mono', Menlo, monospace;">$${data.commission.toLocaleString()}</div>
                                    <div style="font-size: 10px; color: ${colors.textMuted};">${data.count} cases</div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            container.innerHTML = html || `<div style="padding: 16px; color: ${colors.textMuted}; text-align: center;">No data</div>`;
        }

        // Update Quick Stats card - Minimal Flat Blue Accent Design
        function updateTrafficStatsCards(cases) {
            const dismissed = cases.filter(c => c.disposition === 'dismissed').length;
            const amended = cases.filter(c => c.disposition === 'amended').length;
            const totalCommission = cases.reduce((sum, c) => sum + (parseFloat(c.commission) || 0), 0);
            const resolvedCases = cases.filter(c => c.disposition === 'dismissed' || c.disposition === 'amended');
            const avgPerCase = resolvedCases.length > 0 ? totalCommission / resolvedCases.length : 0;
            const dismissRate = resolvedCases.length > 0 ? Math.round((dismissed / resolvedCases.length) * 100) : 0;

            // Color palette
            const colors = {
                bg: '#f7f9fc',
                surface: '#ffffff',
                border: '#e5e9f0',
                textPrimary: '#1a1f36',
                textMuted: '#a3acb9'
            };

            let quickStatsHtml = `
                <div style="font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: ${colors.textMuted}; margin-bottom: 8px;">STATS</div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                    <div style="background: ${colors.surface}; border: 1px solid ${colors.border}; border-radius: 6px; padding: 10px 8px; text-align: center;">
                        <div style="font-size: 13px; font-weight: 600; color: ${colors.textPrimary}; font-family: 'SF Mono', Menlo, monospace;">$${Math.round(avgPerCase)}</div>
                        <div style="font-size: 9px; text-transform: uppercase; color: ${colors.textMuted}; margin-top: 2px;">AVG</div>
                    </div>
                    <div style="background: ${colors.surface}; border: 1px solid ${colors.border}; border-radius: 6px; padding: 10px 8px; text-align: center;">
                        <div style="font-size: 13px; font-weight: 600; color: ${colors.textPrimary}; font-family: 'SF Mono', Menlo, monospace;">${dismissRate}%</div>
                        <div style="font-size: 9px; text-transform: uppercase; color: ${colors.textMuted}; margin-top: 2px;">DISMISS</div>
                    </div>
                    <div style="background: ${colors.surface}; border: 1px solid ${colors.border}; border-radius: 6px; padding: 10px 8px; text-align: center;">
                        <div style="font-size: 13px; font-weight: 600; color: ${colors.textPrimary}; font-family: 'SF Mono', Menlo, monospace;">${cases.length}</div>
                        <div style="font-size: 9px; text-transform: uppercase; color: ${colors.textMuted}; margin-top: 2px;">TOTAL</div>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 10px; border-top: 1px solid ${colors.border}; font-size: 11px;">
                    <span style="color: ${colors.textMuted};">D: ${dismissed}</span>
                    <span style="color: ${colors.textMuted};">A: ${amended}</span>
                    <span style="color: #00a67e; font-weight: 600; font-family: 'SF Mono', Menlo, monospace;">$${totalCommission.toLocaleString()}</span>
                </div>
            `;
            document.getElementById('trafficQuickStats').innerHTML = quickStatsHtml;

            // Also update sidebar content
            updateSidebarContent();
        }

        function updateTrafficBadge() {
            const badge = document.getElementById('trafficBadge');
            if (!badge) return;

            const activeCount = allTrafficCases.filter(c => c.status === 'active').length;
            if (activeCount > 0) {
                badge.textContent = activeCount;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        function openAddTrafficModal() {
            document.getElementById('trafficModalTitle').textContent = 'Add Traffic Case';
            document.getElementById('trafficForm').reset();
            document.getElementById('trafficCaseId').value = '';
            document.getElementById('trafficDisposition').value = 'pending';
            document.getElementById('trafficStatus').value = 'active';
            document.getElementById('trafficNoaSentDate').value = '';
            document.getElementById('trafficDiscovery').checked = false;
            updateTrafficCommission();
            document.getElementById('trafficModal').classList.add('show');
        }

        function closeTrafficModal() {
            document.getElementById('trafficModal').classList.remove('show');
        }

        function updateTrafficCommission() {
            const disposition = document.getElementById('trafficDisposition').value;
            let commission = 0;

            if (disposition === 'dismissed') {
                commission = 150;
            } else if (disposition === 'amended') {
                commission = 100;
            }

            document.getElementById('trafficCommissionDisplay').textContent = `$${commission.toFixed(2)}`;
        }

        async function saveTrafficCase() {
            const caseId = document.getElementById('trafficCaseId').value;
            const data = {
                client_name: document.getElementById('trafficClientName').value,
                client_phone: document.getElementById('trafficClientPhone').value,
                court: document.getElementById('trafficCourt').value,
                court_date: document.getElementById('trafficCourtDate').value || null,
                charge: document.getElementById('trafficCharge').value,
                case_number: document.getElementById('trafficCaseNumber').value,
                prosecutor_offer: document.getElementById('trafficOffer').value,
                disposition: document.getElementById('trafficDisposition').value,
                status: document.getElementById('trafficStatus').value,
                note: document.getElementById('trafficNote').value,
                referral_source: document.getElementById('trafficReferralSource').value,
                paid: document.getElementById('trafficPaid').checked ? 1 : 0,
                noa_sent_date: document.getElementById('trafficNoaSentDate').value || null,
                discovery: document.getElementById('trafficDiscovery').checked ? 1 : 0
            };

            if (!data.client_name) {
                alert('Client name is required');
                return;
            }

            try {
                const method = caseId ? 'PUT' : 'POST';
                if (caseId) data.id = parseInt(caseId);

                const result = await apiCall('api/traffic.php', {
                    method: method,
                    body: JSON.stringify(data)
                });

                if (result.success) {
                    closeTrafficModal();
                    loadTrafficCases();
                } else {
                    alert(result.error || 'Error saving case');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error saving case');
            }
        }

        function editTrafficCase(id) {
            const trafficCase = allTrafficCases.find(c => c.id == id);
            if (!trafficCase) return;

            document.getElementById('trafficModalTitle').textContent = 'Edit Traffic Case';
            document.getElementById('trafficCaseId').value = id;
            document.getElementById('trafficClientName').value = trafficCase.client_name || '';
            document.getElementById('trafficClientPhone').value = trafficCase.client_phone || '';
            document.getElementById('trafficCourt').value = trafficCase.court || '';

            // Format datetime for input
            if (trafficCase.court_date) {
                const dt = new Date(trafficCase.court_date);
                const formatted = dt.toISOString().slice(0, 16);
                document.getElementById('trafficCourtDate').value = formatted;
            } else {
                document.getElementById('trafficCourtDate').value = '';
            }

            document.getElementById('trafficCharge').value = trafficCase.charge || '';
            document.getElementById('trafficCaseNumber').value = trafficCase.case_number || '';
            document.getElementById('trafficOffer').value = trafficCase.prosecutor_offer || '';
            document.getElementById('trafficDisposition').value = trafficCase.disposition || 'pending';
            document.getElementById('trafficStatus').value = trafficCase.status || 'active';
            document.getElementById('trafficNote').value = trafficCase.note || '';
            document.getElementById('trafficReferralSource').value = trafficCase.referral_source || '';
            document.getElementById('trafficPaid').checked = trafficCase.paid == 1;
            document.getElementById('trafficNoaSentDate').value = trafficCase.noa_sent_date || '';
            document.getElementById('trafficDiscovery').checked = trafficCase.discovery == 1;

            updateTrafficCommission();
            document.getElementById('trafficModal').classList.add('show');
        }

        async function deleteTrafficCase(id) {
            if (!confirm('Are you sure you want to delete this traffic case?')) return;

            try {
                const result = await apiCall('api/traffic.php', {
                    method: 'DELETE',
                    body: JSON.stringify({ id: id })
                });

                if (result.success) {
                    loadTrafficCases();
                } else {
                    alert(result.error || 'Error deleting case');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error deleting case');
            }
        }

        // ===== TRAFFIC REQUEST FUNCTIONS (Chong) =====
        let pendingTrafficRequests = [];

        async function loadPendingTrafficRequests() {
            try {
                const data = await apiCall('api/traffic_requests.php?status=pending');
                pendingTrafficRequests = data.requests || [];
                renderPendingTrafficRequests();

                // Update badge on Traffic tab
                const badge = document.getElementById('trafficBadge');
                if (badge) {
                    if (pendingTrafficRequests.length > 0) {
                        badge.textContent = pendingTrafficRequests.length;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            } catch (err) {
                console.error('Error loading pending requests:', err);
            }
        }

        function renderPendingTrafficRequests() {
            const section = document.getElementById('pendingRequestsSection');
            const list = document.getElementById('pendingRequestsList');
            const countBadge = document.getElementById('pendingRequestCount');

            if (!section || !list) return;

            if (pendingTrafficRequests.length === 0) {
                section.style.display = 'none';
                return;
            }

            section.style.display = 'block';
            countBadge.textContent = pendingTrafficRequests.length;

            list.innerHTML = pendingTrafficRequests.map(r => {
                const courtDate = r.court_date ? new Date(r.court_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'No date';

                return `
                    <div style="padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                            <div>
                                <div style="font-size: 13px; font-weight: 600; color: #111827;">${r.client_name}</div>
                                <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">From: ${r.requester_name}</div>
                            </div>
                        </div>
                        <div style="font-size: 11px; color: #6b7280; margin-bottom: 8px;">
                            ${r.court || 'No court'} • ${courtDate}
                            ${r.charge ? '<br>' + r.charge : ''}
                        </div>
                        ${r.note ? `<div style="font-size: 11px; color: #6b7280; background: #f9fafb; padding: 6px 8px; border-radius: 4px; margin-bottom: 8px;">${r.note}</div>` : ''}
                        <div style="display: flex; gap: 8px;">
                            <button onclick="acceptTrafficRequest(${r.id})" style="flex: 1; padding: 6px 12px; background: #10b981; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">Accept</button>
                            <button onclick="showDenyModal(${r.id})" style="flex: 1; padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">Deny</button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function acceptTrafficRequest(requestId) {
            if (!confirm('Accept this traffic case request?')) return;

            try {
                const result = await apiCall('api/traffic_requests.php', {
                    method: 'PUT',
                    body: JSON.stringify({ id: requestId, action: 'accept' })
                });

                if (result.success) {
                    alert('Request accepted! Case added to your traffic cases.');
                    loadPendingTrafficRequests();
                    loadTrafficCases();
                } else {
                    alert(result.error || 'Error accepting request');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error accepting request');
            }
        }

        function showDenyModal(requestId) {
            const reason = prompt('Please enter the reason for denying this request (required):');
            if (reason === null) return; // Cancelled
            if (!reason.trim()) {
                alert('Deny reason is required');
                return;
            }
            denyTrafficRequest(requestId, reason.trim());
        }

        async function denyTrafficRequest(requestId, reason) {
            try {
                const result = await apiCall('api/traffic_requests.php', {
                    method: 'PUT',
                    body: JSON.stringify({ id: requestId, action: 'deny', deny_reason: reason })
                });

                if (result.success) {
                    alert('Request denied. The requester has been notified.');
                    loadPendingTrafficRequests();
                } else {
                    alert(result.error || 'Error denying request');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error denying request');
            }
        }

        <?php endif; ?>

        <?php if (hasPermission('can_request_traffic') && $user['id'] != 2): ?>
        // ===== EMPLOYEE TRAFFIC REQUEST FUNCTIONS =====
        let empTrafficRequests = [];

        async function loadEmpTrafficRequests() {
            try {
                const data = await apiCall('api/traffic_requests.php?type=sent');
                empTrafficRequests = data.requests || [];
                renderEmpTrafficRequests();
            } catch (err) {
                console.error('Error loading requests:', err);
            }
        }

        function renderEmpTrafficRequests() {
            const container = document.getElementById('empMyTrafficRequests');
            if (!container) return;

            if (empTrafficRequests.length === 0) {
                container.innerHTML = '<p style="padding: 24px; text-align: center; color: #8b8fa3; font-size: 12px; font-family: Outfit, sans-serif;">No requests yet</p>';
                return;
            }

            container.innerHTML = empTrafficRequests.map(r => {
                const statusBg = { pending: '#fef3c7', accepted: '#d1fae5', denied: '#fee2e2' };
                const statusColor = { pending: '#92400e', accepted: '#065f46', denied: '#991b1b' };
                const bg = statusBg[r.status] || '#f3f4f6';
                const color = statusColor[r.status] || '#6b7280';
                const courtDate = r.court_date ? new Date(r.court_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' }) : '-';

                return `
                    <div style="padding: 12px 16px; border-bottom: 1px solid #f0f1f3; font-family: 'Outfit', sans-serif;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span style="font-size: 13px; font-weight: 600; color: #1a1a2e;">${escapeHtml(r.client_name)}</span>
                            <span style="display:inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; background: ${bg}; color: ${color};">${r.status}</span>
                        </div>
                        <div style="font-size: 11px; color: #8b8fa3;">
                            ${r.court ? escapeHtml(r.court) : '-'} &bull; ${courtDate}
                            ${r.charge ? ' &bull; ' + escapeHtml(r.charge) : ''}
                        </div>
                        ${r.deny_reason ? `<div style="font-size: 11px; color: #dc2626; margin-top: 4px;">Reason: ${escapeHtml(r.deny_reason)}</div>` : ''}
                    </div>
                `;
            }).join('');
        }

        document.getElementById('empTrafficRequestForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            const data = {
                client_name: document.getElementById('empReqClientName').value.trim(),
                client_phone: document.getElementById('empReqClientPhone').value.trim(),
                client_email: document.getElementById('empReqClientEmail').value.trim(),
                court: document.getElementById('empReqCourt').value.trim(),
                court_date: document.getElementById('empReqCourtDate').value || null,
                charge: document.getElementById('empReqCharge').value.trim(),
                case_number: document.getElementById('empReqCaseNumber').value.trim(),
                citation_issued_date: document.getElementById('empReqIssuedDate').value || null,
                note: document.getElementById('empReqNote').value.trim()
            };

            if (!data.client_name) {
                alert('Client name is required');
                return;
            }

            try {
                const result = await apiCall('api/traffic_requests.php', {
                    method: 'POST',
                    body: JSON.stringify(data)
                });

                if (result.success) {
                    alert('Request submitted successfully!');
                    this.reset();
                    loadEmpTrafficRequests();
                } else {
                    alert(result.error || 'Error submitting request');
                }
            } catch (err) {
                alert(err.message || 'Error submitting request');
            }
        });
        <?php endif; ?>

        // ============================================
        // Goals Functions
        // ============================================

        let myGoalsYearFilterInit = false;

        function initMyGoalsYearFilter() {
            if (myGoalsYearFilterInit) return;
            myGoalsYearFilterInit = true;
            const sel = document.getElementById('myGoalsYearFilter');
            const currentYear = new Date().getFullYear();
            for (let y = currentYear; y >= currentYear - 3; y--) {
                const opt = document.createElement('option');
                opt.value = y;
                opt.textContent = y;
                sel.appendChild(opt);
            }
        }

        async function loadMyGoals() {
            const year = document.getElementById('myGoalsYearFilter').value || new Date().getFullYear();
            try {
                const result = await apiCall(`api/goals.php?year=${year}`);
                if (result.csrf_token) csrfToken = result.csrf_token;
                renderMyGoals(result, year);
            } catch (err) {
                console.error('Error loading goals:', err);
                document.getElementById('goalMonthlyContent').innerHTML = '<p style="text-align:center; padding:40px; color:#dc2626; font-size:12px;">Failed to load goals data</p>';
            }
        }

        function renderMyGoals(data, year) {
            const goal = data.goal;
            const progress = data.progress;
            const monthly = data.monthly || [];

            // Cases card
            document.getElementById('goalCasesActual').textContent = progress.actual_cases;
            document.getElementById('goalCasesTarget').textContent = goal.target_cases;
            const casesPct = Math.min(100, progress.cases_percent || 0);
            document.getElementById('goalCasesBar').style.width = casesPct + '%';
            document.getElementById('goalCasesBar').style.background = casesPct >= 75 ? '#0d9488' : casesPct >= 50 ? '#d97706' : '#0d9488';
            document.getElementById('goalCasesPercent').textContent = casesPct.toFixed(0) + '% complete';

            // Legal fee card
            const feeActual = progress.actual_legal_fee || 0;
            const formatFee = (v) => v >= 1000000 ? '$' + (v/1000000).toFixed(1) + 'M' : v >= 1000 ? '$' + (v/1000).toFixed(0) + 'K' : '$' + v.toFixed(0);
            document.getElementById('goalFeeActual').textContent = formatFee(feeActual);
            document.getElementById('goalFeeTarget').textContent = formatFee(goal.target_legal_fee);
            const feePct = Math.min(100, progress.legal_fee_percent || 0);
            document.getElementById('goalFeeBar').style.width = feePct + '%';
            document.getElementById('goalFeePercent').textContent = feePct.toFixed(0) + '% complete';

            // Pace calculation
            const now = new Date();
            const currentYear = now.getFullYear();
            let expectedPct = 100;
            if (parseInt(year) === currentYear) {
                const monthsPassed = now.getMonth() + 1;
                expectedPct = (monthsPassed / 12) * 100;
            } else if (parseInt(year) > currentYear) {
                expectedPct = 0;
            }

            const expectedCases = Math.round(goal.target_cases * expectedPct / 100);
            const expectedFee = goal.target_legal_fee * expectedPct / 100;

            const casesPaceEl = document.getElementById('goalCasesPace');
            if (progress.actual_cases >= expectedCases) {
                casesPaceEl.innerHTML = '<span style="color:#0d9488;">On pace</span>';
            } else {
                casesPaceEl.innerHTML = `<span style="color:#d97706;">Expected: ${expectedCases} by now</span>`;
            }

            const feePaceEl = document.getElementById('goalFeePace');
            if (feeActual >= expectedFee) {
                feePaceEl.innerHTML = '<span style="color:#0d9488;">On pace</span>';
            } else {
                feePaceEl.innerHTML = `<span style="color:#d97706;">Expected: ${formatFee(expectedFee)} by now</span>`;
            }

            // Monthly breakdown table
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const monthData = {};
            monthly.forEach(m => { monthData[m.month] = m; });

            let cumCases = 0;
            let cumFee = 0;
            let tableRows = '';

            months.forEach(m => {
                const key = m + '. ' + year;
                const d = monthData[key];
                const cases = d ? parseInt(d.cases_count) : 0;
                const fee = d ? parseFloat(d.legal_fee_total) : 0;
                cumCases += cases;
                cumFee += fee;

                if (cases > 0 || fee > 0) {
                    tableRows += `<tr>
                        <td style="font-size:12px; font-weight:500;">${key}</td>
                        <td class="r" style="font-size:12px;">${cases}</td>
                        <td class="r" style="font-size:12px;">${formatFee(fee)}</td>
                        <td class="r" style="font-size:12px; font-weight:600;">${cumCases}</td>
                        <td class="r" style="font-size:12px; font-weight:600;">${formatFee(cumFee)}</td>
                    </tr>`;
                }
            });

            if (!tableRows) {
                document.getElementById('goalMonthlyContent').innerHTML = '<p style="text-align:center; padding:40px; color:#8b8fa3; font-size:12px;">No data for this year</p>';
                return;
            }

            document.getElementById('goalMonthlyContent').innerHTML = `
                <table class="tbl" style="table-layout: auto;">
                    <thead><tr>
                        <th>Month</th>
                        <th class="r">Cases</th>
                        <th class="r">Legal Fee</th>
                        <th class="r">Cumulative Cases</th>
                        <th class="r">Cumulative Fee</th>
                    </tr></thead>
                    <tbody>${tableRows}</tbody>
                    <tfoot>
                        <tr class="tbl-foot">
                            <td style="font-weight:700; font-size:12px;">Total</td>
                            <td class="r" style="font-weight:700; font-size:12px;">${cumCases}</td>
                            <td class="r" style="font-weight:700; font-size:12px;">${formatFee(cumFee)}</td>
                            <td></td><td></td>
                        </tr>
                    </tfoot>
                </table>`;
        }

    </script>
</body>
</html>

