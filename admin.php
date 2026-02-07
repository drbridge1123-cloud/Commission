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
    <link rel="stylesheet" href="assets/css/steel-minimal.css">
    <style>
        /* Sidebar SVG Icons */
        .sidebar-nav .nav-link svg { stroke: currentColor; fill: none; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }

        /* Toggle Switch */
        .toggle-switch { position: relative; display: inline-block; width: 36px; height: 20px; cursor: pointer; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider { position: absolute; inset: 0; background: #d1d5db; border-radius: 20px; transition: background 0.2s; }
        .toggle-slider::before { content: ''; position: absolute; height: 16px; width: 16px; left: 2px; bottom: 2px; background: white; border-radius: 50%; transition: transform 0.2s; }
        .toggle-switch input:checked + .toggle-slider { background: #0d9488; }
        .toggle-switch input:checked + .toggle-slider::before { transform: translateX(16px); }
        .toggle-switch input:disabled + .toggle-slider { opacity: 0.5; cursor: not-allowed; }

        /* INK COMPACT DESIGN SYSTEM - Admin */
        :root { --ink-bg: #f0f1f3; --ink-white: #fff; --ink-border: #e2e4ea; --ink-900: #1a1a2e; --ink-700: #3d3f4e; --ink-500: #5c5f73; --ink-400: #8b8fa3; --ink-teal: #0d9488; --ink-amber: #d97706; }

        /* Quick Stats */
        .quick-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 16px; }
        .qs-card { background: #fff; border-radius: 8px; padding: 14px 16px; border: 1px solid #e2e4ea; display: flex; justify-content: space-between; align-items: center; }
        .qs-label { font-size: 11px; color: #8b8fa3; font-weight: 500; text-transform: uppercase; letter-spacing: 0.3px; font-family: 'Outfit', sans-serif; }
        .qs-val { font-size: 20px; font-weight: 700; font-variant-numeric: tabular-nums; color: #1a1a2e; font-family: 'Outfit', sans-serif; }
        .qs-val.green { color: #0d9488; } .qs-val.amber { color: #d97706; } .qs-val.blue { color: #3b82f6; } .qs-val.red { color: #dc2626; } .qs-val.dim { color: #c4c7d0; }
        @media (max-width: 1200px) { .quick-stats { grid-template-columns: repeat(2, 1fr); } }

        /* Filters */
        .filters { display: flex; align-items: center; gap: 6px; margin-bottom: 12px; flex-wrap: wrap; }
        .f-chip { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; cursor: pointer; border: 1px solid #e2e4ea; background: #fff; color: #5c5f73; transition: all 0.12s; font-family: 'Outfit', sans-serif; user-select: none; }
        .f-chip:hover { background: #f5f5f7; }
        .f-chip.active { background: #1a1a2e; color: #fff; border-color: #1a1a2e; }
        .f-select { padding: 5px 28px 5px 10px; border: 1px solid #e2e4ea; border-radius: 20px; font-size: 12px; color: #5c5f73; background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%238b8fa3' viewBox='0 0 16 16'%3E%3Cpath d='M4.646 5.646a.5.5 0 01.708 0L8 8.293l2.646-2.647a.5.5 0 01.708.708l-3 3a.5.5 0 01-.708 0l-3-3a.5.5 0 010-.708z'/%3E%3C/svg%3E") right 8px center no-repeat; appearance: none; font-family: 'Outfit', sans-serif; cursor: pointer; }
        .f-spacer { flex: 1; }
        .f-search { padding: 5px 12px; border: 1px solid #e2e4ea; border-radius: 20px; font-size: 12px; width: 180px; background: #fff; font-family: 'Outfit', sans-serif; color: #5c5f73; }
        .f-btn { padding: 5px 14px; border: 1px solid #e2e4ea; border-radius: 20px; background: #fff; font-size: 11px; font-weight: 600; color: #5c5f73; cursor: pointer; font-family: 'Outfit', sans-serif; }
        .f-btn:hover { background: #f5f5f7; }

        /* Ink Compact Table */
        .tbl-container { background: #fff; border-radius: 10px; overflow: hidden; border: 1px solid #e2e4ea; }
        .tbl { width: 100%; border-collapse: collapse; font-family: 'Outfit', sans-serif; table-layout: fixed; }
        .tbl thead th { padding: 9px 6px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.6px; font-weight: 700; color: var(--text-500); background: var(--bg); border-bottom: 1px solid var(--border); text-align: left; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .tbl thead th.r { text-align: right; } .tbl thead th.c { text-align: center; }
        .tbl thead th .th-sort { display: inline-flex; align-items: center; gap: 3px; cursor: pointer; padding: 2px 3px; margin: -2px -3px; border-radius: 3px; }
        .tbl thead th .th-sort:hover { background: rgba(255,255,255,0.1); }
        .tbl tbody tr { border-bottom: 1px solid #f0f1f3; transition: background 0.08s; cursor: pointer; }
        .tbl tbody tr:hover { background: #f5f8ff; }
        .tbl tbody td { padding: 6px; font-size: 11px; color: #3d3f4e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .tbl tbody td.r { text-align: right; font-variant-numeric: tabular-nums; font-weight: 500; }
        .tbl tbody td.c { text-align: center; }
        .tbl tbody td.em { font-weight: 700; color: #0d9488; }
        .tbl tbody td.mute { color: #c4c7d0; }
        .tbl-foot { padding: 10px 12px; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; border-top: 1px solid #e2e4ea; font-family: 'Outfit', sans-serif; }
        .tbl-foot .left { font-size: 12px; color: #8b8fa3; }
        .tbl-foot .right { display: flex; gap: 16px; font-size: 12px; }
        .tbl-foot .ft { display: flex; align-items: center; gap: 4px; }
        .tbl-foot .ft-l { color: #8b8fa3; } .tbl-foot .ft-v { font-weight: 700; color: #1a1a2e; font-variant-numeric: tabular-nums; }
        .tbl-foot .ft-v.green { color: #0d9488; } .tbl-foot .ft-v.amber { color: #d97706; }

        /* Status & Resolution Badges */
        .stat-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; font-family: 'Outfit', sans-serif; }
        .stat-badge.unpaid { background: #fef3c7; color: #b45309; } .stat-badge.paid { background: #d1fae5; color: #065f46; }
        .stat-badge.pending { background: #fef3c7; color: #92400e; } .stat-badge.rejected { background: #fee2e2; color: #991b1b; }
        .stat-badge.in_progress { background: #dbeafe; color: #1d4ed8; }
        .res-chip { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #3d3f4e; }
        .res-dot { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }
        .res-dot.demand { background: #3b82f6; } .res-dot.filebump { background: #22c55e; } .res-dot.postdep { background: #8b5cf6; }
        .res-dot.mediation { background: #f59e0b; } .res-dot.other { background: #94a3b8; }

        /* Buttons */
        .ink-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; font-family: 'Outfit', sans-serif; transition: all 0.12s; }
        .ink-btn-primary { background: #1a1a2e; color: #fff; } .ink-btn-primary:hover { background: #2d2d4a; }
        .ink-btn-secondary { background: #fff; color: #5c5f73; border: 1px solid #e2e4ea; } .ink-btn-secondary:hover { background: #f5f5f7; }
        .ink-btn-success { background: #0d9488; color: #fff; } .ink-btn-success:hover { background: #0f766e; }
        .ink-btn-danger { background: #dc2626; color: #fff; } .ink-btn-danger:hover { background: #b91c1c; }
        .ink-btn-sm { padding: 5px 12px; font-size: 11px; }
        .act-link { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; border: none; background: #1a1a2e; color: #fff; font-family: 'Outfit', sans-serif; }
        .act-link:hover { background: #2d2d4a; }
        .act-link.success { background: #0d9488; } .act-link.success:hover { background: #0f766e; }
        .act-link.danger { background: transparent; color: #dc2626; border: 1px solid #fecaca; } .act-link.danger:hover { background: #fef2f2; }

        /* Chart Container */
        .ink-chart-container { background: #fff; border-radius: 10px; padding: 20px 24px; border: 1px solid #e2e4ea; font-family: 'Outfit', sans-serif; }
        .ink-chart-container h3 { font-size: 14px; font-weight: 600; color: #1a1a2e; margin-bottom: 16px; }

        /* Modal Header */
        .modal-header { background: var(--text-900) !important; color: white !important; }
        .modal-header h2, .modal-header h3 { color: white !important; font-family: 'Outfit', sans-serif; }
        .modal-close { color: rgba(255,255,255,0.7) !important; } .modal-close:hover { color: white !important; }

        /* Urgent Section */
        .ink-urgent-section { background: #fff; border-radius: 10px; padding: 16px 20px; margin-bottom: 16px; border: 1px solid #e2e4ea; font-family: 'Outfit', sans-serif; }
        .ink-urgent-section h3 { font-size: 14px; font-weight: 600; color: #1a1a2e; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }

        /* Form Inputs */
        .ink-label { display: block; font-size: 11px; font-weight: 600; color: #5c5f73; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.3px; font-family: 'Outfit', sans-serif; }
        .ink-input { width: 100%; padding: 7px 10px; border: 1px solid #e2e4ea; border-radius: 6px; font-size: 12px; font-family: 'Outfit', sans-serif; color: #3d3f4e; transition: border-color 0.15s; }
        .ink-input:focus { outline: none; border-color: #1a1a2e; }
        .ink-input::placeholder { color: #b4b8c5; }

        /* My Requests Item */
        .req-item { padding: 8px 12px; border-bottom: 1px solid #f0f1f3; cursor: pointer; transition: background 0.1s; }
        .req-item:hover { background: #f5f8ff; }
        .req-item:last-child { border-bottom: none; }
        .req-item .req-name { font-size: 12px; font-weight: 600; color: #1a1a2e; font-family: 'Outfit', sans-serif; }
        .req-item .req-meta { font-size: 10px; color: #8b8fa3; margin-top: 1px; font-family: 'Outfit', sans-serif; }
        .req-item .req-status { display: inline-block; padding: 2px 5px; border-radius: 3px; font-size: 8px; font-weight: 700; text-transform: uppercase; margin-left: 4px; }
        .req-item .req-status.pending { background: #fef3c7; color: #92400e; }
        .req-item .req-status.approved { background: #d1fae5; color: #065f46; }
        .req-item .req-status.denied { background: #fee2e2; color: #991b1b; }

        /* Action Buttons - Teal + Indigo */
        .action-group { display: flex; gap: 4px; }
        .action-group.center { justify-content: center; }
        .act-btn {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-family: inherit;
            transition: all 0.12s;
            white-space: nowrap;
        }
        .act-btn.approve { background: #0d9488; color: #fff; }
        .act-btn.approve:hover { background: #0f766e; }
        .act-btn.reject { background: #dc2626; color: #fff; }
        .act-btn.reject:hover { background: #b91c1c; }
        .act-btn.edit { background: transparent; color: #5c5f73; border: 1px solid #e2e4ea; }
        .act-btn.edit:hover { background: #f5f5f7; }
        .act-btn.mark-paid { background: #16a34a; color: #fff; }
        .act-btn.mark-paid:hover { background: #15803d; }

        /* Pending Table - Auto-fit columns without scroll */
        #pendingTable { table-layout: auto; width: 100%; }
        #pendingTable thead th { padding: 8px 6px; font-size: 10px; white-space: nowrap; }
        #pendingTable tbody td { padding: 6px; font-size: 11px; white-space: nowrap; overflow: visible; text-overflow: clip; }
        #content-pending .tbl-container { overflow-x: auto; }

        /* All Cases Table - Auto-fit columns */
        #allCasesTable { table-layout: auto; width: 100%; }
        #allCasesTable thead th { padding: 8px 6px; font-size: 10px; white-space: nowrap; }
        #allCasesTable tbody td { padding: 6px; font-size: 11px; white-space: nowrap; overflow: visible; text-overflow: clip; }
        #content-all .tbl-container { overflow-x: auto; }

        /* Override steel-minimal global width:100% for all filter rows */
        .filters select.f-select { width: auto !important; flex: 0 0 auto; }
        .filters input.f-search,
        .filters input[type="text"].f-search { width: 150px !important; flex: 0 0 auto; }
        .filters .f-btn,
        .filters .ink-btn { flex: 0 0 auto; }
        .filters .f-spacer { flex: 1; }
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
        <!-- Pending Tab - Ink Compact -->
        <div id="content-pending">
            <!-- Filters -->
            <div class="filters" style="margin-bottom: 12px;">
                <button onclick="bulkAction('approve')" class="act-btn approve" style="padding: 6px 14px; font-size: 12px;">Approve Selected</button>
                <button onclick="bulkAction('reject')" class="act-btn reject" style="padding: 6px 14px; font-size: 12px;">Reject Selected</button>
                <div class="f-spacer"></div>
            </div>

            <!-- Table -->
            <div class="tbl-container">
                <table class="tbl compact" id="pendingTable">
                    <thead>
                        <tr>
                            <th class="c" style="width:28px;padding:8px 4px;"><input type="checkbox" id="selectAllPending" onchange="toggleSelectAll('pending')"></th>
                            <th style="padding:8px 6px;">Counsel</th>
                            <th style="padding:8px 6px;">Month</th>
                            <th style="padding:8px 6px;">Case #</th>
                            <th style="padding:8px 6px;">Client</th>
                            <th class="r" style="padding:8px 6px;">Settled</th>
                            <th class="r" style="padding:8px 6px;">Pre-Suit</th>
                            <th class="r" style="padding:8px 6px;">Diff</th>
                            <th class="r" style="padding:8px 6px;">Legal Fee</th>
                            <th class="r" style="padding:8px 6px;">Disc. Fee</th>
                            <th class="r" style="padding:8px 6px;">Commission</th>
                            <th class="c" style="padding:8px 6px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="pendingBody"></tbody>
                </table>
                <div class="tbl-foot">
                    <span class="left" id="pendingFooterInfo">Showing 0 cases</span>
                    <div class="right">
                        <span class="ft"><span class="ft-l">Total Commission</span><span class="ft-v green" id="pendingFooterTotal">$0.00</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deadline Requests Tab -->
        <div id="content-deadline-requests" class="hidden">
            <div class="table-container">
                <div class="table-toolbar">
                    <div class="toolbar-actions">
                        <select id="filterDeadlineStatus" onchange="loadDeadlineRequests()" class="filter-select">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="all">All Requests</option>
                        </select>
                    </div>
                    <div class="search-box">
                        <input type="text" id="deadlineSearchInput" placeholder="Search requests..." onkeyup="filterDeadlineRequestsTable()">
                    </div>
                </div>

                <div class="table-scroll-wrapper scrollbar-fixed">
                    <table class="excel-table" id="deadlineRequestsTable">
                        <thead>
                            <tr>
                                <th>Date Requested</th>
                                <th>Employee</th>
                                <th>Case #</th>
                                <th>Client</th>
                                <th>Current Deadline</th>
                                <th>Requested Deadline</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="deadlineRequestsBody">
                            <tr><td colspan="9" style="text-align: center; padding: 40px; color: #6b7280;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div class="footer-info">
                        <span id="deadlineRequestsCount">0 requests</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deadline Request Review Modal -->
        <div id="deadlineReviewModal" class="modal-overlay hidden">
            <div class="modal-content" style="max-width: 550px;">
                <div class="modal-header">
                    <h2 id="deadlineReviewTitle">Review Deadline Request</h2>
                    <button class="modal-close" onclick="closeModal('deadlineReviewModal')">&times;</button>
                </div>
                <div class="modal-body" style="padding: 24px;">
                    <input type="hidden" id="deadlineReviewId">

                    <div style="background: #f3f4f6; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <span style="font-size: 12px; color: #6b7280;">Employee</span>
                                <div id="reviewRequesterName" style="font-weight: 600;"></div>
                            </div>
                            <div>
                                <span style="font-size: 12px; color: #6b7280;">Case</span>
                                <div id="reviewCaseInfo" style="font-weight: 600;"></div>
                            </div>
                            <div>
                                <span style="font-size: 12px; color: #6b7280;">Current Deadline</span>
                                <div id="reviewCurrentDeadline" style="font-weight: 600;"></div>
                            </div>
                            <div>
                                <span style="font-size: 12px; color: #6b7280;">Requested Deadline</span>
                                <div id="reviewRequestedDeadline" style="font-weight: 600; color: #059669;"></div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <span style="font-size: 12px; color: #6b7280;">Reason for Extension</span>
                        <div id="reviewReason" style="background: #fefce8; padding: 12px; border-radius: 8px; margin-top: 4px; white-space: pre-wrap;"></div>
                    </div>

                    <div class="form-group">
                        <label>Admin Note (Optional)</label>
                        <textarea id="deadlineAdminNote" rows="2" placeholder="Add a note for the employee..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="display: flex; justify-content: space-between;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deadlineReviewModal')">Cancel</button>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn btn-danger" onclick="processDeadlineRequest('reject')">Reject</button>
                        <button type="button" class="btn btn-primary" onclick="processDeadlineRequest('approve')">Approve</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- All Cases Tab - Ink Compact -->
        <div id="content-all" class="hidden">
            <!-- Filters - Compact Row -->
            <div class="filters" style="margin-bottom: 12px;">
                <select id="filterCounsel" onchange="loadAllCases()" class="f-select">
                    <option value="all">All Counsel</option>
                    <option value="charb">Charb</option>
                    <option value="chong">Chong</option>
                    <option value="soyong">Soyong</option>
                    <option value="dave">Dave</option>
                    <option value="ella">Ella</option>
                    <option value="jimi">Jimi</option>
                </select>
                <select id="filterAllMonth" onchange="loadAllCases()" class="f-select">
                    <option value="all">All Months</option>
                </select>
                <select id="filterAllStatus" onchange="loadAllCases()" class="f-select">
                    <option value="all">All Status</option>
                    <option value="in_progress">In Progress</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="paid">Paid</option>
                    <option value="rejected">Rejected</option>
                </select>
                <div class="f-spacer"></div>
                <input type="text" id="searchAll" placeholder="Search..." class="f-search" onkeyup="filterAllCases()">
                <button onclick="exportAllToExcel()" class="f-btn">Export</button>
            </div>

            <!-- Table - Optimized Columns -->
            <div class="tbl-container">
                <div id="allCasesTableWrapper">
                    <table class="tbl" id="allCasesTable" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('counsel_name')">Counsel</span></th>
                                <th class="c" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('status')">Status</span></th>
                                <th style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('month')">Month</span></th>
                                <th style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('case_number')">Case #</span></th>
                                <th style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('client_name')">Client</span></th>
                                <th style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('resolution_type')">Resolution</span></th>
                                <th class="r" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('settled')">Settled</span></th>
                                <th class="r" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('presuit_offer')">Pre-Suit</span></th>
                                <th class="r" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('difference')">Diff</span></th>
                                <th class="r" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('legal_fee')">Legal Fee</span></th>
                                <th class="r" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('discounted_legal_fee')">Disc. Fee</span></th>
                                <th class="r" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('commission')">Commission</span></th>
                                <th class="c" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('check_received')">Check</span></th>
                                <th class="c" style="padding:8px 6px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="allCasesBody"></tbody>
                    </table>
                </div>
                <div class="tbl-foot">
                    <span class="left" id="allCasesFooterInfo">Showing 0 cases</span>
                    <div class="right">
                        <span class="ft"><span class="ft-l">Total Commission</span><span class="ft-v green" id="allCasesFooterTotal">$0.00</span></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Case Detail Modal (For All Cases Tab) -->
        <div id="allCaseDetailModal" class="modal-overlay" onclick="if(event.target === this) closeCaseModal()">
            <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 680px; max-height: 90vh; border-radius: 12px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.12); overflow: hidden; display: flex; flex-direction: column;">
                <!-- Dark Header -->
                <div style="background: #0f172a; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 3px; height: 16px; background: #22d3ee; border-radius: 2px;"></div>
                        <h2 style="font-size: 14px; font-weight: 600; color: white; margin: 0;">Case Details</h2>
                    </div>
                    <button onclick="closeCaseModal()" style="width: 26px; height: 26px; background: rgba(255,255,255,0.1); border: none; border-radius: 6px; color: rgba(255,255,255,0.7); font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
                </div>

                <!-- Content Area (Scrollable) -->
                <div style="padding: 12px 20px; overflow-y: auto; flex: 1;">
                    <div id="caseDetailContent">
                        <!-- Details will be loaded here -->
                    </div>
                    <!-- Send Message Section -->
                    <div id="caseMessageSection" style="margin-top: 14px; padding: 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <div style="font-size: 11px; font-weight: 600; color: #374151; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.3px;">
                            💬 Send Message to <span id="caseMessageRecipient"></span>
                        </div>
                        <input type="text" id="caseMessageSubject" placeholder="Subject" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; margin-bottom: 8px;">
                        <textarea id="caseMessageBody" rows="2" placeholder="Type your message..." style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; resize: vertical;"></textarea>
                        <button onclick="sendCaseMessage()" style="margin-top: 8px; padding: 7px 14px; background: #0f172a; color: white; border: none; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;">Send Message</button>
                    </div>
                </div>

                <!-- Footer (Fixed) -->
                <div style="background: #f8fafc; padding: 10px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                    <div id="caseApprovalButtons"></div>
                    <div style="display: flex; gap: 8px;">
                        <button onclick="editCaseFromModal()" style="padding: 7px 14px; background: #0f172a; color: white; border: none; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;">Edit</button>
                        <button onclick="closeCaseModal()" style="padding: 7px 12px; background: white; color: #64748b; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 11px; font-weight: 500; cursor: pointer;">Close</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Edit Case Modal -->
        <div id="editCaseModal" class="modal-overlay" onclick="if(event.target === this) closeEditModal()">
            <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 680px; max-height: 90vh; border-radius: 12px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.12); overflow: hidden; display: flex; flex-direction: column;">
                <!-- Dark Header -->
                <div style="background: #0f172a; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 3px; height: 16px; background: #22d3ee; border-radius: 2px;"></div>
                        <h2 style="font-size: 14px; font-weight: 600; color: white; margin: 0;">Edit Case</h2>
                    </div>
                    <button onclick="closeEditModal()" style="width: 26px; height: 26px; background: rgba(255,255,255,0.1); border: none; border-radius: 6px; color: rgba(255,255,255,0.7); font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
                </div>

                <form id="editCaseForm" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                    <input type="hidden" id="editCaseId">

                    <!-- Content Area (Scrollable) -->
                    <div style="padding: 12px 20px; overflow-y: auto; flex: 1;">
                        <!-- Row 1: Client Name & Case Number -->
                        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Client Name</label>
                                <input type="text" id="editClientName" required style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; outline: none; transition: all 0.2s;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Case Number</label>
                                <input type="text" id="editCaseNumber" required style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; outline: none; transition: all 0.2s;">
                            </div>
                        </div>

                        <!-- Row 2: Case Type & Resolution -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Case Type</label>
                                <select id="editCaseType" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;">
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
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Resolution</label>
                                <select id="editResolutionType" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;">
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

                        <!-- Row 3: Month, Fee Rate & Status -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                            <div>
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Month</label>
                                <select id="editMonth" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;"></select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Fee Rate</label>
                                <select id="editFeeRate" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;">
                                    <option value="33.33">1/3 (33.33%)</option>
                                    <option value="40">40%</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Status</label>
                                <select id="editStatus" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;">
                                    <option value="in_progress">In Progress</option>
                                    <option value="unpaid">Unpaid</option>
                                    <option value="paid">Paid</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>

                        <!-- Financial Details Section -->
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
                            <!-- Section Header -->
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                                <div style="width: 26px; height: 26px; background: #0f172a; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px;">💰</div>
                                <span style="font-size: 12px; font-weight: 600; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">Financial Details</span>
                            </div>

                            <!-- Row: Settled & Pre-Suit -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                <div>
                                    <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Settled Amount</label>
                                    <input type="number" step="0.01" id="editSettled" required style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Pre-Suit Offer</label>
                                    <input type="number" step="0.01" id="editPresuitOffer" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none;">
                                </div>
                            </div>

                            <!-- Row: Calculated fields -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                <div>
                                    <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Difference</label>
                                    <input type="text" id="editDifference" readonly style="width: 100%; padding: 8px 12px; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 13px; color: #64748b; background: #f8fafc; outline: none;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Legal Fee</label>
                                    <input type="text" id="editLegalFee" readonly style="width: 100%; padding: 8px 12px; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 13px; color: #64748b; background: #f8fafc; outline: none;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Disc. Legal Fee</label>
                                    <input type="number" step="0.01" id="editDiscountedLegalFee" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none;">
                                </div>
                            </div>

                            <!-- Commission Card -->
                            <div style="background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; position: relative; overflow: hidden;">
                                <div style="position: absolute; right: 0; top: 0; bottom: 0; width: 80px; background: linear-gradient(135deg, rgba(34, 211, 238, 0.1), rgba(168, 85, 247, 0.1));"></div>
                                <span style="font-size: 11px; font-weight: 500; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.5px;">Commission</span>
                                <span style="font-size: 22px; font-weight: 700; color: #22d3ee; position: relative; z-index: 1;" id="editCommission">$0.00</span>
                            </div>
                        </div>

                        <!-- Note & Check Received -->
                        <div style="display: flex; gap: 12px; align-items: flex-end;">
                            <div style="flex: 1;">
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Note</label>
                                <input type="text" id="editNote" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; outline: none;" placeholder="Optional note...">
                            </div>
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; display: flex; align-items: center; gap: 6px;">
                                <input type="checkbox" id="editCheckReceived" style="width: 14px; height: 14px; accent-color: #0f172a; cursor: pointer;">
                                <label for="editCheckReceived" style="font-size: 12px; color: #374151; white-space: nowrap; cursor: pointer;">Check Received</label>
                            </div>
                        </div>
                    </div>

                    <!-- Footer (Fixed) -->
                    <div style="background: #f8fafc; padding: 10px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                        <button type="button" onclick="deleteFromEditModal()" style="padding: 7px 12px; background: transparent; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; font-size: 11px; font-weight: 500; cursor: pointer; transition: all 0.2s;">Delete</button>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" onclick="closeEditModal()" style="padding: 7px 12px; background: white; color: #64748b; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 11px; font-weight: 500; cursor: pointer; transition: all 0.2s;">Cancel</button>
                            <button type="submit" style="padding: 7px 16px; background: #0f172a; color: white; border: none; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.2s;">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Dashboard Tab -->
        <div id="content-dashboard" class="hidden">
            <div id="dashboardCard">
            <!-- Row 1: Quick Stats (6 cards) -->
            <div class="quick-stats" style="grid-template-columns: repeat(6, 1fr);">
                <div class="qs-card">
                    <div><div class="qs-label">Total Cases</div><div class="qs-val" id="statTotalCases">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Pending</div><div class="qs-val amber" id="statPending">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Total Commission</div><div class="qs-val green" id="statTotalCommission">$0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Avg Commission</div><div class="qs-val blue" id="statAvgCommission">$0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Check Received</div><div class="qs-val" id="statCheckRate">0%</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Unreceived</div><div class="qs-val red" id="statUnreceived">$0</div></div>
                </div>
            </div>

            <!-- Row 2: This Month vs Last Month -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="ink-chart-container" style="padding: 16px 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h3 style="margin: 0;">This Month</h3>
                        <span id="thisMonthName" style="font-size: 11px; color: #8b8fa3;"></span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Cases</div>
                            <div style="font-size: 20px; font-weight: 700; color: #1a1a2e;" id="thisMonthCases">0</div>
                            <div style="font-size: 10px; margin-top: 2px;" id="thisMonthCasesChange"></div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Commission</div>
                            <div style="font-size: 20px; font-weight: 700; color: #0d9488;" id="thisMonthComm">$0</div>
                            <div style="font-size: 10px; margin-top: 2px;" id="thisMonthCommChange"></div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Approved</div>
                            <div style="font-size: 20px; font-weight: 700; color: #3b82f6;" id="thisMonthApproved">0</div>
                            <div style="font-size: 10px; margin-top: 2px;" id="thisMonthApprovedChange"></div>
                        </div>
                    </div>
                </div>
                <div class="ink-chart-container" style="padding: 16px 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h3 style="margin: 0;">Last Month</h3>
                        <span id="lastMonthName" style="font-size: 11px; color: #8b8fa3;"></span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Cases</div>
                            <div style="font-size: 20px; font-weight: 700; color: #1a1a2e;" id="lastMonthCases">0</div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Commission</div>
                            <div style="font-size: 20px; font-weight: 700; color: #0d9488;" id="lastMonthComm">$0</div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Approved</div>
                            <div style="font-size: 20px; font-weight: 700; color: #3b82f6;" id="lastMonthApproved">0</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Monthly Trend Chart & Cases by Status -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="ink-chart-container">
                    <h3>Monthly Commission Trend</h3>
                    <div style="height: 220px;">
                        <canvas id="dashboardTrendChart"></canvas>
                    </div>
                </div>
                <div class="ink-chart-container">
                    <h3>Cases by Status</h3>
                    <div style="height: 220px; display: flex; align-items: center; justify-content: center;">
                        <canvas id="dashboardStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Row 4: Commission by Counsel & Top 5 Cases -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="ink-chart-container">
                    <h3>Commission by Counsel</h3>
                    <div id="counselStats"></div>
                </div>
                <div class="ink-chart-container">
                    <h3>Top 5 Highest Commission Cases</h3>
                    <div id="topCasesStats">
                        <div style="padding: 20px; text-align: center; color: #8b8fa3; font-size: 12px;">Loading...</div>
                    </div>
                </div>
            </div>

            <!-- Row 5: Upcoming Deadlines & Recent Activity -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="ink-chart-container">
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #dc2626;">⚠</span> Upcoming Deadlines
                    </h3>
                    <div id="upcomingDeadlines">
                        <div style="padding: 20px; text-align: center; color: #8b8fa3; font-size: 12px;">Loading...</div>
                    </div>
                </div>
                <div class="ink-chart-container">
                    <h3>Commission by Month</h3>
                    <div id="monthStats"></div>
                </div>
            </div>
            </div>
        </div>

        <!-- Reports Tab -->
        <div id="content-report" class="hidden">
            <!-- Filters -->
            <div class="filters" style="margin-bottom: 16px;">
                <button onclick="exportReportToExcel()" class="ink-btn ink-btn-secondary ink-btn-sm">Export Excel</button>
            </div>

            <div id="reportCard">

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="qs-card">
                    <div><div class="qs-label">Monthly Summary</div><div class="qs-val blue" id="report-monthly-amount">$0</div></div>
                    <div style="font-size: 11px; color: #8b8fa3;" id="report-monthly-cases">0 cases</div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Year-to-Date</div><div class="qs-val green" id="report-ytd-amount">$0</div></div>
                    <div style="font-size: 11px; color: #8b8fa3;" id="report-ytd-cases">0 cases</div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Average Commission</div><div class="qs-val" id="report-avg-amount">$0</div></div>
                    <div style="font-size: 11px; color: #8b8fa3;">per case</div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Pending Payments</div><div class="qs-val amber" id="report-pending-amount">$0</div></div>
                    <div style="font-size: 11px; color: #8b8fa3;" id="report-pending-cases">0 cases</div>
                </div>
            </div>

            <!-- Commission Trend Chart -->
            <div class="ink-chart-container" style="margin-bottom: 16px;">
                <h3>Commission by Month</h3>
                <div style="height: 280px;">
                    <canvas id="commissionByMonthChart"></canvas>
                </div>
            </div>

            <!-- Analysis Tables Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <!-- By Counsel Table -->
                <div class="tbl-container">
                    <div style="padding: 12px 16px; background: #1a1a2e; border-radius: 10px 10px 0 0;">
                        <h3 style="font-size: 13px; font-weight: 600; color: #fff; font-family: 'Outfit', sans-serif;">Commission by Counsel</h3>
                    </div>
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Counsel</th>
                                <th class="r">Cases</th>
                                <th class="r">Total</th>
                                <th class="r">Avg</th>
                                <th class="r">%</th>
                            </tr>
                        </thead>
                        <tbody id="counselTableBody">
                            <tr><td colspan="5" style="text-align: center; padding: 20px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- By Case Type Table -->
                <div class="tbl-container">
                    <div style="padding: 12px 16px; background: #1a1a2e; border-radius: 10px 10px 0 0;">
                        <h3 style="font-size: 13px; font-weight: 600; color: #fff; font-family: 'Outfit', sans-serif;">Commission by Case Type</h3>
                    </div>
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th class="r">Cases</th>
                                <th class="r">Total</th>
                                <th class="r">Avg</th>
                                <th class="r">%</th>
                            </tr>
                        </thead>
                        <tbody id="caseTypeTableBody">
                            <tr><td colspan="5" style="text-align: center; padding: 20px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Monthly Breakdown Table -->
            <div class="tbl-container">
                <div style="padding: 12px 16px; background: #1a1a2e; border-radius: 10px 10px 0 0;">
                    <h3 style="font-size: 13px; font-weight: 600; color: #fff; font-family: 'Outfit', sans-serif;">Monthly Breakdown</h3>
                </div>
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="r">Cases</th>
                            <th class="r">Total</th>
                            <th class="r">Avg</th>
                            <th class="r">Received</th>
                            <th class="r">Pending</th>
                        </tr>
                    </thead>
                    <tbody id="monthlyBreakdownTableBody">
                        <tr><td colspan="6" style="text-align: center; padding: 20px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
            </div>
        </div>

        <!-- History Tab -->
        <div id="content-history" class="hidden">
            <div id="historyCard">
                <!-- Filters -->
                <div class="filters" style="margin-bottom: 12px;">
                    <input type="text" id="historySearch" placeholder="Search..." class="f-search" onkeyup="loadHistory()">
                    <select id="historyEmployee" onchange="loadHistory()" class="f-select">
                        <option value="all">All Employees</option>
                    </select>
                    <select id="historyMonth" onchange="loadHistory()" class="f-select">
                        <option value="all">All Months</option>
                    </select>
                    <button onclick="resetHistoryFilters()" class="f-btn">Reset</button>
                    <span class="f-spacer"></span>
                    <button onclick="exportHistoryAdmin()" class="f-btn">Export</button>
                </div>

                <div class="tbl-container">
                    <div id="historyTableContainer" style="overflow-x: auto;">
                        <div id="historyContent">
                            <!-- History will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Detail Modal -->
        <div id="historyDetailModal" class="modal-overlay" onclick="if(event.target === this) this.classList.remove('show')">
            <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 560px; max-height: 90vh; border-radius: 12px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.12); overflow: hidden; display: flex; flex-direction: column;">
                <div style="background: #0f172a; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                    <h3 style="font-size: 14px; font-weight: 600; color: #fff; margin: 0; font-family: 'Outfit', sans-serif;">Payment Detail</h3>
                    <span class="modal-close" onclick="document.getElementById('historyDetailModal').classList.remove('show')" style="cursor: pointer; font-size: 20px; color: rgba(255,255,255,0.7);">&times;</span>
                </div>
                <div style="padding: 16px 20px; overflow-y: auto; flex: 1;" id="historyDetailContent">
                </div>
            </div>
        </div>

        <!-- Goals Tab -->
        <div id="content-goals" class="hidden">
            <!-- Filters -->
            <div class="filters" style="margin-bottom: 16px;">
                <select id="goalsYearFilter" class="f-select" onchange="loadGoalsData()">
                </select>
                <button onclick="loadGoalsData()" class="f-btn" style="padding: 5px 12px; font-size: 11px;">Refresh</button>
            </div>

            <!-- Hero Cards -->
            <div class="hero-row" style="margin-bottom: 16px;">
                <div class="qs-card"><div><div class="qs-label">Employees Tracked</div><div class="qs-val" id="goalsHeroCount">0</div></div></div>
                <div class="qs-card"><div><div class="qs-label">Avg Cases Progress</div><div class="qs-val" id="goalsHeroCases">0%</div></div></div>
                <div class="qs-card"><div><div class="qs-label">Avg Legal Fee Progress</div><div class="qs-val" id="goalsHeroFee">0%</div></div></div>
            </div>

            <!-- Goals Table -->
            <div class="tbl-container">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th class="r">Cases</th>
                            <th style="width:120px;">Progress</th>
                            <th class="r">Legal Fee</th>
                            <th style="width:120px;">Progress</th>
                            <th class="c">Pace</th>
                            <th class="c" style="width:60px;">Edit</th>
                        </tr>
                    </thead>
                    <tbody id="goalsTableBody">
                        <tr><td colspan="7" style="text-align:center; padding:40px; color:#8b8fa3;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Edit Goal Modal -->
            <div id="editGoalModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; display:none; align-items:center; justify-content:center;">
                <div style="background:#fff; border-radius:10px; width:400px; max-width:90vw; overflow:hidden;">
                    <div class="modal-header" style="padding:14px 20px; display:flex; justify-content:space-between; align-items:center;">
                        <h3 style="margin:0; font-size:15px;" id="editGoalTitle">Edit Goal</h3>
                        <span class="modal-close" onclick="closeGoalModal()" style="cursor:pointer; font-size:20px;">&times;</span>
                    </div>
                    <div style="padding:20px;">
                        <input type="hidden" id="goalEditUserId">
                        <div style="margin-bottom:12px;">
                            <label class="ink-label">Year</label>
                            <input type="number" id="goalEditYear" class="ink-input" min="2020" max="2030">
                        </div>
                        <div style="margin-bottom:12px;">
                            <label class="ink-label">Target Cases</label>
                            <input type="number" id="goalEditCases" class="ink-input" min="1" max="999" value="50">
                        </div>
                        <div style="margin-bottom:12px;">
                            <label class="ink-label">Target Legal Fee ($)</label>
                            <input type="number" id="goalEditFee" class="ink-input" min="0" step="1000" value="500000">
                        </div>
                        <div style="margin-bottom:16px;">
                            <label class="ink-label">Notes</label>
                            <textarea id="goalEditNotes" class="ink-input" rows="2" style="resize:vertical;"></textarea>
                        </div>
                        <div style="display:flex; gap:8px; justify-content:flex-end;">
                            <button onclick="closeGoalModal()" class="f-btn" style="padding:6px 16px; font-size:12px;">Cancel</button>
                            <button onclick="saveGoal()" class="ink-btn ink-btn-primary ink-btn-sm">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Control Tab -->
        <div id="content-admin-control" class="hidden">
            <!-- Filters / Actions -->
            <div class="filters">
                <button onclick="openAddUserModal()" class="ink-btn ink-btn-primary ink-btn-sm">+ Add User</button>
            </div>

            <div class="tbl-container">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Display Name</th>
                            <th class="c">Role</th>
                            <th class="r">Commission Rate</th>
                            <th class="c">Traffic</th>
                            <th class="c">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr><td colspan="6" style="padding: 24px; text-align: center; color: #8b8fa3; font-size: 12px;">Loading users...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Traffic Cases Tab (Admin View) -->
        <div id="content-traffic" class="hidden">
            <!-- Quick Stats -->
            <div class="quick-stats" style="grid-template-columns: repeat(4, 1fr);">
                <div class="qs-card"><div><div class="qs-label">Active Cases</div><div class="qs-val blue" id="trafficStatActive">0</div></div></div>
                <div class="qs-card"><div><div class="qs-label">Dismissed</div><div class="qs-val green" id="trafficStatDismissed">0</div></div></div>
                <div class="qs-card"><div><div class="qs-label">Amended</div><div class="qs-val amber" id="trafficStatAmended">0</div></div></div>
                <div class="qs-card"><div><div class="qs-label">Pending Req.</div><div class="qs-val" style="color: #8b5cf6;" id="trafficStatPendingReq">0</div></div></div>
            </div>

            <div style="display: flex; gap: 12px;">
                <!-- Left Sidebar: Overview & Filters -->
                <div style="width: 280px; flex-shrink: 0; display: flex; flex-direction: column; gap: 10px;">
                    <!-- Tab Filter Card -->
                    <div style="background: white; border: 1px solid #e2e4ea; border-radius: 10px; overflow: hidden;">
                        <div style="padding: 6px; background: #f8f9fa; display: flex; gap: 2px;">
                            <button onclick="switchAdminTrafficTab('all')" id="adminTrafficTab-all" class="f-chip active" style="flex: 1; padding: 6px 4px; border-radius: 6px; font-size: 11px;">All</button>
                            <button onclick="switchAdminTrafficTab('referral')" id="adminTrafficTab-referral" class="f-chip" style="flex: 1; padding: 6px 4px; border-radius: 6px; font-size: 11px;">Referral</button>
                            <button onclick="switchAdminTrafficTab('court')" id="adminTrafficTab-court" class="f-chip" style="flex: 1; padding: 6px 4px; border-radius: 6px; font-size: 11px;">Court</button>
                            <button onclick="switchAdminTrafficTab('year')" id="adminTrafficTab-year" class="f-chip" style="flex: 1; padding: 6px 4px; border-radius: 6px; font-size: 11px;">Year</button>
                        </div>
                        <div id="adminTrafficSidebarContent" style="padding: 12px;">
                            <div style="font-size: 11px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Overview</div>
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                <div style="display: flex; justify-content: space-between; font-family: 'Outfit', sans-serif;">
                                    <span style="font-size: 12px; color: #3d3f4e;">Total Cases</span>
                                    <span style="font-size: 13px; font-weight: 700; color: #1a1a2e;" id="trafficStatTotal">0</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-family: 'Outfit', sans-serif;">
                                    <span style="font-size: 12px; color: #3b82f6;">Active</span>
                                    <span style="font-size: 13px; font-weight: 700; color: #3b82f6;" id="trafficOverviewActive">0</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-family: 'Outfit', sans-serif;">
                                    <span style="font-size: 12px; color: #0d9488;">Done</span>
                                    <span style="font-size: 13px; font-weight: 700; color: #0d9488;" id="trafficOverviewDone">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Request Form - Compact -->
                    <div class="tbl-container">
                        <div style="padding: 10px 14px; background: #1a1a2e; border-radius: 10px 10px 0 0;">
                            <h3 style="font-size: 12px; font-weight: 600; color: #fff; font-family: 'Outfit', sans-serif; margin: 0;">Request New Traffic Case</h3>
                        </div>
                        <form id="trafficRequestForm" style="padding: 12px 14px; display: flex; flex-direction: column; gap: 8px;">
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Client Name *</label>
                                <input type="text" id="reqClientName" required class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Phone</label>
                                    <input type="text" id="reqClientPhone" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Email</label>
                                    <input type="email" id="reqClientEmail" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Ticket #</label>
                                    <input type="text" id="reqCaseNumber" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Court</label>
                                    <input type="text" id="reqCourt" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Charge</label>
                                    <input type="text" id="reqCharge" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Requester</label>
                                    <input type="text" id="reqReferralSource" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Issued</label>
                                    <input type="date" id="reqCitationIssuedDate" class="ink-input" style="padding: 5px 6px; font-size: 11px;">
                                </div>
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Court Date</label>
                                    <input type="date" id="reqCourtDate" class="ink-input" style="padding: 5px 6px; font-size: 11px;">
                                </div>
                            </div>
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Note</label>
                                <textarea id="reqNote" rows="2" class="ink-input" style="padding: 6px 8px; font-size: 12px; resize: none;"></textarea>
                            </div>
                            <button type="submit" class="ink-btn ink-btn-primary" style="width: 100%; justify-content: center; padding: 8px 12px; font-size: 12px;">Submit Request</button>
                        </form>
                    </div>

                    <!-- All Requests History -->
                    <div class="tbl-container">
                        <div style="padding: 10px 14px; background: #1a1a2e; border-radius: 10px 10px 0 0; display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="font-size: 12px; font-weight: 600; color: #fff; font-family: 'Outfit', sans-serif; margin: 0;">All Requests</h3>
                        </div>
                        <div style="padding: 8px 10px; border-bottom: 1px solid #e2e4ea;">
                            <input type="text" id="myRequestsSearch" placeholder="Search..."
                                style="width: 100%; padding: 5px 8px; border: 1px solid #e2e4ea; border-radius: 5px; font-size: 11px; font-family: 'Outfit', sans-serif;"
                                oninput="filterMyRequests(this.value)">
                        </div>
                        <div id="myTrafficRequests" style="max-height: 300px; overflow-y: auto;">
                            <p style="padding: 12px; text-align: center; color: #8b8fa3; font-size: 11px; font-family: 'Outfit', sans-serif;">Loading...</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: All Traffic Cases -->
                <div style="flex: 1; min-width: 0;">
                    <div class="tbl-container">
                        <div style="padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e4ea;">
                            <div>
                                <h3 style="font-size: 14px; font-weight: 600; color: #1a1a2e; font-family: 'Outfit', sans-serif;">Traffic Cases</h3>
                                <p style="font-size: 11px; color: #8b8fa3;" id="trafficFilterLabel">All Cases</p>
                            </div>
                            <div style="display: flex; gap: 6px;">
                                <input type="text" class="f-search" id="adminTrafficSearch" placeholder="Search..." oninput="searchAdminTraffic(this.value)" style="width: 140px;">
                            </div>
                        </div>

                        <!-- Status Filter Tabs -->
                        <div style="padding: 8px 16px; background: #f8f9fa; border-bottom: 1px solid #e2e4ea; display: flex; gap: 6px;">
                            <span class="f-chip" onclick="filterAdminTraffic('all', this)" id="adminTrafficStatusBtn-all">
                                All <span id="trafficCountAll" style="opacity: 0.7;">0</span>
                            </span>
                            <span class="f-chip active" onclick="filterAdminTraffic('active', this)" id="adminTrafficStatusBtn-active">
                                Active <span id="trafficCountActive" style="opacity: 0.7;">0</span>
                            </span>
                            <span class="f-chip" onclick="filterAdminTraffic('resolved', this)" id="adminTrafficStatusBtn-resolved">
                                Done <span id="trafficCountDone" style="opacity: 0.7;">0</span>
                            </span>
                        </div>

                        <div style="max-height: 730px; overflow-y: auto;">
                            <table class="tbl" id="adminTrafficTable">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Court</th>
                                        <th>Court Date</th>
                                        <th>Charge</th>
                                        <th class="c">NOA</th>
                                        <th class="c">Discovery</th>
                                        <th>Disposition</th>
                                        <th class="c">Status</th>
                                        <th>Requester</th>
                                        <th class="c">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="adminTrafficTableBody">
                                    <tr><td colspan="10" style="padding: 32px 16px; text-align: center; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tbl-foot">
                            <div class="left"><span id="trafficTableCount">0</span> cases</div>
                            <div class="right">
                                <div class="ft"><span class="ft-l">Dismissed:</span><span class="ft-v green" id="trafficFootDismissed">0</span></div>
                                <div class="ft"><span class="ft-l">Amended:</span><span class="ft-v amber" id="trafficFootAmended">0</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Traffic Case Edit Modal -->
        <div id="adminTrafficModal" class="modal-overlay" style="display: none;" onclick="if(event.target === this) closeAdminTrafficModal()">
            <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 560px;">
                <div class="modal-header">
                    <h3 style="font-size: 14px; font-weight: 600; font-family: 'Outfit', sans-serif;">Edit Traffic Case</h3>
                    <button onclick="closeAdminTrafficModal()" class="modal-close">&times;</button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 16px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="ink-label">Client Name</label>
                            <input type="text" id="adminTrafficClientName" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">Client Phone</label>
                            <input type="text" id="adminTrafficClientPhone" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">Court</label>
                            <input type="text" id="adminTrafficCourt" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">Court Date</label>
                            <input type="date" id="adminTrafficCourtDate" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">Charge</label>
                            <input type="text" id="adminTrafficCharge" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">Ticket #</label>
                            <input type="text" id="adminTrafficCaseNumber" class="ink-input">
                        </div>
                        <div style="grid-column: span 2;">
                            <label class="ink-label">Prosecutor Offer</label>
                            <input type="text" id="adminTrafficOffer" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">Disposition</label>
                            <select id="adminTrafficDisposition" class="ink-input">
                                <option value="pending">Pending</option>
                                <option value="dismissed">Dismissed</option>
                                <option value="amended">Amended</option>
                            </select>
                        </div>
                        <div>
                            <label class="ink-label">Status</label>
                            <select id="adminTrafficStatus" class="ink-input">
                                <option value="active">Active</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>
                        <div>
                            <label class="ink-label">Ticket Issued</label>
                            <input type="date" id="adminTrafficTicketIssuedDate" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">NOA Sent</label>
                            <input type="date" id="adminTrafficNoaSentDate" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">Referral Source</label>
                            <input type="text" id="adminTrafficReferralSource" class="ink-input">
                        </div>
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-top: 18px; font-family: 'Outfit', sans-serif;">
                                <input type="checkbox" id="adminTrafficDiscovery" style="width: 16px; height: 16px;">
                                <span style="font-size: 12px; color: #3d3f4e;">Discovery Received</span>
                            </label>
                        </div>
                        <div style="grid-column: span 2;">
                            <label class="ink-label">Note</label>
                            <textarea id="adminTrafficNote" class="ink-input" rows="2" style="resize: vertical;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="display: flex; gap: 8px; justify-content: flex-end; padding: 12px 16px; border-top: 1px solid #e2e4ea;">
                    <button type="button" onclick="closeAdminTrafficModal()" class="ink-btn ink-btn-secondary ink-btn-sm">Cancel</button>
                    <button type="button" onclick="saveAdminTrafficCase()" class="ink-btn ink-btn-primary ink-btn-sm">Save Changes</button>
                </div>
            </div>
        </div>

        <!-- Add/Edit User Modal -->
        <div id="userModal" class="modal-overlay" onclick="if(event.target === this) closeUserModal()">
            <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 500px;">
                <div class="modal-header">
                    <h2 class="text-xl font-bold text-gray-900" id="userModalTitle">Add User</h2>
                    <button onclick="closeUserModal()" class="modal-close">&times;</button>
                </div>
                <form id="userForm" class="modal-body" style="display: flex; flex-direction: column; gap: 16px;">
                    <input type="hidden" id="editUserId">
                    
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Username</label>
                        <input type="text" id="userUsername" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Display Name</label>
                        <input type="text" id="userDisplayName" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Password</label>
                        <input type="password" id="userPassword" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        <p style="font-size: 12px; color: #6b7280; margin-top: 4px;" id="passwordHint">Leave blank to keep current password</p>
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Role</label>
                        <select id="userRole" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                            <option value="employee">Employee</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Commission Rate (%)</label>
                        <input type="number" step="0.01" id="userCommissionRate" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    </div>
                    
                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Save</button>
                        <button type="button" onclick="closeUserModal()" class="btn-secondary">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notifications Tab -->
        <div id="content-notifications" class="hidden" style="width: 100%;">
            <div id="notificationsCard" style="width: 100%;">
                <!-- Filters / Actions -->
                <div class="filters">
                    <button onclick="openComposeMessageAdmin()" class="ink-btn ink-btn-primary ink-btn-sm">+ New Message</button>
                    <button onclick="markAllReadAdmin()" class="ink-btn ink-btn-secondary ink-btn-sm">Mark All Read</button>
                </div>

                <div class="tbl-container" style="width: 100%;">
                    <div id="notificationsTableContainer" style="overflow-x: auto; width: 100%;">
                        <div id="notificationsContent" style="width: 100%;">
                            <!-- Notifications will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compose Message Modal (for Messages Tab) -->
        <div id="composeMessageModal" class="modal-overlay">
            <div class="modal-content" style="max-width: 500px;">
                <div class="modal-header">
                    <h3 style="font-size: 14px; font-weight: 600; font-family: 'Outfit', sans-serif;">Send Message</h3>
                    <button onclick="closeComposeMessageAdmin()" class="modal-close">&times;</button>
                </div>
                <div class="modal-body" style="padding: 16px;">
                    <form onsubmit="sendAdminMessage(event)">
                        <div style="margin-bottom: 14px;">
                            <label class="ink-label">To</label>
                            <select id="composeRecipientId" required class="ink-input">
                                <option value="">Select employee...</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 14px;">
                            <label class="ink-label">Subject</label>
                            <input type="text" id="composeSubject" required class="ink-input">
                        </div>

                        <div style="margin-bottom: 14px;">
                            <label class="ink-label">Message</label>
                            <textarea id="composeMessage" rows="5" required class="ink-input" style="resize: vertical;"></textarea>
                        </div>

                        <div style="display: flex; gap: 8px; justify-content: flex-end; padding-top: 8px;">
                            <button type="button" onclick="closeComposeMessageAdmin()" class="ink-btn ink-btn-secondary ink-btn-sm">Cancel</button>
                            <button type="submit" class="ink-btn ink-btn-primary ink-btn-sm">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Message Modal -->
        <div id="viewMessageModal" class="modal-overlay">
            <div class="modal-content" style="max-width: 500px;">
                <div class="modal-header">
                    <h3 style="font-size: 14px; font-weight: 600; font-family: 'Outfit', sans-serif;" id="viewMessageTitle">Message</h3>
                    <button onclick="closeViewMessage()" class="modal-close">&times;</button>
                </div>
                <div class="modal-body" style="padding: 16px;">
                    <div style="margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid #e2e4ea;">
                        <div style="font-size: 11px; color: #8b8fa3; font-family: 'Outfit', sans-serif;">
                            <span style="font-weight: 600; text-transform: uppercase;" id="viewMessageFromLabel">From:</span> <span id="viewMessageFrom" style="color: #3d3f4e;"></span>
                        </div>
                        <div style="font-size: 11px; color: #8b8fa3; margin-top: 4px; font-family: 'Outfit', sans-serif;">
                            <span style="font-weight: 600; text-transform: uppercase;">Subject:</span> <span id="viewMessageSubject" style="color: #3d3f4e;"></span>
                        </div>
                        <div style="font-size: 11px; color: #8b8fa3; margin-top: 4px; font-family: 'Outfit', sans-serif;">
                            <span style="font-weight: 600; text-transform: uppercase;">Date:</span> <span id="viewMessageDate" style="color: #3d3f4e;"></span>
                        </div>
                    </div>
                    <div id="viewMessageBody" style="font-size: 13px; line-height: 1.6; color: #3d3f4e; white-space: pre-wrap; font-family: 'Outfit', sans-serif;"></div>
                    <div style="display: flex; gap: 8px; justify-content: flex-end; padding-top: 14px; margin-top: 14px; border-top: 1px solid #e2e4ea;">
                        <button type="button" onclick="closeViewMessage()" class="ink-btn ink-btn-secondary ink-btn-sm">Close</button>
                        <button type="button" onclick="deleteCurrentMessageAdmin()" class="ink-btn ink-btn-danger ink-btn-sm">Delete</button>
                        <button type="button" onclick="replyToMessageAdmin()" class="ink-btn ink-btn-primary ink-btn-sm">Reply</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Send Message Modal -->
        <div id="messageModal" class="modal-overlay">
            <div class="modal-content" style="max-width: 600px;">
                <div class="modal-header">
                    <h3 class="modal-title">Send Message to Employee</h3>
                    <button onclick="closeMessageModal()" class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <form onsubmit="sendMessage(event)">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">To:</label>
                            <div id="messageRecipientName" style="font-weight: 600; color: #2563eb; font-size: 15px;"></div>
                            <input type="hidden" id="messageRecipientId">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Subject:</label>
                            <input type="text" id="messageSubject" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Message:</label>
                            <textarea id="messageBody" rows="6" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; resize: vertical;"></textarea>
                        </div>

                        <div class="modal-actions">
                            <button type="submit" class="btn-primary">Send Message</button>
                            <button type="button" onclick="closeMessageModal()" class="btn-secondary">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Case Detail Modal (For Messages Tab) -->
        <div id="messageCaseDetailModal" class="modal-overlay">
            <div class="modal-content" style="max-width: 700px;">
                <div class="modal-header">
                    <h3 class="modal-title">Case Details</h3>
                    <button onclick="closeCaseDetailAdmin()" class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <!-- Status Badge -->
                        <div style="text-center; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;">
                            <span id="adminDetailStatusBadge" class="status-badge"></span>
                        </div>

                        <!-- Case Information Grid -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Case Number</label>
                                <div id="adminDetailCaseNumber" style="font-size: 15px; font-weight: 500; color: #111827; margin-top: 4px;"></div>
                            </div>
                            <div>
                                <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Client Name</label>
                                <div id="adminDetailClientName" style="font-size: 15px; font-weight: 500; color: #111827; margin-top: 4px;"></div>
                            </div>
                            <div>
                                <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Employee</label>
                                <div id="adminDetailCounsel" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                            </div>
                            <div>
                                <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Case Type</label>
                                <div id="adminDetailCaseType" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                            </div>
                            <div>
                                <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Resolution Type</label>
                                <div id="adminDetailResolutionType" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                            </div>
                            <div>
                                <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Month</label>
                                <div id="adminDetailMonth" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                            </div>
                            <div>
                                <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Fee Rate</label>
                                <div id="adminDetailFeeRate" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div style="padding-top: 16px; border-top: 1px solid #e5e7eb;">
                            <h3 style="font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 12px;">Financial Details</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div>
                                    <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Settled Amount</label>
                                    <div id="adminDetailSettled" style="font-size: 18px; font-weight: 700; color: #111827; margin-top: 4px;"></div>
                                </div>
                                <div>
                                    <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Presuit Offer</label>
                                    <div id="adminDetailPresuitOffer" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                                </div>
                                <div>
                                    <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Difference</label>
                                    <div id="adminDetailDifference" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                                </div>
                                <div>
                                    <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Legal Fee</label>
                                    <div id="adminDetailLegalFee" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                                </div>
                                <div>
                                    <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Discounted Legal Fee</label>
                                    <div id="adminDetailDiscountedLegalFee" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                                </div>
                                <div>
                                    <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Commission</label>
                                    <div id="adminDetailCommission" style="font-size: 18px; font-weight: 700; color: #059669; margin-top: 4px;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Note -->
                        <div style="padding-top: 16px; border-top: 1px solid #e5e7eb;" id="adminDetailNoteSection">
                            <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Note</label>
                            <div id="adminDetailNote" style="font-size: 14px; color: #374151; margin-top: 8px; padding: 12px; background: #f9fafb; border-radius: 8px;"></div>
                        </div>

                        <!-- Dates -->
                        <div style="padding-top: 16px; border-top: 1px solid #e5e7eb;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 13px;">
                                <div>
                                    <span style="color: #6b7280;">Submitted:</span>
                                    <span id="adminDetailSubmittedAt" style="color: #111827; margin-left: 8px;"></span>
                                </div>
                                <div id="adminDetailReviewedSection">
                                    <span style="color: #6b7280;">Reviewed:</span>
                                    <span id="adminDetailReviewedAt" style="color: #111827; margin-left: 8px;"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-actions" style="margin-top: 24px;">
                        <button onclick="closeCaseDetailAdmin()" class="btn-secondary">Close</button>
                    </div>
                </div>
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

        <!-- Performance Analytics Tab -->
        <div id="content-performance" class="hidden">
            <style>
                /* Hero Cards */
                .hero-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
                .hero-card { background: #fff; border: 1px solid #e2e4ea; border-radius: 10px; padding: 18px 20px; position: relative; font-family: 'Outfit', sans-serif; }
                .hero-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: 10px 10px 0 0; }
                .hero-card.accent-dark::before { background: #1a1a2e; }
                .hero-card.accent-teal::before { background: #0d9488; }
                .hero-card.accent-blue::before { background: #3b82f6; }
                .hero-label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #8b8fa3; margin-bottom: 6px; }
                .hero-val { font-size: 26px; font-weight: 700; font-variant-numeric: tabular-nums; color: #1a1a2e; }
                .hero-val.teal { color: #0d9488; }
                .hero-sub { font-size: 11px; color: #8b8fa3; margin-top: 4px; }
                .hero-sub .down { color: #dc2626; font-weight: 600; }
                .hero-sub .up { color: #16a34a; font-weight: 600; }

                /* Analytics Panel */
                .panel { background: #fff; border: 1px solid #e2e4ea; border-radius: 10px; margin-bottom: 12px; overflow: hidden; font-family: 'Outfit', sans-serif; }
                .panel-section { display: grid; grid-template-columns: 160px 1fr; border-bottom: 1px solid #f0f1f3; }
                .panel-section:last-child { border-bottom: none; }
                .panel-label { padding: 16px; background: #fafbfc; border-right: 1px solid #f0f1f3; display: flex; align-items: center; }
                .panel-label-text { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; color: #5c5f73; line-height: 1.4; }
                .panel-data { padding: 12px 16px; display: flex; gap: 0; }
                .pd-cell { flex: 1; padding: 6px 12px; text-align: center; border-right: 1px solid #f0f1f3; }
                .pd-cell:last-child { border-right: none; }
                .pd-label { font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: #8b8fa3; margin-bottom: 3px; }
                .pd-val { font-size: 18px; font-weight: 700; font-variant-numeric: tabular-nums; color: #1a1a2e; }
                .pd-val.blue { color: #3b82f6; } .pd-val.indigo { color: #6366f1; } .pd-val.teal { color: #0d9488; }
                .pd-val.amber { color: #d97706; } .pd-val.red { color: #dc2626; } .pd-val.green { color: #16a34a; }
                .pd-val.dim { color: #c4c7d0; }

                /* Chart Panel */
                .panel-head { padding: 12px 16px; border-bottom: 1px solid #f0f1f3; font-size: 13px; font-weight: 700; color: #1a1a2e; font-family: 'Outfit', sans-serif; }
                .chart-wrap { padding: 16px; }
                .chart-wrap canvas { max-height: 250px; }

                /* Spark Bar */
                .spark-bar { display: flex; align-items: center; gap: 6px; justify-content: flex-end; }
                .spark { width: 50px; height: 6px; background: #f0f1f3; border-radius: 3px; overflow: hidden; }
                .spark-fill { height: 100%; border-radius: 3px; background: #0d9488; }
                .spark-fill.empty { background: #e2e4ea; }
                .spark-pct { font-size: 12px; font-weight: 600; font-variant-numeric: tabular-nums; min-width: 32px; text-align: right; color: #3d3f4e; }
                .spark-pct.zero { color: #c4c7d0; }

                /* Employee Table top row */
                #perfEmployeeBody tr.top-row { background: #f0fdf9; }
                #perfEmployeeBody tr.top-row:hover { background: #e6fbf5; }

                @media (max-width: 1200px) {
                    .panel-section { grid-template-columns: 1fr; }
                    .panel-label { border-right: none; border-bottom: 1px solid #f0f1f3; padding: 10px 16px; }
                    .panel-data { flex-wrap: wrap; }
                    .pd-cell { min-width: 120px; }
                }
                @media (max-width: 768px) { .hero-row { grid-template-columns: 1fr; } }
            </style>

            <!-- Filters -->
            <div class="filters" style="margin-bottom: 16px;">
                <select id="perfEmployeeFilter" class="f-select" onchange="loadPerformanceData()">
                    <option value="2">Chong</option>
                    <option value="0">All Employees</option>
                </select>
                <select id="perfYearFilter" class="f-select" onchange="loadPerformanceData()">
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                </select>
                <span class="f-spacer"></span>
                <button class="f-btn" onclick="loadPerformanceData()" style="background: #1a1a2e; color: #fff; border: none;">Refresh</button>
            </div>

            <!-- Hero Cards -->
            <div class="hero-row">
                <div class="hero-card accent-dark">
                    <div class="hero-label">Total Cases (YTD)</div>
                    <div class="hero-val" id="perfTotalCases">—</div>
                </div>
                <div class="hero-card accent-teal">
                    <div class="hero-label">Total Commission (YTD)</div>
                    <div class="hero-val teal" id="perfTotalCommission">—</div>
                    <div class="hero-sub" id="perfCommissionChange"></div>
                </div>
                <div class="hero-card accent-blue">
                    <div class="hero-label">Avg Commission / Case</div>
                    <div class="hero-val" id="perfAvgCommission">—</div>
                </div>
            </div>

            <!-- Analytics Panel -->
            <div class="panel" id="chongAnalyticsSection">
                <div class="panel-section">
                    <div class="panel-label"><div class="panel-label-text">Phase<br>Breakdown</div></div>
                    <div class="panel-data">
                        <div class="pd-cell"><div class="pd-label">Demand Active</div><div class="pd-val blue" id="perfDemandActive">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Litigation Active</div><div class="pd-val indigo" id="perfLitActive">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Settled (YTD)</div><div class="pd-val teal" id="perfSettled">—</div></div>
                    </div>
                </div>
                <div class="panel-section">
                    <div class="panel-label"><div class="panel-label-text">Settlement<br>Breakdown</div></div>
                    <div class="panel-data">
                        <div class="pd-cell"><div class="pd-label">Demand Settled</div><div class="pd-val" id="perfDemandSettled">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Litigation Settled</div><div class="pd-val" id="perfLitSettled">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Resolution Rate</div><div class="pd-val green" id="perfResolutionRate">—</div></div>
                    </div>
                </div>
                <div class="panel-section">
                    <div class="panel-label"><div class="panel-label-text">Efficiency<br>Metrics</div></div>
                    <div class="panel-data">
                        <div class="pd-cell"><div class="pd-label">Avg Demand Days</div><div class="pd-val dim" id="perfAvgDemandDays">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Avg Lit Days</div><div class="pd-val dim" id="perfAvgLitDays">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Avg Total Days</div><div class="pd-val dim" id="perfAvgTotalDays">—</div></div>
                    </div>
                </div>
                <div class="panel-section">
                    <div class="panel-label"><div class="panel-label-text">Time<br>Management</div></div>
                    <div class="panel-data">
                        <div class="pd-cell"><div class="pd-label">Deadline Compliance</div><div class="pd-val green" id="perfDeadlineCompliance">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Urgent Cases</div><div class="pd-val dim" id="perfUrgentCases">—</div></div>
                        <div class="pd-cell"></div>
                    </div>
                </div>
                <div class="panel-section">
                    <div class="panel-label"><div class="panel-label-text">Commission<br>Breakdown</div></div>
                    <div class="panel-data">
                        <div class="pd-cell"><div class="pd-label">Total</div><div class="pd-val teal" id="perfCommTotal">—</div></div>
                        <div class="pd-cell"><div class="pd-label">From Demand (5%)</div><div class="pd-val" id="perfCommDemand">—</div></div>
                        <div class="pd-cell"><div class="pd-label">From Litigation (20%)</div><div class="pd-val" id="perfCommLit">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Active Cases</div><div class="pd-val" id="perfActiveCases">—</div></div>
                    </div>
                </div>
            </div>

            <!-- Chart Panel -->
            <div class="panel" style="margin-bottom: 12px;">
                <div class="panel-head">Monthly Commission Trend</div>
                <div class="chart-wrap">
                    <canvas id="perfCommissionChart"></canvas>
                </div>
            </div>

            <!-- Employee Table -->
            <div class="tbl-container">
                <table class="tbl" style="table-layout: auto;">
                    <thead>
                        <tr>
                            <th style="padding: 10px 14px;">Employee</th>
                            <th class="r" style="padding: 10px 14px;">Cases</th>
                            <th class="r" style="padding: 10px 14px;">Paid</th>
                            <th class="r" style="padding: 10px 14px;">Commission</th>
                            <th class="r" style="padding: 10px 14px;">Avg/Case</th>
                            <th class="r" style="padding: 10px 14px;">Share</th>
                        </tr>
                    </thead>
                    <tbody id="perfEmployeeBody">
                        <tr><td colspan="6" style="text-align: center; padding: 20px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Database Tab -->
        <div id="content-database" class="hidden">
            <div class="tbl-container" style="height: calc(100vh - 140px);">
                <iframe id="databaseFrame" src="" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>

        </div><!-- /page-content -->
    </div><!-- /main -->

    <script>
        let allCases = [];
        let pendingCases = [];
        let stats = {};
        let allCasesSortColumn = null;
        let allCasesSortDirection = 'asc';
        let pendingSortColumn = null;
        let pendingSortDirection = 'asc';
        let csrfToken = '<?= generateCSRFToken() ?>';

        // Default widths for each tab (Admin page)
        const TAB_DEFAULT_WIDTHS = {
            'pending': '100',
            'all': '100',
            'dashboard': '100',
            'report': '100',
            'notifications': '100',
            'history': '100',
            'traffic': '100',
            'admin-control': '100',
            'performance': '100',
            'goals': '100',
            'deadline-requests': '100',
            'database': '100'
        };

        // Helper function for API calls with CSRF token
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
                    ...options.headers
                }
            };

            const response = await fetch(url, mergedOptions);
            const text = await response.text();

            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON response:', text.substring(0, 500));
                throw new Error('Server returned invalid response. Check PHP error logs.');
            }

            // Update CSRF token if provided in response
            if (data.csrf_token) {
                csrfToken = data.csrf_token;
            }

            if (!response.ok) {
                throw new Error(data.error || 'Request failed');
            }

            return data;
        }

        // Escape HTML to prevent XSS
        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

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
            localStorage.setItem('adminWidth', width);
        }

        function loadWidthPreference() {
            const savedWidth = localStorage.getItem('adminWidth') || '100';
            setWidth(savedWidth);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            // Set initial width for pending tab (first tab)
            setWidth(TAB_DEFAULT_WIDTHS['pending'] || '75');
            initMonthDropdown();
            loadPendingCases();
            loadStats();
            loadMessages(); // Load messages to update notification badge
            loadDeadlineRequestsBadge(); // Load deadline requests badge
            initFixedScrollbar();
        });

        // Load just the badge count for deadline requests
        async function loadDeadlineRequestsBadge() {
            try {
                const data = await apiCall('api/deadline_requests.php?status=pending');
                const badge = document.getElementById('deadlineRequestBadge');
                const pendingCount = data.pending_count || 0;
                badge.textContent = pendingCount;
                badge.classList.toggle('hidden', pendingCount === 0);
            } catch (err) {
                console.error('Error loading deadline requests badge:', err);
            }
        }

        // Fixed scrollbar at bottom of screen
        function initFixedScrollbar() {
            // Create fixed scrollbar element
            const fixedScrollbar = document.createElement('div');
            fixedScrollbar.className = 'scrollbar-fixed';
            fixedScrollbar.id = 'fixedScrollbar';
            fixedScrollbar.innerHTML = '<div class="scrollbar-fixed-inner" id="scrollbarInner"></div>';
            document.body.appendChild(fixedScrollbar);

            // Sync scroll between table wrapper and fixed scrollbar
            const wrappers = document.querySelectorAll('.table-scroll-wrapper');

            wrappers.forEach(wrapper => {
                wrapper.addEventListener('scroll', () => {
                    if (isElementInViewport(wrapper)) {
                        fixedScrollbar.scrollLeft = wrapper.scrollLeft;
                    }
                });
            });

            fixedScrollbar.addEventListener('scroll', () => {
                const activeWrapper = getVisibleWrapper();
                if (activeWrapper) {
                    activeWrapper.scrollLeft = fixedScrollbar.scrollLeft;
                }
            });

            // Update scrollbar width on tab switch and resize
            updateFixedScrollbar();
            window.addEventListener('resize', updateFixedScrollbar);

            // Watch for tab changes
            const observer = new MutationObserver(updateFixedScrollbar);
            document.querySelectorAll('[id^="content-"]').forEach(el => {
                observer.observe(el, { attributes: true, attributeFilter: ['class'] });
            });
        }

        function getVisibleWrapper() {
            const wrappers = document.querySelectorAll('.table-scroll-wrapper');
            for (let wrapper of wrappers) {
                if (wrapper.offsetParent !== null && isElementInViewport(wrapper)) {
                    return wrapper;
                }
            }
            return null;
        }

        function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            return rect.top < window.innerHeight && rect.bottom > 0;
        }

        function updateFixedScrollbar() {
            const fixedScrollbar = document.getElementById('fixedScrollbar');
            const scrollbarInner = document.getElementById('scrollbarInner');
            const activeWrapper = getVisibleWrapper();

            if (activeWrapper && fixedScrollbar && scrollbarInner) {
                const table = activeWrapper.querySelector('table');
                if (table) {
                    scrollbarInner.style.width = table.scrollWidth + 'px';
                    fixedScrollbar.style.display = 'block';
                    fixedScrollbar.scrollLeft = activeWrapper.scrollLeft;
                }
            } else if (fixedScrollbar) {
                fixedScrollbar.style.display = 'none';
            }
        }

        function initMonthDropdown() {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const year = new Date().getFullYear();
            const select = document.getElementById('filterAllMonth');

            months.forEach(m => {
                select.innerHTML += `<option value="${m}. ${year}">${m}. ${year}</option>`;
            });
        }

        // Page title mapping for sidebar navigation
        const pageTitles = {
            'pending': 'Pending Review',
            'deadline-requests': 'Deadline Requests',
            'all': 'All Cases',
            'traffic': 'Traffic Cases',
            'dashboard': 'Dashboard',
            'report': 'Reports',
            'performance': 'Performance Analytics',
            'goals': 'Employee Goals',
            'admin-control': 'Admin Control',
            'history': 'History',
            'notifications': 'Notifications'
        };

        async function switchTab(tab) {
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
            if (tab === 'all') loadAllCases();
            if (tab === 'dashboard') loadStats();
            if (tab === 'report') {
                // Load all cases first, then generate report
                await loadAllCases();
                generateComprehensiveReport();
            }
            if (tab === 'notifications') loadMessages();
            if (tab === 'history') loadHistory();
            if (tab === 'admin-control') loadUsers();
            if (tab === 'traffic') {
                loadAdminTrafficCases();
                loadMyTrafficRequests();
            }
            if (tab === 'deadline-requests') {
                loadDeadlineRequests();
            }
            if (tab === 'performance') {
                loadPerformanceData();
            }
            if (tab === 'goals') {
                initGoalsYearFilter();
                loadGoalsData();
            }
            if (tab === 'database') {
                const frame = document.getElementById('databaseFrame');
                if (frame && !frame.src.includes('check_db.php')) {
                    frame.src = 'check_db.php';
                }
            }
        }

        // Sidebar navigation click handlers
        document.querySelectorAll('.nav-link[data-tab]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const tabName = this.dataset.tab;
                if (tabName) {
                    switchTab(tabName);
                }
            });
        });

        async function loadPendingCases() {
            try {
                const data = await apiCall('api/cases.php?status=unpaid');
                pendingCases = data.cases || [];
                renderPendingCases();
                document.getElementById('pendingBadge').textContent = pendingCases.length;
            } catch (err) {
                console.error('Error:', err);
            }
        }

        // ============================================
        // Deadline Extension Requests Functions
        // ============================================

        let deadlineRequests = [];

        async function loadDeadlineRequests() {
            try {
                const status = document.getElementById('filterDeadlineStatus').value;
                const data = await apiCall(`api/deadline_requests.php?status=${status}`);
                deadlineRequests = data.requests || [];
                renderDeadlineRequests();

                // Update badge
                const badge = document.getElementById('deadlineRequestBadge');
                const pendingCount = data.pending_count || 0;
                badge.textContent = pendingCount;
                badge.classList.toggle('hidden', pendingCount === 0);
            } catch (err) {
                console.error('Error loading deadline requests:', err);
            }
        }

        function renderDeadlineRequests() {
            const tbody = document.getElementById('deadlineRequestsBody');
            const countEl = document.getElementById('deadlineRequestsCount');

            if (!deadlineRequests || deadlineRequests.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; padding: 40px; color: #6b7280;">No deadline requests found</td></tr>`;
                countEl.textContent = '0 requests';
                return;
            }

            tbody.innerHTML = deadlineRequests.map(r => {
                const statusBadge = getStatusBadge(r.status);
                const createdDate = new Date(r.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

                return `
                    <tr>
                        <td>${createdDate}</td>
                        <td>${escapeHtml(r.requester_name)}</td>
                        <td>${escapeHtml(r.case_number)}</td>
                        <td>${escapeHtml(r.client_name)}</td>
                        <td>${r.current_deadline}</td>
                        <td style="color: #059669; font-weight: 500;">${r.requested_deadline}</td>
                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(r.reason)}">${escapeHtml(r.reason)}</td>
                        <td>${statusBadge}</td>
                        <td style="text-align: center;">
                            ${r.status === 'pending' ?
                                `<button class="btn btn-sm btn-primary" onclick="openDeadlineReviewModal(${r.id})">Review</button>` :
                                `<span style="font-size: 12px; color: #6b7280;">${r.reviewer_name || '-'}</span>`
                            }
                        </td>
                    </tr>
                `;
            }).join('');

            countEl.textContent = `${deadlineRequests.length} request${deadlineRequests.length !== 1 ? 's' : ''}`;
        }

        function filterDeadlineRequestsTable() {
            const search = document.getElementById('deadlineSearchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#deadlineRequestsBody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(search) ? '' : 'none';
            });
        }

        function openDeadlineReviewModal(requestId) {
            const request = deadlineRequests.find(r => r.id == requestId);
            if (!request) return;

            document.getElementById('deadlineReviewId').value = requestId;
            document.getElementById('reviewRequesterName').textContent = request.requester_name;
            document.getElementById('reviewCaseInfo').textContent = `${request.case_number} - ${request.client_name}`;
            document.getElementById('reviewCurrentDeadline').textContent = request.current_deadline;
            document.getElementById('reviewRequestedDeadline').textContent = request.requested_deadline;
            document.getElementById('reviewReason').textContent = request.reason;
            document.getElementById('deadlineAdminNote').value = '';

            openModal('deadlineReviewModal');
        }

        async function processDeadlineRequest(action) {
            const requestId = document.getElementById('deadlineReviewId').value;
            const adminNote = document.getElementById('deadlineAdminNote').value;

            if (!confirm(`Are you sure you want to ${action} this deadline extension request?`)) {
                return;
            }

            try {
                const result = await apiCall('api/deadline_requests.php', 'PUT', {
                    id: parseInt(requestId),
                    action: action,
                    admin_note: adminNote
                });

                if (result.success) {
                    showNotification(`Request ${action}d successfully`, 'success');
                    closeModal('deadlineReviewModal');
                    loadDeadlineRequests();
                } else {
                    showNotification(result.error || `Failed to ${action} request`, 'error');
                }
            } catch (err) {
                showNotification('Error processing request', 'error');
            }
        }

        // Sort Pending Cases
        function sortPendingCases(column) {
            // Toggle direction if same column
            if (pendingSortColumn === column) {
                pendingSortDirection = pendingSortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                pendingSortColumn = column;
                pendingSortDirection = 'asc';
            }

            // Sort the data
            pendingCases.sort((a, b) => {
                let valA, valB;

                // Handle different column types
                switch(column) {
                    case 'counsel':
                        valA = (a.counsel_name || '').toLowerCase();
                        valB = (b.counsel_name || '').toLowerCase();
                        break;
                    case 'status':
                        valA = (a.status || '').toLowerCase();
                        valB = (b.status || '').toLowerCase();
                        break;
                    case 'month':
                        // Parse month for proper sorting (e.g., "Dec. 2025")
                        valA = parseMonthForSort(a.month);
                        valB = parseMonthForSort(b.month);
                        break;
                    case 'case_type':
                        valA = (a.case_type || '').toLowerCase();
                        valB = (b.case_type || '').toLowerCase();
                        break;
                    case 'case_number':
                        valA = (a.case_number || '').toLowerCase();
                        valB = (b.case_number || '').toLowerCase();
                        break;
                    case 'client_name':
                        valA = (a.client_name || '').toLowerCase();
                        valB = (b.client_name || '').toLowerCase();
                        break;
                    case 'resolution_type':
                        valA = (a.resolution_type || '').toLowerCase();
                        valB = (b.resolution_type || '').toLowerCase();
                        break;
                    case 'fee_rate':
                        valA = parseFloat(a.fee_rate) || 0;
                        valB = parseFloat(b.fee_rate) || 0;
                        break;
                    case 'settled_amount':
                        valA = parseFloat(a.settled) || 0;
                        valB = parseFloat(b.settled) || 0;
                        break;
                    case 'pre_suit_demand':
                        valA = parseFloat(a.presuit_offer) || 0;
                        valB = parseFloat(b.presuit_offer) || 0;
                        break;
                    case 'difference':
                        valA = parseFloat(a.difference) || 0;
                        valB = parseFloat(b.difference) || 0;
                        break;
                    case 'legal_fee':
                        valA = parseFloat(a.legal_fee) || 0;
                        valB = parseFloat(b.legal_fee) || 0;
                        break;
                    case 'discounted_legal_fee':
                        valA = parseFloat(a.discounted_legal_fee) || 0;
                        valB = parseFloat(b.discounted_legal_fee) || 0;
                        break;
                    case 'commission':
                        valA = parseFloat(a.commission) || 0;
                        valB = parseFloat(b.commission) || 0;
                        break;
                    default:
                        valA = '';
                        valB = '';
                }

                // Compare
                if (valA < valB) return pendingSortDirection === 'asc' ? -1 : 1;
                if (valA > valB) return pendingSortDirection === 'asc' ? 1 : -1;
                return 0;
            });

            // Update sort icons in header
            updatePendingSortIcons(column);

            // Re-render the table
            renderPendingCases();
        }

        function parseMonthForSort(monthStr) {
            if (!monthStr || monthStr === 'TBD') return 0;
            const months = {'jan': 1, 'feb': 2, 'mar': 3, 'apr': 4, 'may': 5, 'jun': 6, 'jul': 7, 'aug': 8, 'sep': 9, 'oct': 10, 'nov': 11, 'dec': 12};
            const match = monthStr.toLowerCase().match(/([a-z]+)\.?\s*(\d{4})/);
            if (match) {
                const monthNum = months[match[1].substring(0, 3)] || 0;
                const year = parseInt(match[2]) || 0;
                return year * 100 + monthNum;
            }
            return 0;
        }

        function updatePendingSortIcons(activeColumn) {
            // Reset all sort icons in pending table
            document.querySelectorAll('#pendingTable .th-content.sortable .sort-icon').forEach(icon => {
                icon.textContent = '▼';
                icon.style.opacity = '0.3';
            });

            // Update active column icon
            const columnMap = {
                'counsel': 0, 'status': 1, 'month': 2, 'case_type': 3, 'case_number': 4,
                'client_name': 5, 'resolution_type': 6, 'fee_rate': 7, 'settled_amount': 8,
                'pre_suit_demand': 9, 'difference': 10, 'legal_fee': 11, 'discounted_legal_fee': 12, 'commission': 13
            };

            const icons = document.querySelectorAll('#pendingTable .th-content.sortable .sort-icon');
            const idx = columnMap[activeColumn];
            if (icons[idx]) {
                icons[idx].textContent = pendingSortDirection === 'asc' ? '▲' : '▼';
                icons[idx].style.opacity = '1';
            }
        }

        function renderPendingCases() {
            const tbody = document.getElementById('pendingBody');
            const footerInfo = document.getElementById('pendingFooterInfo');
            const footerTotal = document.getElementById('pendingFooterTotal');

            if (pendingCases.length === 0) {
                tbody.innerHTML = `<tr><td colspan="12" style="padding: 32px 16px; text-align: center;" class="text-secondary">No pending cases</td></tr>`;
                if (footerInfo) footerInfo.textContent = 'Showing 0 cases';
                if (footerTotal) footerTotal.textContent = formatCurrency(0);
                return;
            }

            // Update footer info
            if (footerInfo) footerInfo.textContent = `Showing ${pendingCases.length} case${pendingCases.length !== 1 ? 's' : ''}`;

            tbody.innerHTML = pendingCases.map(c => `
                <tr onclick="viewPendingCaseDetail(${c.id})" data-case-id="${c.id}" style="cursor:pointer;">
                    <td style="text-align:center;padding:6px 4px;" onclick="event.stopPropagation()">
                        <input type="checkbox" class="pending-checkbox" value="${c.id}">
                    </td>
                    <td style="font-size:12px;padding:6px;">${c.counsel_name}</td>
                    <td style="font-size:12px;padding:6px;">${c.month}</td>
                    <td style="font-weight:600;font-size:12px;padding:6px;">${c.case_number}</td>
                    <td style="font-size:12px;padding:6px;">${c.client_name}</td>
                    <td style="text-align:right;font-weight:600;font-size:12px;padding:6px;">${formatCurrency(c.settled)}</td>
                    <td style="text-align:right;font-size:12px;padding:6px;">${formatCurrency(c.presuit_offer || 0)}</td>
                    <td style="text-align:right;font-size:12px;padding:6px;">${formatCurrency(c.difference || 0)}</td>
                    <td style="text-align:right;font-size:12px;padding:6px;">${formatCurrency(c.legal_fee || 0)}</td>
                    <td style="text-align:right;font-size:12px;padding:6px;">${formatCurrency(c.discounted_legal_fee)}</td>
                    <td style="text-align:right;font-weight:700;color:#059669;font-size:12px;padding:6px;">${formatCurrency(c.commission)}</td>
                    <td style="text-align:center;padding:6px;" onclick="event.stopPropagation()">
                        <div class="action-group center">
                            <button class="act-btn approve" onclick="approveCase(${c.id})" title="Approve">Approve</button>
                            <button class="act-btn reject" onclick="rejectCase(${c.id})" title="Reject">Reject</button>
                        </div>
                    </td>
                </tr>
            `).join('');

            // Update total commission
            const totalCommission = pendingCases.reduce((sum, c) => sum + parseFloat(c.commission), 0);
            if (footerTotal) footerTotal.textContent = formatCurrency(totalCommission);
        }

        async function loadAllCases() {
            const counselEl = document.getElementById('filterCounsel');
            const monthEl = document.getElementById('filterAllMonth');
            const statusEl = document.getElementById('filterAllStatus');

            let url = 'api/cases.php?';
            if (counselEl && counselEl.value !== 'all') url += `counsel=${counselEl.value}&`;
            if (monthEl && monthEl.value !== 'all') url += `month=${encodeURIComponent(monthEl.value)}&`;
            if (statusEl && statusEl.value !== 'all') url += `status=${statusEl.value}&`;

            try {
                const res = await fetch(url);
                const data = await res.json();
                allCases = data.cases || [];
                if (counselEl) renderAllCases(); // Only render table if we're on All Cases tab
            } catch (err) {
                console.error('Error:', err);
            }
        }

        function filterAllCases() {
            renderAllCases();
        }

        function sortAllCases(column) {
            // Toggle sort direction if clicking the same column
            if (allCasesSortColumn === column) {
                allCasesSortDirection = allCasesSortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                allCasesSortColumn = column;
                allCasesSortDirection = 'asc';
            }

            // Update sort arrow indicators
            document.querySelectorAll('#allCasesTable .sort-arrow').forEach(arrow => {
                arrow.className = 'sort-arrow';
            });

            // Find the clicked header's arrow
            const clickedHeader = event.target.closest('th');
            if (clickedHeader) {
                const arrow = clickedHeader.querySelector('.sort-arrow');
                if (arrow) {
                    arrow.className = `sort-arrow ${allCasesSortDirection}`;
                }
            }

            renderAllCases();
        }

        function renderAllCases() {
            const search = document.getElementById('searchAll').value.toLowerCase();
            const monthFilter = document.getElementById('filterAllMonth').value;
            let filtered = allCases;
            
            if (search) {
                filtered = filtered.filter(c =>
                    c.client_name.toLowerCase().includes(search) ||
                    c.case_number.toLowerCase().includes(search) ||
                    c.counsel_name.toLowerCase().includes(search)
                );
            }

            // Apply sorting if a column is selected
            if (allCasesSortColumn) {
                filtered.sort((a, b) => {
                    let aVal = a[allCasesSortColumn];
                    let bVal = b[allCasesSortColumn];

                    // Handle null/undefined values
                    if (aVal == null) aVal = '';
                    if (bVal == null) bVal = '';

                    // Convert to appropriate types for comparison
                    if (['settled', 'presuit_offer', 'difference', 'legal_fee', 'discounted_legal_fee', 'commission', 'fee_rate'].includes(allCasesSortColumn)) {
                        aVal = parseFloat(aVal) || 0;
                        bVal = parseFloat(bVal) || 0;
                    } else if (allCasesSortColumn === 'check_received') {
                        aVal = parseInt(aVal) || 0;
                        bVal = parseInt(bVal) || 0;
                    } else {
                        aVal = String(aVal).toLowerCase();
                        bVal = String(bVal).toLowerCase();
                    }

                    if (aVal < bVal) return allCasesSortDirection === 'asc' ? -1 : 1;
                    if (aVal > bVal) return allCasesSortDirection === 'asc' ? 1 : -1;
                    return 0;
                });
            }

            const tbody = document.getElementById('allCasesBody');
            const footerInfo = document.getElementById('allCasesFooterInfo');
            const footerTotal = document.getElementById('allCasesFooterTotal');
            
            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="14" style="padding: 32px 16px; text-align: center;" class="text-secondary">No cases found</td></tr>`;
                if (footerInfo) footerInfo.textContent = 'Showing 0 cases';
                if (footerTotal) footerTotal.textContent = formatCurrency(0);
                return;
            }

            // Update footer info
            if (footerInfo) footerInfo.textContent = `Showing ${filtered.length} case${filtered.length !== 1 ? 's' : ''}`;

            // If "All Months" is selected, group by month
            if (monthFilter === 'all') {
                const casesByMonth = {};
                filtered.forEach(c => {
                    if (!casesByMonth[c.month]) {
                        casesByMonth[c.month] = [];
                    }
                    casesByMonth[c.month].push(c);
                });

                // Sort months from newest to oldest
                const sortedMonths = Object.keys(casesByMonth).sort((a, b) => {
                    const parseMonth = (monthStr) => {
                        const parts = monthStr.replace('.', '').split(' ');
                        const monthAbbr = parts[0];
                        const year = parts[1];
                        const monthMap = {'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'May': 4, 'Jun': 5,
                                         'Jul': 6, 'Aug': 7, 'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11};
                        return new Date(parseInt(year), monthMap[monthAbbr]);
                    };
                    return parseMonth(b) - parseMonth(a);
                });

                let html = '';
                sortedMonths.forEach(monthKey => {
                    const cases = casesByMonth[monthKey];
                    const caseCount = cases.length;
                    const monthTotal = cases.reduce((sum, c) => sum + parseFloat(c.commission), 0);

                    // Month header
                    html += `
                        <tr class="month-header-row">
                            <td colspan="14" style="background: #f8fafc; padding: 10px 12px; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: 700; font-size: 12px; color: #0f172a;">${monthKey}</span>
                                    <span style="font-size: 11px; color: #047857; font-weight: 600;">
                                        ${caseCount} case${caseCount !== 1 ? 's' : ''} | ${formatCurrency(monthTotal)}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    `;

                    // Cases for this month
                    html += cases.map(c => `
                        <tr onclick="viewCaseDetail(${c.id})" style="cursor:pointer;">
                            <td style="font-size:12px;padding:6px;">${c.counsel_name}</td>
                            <td class="c" style="padding:6px;"><span class="stat-badge ${c.status}">${c.status === 'in_progress' ? 'Prog' : c.status.charAt(0).toUpperCase() + c.status.slice(1)}</span></td>
                            <td style="font-size:12px;padding:6px;">${c.month}</td>
                            <td style="font-weight:600;font-size:12px;padding:6px;">${c.case_number}</td>
                            <td style="font-size:12px;padding:6px;">${c.client_name}</td>
                            <td style="font-size:11px;padding:6px;">${(c.resolution_type || '-').replace('No Offer Settle', 'No Offer').replace('File and Bump', 'File/Bump').replace('Post Deposition Settle', 'Post Dep')}</td>
                            <td class="r" style="font-weight:600;font-size:12px;padding:6px;">${formatCurrency(c.settled)}</td>
                            <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.presuit_offer || 0)}</td>
                            <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.difference || 0)}</td>
                            <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.legal_fee || 0)}</td>
                            <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.discounted_legal_fee || 0)}</td>
                            <td class="r" style="font-weight:700;color:#059669;font-size:12px;padding:6px;">${formatCurrency(c.commission)}</td>
                            <td class="c" style="padding:6px;">${c.check_received ? '✓' : '-'}</td>
                            <td class="c" style="padding:6px;" onclick="event.stopPropagation()">
                                <span onclick="editCaseFromRow(${c.id})" style="color: #2563eb; cursor: pointer; font-size: 10px;">Edit</span>
                            </td>
                        </tr>
                    `).join('');
                });

                tbody.innerHTML = html;
            } else {
                // Normal rendering for specific month
                tbody.innerHTML = filtered.map(c => `
                    <tr onclick="viewCaseDetail(${c.id})" style="cursor:pointer;">
                        <td style="font-size:12px;padding:6px;">${c.counsel_name}</td>
                        <td class="c" style="padding:6px;"><span class="stat-badge ${c.status}">${c.status === 'in_progress' ? 'Prog' : c.status.charAt(0).toUpperCase() + c.status.slice(1)}</span></td>
                        <td style="font-size:12px;padding:6px;">${c.month}</td>
                        <td style="font-weight:600;font-size:12px;padding:6px;">${c.case_number}</td>
                        <td style="font-size:12px;padding:6px;">${c.client_name}</td>
                        <td style="font-size:11px;padding:6px;">${(c.resolution_type || '-').replace('No Offer Settle', 'No Offer').replace('File and Bump', 'File/Bump').replace('Post Deposition Settle', 'Post Dep')}</td>
                        <td class="r" style="font-weight:600;font-size:12px;padding:6px;">${formatCurrency(c.settled)}</td>
                        <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.presuit_offer || 0)}</td>
                        <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.difference || 0)}</td>
                        <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.legal_fee || 0)}</td>
                        <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.discounted_legal_fee || 0)}</td>
                        <td class="r" style="font-weight:700;color:#059669;font-size:12px;padding:6px;">${formatCurrency(c.commission)}</td>
                        <td class="c" style="padding:6px;">${c.check_received ? '✓' : '-'}</td>
                        <td class="c" style="padding:6px;" onclick="event.stopPropagation()">
                            <span onclick="editCaseFromRow(${c.id})" style="color: #2563eb; cursor: pointer; font-size: 10px;">Edit</span>
                        </td>
                    </tr>
                `).join('');
            }

            // Update total commission
            const filteredCommission = filtered.reduce((sum, c) => sum + parseFloat(c.commission), 0);
            if (footerTotal) footerTotal.textContent = formatCurrency(filteredCommission);
        }

        async function loadStats() {
            try {
                stats = await apiCall('api/approve.php');
                renderStats();
            } catch (err) {
                console.error('Error:', err);
            }
        }

        function renderStats() {
            // Quick Stats Row
            document.getElementById('statTotalCases').textContent = stats.total_cases || 0;
            document.getElementById('statPending').textContent = stats.pending_count || 0;
            document.getElementById('statTotalCommission').textContent = formatCurrency(stats.total_commission || 0);
            document.getElementById('statAvgCommission').textContent = formatCurrency(stats.avg_commission || 0);
            document.getElementById('statCheckRate').textContent = (stats.check_received_rate || 0) + '%';
            document.getElementById('statUnreceived').textContent = formatCurrency(stats.unreceived?.total || 0);

            // This Month vs Last Month
            if (stats.this_month) {
                document.getElementById('thisMonthName').textContent = stats.this_month.name;
                document.getElementById('thisMonthCases').textContent = stats.this_month.cases || 0;
                document.getElementById('thisMonthComm').textContent = formatCurrency(stats.this_month.commission || 0);
                document.getElementById('thisMonthApproved').textContent = stats.this_month.approved || 0;

                // Calculate change percentages
                if (stats.last_month && stats.last_month.cases > 0) {
                    const casesChange = ((stats.this_month.cases - stats.last_month.cases) / stats.last_month.cases * 100).toFixed(0);
                    const casesEl = document.getElementById('thisMonthCasesChange');
                    casesEl.innerHTML = casesChange >= 0 ?
                        `<span style="color: #059669;">↑ ${casesChange}%</span>` :
                        `<span style="color: #dc2626;">↓ ${Math.abs(casesChange)}%</span>`;
                }
                if (stats.last_month && stats.last_month.commission > 0) {
                    const commChange = ((stats.this_month.commission - stats.last_month.commission) / stats.last_month.commission * 100).toFixed(0);
                    const commEl = document.getElementById('thisMonthCommChange');
                    commEl.innerHTML = commChange >= 0 ?
                        `<span style="color: #059669;">↑ ${commChange}%</span>` :
                        `<span style="color: #dc2626;">↓ ${Math.abs(commChange)}%</span>`;
                }
            }

            if (stats.last_month) {
                document.getElementById('lastMonthName').textContent = stats.last_month.name;
                document.getElementById('lastMonthCases').textContent = stats.last_month.cases || 0;
                document.getElementById('lastMonthComm').textContent = formatCurrency(stats.last_month.commission || 0);
                document.getElementById('lastMonthApproved').textContent = stats.last_month.approved || 0;
            }

            // Monthly Trend Chart
            if (stats.by_month && stats.by_month.length > 0) {
                renderTrendChart(stats.by_month.slice(0, 6).reverse());
            }

            // Cases by Status Pie Chart
            if (stats.by_status) {
                renderStatusChart(stats.by_status);
            }

            // Counsel stats
            const counselDiv = document.getElementById('counselStats');
            if (stats.by_counsel) {
                counselDiv.innerHTML = stats.by_counsel.map(c => `
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f1f3;">
                        <div>
                            <span style="font-size: 13px; font-weight: 600; color: #1a1a2e; font-family: 'Outfit', sans-serif;">${c.display_name}</span>
                            ${c.pending_count > 0 ? `<span class="stat-badge pending" style="margin-left: 8px;">${c.pending_count} pending</span>` : ''}
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 14px; font-weight: 700; color: #0d9488; font-family: 'Outfit', sans-serif;">${formatCurrency(c.total_commission)}</div>
                            <div style="font-size: 11px; color: #8b8fa3;">${c.case_count} cases</div>
                        </div>
                    </div>
                `).join('');
            }

            // Top 5 Cases
            const topCasesDiv = document.getElementById('topCasesStats');
            if (stats.top_cases && stats.top_cases.length > 0) {
                topCasesDiv.innerHTML = stats.top_cases.map((c, i) => `
                    <div style="display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f1f3; cursor: pointer;" onclick="viewCaseDetail(${c.id})">
                        <div style="width: 24px; height: 24px; border-radius: 50%; background: ${i === 0 ? '#fbbf24' : i === 1 ? '#9ca3af' : i === 2 ? '#d97706' : '#e5e7eb'}; color: ${i < 3 ? '#fff' : '#6b7280'}; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; margin-right: 10px;">${i + 1}</div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 12px; font-weight: 600; color: #1a1a2e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${c.client_name}</div>
                            <div style="font-size: 10px; color: #8b8fa3;">${c.counsel_name} · ${c.case_number}</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 13px; font-weight: 700; color: #0d9488;">${formatCurrency(c.commission)}</div>
                            <span class="stat-badge ${c.status}" style="font-size: 8px;">${c.status}</span>
                        </div>
                    </div>
                `).join('');
            } else {
                topCasesDiv.innerHTML = '<div style="padding: 20px; text-align: center; color: #8b8fa3; font-size: 12px;">No cases found</div>';
            }

            // Upcoming Deadlines
            const deadlinesDiv = document.getElementById('upcomingDeadlines');
            let deadlinesHtml = '';

            // Past due first
            if (stats.past_due && stats.past_due.length > 0) {
                deadlinesHtml += stats.past_due.map(c => `
                    <div style="display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f1f3; background: #fef2f2; margin: 0 -20px; padding-left: 20px; padding-right: 20px;">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 12px; font-weight: 600; color: #1a1a2e;">${c.client_name}</div>
                            <div style="font-size: 10px; color: #8b8fa3;">${c.counsel_name} · ${c.case_number}</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 11px; font-weight: 700; color: #dc2626;">${c.days_overdue} days overdue</div>
                            <div style="font-size: 10px; color: #8b8fa3;">${c.demand_deadline}</div>
                        </div>
                    </div>
                `).join('');
            }

            // Upcoming deadlines
            if (stats.upcoming_deadlines && stats.upcoming_deadlines.length > 0) {
                deadlinesHtml += stats.upcoming_deadlines.map(c => `
                    <div style="display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f1f3;">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 12px; font-weight: 600; color: #1a1a2e;">${c.client_name}</div>
                            <div style="font-size: 10px; color: #8b8fa3;">${c.counsel_name} · ${c.case_number}</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 11px; font-weight: 600; color: ${c.days_until <= 3 ? '#dc2626' : c.days_until <= 7 ? '#d97706' : '#059669'};">
                                ${c.days_until === 0 ? 'Today' : c.days_until === 1 ? 'Tomorrow' : c.days_until + ' days'}
                            </div>
                            <div style="font-size: 10px; color: #8b8fa3;">${c.demand_deadline}</div>
                        </div>
                    </div>
                `).join('');
            }

            if (!deadlinesHtml) {
                deadlinesHtml = '<div style="padding: 20px; text-align: center; color: #8b8fa3; font-size: 12px;">No upcoming deadlines</div>';
            }
            deadlinesDiv.innerHTML = deadlinesHtml;

            // Month stats list
            const monthDiv = document.getElementById('monthStats');
            if (stats.by_month && monthDiv) {
                monthDiv.innerHTML = stats.by_month.slice(0, 6).map(m => `
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f1f3;">
                        <div>
                            <span style="font-size: 13px; font-weight: 600; color: #1a1a2e; font-family: 'Outfit', sans-serif;">${m.month_name}</span>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 14px; font-weight: 700; color: #0d9488; font-family: 'Outfit', sans-serif;">${formatCurrency(m.total_commission)}</div>
                            <div style="font-size: 11px; color: #8b8fa3;">${m.case_count} cases</div>
                        </div>
                    </div>
                `).join('');
            }
        }

        // Dashboard Trend Chart
        let trendChartInstance = null;
        function renderTrendChart(monthData) {
            const ctx = document.getElementById('dashboardTrendChart');
            if (!ctx) return;

            if (trendChartInstance) {
                trendChartInstance.destroy();
            }

            trendChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: monthData.map(m => m.month_name),
                    datasets: [{
                        label: 'Commission',
                        data: monthData.map(m => parseFloat(m.total_commission)),
                        backgroundColor: '#0d9488',
                        borderRadius: 4,
                        barThickness: 32
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => formatCurrency(ctx.raw)
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: v => '$' + (v / 1000).toFixed(0) + 'k',
                                font: { size: 10 }
                            },
                            grid: { color: '#f0f1f3' }
                        },
                        x: {
                            ticks: { font: { size: 10 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // Dashboard Status Pie Chart
        let statusChartInstance = null;
        function renderStatusChart(statusData) {
            const ctx = document.getElementById('dashboardStatusChart');
            if (!ctx) return;

            if (statusChartInstance) {
                statusChartInstance.destroy();
            }

            const labels = [];
            const data = [];
            const colors = {
                'in_progress': '#3b82f6',
                'unpaid': '#f59e0b',
                'paid': '#10b981',
                'rejected': '#ef4444'
            };
            const bgColors = [];

            for (const [status, count] of Object.entries(statusData)) {
                labels.push(status === 'in_progress' ? 'In Progress' : status.charAt(0).toUpperCase() + status.slice(1));
                data.push(count);
                bgColors.push(colors[status] || '#94a3b8');
            }

            statusChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: bgColors,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { font: { size: 10 }, boxWidth: 12 }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        async function approveCase(id) {
            await processCase(id, 'approve');
        }

        async function rejectCase(id) {
            if (!confirm('Are you sure you want to reject this case?')) return;
            await processCase(id, 'reject');
        }

        async function processCase(id, action) {
            try {
                const result = await apiCall('api/approve.php', {
                    method: 'POST',
                    body: JSON.stringify({ case_id: id, action })
                });

                if (result.success) {
                    loadPendingCases();
                    loadStats();
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error processing case');
            }
        }

        function toggleSelectAll(type) {
            const checked = document.getElementById(`selectAll${type.charAt(0).toUpperCase() + type.slice(1)}`).checked;
            document.querySelectorAll(`.${type}-checkbox`).forEach(cb => cb.checked = checked);
        }

        async function deletePendingCase(id) {
            if (!confirm('Are you sure you want to delete this case?')) return;

            try {
                const result = await apiCall(`api/cases.php?id=${id}`, {
                    method: 'DELETE'
                });

                if (result.success) {
                    loadPendingCases();
                    loadStats();
                    alert('Case deleted successfully');
                } else {
                    alert(result.error || 'Error deleting case');
                }
            } catch (err) {
                alert(err.message || 'Error deleting case');
            }
        }

        async function deleteFromEditModal() {
            if (!currentCaseId) return;
            if (!confirm('Are you sure you want to delete this case? This action cannot be undone.')) return;

            try {
                const result = await apiCall(`api/cases.php?id=${currentCaseId}`, {
                    method: 'DELETE'
                });

                if (result.success) {
                    closeEditModal();
                    loadPendingCases();
                    loadAllCases();
                    loadStats();
                    alert('Case deleted successfully');
                } else {
                    alert(result.error || 'Error deleting case');
                }
            } catch (err) {
                alert(err.message || 'Error deleting case');
            }
        }

        async function bulkAction(action) {
            const selected = Array.from(document.querySelectorAll('.pending-checkbox:checked')).map(cb => parseInt(cb.value));

            if (selected.length === 0) {
                alert('Please select at least one case');
                return;
            }

            if (action === 'reject' && !confirm(`Are you sure you want to reject ${selected.length} cases?`)) {
                return;
            }

            try {
                const result = await apiCall('api/approve.php', {
                    method: 'PUT',
                    body: JSON.stringify({ case_ids: selected, action })
                });

                if (result.success) {
                    loadPendingCases();
                    loadStats();
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error processing cases');
            }
        }

        function generateReport() {
            const type = document.getElementById('reportType').value;
            if (!type) return;
            
            // Show/hide counsel filter
            document.getElementById('reportCounsel').classList.toggle('hidden', type !== 'counsel');
            
            // Generate report based on type
            const content = document.getElementById('reportContent');
                content.innerHTML = '<p style="text-align: center; padding: 16px 0;" class="text-secondary">Loading report...</p>';
            
            // For now, show a simple summary
            loadAllCases().then(() => {
                const paid = allCases.filter(c => c.status === 'paid');
                let html = '';

                if (type === 'counsel') {
                    const byCounsel = {};
                    paid.forEach(c => {
                        if (!byCounsel[c.counsel_name]) byCounsel[c.counsel_name] = { count: 0, commission: 0 };
                        byCounsel[c.counsel_name].count++;
                        byCounsel[c.counsel_name].commission += parseFloat(c.commission);
                    });
                    
                    html = `<table class="w-full"><thead><tr style="border-bottom: 1px solid #e5e7eb;"><th style="text-align: left; padding: 12px 0;" class="text-sm font-semibold text-secondary">Counsel</th><th style="text-align: right; padding: 12px 0;" class="text-sm font-semibold text-secondary">Cases</th><th style="text-align: right; padding: 12px 0;" class="text-sm font-semibold text-secondary">Commission</th></tr></thead><tbody>`;
                    Object.entries(byCounsel).forEach(([name, data]) => {
                        html += `<tr style="border-bottom: 1px solid #f3f4f6;"><td style="padding: 12px 0;" class="text-primary">${name}</td><td style="text-align: right; padding: 12px 0;">${data.count}</td><td style="text-align: right; padding: 12px 0;" class="text-green-600 font-bold text-money">${formatCurrency(data.commission)}</td></tr>`;
                    });
                    html += '</tbody></table>';
                }
                
                if (type === 'month') {
                    const byMonth = {};
                    paid.forEach(c => {
                        if (!byMonth[c.month]) byMonth[c.month] = { count: 0, commission: 0 };
                        byMonth[c.month].count++;
                        byMonth[c.month].commission += parseFloat(c.commission);
                    });
                    
                    html = `<table class="w-full"><thead><tr style="border-bottom: 1px solid #e5e7eb;"><th style="text-align: left; padding: 12px 0;" class="text-sm font-semibold text-secondary">Month</th><th style="text-align: right; padding: 12px 0;" class="text-sm font-semibold text-secondary">Cases</th><th style="text-align: right; padding: 12px 0;" class="text-sm font-semibold text-secondary">Commission</th></tr></thead><tbody>`;
                    Object.entries(byMonth).forEach(([month, data]) => {
                        html += `<tr style="border-bottom: 1px solid #f3f4f6;"><td style="padding: 12px 0;" class="text-primary">${month}</td><td style="text-align: right; padding: 12px 0;">${data.count}</td><td style="text-align: right; padding: 12px 0;" class="text-green-600 font-bold text-money">${formatCurrency(data.commission)}</td></tr>`;
                    });
                    html += '</tbody></table>';
                }
                
                content.innerHTML = html || '<p style="text-align: center; padding: 16px 0;" class="text-secondary">No data available</p>';
            });
        }

        function exportAllToExcel() {
            const data = allCases.map(c => ({
                'Status': c.status,
                'Counsel': c.counsel_name,
                'Case Type': c.case_type || '',
                'Case #': c.case_number,
                'Client Name': c.client_name,
                'Resolution Type': c.resolution_type || '',
                'Fee Rate': c.fee_rate || '',
                'Month': c.month,
                'Settled': c.settled,
                'Pre-Suit Offer': c.presuit_offer || 0,
                'Difference': c.difference || 0,
                'Legal Fee': c.legal_fee || 0,
                'Disc. Legal Fee': c.discounted_legal_fee || 0,
                'Commission': c.commission,
                'Check Received': c.check_received == 1 ? 'Yes' : 'No',
                'Note': c.note || ''
            }));
            
            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'All Cases');
            XLSX.writeFile(wb, `all-cases-${new Date().toISOString().split('T')[0]}.xlsx`);
        }

        function exportReport() {
            exportAllToExcel();
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0);
        }

        // Case Detail Modal Functions
        let currentCaseId = null;
        
        function viewPendingCaseDetail(id) {
            const c = pendingCases.find(x => x.id == id);
            if (!c) return;

            currentCaseId = id;
            
            // Use the same modal display function
            displayCaseDetailModal(c);
        }
        
        function viewCaseDetail(id) {
            const c = allCases.find(x => x.id == id);
            if (!c) return;

            currentCaseId = id;
            
            displayCaseDetailModal(c);
        }
        
        function displayCaseDetailModal(c) {
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
                    <div style="background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; position: relative; overflow: hidden;">
                        <div style="position: absolute; right: 0; top: 0; bottom: 0; width: 80px; background: linear-gradient(135deg, rgba(34, 211, 238, 0.1), rgba(168, 85, 247, 0.1));"></div>
                        <div>
                            <span style="font-size: 11px; font-weight: 500; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.5px;">Commission</span>
                            <div style="font-size: 11px; color: rgba(255,255,255,0.5); margin-top: 2px;">${c.check_received == 1 ? '✓ Check Received' : '⏳ Check Pending'}</div>
                        </div>
                        <span style="font-size: 22px; font-weight: 700; color: #22d3ee; position: relative; z-index: 1;">${formatCurrency(c.commission)}</span>
                    </div>
                </div>

                <!-- Counsel & Note -->
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px;">
                    <div>
                        <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Counsel</div>
                        <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; font-weight: 500;">${c.counsel_name}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Note</div>
                        <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${c.note || '-'}</div>
                    </div>
                </div>
            `;

            // Dynamically add approval buttons if case is unpaid
            const approvalButtonsDiv = document.getElementById('caseApprovalButtons');
            if (c.status === 'unpaid') {
                approvalButtonsDiv.innerHTML = `
                    <button onclick="approveCaseFromModal()" style="padding: 7px 14px; background: #059669; color: white; border: none; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;">Approve</button>
                `;
            } else {
                approvalButtonsDiv.innerHTML = '';
            }

            // Setup message section
            document.getElementById('caseMessageRecipient').textContent = c.counsel_name;
            document.getElementById('caseMessageSubject').value = `Case #${c.case_number} - ${c.client_name}`;
            document.getElementById('caseMessageBody').value = '';

            // Store the counsel user_id for sending message
            window.currentCaseUserId = c.user_id;

            document.getElementById('allCaseDetailModal').classList.add('show');
        }

        function closeCaseModal() {
            document.getElementById('allCaseDetailModal').classList.remove('show');
            currentCaseId = null;
            window.currentCaseUserId = null;
        }

        async function sendCaseMessage() {
            const subject = document.getElementById('caseMessageSubject').value.trim();
            const message = document.getElementById('caseMessageBody').value.trim();
            const toUserId = window.currentCaseUserId;

            if (!subject || !message) {
                alert('Please enter both subject and message');
                return;
            }

            if (!toUserId) {
                alert('Unable to determine recipient');
                return;
            }

            try {
                const result = await apiCall('api/messages.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        to_user_id: toUserId,
                        subject: subject,
                        message: message
                    })
                });

                if (result.success) {
                    alert('Message sent successfully!');
                    document.getElementById('caseMessageBody').value = '';
                    loadMessages(); // Reload notifications
                } else {
                    alert(result.error || 'Error sending message');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error sending message');
            }
        }

        function editCaseFromModal() {
            if (!currentCaseId) return;
            const caseId = currentCaseId;
            closeCaseModal();
            editCaseFromRow(caseId);
        }

        function deleteCaseFromModal() {
            if (!currentCaseId) return;
            const caseId = currentCaseId;
            closeCaseModal();
            deleteCaseFromRow(caseId);
        }

        function approveCaseFromModal() {
            if (!currentCaseId) return;
            const caseId = currentCaseId;
            closeCaseModal();
            approveCase(caseId);
        }

        function rejectCaseFromModal() {
            if (!currentCaseId) return;
            const caseId = currentCaseId;
            closeCaseModal();
            rejectCase(caseId);
        }
        
        function editCaseFromRow(id) {
            // Try to find in allCases first, then pendingCases
            let c = allCases.find(x => x.id == id);
            if (!c) {
                c = pendingCases.find(x => x.id == id);
            }
            if (!c) return;
            
            currentCaseId = id;
            
            // Initialize month dropdown if needed
            const monthSelect = document.getElementById('editMonth');
            if (monthSelect.options.length === 0) {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const year = new Date().getFullYear();
                months.forEach(m => {
                    monthSelect.innerHTML += `<option value="${m}. ${year}">${m}. ${year}</option>`;
                });
                // Add TBD option
                monthSelect.innerHTML += `<option value="TBD">TBD</option>`;
            }
            
            // Populate form
            document.getElementById('editCaseId').value = c.id;
            document.getElementById('editCaseType').value = c.case_type || 'Auto Accident';
            document.getElementById('editResolutionType').value = c.resolution_type || '';
            document.getElementById('editCaseNumber').value = c.case_number;
            document.getElementById('editClientName').value = c.client_name;
            document.getElementById('editMonth').value = c.month;
            document.getElementById('editFeeRate').value = c.fee_rate || '33.33';
            document.getElementById('editSettled').value = c.settled;
            document.getElementById('editPresuitOffer').value = c.presuit_offer || 0;
            document.getElementById('editDifference').value = formatCurrency(c.difference || 0);
            document.getElementById('editLegalFee').value = formatCurrency(c.legal_fee || 0);
            document.getElementById('editDiscountedLegalFee').value = c.discounted_legal_fee || 0;
            document.getElementById('editNote').value = c.note || '';
            document.getElementById('editCheckReceived').checked = c.check_received == 1;
            document.getElementById('editStatus').value = c.status || 'pending';

            // Calculate commission
            calculateEditCommission();
            
            document.getElementById('editCaseModal').classList.add('show');
        }
        
        function calculateEditCommission() {
            const settled = parseFloat(document.getElementById('editSettled').value) || 0;
            const presuit = parseFloat(document.getElementById('editPresuitOffer').value) || 0;
            const feeRate = parseFloat(document.getElementById('editFeeRate').value);
            const discLegalFee = parseFloat(document.getElementById('editDiscountedLegalFee').value) || 0;
            
            const difference = settled - presuit;
            const base = settled - presuit;
            const legalFee = feeRate === 33.33 ? (base / 3) : (base * 0.4);
            
            document.getElementById('editDifference').value = formatCurrency(difference);
            document.getElementById('editLegalFee').value = formatCurrency(legalFee);
            
            // Get commission rate from case owner
            let c = allCases.find(x => x.id == currentCaseId);
            if (!c) {
                c = pendingCases.find(x => x.id == currentCaseId);
            }
            if (c) {
                // Calculate commission based on the original commission rate
                // We'll use the ratio of current commission to discounted_legal_fee to get the rate
                const originalCommissionRate = c.commission && c.discounted_legal_fee ? 
                    (c.commission / c.discounted_legal_fee) * 100 : 10;
                const commission = discLegalFee * (originalCommissionRate / 100);
                document.getElementById('editCommission').textContent = formatCurrency(commission);
            } else {
                // Fallback to 10%
                const commission = discLegalFee * 0.1;
                document.getElementById('editCommission').textContent = formatCurrency(commission);
            }
        }
        
        function closeEditModal() {
            document.getElementById('editCaseModal').classList.remove('show');
            currentCaseId = null;
        }
        
        async function deleteCaseFromRow(id) {
            if (!confirm('Are you sure you want to delete this case?')) return;

            try {
                const result = await apiCall(`api/cases.php?id=${id}`, { method: 'DELETE' });

                if (result.success) {
                    loadAllCases();
                    loadPendingCases();
                    loadStats();
                    closeCaseModal();
                    closeEditModal();
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error deleting case');
            }
        }
        
        // Edit form submission
        document.getElementById('editCaseForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const caseId = document.getElementById('editCaseId').value;
            const data = {
                id: parseInt(caseId),
                case_type: document.getElementById('editCaseType').value,
                resolution_type: document.getElementById('editResolutionType').value,
                case_number: document.getElementById('editCaseNumber').value,
                client_name: document.getElementById('editClientName').value,
                month: document.getElementById('editMonth').value,
                fee_rate: parseFloat(document.getElementById('editFeeRate').value),
                settled: parseFloat(document.getElementById('editSettled').value) || 0,
                presuit_offer: parseFloat(document.getElementById('editPresuitOffer').value) || 0,
                discounted_legal_fee: parseFloat(document.getElementById('editDiscountedLegalFee').value) || 0,
                note: document.getElementById('editNote').value,
                check_received: document.getElementById('editCheckReceived').checked,
                status: document.getElementById('editStatus').value
            };

            try {
                const result = await apiCall('api/cases.php', {
                    method: 'PUT',
                    body: JSON.stringify(data)
                });

                if (result.success) {
                    closeEditModal();
                    loadAllCases();
                    loadPendingCases();
                    loadStats();
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error updating case');
            }
        });
        
        // Add event listeners for edit form calculations
        document.getElementById('editSettled')?.addEventListener('change', calculateEditCommission);
        document.getElementById('editPresuitOffer')?.addEventListener('change', calculateEditCommission);
        document.getElementById('editFeeRate')?.addEventListener('change', calculateEditCommission);
        document.getElementById('editDiscountedLegalFee')?.addEventListener('change', calculateEditCommission);

        // Comprehensive Reports
        let reportCharts = {
            monthly: null,
            counsel: null,
            caseType: null
        };

        function generateComprehensiveReport() {
            const paid = allCases.filter(c => c.status === 'paid');
            const currentYear = new Date().getFullYear();
            const currentMonth = new Date().toLocaleDateString('en-US', { month: 'short' });
            const currentMonthStr = `${currentMonth}. ${currentYear}`;

            // Calculate stats
            const monthlyData = paid.filter(c => c.month === currentMonthStr);
            const ytdData = paid.filter(c => {
                const caseYear = c.month.split('. ')[1];
                return caseYear == currentYear;
            });
            const pendingPayment = paid.filter(c => !c.check_received);

            const monthlyCommission = monthlyData.reduce((sum, c) => sum + parseFloat(c.commission), 0);
            const ytdCommission = ytdData.reduce((sum, c) => sum + parseFloat(c.commission), 0);
            const avgCommission = paid.length > 0 ? paid.reduce((sum, c) => sum + parseFloat(c.commission), 0) / paid.length : 0;
            const pendingCommission = pendingPayment.reduce((sum, c) => sum + parseFloat(c.commission), 0);

            // Update stat cards
            document.getElementById('report-monthly-amount').textContent = formatCurrency(monthlyCommission);
            document.getElementById('report-monthly-cases').textContent = `${monthlyData.length} cases`;
            document.getElementById('report-ytd-amount').textContent = formatCurrency(ytdCommission);
            document.getElementById('report-ytd-cases').textContent = `${ytdData.length} cases`;
            document.getElementById('report-avg-amount').textContent = formatCurrency(avgCommission);
            document.getElementById('report-pending-amount').textContent = formatCurrency(pendingCommission);
            document.getElementById('report-pending-cases').textContent = `${pendingPayment.length} cases`;

            // Generate charts
            generateMonthlyChart(paid);

            // Generate tables
            generateCounselTable(paid);
            generateCaseTypeTable(paid);
            generateMonthlyBreakdownTable(paid);
        }

        function generateMonthlyChart(cases) {
            const ctx = document.getElementById('commissionByMonthChart');
            if (!ctx) return;

            // Destroy existing chart
            if (reportCharts.monthly) {
                reportCharts.monthly.destroy();
            }

            // Group by month
            const byMonth = {};
            cases.forEach(c => {
                if (!byMonth[c.month]) {
                    byMonth[c.month] = 0;
                }
                byMonth[c.month] += parseFloat(c.commission);
            });

            // Sort by date
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const sorted = Object.entries(byMonth).sort((a, b) => {
                const aMonth = a[0].split('. ')[0];
                const bMonth = b[0].split('. ')[0];
                const aYear = parseInt(a[0].split('. ')[1]);
                const bYear = parseInt(b[0].split('. ')[1]);
                if (aYear !== bYear) return aYear - bYear;
                return months.indexOf(aMonth) - months.indexOf(bMonth);
            });

            const labels = sorted.map(([month]) => month);
            const data = sorted.map(([, commission]) => commission);

            reportCharts.monthly = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Commission',
                        data: data,
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return formatCurrency(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + (value / 1000).toFixed(0) + 'k';
                                }
                            }
                        }
                    }
                }
            });
        }

        function generateCounselChart(cases) {
            const ctx = document.getElementById('counselChart');
            if (!ctx) return;

            // Destroy existing chart
            if (reportCharts.counsel) {
                reportCharts.counsel.destroy();
            }

            // Group by counsel
            const byCounsel = {};
            cases.forEach(c => {
                const counsel = c.counsel_name || 'Unknown';
                if (!byCounsel[counsel]) {
                    byCounsel[counsel] = 0;
                }
                byCounsel[counsel] += parseFloat(c.commission);
            });

            const sorted = Object.entries(byCounsel).sort((a, b) => b[1] - a[1]);
            const labels = sorted.map(([counsel]) => counsel);
            const data = sorted.map(([, commission]) => commission);

            const colors = [
                'rgba(59, 130, 246, 0.8)',
                'rgba(34, 197, 94, 0.8)',
                'rgba(251, 191, 36, 0.8)',
                'rgba(168, 85, 247, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(20, 184, 166, 0.8)',
                'rgba(244, 63, 94, 0.8)'
            ];

            reportCharts.counsel = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = formatCurrency(context.parsed);
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function generateCaseTypeChart(cases) {
            const ctx = document.getElementById('caseTypeChart');
            if (!ctx) return;

            // Destroy existing chart
            if (reportCharts.caseType) {
                reportCharts.caseType.destroy();
            }

            // Group by case type
            const byType = {};
            cases.forEach(c => {
                const type = c.case_type || 'Unknown';
                if (!byType[type]) {
                    byType[type] = 0;
                }
                byType[type] += parseFloat(c.commission);
            });

            const sorted = Object.entries(byType).sort((a, b) => b[1] - a[1]);
            const labels = sorted.map(([type]) => type);
            const data = sorted.map(([, commission]) => commission);

            const colors = [
                'rgba(59, 130, 246, 0.8)',
                'rgba(34, 197, 94, 0.8)',
                'rgba(251, 191, 36, 0.8)',
                'rgba(168, 85, 247, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(20, 184, 166, 0.8)',
                'rgba(244, 63, 94, 0.8)'
            ];

            reportCharts.caseType = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = formatCurrency(context.parsed);
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function generateCounselTable(cases) {
            const tbody = document.getElementById('counselTableBody');
            if (!tbody) return;

            // Group by counsel
            const byCounsel = {};
            cases.forEach(c => {
                const counsel = c.counsel_name || 'Unknown';
                if (!byCounsel[counsel]) {
                    byCounsel[counsel] = { count: 0, commission: 0 };
                }
                byCounsel[counsel].count++;
                byCounsel[counsel].commission += parseFloat(c.commission);
            });

            const totalCommission = cases.reduce((sum, c) => sum + parseFloat(c.commission), 0);
            const sorted = Object.entries(byCounsel).sort((a, b) => b[1].commission - a[1].commission);

            tbody.innerHTML = sorted.map(([counsel, data]) => {
                const avg = data.commission / data.count;
                const percentage = (data.commission / totalCommission * 100).toFixed(1);
                return `
                    <tr>
                        <td class="font-medium">${counsel}</td>
                        <td class="text-right">${data.count}</td>
                        <td class="text-right font-semibold text-green-600">${formatCurrency(data.commission)}</td>
                        <td class="text-right">${formatCurrency(avg)}</td>
                        <td class="text-right text-blue-600 font-medium">${percentage}%</td>
                    </tr>
                `;
            }).join('');
        }

        function generateCaseTypeTable(cases) {
            const tbody = document.getElementById('caseTypeTableBody');
            if (!tbody) return;

            // Group by case type
            const byType = {};
            cases.forEach(c => {
                const type = c.case_type || 'Unknown';
                if (!byType[type]) {
                    byType[type] = { count: 0, commission: 0 };
                }
                byType[type].count++;
                byType[type].commission += parseFloat(c.commission);
            });

            const totalCommission = cases.reduce((sum, c) => sum + parseFloat(c.commission), 0);
            const sorted = Object.entries(byType).sort((a, b) => b[1].commission - a[1].commission);

            tbody.innerHTML = sorted.map(([type, data]) => {
                const avg = data.commission / data.count;
                const percentage = (data.commission / totalCommission * 100).toFixed(1);
                return `
                    <tr>
                        <td class="font-medium">${type}</td>
                        <td class="text-right">${data.count}</td>
                        <td class="text-right font-semibold text-green-600">${formatCurrency(data.commission)}</td>
                        <td class="text-right">${formatCurrency(avg)}</td>
                        <td class="text-right text-blue-600 font-medium">${percentage}%</td>
                    </tr>
                `;
            }).join('');
        }

        function generateMonthlyBreakdownTable(cases) {
            const tbody = document.getElementById('monthlyBreakdownTableBody');
            if (!tbody) return;

            // Group by month
            const byMonth = {};
            cases.forEach(c => {
                if (!byMonth[c.month]) {
                    byMonth[c.month] = { count: 0, commission: 0, checkReceived: 0, checkPending: 0 };
                }
                byMonth[c.month].count++;
                byMonth[c.month].commission += parseFloat(c.commission);
                if (c.check_received) {
                    byMonth[c.month].checkReceived++;
                } else {
                    byMonth[c.month].checkPending++;
                }
            });

            // Sort by date
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const sorted = Object.entries(byMonth).sort((a, b) => {
                const aMonth = a[0].split('. ')[0];
                const bMonth = b[0].split('. ')[0];
                const aYear = parseInt(a[0].split('. ')[1]);
                const bYear = parseInt(b[0].split('. ')[1]);
                if (aYear !== bYear) return bYear - aYear;
                return months.indexOf(bMonth) - months.indexOf(aMonth);
            });

            tbody.innerHTML = sorted.map(([month, data]) => {
                const avg = data.commission / data.count;
                return `
                    <tr>
                        <td class="font-medium">${month}</td>
                        <td class="text-right">${data.count}</td>
                        <td class="text-right font-semibold text-green-600">${formatCurrency(data.commission)}</td>
                        <td class="text-right">${formatCurrency(avg)}</td>
                        <td class="text-right text-blue-600">${data.checkReceived}</td>
                        <td class="text-right text-amber-600">${data.checkPending}</td>
                    </tr>
                `;
            }).join('');
        }

        function exportReportToExcel() {
            const paid = allCases.filter(c => c.status === 'paid');

            if (paid.length === 0) {
                alert('No paid cases to export');
                return;
            }

            // Main data
            const mainData = paid.map(c => ({
                'Month': c.month,
                'Counsel': c.counsel_name,
                'Case #': c.case_number,
                'Client': c.client_name,
                'Case Type': c.case_type,
                'Resolution': c.resolution_type,
                'Settled': c.settled,
                'Pre-Suit Offer': c.presuit_offer,
                'Difference': c.difference,
                'Legal Fee': c.legal_fee,
                'Disc. Legal Fee': c.discounted_legal_fee,
                'Commission': c.commission,
                'Check Received': c.check_received ? 'Yes' : 'No'
            }));

            // Create workbook
            const wb = XLSX.utils.book_new();

            // Add main sheet
            const ws1 = XLSX.utils.json_to_sheet(mainData);
            XLSX.utils.book_append_sheet(wb, ws1, 'All Cases');

            // Add monthly summary
            const byMonth = {};
            paid.forEach(c => {
                if (!byMonth[c.month]) {
                    byMonth[c.month] = { count: 0, commission: 0 };
                }
                byMonth[c.month].count++;
                byMonth[c.month].commission += parseFloat(c.commission);
            });

            const monthlyData = Object.entries(byMonth).map(([month, data]) => ({
                'Month': month,
                'Cases': data.count,
                'Total Commission': data.commission,
                'Average': data.commission / data.count
            }));

            const ws2 = XLSX.utils.json_to_sheet(monthlyData);
            XLSX.utils.book_append_sheet(wb, ws2, 'Monthly Summary');

            // Add counsel summary
            const byCounsel = {};
            paid.forEach(c => {
                const counsel = c.counsel_name || 'Unknown';
                if (!byCounsel[counsel]) {
                    byCounsel[counsel] = { count: 0, commission: 0 };
                }
                byCounsel[counsel].count++;
                byCounsel[counsel].commission += parseFloat(c.commission);
            });

            const counselData = Object.entries(byCounsel).map(([counsel, data]) => ({
                'Counsel': counsel,
                'Cases': data.count,
                'Total Commission': data.commission,
                'Average': data.commission / data.count
            }));

            const ws3 = XLSX.utils.json_to_sheet(counselData);
            XLSX.utils.book_append_sheet(wb, ws3, 'By Counsel');

            // Export
            XLSX.writeFile(wb, `admin-comprehensive-report-${new Date().toISOString().split('T')[0]}.xlsx`);
        }

        // Message functions
        function openMessageModal(userId, userName) {
            document.getElementById('messageRecipientId').value = userId;
            document.getElementById('messageRecipientName').textContent = userName;
            document.getElementById('messageSubject').value = '';
            document.getElementById('messageBody').value = '';
            document.getElementById('messageModal').style.display = 'flex';
        }

        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        async function sendMessage(e) {
            e.preventDefault();

            const userId = document.getElementById('messageRecipientId').value;
            const subject = document.getElementById('messageSubject').value;
            const message = document.getElementById('messageBody').value;

            try {
                const result = await apiCall('api/messages.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        to_user_id: parseInt(userId),
                        subject: subject,
                        message: message
                    })
                });

                if (result.success) {
                    alert('Message sent successfully!');
                    closeMessageModal();
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error sending message');
            }
        }

        // Messages Tab Functions
        let currentViewingMessageAdmin = null;
        let allMessagesAdmin = [];
        let allItems = []; // Global variable to store all message items including case notifications

        async function loadMessages() {
            try {
                // Get dismissed notifications from localStorage
                const dismissed = JSON.parse(localStorage.getItem('dismissedNotificationsAdmin') || '[]');

                // Get all cases for notifications
                const casesData = await apiCall('api/cases.php');
                const cases = casesData.cases || [];

                // Get all messages
                const messagesData = await apiCall('api/messages.php');
                const messages = messagesData.messages || [];
                allMessagesAdmin = messages;

                // Get all users for employee names
                const usersData = await apiCall('api/users.php');
                const users = usersData.users || [];

                // Get case approval/rejection notifications (last 50) - filter out dismissed
                const reviewed = cases.filter(c => c.reviewed_at && !dismissed.includes(`case_${c.id}`))
                    .sort((a, b) => new Date(b.reviewed_at) - new Date(a.reviewed_at))
                    .slice(0, 50);

                const content = document.getElementById('notificationsContent');

                if (reviewed.length === 0 && messages.length === 0) {
                    content.innerHTML = '<p style="text-align: center; color: #8b8fa3; padding: 32px; font-size: 12px; font-family: Outfit, sans-serif;">No notifications yet</p>';
                    return;
                }

                let htmlOutput = '';

                // System Notifications Section
                if (reviewed.length > 0) {
                    htmlOutput += `
                        <div style="background: #1a1a2e; padding: 10px 16px;">
                            <h3 style="font-size: 11px; font-weight: 600; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; font-family: 'Outfit', sans-serif;">System Notifications</h3>
                        </div>
                    `;

                    reviewed.forEach(c => {
                        const employee = users.find(u => u.id === c.user_id);
                        const statusIcon = c.status === 'paid' ? '<span style="color: #0d9488;">✓</span>' : '<span style="color: #dc2626;">✗</span>';
                        htmlOutput += `
                            <div onclick="showCaseDetailAdmin(${c.id})" style="display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid #f0f1f3; cursor: pointer; transition: background 0.1s;" onmouseover="this.style.background='#f5f8ff'" onmouseout="this.style.background='transparent'">
                                <div style="width: 28px; height: 28px; border-radius: 6px; background: ${c.status === 'paid' ? '#d1fae5' : '#fee2e2'}; display: flex; align-items: center; justify-content: center; font-size: 14px; margin-right: 12px;">${statusIcon}</div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 13px; font-weight: 600; color: #1a1a2e; font-family: 'Outfit', sans-serif;">Case #${c.case_number} - ${c.client_name}</div>
                                    <div style="font-size: 11px; color: #8b8fa3; margin-top: 2px;">${c.status === 'paid' ? `Commission: $${parseFloat(c.commission).toLocaleString('en-US', {minimumFractionDigits: 2})}` : 'Rejected'} · ${formatDate(new Date(c.reviewed_at))}</div>
                                </div>
                                <button onclick="event.stopPropagation(); showDeleteConfirmModal('case', 'case_${c.id}')" class="act-link danger" style="margin-left: 8px;">Delete</button>
                            </div>
                        `;
                    });
                }

                // Messages Section
                if (messages.length > 0) {
                    htmlOutput += `
                        <div style="background: #1a1a2e; padding: 10px 16px;">
                            <h3 style="font-size: 11px; font-weight: 600; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; font-family: 'Outfit', sans-serif;">Messages</h3>
                        </div>
                    `;

                    messages.forEach(m => {
                        const isSent = m.direction === 'sent';
                        const bgColor = isSent ? '#d1fae5' : '#dbeafe';
                        const iconEmoji = isSent ? '↑' : '↓';
                        const unreadDot = !isSent && !m.is_read ? '<span style="color: #dc2626; margin-right: 4px;">●</span>' : '';

                        htmlOutput += `
                            <div onclick="viewMessage(${m.id})" style="display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid #f0f1f3; cursor: pointer; transition: background 0.1s;" onmouseover="this.style.background='#f5f8ff'" onmouseout="this.style.background='transparent'">
                                <div style="width: 28px; height: 28px; border-radius: 6px; background: ${bgColor}; display: flex; align-items: center; justify-content: center; font-size: 14px; margin-right: 12px; font-weight: 600; color: ${isSent ? '#0d9488' : '#3b82f6'};">${iconEmoji}</div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 13px; font-weight: 600; color: #1a1a2e; font-family: 'Outfit', sans-serif;">${unreadDot}${m.from_name} → ${m.to_name}</div>
                                    <div style="font-size: 11px; color: #8b8fa3; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${m.subject} · ${formatDate(new Date(m.created_at))}</div>
                                </div>
                                ${!isSent ? `<button onclick="event.stopPropagation(); showDeleteConfirmModal('message', ${m.id})" class="act-link danger" style="margin-left: 8px;">Delete</button>` : ''}
                            </div>
                        `;
                    });
                }

                // Render all content
                content.innerHTML = htmlOutput;

                // Update badge with unread count from API
                const unreadCount = messagesData.unread_count || 0;
                const badge = document.getElementById('notificationBadge');
                if (badge) {
                    if (unreadCount > 0) {
                        badge.textContent = unreadCount;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }

                // Populate employee dropdown for compose
                const select = document.getElementById('composeRecipientId');
                const employees = users.filter(u => u.role === 'employee');
                select.innerHTML = '<option value="">Select employee...</option>' +
                    employees.map(u => `<option value="${u.id}">${u.display_name}</option>`).join('');

            } catch (err) {
                console.error('Error loading messages:', err);
            }
        }

        async function markAllReadAdmin() {
            try {
                // Mark all messages as read
                await apiCall('api/messages.php', {
                    method: 'PUT',
                    body: JSON.stringify({ mark_all: true })
                });

                // Dismiss all case notifications
                const casesData = await apiCall('api/cases.php');
                const cases = casesData.cases || [];
                const reviewedCases = cases.filter(c => c.reviewed_at);
                const dismissed = reviewedCases.map(c => `case_${c.id}`);
                localStorage.setItem('dismissedNotificationsAdmin', JSON.stringify(dismissed));

                // Reload notifications
                loadMessages();
            } catch (err) {
                console.error('Error marking all as read:', err);
            }
        }

        function formatDate(date) {
            const now = new Date();
            const diff = now - date;
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));

            if (days === 0) {
                const hours = Math.floor(diff / (1000 * 60 * 60));
                if (hours === 0) {
                    const minutes = Math.floor(diff / (1000 * 60));
                    return minutes <= 1 ? 'Just now' : `${minutes}m ago`;
                }
                return `${hours}h ago`;
            } else if (days === 1) {
                return 'Yesterday';
            } else if (days < 7) {
                return `${days}d ago`;
            } else {
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }
        }

        function openComposeMessageAdmin() {
            document.getElementById('composeMessageModal').style.display = 'flex';
            document.getElementById('composeRecipientId').value = '';
            document.getElementById('composeSubject').value = '';
            document.getElementById('composeMessage').value = '';
        }

        function closeComposeMessageAdmin() {
            document.getElementById('composeMessageModal').style.display = 'none';
        }

        async function sendAdminMessage(e) {
            e.preventDefault();

            const toUserId = document.getElementById('composeRecipientId').value;
            const subject = document.getElementById('composeSubject').value;
            const message = document.getElementById('composeMessage').value;

            try {
                const result = await apiCall('api/messages.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        to_user_id: parseInt(toUserId),
                        subject: subject,
                        message: message
                    })
                });

                if (result.success) {
                    alert('Message sent successfully!');
                    closeComposeMessageAdmin();
                    loadMessages();
                } else {
                    alert(result.error || 'Error sending message');
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Error sending message');
            }
        }

        async function viewMessage(messageId) {
            try {
                // Get all messages
                const data = await apiCall('api/messages.php');
                const messages = data.messages || [];
                const message = messages.find(m => m.id == messageId);

                if (!message) return;

                // Get users for names
                const usersData = await apiCall('api/users.php');
                const users = usersData.users || [];

                // Determine if sent or received based on direction field from API
                const isSent = message.direction === 'sent';
                const otherName = isSent ? message.to_name : message.from_name;

                // Store current message with additional info for reply
                currentViewingMessageAdmin = {
                    ...message,
                    from_name: message.from_name,
                    to_name: message.to_name,
                    isSent: isSent
                };

                // Populate modal with direction-aware labels
                document.getElementById('viewMessageTitle').textContent = isSent ? `Message to ${otherName}` : `Message from ${otherName}`;
                document.getElementById('viewMessageFromLabel').textContent = isSent ? 'To:' : 'From:';
                document.getElementById('viewMessageFrom').textContent = otherName;
                document.getElementById('viewMessageSubject').textContent = message.subject;
                document.getElementById('viewMessageDate').textContent = new Date(message.created_at).toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                document.getElementById('viewMessageBody').textContent = message.message;

                // Show modal
                document.getElementById('viewMessageModal').style.display = 'flex';

                // Mark as read if it's a received message
                if (!isSent && !message.is_read) {
                    await apiCall('api/messages.php', {
                        method: 'PUT',
                        body: JSON.stringify({ id: messageId })
                    });
                    loadMessages(); // Reload to update badge
                }
            } catch (err) {
                console.error('Error:', err);
            }
        }

        function closeViewMessage() {
            document.getElementById('viewMessageModal').style.display = 'none';
        }

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
                dismissNotificationAdmin(pendingDeleteId);
            } else if (pendingDeleteType === 'message') {
                deleteMessageAdminConfirmed(pendingDeleteId);
            }
            closeDeleteConfirmModal();
        }

        function dismissNotificationAdmin(notificationId) {
            const dismissed = JSON.parse(localStorage.getItem('dismissedNotificationsAdmin') || '[]');
            dismissed.push(notificationId);
            localStorage.setItem('dismissedNotificationsAdmin', JSON.stringify(dismissed));
            loadMessages(); // Reload to hide the dismissed notification
        }

        async function deleteMessageAdmin(messageId) {
            // This function is no longer used - replaced by deleteMessageAdminConfirmed
            // Kept for backward compatibility
            if (!confirm('Are you sure you want to delete this message?')) {
                return;
            }
            await deleteMessageAdminConfirmed(messageId);
        }

        async function deleteMessageAdminConfirmed(messageId) {
            try {
                const result = await apiCall(`api/messages.php?id=${messageId}`, {
                    method: 'DELETE'
                });

                if (result.success) {
                    loadMessages(); // Reload messages
                } else {
                    alert(result.error || 'Error deleting message');
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Error deleting message');
            }
        }

        function replyToMessageAdmin() {
            if (!currentViewingMessageAdmin) return;

            closeViewMessage();

            // Open compose modal with pre-filled recipient and subject
            const replySubject = currentViewingMessageAdmin.subject.startsWith('Re: ')
                ? currentViewingMessageAdmin.subject
                : 'Re: ' + currentViewingMessageAdmin.subject;

            // Set recipient to the sender of the message
            document.getElementById('composeRecipientId').value = currentViewingMessageAdmin.from_user_id;
            document.getElementById('composeSubject').value = replySubject;
            document.getElementById('composeMessage').value = '';
            document.getElementById('composeMessageModal').style.display = 'flex';
        }

        async function deleteCurrentMessageAdmin() {
            if (!currentViewingMessageAdmin) return;

            if (!confirm('Are you sure you want to delete this message?')) {
                return;
            }

            try {
                const result = await apiCall(`api/messages.php?id=${currentViewingMessageAdmin.id}`, {
                    method: 'DELETE'
                });

                if (result.success) {
                    closeViewMessage();
                    loadMessages(); // Reload messages
                } else {
                    alert(result.error || 'Error deleting message');
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Error deleting message');
            }
        }

        // Close modals on outside click
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                e.target.style.display = 'none';
            }
        });

        // History Tab Functions
        let historyDropdownsInitialized = false;

        let historyCases = [];

        async function loadHistory() {
            try {
                // Fetch all cases
                const data = await apiCall('api/cases.php');
                const allHistoryCases = data.cases || [];

                // Populate dropdowns on first load (use paid cases only)
                const paidCases = allHistoryCases.filter(c => c.status === 'paid');
                if (!historyDropdownsInitialized) {
                    populateHistoryDropdowns(paidCases);
                    historyDropdownsInitialized = true;
                }

                // Get filter values
                const searchText = (document.getElementById('historySearch')?.value || '').toLowerCase();
                const employeeFilter = document.getElementById('historyEmployee')?.value || 'all';
                const monthFilter = document.getElementById('historyMonth')?.value || 'all';

                // Start with paid cases only
                let filtered = paidCases;

                // Employee filter
                if (employeeFilter !== 'all') {
                    filtered = filtered.filter(c => c.counsel_name?.toLowerCase() === employeeFilter.toLowerCase());
                }

                // Month filter
                if (monthFilter !== 'all') {
                    filtered = filtered.filter(c => c.month === monthFilter);
                }

                // Search filter
                if (searchText) {
                    filtered = filtered.filter(c =>
                        (c.case_number || '').toLowerCase().includes(searchText) ||
                        (c.client_name || '').toLowerCase().includes(searchText) ||
                        (c.note || '').toLowerCase().includes(searchText)
                    );
                }

                // Store for detail view
                historyCases = filtered;

                // Sort by reviewed_at or submitted_at, newest first
                filtered.sort((a, b) => {
                    const aDate = a.reviewed_at || a.submitted_at;
                    const bDate = b.reviewed_at || b.submitted_at;
                    return new Date(bDate) - new Date(aDate);
                });

                const content = document.getElementById('historyContent');

                if (filtered.length === 0) {
                    content.innerHTML = '<p style="text-align: center; color: #8b8fa3; padding: 32px; font-size: 12px; font-family: Outfit, sans-serif;">No cases found</p>';
                    return;
                }

                const totalCommission = filtered.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);

                const formatPaymentDate = (dateStr) => {
                    if (!dateStr) return '<span class="mute">-</span>';
                    const date = new Date(dateStr);
                    return date.toLocaleString('en-US', { month: 'short', day: 'numeric', year: '2-digit' });
                };

                const getStatusBadge = (status) => {
                    if (status === 'paid') return '<span class="stat-badge paid">Paid</span>';
                    if (status === 'pending') return '<span class="stat-badge pending">Pending</span>';
                    if (status === 'in_progress') return '<span class="stat-badge in_progress">In Progress</span>';
                    if (status === 'rejected') return '<span class="stat-badge rejected">Rejected</span>';
                    return `<span class="stat-badge">${status}</span>`;
                };

                // Group cases by month
                const casesByMonth = {};
                filtered.forEach(c => {
                    const monthKey = c.month || 'Unknown';
                    if (!casesByMonth[monthKey]) casesByMonth[monthKey] = [];
                    casesByMonth[monthKey].push(c);
                });

                // Sort months newest to oldest
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
                    <table class="tbl" id="historyTable" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Case #</th>
                                <th>Client</th>
                                <th>Resolution</th>
                                <th class="r">Settled</th>
                                <th class="r">Disc. Fee</th>
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
                            <td colspan="9" style="padding: 10px 12px; font-weight: 700; font-size: 12px; color: #1a1a2e; font-family: 'Outfit', sans-serif;">
                                ${monthKey}
                                <span style="float: right; color: #0d9488; font-size: 11px;">
                                    ${caseCount} case${caseCount !== 1 ? 's' : ''} · $${monthTotal.toLocaleString('en-US', {minimumFractionDigits: 2})}
                                </span>
                            </td>
                        </tr>
                    `;

                    cases.forEach(c => {
                        tableHtml += `
                            <tr onclick="viewHistoryDetail(${c.id})" style="cursor:pointer;">
                                <td style="font-weight: 600;">${c.counsel_name || '-'}</td>
                                <td style="font-weight: 600;">${c.case_number}</td>
                                <td>${c.client_name}</td>
                                <td style="font-size:11px;">${c.resolution_type || '-'}</td>
                                <td class="r">${formatCurrency(c.settled || 0)}</td>
                                <td class="r">${formatCurrency(c.discounted_legal_fee || 0)}</td>
                                <td class="r em">${formatCurrency(c.commission || 0)}</td>
                                <td>${formatPaymentDate(c.reviewed_at || c.submitted_at)}</td>
                                <td class="c">${c.check_received ? '<span style="display:inline-block;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:700;background:#d1fae5;color:#065f46;">Received</span>' : '<span style="display:inline-block;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:700;background:#fef3c7;color:#92400e;">Pending</span>'}</td>
                            </tr>
                        `;
                    });
                });

                tableHtml += `</tbody></table>`;

                // Add footer
                tableHtml += `
                    <div class="tbl-foot">
                        <div class="left">${filtered.length} case${filtered.length !== 1 ? 's' : ''}</div>
                        <div class="right">
                            <div class="ft"><span class="ft-l">Total:</span><span class="ft-v green">$${totalCommission.toLocaleString('en-US', {minimumFractionDigits: 2})}</span></div>
                        </div>
                    </div>
                `;

                content.innerHTML = tableHtml;
            } catch (err) {
                console.error('Error loading history:', err);
                document.getElementById('historyContent').innerHTML =
                    '<p style="text-align: center; color: #ef4444; padding: 32px;">Error loading data</p>';
            }
        }

        function populateHistoryDropdowns(cases) {
            // Populate employee dropdown
            const employeeSelect = document.getElementById('historyEmployee');
            const employees = [...new Set(cases.map(c => c.counsel_name).filter(Boolean))].sort();
            employees.forEach(emp => {
                const option = document.createElement('option');
                option.value = emp;
                option.textContent = emp;
                employeeSelect.appendChild(option);
            });

            // Populate month dropdown
            const monthSelect = document.getElementById('historyMonth');
            const months = [...new Set(cases.map(c => c.month).filter(Boolean))];

            // Sort months from newest to oldest
            months.sort((a, b) => {
                const parseMonth = (monthStr) => {
                    const parts = monthStr.split('. ');
                    if (parts.length !== 2) return new Date(0);
                    const [monthAbbr, year] = parts;
                    const monthMap = {'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'May': 4, 'Jun': 5,
                                     'Jul': 6, 'Aug': 7, 'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11};
                    return new Date(parseInt(year), monthMap[monthAbbr] || 0);
                };
                return parseMonth(b) - parseMonth(a);
            });

            months.forEach(month => {
                const option = document.createElement('option');
                option.value = month;
                option.textContent = month;
                monthSelect.appendChild(option);
            });
        }

        function resetHistoryFilters() {
            document.getElementById('historySearch').value = '';
            document.getElementById('historyEmployee').value = 'all';
            document.getElementById('historyMonth').value = 'all';
            loadHistory();
        }

        // Export history function
        function exportHistoryAdmin() {
            const employeeFilter = document.getElementById('historyEmployee')?.value || 'all';
            const monthFilter = document.getElementById('historyMonth')?.value || 'all';
            const searchText = document.getElementById('historySearch')?.value || '';

            let url = `api/export.php?type=history&status=paid`;
            if (employeeFilter !== 'all') url += `&employee=${encodeURIComponent(employeeFilter)}`;
            if (monthFilter !== 'all') url += `&month=${encodeURIComponent(monthFilter)}`;
            if (searchText) url += `&search=${encodeURIComponent(searchText)}`;

            window.location.href = url;
        }

        // History detail view
        function viewHistoryDetail(id) {
            const c = historyCases.find(x => x.id == id);
            if (!c) {
                // Fallback: try allCases
                const ac = allCases.find(x => x.id == id);
                if (ac) { viewCaseDetail(id); return; }
                return;
            }

            const modal = document.getElementById('historyDetailModal');
            const content = document.getElementById('historyDetailContent');

            const formatDate = (d) => {
                if (!d) return '-';
                return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            };

            content.innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Counsel</div>
                        <div style="font-size: 13px; font-weight: 600; color: #1a1a2e;">${c.counsel_name || '-'}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Month</div>
                        <div style="font-size: 13px; font-weight: 600; color: #1a1a2e;">${c.month}</div>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Case Number</div>
                        <div style="font-size: 13px; font-weight: 600; color: #1a1a2e;">${c.case_number}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Case Type</div>
                        <div style="font-size: 13px; color: #1a1a2e;">${c.case_type || '-'}</div>
                    </div>
                </div>
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Client Name</div>
                    <div style="font-size: 14px; font-weight: 700; color: #1a1a2e;">${c.client_name}</div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Resolution</div>
                        <div style="font-size: 13px; color: #1a1a2e;">${c.resolution_type || '-'}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Fee Rate</div>
                        <div style="font-size: 13px; color: #1a1a2e;">${c.fee_rate}%</div>
                    </div>
                </div>
                <div style="background: #f8fafc; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 8px;">
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase;">Settled</div>
                            <div style="font-size: 14px; font-weight: 700; color: #1a1a2e;">${formatCurrency(c.settled)}</div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase;">Pre-Suit</div>
                            <div style="font-size: 14px; font-weight: 600; color: #1a1a2e;">${formatCurrency(c.presuit_offer || 0)}</div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase;">Difference</div>
                            <div style="font-size: 14px; font-weight: 600; color: #1a1a2e;">${formatCurrency(c.difference || 0)}</div>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase;">Legal Fee</div>
                            <div style="font-size: 14px; font-weight: 600; color: #1a1a2e;">${formatCurrency(c.legal_fee || 0)}</div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase;">Disc. Fee</div>
                            <div style="font-size: 14px; font-weight: 600; color: #1a1a2e;">${formatCurrency(c.discounted_legal_fee || 0)}</div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase;">Commission</div>
                            <div style="font-size: 14px; font-weight: 700; color: #0d9488;">${formatCurrency(c.commission)}</div>
                        </div>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div>
                        <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Paid Date</div>
                        <div style="font-size: 13px; color: #1a1a2e;">${formatDate(c.reviewed_at)}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Check Received</div>
                        <div style="font-size: 13px; color: ${c.check_received ? '#059669' : '#dc2626'}; font-weight: 600;">${c.check_received ? 'Yes' : 'No'}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Submitted</div>
                        <div style="font-size: 13px; color: #1a1a2e;">${formatDate(c.submitted_at)}</div>
                    </div>
                </div>
                ${c.note ? `
                <div style="margin-top: 16px; padding: 10px 14px; background: #fffbeb; border-radius: 6px; border: 1px solid #fde68a;">
                    <div style="font-size: 10px; color: #92400e; text-transform: uppercase; margin-bottom: 2px;">Note</div>
                    <div style="font-size: 12px; color: #78350f;">${c.note}</div>
                </div>` : ''}
            `;

            modal.classList.add('show');
        }

        // Case Detail Modal Functions
        function showCaseDetailAdmin(caseId) {
            // First try to find in allCases
            let caseData = allCases.find(c => c.id === caseId);

            // If not found, try to find in allItems (from Messages tab)
            if (!caseData) {
                const item = allItems.find(i => i.type === 'case_notification' && i.case_id === caseId);
                if (item && item.caseData) {
                    caseData = item.caseData;
                }
            }

            if (!caseData) {
                alert('Case not found');
                return;
            }

            // Populate modal with case details
            const statusBadge = document.getElementById('adminDetailStatusBadge');
            statusBadge.className = 'status-badge';
            if (caseData.status === 'paid') {
                statusBadge.classList.add('badge-paid');
                statusBadge.textContent = 'Approved / Paid';
            } else if (caseData.status === 'rejected') {
                statusBadge.classList.add('badge-rejected');
                statusBadge.textContent = 'Rejected';
            } else {
                statusBadge.classList.add('badge-pending');
                statusBadge.textContent = 'Pending';
            }

            document.getElementById('adminDetailCaseNumber').textContent = caseData.case_number;
            document.getElementById('adminDetailClientName').textContent = caseData.client_name;
            document.getElementById('adminDetailCounsel').textContent = caseData.counsel_name || '-';
            document.getElementById('adminDetailCaseType').textContent = caseData.case_type || '-';
            document.getElementById('adminDetailResolutionType').textContent = caseData.resolution_type || '-';
            document.getElementById('adminDetailMonth').textContent = caseData.month;
            document.getElementById('adminDetailFeeRate').textContent = caseData.fee_rate + '%';

            document.getElementById('adminDetailSettled').textContent = '$' + parseFloat(caseData.settled).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('adminDetailPresuitOffer').textContent = '$' + parseFloat(caseData.presuit_offer).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('adminDetailDifference').textContent = '$' + parseFloat(caseData.difference).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('adminDetailLegalFee').textContent = '$' + parseFloat(caseData.legal_fee).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('adminDetailDiscountedLegalFee').textContent = '$' + parseFloat(caseData.discounted_legal_fee).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('adminDetailCommission').textContent = '$' + parseFloat(caseData.commission).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            // Note section
            const noteSection = document.getElementById('adminDetailNoteSection');
            const noteDiv = document.getElementById('adminDetailNote');
            if (caseData.note && caseData.note.trim()) {
                noteDiv.textContent = caseData.note;
                noteSection.style.display = 'block';
            } else {
                noteSection.style.display = 'none';
            }

            // Dates
            document.getElementById('adminDetailSubmittedAt').textContent = new Date(caseData.submitted_at).toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            const reviewedSection = document.getElementById('adminDetailReviewedSection');
            if (caseData.reviewed_at) {
                document.getElementById('adminDetailReviewedAt').textContent = new Date(caseData.reviewed_at).toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                reviewedSection.style.display = 'block';
            } else {
                reviewedSection.style.display = 'none';
            }

            // Show modal
            document.getElementById('messageCaseDetailModal').style.display = 'flex';
        }

        function closeCaseDetailAdmin() {
            document.getElementById('messageCaseDetailModal').style.display = 'none';
        }

        // User Management Functions
        let allUsers = [];

        async function loadUsers() {
            try {
                const response = await fetch('api/users.php');
                const data = await response.json();
                allUsers = data.users || [];
                renderUsers();
            } catch (err) {
                console.error('Error loading users:', err);
                document.getElementById('usersTableBody').innerHTML = `
                    <tr><td colspan="5" style="padding: 32px 16px; text-align: center; color: #ef4444;">Error loading users</td></tr>
                `;
            }
        }

        function renderUsers() {
            const tbody = document.getElementById('usersTableBody');

            if (allUsers.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" style="padding: 24px; text-align: center; color: #8b8fa3; font-size: 12px;">No users found</td></tr>`;
                return;
            }

            tbody.innerHTML = allUsers.map(user => {
                const perms = user.permissions || {};
                const isTrafficOn = user.role === 'admin' || !!perms.can_request_traffic;
                const isAdminUser = user.role === 'admin';

                return `
                <tr>
                    <td style="font-weight: 600;">${user.username}</td>
                    <td>${user.display_name}</td>
                    <td class="c">
                        <span class="stat-badge ${isAdminUser ? 'pending' : 'paid'}">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span>
                    </td>
                    <td class="r">${user.commission_rate || 0}%</td>
                    <td class="c">
                        <label class="toggle-switch" title="Traffic Request Access">
                            <input type="checkbox" ${isTrafficOn ? 'checked' : ''} ${isAdminUser ? 'disabled' : ''}
                                onchange="togglePermission(${user.id}, 'can_request_traffic', this.checked)">
                            <span class="toggle-slider"></span>
                        </label>
                    </td>
                    <td class="c">
                        <button onclick="editUser(${user.id})" class="act-link" style="margin-right: 4px;">Edit</button>
                        <button onclick="resetPassword(${user.id})" class="act-link" style="background: #d97706; margin-right: 4px;">Reset</button>
                        ${!isAdminUser ? `<button onclick="deleteUser(${user.id})" class="act-link danger">Delete</button>` : ''}
                    </td>
                </tr>`;
            }).join('');
        }

        async function togglePermission(userId, permission, enabled) {
            try {
                const result = await apiCall(`api/users.php?id=${userId}`, {
                    method: 'PUT',
                    body: JSON.stringify({ permissions: { [permission]: enabled } })
                });
                if (result.success) {
                    const user = allUsers.find(u => u.id === userId);
                    if (user) {
                        if (!user.permissions) user.permissions = {};
                        user.permissions[permission] = enabled;
                    }
                } else {
                    alert(result.error || 'Error updating permission');
                    loadUsers();
                }
            } catch (err) {
                console.error('Error toggling permission:', err);
                alert(err.message || 'Error updating permission');
                loadUsers();
            }
        }

        function openAddUserModal() {
            document.getElementById('userModalTitle').textContent = 'Add User';
            document.getElementById('editUserId').value = '';
            document.getElementById('userUsername').value = '';
            document.getElementById('userDisplayName').value = '';
            document.getElementById('userPassword').value = '';
            document.getElementById('userPassword').required = true;
            document.getElementById('passwordHint').textContent = 'Required for new users';
            document.getElementById('userRole').value = 'employee';
            document.getElementById('userCommissionRate').value = '0';
            document.getElementById('userModal').classList.add('show');
        }

        function editUser(id) {
            const user = allUsers.find(u => u.id === id);
            if (!user) return;

            document.getElementById('userModalTitle').textContent = 'Edit User';
            document.getElementById('editUserId').value = user.id;
            document.getElementById('userUsername').value = user.username;
            document.getElementById('userDisplayName').value = user.display_name;
            document.getElementById('userPassword').value = '';
            document.getElementById('userPassword').required = false;
            document.getElementById('passwordHint').textContent = 'Leave blank to keep current password';
            document.getElementById('userRole').value = user.role;
            document.getElementById('userCommissionRate').value = user.commission_rate || 0;
            document.getElementById('userModal').classList.add('show');
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.remove('show');
        }

        document.getElementById('userForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const userId = document.getElementById('editUserId').value;
            const username = document.getElementById('userUsername').value;
            const displayName = document.getElementById('userDisplayName').value;
            const password = document.getElementById('userPassword').value;
            const role = document.getElementById('userRole').value;
            const commissionRate = document.getElementById('userCommissionRate').value;

            const userData = {
                username,
                display_name: displayName,
                role,
                commission_rate: parseFloat(commissionRate)
            };

            if (password) {
                userData.password = password;
            }

            try {
                const url = userId ? `api/users.php?id=${userId}` : 'api/users.php';
                const method = userId ? 'PUT' : 'POST';

                const result = await apiCall(url, {
                    method,
                    body: JSON.stringify(userData)
                });

                if (result.success) {
                    closeUserModal();
                    loadUsers();
                    alert(userId ? 'User updated successfully!' : 'User added successfully!');
                } else {
                    alert(result.error || 'Error saving user');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error saving user');
            }
        });

        async function deleteUser(id) {
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;

            try {
                const result = await apiCall(`api/users.php?id=${id}`, {
                    method: 'DELETE'
                });

                if (result.success) {
                    loadUsers();
                    alert('User deleted successfully!');
                } else {
                    alert(result.error || 'Error deleting user');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error deleting user');
            }
        }

        async function resetPassword(id) {
            const newPassword = prompt('Enter new password for this user:');
            if (!newPassword) return;

            if (newPassword.length < 4) {
                alert('Password must be at least 4 characters long');
                return;
            }

            try {
                const result = await apiCall(`api/users.php?id=${id}`, {
                    method: 'PUT',
                    body: JSON.stringify({ password: newPassword })
                });

                if (result.success) {
                    alert('Password reset successfully!');
                } else {
                    alert(result.error || 'Error resetting password');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error resetting password');
            }
        }

        // ===== TRAFFIC CASES FUNCTIONS (Admin) =====
        let adminTrafficCases = [];
        let adminTrafficAllCases = [];
        let myTrafficRequests = [];
        let adminTrafficFilter = 'active';
        let adminTrafficSearchTerm = '';

        async function loadAdminTrafficCases() {
            try {
                const data = await apiCall('api/traffic.php?status=all');
                adminTrafficAllCases = data.cases || [];
                updateTrafficStats();
                applyAdminTrafficFilters();
            } catch (err) {
                console.error('Error loading traffic cases:', err);
            }
        }

        function updateTrafficStats() {
            const total = adminTrafficAllCases.length;
            const active = adminTrafficAllCases.filter(c => c.status === 'active').length;
            const resolved = adminTrafficAllCases.filter(c => c.status === 'resolved').length;
            const dismissed = adminTrafficAllCases.filter(c => c.disposition === 'dismissed').length;
            const amended = adminTrafficAllCases.filter(c => c.disposition === 'amended').length;
            const pendingReq = myTrafficRequests.filter(r => r.status === 'pending').length;

            const setEl = (id, val) => { const el = document.getElementById(id); if(el) el.textContent = val; };
            setEl('trafficStatTotal', total);
            setEl('trafficStatActive', active);
            setEl('trafficStatDismissed', dismissed);
            setEl('trafficStatAmended', amended);
            setEl('trafficStatPendingReq', pendingReq);

            // Update overview sidebar
            setEl('trafficOverviewActive', active);
            setEl('trafficOverviewDone', resolved);

            // Update filter counts
            setEl('trafficCountAll', total);
            setEl('trafficCountActive', active);
            setEl('trafficCountDone', resolved);
        }

        // Sidebar tab switching
        let adminTrafficSidebarTab = 'all';

        function switchAdminTrafficTab(tab) {
            adminTrafficSidebarTab = tab;

            // Update tab buttons
            document.querySelectorAll('[id^="adminTrafficTab-"]').forEach(btn => btn.classList.remove('active'));
            document.getElementById(`adminTrafficTab-${tab}`)?.classList.add('active');

            // Update sidebar content
            const contentEl = document.getElementById('adminTrafficSidebarContent');
            if (!contentEl) return;

            if (tab === 'all') {
                const total = adminTrafficAllCases.length;
                const active = adminTrafficAllCases.filter(c => c.status === 'active').length;
                const done = adminTrafficAllCases.filter(c => c.status === 'resolved').length;

                contentEl.innerHTML = `
                    <div style="font-size: 11px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Overview</div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <div style="display: flex; justify-content: space-between; font-family: 'Outfit', sans-serif;">
                            <span style="font-size: 12px; color: #3d3f4e;">Total Cases</span>
                            <span style="font-size: 13px; font-weight: 700; color: #1a1a2e;">${total}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-family: 'Outfit', sans-serif;">
                            <span style="font-size: 12px; color: #3b82f6;">Active</span>
                            <span style="font-size: 13px; font-weight: 700; color: #3b82f6;">${active}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-family: 'Outfit', sans-serif;">
                            <span style="font-size: 12px; color: #0d9488;">Done</span>
                            <span style="font-size: 13px; font-weight: 700; color: #0d9488;">${done}</span>
                        </div>
                    </div>
                `;
            } else if (tab === 'referral') {
                const grouped = {};
                adminTrafficAllCases.forEach(c => {
                    const ref = c.referral_source || 'Unknown';
                    if (!grouped[ref]) grouped[ref] = { count: 0 };
                    grouped[ref].count++;
                });

                const sorted = Object.entries(grouped).sort((a, b) => b[1].count - a[1].count);
                contentEl.innerHTML = `
                    <div style="font-size: 11px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">By Referral</div>
                    <div style="display: flex; flex-direction: column; gap: 4px; max-height: 250px; overflow-y: auto;">
                        ${sorted.map(([name, data]) => `
                            <div onclick="filterByReferral('${name}')" style="display: flex; justify-content: space-between; padding: 6px 8px; border-radius: 6px; cursor: pointer; font-family: 'Outfit', sans-serif; transition: background 0.1s;" onmouseover="this.style.background='#f5f5f7'" onmouseout="this.style.background='transparent'">
                                <span style="font-size: 12px; color: #3d3f4e;">${name}</span>
                                <span style="font-size: 12px; font-weight: 600; color: #5c5f73;">${data.count}</span>
                            </div>
                        `).join('')}
                    </div>
                `;
            } else if (tab === 'court') {
                const grouped = {};
                adminTrafficAllCases.forEach(c => {
                    const court = c.court || 'Unknown';
                    if (!grouped[court]) grouped[court] = { count: 0 };
                    grouped[court].count++;
                });

                const sorted = Object.entries(grouped).sort((a, b) => b[1].count - a[1].count);
                contentEl.innerHTML = `
                    <div style="font-size: 11px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">By Court</div>
                    <div style="display: flex; flex-direction: column; gap: 4px; max-height: 250px; overflow-y: auto;">
                        ${sorted.map(([name, data]) => `
                            <div onclick="filterByCourt('${name}')" style="display: flex; justify-content: space-between; padding: 6px 8px; border-radius: 6px; cursor: pointer; font-family: 'Outfit', sans-serif; transition: background 0.1s;" onmouseover="this.style.background='#f5f5f7'" onmouseout="this.style.background='transparent'">
                                <span style="font-size: 12px; color: #3d3f4e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${name}</span>
                                <span style="font-size: 12px; font-weight: 600; color: #5c5f73; flex-shrink: 0; margin-left: 8px;">${data.count}</span>
                            </div>
                        `).join('')}
                    </div>
                `;
            } else if (tab === 'year') {
                const grouped = {};
                adminTrafficAllCases.forEach(c => {
                    const year = c.court_date ? new Date(c.court_date).getFullYear() : 'Unknown';
                    if (!grouped[year]) grouped[year] = { count: 0 };
                    grouped[year].count++;
                });

                const sorted = Object.entries(grouped).sort((a, b) => b[0] - a[0]);
                contentEl.innerHTML = `
                    <div style="font-size: 11px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">By Year</div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        ${sorted.map(([year, data]) => `
                            <div onclick="filterByYear('${year}')" style="display: flex; justify-content: space-between; padding: 6px 8px; border-radius: 6px; cursor: pointer; font-family: 'Outfit', sans-serif; transition: background 0.1s;" onmouseover="this.style.background='#f5f5f7'" onmouseout="this.style.background='transparent'">
                                <span style="font-size: 12px; color: #3d3f4e;">${year}</span>
                                <span style="font-size: 12px; font-weight: 600; color: #5c5f73;">${data.count}</span>
                            </div>
                        `).join('')}
                    </div>
                `;
            }
        }

        function filterByReferral(name) {
            document.getElementById('trafficFilterLabel').textContent = `Referral: ${name}`;
            adminTrafficCases = adminTrafficAllCases.filter(c => (c.referral_source || 'Unknown') === name);
            renderAdminTrafficCases();
        }

        function filterByCourt(name) {
            document.getElementById('trafficFilterLabel').textContent = `Court: ${name}`;
            adminTrafficCases = adminTrafficAllCases.filter(c => (c.court || 'Unknown') === name);
            renderAdminTrafficCases();
        }

        function filterByYear(year) {
            document.getElementById('trafficFilterLabel').textContent = `Year: ${year}`;
            if (year === 'Unknown') {
                adminTrafficCases = adminTrafficAllCases.filter(c => !c.court_date);
            } else {
                adminTrafficCases = adminTrafficAllCases.filter(c => c.court_date && new Date(c.court_date).getFullYear() == year);
            }
            renderAdminTrafficCases();
        }

        function filterAdminTraffic(status, el) {
            adminTrafficFilter = status;
            document.getElementById('trafficFilterLabel').textContent = status === 'all' ? 'All Cases' : (status === 'active' ? 'Active Cases' : 'Done Cases');

            // Update status filter buttons
            document.querySelectorAll('[id^="adminTrafficStatusBtn-"]').forEach(c => c.classList.remove('active'));
            if (el) el.classList.add('active');
            applyAdminTrafficFilters();
        }

        function searchAdminTraffic(term) {
            adminTrafficSearchTerm = term.toLowerCase();
            applyAdminTrafficFilters();
        }

        function applyAdminTrafficFilters() {
            let filtered = [...adminTrafficAllCases];

            // Status filter
            if (adminTrafficFilter !== 'all') {
                filtered = filtered.filter(c => c.status === adminTrafficFilter);
            }

            // Search filter
            if (adminTrafficSearchTerm) {
                filtered = filtered.filter(c =>
                    (c.client_name || '').toLowerCase().includes(adminTrafficSearchTerm) ||
                    (c.court || '').toLowerCase().includes(adminTrafficSearchTerm) ||
                    (c.charge || '').toLowerCase().includes(adminTrafficSearchTerm) ||
                    (c.case_number || '').toLowerCase().includes(adminTrafficSearchTerm) ||
                    (c.requester_name || '').toLowerCase().includes(adminTrafficSearchTerm)
                );
            }

            adminTrafficCases = filtered;
            renderAdminTrafficCases();
        }

        function renderAdminTrafficCases() {
            const tbody = document.getElementById('adminTrafficTableBody');
            if (!tbody) return;

            if (adminTrafficCases.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" style="padding: 32px 16px; text-align: center; color: #8b8fa3; font-size: 12px; font-family: Outfit, sans-serif;">No traffic cases found</td></tr>';
                updateTrafficFooter([]);
                return;
            }

            tbody.innerHTML = adminTrafficCases.map(c => {
                const courtDate = c.court_date ? new Date(c.court_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' }) : '-';
                const noaDate = c.noa_sent_date ? new Date(c.noa_sent_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '<span class="mute">-</span>';
                const discoveryStatus = c.discovery ? '<span style="color: #0d9488; font-weight: 600;">✓</span>' : '<span class="mute">-</span>';

                let dispositionBadge = '';
                if (c.disposition === 'dismissed') {
                    dispositionBadge = '<span class="stat-badge paid">Dismissed</span>';
                } else if (c.disposition === 'amended') {
                    dispositionBadge = '<span class="stat-badge unpaid">Amended</span>';
                } else {
                    dispositionBadge = '<span class="stat-badge pending">Pending</span>';
                }

                let statusBadge = c.status === 'active'
                    ? '<span class="stat-badge in_progress">Active</span>'
                    : '<span style="background: #f0f1f3; color: #5c5f73; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase;">Done</span>';

                return `
                    <tr onclick="editAdminTrafficCase(${c.id})">
                        <td style="font-weight: 600;">${c.client_name || '-'}</td>
                        <td>${c.court || '-'}</td>
                        <td>${courtDate}</td>
                        <td>${c.charge || '-'}</td>
                        <td class="c">${noaDate}</td>
                        <td class="c">${discoveryStatus}</td>
                        <td>${dispositionBadge}</td>
                        <td class="c">${statusBadge}</td>
                        <td class="mute" style="font-size: 11px;">${c.requester_name || '-'}</td>
                        <td class="c">
                            <button onclick="event.stopPropagation(); editAdminTrafficCase(${c.id})" class="act-link" title="Edit">Edit</button>
                        </td>
                    </tr>
                `;
            }).join('');

            updateTrafficFooter(adminTrafficCases);
        }

        function updateTrafficFooter(cases) {
            const count = cases.length;
            const dismissed = cases.filter(c => c.disposition === 'dismissed').length;
            const amended = cases.filter(c => c.disposition === 'amended').length;

            const setEl = (id, val) => { const el = document.getElementById(id); if(el) el.textContent = val; };
            setEl('trafficTableCount', count);
            setEl('trafficFootDismissed', dismissed);
            setEl('trafficFootAmended', amended);
        }

        async function loadMyTrafficRequests() {
            try {
                const data = await apiCall('api/traffic_requests.php');
                myTrafficRequests = data.requests || [];
                renderMyTrafficRequests();
                updateTrafficStats();
            } catch (err) {
                console.error('Error loading traffic requests:', err);
            }
        }

        function filterMyRequests(searchTerm) {
            renderMyTrafficRequests(searchTerm);
        }

        function renderMyTrafficRequests(searchTerm = '') {
            const container = document.getElementById('myTrafficRequests');
            if (!container) return;

            // Update stats
            updateTrafficStats();

            // Filter by search term
            const filteredRequests = searchTerm.trim()
                ? myTrafficRequests.filter(r => {
                    const term = searchTerm.toLowerCase();
                    return (r.client_name || '').toLowerCase().includes(term) ||
                           (r.court || '').toLowerCase().includes(term) ||
                           (r.case_number || '').toLowerCase().includes(term) ||
                           (r.requester_name || '').toLowerCase().includes(term);
                })
                : myTrafficRequests;

            if (filteredRequests.length === 0) {
                container.innerHTML = searchTerm.trim()
                    ? '<p style="padding: 12px; text-align: center; color: #8b8fa3; font-size: 11px; font-family: Outfit, sans-serif;">No matching requests</p>'
                    : '<p style="padding: 12px; text-align: center; color: #8b8fa3; font-size: 11px; font-family: Outfit, sans-serif;">No requests yet</p>';
                return;
            }

            container.innerHTML = filteredRequests.map(r => {
                let statusClass = r.status === 'pending' ? 'pending' : (r.status === 'accepted' ? 'approved' : 'denied');
                let statusText = r.status === 'pending' ? 'Pending' : (r.status === 'accepted' ? 'Accepted' : 'Denied');
                const courtDateStr = r.court_date ? new Date(r.court_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '-';
                const requesterName = r.requester_name || 'Unknown';
                const respondedAt = r.responded_at ? new Date(r.responded_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '';

                return `
                    <div class="req-item" onclick="viewTrafficRequest(${r.id})">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <span class="req-name">${escapeHtml(r.client_name)}</span>
                                <span class="req-status ${statusClass}">${statusText}</span>
                            </div>
                        </div>
                        <div class="req-meta">${escapeHtml(requesterName)} → Chong · ${escapeHtml(r.court || '-')} · ${courtDateStr}${respondedAt ? ' · ' + respondedAt : ''}</div>
                    </div>
                `;
            }).join('');
        }

        function viewTrafficRequest(id) {
            const req = myTrafficRequests.find(r => r.id == id);
            if (!req) return;

            const courtDate = req.court_date ? new Date(req.court_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-';
            const citationDate = req.citation_issued_date ? new Date(req.citation_issued_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-';
            const createdAt = req.created_at ? new Date(req.created_at).toLocaleString() : '-';
            const respondedAt = req.responded_at ? new Date(req.responded_at).toLocaleString() : '-';

            let statusText = req.status.charAt(0).toUpperCase() + req.status.slice(1);
            const requesterName = req.requester_name || 'Unknown';

            let details = `Status: ${statusText}
Requested by: ${requesterName}
Requested: ${createdAt}${req.status === 'accepted' ? '\nAccepted: ' + respondedAt : ''}${req.status === 'denied' ? '\nDenied: ' + respondedAt : ''}

Client: ${req.client_name || '-'}
Phone: ${req.client_phone || '-'}
Email: ${req.client_email || '-'}

Court: ${req.court || '-'}
Court Date: ${courtDate}
Charge: ${req.charge || '-'}
Ticket #: ${req.case_number || '-'}
Issued: ${citationDate}

Note: ${req.note || '-'}`;

            if (req.status === 'denied' && req.deny_reason) {
                details += `\n\nDeny Reason: ${req.deny_reason}`;
            }

            alert(details);
        }

        // Delete My Traffic Request
        async function deleteMyTrafficRequest(id, clientName) {
            if (!confirm(`Delete request for "${clientName}"?`)) return;

            try {
                const result = await apiCall('api/traffic_requests.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                if (result.success) {
                    loadMyTrafficRequests();
                } else {
                    alert(result.error || 'Failed to delete request');
                }
            } catch (err) {
                console.error('Error deleting request:', err);
                alert('Error deleting request');
            }
        }

        // Traffic Request Form Submit
        document.getElementById('trafficRequestForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            const data = {
                client_name: document.getElementById('reqClientName').value.trim(),
                client_phone: document.getElementById('reqClientPhone').value.trim(),
                client_email: document.getElementById('reqClientEmail').value.trim(),
                court: document.getElementById('reqCourt').value.trim(),
                court_date: document.getElementById('reqCourtDate').value || null,
                charge: document.getElementById('reqCharge').value.trim(),
                case_number: document.getElementById('reqCaseNumber').value.trim(),
                citation_issued_date: document.getElementById('reqCitationIssuedDate').value || null,
                note: document.getElementById('reqNote').value.trim(),
                referral_source: document.getElementById('reqReferralSource').value.trim()
            };

            if (!data.client_name) {
                alert('Client name is required');
                return;
            }

            try {
                const result = await apiCall('api/traffic_requests.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                if (result.success) {
                    alert('Request submitted successfully! Chong will receive a notification.');
                    this.reset();
                    loadMyTrafficRequests();
                } else {
                    alert(result.error || 'Error submitting request');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error submitting request');
            }
        });

        // ===== ADMIN TRAFFIC CASE EDIT/DELETE FUNCTIONS =====
        let editingTrafficCaseId = null;

        function editAdminTrafficCase(id) {
            const c = adminTrafficCases.find(c => c.id == id);
            if (!c) return;

            editingTrafficCaseId = id;

            // Populate modal fields
            document.getElementById('adminTrafficClientName').value = c.client_name || '';
            document.getElementById('adminTrafficClientPhone').value = c.client_phone || '';
            document.getElementById('adminTrafficCourt').value = c.court || '';
            document.getElementById('adminTrafficCourtDate').value = c.court_date ? c.court_date.split(' ')[0] : '';
            document.getElementById('adminTrafficCharge').value = c.charge || '';
            document.getElementById('adminTrafficCaseNumber').value = c.case_number || '';
            document.getElementById('adminTrafficOffer').value = c.prosecutor_offer || '';
            document.getElementById('adminTrafficDisposition').value = c.disposition || 'pending';
            document.getElementById('adminTrafficStatus').value = c.status || 'active';
            document.getElementById('adminTrafficTicketIssuedDate').value = c.citation_issued_date || '';
            document.getElementById('adminTrafficNoaSentDate').value = c.noa_sent_date || '';
            document.getElementById('adminTrafficDiscovery').checked = c.discovery == 1;
            document.getElementById('adminTrafficNote').value = c.note || '';
            document.getElementById('adminTrafficReferralSource').value = c.referral_source || '';

            document.getElementById('adminTrafficModal').style.display = 'flex';
        }

        function closeAdminTrafficModal() {
            document.getElementById('adminTrafficModal').style.display = 'none';
            editingTrafficCaseId = null;
        }

        async function saveAdminTrafficCase() {
            if (!editingTrafficCaseId) return;

            const data = {
                id: editingTrafficCaseId,
                client_name: document.getElementById('adminTrafficClientName').value.trim(),
                client_phone: document.getElementById('adminTrafficClientPhone').value.trim(),
                court: document.getElementById('adminTrafficCourt').value.trim(),
                court_date: document.getElementById('adminTrafficCourtDate').value || null,
                charge: document.getElementById('adminTrafficCharge').value.trim(),
                case_number: document.getElementById('adminTrafficCaseNumber').value.trim(),
                prosecutor_offer: document.getElementById('adminTrafficOffer').value.trim(),
                disposition: document.getElementById('adminTrafficDisposition').value,
                status: document.getElementById('adminTrafficStatus').value,
                citation_issued_date: document.getElementById('adminTrafficTicketIssuedDate').value || null,
                noa_sent_date: document.getElementById('adminTrafficNoaSentDate').value || null,
                discovery: document.getElementById('adminTrafficDiscovery').checked,
                note: document.getElementById('adminTrafficNote').value.trim(),
                referral_source: document.getElementById('adminTrafficReferralSource').value.trim(),
                paid: false
            };

            try {
                const result = await apiCall('api/traffic.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                if (result.success) {
                    closeAdminTrafficModal();
                    loadAdminTrafficCases();
                } else {
                    alert(result.error || 'Error saving case');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error saving case');
            }
        }

        async function deleteAdminTrafficCase(id, clientName) {
            if (!confirm(`Delete traffic case for "${clientName}"?`)) return;

            try {
                const result = await apiCall('api/traffic.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                if (result.success) {
                    loadAdminTrafficCases();
                } else {
                    alert(result.error || 'Error deleting case');
                }
            } catch (err) {
                console.error('Error:', err);
                alert(err.message || 'Error deleting case');
            }
        }

        // ============================================
        // Performance Analytics Functions
        // ============================================

        let perfChartInstance = null;

        async function loadPerformanceData() {
            const employeeId = document.getElementById('perfEmployeeFilter').value;
            const year = document.getElementById('perfYearFilter').value;

            // Show/hide Chong analytics panel
            const chongSection = document.getElementById('chongAnalyticsSection');
            chongSection.style.display = (employeeId == 2) ? 'block' : 'none';

            if (employeeId == 2) await loadChongAnalytics(year);
            await loadPerformanceSummary(employeeId, year);
            await loadMonthlyTrend(employeeId, year);
            await loadByEmployee(year);
        }

        async function loadPerformanceSummary(employeeId, year) {
            try {
                let url = `api/performance.php?action=summary&year=${year}`;
                if (employeeId > 0) url += `&employee_id=${employeeId}`;

                const result = await apiCall(url);
                if (result.summary) {
                    const s = result.summary;
                    const mom = result.month_over_month;

                    document.getElementById('perfTotalCases').textContent = s.total_cases;
                    document.getElementById('perfTotalCommission').textContent = formatCurrency(s.valid_commission);
                    document.getElementById('perfAvgCommission').textContent = formatCurrency(s.avg_commission);

                    const changeEl = document.getElementById('perfCommissionChange');
                    if (mom && mom.change_percent !== 0) {
                        const isUp = mom.change_percent > 0;
                        changeEl.innerHTML = `<span class="${isUp ? 'up' : 'down'}">${isUp ? '+' : ''}${mom.change_percent.toFixed(1)}% vs last month</span>`;
                    } else {
                        changeEl.textContent = '';
                    }
                }
            } catch (err) {
                console.error('Error loading summary:', err);
            }
        }

        async function loadChongAnalytics(year) {
            try {
                const result = await apiCall(`api/performance.php?action=chong&year=${year}`);
                if (result.chong_analytics) {
                    const c = result.chong_analytics;

                    // Phase breakdown
                    document.getElementById('perfDemandActive').textContent = c.phase_breakdown.demand_active;
                    document.getElementById('perfLitActive').textContent = c.phase_breakdown.litigation_active;
                    document.getElementById('perfSettled').textContent = c.phase_breakdown.settled;

                    // Settlement
                    document.getElementById('perfDemandSettled').textContent = c.settlement_breakdown.demand_settled;
                    const litEl = document.getElementById('perfLitSettled');
                    litEl.textContent = c.settlement_breakdown.litigation_settled;
                    litEl.className = 'pd-val' + (c.settlement_breakdown.litigation_settled == 0 ? ' dim' : '');
                    document.getElementById('perfResolutionRate').textContent = c.settlement_breakdown.demand_resolution_rate + '%';

                    // Efficiency
                    const setMetric = (id, val) => {
                        const el = document.getElementById(id);
                        if (!val || val === '-' || val == 0) { el.textContent = '—'; el.className = 'pd-val dim'; }
                        else { el.textContent = val; el.className = 'pd-val'; }
                    };
                    setMetric('perfAvgDemandDays', c.efficiency.avg_demand_days);
                    setMetric('perfAvgLitDays', c.efficiency.avg_litigation_days);
                    setMetric('perfAvgTotalDays', c.efficiency.avg_total_days);

                    // Time management
                    const compEl = document.getElementById('perfDeadlineCompliance');
                    const compRate = c.time_management.deadline_compliance_rate;
                    compEl.textContent = compRate + '%';
                    compEl.className = 'pd-val ' + (compRate < 80 ? 'amber' : 'green');

                    const urgentEl = document.getElementById('perfUrgentCases');
                    const urgentCount = c.current_status.urgent_cases;
                    urgentEl.textContent = urgentCount;
                    urgentEl.className = 'pd-val ' + (urgentCount > 0 ? 'red' : 'dim');

                    // Commission
                    document.getElementById('perfCommTotal').textContent = formatCurrency(c.commission_breakdown.total);
                    document.getElementById('perfCommDemand').textContent = formatCurrency(c.commission_breakdown.from_demand);
                    const commLitEl = document.getElementById('perfCommLit');
                    commLitEl.textContent = formatCurrency(c.commission_breakdown.from_litigation);
                    commLitEl.className = 'pd-val ' + (c.commission_breakdown.from_litigation == 0 ? 'amber' : 'teal');
                    document.getElementById('perfActiveCases').textContent = c.current_status.active_cases;
                }
            } catch (err) {
                console.error('Error loading Chong analytics:', err);
            }
        }

        async function loadMonthlyTrend(employeeId, year) {
            try {
                let url = `api/performance.php?action=by_month&year=${year}`;
                if (employeeId > 0) url += `&employee_id=${employeeId}`;

                const result = await apiCall(url);
                if (result.by_month) {
                    renderPerfChart(result.by_month);
                }
            } catch (err) {
                console.error('Error loading monthly trend:', err);
            }
        }

        function renderPerfChart(data) {
            const ctx = document.getElementById('perfCommissionChart');
            if (!ctx) return;
            if (perfChartInstance) perfChartInstance.destroy();

            const labels = data.map(d => d.month);
            const commissions = data.map(d => parseFloat(d.commission));
            const cases = data.map(d => parseInt(d.cases_count));

            perfChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Commission ($)',
                            data: commissions,
                            backgroundColor: '#1a1a2e',
                            borderRadius: 4,
                            barPercentage: 0.6,
                            order: 2,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Cases',
                            data: cases,
                            type: 'line',
                            borderColor: '#d97706',
                            backgroundColor: 'transparent',
                            pointBackgroundColor: '#d97706',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            borderWidth: 2,
                            tension: 0.3,
                            yAxisID: 'y1',
                            order: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'start',
                            labels: { font: { family: 'Outfit', size: 11, weight: '500' }, color: '#8b8fa3', boxWidth: 12, boxHeight: 3, padding: 16 }
                        },
                        tooltip: {
                            backgroundColor: '#1a1a2e',
                            titleFont: { family: 'Outfit', size: 12, weight: '600' },
                            bodyFont: { family: 'Outfit', size: 11 },
                            cornerRadius: 6,
                            padding: 10,
                            callbacks: { label: ctx => ctx.dataset.label === 'Commission ($)' ? formatCurrency(ctx.raw) : ctx.raw + ' cases' }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 11 }, color: '#8b8fa3' } },
                        y: {
                            position: 'left',
                            grid: { color: '#f0f1f3' },
                            ticks: { font: { family: 'Outfit', size: 11 }, color: '#8b8fa3', callback: v => '$' + v.toLocaleString() }
                        },
                        y1: {
                            position: 'right',
                            grid: { display: false },
                            ticks: { font: { family: 'Outfit', size: 11 }, color: '#8b8fa3', stepSize: 2 }
                        }
                    }
                }
            });
        }

        async function loadByEmployee(year) {
            try {
                const result = await apiCall(`api/performance.php?action=by_employee&year=${year}`);
                if (result.by_employee) {
                    renderPerfEmployeeTable(result.by_employee);
                }
            } catch (err) {
                console.error('Error loading by employee:', err);
            }
        }

        function renderPerfEmployeeTable(employees) {
            const tbody = document.getElementById('perfEmployeeBody');
            if (!employees || employees.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px; color: #8b8fa3; font-size: 12px;">No data</td></tr>';
                return;
            }

            employees.sort((a, b) => parseFloat(b.total_commission) - parseFloat(a.total_commission));
            const totalCommission = employees.reduce((sum, e) => sum + parseFloat(e.total_commission || 0), 0);

            tbody.innerHTML = employees.map((e, idx) => {
                const pct = totalCommission > 0 ? ((parseFloat(e.total_commission) / totalCommission) * 100) : 0;
                const isTop = idx === 0 && parseFloat(e.total_commission) > 0;
                const isZero = parseFloat(e.total_commission) === 0;

                return `
                    <tr class="${isTop ? 'top-row' : ''}">
                        <td style="padding: 10px 14px; font-weight: 600; color: #1a1a2e; font-size: 13px;">${escapeHtml(e.display_name)}</td>
                        <td class="r" style="padding: 10px 14px; font-size: 13px;">${e.total_cases}</td>
                        <td class="r" style="padding: 10px 14px; font-size: 13px;">${e.paid_cases}</td>
                        <td class="r" style="padding: 10px 14px; font-weight: 700; ${isZero ? 'color: #c4c7d0;' : 'color: #0d9488;'} font-size: 13px;">${formatCurrency(e.total_commission)}</td>
                        <td class="r" style="padding: 10px 14px; font-size: 13px; ${isZero ? 'color: #c4c7d0;' : ''}">${formatCurrency(e.avg_commission)}</td>
                        <td class="r" style="padding: 10px 14px;">
                            <div class="spark-bar">
                                <div class="spark"><div class="spark-fill ${pct === 0 ? 'empty' : ''}" style="width: ${pct}%;"></div></div>
                                <span class="spark-pct ${pct === 0 ? 'zero' : ''}">${pct.toFixed(1)}%</span>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        // ============================================
        // Goals Functions
        // ============================================

        let goalsYearFilterInit = false;

        function initGoalsYearFilter() {
            if (goalsYearFilterInit) return;
            goalsYearFilterInit = true;
            const sel = document.getElementById('goalsYearFilter');
            const currentYear = new Date().getFullYear();
            for (let y = currentYear; y >= currentYear - 3; y--) {
                const opt = document.createElement('option');
                opt.value = y;
                opt.textContent = y;
                sel.appendChild(opt);
            }
        }

        async function loadGoalsData() {
            const year = document.getElementById('goalsYearFilter').value || new Date().getFullYear();
            try {
                const result = await apiCall(`api/goals.php?action=summary&year=${year}`);
                if (result.csrf_token) csrfToken = result.csrf_token;
                const employees = result.employees || [];
                renderGoalsTable(employees, year);
                updateGoalsHeroCards(employees);
            } catch (err) {
                console.error('Error loading goals:', err);
                document.getElementById('goalsTableBody').innerHTML = '<tr><td colspan="7" style="text-align:center; padding:40px; color:#dc2626;">Failed to load goals data</td></tr>';
            }
        }

        function getOnPacePercent(year) {
            const now = new Date();
            const currentYear = now.getFullYear();
            if (parseInt(year) < currentYear) return 100;
            if (parseInt(year) > currentYear) return 0;
            const monthsPassed = now.getMonth() + 1;
            return (monthsPassed / 12) * 100;
        }

        function getPaceColor(actualPct, expectedPct) {
            if (expectedPct === 0) return '#8b8fa3';
            const ratio = actualPct / expectedPct;
            if (ratio >= 0.85) return '#0d9488';
            if (ratio >= 0.6) return '#d97706';
            return '#dc2626';
        }

        function getPaceLabel(actualPct, expectedPct) {
            if (expectedPct === 0) return '-';
            const ratio = actualPct / expectedPct;
            if (ratio >= 0.85) return 'On Pace';
            if (ratio >= 0.6) return 'Behind';
            return 'Far Behind';
        }

        function renderGoalsTable(employees, year) {
            const tbody = document.getElementById('goalsTableBody');
            const expectedPct = getOnPacePercent(year);

            if (!employees.length) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:40px; color:#8b8fa3;">No employees found</td></tr>';
                return;
            }

            tbody.innerHTML = employees.map(emp => {
                const casesPct = Math.min(100, parseFloat(emp.cases_percent) || 0);
                const feePct = Math.min(100, parseFloat(emp.legal_fee_percent) || 0);
                const avgPct = (casesPct + feePct) / 2;
                const paceColor = getPaceColor(avgPct, expectedPct);
                const paceLabel = getPaceLabel(avgPct, expectedPct);

                const feeActual = parseFloat(emp.actual_legal_fee) || 0;
                const feeTarget = parseFloat(emp.target_legal_fee) || 500000;

                const formatFee = (v) => v >= 1000 ? '$' + (v/1000).toFixed(0) + 'K' : '$' + v.toFixed(0);

                return `<tr>
                    <td style="font-weight:600; font-size:12px;">${emp.display_name}</td>
                    <td class="r" style="font-size:12px;">${emp.actual_cases}/${emp.target_cases}</td>
                    <td>
                        <div class="spark-bar">
                            <div class="spark" style="width:70px;">
                                <div class="spark-fill" style="width:${casesPct}%; background:${casesPct >= 75 ? '#0d9488' : casesPct >= 50 ? '#d97706' : '#e2e4ea'};"></div>
                            </div>
                            <span class="spark-pct ${casesPct === 0 ? 'zero' : ''}">${casesPct.toFixed(0)}%</span>
                        </div>
                    </td>
                    <td class="r" style="font-size:12px;">${formatFee(feeActual)}/${formatFee(feeTarget)}</td>
                    <td>
                        <div class="spark-bar">
                            <div class="spark" style="width:70px;">
                                <div class="spark-fill" style="width:${feePct}%; background:${feePct >= 75 ? '#0d9488' : feePct >= 50 ? '#d97706' : '#e2e4ea'};"></div>
                            </div>
                            <span class="spark-pct ${feePct === 0 ? 'zero' : ''}">${feePct.toFixed(0)}%</span>
                        </div>
                    </td>
                    <td class="c"><span style="font-size:11px; font-weight:600; color:${paceColor};">${paceLabel}</span></td>
                    <td class="c">
                        <button onclick="openEditGoalModal(${emp.id}, '${emp.display_name.replace(/'/g, "\\'")}', {target_cases:${emp.target_cases}, target_legal_fee:${emp.target_legal_fee}, notes:'${(emp.goal_notes||'').replace(/'/g, "\\'").replace(/\n/g, ' ')}'})" class="act-link" style="padding:3px 8px; font-size:10px;">Edit</button>
                    </td>
                </tr>`;
            }).join('');
        }

        function updateGoalsHeroCards(employees) {
            document.getElementById('goalsHeroCount').textContent = employees.length;

            if (employees.length > 0) {
                const avgCases = employees.reduce((s, e) => s + (parseFloat(e.cases_percent) || 0), 0) / employees.length;
                const avgFee = employees.reduce((s, e) => s + (parseFloat(e.legal_fee_percent) || 0), 0) / employees.length;
                document.getElementById('goalsHeroCases').textContent = avgCases.toFixed(1) + '%';
                document.getElementById('goalsHeroFee').textContent = avgFee.toFixed(1) + '%';
            } else {
                document.getElementById('goalsHeroCases').textContent = '0%';
                document.getElementById('goalsHeroFee').textContent = '0%';
            }
        }

        function openEditGoalModal(userId, displayName, goal) {
            document.getElementById('goalEditUserId').value = userId;
            document.getElementById('editGoalTitle').textContent = 'Edit Goal - ' + displayName;
            document.getElementById('goalEditYear').value = document.getElementById('goalsYearFilter').value || new Date().getFullYear();
            document.getElementById('goalEditCases').value = goal.target_cases || 50;
            document.getElementById('goalEditFee').value = goal.target_legal_fee || 500000;
            document.getElementById('goalEditNotes').value = goal.notes || '';
            document.getElementById('editGoalModal').style.display = 'flex';
        }

        function closeGoalModal() {
            document.getElementById('editGoalModal').style.display = 'none';
        }

        async function saveGoal() {
            const data = {
                user_id: parseInt(document.getElementById('goalEditUserId').value),
                year: parseInt(document.getElementById('goalEditYear').value),
                target_cases: parseInt(document.getElementById('goalEditCases').value),
                target_legal_fee: parseFloat(document.getElementById('goalEditFee').value),
                notes: document.getElementById('goalEditNotes').value.trim()
            };

            try {
                const result = await apiCall('api/goals.php', {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
                if (result.csrf_token) csrfToken = result.csrf_token;
                closeGoalModal();
                loadGoalsData();
            } catch (err) {
                alert('Failed to save goal: ' + (err.message || err));
            }
        }

    </script>
</body>
</html>
