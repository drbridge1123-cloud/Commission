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
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/steel-minimal.css">
    <style>
        /* INK Design System - Quick Stats */
        .qs-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        @media (max-width: 1200px) { .qs-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .qs-grid { grid-template-columns: 1fr; } }
        .qs-card { background: #fff; border-radius: 8px; padding: 14px 16px; border: 1px solid #e2e4ea; display: flex; justify-content: space-between; align-items: center; transition: all 0.15s; }
        .qs-card:hover { background: #f8f9fa; border-color: #1a1a2e; }
        .qs-label { font-size: 11px; color: #8b8fa3; font-weight: 500; text-transform: uppercase; letter-spacing: 0.3px; }
        .qs-val { font-size: 20px; font-weight: 700; font-variant-numeric: tabular-nums; color: #1a1a2e; }
        .qs-val.green { color: #0d9488; }
        .qs-val.amber { color: #d97706; }
        .qs-val.blue { color: #3b82f6; }
        .qs-val.red { color: #dc2626; }
        .qs-val.teal { color: #0d9488; }
        .qs-val.dim { color: #c4c7d0; }

        /* INK Design System - Filter Chips */
        .f-row { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 16px; }
        .f-chip { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; cursor: pointer; border: 1px solid #e2e4ea; background: #fff; color: #5c5f73; transition: all 0.12s; user-select: none; }
        .f-chip:hover { background: #f5f5f7; }
        .f-chip.active { background: #1a1a2e; color: #fff; border-color: #1a1a2e; }
        .f-select { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; border: 1px solid #e2e4ea; background: #fff; color: #5c5f73; cursor: pointer; }
        .f-select:focus { outline: none; border-color: #1a1a2e; }

        /* INK Design System - Table Container */
        .tbl-container { background: #fff; border: 1px solid #e2e4ea; border-radius: 10px; overflow: hidden; }
        .tbl-header { padding: 12px 16px; border-bottom: 1px solid #e2e4ea; display: flex; justify-content: space-between; align-items: center; }
        .tbl-title { font-size: 14px; font-weight: 600; color: #1a1a2e; }
        .tbl-actions { display: flex; gap: 8px; align-items: center; }
        .tbl-footer { padding: 12px 16px; border-top: 1px solid #e2e4ea; display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #8b8fa3; }
        .tbl-footer .ft-val { font-weight: 600; color: #1a1a2e; }

        /* INK Design System - Search Box */
        .ink-search { padding: 6px 12px; border: 1px solid #e2e4ea; border-radius: 6px; font-size: 13px; width: 200px; }
        .ink-search:focus { outline: none; border-color: #1a1a2e; }

        /* INK Design System - Buttons */
        .ink-btn { padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: 1px solid transparent; transition: all 0.12s; }
        .ink-btn-primary { background: #1a1a2e; color: #fff; }
        .ink-btn-primary:hover { background: #2d2d4a; }
        .ink-btn-secondary { background: #fff; border-color: #e2e4ea; color: #5c5f73; }
        .ink-btn-secondary:hover { background: #f5f5f7; border-color: #c4c7d0; }
        .ink-btn-success { background: #0d9488; color: #fff; }
        .ink-btn-success:hover { background: #0f766e; }
        .ink-btn-warning { background: #d97706; color: #fff; }
        .ink-btn-warning:hover { background: #b45309; }

        /* INK Icon Buttons */
        .ink-icon-btn { width: 28px; height: 28px; padding: 0; border: 1px solid #e2e4ea; border-radius: 6px; background: #fff; color: #5c5f73; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.12s; }
        .ink-icon-btn:hover { background: #f5f5f7; border-color: #c4c7d0; color: #1a1a2e; }
        .ink-icon-btn-danger { color: #dc2626; }
        .ink-icon-btn-danger:hover { background: #fef2f2; border-color: #fca5a5; color: #b91c1c; }

        /* INK Design System - Status Badges */
        .ink-badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
        .ink-badge.unpaid { background: #fef3c7; color: #b45309; }
        .ink-badge.paid { background: #d1fae5; color: #065f46; }
        .ink-badge.in_progress { background: #dbeafe; color: #1d4ed8; }
        .ink-badge.pending { background: #fef3c7; color: #b45309; }
        .ink-badge.settled { background: #d1fae5; color: #065f46; }

        /* Clickable Row */
        .clickable-row { cursor: pointer; transition: background 0.15s; }
        .clickable-row:hover { background: #eff6ff !important; }
        .clickable-row:hover td:first-child { position: relative; }
        .clickable-row:hover td:first-child::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: #2563eb; }
        .action-cell { cursor: default; }

        /* Modal Styles */
        .modal-overlay {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            z-index: 9999 !important;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 40px;
            overflow-y: auto;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
        }
        .modal-overlay.show {
            display: flex !important;
        }
        .modal-content {
            max-height: 85vh !important;
            overflow-y: auto !important;
            overflow-x: hidden;
            margin: auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            min-width: 520px;
            max-width: 650px;
            scrollbar-width: thin;
            scrollbar-color: #c4c7d0 transparent;
        }
        .modal-content::-webkit-scrollbar { width: 6px; }
        .modal-content::-webkit-scrollbar-track { background: transparent; }
        .modal-content::-webkit-scrollbar-thumb { background: #c4c7d0; border-radius: 3px; }
        .modal-content::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
        .modal-header {
            padding: 18px 24px !important;
            background: #1a1a2e !important;
            border-bottom: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 12px 12px 0 0;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .modal-header h2, .modal-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #fff !important;
            margin: 0;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            color: rgba(255,255,255,0.7);
            cursor: pointer;
            padding: 0;
            line-height: 1;
            transition: color 0.2s;
        }
        .modal-close:hover {
            color: #fff;
        }
        .modal-content form {
            padding: 28px 32px !important;
        }
        .modal-footer {
            padding: 20px 32px 24px !important;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #f9fafb;
            border-radius: 0 0 16px 16px;
            margin-top: 0 !important;
        }

        /* Phase Badges */
        .badge-demand { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .badge-litigation { background: #fed7aa; color: #9a3412; border: 1px solid #fdba74; }
        .badge-settled { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-demand-settled { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-litigation-settled { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }

        /* Deadline Colors */
        .deadline-safe { color: #059669; }
        .deadline-warning { color: #d97706; font-weight: 600; }
        .deadline-critical { color: #dc2626; font-weight: 700; }
        .deadline-overdue { color: #991b1b; font-weight: 700; background: #fee2e2; padding: 2px 8px; border-radius: 4px; }

        /* Urgent Alert Bar */
        .urgent-bar { padding: 12px 20px; border-radius: 8px; margin-bottom: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .urgent-bar.green { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .urgent-bar.yellow { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .urgent-bar.red { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .urgent-bar.critical { background: #991b1b; color: white; }

        /* Stats Grid */
        .stats-grid-6 { display: grid; grid-template-columns: repeat(6, 1fr); gap: 16px; margin-bottom: 24px; }
        @media (max-width: 1400px) { .stats-grid-6 { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 900px) { .stats-grid-6 { grid-template-columns: repeat(2, 1fr); } }

        /* Urgent Cases Section */
        .urgent-section { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 24px; border: 1px solid #e5e7eb; }
        .urgent-section h3 { font-size: 16px; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .urgent-case-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #fef2f2; border-radius: 8px; margin-bottom: 8px; border-left: 4px solid #dc2626; }
        .urgent-case-item.warning { background: #fffbeb; border-left-color: #d97706; }
        .urgent-case-item .case-info { flex: 1; }
        .urgent-case-item .case-number { font-weight: 600; color: #1f2937; }
        .urgent-case-item .client-name { color: #6b7280; font-size: 14px; }
        .urgent-case-item .days-left { font-weight: 700; }

        /* Urgent Table Styles */
        .urgent-table tbody tr.urgent-row-overdue { background: #fef2f2 !important; }
        .urgent-table tbody tr.urgent-row-overdue:hover { background: #fee2e2 !important; }
        .urgent-table tbody tr.urgent-row-warning { background: #fffbeb !important; }
        .urgent-table tbody tr.urgent-row-warning:hover { background: #fef3c7 !important; }

        /* Demand Table Row Highlighting */
        tr.row-overdue { background: #fef2f2 !important; }
        tr.row-overdue:hover { background: #fee2e2 !important; }
        tr.row-critical { background: #fffbeb !important; }
        tr.row-critical:hover { background: #fef3c7 !important; }
        tr.selected-row { background: #dbeafe !important; border-left: 3px solid #1d4ed8 !important; }
        tr.selected-row:hover { background: #bfdbfe !important; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; white-space: nowrap; }
        .badge.deadline-overdue { background: #dc2626; color: white; }
        .badge.deadline-critical { background: #f59e0b; color: white; }
        .badge.status-in_progress { background: #3b82f6; color: white; }
        .badge.status-unpaid { background: #f59e0b; color: white; }
        .badge.status-paid { background: #10b981; color: white; }
        .badge.status-rejected { background: #ef4444; color: white; }
        .badge-success { background: #10b981; color: white; }
        .badge-warning { background: #f59e0b; color: white; }

        /* Toast Notifications */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        }
        .toast-notification button {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            opacity: 0.7;
        }
        .toast-notification button:hover { opacity: 1; }
        .toast-success { background: #10b981; color: white; }
        .toast-error { background: #ef4444; color: white; }
        .toast-info { background: #3b82f6; color: white; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Action Buttons - Legacy (kept for compatibility) */
        /* See .act-btn for new Teal + Indigo button system */

        /* Fix for action buttons in table cells - override admin.css */
        table.excel-table tbody td:last-child {
            overflow: visible !important;
            white-space: nowrap !important;
            position: relative;
            z-index: 1;
        }
        /* Ensure header stays above body cells */
        table.excel-table thead th {
            z-index: 20 !important;
        }
        table.excel-table tbody tr { cursor: default !important; }
        table.excel-table tbody td { pointer-events: auto !important; }

        /* Commission Type Badge */
        .commission-type { font-size: 11px; padding: 2px 6px; border-radius: 4px; display: inline-block; }
        .commission-type.demand { background: #dbeafe; color: #1e40af; }
        .commission-type.lit33 { background: #fef3c7; color: #92400e; }
        .commission-type.lit40 { background: #fed7aa; color: #9a3412; }
        .commission-type.variable { background: #e5e7eb; color: #374151; }

        /* Traffic Count Badge (for f-chip) */
        .traffic-count-badge {
            padding: 2px 6px;
            font-size: 10px;
            font-weight: 700;
            border-radius: 10px;
            background: rgba(0,0,0,0.1);
            margin-left: 4px;
        }
        .f-chip.active .traffic-count-badge {
            background: rgba(255,255,255,0.25);
        }

        /* Form Groups */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .form-row.full { grid-template-columns: 1fr; }
        .form-group label { display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 4px; }
        .form-group input, .form-group select { width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #1565c0; box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.1); }
        .form-group .calculated { background: #f3f4f6; color: #374151; font-weight: 600; }
        .form-group .help-text { font-size: 12px; color: #6b7280; margin-top: 4px; }

        /* Section Divider */
        .form-section { border-top: 1px solid #e5e7eb; padding-top: 16px; margin-top: 16px; }
        .form-section-title { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 12px; }

        /* Resolution Type Info */
        .resolution-info { background: #f3f4f6; padding: 12px; border-radius: 8px; margin-top: 12px; font-size: 13px; }
        .resolution-info .label { color: #6b7280; }
        .resolution-info .value { font-weight: 600; color: #1f2937; }

        /* Sortable Table Headers - INK Dark Style */
        .sortable {
            cursor: pointer;
            user-select: none;
            position: relative;
            padding-right: 18px !important;
        }
        .sortable:hover {
            background: #2d2d4a !important;
        }
        .sortable:hover .th-content,
        .th-content:hover {
            background: transparent !important;
            color: #fff !important;
        }
        .sortable .sort-icon {
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 2px;
            opacity: 0.4;
        }
        .sortable:hover .sort-icon { opacity: 0.7; }
        .sortable .sort-icon::before,
        .sortable .sort-icon::after {
            content: '';
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
        }
        .sortable .sort-icon::before {
            border-bottom: 5px solid currentColor;
        }
        .sortable .sort-icon::after {
            border-top: 5px solid currentColor;
        }
        .sortable.asc .sort-icon { opacity: 1; }
        .sortable.asc .sort-icon::before { border-bottom-color: #60a5fa; }
        .sortable.asc .sort-icon::after { opacity: 0.3; }
        .sortable.desc .sort-icon { opacity: 1; }
        .sortable.desc .sort-icon::before { opacity: 0.3; }
        .sortable.desc .sort-icon::after { border-top-color: #60a5fa; }

        /* Messages Styles */
        .messages-list {
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }
        .message-item {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: background-color 0.15s;
            gap: 10px;
        }
        .message-item:hover { background-color: #f9fafb; }
        .message-item:last-child { border-bottom: none; }
        .message-item.unread { background-color: #eff6ff; }
        .message-item.unread:hover { background-color: #dbeafe; }
        .message-indicator {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #3b82f6;
            flex-shrink: 0;
        }
        .message-item:not(.unread) .message-indicator { background: transparent; }
        .message-content { flex: 1; min-width: 0; display: flex; align-items: center; gap: 12px; }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex: 1;
            min-width: 0;
        }
        .message-from {
            font-weight: 600;
            color: #1f2937;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .message-date {
            font-size: 11px;
            color: #9ca3af;
            flex-shrink: 0;
        }
        .message-subject {
            font-size: 13px;
            color: #374151;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .message-preview {
            font-size: 12px;
            color: #6b7280;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .message-direction {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        .message-direction.received { background: #dbeafe; color: #1d4ed8; }
        .message-direction.sent { background: #dcfce7; color: #15803d; }
        .message-detail { padding: 24px 32px; }
        .message-meta {
            display: flex;
            justify-content: space-between;
            padding-bottom: 16px;
            margin-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .message-body {
            font-size: 14px;
            line-height: 1.7;
            color: #374151;
            white-space: pre-wrap;
        }
        .empty-messages {
            padding: 60px 40px;
            text-align: center;
            color: #9ca3af;
        }
        .empty-messages svg {
            width: 48px;
            height: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        /* Deadline Extension Styles */
        .deadline-extension-section {
            background: #fefce8;
            border: 1px solid #fde047;
            border-radius: 8px;
            padding: 16px;
            margin: 16px 0;
        }
        .pending-extension-alert {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .pending-extension-alert .alert-icon {
            font-size: 18px;
        }
        .pending-extension-alert .alert-text {
            color: #92400e;
            font-weight: 500;
            font-size: 14px;
        }
        .btn-outline {
            background: transparent;
            border: 1px solid #d1d5db;
            color: #374151;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-outline:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }
        .btn-warning {
            background: #f59e0b;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-warning:hover {
            background: #d97706;
        }

        /* ── INK COMPACT DESIGN STYLES ── */
        /* Alert Overdue */
        .alert-overdue {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 600;
            background: #1a1a2e;
            color: #fca5a5;
            border: 1px solid #2d2d4a;
        }
        .alert-overdue svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            color: #f87171;
        }
        .alert-overdue .count {
            background: #dc2626;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 8px;
            margin-left: 2px;
        }

        /* Quick Stats Grid */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }
        @media (max-width: 1200px) { .quick-stats { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .quick-stats { grid-template-columns: 1fr; } }

        /* Filters Row */
        .filters {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
        }
        .f-spacer { flex: 1; }
        .f-search {
            padding: 5px 12px;
            border: 1px solid #e2e4ea;
            border-radius: 20px;
            font-size: 12px;
            width: 180px;
            max-width: 180px;
            flex-shrink: 0;
            background: #fff;
            font-family: inherit;
            color: #5c5f73;
            outline: none;
        }
        .f-search:focus { border-color: #1a1a2e; }
        .f-btn {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            background: #1a1a2e;
            color: #fff;
            font-family: inherit;
            transition: all 0.12s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .f-btn:hover { background: #2d2d4a; }

        /* Table Styles - Ink Compact */
        .tbl {
            width: calc(100% + 2px);
            margin-left: -1px;
            border-collapse: collapse;
        }
        .tbl thead { background: #1a1a2e; }
        .tbl thead th {
            padding: 10px 14px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: #fff;
            background: #1a1a2e;
            text-align: left;
            white-space: nowrap;
        }
        .tbl thead th.r { text-align: right; }
        .tbl thead th.c { text-align: center; }
        .tbl thead th .th-sort {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            cursor: pointer;
            padding: 2px 4px;
            margin: -2px -4px;
            border-radius: 3px;
            transition: background 0.1s;
        }
        .tbl thead th .th-sort:hover { background: rgba(255,255,255,0.08); }
        .tbl thead th .sort-arrow {
            font-size: 8px;
            opacity: 0.4;
        }
        .tbl tbody tr {
            border-bottom: 1px solid #f0f1f3;
            transition: background 0.08s;
        }
        .tbl tbody tr:hover { background: #f5f8ff; }
        .tbl tbody tr.row-overdue {
            background: #fef2f2;
        }
        .tbl tbody tr.row-overdue:hover {
            background: #fee2e2;
        }
        .tbl tbody tr.row-urgent {
            background: #fffbeb;
        }
        .tbl tbody tr.row-urgent:hover {
            background: #fef3c7;
        }
        .tbl-compact thead th { padding: 8px 6px; font-size: 9px; }
        .tbl-compact tbody td { padding: 8px 6px; font-size: 12px; }
        .tbl tbody td {
            padding: 10px 14px;
            font-size: 13px;
            color: #3d3f4e;
            white-space: nowrap;
            vertical-align: middle;
        }
        .tbl tbody td.case-num {
            font-weight: 600;
            color: #1a1a2e;
        }
        .tbl tbody td.mute { color: #c4c7d0; }

        /* Days Left Pills */
        .days-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .days-pill.overdue {
            background: #dc2626;
            color: #fff;
        }
        .days-pill.critical {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .days-pill.warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .days-pill.safe {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        /* Status Badges - Ink Compact */
        .stat-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .stat-badge::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
        }
        .stat-badge.unpaid { background: #fef3c7; color: #b45309; }
        .stat-badge.unpaid::before { background: #f59e0b; }
        .stat-badge.in_progress { background: #dbeafe; color: #1d4ed8; }
        .stat-badge.in_progress::before { background: #3b82f6; }
        .stat-badge.paid { background: #d1fae5; color: #065f46; }
        .stat-badge.paid::before { background: #22c55e; }

        /* Action Buttons - Ink Compact */
        .action-group {
            display: flex;
            gap: 4px;
        }
        .action-group.center { justify-content: center; }

        .act-btn {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-family: inherit;
            transition: all 0.12s;
        }
        .act-btn.edit {
            background: transparent;
            color: #5c5f73;
            border: 1px solid #e2e4ea;
        }
        .act-btn.edit:hover { background: #f5f5f7; }
        .act-btn.settle {
            background: #0d9488;
            color: #fff;
        }
        .act-btn.settle:hover { background: #0f766e; }
        .act-btn.to-lit {
            background: #6366f1;
            color: #fff;
        }
        .act-btn.to-lit:hover { background: #4f46e5; }

        /* Table Footer */
        .tbl-foot {
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            border-top: 1px solid #e2e4ea;
            font-size: 12px;
        }
        .tbl-foot .left { color: #8b8fa3; }

        /* Row Indicator */
        .row-indicator {
            width: 3px;
            padding: 0 !important;
        }
        .row-indicator.overdue { background: #dc2626; }
        .row-indicator.urgent { background: #f59e0b; }
        .row-indicator.normal { background: transparent; }
        .row-indicator.safe { background: #22c55e; }

        /* ══════════════════════════════════════════════════════════════
           NOTIFICATIONS - Ink Compact Design
           ══════════════════════════════════════════════════════════════ */

        /* Quick Stats */
        .notif-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }

        /* Filters */
        .notif-filters {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .f-btn-ghost {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid #e2e4ea;
            background: #fff;
            color: #5c5f73;
            font-family: inherit;
            transition: all 0.12s;
        }
        .f-btn-ghost:hover { background: #f5f5f7; }

        .f-btn-primary {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            background: #1a1a2e;
            color: #fff;
            font-family: inherit;
            transition: all 0.12s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .f-btn-primary:hover { background: #2d2d4a; }

        /* Unread row styling */
        .tbl tbody tr.unread { background: #fafbff; }
        .tbl tbody tr.unread:hover { background: #eff6ff; }
        .tbl tbody tr.unread td { font-weight: 500; color: #1a1a2e; }

        /* Unread dot */
        .unread-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #3b82f6;
        }

        /* Direction badges */
        .dir-badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .dir-badge.sent { background: #e2e4ea; color: #5c5f73; }
        .dir-badge.received { background: #dbeafe; color: #1d4ed8; }
        .dir-badge.system-approved { background: #d1fae5; color: #065f46; }
        .dir-badge.system-rejected { background: #fee2e2; color: #991b1b; }

        /* Subject column */
        .td-subject {
            max-width: 400px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .tbl tbody tr.unread .td-subject { font-weight: 600; color: #1a1a2e; }

        /* Time column */
        .td-time { color: #8b8fa3; font-size: 12px; }
        .tbl tbody tr.unread .td-time { color: #3b82f6; font-weight: 600; }

        /* Action buttons */
        .act-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 1px solid #e2e4ea;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.1s;
            color: #8b8fa3;
        }
        .act-icon:hover { background: #f0f1f3; color: #1a1a2e; }
        .act-icon.danger { border-color: #fecaca; color: #dc2626; }
        .act-icon.danger:hover { background: #fef2f2; }
        .act-icon svg { width: 14px; height: 14px; }

        .action-group { display: flex; gap: 4px; justify-content: center; }

        /* Empty state */
        .notif-empty { padding: 48px 24px; text-align: center; }
        .notif-empty-icon {
            width: 48px; height: 48px;
            background: #f0f1f3;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }
        .notif-empty-icon svg { width: 24px; height: 24px; color: #c4c7d0; }
        .notif-empty-title { font-size: 14px; font-weight: 600; color: #3d3f4e; margin-bottom: 4px; }
        .notif-empty-desc { font-size: 12px; color: #8b8fa3; }

        /* Modal - Ink style */
        .modal-body-ink { padding: 20px; }
        .msg-detail-row { display: flex; gap: 8px; margin-bottom: 12px; align-items: baseline; }
        .msg-detail-label {
            font-size: 11px; font-weight: 600; color: #8b8fa3;
            text-transform: uppercase; letter-spacing: 0.3px; min-width: 60px;
        }
        .msg-detail-value { font-size: 13px; font-weight: 600; color: #1a1a2e; }
        .msg-detail-body {
            background: #f8f9fa;
            padding: 14px 16px;
            border-radius: 8px;
            border: 1px solid #e2e4ea;
            white-space: pre-wrap;
            line-height: 1.6;
            font-size: 13px;
            color: #3d3f4e;
            margin-top: 16px;
            margin-bottom: 20px;
            max-height: 300px;
            overflow-y: auto;
        }
        .modal-actions-ink {
            display: flex;
            gap: 6px;
            padding-top: 16px;
            border-top: 1px solid #e2e4ea;
        }
        .act-btn-primary {
            padding: 7px 16px; background: #1a1a2e; color: #fff;
            border: none; border-radius: 6px; font-size: 12px;
            font-weight: 600; cursor: pointer; font-family: inherit;
        }
        .act-btn-primary:hover { background: #2d2d4a; }
        .act-btn-danger {
            padding: 7px 16px; background: #dc2626; color: #fff;
            border: none; border-radius: 6px; font-size: 12px;
            font-weight: 600; cursor: pointer; font-family: inherit;
        }
        .act-btn-danger:hover { background: #b91c1c; }
        .act-btn-secondary {
            padding: 7px 16px; background: #fff; color: #5c5f73;
            border: 1px solid #e2e4ea; border-radius: 6px; font-size: 12px;
            font-weight: 600; cursor: pointer; font-family: inherit;
        }
        .act-btn-secondary:hover { background: #f5f5f7; }

        /* Compose form */
        .compose-label {
            font-size: 11px; font-weight: 600; color: #8b8fa3;
            text-transform: uppercase; letter-spacing: 0.3px;
            margin-bottom: 6px; display: block;
        }
        .compose-input {
            padding: 8px 12px;
            border: 1px solid #e2e4ea;
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
            color: #1a1a2e;
            width: 100%;
            outline: none;
            box-sizing: border-box;
        }
        .compose-input:focus {
            border-color: #1a1a2e;
            box-shadow: 0 0 0 2px rgba(26,26,46,0.08);
        }

        /* ══════════════════════════════════════════════════════════════
           ACTION BUTTONS - Teal + Indigo Color Scheme
           ══════════════════════════════════════════════════════════════ */

        /* Base button style */
        .act-btn {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-family: inherit;
            transition: all 0.12s;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .act-btn:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Edit — Ghost Outline */
        .act-btn.edit {
            background: transparent;
            color: #5c5f73;
            border: 1px solid #e2e4ea;
        }
        .act-btn.edit:hover { background: #f5f5f7; border-color: #d4d4d8; }

        /* Settle — Teal */
        .act-btn.settle { background: #0d9488; color: #fff; }
        .act-btn.settle:hover { background: #0f766e; }

        /* To Lit — Indigo */
        .act-btn.to-lit { background: #6366f1; color: #fff; }
        .act-btn.to-lit:hover { background: #4f46e5; }

        /* Mark Paid — Green */
        .act-btn.mark-paid { background: #16a34a; color: #fff; }
        .act-btn.mark-paid:hover { background: #15803d; }

        /* Approve — Teal */
        .act-btn.approve { background: #0d9488; color: #fff; }
        .act-btn.approve:hover { background: #0f766e; }

        /* Reject / Delete / Danger — Red */
        .act-btn.reject, .act-btn.delete, .act-btn.danger { background: #dc2626; color: #fff; }
        .act-btn.reject:hover, .act-btn.delete:hover, .act-btn.danger:hover { background: #b91c1c; }

        /* View / Detail — Dark */
        .act-btn.view { background: #1a1a2e; color: #fff; }
        .act-btn.view:hover { background: #2d2d4a; }

        /* Cancel / Close — Secondary */
        .act-btn.cancel, .act-btn.secondary {
            background: #fff;
            color: #5c5f73;
            border: 1px solid #e2e4ea;
        }
        .act-btn.cancel:hover, .act-btn.secondary:hover { background: #f5f5f7; }

        /* Icon-only button variants */
        .act-icon.edit {
            background: transparent;
            color: #8b8fa3;
            border: 1px solid #e2e4ea;
        }
        .act-icon.edit:hover { background: #f5f5f7; color: #5c5f73; }

        .act-icon.settle { background: #0d9488; color: #fff; border: none; }
        .act-icon.settle:hover { background: #0f766e; }

        .act-icon.to-lit { background: #6366f1; color: #fff; border: none; }
        .act-icon.to-lit:hover { background: #4f46e5; }
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
        <div id="content-dashboard" class="tab-content">
            <!-- Quick Stats -->
            <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 16px; margin-bottom: 24px;">
                <div class="qs-card">
                    <div><div class="qs-label">Total Active</div><div class="qs-val" id="statTotalActive">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Demand Cases</div><div class="qs-val blue" id="statDemand">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Litigation Cases</div><div class="qs-val amber" id="statLitigation">0</div></div>
                </div>
                <div class="qs-card" style="border-left: 3px solid #dc2626;">
                    <div><div class="qs-label">Past Due</div><div class="qs-val red" id="statOverdue">0</div></div>
                </div>
                <div class="qs-card" style="border-left: 3px solid #f59e0b;">
                    <div><div class="qs-label">Due in 2 Weeks</div><div class="qs-val amber" id="statDue2Weeks">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">This Month</div><div class="qs-val teal" id="statMonthCommission">$0</div></div>
                </div>
            </div>

            <!-- Urgent Cases Section -->
            <div class="tbl-container" id="urgentSection" style="margin-bottom: 24px;">
                <div class="tbl-header">
                    <span class="tbl-title"><span style="color: #dc2626; margin-right: 8px;">⚠</span>Cases Needing Attention</span>
                    <button class="f-btn" data-action="new-demand">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Demand Case
                    </button>
                </div>
                <div id="urgentCasesList" style="padding: 16px;">Loading...</div>
            </div>
        </div>

        <!-- Demand Cases Tab -->
        <div id="content-demand" class="tab-content hidden">
            <!-- Overdue Alert -->
            <div id="demandOverdueAlert" class="alert-overdue" style="display:none;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><span class="count" id="overdueCount">0</span> case overdue — Immediate action required</span>
            </div>

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="qs-card" onclick="clickDemandStat('all')" style="cursor:pointer;">
                    <span class="qs-label">Total Demand</span>
                    <span class="qs-val" id="demandStatTotal">0</span>
                </div>
                <div class="qs-card" onclick="clickDemandStat('due2weeks')" style="cursor:pointer;">
                    <span class="qs-label">Due in 2 Weeks</span>
                    <span class="qs-val amber" id="demandStatDue2Weeks">0</span>
                </div>
                <div class="qs-card" onclick="clickDemandStat('overdue')" style="cursor:pointer;">
                    <span class="qs-label">Overdue</span>
                    <span class="qs-val red" id="demandStatOverdue">0</span>
                </div>
                <div class="qs-card" id="demandStageCard">
                    <span class="qs-label">Stage</span>
                    <span class="qs-val" id="demandStatStage" style="font-size: 14px; color: #8b8fa3;">Select a case</span>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <span class="f-chip active" data-filter="all" onclick="setDemandFilter('all', this)">All</span>
                <span class="f-chip" data-filter="due2weeks" onclick="setDemandFilter('due2weeks', this)" style="background:#fef3c7;color:#b45309;border-color:#fde68a;">Due in 2 Weeks</span>
                <span class="f-chip" data-filter="overdue" onclick="setDemandFilter('overdue', this)" style="background:#fef2f2;color:#b91c1c;border-color:#fecaca;">Overdue</span>
                <div class="f-spacer"></div>
                <input class="f-search" type="text" id="demandSearch" placeholder="Search..." onkeyup="filterDemandCases()">
                <button class="f-btn" data-action="new-demand">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Demand Case
                </button>
            </div>

            <!-- Table -->
            <div class="tbl-container">
                <table class="tbl" id="demandTable">
                    <thead>
                        <tr>
                            <th style="width:0;padding:0;border:none;"></th>
                            <th><span class="th-sort" onclick="sortDemandCases('case_number')">Case # <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortDemandCases('client_name')">Client Name <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortDemandCases('case_type')">Case Type <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortDemandCases('stage')">Stage <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortDemandCases('assigned_date')">Assigned <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortDemandCases('demand_deadline')">Deadline <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortDemandCases('days_left')">Days Left <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortDemandCases('status')">Status <span class="sort-arrow">▼</span></span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="demandTableBody">
                        <tr><td colspan="10" style="text-align:center; padding: 40px; color: #8b8fa3;">Loading...</td></tr>
                    </tbody>
                </table>
                <div class="tbl-foot">
                    <span class="left" id="demandFooterLeft">0 demand cases</span>
                    <span class="left" id="demandFooterRight">Due in 2 Weeks: 0 · Overdue: 0</span>
                </div>
            </div>
        </div>

        <!-- Litigation Cases Tab -->
        <div id="content-litigation" class="tab-content hidden">
            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="qs-card">
                    <span class="qs-label">Total Litigation</span>
                    <span class="qs-val" id="litStatTotal">0</span>
                </div>
                <div class="qs-card">
                    <span class="qs-label">Active Cases</span>
                    <span class="qs-val blue" id="litStatActive">0</span>
                </div>
                <div class="qs-card">
                    <span class="qs-label">Settled</span>
                    <span class="qs-val green" id="litStatSettled">0</span>
                </div>
                <div class="qs-card">
                    <span class="qs-label">Avg Duration</span>
                    <span class="qs-val amber" id="litStatAvgDuration">0d</span>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <span class="f-chip active" data-filter="all" onclick="setLitigationFilter('all', this)">All</span>
                <span class="f-chip" data-filter="active" onclick="setLitigationFilter('active', this)">Active</span>
                <span class="f-chip" data-filter="settled" onclick="setLitigationFilter('settled', this)">Settled</span>
                <div class="f-spacer"></div>
                <input class="f-search" type="text" id="litigationSearch" placeholder="Search..." onkeyup="filterLitigationCases()">
                <button class="f-btn" data-action="add-litigation" onclick="openAddLitigationModal()">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Case
                </button>
            </div>

            <!-- Table -->
            <div class="tbl-container">
                <table class="tbl" id="litigationTable">
                    <thead>
                        <tr>
                            <th style="width:0;padding:0;border:none;"></th>
                            <th><span class="th-sort" onclick="sortLitigationCases('case_number')">Case # <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortLitigationCases('client_name')">Client <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortLitigationCases('litigation_start_date')">Lit. Start <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortLitigationCases('litigation_duration_days')">Duration <span class="sort-arrow">▼</span></span></th>
                            <th class="r"><span class="th-sort" onclick="sortLitigationCases('presuit_offer')">Pre-Suit Offer <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortLitigationCases('status')">Status <span class="sort-arrow">▼</span></span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="litigationTableBody">
                        <tr><td colspan="8" style="text-align:center; padding: 40px; color: #8b8fa3;">Loading...</td></tr>
                    </tbody>
                </table>
                <div class="tbl-foot">
                    <span class="left" id="litFooterLeft">0 litigation cases</span>
                    <span class="left" id="litFooterRight">Active: 0 · Settled: 0</span>
                </div>
            </div>
        </div>

        <!-- Commissions Tab (Settled Cases Only) -->
        <div id="content-commissions" class="tab-content hidden">
            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="qs-card">
                    <span class="qs-label">Cases</span>
                    <span class="qs-val" id="commStatCases">0</span>
                </div>
                <div class="qs-card">
                    <span class="qs-label">Total</span>
                    <span class="qs-val" id="commStatTotal">$0.00</span>
                </div>
                <div class="qs-card">
                    <span class="qs-label">Paid</span>
                    <span class="qs-val teal" id="commStatPaid">$0.00</span>
                </div>
                <div class="qs-card">
                    <span class="qs-label">Unpaid</span>
                    <span class="qs-val red" id="commStatUnpaid">$0.00</span>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <span class="f-chip active" data-filter="status" data-value="all" onclick="setCommissionFilter('status', 'all', this)">All</span>
                <span class="f-chip" data-filter="status" data-value="paid" onclick="setCommissionFilter('status', 'paid', this)">Paid</span>
                <span class="f-chip" data-filter="status" data-value="unpaid" onclick="setCommissionFilter('status', 'unpaid', this)">Unpaid</span>
                <span style="width: 1px; height: 20px; background: #e2e4ea; margin: 0 8px;"></span>
                <select id="commissionYearFilter" class="f-select" onchange="loadCommissions()" style="width: 85px;">
                    <option value="all">All</option>
                    <option value="2026" selected>2026</option>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                </select>
                <select id="commissionMonthFilter" class="f-select" onchange="loadCommissions()" style="width: 100px;">
                    <option value="all">All Months</option>
                    <option value="Jan">Jan</option>
                    <option value="Feb">Feb</option>
                    <option value="Mar">Mar</option>
                    <option value="Apr">Apr</option>
                    <option value="May">May</option>
                    <option value="Jun">Jun</option>
                    <option value="Jul">Jul</option>
                    <option value="Aug">Aug</option>
                    <option value="Sep">Sep</option>
                    <option value="Oct">Oct</option>
                    <option value="Nov">Nov</option>
                    <option value="Dec">Dec</option>
                </select>
                <div class="f-spacer"></div>
                <input type="text" id="commissionSearch" class="f-search" placeholder="Search..." onkeyup="filterCommissions()">
                <button class="f-btn" onclick="exportCommissionsToExcel()" style="background:#059669;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export
                </button>
            </div>

            <!-- Table -->
            <div class="tbl-container">
                <table class="tbl" id="commissionsTable">
                    <thead>
                        <tr>
                            <th style="width:0;padding:0;border:none;"></th>
                            <th><span class="th-sort" onclick="sortCommissions('resolution_type')">Resolution Type <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortCommissions('client_name')">Client Name <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortCommissions('settled')">Settled <span class="sort-arrow">▼</span></span></th>
                            <th class="r"><span class="th-sort" onclick="sortCommissions('pre_suit_offer')">Pre Suit Offer <span class="sort-arrow">▼</span></span></th>
                            <th class="r"><span class="th-sort" onclick="sortCommissions('difference')">Difference <span class="sort-arrow">▼</span></span></th>
                            <th class="r"><span class="th-sort" onclick="sortCommissions('legal_fee')">Legal Fee <span class="sort-arrow">▼</span></span></th>
                            <th class="r"><span class="th-sort" onclick="sortCommissions('discounted_fee')">Disc. Legal Fee <span class="sort-arrow">▼</span></span></th>
                            <th class="r"><span class="th-sort" onclick="sortCommissions('commission')">Commission <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortCommissions('month')">Month <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortCommissions('status')">Status <span class="sort-arrow">▼</span></span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="commissionsTableBody">
                        <tr><td colspan="12" style="text-align:center; padding: 40px; color: #8b8fa3;">Loading...</td></tr>
                    </tbody>
                </table>
                <div class="tbl-foot">
                    <span id="commTableCount">0 cases</span>
                    <span>Total <span class="ft-val" id="commTableTotal">$0.00</span>&nbsp;&nbsp;Paid <span class="ft-val" style="color:#0d9488;" id="commTablePaid">$0.00</span>&nbsp;&nbsp;Unpaid <span class="ft-val" style="color:#dc2626;" id="commTableUnpaid">$0.00</span></span>
                </div>
            </div>
        </div>

        <!-- Notifications Tab -->
        <div id="content-notifications" class="tab-content hidden">
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
                <button class="f-btn-primary" data-action="new-message">
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

        <!-- New Message Modal -->
        <div id="newMessageModal" class="modal-overlay">
            <div class="modal-content" style="max-width:500px;">
                <div class="modal-header">
                    <h2>New Message</h2>
                    <button class="modal-close" onclick="closeModal('newMessageModal')">&times;</button>
                </div>
                <form id="newMessageForm" onsubmit="sendMessage(event)" style="padding:20px;">
                    <div style="margin-bottom:16px;">
                        <label class="compose-label">To</label>
                        <input type="text" class="compose-input" value="Admin" disabled style="background:#f8f9fa;">
                    </div>
                    <div style="margin-bottom:16px;">
                        <label class="compose-label">Subject *</label>
                        <input type="text" name="subject" class="compose-input" required maxlength="200" placeholder="Enter subject">
                    </div>
                    <div style="margin-bottom:20px;">
                        <label class="compose-label">Message *</label>
                        <textarea name="message" class="compose-input" required rows="5" maxlength="5000" placeholder="Enter your message..." style="resize:vertical;"></textarea>
                    </div>
                    <div class="modal-actions-ink" style="border-top:none; padding-top:0;">
                        <button type="button" class="act-btn-secondary" onclick="closeModal('newMessageModal')">Cancel</button>
                        <button type="submit" class="act-btn-primary">Send Message</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Message Modal -->
        <div id="viewMessageModal" class="modal-overlay">
            <div class="modal-content" style="max-width:560px;">
                <div class="modal-header">
                    <h2>Message</h2>
                    <button class="modal-close" onclick="closeModal('viewMessageModal')">&times;</button>
                </div>
                <div class="modal-body-ink">
                    <div class="msg-detail-row">
                        <span class="msg-detail-label">From:</span>
                        <span class="msg-detail-value" id="viewMessageFrom"></span>
                    </div>
                    <div class="msg-detail-row">
                        <span class="msg-detail-label">Subject:</span>
                        <span class="msg-detail-value" id="viewMessageSubject"></span>
                    </div>
                    <div class="msg-detail-row">
                        <span class="msg-detail-label">Date:</span>
                        <span class="msg-detail-value" id="viewMessageDate"></span>
                    </div>
                    <div class="msg-detail-body" id="viewMessageBody"></div>
                    <div class="modal-actions-ink">
                        <button type="button" class="act-btn-primary" id="replyBtn" onclick="replyToMessage()">Reply</button>
                        <button type="button" class="act-btn-danger" onclick="deleteCurrentMessage()">Delete</button>
                        <div style="flex:1;"></div>
                        <button type="button" class="act-btn-secondary" onclick="closeModal('viewMessageModal')">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Tab -->
        <div id="content-reports" class="tab-content hidden">
            <!-- Quick Stats -->
            <div class="qs-grid" style="margin-bottom: 20px;">
                <div class="qs-card">
                    <div><div class="qs-label">Total Settled (YTD)</div><div class="qs-val" id="reportTotalCases">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Demand Settled</div><div class="qs-val blue" id="reportDemandSettled">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Litigation Settled</div><div class="qs-val amber" id="reportLitSettled">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Total Commission (YTD)</div><div class="qs-val teal" id="reportTotalCommission">$0</div></div>
                </div>
            </div>

            <!-- Reports Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <!-- Monthly Chart -->
                <div class="tbl-container">
                    <div class="tbl-header">
                        <span class="tbl-title">Monthly Commission</span>
                        <select id="reportYearFilter" class="f-select" onchange="loadReports()" style="min-width: 100px;">
                            <option value="2026">2026</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                        </select>
                    </div>
                    <div style="padding: 16px;">
                        <canvas id="commissionChart" height="200"></canvas>
                    </div>
                </div>

                <!-- Commission Breakdown -->
                <div class="tbl-container">
                    <div class="tbl-header">
                        <span class="tbl-title">Commission Breakdown</span>
                    </div>
                    <div style="padding: 16px;">
                        <canvas id="breakdownChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Settlements -->
            <div class="tbl-container" style="margin-top: 16px;">
                <div class="tbl-header">
                    <span class="tbl-title">Recent Settlements</span>
                    <button class="ink-btn ink-btn-secondary" onclick="exportReportToCSV()">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 4px;"><path stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Export CSV
                    </button>
                </div>
                <div style="overflow-x: auto;">
                    <table class="excel-table" id="recentSettlementsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Type</th>
                                <th>Resolution</th>
                                <th style="text-align: right;">Settled</th>
                                <th style="text-align: right;">Commission</th>
                            </tr>
                        </thead>
                        <tbody id="recentSettlementsBody">
                            <tr><td colspan="6" style="text-align:center; padding: 30px; color: #8b8fa3;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Traffic Cases Tab -->
        <div id="content-traffic" class="tab-content hidden">
            <!-- Sub-Tab Navigation -->
            <div style="display: flex; gap: 2px; padding: 6px; background: #f8f9fa; border-radius: 10px; margin-bottom: 16px; width: fit-content;">
                <button type="button" onclick="switchTrafficSubTab('cases')" id="trafficSubTab-cases" class="sidebar-tab-btn active" style="padding: 8px 16px; font-size: 12px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; font-family: 'Outfit', sans-serif;">Cases</button>
                <button type="button" onclick="switchTrafficSubTab('commission')" id="trafficSubTab-commission" class="sidebar-tab-btn" style="padding: 8px 16px; font-size: 12px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; font-family: 'Outfit', sans-serif;">Commission</button>
                <button type="button" onclick="switchTrafficSubTab('requests')" id="trafficSubTab-requests" class="sidebar-tab-btn" style="padding: 8px 16px; font-size: 12px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; font-family: 'Outfit', sans-serif;">Requests <span id="requestsSubTabBadge" class="traffic-count-badge" style="display:none; background:#dc2626; color:white;">0</span></button>
            </div>

            <!-- ========== SUB-TAB 1: CASES ========== -->
            <div id="trafficSubContent-cases">
                <!-- Pending Requests Alert Section -->
                <div id="pendingRequestsSection" style="display: none; margin-bottom: 16px;">
                    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #f59e0b; border-radius: 10px; padding: 16px;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                            <div style="background: #f59e0b; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <div style="font-size: 14px; font-weight: 700; color: #92400e; font-family: 'Outfit', sans-serif;">Pending Requests</div>
                                <div style="font-size: 12px; color: #b45309;">Review and accept new traffic case requests</div>
                            </div>
                        </div>
                        <div id="pendingRequestsCards" style="display: flex; flex-direction: column; gap: 8px;"></div>
                    </div>
                </div>

                <div class="quick-stats">
                    <div class="qs-card">
                        <span class="qs-label">Active Cases</span>
                        <span class="qs-val blue" id="trafficActive">0</span>
                    </div>
                    <div class="qs-card">
                        <span class="qs-label">Dismissed</span>
                        <span class="qs-val teal" id="trafficDismissed">0</span>
                    </div>
                    <div class="qs-card">
                        <span class="qs-label">Amended</span>
                        <span class="qs-val amber" id="trafficAmended">0</span>
                    </div>
                    <div class="qs-card">
                        <span class="qs-label">Total Commission</span>
                        <span class="qs-val teal" id="trafficCommission">$0</span>
                    </div>
                </div>

                <div style="display: flex; gap: 16px; margin-top: 16px;">
                    <div class="traffic-sidebar" style="width: 280px; flex-shrink: 0;">
                        <div style="background: white; border: 1px solid #e2e4ea; border-radius: 10px; overflow: hidden;">
                            <div style="padding: 6px; background: #f8f9fa; display: flex; gap: 2px;">
                                <button type="button" onclick="switchSidebarTab('all')" id="sidebarTab-all" class="sidebar-tab-btn active" style="flex: 1; padding: 8px 6px; font-size: 11px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; font-family: 'Outfit', sans-serif;">All</button>
                                <button type="button" onclick="switchSidebarTab('referral')" id="sidebarTab-referral" class="sidebar-tab-btn" style="flex: 1; padding: 8px 6px; font-size: 11px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; font-family: 'Outfit', sans-serif;">Requester</button>
                                <button type="button" onclick="switchSidebarTab('court')" id="sidebarTab-court" class="sidebar-tab-btn" style="flex: 1; padding: 8px 6px; font-size: 11px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; font-family: 'Outfit', sans-serif;">Court</button>
                                <button type="button" onclick="switchSidebarTab('year')" id="sidebarTab-year" class="sidebar-tab-btn" style="flex: 1; padding: 8px 6px; font-size: 11px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; font-family: 'Outfit', sans-serif;">Year</button>
                            </div>
                            <div id="sidebarContent" style="max-height: 380px; overflow-y: auto; padding: 8px;"></div>
                        </div>
                    </div>

                    <div style="flex: 1; min-width: 0;">
                        <p style="font-size: 11px; color: #8b8fa3; margin: 0 0 8px 0;" id="trafficFilterLabel">Active Cases</p>
                        <div class="filters">
                            <span class="f-chip" id="trafficStatusBtn-all" onclick="setTrafficStatusFilter('all')">All <span id="trafficCountAll" class="traffic-count-badge">0</span></span>
                            <span class="f-chip active" id="trafficStatusBtn-active" onclick="setTrafficStatusFilter('active')">Active <span id="trafficCountActive" class="traffic-count-badge">0</span></span>
                            <span class="f-chip" id="trafficStatusBtn-done" onclick="setTrafficStatusFilter('done')">Done <span id="trafficCountDone" class="traffic-count-badge">0</span></span>
                            <div class="f-spacer"></div>
                            <input type="text" id="trafficSearch" placeholder="Search..." class="f-search" onkeyup="filterTrafficCases()">
                        </div>

                        <div class="tbl-container">
                            <table class="tbl" id="trafficTable">
                                <thead>
                                    <tr>
                                        <th style="width:0;padding:0;border:none;"></th>
                                        <th><span class="th-sort" onclick="sortTrafficCases('created_at')">Accepted <span class="sort-arrow">▼</span></span></th>
                                        <th><span class="th-sort" onclick="sortTrafficCases('client_name')">Client <span class="sort-arrow">▼</span></span></th>
                                        <th><span class="th-sort" onclick="sortTrafficCases('case_number')">Case # <span class="sort-arrow">▼</span></span></th>
                                        <th><span class="th-sort" onclick="sortTrafficCases('court')">Court <span class="sort-arrow">▼</span></span></th>
                                        <th><span class="th-sort" onclick="sortTrafficCases('charge')">Charge <span class="sort-arrow">▼</span></span></th>
                                        <th><span class="th-sort" onclick="sortTrafficCases('court_date')">Court Date <span class="sort-arrow">▼</span></span></th>
                                        <th><span class="th-sort" onclick="sortTrafficCases('noa_sent_date')">NOA Sent <span class="sort-arrow">▼</span></span></th>
                                        <th class="c"><span class="th-sort" onclick="sortTrafficCases('discovery')">Discovery <span class="sort-arrow">▼</span></span></th>
                                        <th class="c"><span class="th-sort" onclick="sortTrafficCases('status')">Status <span class="sort-arrow">▼</span></span></th>
                                        <th><span class="th-sort" onclick="sortTrafficCases('referral_source')">Requester <span class="sort-arrow">▼</span></span></th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="trafficTableBody">
                                    <tr><td colspan="12" style="text-align:center; padding: 40px; color: #8b8fa3;">Loading...</td></tr>
                                </tbody>
                            </table>
                            <div class="tbl-foot">
                                <span id="trafficCaseCount">0 cases</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== SUB-TAB 2: COMMISSION ========== -->
            <div id="trafficSubContent-commission" style="display: none;">
                <div class="quick-stats">
                    <div class="qs-card">
                        <span class="qs-label">Total Commission</span>
                        <span class="qs-val teal" id="commTotalCommission">$0</span>
                    </div>
                    <div class="qs-card">
                        <span class="qs-label">Paid</span>
                        <span class="qs-val green" id="commPaidTotal">$0</span>
                    </div>
                    <div class="qs-card">
                        <span class="qs-label">Unpaid</span>
                        <span class="qs-val amber" id="commUnpaidTotal">$0</span>
                    </div>
                    <div class="qs-card">
                        <span class="qs-label">Resolved Cases</span>
                        <span class="qs-val blue" id="commCaseCount">0</span>
                    </div>
                </div>

                <div class="filters">
                    <span class="f-chip active" id="commFilterBtn-all" onclick="setCommTrafficFilter('all')">All <span id="commCountAll" class="traffic-count-badge">0</span></span>
                    <span class="f-chip" id="commFilterBtn-paid" onclick="setCommTrafficFilter('paid')">Paid <span id="commCountPaid" class="traffic-count-badge">0</span></span>
                    <span class="f-chip" id="commFilterBtn-unpaid" onclick="setCommTrafficFilter('unpaid')">Unpaid <span id="commCountUnpaid" class="traffic-count-badge">0</span></span>
                    <span style="width: 1px; height: 20px; background: #e2e4ea; margin: 0 8px;"></span>
                    <select id="commYearFilter" class="f-select" onchange="filterCommTraffic()" style="width: 85px;">
                        <option value="">All Years</option>
                    </select>
                    <select id="commMonthFilter" class="f-select" onchange="filterCommTraffic()" style="width: 100px;">
                        <option value="">All Months</option>
                    </select>
                    <div class="f-spacer"></div>
                    <input type="text" id="commTrafficSearch" placeholder="Search..." class="f-search" onkeyup="filterCommTraffic()">
                    <button class="f-btn" onclick="exportTrafficCommissions()" style="background:#059669;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Export
                    </button>
                </div>

                <div class="tbl-container">
                    <table class="tbl" id="commTrafficTable">
                        <thead>
                            <tr>
                                <th style="width:0;padding:0;border:none;"></th>
                                <th><span class="th-sort" onclick="sortCommTraffic('client_name')">Client <span class="sort-arrow">▼</span></span></th>
                                <th><span class="th-sort" onclick="sortCommTraffic('court')">Court <span class="sort-arrow">▼</span></span></th>
                                <th><span class="th-sort" onclick="sortCommTraffic('court_date')">Court Date <span class="sort-arrow">▼</span></span></th>
                                <th><span class="th-sort" onclick="sortCommTraffic('resolved_at')">Resolved <span class="sort-arrow">▼</span></span></th>
                                <th><span class="th-sort" onclick="sortCommTraffic('disposition')">Disposition <span class="sort-arrow">▼</span></span></th>
                                <th><span class="th-sort" onclick="sortCommTraffic('referral_source')">Requester <span class="sort-arrow">▼</span></span></th>
                                <th class="r"><span class="th-sort" onclick="sortCommTraffic('commission')">Amount <span class="sort-arrow">▼</span></span></th>
                                <th class="c"><span class="th-sort" onclick="sortCommTraffic('paid')">Paid <span class="sort-arrow">▼</span></span></th>
                            </tr>
                        </thead>
                        <tbody id="commTrafficTableBody">
                            <tr><td colspan="9" style="text-align:center; padding: 40px; color: #8b8fa3;">Loading...</td></tr>
                        </tbody>
                    </table>
                    <div class="tbl-foot">
                        <span id="commTrafficCaseCount">0 cases</span>
                        <span>Total: <span class="ft-val" id="commTrafficTotal">$0.00</span></span>
                    </div>
                </div>
            </div>

            <!-- ========== SUB-TAB 3: REQUESTS ========== -->
            <div id="trafficSubContent-requests" style="display: none;">
                <div class="quick-stats">
                    <div class="qs-card">
                        <span class="qs-label">Pending</span>
                        <span class="qs-val amber" id="reqPendingCount">0</span>
                    </div>
                    <div class="qs-card">
                        <span class="qs-label">Accepted</span>
                        <span class="qs-val green" id="reqAcceptedCount">0</span>
                    </div>
                    <div class="qs-card">
                        <span class="qs-label">Denied</span>
                        <span class="qs-val" style="color:#dc2626;" id="reqDeniedCount">0</span>
                    </div>
                    <div class="qs-card">
                        <span class="qs-label">Total Requests</span>
                        <span class="qs-val blue" id="reqTotalCount">0</span>
                    </div>
                </div>

                <div class="filters">
                    <span class="f-chip active" id="reqFilterBtn-all" onclick="setRequestFilter('all')">All</span>
                    <span class="f-chip" id="reqFilterBtn-pending" onclick="setRequestFilter('pending')">Pending <span id="reqBadgePending" class="traffic-count-badge" style="background:#dc2626; color:white;">0</span></span>
                    <span class="f-chip" id="reqFilterBtn-accepted" onclick="setRequestFilter('accepted')">Accepted</span>
                    <span class="f-chip" id="reqFilterBtn-denied" onclick="setRequestFilter('denied')">Denied</span>
                    <div class="f-spacer"></div>
                    <input type="text" id="requestsSearch" placeholder="Search..." class="f-search" onkeyup="filterRequests()">
                </div>

                <div class="tbl-container" style="margin-top: 8px; overflow-x: hidden;">
                    <table class="tbl tbl-compact" style="table-layout: fixed; width: 100%;">
                        <colgroup>
                            <col style="width:7%">
                            <col style="width:7%">
                            <col style="width:12%">
                            <col style="width:8%">
                            <col style="width:12%">
                            <col style="width:10%">
                            <col style="width:12%">
                            <col style="width:8%">
                            <col style="width:7%">
                            <col style="width:7%">
                            <col style="width:10%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Reqstr</th>
                                <th>Req'd</th>
                                <th>Client</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Court</th>
                                <th>Charge</th>
                                <th>Ticket #</th>
                                <th>Ct. Date</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody id="requestsTableBody">
                            <tr><td colspan="11" style="text-align:center; padding:40px; color:#8b8fa3;">Loading...</td></tr>
                        </tbody>
                    </table>
                    <div class="tbl-foot"><span id="requestsCaseCount">0 requests</span></div>
                </div>
            </div>

        </div>

        </div><!-- /.page-content -->
    </div><!-- /.main -->

    <!-- New Demand Case Modal -->
    <div id="newDemandModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Add New Demand Case</h2>
                <button class="modal-close" onclick="closeModal('newDemandModal')">&times;</button>
            </div>
            <form id="newDemandForm" onsubmit="submitNewDemand(event)">
                <input type="hidden" id="newDemandPhase" name="phase" value="demand">
                <input type="hidden" id="newDemandStage" name="stage" value="demand_review">
                <div class="form-row">
                    <div class="form-group">
                        <label>Case Number *</label>
                        <input type="text" name="case_number" required>
                    </div>
                    <div class="form-group">
                        <label>Client Name *</label>
                        <input type="text" name="client_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Case Type</label>
                        <select name="case_type">
                            <option value="Auto Accident">Auto Accident</option>
                            <option value="Pedestrian">Pedestrian</option>
                            <option value="Slip and Fall">Slip and Fall</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assigned Date</label>
                        <input type="date" id="newDemandAssignedDate" name="assigned_date" value="<?php echo date('Y-m-d'); ?>">
                        <div class="help-text">Deadline auto-calculated: +90 days</div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Settlement (Optional - fill if settling now)</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Settled Amount ($)</label>
                            <input type="number" name="settled" step="0.01" min="0" onchange="calculateDemandCommission()">
                        </div>
                        <div class="form-group">
                            <label>Discounted Legal Fee ($)</label>
                            <input type="number" name="discounted_legal_fee" step="0.01" min="0" onchange="calculateDemandCommission()">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Commission (5% of Disc. Legal Fee)</label>
                            <input type="text" name="commission_display" class="calculated" readonly>
                        </div>
                        <div class="form-group">
                            <label>Month</label>
                            <select name="month">
                                <?php
                                $months = getMonthOptions();
                                $currentMonth = getCurrentMonth();
                                foreach ($months as $m) {
                                    $selected = ($m === $currentMonth) ? 'selected' : '';
                                    echo "<option value=\"$m\" $selected>$m</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label>Note</label>
                        <input type="text" name="note">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('newDemandModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Case</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Settle Demand Modal -->
    <div id="settleDemandModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Settle Demand Case</h2>
                <button class="modal-close" onclick="closeModal('settleDemandModal')">&times;</button>
            </div>
            <form id="settleDemandForm" onsubmit="submitSettleDemand(event)">
                <input type="hidden" name="case_id">
                <div class="form-row full">
                    <div class="form-group">
                        <label>Case: <span id="settleDemandCaseInfo" style="font-weight: 600;"></span></label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Settled Amount ($) *</label>
                        <input type="number" name="settled" step="0.01" min="0" required oninput="updateSettleDemandLegalFee()">
                    </div>
                    <div class="form-group">
                        <label>Legal Fee (33.33%)</label>
                        <input type="text" name="legal_fee_display" class="calculated" readonly style="background: #f3f4f6;">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Disc. Legal Fee ($) * <span style="font-size:11px;color:#6b7280;">(Editable)</span></label>
                        <input type="number" name="discounted_legal_fee" step="0.01" min="0" required oninput="this.dataset.userModified='true'; calculateSettleDemandCommission()">
                    </div>
                    <div class="form-group">
                        <label>Commission (5%)</label>
                        <input type="text" name="commission_display" class="calculated" readonly>
                    </div>
                    <div class="form-group">
                        <label>Month</label>
                        <select name="month">
                            <?php foreach ($months as $m): ?>
                                <option value="<?php echo $m; ?>" <?php echo ($m === $currentMonth) ? 'selected' : ''; ?>><?php echo $m; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; padding: 12px 0; margin-top: 8px; border-top: 1px solid #e5e7eb;">
                    <input type="checkbox" name="check_received" id="demandCheckReceived" style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="demandCheckReceived" style="font-size: 13px; font-weight: 500; color: #374151; cursor: pointer; margin: 0;">Check Received</label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('settleDemandModal')">Cancel</button>
                    <button type="submit" class="act-btn settle" style="padding: 8px 20px; font-size: 13px;">Settle Case</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Move to Litigation Modal -->
    <div id="toLitigationModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Move to Litigation</h2>
                <button class="modal-close" onclick="closeModal('toLitigationModal')">&times;</button>
            </div>
            <form id="toLitigationForm" onsubmit="submitToLitigation(event)">
                <input type="hidden" name="case_id">
                <div class="form-row full">
                    <div class="form-group">
                        <label>Case: <span id="toLitCaseInfo" style="font-weight: 600;"></span></label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Litigation Start Date</label>
                        <input type="date" name="litigation_start_date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Pre-Suit Offer ($)</label>
                        <input type="number" name="presuit_offer" step="0.01" min="0" value="0">
                        <div class="help-text">The offer received before litigation</div>
                    </div>
                </div>
                <div class="form-row full">
                    <div class="form-group">
                        <label>Note</label>
                        <input type="text" name="note">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('toLitigationModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: #f59e0b;">Move to Litigation</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Settle Litigation Modal -->
    <div id="settleLitigationModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 650px;">
            <div class="modal-header">
                <h2>Settle Litigation Case</h2>
                <button class="modal-close" onclick="closeModal('settleLitigationModal')">&times;</button>
            </div>
            <form id="settleLitigationForm" onsubmit="submitSettleLitigation(event)">
                <input type="hidden" name="case_id">
                <input type="hidden" name="presuit_offer_hidden">

                <div class="form-row full">
                    <div class="form-group">
                        <label>Case: <span id="settleLitCaseInfo" style="font-weight: 600;"></span></label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Resolution Type *</label>
                        <select name="resolution_type" required onchange="onResolutionTypeChange()">
                            <option value="">-- Select --</option>
                            <optgroup label="33.33% (Pre-Suit Deducted)">
                                <option value="File and Bump">File and Bump</option>
                                <option value="Post Deposition Settle">Post Deposition Settle</option>
                                <option value="Mediation">Mediation</option>
                                <option value="Settled Post Arbitration">Settled Post Arbitration</option>
                                <option value="Settlement Conference">Settlement Conference</option>
                            </optgroup>
                            <optgroup label="40% (No Deduction)">
                                <option value="Arbitration Award">Arbitration Award</option>
                                <option value="Beasley">Beasley</option>
                            </optgroup>
                            <optgroup label="Variable (Manual)">
                                <option value="Co-Counsel">Co-Counsel</option>
                                <option value="Other">Other</option>
                                <option value="No Offer Settle">No Offer Settle</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Pre-Suit Offer ($)</label>
                        <input type="number" name="presuit_offer" step="0.01" readonly class="calculated">
                    </div>
                </div>

                <div id="resolutionInfo" class="resolution-info" style="display:none;">
                    <span class="label">Fee Rate:</span> <span class="value" id="infoFeeRate">-</span> |
                    <span class="label">Commission Rate:</span> <span class="value" id="infoCommRate">-</span>
                </div>

                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Settled Amount ($) *</label>
                            <input type="number" name="settled" step="0.01" min="0" required onchange="calculateLitCommission()">
                        </div>
                        <div class="form-group">
                            <label>Difference ($)</label>
                            <input type="text" name="difference_display" class="calculated" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Legal Fee (Reference)</label>
                            <input type="text" name="legal_fee_display" class="calculated" readonly>
                        </div>
                        <div class="form-group">
                            <label>Disc. Legal Fee ($) * <span style="font-size:11px;color:#6b7280;">(Editable)</span></label>
                            <input type="number" name="discounted_legal_fee" step="0.01" min="0" required oninput="this.dataset.userModified='true'; calculateLitCommission()">
                        </div>
                    </div>

                    <!-- Variable fields (hidden by default) -->
                    <div id="variableFields" class="form-row" style="display:none;">
                        <div class="form-group">
                            <label>Manual Fee Rate (%)</label>
                            <input type="number" name="manual_fee_rate" step="0.01" min="0" max="100" onchange="calculateLitCommission()">
                        </div>
                        <div class="form-group">
                            <label>Manual Commission Rate (%)</label>
                            <input type="number" name="manual_commission_rate" step="0.01" min="0" max="100" onchange="calculateLitCommission()">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Commission</label>
                            <input type="text" name="commission_display" class="calculated" readonly style="font-size: 18px; font-weight: 700; color: #059669;">
                        </div>
                        <div class="form-group">
                            <label>Month</label>
                            <select name="month">
                                <?php foreach ($months as $m): ?>
                                    <option value="<?php echo $m; ?>" <?php echo ($m === $currentMonth) ? 'selected' : ''; ?>><?php echo $m; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; padding: 12px 0; margin-top: 8px; border-top: 1px solid #e5e7eb;">
                    <input type="checkbox" name="check_received" id="litCheckReceived" style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="litCheckReceived" style="font-size: 13px; font-weight: 500; color: #374151; cursor: pointer; margin: 0;">Check Received</label>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('settleLitigationModal')">Cancel</button>
                    <button type="submit" class="act-btn settle" style="padding: 8px 20px; font-size: 13px;">Settle Case</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Traffic Case Modal -->
    <div id="trafficModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 id="trafficModalTitle">Add Traffic Case</h2>
                <button class="modal-close" onclick="closeModal('trafficModal')">&times;</button>
            </div>
            <form id="trafficForm" onsubmit="submitTrafficCase(event)">
                <input type="hidden" id="trafficCaseId">

                <div class="form-row">
                    <div class="form-group">
                        <label>Client Name *</label>
                        <input type="text" id="trafficClientName" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" id="trafficClientPhone">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Court</label>
                        <select id="trafficCourt">
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
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Court Date</label>
                        <input type="datetime-local" id="trafficCourtDate">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Charge</label>
                        <select id="trafficCharge">
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
                    <div class="form-group">
                        <label>Case Number</label>
                        <input type="text" id="trafficCaseNumber">
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label>Prosecutor Offer</label>
                        <input type="text" id="trafficOffer" placeholder="e.g., DDS1 and dismiss">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Disposition</label>
                        <select id="trafficDisposition" onchange="updateTrafficCommission()">
                            <option value="pending">Pending</option>
                            <option value="dismissed">Dismissed ($150)</option>
                            <option value="amended">Amended ($100)</option>
                            <option value="other">Other ($0)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="trafficStatus">
                            <option value="active">Active</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Requester</label>
                        <select id="trafficReferralSource">
                            <option value="">Select Requester</option>
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
                    <div class="form-group">
                        <label>NOA Sent Date</label>
                        <input type="date" id="trafficNoaSentDate">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><input type="checkbox" id="trafficDiscovery"> Discovery Received</label>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" id="trafficPaid"> Commission Paid</label>
                    </div>
                </div>

                <div style="background: #f3f4f6; padding: 12px 16px; border-radius: 8px; margin: 16px 0; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600;">Commission:</span>
                    <span id="trafficCommissionDisplay" style="font-size: 20px; font-weight: 700; color: #059669;">$0.00</span>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label>Note</label>
                        <textarea id="trafficNote" rows="2" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;"></textarea>
                    </div>
                </div>

                <!-- File Attachments Section (only shown when editing) -->
                <div id="trafficFilesSection" style="display: none; margin: 16px 0; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                    <div style="background: #f9fafb; padding: 10px 16px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 600; font-size: 14px;">Attachments</span>
                        <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: #2563eb; color: white; border-radius: 6px; font-size: 12px; font-weight: 500;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Upload
                            <input type="file" id="trafficFileInput" style="display: none;" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif" onchange="uploadTrafficFile(this)">
                        </label>
                    </div>
                    <div id="trafficFilesList" style="max-height: 200px; overflow-y: auto;">
                        <div style="padding: 16px; text-align: center; color: #9ca3af; font-size: 13px;">No files attached</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('trafficModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Case</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Case Modal -->
    <div id="editCaseModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2>Edit Case</h2>
                <button class="modal-close" onclick="closeModal('editCaseModal')">&times;</button>
            </div>
            <form id="editCaseForm" onsubmit="submitEditCase(event)">
                <input type="hidden" id="editCaseId">

                <div class="form-row">
                    <div class="form-group">
                        <label>Case Number *</label>
                        <input type="text" id="editCaseNumber" required>
                    </div>
                    <div class="form-group">
                        <label>Client Name *</label>
                        <input type="text" id="editClientName" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Phase</label>
                        <select id="editPhase" onchange="toggleEditPhaseFields()">
                            <option value="demand">Demand</option>
                            <option value="litigation">Litigation</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Month</label>
                        <select id="editMonth">
                            <?php foreach (getMonthOptions() as $month): ?>
                            <option value="<?php echo $month; ?>"><?php echo $month; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row" id="editStageRow">
                    <div class="form-group">
                        <label>Stage</label>
                        <select id="editStage">
                            <option value="">Select Stage...</option>
                            <option value="demand_review">Demand Review</option>
                            <option value="demand_write">Demand Write</option>
                            <option value="demand_sent">Demand Sent</option>
                            <option value="negotiate">Negotiate</option>
                        </select>
                    </div>
                    <div class="form-group"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Settled Amount</label>
                        <input type="number" id="editSettled" step="0.01" min="0" onchange="calculateEditCommission()">
                    </div>
                    <div class="form-group">
                        <label>Discounted Legal Fee</label>
                        <input type="number" id="editDiscLegalFee" step="0.01" min="0" onchange="calculateEditCommission()">
                    </div>
                </div>

                <div id="editLitigationFields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Pre-suit Offer</label>
                            <input type="number" id="editPresuitOffer" step="0.01" min="0" onchange="calculateEditCommission()">
                        </div>
                        <div class="form-group">
                            <label>Resolution Type</label>
                            <select id="editResolutionType" onchange="calculateEditCommission()">
                                <option value="">Select...</option>
                                <optgroup label="33.33% Fee Rate">
                                    <option value="File and Bump">File and Bump</option>
                                    <option value="Post Deposition Settle">Post Deposition Settle</option>
                                    <option value="Mediation">Mediation</option>
                                    <option value="Settled Post Arbitration">Settled Post Arbitration</option>
                                    <option value="Settlement Conference">Settlement Conference</option>
                                </optgroup>
                                <optgroup label="40% Fee Rate">
                                    <option value="Arbitration Award">Arbitration Award</option>
                                    <option value="Beasley">Beasley</option>
                                </optgroup>
                                <optgroup label="Variable">
                                    <option value="Co-Counsel">Co-Counsel</option>
                                    <option value="Other">Other</option>
                                    <option value="No Offer Settle">No Offer Settle</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Assigned Date</label>
                        <input type="date" id="editAssignedDate">
                    </div>
                    <div class="form-group">
                        <label>Note</label>
                        <input type="text" id="editNote">
                    </div>
                </div>

                <!-- Deadline Extension Request Section -->
                <div id="deadlineSection" class="deadline-extension-section" style="display: none;">
                    <div class="form-section-title" style="margin-top: 16px;">Deadline Management</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Current Deadline</label>
                            <input type="date" id="editCurrentDeadline" disabled style="background: #f3f4f6;">
                        </div>
                        <div class="form-group">
                            <label>Days Remaining</label>
                            <input type="text" id="editDaysRemaining" disabled style="background: #f3f4f6;">
                        </div>
                    </div>
                    <div id="pendingExtensionAlert" class="pending-extension-alert" style="display: none;">
                        <span class="alert-icon">⏳</span>
                        <span class="alert-text">Deadline extension request pending approval</span>
                    </div>
                    <div id="deadlineExtensionForm" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Requested New Deadline <span class="required">*</span></label>
                                <input type="date" id="editRequestedDeadline">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Reason for Extension <span class="required">*</span></label>
                            <textarea id="editExtensionReason" rows="3" placeholder="Please explain why you need a deadline extension..."></textarea>
                        </div>
                        <div style="display: flex; gap: 8px; margin-top: 8px;">
                            <button type="button" class="btn btn-warning" onclick="submitDeadlineExtension()">Submit Extension Request</button>
                            <button type="button" class="btn btn-secondary" onclick="cancelDeadlineExtension()">Cancel</button>
                        </div>
                    </div>
                    <button type="button" id="requestExtensionBtn" class="btn btn-outline" onclick="showDeadlineExtensionForm()">
                        Request Deadline Extension
                    </button>
                </div>

                <div style="background: #f3f4f6; padding: 12px 16px; border-radius: 8px; margin: 16px 0; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600;">Commission:</span>
                    <span id="editCommissionDisplay" style="font-size: 20px; font-weight: 700; color: #059669;">$0.00</span>
                </div>

                <div class="modal-footer" style="justify-content: space-between;">
                    <button type="button" class="btn" style="background:#dc2626; color:#fff;" onclick="deleteCaseFromModal()">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 4px;"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Delete
                    </button>
                    <div style="display: flex; gap: 12px;">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('editCaseModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Commission Modal -->
    <div id="editCommissionModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Edit Commission</h2>
                <button class="modal-close" onclick="closeModal('editCommissionModal')">&times;</button>
            </div>
            <div style="padding: 20px;">
                <input type="hidden" id="editCommCaseId">

                <div class="form-row">
                    <div class="form-group">
                        <label>Client Name</label>
                        <input type="text" id="editCommClientName">
                    </div>
                    <div class="form-group">
                        <label>Resolution Type</label>
                        <select id="editCommResolutionType">
                            <option value="">Select...</option>
                            <option value="File and Bump">File and Bump</option>
                            <option value="Post Deposition Settle">Post Deposition Settle</option>
                            <option value="Mediation">Mediation</option>
                            <option value="Settled Post Arbitration">Settled Post Arbitration</option>
                            <option value="Settlement Conference">Settlement Conference</option>
                            <option value="Arbitration Award">Arbitration Award</option>
                            <option value="Beasley">Beasley</option>
                            <option value="Co-Counsel">Co-Counsel</option>
                            <option value="Other">Other</option>
                            <option value="No Offer Settle">No Offer Settle</option>
                            <option value="Demand">Demand</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Settled Amount</label>
                        <input type="number" id="editCommSettled" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Pre-Suit Offer</label>
                        <input type="number" id="editCommPreSuitOffer" step="0.01" min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Legal Fee</label>
                        <input type="number" id="editCommLegalFee" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Discounted Legal Fee</label>
                        <input type="number" id="editCommDiscountedFee" step="0.01" min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Commission</label>
                        <input type="number" id="editCommCommission" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Month</label>
                        <select id="editCommMonth">
                            <?php foreach (getMonthOptions() as $month): ?>
                            <option value="<?php echo $month; ?>"><?php echo $month; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="modal-footer" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #e2e4ea;">
                    <button type="button" class="ink-btn ink-btn-secondary" onclick="closeModal('editCommissionModal')">Cancel</button>
                    <button type="button" class="ink-btn ink-btn-primary" onclick="saveCommission()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global state
        let csrfToken = '<?php echo $csrfToken; ?>';
        let commissionsData = [];
        let demandCasesData = [];
        let litigationCasesData = [];
        let commissionsSortColumn = 'month';
        let commissionsSortDir = 'desc';
        let demandSortColumn = 'demand_deadline';
        let demandSortDir = 'asc';
        let litigationSortColumn = 'litigation_start_date';
        let litigationSortDir = 'desc';
        let currentDemandFilter = 'all';
        let currentLitigationFilter = 'all';
        let currentCommissionStatus = 'all';
        let messagesData = [];
        let currentMessageId = null;
        let adminUserId = null; // Will be fetched
        let notifCurrentFilter = 'all';
        let trafficSortColumn = 'court_date';
        let trafficSortDir = 'desc';

        // Resolution type configurations
        const resolutionConfig = {
            'File and Bump': { feeRate: 33.33, commRate: 20, deductPresuit: true },
            'Post Deposition Settle': { feeRate: 33.33, commRate: 20, deductPresuit: true },
            'Mediation': { feeRate: 33.33, commRate: 20, deductPresuit: true },
            'Settled Post Arbitration': { feeRate: 33.33, commRate: 20, deductPresuit: true },
            'Settlement Conference': { feeRate: 33.33, commRate: 20, deductPresuit: true },
            'Arbitration Award': { feeRate: 40, commRate: 20, deductPresuit: false },
            'Beasley': { feeRate: 40, commRate: 20, deductPresuit: false },
            'Co-Counsel': { feeRate: 0, commRate: 0, variable: true },
            'Other': { feeRate: 0, commRate: 0, variable: true },
            'No Offer Settle': { feeRate: 0, commRate: 0, variable: true }
        };

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboard();
            loadDemandCases();
            loadLitigationCases();
            // Commissions tab loads on demand when clicked
            loadUnreadCount(); // Load notification badge count
            loadAllTrafficRequests(); // Load traffic request badge

            // Sidebar nav-link click handler
            document.querySelectorAll('.nav-link[data-tab]').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    switchTab(this.dataset.tab);
                });
            });

            // Event delegation for dynamically created buttons
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('[data-action]');
                if (!btn) return;

                e.preventDefault();
                e.stopPropagation();

                const action = btn.dataset.action;
                const id = btn.dataset.id;
                const caseNum = btn.dataset.case || '';
                const client = btn.dataset.client || '';
                const presuit = parseFloat(btn.dataset.presuit) || 0;

                switch (action) {
                    case 'new-demand':
                        openNewDemandModal();
                        break;
                    case 'new-traffic':
                        openTrafficModal();
                        break;
                    case 'edit':
                        openEditCaseModal(parseInt(id));
                        break;
                    case 'settle-demand':
                        openSettleDemandModal(parseInt(id), caseNum, client);
                        break;
                    case 'to-litigation':
                        openToLitigationModal(parseInt(id), caseNum, client);
                        break;
                    case 'settle-litigation':
                        openSettleLitigationModal(parseInt(id), caseNum, client, presuit);
                        break;
                    case 'edit-traffic':
                        editTrafficCase(parseInt(id));
                        break;
                    case 'delete-traffic':
                        deleteTrafficCase(parseInt(id));
                        break;
                    case 'new-message':
                        openNewMessageModal();
                        break;
                    case 'view-message':
                        viewMessage(parseInt(id));
                        break;
                    default:
                        console.warn('Unknown action:', action);
                }
            });
        });

        // Tab switching
        // Page titles for each tab
        const pageTitles = {
            'dashboard': 'Dashboard',
            'commissions': 'Commissions',
            'demand': 'Demand Cases',
            'litigation': 'Litigation Cases',
            'traffic': 'Traffic Cases',
            'notifications': 'Notifications',
            'reports': 'Reports'
        };

        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));

            // Remove active from all sidebar links
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));

            // Show selected tab content
            document.getElementById('content-' + tabName).classList.remove('hidden');

            // Activate sidebar link
            const navLink = document.querySelector('.nav-link[data-tab="' + tabName + '"]');
            if (navLink) navLink.classList.add('active');

            // Update page title
            document.getElementById('pageTitle').textContent = pageTitles[tabName] || tabName;

            // Load data for specific tabs
            if (tabName === 'commissions') {
                loadCommissions();
            }
            if (tabName === 'reports') {
                loadReports();
            }
            if (tabName === 'traffic') {
                loadTrafficCases();
                loadAllTrafficRequests();
            }
            if (tabName === 'notifications') {
                loadMessages();
            }
            setWidth('100');
        }

        // Width control
        function setWidth(width) {
            const mainContent = document.getElementById('mainContent');
            mainContent.className = 'page-content w-' + width;
            document.querySelectorAll('.sz-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.width === width);
            });
        }

        // Toast notification
        function showToast(message, type = 'info') {
            // Remove existing toast
            const existing = document.querySelector('.toast-notification');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;
            toast.innerHTML = `
                <span>${message}</span>
                <button onclick="this.parentElement.remove()">&times;</button>
            `;
            document.body.appendChild(toast);

            // Auto remove after 4 seconds
            setTimeout(() => toast.remove(), 4000);
        }

        // API calls
        async function apiCall(url, method = 'GET', data = null) {
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                }
            };
            if (data) {
                options.body = JSON.stringify(data);
            }
            const response = await fetch(url, options);
            const result = await response.json();
            if (result.csrf_token) {
                csrfToken = result.csrf_token;
            }
            return result;
        }

        // Load Dashboard
        async function loadDashboard() {
            const result = await apiCall('api/chong_cases.php?stats=1');
            if (result.stats) {
                const s = result.stats;
                document.getElementById('statTotalActive').textContent = s.total_active;
                document.getElementById('statDemand').textContent = s.demand_count;
                document.getElementById('statLitigation').textContent = s.litigation_count;
                document.getElementById('statOverdue').textContent = s.overdue_count || 0;
                document.getElementById('statDue2Weeks').textContent = s.urgent_count || 0;
                document.getElementById('statMonthCommission').textContent = formatCurrency(s.month_commission);

                // Update badge
                if (s.urgent_count > 0) {
                    document.getElementById('demandBadge').textContent = s.urgent_count;
                    document.getElementById('demandBadge').style.display = 'inline';
                }
            }

            // Load urgent cases
            const urgentResult = await apiCall('api/chong_cases.php?urgent=1');
            if (urgentResult.cases) {
                renderUrgentCases(urgentResult.cases);
            }
        }

        function renderUrgentCases(cases) {
            const container = document.getElementById('urgentCasesList');
            if (cases.length === 0) {
                container.innerHTML = '<p style="color: #059669;">No urgent cases. All cases are on track!</p>';
                return;
            }

            // Build table with all details
            let html = `
                <div class="table-container" style="margin-top: 12px;">
                    <table class="excel-table urgent-table">
                        <thead>
                            <tr>
                                <th>Case #</th>
                                <th>Client Name</th>
                                <th>Case Type</th>
                                <th>Incident Date</th>
                                <th>Deadline</th>
                                <th>Days Left</th>
                                <th>Status</th>
                                <th style="text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            cases.forEach(c => {
                const isOverdue = c.days_until_deadline < 0;
                const daysText = isOverdue ? `${Math.abs(c.days_until_deadline)} days overdue` : `${c.days_until_deadline} days left`;
                const daysClass = isOverdue ? 'deadline-overdue' : 'deadline-critical';
                const rowClass = isOverdue ? 'urgent-row-overdue' : 'urgent-row-warning';
                const statusClass = c.status ? `status-${c.status}` : 'status-in_progress';
                const statusText = c.status ? c.status.replace('_', ' ') : 'in progress';

                html += `
                    <tr class="${rowClass}">
                        <td><strong>${escapeHtml(c.case_number)}</strong></td>
                        <td>${escapeHtml(c.client_name)}</td>
                        <td>${escapeHtml(c.case_type || '-')}</td>
                        <td>${formatDate(c.incident_date) || '-'}</td>
                        <td>${formatDate(c.demand_deadline) || '-'}</td>
                        <td><span class="badge ${daysClass}">${daysText}</span></td>
                        <td><span class="badge ${statusClass}">${statusText}</span></td>
                        <td style="text-align: center;">
                            <div class="action-group center">
                                <button class="act-btn settle" data-action="settle-demand" data-id="${c.id}" data-case="${escapeHtml(c.case_number)}" data-client="${escapeHtml(c.client_name)}">Settle</button>
                                <button class="act-btn to-lit" data-action="to-litigation" data-id="${c.id}" data-case="${escapeHtml(c.case_number)}" data-client="${escapeHtml(c.client_name)}">To Lit</button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            container.innerHTML = html;
        }

        // Load Demand Cases
        async function loadDemandCases() {
            const result = await apiCall('api/chong_cases.php?phase=demand');
            if (result.cases) {
                demandCasesData = result.cases;
                renderDemandTable(demandCasesData);
                updateDemandAlertBar(demandCasesData);
                updateDemandStats(demandCasesData);
            }
        }

        function updateDemandStats(cases) {
            const total = cases.length;

            // Use deadline_status from API which already calculates days
            const dueIn2Weeks = cases.filter(c => {
                if (!c.deadline_status || c.deadline_status.days === null) return false;
                return c.deadline_status.days >= 0 && c.deadline_status.days <= 14;
            }).length;

            const overdue = cases.filter(c => {
                if (!c.deadline_status || c.deadline_status.days === null) return false;
                return c.deadline_status.days < 0;
            }).length;

            document.getElementById('demandStatTotal').textContent = total;
            document.getElementById('demandStatDue2Weeks').textContent = dueIn2Weeks;
            document.getElementById('demandStatOverdue').textContent = overdue;
        }

        let selectedDemandCaseId = null;

        function renderDemandTable(cases) {
            const tbody = document.getElementById('demandTableBody');
            if (cases.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" style="text-align:center; padding: 40px; color:#8b8fa3;">No demand cases</td></tr>';
                return;
            }

            tbody.innerHTML = cases.map(c => {
                const deadlineStatus = c.deadline_status || {};
                const daysClass = deadlineStatus.class || '';
                const daysText = deadlineStatus.message || '-';

                // Determine row highlight class based on days left
                let rowClass = '';
                if (deadlineStatus.days !== undefined && deadlineStatus.days !== null) {
                    if (deadlineStatus.days < 0) {
                        rowClass = 'row-overdue';  // Red - overdue
                    } else if (deadlineStatus.days <= 14) {
                        rowClass = 'row-critical';  // Yellow - due within 2 weeks
                    }
                }

                const statusBadge = c.status === 'in_progress'
                    ? '<span class="ink-badge" style="background:#dbeafe;color:#1d4ed8;">in progress</span>'
                    : `<span class="ink-badge">${escapeHtml(c.status || 'unpaid')}</span>`;

                // Stage badge
                const stageColors = {
                    'demand_review': { bg: '#f3e8ff', color: '#7c3aed', text: 'Demand Review' },
                    'demand_write': { bg: '#e0e7ff', color: '#4338ca', text: 'Demand Write' },
                    'demand_sent': { bg: '#fef3c7', color: '#b45309', text: 'Demand Sent' },
                    'negotiate': { bg: '#d1fae5', color: '#059669', text: 'Negotiate' }
                };
                const stageInfo = stageColors[c.stage] || { bg: '#f3f4f6', color: '#6b7280', text: c.stage || '-' };
                const stageBadge = c.stage
                    ? `<span class="ink-badge" style="background:${stageInfo.bg};color:${stageInfo.color};">${stageInfo.text}</span>`
                    : '-';

                const isSelected = selectedDemandCaseId === c.id ? 'selected-row' : '';

                return `
                    <tr class="${rowClass} ${isSelected} clickable-row" data-id="${c.id}" data-stage="${c.stage || ''}" style="cursor: pointer;">
                        <td style="width:0;padding:0;border:none;"></td>
                        <td style="font-family: monospace; font-size: 12px;">${escapeHtml(c.case_number)}</td>
                        <td style="font-weight: 500;">${escapeHtml(c.client_name)}</td>
                        <td>${escapeHtml(c.case_type || '-')}</td>
                        <td>${stageBadge}</td>
                        <td>${formatDate(c.assigned_date)}</td>
                        <td>${formatDate(c.demand_deadline)}</td>
                        <td class="${daysClass}">${daysText}</td>
                        <td>${statusBadge}</td>
                        <td class="action-cell" style="text-align: center;">
                            <div class="action-group center">
                                <button class="act-btn edit" data-action="edit" data-id="${c.id}">Edit</button>
                                <button class="act-btn settle" data-action="settle-demand" data-id="${c.id}" data-case="${escapeHtml(c.case_number)}" data-client="${escapeHtml(c.client_name)}">Settle</button>
                                <button class="act-btn to-lit" data-action="to-litigation" data-id="${c.id}" data-case="${escapeHtml(c.case_number)}" data-client="${escapeHtml(c.client_name)}">To Lit</button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            // Add row click event for selecting
            tbody.querySelectorAll('.clickable-row').forEach(row => {
                row.addEventListener('click', function(e) {
                    if (!e.target.closest('.action-cell') && !e.target.closest('button')) {
                        const id = parseInt(this.dataset.id);
                        const stage = this.dataset.stage;
                        selectDemandRow(id, stage);
                    }
                });
            });

            // Add action button click events
            tbody.querySelectorAll('.act-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const action = this.dataset.action;
                    const id = this.dataset.id;
                    const caseNum = this.dataset.case;
                    const client = this.dataset.client;

                    if (action === 'edit') {
                        openEditCaseModal(parseInt(id));
                    } else if (action === 'settle-demand') {
                        openSettleDemandModal(id, caseNum, client);
                    } else if (action === 'to-litigation') {
                        openToLitigationModal(id, caseNum, client);
                    }
                });
            });

            // Update footer using deadline_status from API
            const dueIn2Weeks = cases.filter(c => {
                if (!c.deadline_status || c.deadline_status.days === null) return false;
                return c.deadline_status.days >= 0 && c.deadline_status.days <= 14;
            }).length;
            const overdue = cases.filter(c => {
                if (!c.deadline_status || c.deadline_status.days === null) return false;
                return c.deadline_status.days < 0;
            }).length;

            document.getElementById('demandFooterLeft').textContent = `${cases.length} demand cases`;
            document.getElementById('demandFooterRight').textContent = `Due in 2 Weeks: ${dueIn2Weeks} · Overdue: ${overdue}`;
        }

        function selectDemandRow(id, stage) {
            selectedDemandCaseId = id;

            // Update row highlighting
            document.querySelectorAll('#demandTableBody .clickable-row').forEach(row => {
                row.classList.remove('selected-row');
                if (parseInt(row.dataset.id) === id) {
                    row.classList.add('selected-row');
                }
            });

            // Update Stage card
            const stageLabels = {
                'demand_review': 'Demand Review',
                'demand_write': 'Demand Write',
                'demand_sent': 'Demand Sent',
                'negotiate': 'Negotiate'
            };
            const stageText = stageLabels[stage] || stage || 'Not set';
            document.getElementById('demandStatStage').textContent = stageText;
            document.getElementById('demandStatStage').style.color = stage ? '#1a1a2e' : '#8b8fa3';
            document.getElementById('demandStatStage').style.fontSize = '16px';
        }

        function updateDemandAlertBar(cases) {
            const bar = document.getElementById('demandAlertBar');
            if (!bar) return; // Element doesn't exist, skip

            const overdue = cases.filter(c => c.deadline_status && c.deadline_status.days < 0).length;
            const critical = cases.filter(c => c.deadline_status && c.deadline_status.days >= 0 && c.deadline_status.days <= 14).length;

            if (overdue > 0) {
                bar.className = 'urgent-bar critical';
                bar.innerHTML = `<span>&#9888;</span> ${overdue} case(s) OVERDUE - Immediate action required!`;
                bar.style.display = 'flex';
            } else if (critical > 0) {
                bar.className = 'urgent-bar red';
                bar.innerHTML = `<span>&#9888;</span> ${critical} case(s) due within 2 weeks`;
                bar.style.display = 'flex';
            } else {
                bar.style.display = 'none';
            }
        }

        async function deleteDemandCase(caseId) {
            if (!confirm('Are you sure you want to delete this case?')) return;

            const result = await apiCall(`api/chong_cases.php?id=${caseId}`, 'DELETE', { csrf_token: csrfToken });
            if (result.success) {
                showToast('Case deleted', 'success');
                loadDemandCases();
            } else {
                showToast(result.error || 'Failed to delete', 'error');
            }
        }

        // Load Litigation Cases
        async function loadLitigationCases() {
            const result = await apiCall('api/chong_cases.php?phase=litigation');
            if (result.cases) {
                litigationCasesData = result.cases;
                renderLitigationTable(litigationCasesData);
                updateLitigationStats(litigationCasesData);
            }
        }

        function updateLitigationStats(cases) {
            const total = cases.length;
            const active = cases.filter(c => c.status === 'active' || c.status === 'in_progress').length;
            const settled = cases.filter(c => c.status === 'settled' || c.status === 'closed').length;

            // Calculate average duration
            let totalDays = 0;
            let countWithDuration = 0;
            cases.forEach(c => {
                if (c.litigation_start_date) {
                    const startDate = new Date(c.litigation_start_date);
                    const endDate = c.litigation_settled_date ? new Date(c.litigation_settled_date) : new Date();
                    const days = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24));
                    totalDays += days;
                    countWithDuration++;
                }
            });
            const avgDuration = countWithDuration > 0 ? Math.round(totalDays / countWithDuration) : 0;

            document.getElementById('litStatTotal').textContent = total;
            document.getElementById('litStatActive').textContent = active;
            document.getElementById('litStatSettled').textContent = settled;
            document.getElementById('litStatAvgDuration').textContent = avgDuration + 'd';
        }

        function renderLitigationTable(cases) {
            const tbody = document.getElementById('litigationTableBody');
            if (cases.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; color:#6b7280; padding: 40px;">No litigation cases</td></tr>';
                return;
            }

            tbody.innerHTML = cases.map(c => {
                const duration = c.litigation_start_date ? Math.floor((new Date() - new Date(c.litigation_start_date)) / (1000 * 60 * 60 * 24)) + ' days' : '-';
                const statusBadge = c.status === 'in_progress'
                    ? '<span class="ink-badge" style="background:#dbeafe;color:#1d4ed8;">in progress</span>'
                    : `<span class="ink-badge">${escapeHtml(c.status)}</span>`;

                return `
                    <tr class="clickable-row" data-id="${c.id}" style="cursor:pointer;">
                        <td style="width:0;padding:0;border:none;"></td>
                        <td style="font-family: monospace; font-size: 12px;">${escapeHtml(c.case_number)}</td>
                        <td style="font-weight: 500;">${escapeHtml(c.client_name)}</td>
                        <td>${formatDate(c.litigation_start_date)}</td>
                        <td>${duration}</td>
                        <td style="text-align: right;">${formatCurrency(c.presuit_offer || 0)}</td>
                        <td>${statusBadge}</td>
                        <td class="action-cell" style="text-align: center;">
                            <div class="action-group center">
                                <button class="act-btn settle" data-action="settle-litigation" data-id="${c.id}" data-case="${escapeHtml(c.case_number)}" data-client="${escapeHtml(c.client_name)}" data-presuit="${c.presuit_offer || 0}">Settle</button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            // Add row click event for editing
            tbody.querySelectorAll('.clickable-row').forEach(row => {
                row.addEventListener('click', function(e) {
                    if (!e.target.closest('.action-cell') && !e.target.closest('button')) {
                        const id = parseInt(this.dataset.id);
                        openEditLitigationModal(id);
                    }
                });
            });

            // Add action button click events
            tbody.querySelectorAll('.act-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const action = this.dataset.action;
                    const id = this.dataset.id;
                    const caseNum = this.dataset.case;
                    const client = this.dataset.client;
                    const presuit = this.dataset.presuit;

                    if (action === 'settle-litigation') {
                        openSettleLitigationModal(parseInt(id), caseNum, client, parseFloat(presuit));
                    }
                });
            });

            // Update footer
            const active = cases.filter(c => c.status === 'active' || c.status === 'in_progress').length;
            const settled = cases.filter(c => c.status === 'settled' || c.status === 'closed').length;
            document.getElementById('litFooterLeft').textContent = `${cases.length} litigation cases`;
            document.getElementById('litFooterRight').textContent = `Active: ${active} · Settled: ${settled}`;
        }

        async function deleteLitigationCase(caseId) {
            if (!confirm('Are you sure you want to delete this case?')) return;

            const result = await apiCall(`api/chong_cases.php?id=${caseId}`, 'DELETE', { csrf_token: csrfToken });
            if (result.success) {
                showToast('Case deleted', 'success');
                loadLitigationCases();
                loadDashboardStats();
            } else {
                showToast(result.error || 'Failed to delete', 'error');
            }
        }

        function openAddLitigationModal() {
            // Reset and open the new demand modal for litigation
            document.getElementById('newDemandForm').reset();
            document.getElementById('newDemandPhase').value = 'litigation';
            document.querySelector('#newDemandModal .modal-header h2').textContent = 'Add New Litigation Case';

            // Set default dates
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('newDemandAssignedDate').value = today;

            document.getElementById('newDemandModal').classList.remove('hidden');
        }

        function openEditLitigationModal(caseId) {
            const c = litigationCasesData.find(item => item.id == caseId);
            if (!c) return;

            // Populate existing edit modal
            document.getElementById('editCaseId').value = c.id;
            document.getElementById('editCaseNumber').value = c.case_number || '';
            document.getElementById('editClientName').value = c.client_name || '';
            document.getElementById('editPhase').value = 'litigation';
            document.getElementById('editMonth').value = c.month || '';
            document.getElementById('editSettled').value = c.settled || '';
            document.getElementById('editDiscLegalFee').value = c.discounted_legal_fee || '';
            document.getElementById('editPresuitOffer').value = c.presuit_offer || '';
            document.getElementById('editResolutionType').value = c.resolution_type || '';

            toggleEditPhaseFields();
            calculateEditCommission();

            document.getElementById('editCaseModal').classList.remove('hidden');
        }

        // Load All Cases
        // ============================================
        // COMMISSIONS TAB (Settled Cases Only)
        // ============================================
        function setCommissionFilter(filterType, value, btn) {
            // Update UI - remove active from siblings, add to clicked
            const chips = btn.parentElement.querySelectorAll(`.f-chip[data-filter="${filterType}"]`);
            chips.forEach(c => c.classList.remove('active'));
            btn.classList.add('active');

            // Update status variable
            currentCommissionStatus = value;

            // Reload data
            loadCommissions();
        }

        async function loadCommissions() {
            const status = currentCommissionStatus;
            const year = document.getElementById('commissionYearFilter').value;
            const monthFilter = document.getElementById('commissionMonthFilter').value;

            // Only load settled cases for commissions
            let url = `api/chong_cases.php?phase=settled&status=${status}`;
            if (year !== 'all') url += `&year=${year}`;

            const result = await apiCall(url);
            if (result.cases) {
                // Filter by month if specified
                if (monthFilter !== 'all') {
                    commissionsData = result.cases.filter(c => c.month && c.month.startsWith(monthFilter));
                } else {
                    commissionsData = result.cases;
                }
                renderCommissionsTable(commissionsData);
            }
        }

        function renderCommissionsTable(cases) {
            const tbody = document.getElementById('commissionsTableBody');

            // Calculate stats
            const totalCommission = cases.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);
            const paidCommission = cases.filter(c => c.status === 'paid').reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);
            const unpaidCommission = cases.filter(c => c.status === 'unpaid').reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);

            // Update stats cards
            document.getElementById('commStatCases').textContent = cases.length;
            document.getElementById('commStatTotal').textContent = formatCurrency(totalCommission);
            document.getElementById('commStatPaid').textContent = formatCurrency(paidCommission);
            document.getElementById('commStatUnpaid').textContent = formatCurrency(unpaidCommission);

            if (cases.length === 0) {
                tbody.innerHTML = '<tr><td colspan="11" style="text-align:center; padding: 40px; color:#8b8fa3;">No commissions found</td></tr>';
                return;
            }

            tbody.innerHTML = cases.map(c => {
                const statusBadge = c.status === 'paid'
                    ? '<span class="ink-badge paid">PAID</span>'
                    : '<span class="ink-badge unpaid">UNPAID</span>';

                const settled = parseFloat(c.settled || 0);
                const preSuitOffer = parseFloat(c.pre_suit_offer || 0);
                const difference = settled - preSuitOffer;
                const legalFee = parseFloat(c.legal_fee || 0);
                const discFee = parseFloat(c.discounted_legal_fee || 0);
                const commission = parseFloat(c.commission || 0);

                // Resolution type badge color
                const resType = c.resolution_type || '-';
                let resBadge = '';
                if (resType.toLowerCase().includes('demand')) {
                    resBadge = '<span style="display:inline-block;width:8px;height:8px;background:#3b82f6;border-radius:50%;margin-right:6px;"></span>';
                } else if (resType.toLowerCase().includes('mediation')) {
                    resBadge = '<span style="display:inline-block;width:8px;height:8px;background:#d97706;border-radius:50%;margin-right:6px;"></span>';
                } else if (resType.toLowerCase().includes('arb')) {
                    resBadge = '<span style="display:inline-block;width:8px;height:8px;background:#8b5cf6;border-radius:50%;margin-right:6px;"></span>';
                }

                return `
                    <tr>
                        <td style="width:0;padding:0;border:none;"></td>
                        <td>${resBadge}${escapeHtml(resType)}</td>
                        <td>${escapeHtml(c.client_name)}</td>
                        <td style="text-align:right; font-weight:600;">${formatCurrency(settled)}</td>
                        <td style="text-align:right; color:#8b8fa3;">${preSuitOffer > 0 ? formatCurrency(preSuitOffer) : '—'}</td>
                        <td style="text-align:right;">${difference > 0 ? formatCurrency(difference) : '—'}</td>
                        <td style="text-align:right;">${formatCurrency(legalFee)}</td>
                        <td style="text-align:right;">${formatCurrency(discFee)}</td>
                        <td style="text-align:right; font-weight:700; color:#0d9488;">${formatCurrency(commission)}</td>
                        <td>${escapeHtml(c.month || '-')}</td>
                        <td style="text-align:center;">${statusBadge}</td>
                        <td style="text-align:center;">
                            <div class="action-group center">
                                <button class="act-icon edit" onclick="editCommission(${c.id})" title="Edit">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button class="act-icon danger" onclick="deleteCommission(${c.id})" title="Delete">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            // Update table footer
            document.getElementById('commTableCount').textContent = `${cases.length} cases`;
            document.getElementById('commTableTotal').textContent = formatCurrency(totalCommission);
            document.getElementById('commTablePaid').textContent = formatCurrency(paidCommission);
            document.getElementById('commTableUnpaid').textContent = formatCurrency(unpaidCommission);
        }

        function filterCommissions() {
            const search = document.getElementById('commissionSearch').value.toLowerCase();
            const filtered = commissionsData.filter(c =>
                c.case_number.toLowerCase().includes(search) ||
                c.client_name.toLowerCase().includes(search)
            );
            renderCommissionsTable(filtered);
        }

        function sortCommissions(column) {
            if (commissionsSortColumn === column) {
                commissionsSortDir = commissionsSortDir === 'asc' ? 'desc' : 'asc';
            } else {
                commissionsSortColumn = column;
                commissionsSortDir = 'asc';
            }

            document.querySelectorAll('#commissionsTable th.sortable').forEach(th => {
                th.classList.remove('asc', 'desc');
                if (th.dataset.sort === column) {
                    th.classList.add(commissionsSortDir);
                }
            });

            const numericCols = ['settled', 'commission'];
            commissionsData.sort((a, b) => {
                let valA = a[column] ?? '';
                let valB = b[column] ?? '';

                if (numericCols.includes(column)) {
                    valA = parseFloat(valA) || 0;
                    valB = parseFloat(valB) || 0;
                } else {
                    valA = String(valA).toLowerCase();
                    valB = String(valB).toLowerCase();
                }

                if (valA < valB) return commissionsSortDir === 'asc' ? -1 : 1;
                if (valA > valB) return commissionsSortDir === 'asc' ? 1 : -1;
                return 0;
            });

            filterCommissions();
        }

        function editCommission(caseId) {
            const c = commissionsData.find(item => item.id == caseId);
            if (!c) return;

            // Populate and show edit modal
            document.getElementById('editCommCaseId').value = c.id;
            document.getElementById('editCommClientName').value = c.client_name || '';
            document.getElementById('editCommResolutionType').value = c.resolution_type || '';
            document.getElementById('editCommSettled').value = c.settled || '';
            document.getElementById('editCommPreSuitOffer').value = c.pre_suit_offer || '';
            document.getElementById('editCommLegalFee').value = c.legal_fee || '';
            document.getElementById('editCommDiscountedFee').value = c.discounted_legal_fee || '';
            document.getElementById('editCommCommission').value = c.commission || '';
            document.getElementById('editCommMonth').value = c.month || '';

            document.getElementById('editCommissionModal').classList.add('show');
        }

        async function saveCommission() {
            const caseId = document.getElementById('editCommCaseId').value;
            const data = {
                client_name: document.getElementById('editCommClientName').value,
                resolution_type: document.getElementById('editCommResolutionType').value,
                settled: document.getElementById('editCommSettled').value,
                pre_suit_offer: document.getElementById('editCommPreSuitOffer').value,
                legal_fee: document.getElementById('editCommLegalFee').value,
                discounted_legal_fee: document.getElementById('editCommDiscountedFee').value,
                commission: document.getElementById('editCommCommission').value,
                month: document.getElementById('editCommMonth').value,
                csrf_token: csrfToken
            };

            const result = await apiCall(`api/chong_cases.php?id=${caseId}`, 'PUT', data);

            if (result.success) {
                showToast('Commission updated', 'success');
                document.getElementById('editCommissionModal').classList.remove('show');
                loadCommissions();
                loadDashboardStats();
            } else {
                showToast(result.error || 'Failed to update', 'error');
            }
        }

        async function deleteCommission(caseId) {
            if (!confirm('Are you sure you want to delete this commission? This cannot be undone.')) return;

            const result = await apiCall(`api/chong_cases.php?id=${caseId}`, 'DELETE', { csrf_token: csrfToken });

            if (result.success) {
                showToast('Commission deleted', 'success');
                loadCommissions();
                loadDashboardStats();
            } else {
                showToast(result.error || 'Failed to delete', 'error');
            }
        }

        function exportCommissionsToExcel() {
            const data = commissionsData.map(c => ({
                'Case #': c.case_number,
                'Client': c.client_name,
                'Resolution': c.resolution_type || '',
                'Settled': c.settled || 0,
                'Commission': c.commission || 0,
                'Month': c.month || '',
                'Status': c.status
            }));

            let csv = Object.keys(data[0] || {}).join(',') + '\n';
            data.forEach(row => {
                csv += Object.values(row).map(v => `"${v}"`).join(',') + '\n';
            });

            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `commissions_${new Date().toISOString().slice(0,10)}.csv`;
            a.click();
        }

        function setDemandFilter(filter, btn) {
            currentDemandFilter = filter;
            // Update active state on chips - reset all styles first
            document.querySelectorAll('#content-demand .f-chip').forEach(chip => {
                chip.classList.remove('active');
                // Reset to default styles based on data-filter
                const chipFilter = chip.dataset.filter;
                if (chipFilter === 'due2weeks') {
                    chip.style.background = '#fef3c7';
                    chip.style.color = '#b45309';
                    chip.style.borderColor = '#fde68a';
                } else if (chipFilter === 'overdue') {
                    chip.style.background = '#fef2f2';
                    chip.style.color = '#b91c1c';
                    chip.style.borderColor = '#fecaca';
                }
            });
            btn.classList.add('active');
            // Set active style
            if (filter === 'overdue') {
                btn.style.background = '#b91c1c';
                btn.style.color = '#fff';
                btn.style.borderColor = '#b91c1c';
            } else if (filter === 'due2weeks') {
                btn.style.background = '#b45309';
                btn.style.color = '#fff';
                btn.style.borderColor = '#b45309';
            }
            filterDemandCases();
        }

        function clickDemandStat(filter) {
            const chip = document.querySelector(`#content-demand .f-chip[data-filter="${filter}"]`);
            if (chip) {
                setDemandFilter(filter, chip);
            }
        }

        function filterDemandCases() {
            const search = document.getElementById('demandSearch').value.toLowerCase();
            let filtered = demandCasesData.filter(c =>
                c.case_number.toLowerCase().includes(search) ||
                c.client_name.toLowerCase().includes(search)
            );

            // Apply filter using deadline_status from API
            if (currentDemandFilter === 'due2weeks') {
                filtered = filtered.filter(c => {
                    if (!c.deadline_status || c.deadline_status.days === null) return false;
                    return c.deadline_status.days >= 0 && c.deadline_status.days <= 14;
                });
            } else if (currentDemandFilter === 'overdue') {
                filtered = filtered.filter(c => {
                    if (!c.deadline_status || c.deadline_status.days === null) return false;
                    return c.deadline_status.days < 0;
                });
            }

            renderDemandTable(filtered);
        }

        function setLitigationFilter(filter, btn) {
            currentLitigationFilter = filter;
            // Update active state on chips
            document.querySelectorAll('#content-litigation .f-chip').forEach(chip => {
                chip.classList.remove('active');
            });
            btn.classList.add('active');
            filterLitigationCases();
        }

        function filterLitigationCases() {
            const search = document.getElementById('litigationSearch').value.toLowerCase();
            let filtered = litigationCasesData.filter(c =>
                c.case_number.toLowerCase().includes(search) ||
                c.client_name.toLowerCase().includes(search)
            );

            // Apply filter
            if (currentLitigationFilter === 'active') {
                filtered = filtered.filter(c => c.status === 'active' || c.status === 'in_progress');
            } else if (currentLitigationFilter === 'settled') {
                filtered = filtered.filter(c => c.status === 'settled' || c.status === 'closed');
            }

            renderLitigationTable(filtered);
        }

        // Sort Demand Cases
        function sortDemandCases(column) {
            if (demandSortColumn === column) {
                demandSortDir = demandSortDir === 'asc' ? 'desc' : 'asc';
            } else {
                demandSortColumn = column;
                demandSortDir = 'asc';
            }

            // Update header styles
            document.querySelectorAll('#demandTable th.sortable').forEach(th => {
                th.classList.remove('asc', 'desc');
                if (th.dataset.sort === column) {
                    th.classList.add(demandSortDir);
                }
            });

            // Sort the data
            demandCasesData.sort((a, b) => {
                let valA = a[column];
                let valB = b[column];

                // Handle null/undefined
                if (valA == null) valA = '';
                if (valB == null) valB = '';

                // Calculate days_left on the fly
                if (column === 'days_left') {
                    valA = a.demand_deadline ? Math.ceil((new Date(a.demand_deadline) - new Date()) / (1000 * 60 * 60 * 24)) : 999;
                    valB = b.demand_deadline ? Math.ceil((new Date(b.demand_deadline) - new Date()) / (1000 * 60 * 60 * 24)) : 999;
                }

                // Numeric comparison for certain columns
                if (['days_left'].includes(column)) {
                    valA = parseFloat(valA) || 0;
                    valB = parseFloat(valB) || 0;
                } else {
                    valA = String(valA).toLowerCase();
                    valB = String(valB).toLowerCase();
                }

                if (valA < valB) return demandSortDir === 'asc' ? -1 : 1;
                if (valA > valB) return demandSortDir === 'asc' ? 1 : -1;
                return 0;
            });

            filterDemandCases();
        }

        // Sort Litigation Cases
        function sortLitigationCases(column) {
            if (litigationSortColumn === column) {
                litigationSortDir = litigationSortDir === 'asc' ? 'desc' : 'asc';
            } else {
                litigationSortColumn = column;
                litigationSortDir = 'asc';
            }

            // Update header styles
            document.querySelectorAll('#litigationTable th.sortable').forEach(th => {
                th.classList.remove('asc', 'desc');
                if (th.dataset.sort === column) {
                    th.classList.add(litigationSortDir);
                }
            });

            // Sort the data
            litigationCasesData.sort((a, b) => {
                let valA = a[column];
                let valB = b[column];

                // Handle null/undefined
                if (valA == null) valA = '';
                if (valB == null) valB = '';

                // Numeric comparison for certain columns
                if (['presuit_offer', 'litigation_duration_days'].includes(column)) {
                    valA = parseFloat(valA) || 0;
                    valB = parseFloat(valB) || 0;
                } else {
                    valA = String(valA).toLowerCase();
                    valB = String(valB).toLowerCase();
                }

                if (valA < valB) return litigationSortDir === 'asc' ? -1 : 1;
                if (valA > valB) return litigationSortDir === 'asc' ? 1 : -1;
                return 0;
            });

            filterLitigationCases();
        }

        // Sort Traffic Cases
        function sortTrafficCases(column) {
            if (trafficSortColumn === column) {
                trafficSortDir = trafficSortDir === 'asc' ? 'desc' : 'asc';
            } else {
                trafficSortColumn = column;
                trafficSortDir = 'asc';
            }

            // Update header styles
            document.querySelectorAll('#trafficTable th.sortable').forEach(th => {
                th.classList.remove('asc', 'desc');
                if (th.dataset.sort === column) {
                    th.classList.add(trafficSortDir);
                }
            });

            // Sort the data
            trafficCasesData.sort((a, b) => {
                let valA = a[column];
                let valB = b[column];

                // Handle null/undefined
                if (valA == null) valA = '';
                if (valB == null) valB = '';

                // Numeric comparison for certain columns
                if (['commission'].includes(column)) {
                    valA = parseFloat(valA) || 0;
                    valB = parseFloat(valB) || 0;
                } else if (column === 'court_date') {
                    // Date comparison
                    valA = valA ? new Date(valA).getTime() : 0;
                    valB = valB ? new Date(valB).getTime() : 0;
                } else {
                    valA = String(valA).toLowerCase();
                    valB = String(valB).toLowerCase();
                }

                if (valA < valB) return trafficSortDir === 'asc' ? -1 : 1;
                if (valA > valB) return trafficSortDir === 'asc' ? 1 : -1;
                return 0;
            });

            filterTrafficCases();
        }

        // ============================================
        // Messages / Notifications Functions
        // ============================================

        async function loadUnreadCount() {
            const result = await apiCall('api/messages.php');
            if (result.unread_count !== undefined) {
                const badge = document.getElementById('notifBadge');
                if (badge) {
                    badge.textContent = result.unread_count;
                    badge.style.display = result.unread_count > 0 ? 'inline-flex' : 'none';
                }
                // Also store admin user ID
                if (result.messages && result.messages.length > 0) {
                    const adminMsg = result.messages.find(m => m.direction === 'received');
                    if (adminMsg) adminUserId = adminMsg.from_user_id;
                }
            }
        }

        async function loadMessages() {
            const result = await apiCall('api/messages.php');
            if (result.messages) {
                messagesData = result.messages;
                // Update unread badge
                const unread = result.unread_count || 0;
                const badge = document.getElementById('notifBadge');
                if (badge) {
                    badge.textContent = unread;
                    badge.style.display = unread > 0 ? 'inline-flex' : 'none';
                }
                // Find admin user ID for sending messages
                if (!adminUserId && messagesData.length > 0) {
                    const adminMsg = messagesData.find(m => m.direction === 'received');
                    if (adminMsg) adminUserId = adminMsg.from_user_id;
                }
                renderNotifications(messagesData, notifCurrentFilter);
            }
        }

        function renderNotifications(messages, filter) {
            // Filter messages
            let filtered = messages;
            if (filter === 'unread') {
                filtered = messages.filter(m => m.direction === 'received' && !m.is_read);
            } else if (filter === 'sent') {
                filtered = messages.filter(m => m.direction === 'sent');
            } else if (filter === 'read') {
                filtered = messages.filter(m => m.direction === 'received' && m.is_read);
            }

            // Stats
            const totalCount = messages.length;
            const unreadCount = messages.filter(m => m.direction === 'received' && !m.is_read).length;
            const sentCount = messages.filter(m => m.direction === 'sent').length;

            // Update Quick Stats
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

            // Build table rows
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
                filtered.forEach(m => {
                    const isUnread = m.direction === 'received' && !m.is_read;
                    const rowClass = isUnread ? 'unread' : '';

                    // Direction badge
                    const dirBadge = m.direction === 'sent'
                        ? '<span class="dir-badge sent">Sent</span>'
                        : '<span class="dir-badge received">Received</span>';

                    // Unread dot
                    const dot = isUnread
                        ? '<div class="unread-dot"></div>'
                        : '';

                    // Time
                    const timeStr = formatRelativeTime(m.created_at);

                    // From/To
                    const fromTo = m.direction === 'received'
                        ? escapeHtml(m.from_name)
                        : 'To: ' + escapeHtml(m.to_name);

                    // Delete button (only for received messages)
                    const deleteBtn = m.direction === 'received' ? `
                        <button class="act-icon danger" onclick="event.stopPropagation(); deleteMessage(${m.id})" title="Delete">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    ` : '';

                    html += `
                        <tr class="${rowClass}" onclick="viewMessage(${m.id})" style="cursor:pointer;">
                            <td style="width:24px;padding:10px 6px 10px 14px;">${dot}</td>
                            <td>${dirBadge}</td>
                            <td>${fromTo}</td>
                            <td class="td-subject">${escapeHtml(m.subject || '(No subject)')}</td>
                            <td class="td-time">${timeStr}</td>
                            <td style="text-align:center;">
                                <div class="action-group">
                                    <button class="act-icon" onclick="event.stopPropagation(); viewMessage(${m.id})" title="View">
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
            if (footLeft) footLeft.textContent = `${filtered.length} message${filtered.length !== 1 ? 's' : ''}`;
            if (footRight) footRight.textContent = `${unreadCount} unread`;
        }

        function filterNotifications(filter) {
            notifCurrentFilter = filter;

            // Update chip active state
            document.querySelectorAll('.notif-filters .f-chip').forEach(chip => {
                chip.classList.toggle('active', chip.dataset.filter === filter);
            });

            // Re-render with current data
            renderNotifications(messagesData, filter);
        }

        function formatRelativeTime(dateStr) {
            const date = new Date(dateStr);
            const now = new Date();
            const diff = now - date;
            const mins = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);

            if (mins < 1) return 'Just now';
            if (mins < 60) return `${mins}m ago`;
            if (hours < 24) return `${hours}h ago`;
            if (days === 1) return 'Yesterday';
            if (days < 7) return `${days}d ago`;
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }

        async function viewMessage(id) {
            const msg = messagesData.find(m => m.id == id);
            if (!msg) return;

            currentMessageId = id;

            document.getElementById('viewMessageSubject').textContent = msg.subject;
            document.getElementById('viewMessageFrom').textContent = msg.direction === 'received'
                ? 'From: ' + msg.from_name
                : 'To: ' + msg.to_name;
            document.getElementById('viewMessageDate').textContent = new Date(msg.created_at).toLocaleString();
            document.getElementById('viewMessageBody').textContent = msg.message;

            // Show/hide reply button based on direction
            const replyBtn = document.getElementById('replyBtn');
            replyBtn.style.display = msg.direction === 'received' ? 'inline-flex' : 'none';

            openModal('viewMessageModal');

            // Mark as read if unread
            if (msg.direction === 'received' && !msg.is_read) {
                await markMessageRead(id);
            }
        }

        async function markMessageRead(id) {
            const result = await apiCall('api/messages.php', 'PUT', { id: id });
            if (result.success) {
                // Update local data
                const msg = messagesData.find(m => m.id == id);
                if (msg) msg.is_read = 1;

                // Update unread count badge
                const unread = messagesData.filter(m => m.direction === 'received' && !m.is_read).length;
                const badge = document.getElementById('notifBadge');
                if (badge) {
                    badge.textContent = unread;
                    badge.style.display = unread > 0 ? 'inline-flex' : 'none';
                }

                // Re-render to update visual state
                renderNotifications(messagesData, notifCurrentFilter);
            }
        }

        async function deleteMessage(id) {
            if (!confirm('Are you sure you want to delete this message?')) return;

            const result = await apiCall(`api/messages.php?id=${id}`, 'DELETE');
            if (result.success) {
                showToast('Message deleted', 'success');
                // Remove from local data
                messagesData = messagesData.filter(m => m.id != id);
                renderNotifications(messagesData, notifCurrentFilter);
            } else {
                showToast(result.error || 'Failed to delete message', 'error');
            }
        }

        async function deleteCurrentMessage() {
            if (!currentMessageId) return;
            closeModal('viewMessageModal');
            await deleteMessage(currentMessageId);
        }

        async function markAllRead() {
            const result = await apiCall('api/messages.php', 'PUT', { mark_all: true });
            if (result.success) {
                showToast('All messages marked as read', 'success');
                loadMessages();
            }
        }

        function openNewMessageModal() {
            document.getElementById('newMessageForm').reset();
            openModal('newMessageModal');
        }

        function replyToMessage() {
            const msg = messagesData.find(m => m.id == currentMessageId);
            if (!msg) return;

            closeModal('viewMessageModal');

            // Pre-fill subject with Re:
            const form = document.getElementById('newMessageForm');
            const subjectInput = form.querySelector('[name="subject"]');
            if (!msg.subject.startsWith('Re: ')) {
                subjectInput.value = 'Re: ' + msg.subject;
            } else {
                subjectInput.value = msg.subject;
            }

            // Store the admin user ID to reply to
            adminUserId = msg.from_user_id;

            openModal('newMessageModal');
        }

        async function sendMessage(e) {
            e.preventDefault();

            // Get admin user ID if not set
            if (!adminUserId) {
                // Fetch admin user
                const usersResult = await apiCall('api/users.php');
                if (usersResult.users) {
                    const admin = usersResult.users.find(u => u.role === 'admin');
                    if (admin) adminUserId = admin.id;
                }
            }

            if (!adminUserId) {
                showToast('Cannot find admin user', 'error');
                return;
            }

            const form = e.target;
            const data = {
                to_user_id: adminUserId,
                subject: form.subject.value,
                message: form.message.value
            };

            const result = await apiCall('api/messages.php', 'POST', data);
            if (result.success) {
                showToast('Message sent successfully', 'success');
                closeModal('newMessageModal');
                loadMessages();
            } else {
                showToast(result.error || 'Failed to send message', 'error');
            }
        }

        // Modal functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('show');
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('show');
                modal.classList.add('hidden');
            }
        }

        function openNewDemandModal() {
            document.getElementById('newDemandForm').reset();
            document.querySelector('#newDemandForm [name="assigned_date"]').value = new Date().toISOString().split('T')[0];
            openModal('newDemandModal');
        }

        // Edit Case Functions
        let currentEditCaseData = null;
        let currentPendingExtension = null;

        async function openEditCaseModal(caseId) {
            // Find case in any of the data arrays
            let caseData = commissionsData.find(c => c.id == caseId) ||
                          demandCasesData.find(c => c.id == caseId) ||
                          litigationCasesData.find(c => c.id == caseId);

            if (!caseData) {
                // Fetch from API if not found
                const result = await apiCall(`api/chong_cases.php?id=${caseId}`);
                if (result.case) {
                    caseData = result.case;
                } else {
                    alert('Case not found');
                    return;
                }
            }

            currentEditCaseData = caseData;

            // Populate form
            document.getElementById('editCaseId').value = caseData.id;
            document.getElementById('editCaseNumber').value = caseData.case_number || '';
            document.getElementById('editClientName').value = caseData.client_name || '';
            document.getElementById('editPhase').value = caseData.phase || 'demand';
            document.getElementById('editMonth').value = caseData.month || '';
            document.getElementById('editSettled').value = caseData.settled || '';
            document.getElementById('editDiscLegalFee').value = caseData.discounted_legal_fee || '';
            document.getElementById('editPresuitOffer').value = caseData.presuit_offer || '';
            document.getElementById('editResolutionType').value = caseData.resolution_type || '';
            document.getElementById('editAssignedDate').value = caseData.assigned_date || '';
            document.getElementById('editNote').value = caseData.note || '';
            document.getElementById('editStage').value = caseData.stage || '';

            // Show/hide litigation fields and stage row
            toggleEditPhaseFields();
            calculateEditCommission();

            // Setup deadline section for demand cases
            await setupDeadlineSection(caseData);

            openModal('editCaseModal');
        }

        async function setupDeadlineSection(caseData) {
            const deadlineSection = document.getElementById('deadlineSection');
            const deadlineExtensionForm = document.getElementById('deadlineExtensionForm');
            const requestExtensionBtn = document.getElementById('requestExtensionBtn');
            const pendingExtensionAlert = document.getElementById('pendingExtensionAlert');

            // Only show deadline section for demand phase cases
            if (caseData.phase === 'demand' && caseData.demand_deadline) {
                deadlineSection.style.display = 'block';

                // Set current deadline
                document.getElementById('editCurrentDeadline').value = caseData.demand_deadline;

                // Calculate days remaining
                const deadline = new Date(caseData.demand_deadline);
                const today = new Date();
                const daysLeft = Math.ceil((deadline - today) / (1000 * 60 * 60 * 24));

                const daysInput = document.getElementById('editDaysRemaining');
                if (daysLeft < 0) {
                    daysInput.value = `${Math.abs(daysLeft)} days overdue`;
                    daysInput.style.color = '#dc2626';
                } else if (daysLeft <= 14) {
                    daysInput.value = `${daysLeft} days left`;
                    daysInput.style.color = '#dc2626';
                } else if (daysLeft <= 30) {
                    daysInput.value = `${daysLeft} days left`;
                    daysInput.style.color = '#f59e0b';
                } else {
                    daysInput.value = `${daysLeft} days left`;
                    daysInput.style.color = '#059669';
                }

                // Check for pending extension request
                const result = await apiCall('api/deadline_requests.php');
                currentPendingExtension = null;

                if (result.requests) {
                    currentPendingExtension = result.requests.find(r =>
                        r.case_id == caseData.id && r.status === 'pending'
                    );
                }

                if (currentPendingExtension) {
                    pendingExtensionAlert.style.display = 'flex';
                    pendingExtensionAlert.querySelector('.alert-text').textContent =
                        `Pending request: ${currentPendingExtension.current_deadline} → ${currentPendingExtension.requested_deadline}`;
                    requestExtensionBtn.style.display = 'none';
                } else {
                    pendingExtensionAlert.style.display = 'none';
                    requestExtensionBtn.style.display = 'inline-block';
                }

                // Reset extension form
                deadlineExtensionForm.style.display = 'none';
                document.getElementById('editRequestedDeadline').value = '';
                document.getElementById('editExtensionReason').value = '';
            } else {
                deadlineSection.style.display = 'none';
            }
        }

        function showDeadlineExtensionForm() {
            document.getElementById('deadlineExtensionForm').style.display = 'block';
            document.getElementById('requestExtensionBtn').style.display = 'none';

            // Set min date for requested deadline (tomorrow)
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('editRequestedDeadline').min = tomorrow.toISOString().split('T')[0];
        }

        function cancelDeadlineExtension() {
            document.getElementById('deadlineExtensionForm').style.display = 'none';
            document.getElementById('requestExtensionBtn').style.display = 'inline-block';
            document.getElementById('editRequestedDeadline').value = '';
            document.getElementById('editExtensionReason').value = '';
        }

        async function submitDeadlineExtension() {
            const caseId = document.getElementById('editCaseId').value;
            const requestedDeadline = document.getElementById('editRequestedDeadline').value;
            const reason = document.getElementById('editExtensionReason').value;

            if (!requestedDeadline || !reason.trim()) {
                alert('Please provide both a new deadline and a reason for the extension.');
                return;
            }

            const result = await apiCall('api/deadline_requests.php', 'POST', {
                case_id: caseId,
                requested_deadline: requestedDeadline,
                reason: reason.trim()
            });

            if (result.success) {
                showToast('Deadline extension request submitted!', 'success');
                // Refresh the deadline section
                await setupDeadlineSection(currentEditCaseData);
            } else {
                alert('Error: ' + (result.error || 'Failed to submit request'));
            }
        }

        function toggleEditPhaseFields() {
            const phase = document.getElementById('editPhase').value;
            document.getElementById('editLitigationFields').style.display = phase === 'litigation' ? 'block' : 'none';
            document.getElementById('editStageRow').style.display = phase === 'demand' ? 'flex' : 'none';
            calculateEditCommission();
        }

        function calculateEditCommission() {
            const phase = document.getElementById('editPhase').value;
            const discLegalFee = parseFloat(document.getElementById('editDiscLegalFee').value) || 0;
            let commission = 0;

            if (phase === 'demand') {
                // Demand: 5% of Disc. Legal Fee
                commission = discLegalFee * 0.05;
            } else {
                // Litigation: 20% of Disc. Legal Fee
                commission = discLegalFee * 0.20;
            }

            document.getElementById('editCommissionDisplay').textContent = formatCurrency(commission);
        }

        async function submitEditCase(event) {
            event.preventDefault();

            const caseId = document.getElementById('editCaseId').value;
            const phase = document.getElementById('editPhase').value;

            const data = {
                id: caseId,
                case_number: document.getElementById('editCaseNumber').value,
                client_name: document.getElementById('editClientName').value,
                phase: phase,
                month: document.getElementById('editMonth').value,
                settled: parseFloat(document.getElementById('editSettled').value) || 0,
                discounted_legal_fee: parseFloat(document.getElementById('editDiscLegalFee').value) || 0,
                assigned_date: document.getElementById('editAssignedDate').value,
                note: document.getElementById('editNote').value
            };

            if (phase === 'demand') {
                data.stage = document.getElementById('editStage').value;
            }

            if (phase === 'litigation') {
                data.presuit_offer = parseFloat(document.getElementById('editPresuitOffer').value) || 0;
                data.resolution_type = document.getElementById('editResolutionType').value;
            }

            const result = await apiCall('api/cases.php', 'PUT', data);
            if (result.success) {
                closeModal('editCaseModal');
                // Reload all data
                loadDemandCases();
                loadLitigationCases();
                loadCommissions();
                loadDashboard();
                alert('Case updated successfully!');
            } else {
                alert('Error: ' + (result.error || 'Failed to update case'));
            }
        }

        async function deleteCaseFromModal() {
            const caseId = document.getElementById('editCaseId').value;
            const caseNumber = document.getElementById('editCaseNumber').value;

            if (!confirm(`Are you sure you want to delete case "${caseNumber}"?\n\nThis action cannot be undone.`)) {
                return;
            }

            const result = await apiCall(`api/cases.php?id=${caseId}`, 'DELETE', { csrf_token: csrfToken });

            if (result.success) {
                closeModal('editCaseModal');
                showToast('Case deleted successfully', 'success');
                loadDemandCases();
                loadLitigationCases();
                loadCommissions();
                loadDashboard();
            } else {
                showToast(result.error || 'Failed to delete case', 'error');
            }
        }

        function openSettleDemandModal(caseId, caseNumber, clientName) {
            const form = document.getElementById('settleDemandForm');
            form.reset();
            form.querySelector('[name="case_id"]').value = caseId;
            form.querySelector('[name="discounted_legal_fee"]').dataset.userModified = '';
            form.querySelector('[name="legal_fee_display"]').value = '';
            form.querySelector('[name="commission_display"]').value = '';
            document.getElementById('settleDemandCaseInfo').textContent = `${caseNumber} - ${clientName}`;
            openModal('settleDemandModal');
        }

        function openToLitigationModal(caseId, caseNumber, clientName) {
            document.getElementById('toLitigationForm').reset();
            document.querySelector('#toLitigationForm [name="case_id"]').value = caseId;
            document.querySelector('#toLitigationForm [name="litigation_start_date"]').value = new Date().toISOString().split('T')[0];
            document.getElementById('toLitCaseInfo').textContent = `${caseNumber} - ${clientName}`;
            openModal('toLitigationModal');
        }

        function openSettleLitigationModal(caseId, caseNumber, clientName, presuitOffer) {
            const form = document.getElementById('settleLitigationForm');
            form.reset();
            form.querySelector('[name="case_id"]').value = caseId;
            form.querySelector('[name="presuit_offer"]').value = presuitOffer;
            form.querySelector('[name="presuit_offer_hidden"]').value = presuitOffer;
            form.querySelector('[name="discounted_legal_fee"]').dataset.userModified = '';
            document.getElementById('settleLitCaseInfo').textContent = `${caseNumber} - ${clientName}`;
            document.getElementById('resolutionInfo').style.display = 'none';
            document.getElementById('variableFields').style.display = 'none';
            openModal('settleLitigationModal');
        }

        // Commission calculations
        function calculateDemandCommission() {
            const form = document.getElementById('newDemandForm');
            const discLegalFee = parseFloat(form.discounted_legal_fee.value) || 0;
            const commission = discLegalFee * 0.05;
            form.commission_display.value = formatCurrency(commission);
        }

        function calculateSettleDemandCommission() {
            const form = document.getElementById('settleDemandForm');
            const discLegalFee = parseFloat(form.discounted_legal_fee.value) || 0;
            const commission = discLegalFee * 0.05;
            form.commission_display.value = formatCurrency(commission);
        }

        function updateSettleDemandLegalFee() {
            const form = document.getElementById('settleDemandForm');
            const settled = parseFloat(form.settled.value) || 0;
            const legalFee = settled / 3; // 33.33%

            // Display calculated Legal Fee
            form.legal_fee_display.value = formatCurrency(legalFee);

            // Auto-fill Disc. Legal Fee (default = Legal Fee, user can modify)
            if (!form.discounted_legal_fee.dataset.userModified) {
                form.discounted_legal_fee.value = legalFee.toFixed(2);
            }

            // Recalculate commission
            calculateSettleDemandCommission();
        }

        function onResolutionTypeChange() {
            const form = document.getElementById('settleLitigationForm');
            const resType = form.resolution_type.value;
            const config = resolutionConfig[resType];

            if (config) {
                document.getElementById('resolutionInfo').style.display = 'block';
                document.getElementById('infoFeeRate').textContent = config.variable ? 'Manual' : config.feeRate + '%';
                document.getElementById('infoCommRate').textContent = config.variable ? 'Manual' : config.commRate + '%';
                document.getElementById('variableFields').style.display = config.variable ? 'flex' : 'none';
            } else {
                document.getElementById('resolutionInfo').style.display = 'none';
                document.getElementById('variableFields').style.display = 'none';
            }

            calculateLitCommission();
        }

        function calculateLitCommission() {
            const form = document.getElementById('settleLitigationForm');
            const resType = form.resolution_type.value;
            const config = resolutionConfig[resType];
            if (!config) return;

            const settled = parseFloat(form.settled.value) || 0;
            const presuitOffer = parseFloat(form.presuit_offer.value) || 0;

            let difference, legalFee, commission;

            if (config.variable) {
                const manualFeeRate = parseFloat(form.manual_fee_rate.value) || 0;
                const manualCommRate = parseFloat(form.manual_commission_rate.value) || 0;
                difference = settled - presuitOffer;
                legalFee = settled * (manualFeeRate / 100);
            } else if (config.feeRate === 40) {
                difference = settled - presuitOffer;
                legalFee = settled * 0.40;  // 40% of Settled
            } else {
                difference = settled - presuitOffer;
                legalFee = difference / 3;  // 33.33% of (Settled - PreSuit)
            }

            // Auto-fill Disc. Legal Fee if user hasn't modified it
            if (!form.discounted_legal_fee.dataset.userModified) {
                form.discounted_legal_fee.value = legalFee.toFixed(2);
            }

            const discLegalFee = parseFloat(form.discounted_legal_fee.value) || 0;

            // Calculate commission based on Disc. Legal Fee
            if (config.variable) {
                const manualCommRate = parseFloat(form.manual_commission_rate.value) || 0;
                commission = discLegalFee * (manualCommRate / 100);
            } else {
                commission = discLegalFee * 0.20;  // 20% of Disc. Legal Fee
            }

            form.difference_display.value = formatCurrency(difference);
            form.legal_fee_display.value = formatCurrency(legalFee);
            form.commission_display.value = formatCurrency(commission);
        }

        // Form submissions
        async function submitNewDemand(event) {
            event.preventDefault();
            const form = event.target;
            const data = {
                case_number: form.case_number.value,
                client_name: form.client_name.value,
                case_type: form.case_type.value,
                phase: form.phase.value,
                stage: form.stage.value,
                assigned_date: form.assigned_date.value,
                settled: parseFloat(form.settled.value) || 0,
                discounted_legal_fee: parseFloat(form.discounted_legal_fee.value) || 0,
                month: form.month.value,
                note: form.note.value
            };

            const result = await apiCall('api/chong_cases.php', 'POST', data);
            if (result.success) {
                closeModal('newDemandModal');
                loadDashboard();
                loadDemandCases();
                loadCommissions();
                alert('Demand case added successfully!');
            } else {
                alert('Error: ' + (result.error || 'Failed to add case'));
            }
        }

        async function submitSettleDemand(event) {
            event.preventDefault();
            const form = event.target;
            const caseId = form.case_id.value;
            const data = {
                settled: parseFloat(form.settled.value),
                discounted_legal_fee: parseFloat(form.discounted_legal_fee.value),
                month: form.month.value,
                check_received: form.check_received.checked
            };

            const result = await apiCall(`api/chong_cases.php?id=${caseId}&action=settle_demand`, 'PUT', data);
            if (result.success) {
                closeModal('settleDemandModal');
                loadDashboard();
                loadDemandCases();
                loadCommissions();
                alert(`Case settled! Commission: ${formatCurrency(result.commission)}`);
            } else {
                alert('Error: ' + (result.error || 'Failed to settle case'));
            }
        }

        async function submitToLitigation(event) {
            event.preventDefault();
            const form = event.target;
            const caseId = form.case_id.value;
            const data = {
                litigation_start_date: form.litigation_start_date.value,
                presuit_offer: parseFloat(form.presuit_offer.value) || 0,
                note: form.note.value
            };

            const result = await apiCall(`api/chong_cases.php?id=${caseId}&action=to_litigation`, 'PUT', data);
            if (result.success) {
                closeModal('toLitigationModal');
                loadDashboard();
                loadDemandCases();
                loadLitigationCases();
                loadCommissions();
                alert('Case moved to Litigation!');
            } else {
                alert('Error: ' + (result.error || 'Failed to move case'));
            }
        }

        async function submitSettleLitigation(event) {
            event.preventDefault();
            const form = event.target;
            const caseId = form.case_id.value;
            const data = {
                resolution_type: form.resolution_type.value,
                settled: parseFloat(form.settled.value),
                presuit_offer: parseFloat(form.presuit_offer_hidden.value),
                discounted_legal_fee: parseFloat(form.discounted_legal_fee.value),
                manual_fee_rate: parseFloat(form.manual_fee_rate?.value) || 0,
                manual_commission_rate: parseFloat(form.manual_commission_rate?.value) || 0,
                month: form.month.value,
                check_received: form.check_received.checked
            };

            const result = await apiCall(`api/chong_cases.php?id=${caseId}&action=settle_litigation`, 'PUT', data);
            if (result.success) {
                closeModal('settleLitigationModal');
                loadDashboard();
                loadLitigationCases();
                loadCommissions();
                alert(`Case settled! Commission: ${formatCurrency(result.commission)}`);
            } else {
                alert('Error: ' + (result.error || 'Failed to settle case'));
            }
        }

        // Reports
        async function loadReports() {
            const selectedYear = document.getElementById('reportYearFilter')?.value || new Date().getFullYear();
            const result = await apiCall('api/chong_cases.php?phase=settled&status=all');

            if (result.cases) {
                const cases = result.cases;
                const ytdCases = cases.filter(c => c.month && c.month.includes(selectedYear));

                // Calculate stats
                const demandCases = ytdCases.filter(c => c.commission_type === 'demand_5pct');
                const litCases = ytdCases.filter(c => c.commission_type && c.commission_type.startsWith('litigation'));
                const demandComm = demandCases.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);
                const litComm = litCases.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);
                const totalCommission = ytdCases.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);

                document.getElementById('reportTotalCases').textContent = ytdCases.length;
                document.getElementById('reportDemandSettled').textContent = demandCases.length;
                document.getElementById('reportLitSettled').textContent = litCases.length;
                document.getElementById('reportTotalCommission').textContent = formatCurrency(totalCommission);

                // Monthly chart data
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const monthlyData = months.map(m => {
                    const monthCases = ytdCases.filter(c => c.month && c.month.startsWith(m));
                    return monthCases.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);
                });

                renderCommissionChart(months, monthlyData);
                renderBreakdownChart(demandComm, litComm);
                renderRecentSettlements(ytdCases.slice(0, 10));
            }
        }

        let commissionChart = null;
        let breakdownChart = null;

        function renderCommissionChart(labels, data) {
            const ctx = document.getElementById('commissionChart');
            if (!ctx) return;
            if (commissionChart) commissionChart.destroy();

            commissionChart = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Commission',
                        data: data,
                        backgroundColor: '#1a1a2e',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: v => '$' + v.toLocaleString() }
                        }
                    }
                }
            });
        }

        function renderBreakdownChart(demand, litigation) {
            const ctx = document.getElementById('breakdownChart');
            if (!ctx) return;
            if (breakdownChart) breakdownChart.destroy();

            breakdownChart = new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Demand', 'Litigation'],
                    datasets: [{
                        data: [demand, litigation],
                        backgroundColor: ['#3b82f6', '#f59e0b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.label + ': $' + ctx.raw.toLocaleString()
                            }
                        }
                    }
                }
            });
        }

        function renderRecentSettlements(cases) {
            const tbody = document.getElementById('recentSettlementsBody');
            if (!tbody) return;

            if (!cases || cases.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 30px; color: #8b8fa3;">No settlements found</td></tr>';
                return;
            }

            tbody.innerHTML = cases.map(c => {
                const isLit = c.commission_type && c.commission_type.startsWith('litigation');
                const typeBadge = isLit
                    ? '<span class="ink-badge" style="background:#fef3c7;color:#d97706;">Litigation</span>'
                    : '<span class="ink-badge" style="background:#dbeafe;color:#1d4ed8;">Demand</span>';

                return `
                    <tr>
                        <td>${escapeHtml(c.month || '-')}</td>
                        <td style="font-weight: 500;">${escapeHtml(c.client_name)}</td>
                        <td>${typeBadge}</td>
                        <td>${escapeHtml(c.resolution_type || '-')}</td>
                        <td style="text-align: right;">${formatCurrency(c.settled)}</td>
                        <td style="text-align: right; font-weight: 600; color: #059669;">${formatCurrency(c.commission)}</td>
                    </tr>
                `;
            }).join('');
        }

        function exportReportToCSV() {
            const year = document.getElementById('reportYearFilter')?.value || new Date().getFullYear();
            const data = commissionsData.filter(c => c.month && c.month.includes(year));

            if (data.length === 0) {
                alert('No data to export');
                return;
            }

            let csv = 'Month,Client,Type,Resolution,Settled,Commission\n';
            data.forEach(c => {
                const type = c.commission_type?.startsWith('litigation') ? 'Litigation' : 'Demand';
                csv += `"${c.month || ''}","${c.client_name || ''}","${type}","${c.resolution_type || ''}",${c.settled || 0},${c.commission || 0}\n`;
            });

            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `commission_report_${year}.csv`;
            a.click();
        }

        // Excel Export (for Reports tab)
        function exportToExcel() {
            // Export current commission data
            exportCommissionsToExcel();
        }

        // Utility functions
        function formatCurrency(amount) {
            return '$' + parseFloat(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            return d.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function escapeJs(str) {
            if (!str) return '';
            return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"');
        }

        // ============================================
        // Traffic Sub-Tab System
        // ============================================

        let currentTrafficSubTab = 'cases';

        function switchTrafficSubTab(subTab) {
            currentTrafficSubTab = subTab;
            ['cases', 'commission', 'requests'].forEach(t => {
                const panel = document.getElementById('trafficSubContent-' + t);
                if (panel) panel.style.display = t === subTab ? '' : 'none';
                const btn = document.getElementById('trafficSubTab-' + t);
                if (btn) btn.classList.toggle('active', t === subTab);
            });
            if (subTab === 'cases') {
                filterTrafficCases();
            } else if (subTab === 'commission') {
                updateCommissionStats();
                filterCommTraffic();
            } else if (subTab === 'requests') {
                loadAllTrafficRequests();
            }
        }

        // ============================================
        // Traffic Commission Sub-Tab
        // ============================================

        let commTrafficSortColumn = 'court_date';
        let commTrafficSortDir = 'desc';
        let currentCommTrafficFilter = 'all';

        function updateCommissionStats() {
            const commCases = trafficCasesData.filter(c =>
                c.disposition === 'dismissed' || c.disposition === 'amended'
            );
            const total = commCases.reduce((sum, c) => sum + getTrafficCommission(c.disposition), 0);
            const paid = commCases.filter(c => c.paid == 1);
            const unpaid = commCases.filter(c => c.paid != 1);
            const paidTotal = paid.reduce((sum, c) => sum + getTrafficCommission(c.disposition), 0);
            const unpaidTotal = unpaid.reduce((sum, c) => sum + getTrafficCommission(c.disposition), 0);

            document.getElementById('commTotalCommission').textContent = formatCurrency(total);
            document.getElementById('commPaidTotal').textContent = formatCurrency(paidTotal);
            document.getElementById('commUnpaidTotal').textContent = formatCurrency(unpaidTotal);
            document.getElementById('commCaseCount').textContent = commCases.length;

            document.getElementById('commCountAll').textContent = commCases.length;
            document.getElementById('commCountPaid').textContent = paid.length;
            document.getElementById('commCountUnpaid').textContent = unpaid.length;

            populateCommissionDropdowns(commCases);
        }

        function populateCommissionDropdowns(cases) {
            const months = new Set();
            const years = new Set();
            cases.forEach(c => {
                if (c.court_date) {
                    const d = new Date(c.court_date);
                    if (!isNaN(d)) {
                        years.add(d.getFullYear().toString());
                        months.add(d.getMonth().toString());
                    }
                }
            });
            const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

            const monthSelect = document.getElementById('commMonthFilter');
            const curMonth = monthSelect.value;
            monthSelect.innerHTML = '<option value="">All Months</option>' +
                [...months].sort((a,b) => a-b).map(m =>
                    `<option value="${m}"${m === curMonth ? ' selected' : ''}>${monthNames[parseInt(m)]}</option>`
                ).join('');

            const yearSelect = document.getElementById('commYearFilter');
            const curYear = yearSelect.value;
            yearSelect.innerHTML = '<option value="">All Years</option>' +
                [...years].sort().reverse().map(y =>
                    `<option value="${y}"${y === curYear ? ' selected' : ''}>${y}</option>`
                ).join('');
        }

        function setCommTrafficFilter(filter) {
            currentCommTrafficFilter = filter;
            ['all', 'paid', 'unpaid'].forEach(f => {
                const btn = document.getElementById('commFilterBtn-' + f);
                if (btn) btn.classList.toggle('active', f === filter);
            });
            filterCommTraffic();
        }

        function filterCommTraffic() {
            const search = (document.getElementById('commTrafficSearch').value || '').toLowerCase();
            const monthVal = document.getElementById('commMonthFilter').value;
            const yearVal = document.getElementById('commYearFilter').value;

            let filtered = trafficCasesData.filter(c =>
                c.disposition === 'dismissed' || c.disposition === 'amended'
            );

            if (currentCommTrafficFilter === 'paid') {
                filtered = filtered.filter(c => c.paid == 1);
            } else if (currentCommTrafficFilter === 'unpaid') {
                filtered = filtered.filter(c => c.paid != 1);
            }

            if (monthVal !== '') {
                filtered = filtered.filter(c => {
                    if (!c.court_date) return false;
                    return new Date(c.court_date).getMonth().toString() === monthVal;
                });
            }
            if (yearVal) {
                filtered = filtered.filter(c => {
                    if (!c.court_date) return false;
                    return new Date(c.court_date).getFullYear().toString() === yearVal;
                });
            }
            if (search) {
                filtered = filtered.filter(c =>
                    (c.client_name || '').toLowerCase().includes(search) ||
                    (c.court || '').toLowerCase().includes(search) ||
                    (c.referral_source || '').toLowerCase().includes(search)
                );
            }

            renderCommissionTable(filtered);
        }

        function renderCommissionTable(cases) {
            const tbody = document.getElementById('commTrafficTableBody');
            if (!cases || !cases.length) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; color:#6b7280; padding:40px;">No commission records</td></tr>';
                document.getElementById('commTrafficCaseCount').textContent = '0 cases';
                document.getElementById('commTrafficTotal').textContent = '$0.00';
                return;
            }

            const sorted = [...cases].sort((a, b) => {
                let valA = a[commTrafficSortColumn] ?? '';
                let valB = b[commTrafficSortColumn] ?? '';
                if (commTrafficSortColumn === 'commission') {
                    valA = getTrafficCommission(a.disposition);
                    valB = getTrafficCommission(b.disposition);
                } else if (commTrafficSortColumn === 'paid') {
                    valA = a.paid ? 1 : 0;
                    valB = b.paid ? 1 : 0;
                } else if (commTrafficSortColumn === 'court_date' || commTrafficSortColumn === 'resolved_at') {
                    valA = valA ? new Date(valA).getTime() : 0;
                    valB = valB ? new Date(valB).getTime() : 0;
                } else {
                    valA = String(valA).toLowerCase();
                    valB = String(valB).toLowerCase();
                }
                if (valA < valB) return commTrafficSortDir === 'asc' ? -1 : 1;
                if (valA > valB) return commTrafficSortDir === 'asc' ? 1 : -1;
                return 0;
            });

            tbody.innerHTML = sorted.map(c => {
                const commission = getTrafficCommission(c.disposition);
                const dispBadge = c.disposition === 'dismissed'
                    ? '<span class="ink-badge" style="background:#d1fae5;color:#059669;">dismissed</span>'
                    : '<span class="ink-badge" style="background:#fef3c7;color:#d97706;">amended</span>';
                const paidBadge = c.paid == 1
                    ? `<span class="ink-badge paid" style="cursor:pointer;" onclick="event.stopPropagation(); toggleTrafficPaid(${c.id}, 0)">PAID</span>`
                    : `<span class="ink-badge unpaid" style="cursor:pointer;" onclick="event.stopPropagation(); toggleTrafficPaid(${c.id}, 1)">UNPAID</span>`;

                return `
                    <tr class="clickable-row" onclick="openTrafficModal(trafficCasesData.find(x => x.id == ${c.id}))" style="cursor:pointer;">
                        <td style="width:0;padding:0;border:none;"></td>
                        <td>${escapeHtml(c.client_name)}</td>
                        <td>${escapeHtml(c.court || '-')}</td>
                        <td>${c.court_date ? formatDate(c.court_date) : '-'}</td>
                        <td>${c.resolved_at ? formatDate(c.resolved_at) : '-'}</td>
                        <td>${dispBadge}</td>
                        <td>${escapeHtml(c.referral_source || '-')}</td>
                        <td style="text-align:right; font-weight:600; color:#059669;">${formatCurrency(commission)}</td>
                        <td style="text-align:center;">${paidBadge}</td>
                    </tr>
                `;
            }).join('');

            const total = sorted.reduce((sum, c) => sum + getTrafficCommission(c.disposition), 0);
            document.getElementById('commTrafficCaseCount').textContent = sorted.length + ' cases';
            document.getElementById('commTrafficTotal').textContent = formatCurrency(total);
        }

        function sortCommTraffic(column) {
            if (commTrafficSortColumn === column) {
                commTrafficSortDir = commTrafficSortDir === 'asc' ? 'desc' : 'asc';
            } else {
                commTrafficSortColumn = column;
                commTrafficSortDir = 'asc';
            }
            filterCommTraffic();
        }

        async function toggleTrafficPaid(caseId, newPaidValue) {
            const caseData = trafficCasesData.find(c => c.id == caseId);
            if (!caseData) return;

            const data = {
                client_name: caseData.client_name,
                client_phone: caseData.client_phone || '',
                court: caseData.court || '',
                court_date: caseData.court_date || '',
                charge: caseData.charge || '',
                case_number: caseData.case_number || '',
                prosecutor_offer: caseData.prosecutor_offer || '',
                disposition: caseData.disposition || 'pending',
                status: caseData.status || 'active',
                referral_source: caseData.referral_source || '',
                noa_sent_date: caseData.noa_sent_date || '',
                discovery: caseData.discovery == 1,
                paid: newPaidValue == 1,
                note: caseData.note || ''
            };

            data.id = caseId;
            const result = await apiCall('api/traffic.php', 'PUT', data);
            if (result.success) {
                caseData.paid = newPaidValue;
                showToast(newPaidValue ? 'Marked as paid' : 'Marked as unpaid', 'success');
                updateCommissionStats();
                filterCommTraffic();
            } else {
                showToast(result.error || 'Failed to update', 'error');
            }
        }

        function exportTrafficCommissions() {
            const commCases = trafficCasesData.filter(c =>
                c.disposition === 'dismissed' || c.disposition === 'amended'
            );
            if (!commCases.length) { showToast('No data to export', 'error'); return; }

            const data = commCases.map(c => ({
                'Client': c.client_name,
                'Court': c.court || '',
                'Court Date': c.court_date ? formatDate(c.court_date) : '',
                'Requester': c.referral_source || '',
                'Disposition': c.disposition,
                'Commission': getTrafficCommission(c.disposition),
                'Paid': c.paid == 1 ? 'Yes' : 'No'
            }));

            let csv = Object.keys(data[0]).join(',') + '\n';
            data.forEach(row => {
                csv += Object.values(row).map(v => `"${v}"`).join(',') + '\n';
            });

            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `traffic_commissions_${new Date().toISOString().slice(0,10)}.csv`;
            a.click();
            URL.revokeObjectURL(url);
        }

        // ============================================
        // Traffic Requests Sub-Tab
        // ============================================

        let allTrafficRequests = [];
        let pendingTrafficRequests = [];
        let currentRequestFilter = 'all';

        async function loadAllTrafficRequests() {
            try {
                const result = await apiCall('api/traffic_requests.php');
                allTrafficRequests = result.requests || [];
                pendingTrafficRequests = allTrafficRequests.filter(r => r.status === 'pending');

                // Update main nav badge
                const badge = document.getElementById('trafficBadge');
                if (pendingTrafficRequests.length > 0) {
                    badge.textContent = pendingTrafficRequests.length;
                    badge.style.display = 'inline';
                } else {
                    badge.style.display = 'none';
                }

                // Update sub-tab badge
                const subBadge = document.getElementById('requestsSubTabBadge');
                if (pendingTrafficRequests.length > 0) {
                    subBadge.textContent = pendingTrafficRequests.length;
                    subBadge.style.display = 'inline';
                } else {
                    subBadge.style.display = 'none';
                }

                // Render pending requests in Cases tab
                renderPendingRequestsInCases();

                updateRequestStats();
                filterRequests();
            } catch (err) {
                console.error('Error loading traffic requests:', err);
            }
        }

        function renderPendingRequestsInCases() {
            const section = document.getElementById('pendingRequestsSection');
            const container = document.getElementById('pendingRequestsCards');

            if (!pendingTrafficRequests.length) {
                section.style.display = 'none';
                return;
            }

            section.style.display = 'block';
            const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';

            container.innerHTML = pendingTrafficRequests.map(r => {
                const reqName = (r.referral_source || r.requester_name || '').replace(/\s*\(.*?\)\s*$/, '');
                return `
                    <div style="background: white; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div style="flex: 1; display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr; gap: 12px; align-items: center;">
                            <div>
                                <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px;">Requester</div>
                                <div style="font-size: 13px; font-weight: 600; color: #1a1a2e;">${escapeHtml(reqName || '—')}</div>
                            </div>
                            <div>
                                <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px;">Client</div>
                                <div style="font-size: 13px; font-weight: 600; color: #1a1a2e;">${escapeHtml(r.client_name)}</div>
                            </div>
                            <div>
                                <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px;">Court</div>
                                <div style="font-size: 13px; color: #3d3f4e;">${escapeHtml(r.court || '—')}</div>
                            </div>
                            <div>
                                <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px;">Ticket Issued</div>
                                <div style="font-size: 13px; color: #3d3f4e;">${fmtDate(r.citation_issued_date)}</div>
                            </div>
                            <div>
                                <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px;">Court Date</div>
                                <div style="font-size: 13px; color: #3d3f4e;">${fmtDate(r.court_date)}</div>
                            </div>
                            <div>
                                <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px;">Charge</div>
                                <div style="font-size: 12px; color: #5c5f73; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${escapeHtml(r.charge || '')}">${escapeHtml(r.charge || '—')}</div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button onclick="acceptTrafficRequest(${r.id})" style="background: #059669; color: white; border: none; border-radius: 6px; padding: 8px 16px; font-size: 12px; font-weight: 600; cursor: pointer; font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                                Accept
                            </button>
                            <button onclick="denyTrafficRequest(${r.id}, '${escapeJs(r.client_name)}')" style="background: white; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; padding: 8px 16px; font-size: 12px; font-weight: 600; cursor: pointer; font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                Deny
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function updateRequestStats() {
            const pending = allTrafficRequests.filter(r => r.status === 'pending').length;
            const accepted = allTrafficRequests.filter(r => r.status === 'accepted').length;
            const denied = allTrafficRequests.filter(r => r.status === 'denied').length;

            document.getElementById('reqPendingCount').textContent = pending;
            document.getElementById('reqAcceptedCount').textContent = accepted;
            document.getElementById('reqDeniedCount').textContent = denied;
            document.getElementById('reqTotalCount').textContent = allTrafficRequests.length;
            document.getElementById('reqBadgePending').textContent = pending;
        }

        function setRequestFilter(filter) {
            currentRequestFilter = filter;
            ['all', 'pending', 'accepted', 'denied'].forEach(f => {
                const btn = document.getElementById('reqFilterBtn-' + f);
                if (btn) btn.classList.toggle('active', f === filter);
            });
            filterRequests();
        }

        function filterRequests() {
            const search = (document.getElementById('requestsSearch').value || '').toLowerCase();
            let filtered = allTrafficRequests;

            if (currentRequestFilter !== 'all') {
                filtered = filtered.filter(r => r.status === currentRequestFilter);
            }
            if (search) {
                filtered = filtered.filter(r =>
                    (r.client_name || '').toLowerCase().includes(search) ||
                    (r.court || '').toLowerCase().includes(search) ||
                    (r.requester_name || '').toLowerCase().includes(search)
                );
            }
            renderAllRequests(filtered);
        }

        function renderAllRequests(requests) {
            const tbody = document.getElementById('requestsTableBody');
            if (!requests.length) {
                tbody.innerHTML = '<tr><td colspan="11" style="text-align:center; color:#8b8fa3; padding:40px;">No requests found</td></tr>';
                document.getElementById('requestsCaseCount').textContent = '0 requests';
                return;
            }

            tbody.innerHTML = requests.map(r => {
                const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
                const courtDate = fmtDate(r.court_date);
                const citationDate = fmtDate(r.citation_issued_date);
                const responded = fmtDate(r.responded_at);

                const statusBadge = r.status === 'pending'
                    ? '<span class="stat-badge" style="background:#fef3c7;color:#92400e;">Pending</span>'
                    : r.status === 'accepted'
                    ? '<span class="stat-badge" style="background:#d1fae5;color:#065f46;">Accepted</span>'
                    : '<span class="stat-badge" style="background:#fee2e2;color:#991b1b;">Denied</span>';

                const actions = r.status === 'pending' ? `
                    <div style="display:flex; gap:4px;">
                        <button onclick="acceptTrafficRequest(${r.id})" class="ink-icon-btn" title="Accept" style="color:#059669; border:1px solid #d1fae5; border-radius:6px; padding:4px 8px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button onclick="denyTrafficRequest(${r.id}, '${escapeJs(r.client_name)}')" class="ink-icon-btn" title="Deny" style="color:#dc2626; border:1px solid #fecaca; border-radius:6px; padding:4px 8px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                ` : '<span style="color:#c4c7d0;">—</span>';

                const noteText = r.status === 'denied' && r.deny_reason
                    ? '<span style="color:#dc2626;">' + escapeHtml(r.deny_reason) + '</span>'
                    : (r.note ? escapeHtml(r.note) : '<span style="color:#c4c7d0;">—</span>');

                const created = fmtDate(r.created_at);

                const ov = 'white-space:nowrap; overflow:hidden; text-overflow:ellipsis;';
                const reqName = (r.referral_source || r.requester_name || '').replace(/\s*\(.*?\)\s*$/, '');
                return `
                    <tr>
                        <td style="${ov}" title="${escapeHtml(reqName)}">${escapeHtml(reqName || '—')}</td>
                        <td style="font-size:12px;">${created || '—'}</td>
                        <td style="font-weight:600; color:#1a1a2e; ${ov}" title="${escapeHtml(r.client_name)}">${escapeHtml(r.client_name)}</td>
                        <td style="font-size:12px; ${ov}">${escapeHtml(r.client_phone || '—')}</td>
                        <td style="font-size:11px; ${ov}" title="${escapeHtml(r.client_email || '')}">${escapeHtml(r.client_email || '—')}</td>
                        <td style="${ov}" title="${escapeHtml(r.court || '')}">${escapeHtml(r.court || '—')}</td>
                        <td style="${ov}" title="${escapeHtml(r.charge || '')}">${escapeHtml(r.charge || '—')}</td>
                        <td style="font-family:monospace; font-size:11px; ${ov}">${escapeHtml(r.case_number || '—')}</td>
                        <td style="font-size:12px;">${courtDate || '—'}</td>
                        <td>${statusBadge}${r.status === 'pending' ? '<div style="margin-top:4px;">' + actions + '</div>' : ''}</td>
                        <td style="font-size:11px; color:#5c5f73; ${ov}" title="${escapeHtml(r.note || r.deny_reason || '')}">${noteText}</td>
                    </tr>
                `;
            }).join('');

            document.getElementById('requestsCaseCount').textContent = requests.length + ' requests';
        }

        async function acceptTrafficRequest(id) {
            try {
                const result = await apiCall('api/traffic_requests.php', 'PUT', { id, action: 'accept' });
                if (result.success) {
                    loadAllTrafficRequests();
                    loadTrafficCases();
                } else {
                    alert(result.error || 'Error accepting request');
                }
            } catch (err) {
                console.error('Error accepting request:', err);
                alert('Error accepting request');
            }
        }

        async function denyTrafficRequest(id, clientName) {
            const reason = prompt(`Reason for denying "${clientName}":`);
            if (reason === null) return;
            if (!reason.trim()) {
                alert('Deny reason is required');
                return;
            }
            try {
                const result = await apiCall('api/traffic_requests.php', 'PUT', { id, action: 'deny', deny_reason: reason.trim() });
                if (result.success) {
                    loadAllTrafficRequests();
                } else {
                    alert(result.error || 'Error denying request');
                }
            } catch (err) {
                console.error('Error denying request:', err);
                alert('Error denying request');
            }
        }

        // ============================================
        // Traffic Cases Functions
        // ============================================

        let trafficCasesData = [];
        let currentSidebarTab = 'all';
        let currentTrafficFilter = null;
        let currentTrafficStatusFilter = 'active'; // 'all', 'active', 'done'

        async function loadTrafficCases() {
            try {
                const result = await apiCall('api/traffic.php');
                if (result.cases) {
                    trafficCasesData = result.cases;
                    updateTrafficStatusCounts();
                    filterTrafficCases();
                    updateTrafficStats(trafficCasesData);
                    populateTrafficFilters();
                    if (typeof renderSidebarContent === 'function') {
                        renderSidebarContent(currentSidebarTab || 'all');
                    }
                    // Update commission sub-tab if active
                    if (currentTrafficSubTab === 'commission') {
                        updateCommissionStats();
                        filterCommTraffic();
                    }
                }
            } catch (err) {
                console.error('Error loading traffic cases:', err);
            }
        }

        function populateTrafficFilters() {
            // Populate court dropdown
            const courts = [...new Set(trafficCasesData.map(c => c.court).filter(Boolean))].sort();
            const courtSelect = document.getElementById('trafficCourtFilter');
            if (courtSelect) {
                courtSelect.innerHTML = '<option value="">All Courts</option>' +
                    courts.map(c => `<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`).join('');
            }

            // Populate referral dropdown
            const referrals = [...new Set(trafficCasesData.map(c => c.referral_source).filter(Boolean))].sort();
            const referralSelect = document.getElementById('trafficReferralFilter');
            if (referralSelect) {
                referralSelect.innerHTML = '<option value="">All Requesters</option>' +
                    referrals.map(r => `<option value="${escapeHtml(r)}">${escapeHtml(r)}</option>`).join('');
            }
        }

        function filterTrafficByDropdown() {
            const court = document.getElementById('trafficCourtFilter')?.value || '';
            const referral = document.getElementById('trafficReferralFilter')?.value || '';

            if (court) {
                currentTrafficFilter = { type: 'court', value: court };
            } else if (referral) {
                currentTrafficFilter = { type: 'referral', value: referral };
            } else {
                currentTrafficFilter = null;
            }
            filterTrafficCases();
        }

        function renderTrafficTable(cases) {
            const tbody = document.getElementById('trafficTableBody');
            if (!cases || cases.length === 0) {
                tbody.innerHTML = '<tr><td colspan="12" style="text-align:center; color:#6b7280; padding: 40px;">No traffic cases</td></tr>';
                document.getElementById('trafficCaseCount').textContent = '0 cases';
                return;
            }

            tbody.innerHTML = cases.map(c => {
                return `
                    <tr class="clickable-row" onclick="openTrafficModal(trafficCasesData.find(x => x.id == ${c.id}))" style="cursor:pointer;">
                        <td style="width:0;padding:0;border:none;"></td>
                        <td>${c.created_at ? formatDate(c.created_at) : '-'}</td>
                        <td>${escapeHtml(c.client_name)}</td>
                        <td style="font-family: monospace; font-size: 12px;">${escapeHtml(c.case_number || '-')}</td>
                        <td>${escapeHtml(c.court || '-')}</td>
                        <td>${escapeHtml(c.charge || '-')}</td>
                        <td>${c.court_date ? formatDate(c.court_date) : '-'}</td>
                        <td>${c.noa_sent_date || '-'}</td>
                        <td style="text-align:center;">${c.discovery == 1
                            ? '<span class="ink-badge" style="background:#d1fae5;color:#059669;">Received</span>'
                            : ''
                        }</td>
                        <td>${c.status === 'resolved'
                            ? '<span class="ink-badge" style="background:#d1fae5;color:#059669;">Resolved</span>'
                            : '<span class="ink-badge" style="background:#dbeafe;color:#1d4ed8;">Active</span>'
                        }</td>
                        <td>${escapeHtml(c.referral_source || '-')}</td>
                        <td style="text-align: center;" onclick="event.stopPropagation();">
                            <div style="display:flex; gap:4px; justify-content:center;">
                                <button class="ink-icon-btn" onclick="downloadTrafficCasePDF(${c.id})" title="Download PDF" style="color:#3b82f6;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                                </button>
                                <button class="ink-icon-btn ink-icon-btn-danger" onclick="deleteTrafficCase(${c.id})" title="Delete">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            document.getElementById('trafficCaseCount').textContent = cases.length + ' cases';
        }

        function downloadTrafficCasePDF(caseId) {
            const c = trafficCasesData.find(x => x.id == caseId);
            if (!c) return;

            if (!window.jspdf) {
                alert('PDF library is still loading. Please try again.');
                return;
            }
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            let y = 20;

            // Title
            doc.setFontSize(18);
            doc.setFont('helvetica', 'bold');
            doc.text('Traffic Case Report', 105, y, { align: 'center' });
            y += 12;

            // Divider line
            doc.setDrawColor(200);
            doc.setLineWidth(0.5);
            doc.line(20, y, 190, y);
            y += 10;

            // Helper to add a field row
            doc.setFontSize(11);
            const addField = (label, value) => {
                if (y > 270) { doc.addPage(); y = 20; }
                doc.setFont('helvetica', 'bold');
                doc.text(label + ':', 25, y);
                doc.setFont('helvetica', 'normal');
                doc.text(String(value || '-'), 80, y);
                y += 8;
            };

            addField('Client Name', c.client_name);
            addField('Phone', c.client_phone);
            addField('Court', c.court);
            addField('Court Date', c.court_date ? formatDate(c.court_date) : '-');
            addField('Charge', c.charge);
            addField('Case Number', c.case_number);
            addField('Disposition', c.disposition);
            addField('Status', c.status);
            addField('Requester', c.referral_source);
            addField('Discovery', c.discovery == 1 ? 'Received' : 'Not Received');
            addField('NOA Sent', c.noa_sent_date || '-');
            addField('Prosecutor Offer', c.prosecutor_offer);
            addField('Commission', formatCurrency(getTrafficCommission(c.disposition)));
            addField('Paid', c.paid == 1 ? 'Yes' : 'No');

            // Note section
            if (c.note) {
                y += 4;
                doc.setFont('helvetica', 'bold');
                doc.text('Note:', 25, y);
                y += 7;
                doc.setFont('helvetica', 'normal');
                const lines = doc.splitTextToSize(c.note, 155);
                doc.text(lines, 25, y);
                y += lines.length * 6;
            }

            // Footer
            y += 10;
            doc.setDrawColor(200);
            doc.line(20, y, 190, y);
            y += 6;
            doc.setFontSize(8);
            doc.setTextColor(150);
            doc.text('Generated on ' + new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }), 25, y);

            const clientSlug = (c.client_name || 'case').replace(/[^a-zA-Z0-9]/g, '_').substring(0, 30);
            doc.save(`traffic_case_${clientSlug}.pdf`);
        }

        // ========== Traffic Case File Attachments ==========
        async function loadTrafficFiles(caseId) {
            const container = document.getElementById('trafficFilesList');
            container.innerHTML = '<div style="padding: 16px; text-align: center; color: #9ca3af; font-size: 13px;">Loading...</div>';

            try {
                const result = await apiCall('api/traffic_files.php?case_id=' + caseId);
                if (result.files && result.files.length > 0) {
                    container.innerHTML = result.files.map(f => `
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 16px; border-bottom: 1px solid #f3f4f6;" data-file-id="${f.id}">
                            <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1;">
                                <span style="font-size: 18px; flex-shrink: 0;">${getFileIcon(f.original_name)}</span>
                                <div style="min-width: 0;">
                                    <div style="font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${f.original_name}">${f.original_name}</div>
                                    <div style="font-size: 11px; color: #9ca3af;">${formatFileSize(f.file_size)} &middot; ${formatDate(f.uploaded_at)}</div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 6px; flex-shrink: 0;">
                                <button type="button" onclick="downloadTrafficFile(${f.id})" title="Download" style="background: none; border: 1px solid #d1d5db; border-radius: 6px; padding: 4px 8px; cursor: pointer; color: #2563eb; font-size: 14px;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </button>
                                <button type="button" onclick="deleteTrafficFile(${f.id})" title="Delete" style="background: none; border: 1px solid #fecaca; border-radius: 6px; padding: 4px 8px; cursor: pointer; color: #dc2626; font-size: 14px;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<div style="padding: 16px; text-align: center; color: #9ca3af; font-size: 13px;">No files attached</div>';
                }
            } catch (e) {
                container.innerHTML = '<div style="padding: 16px; text-align: center; color: #ef4444; font-size: 13px;">Failed to load files</div>';
            }
        }

        async function uploadTrafficFile(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            const caseId = document.getElementById('trafficCaseId').value;

            if (!caseId) {
                showToast('Please save the case first before uploading files', 'error');
                input.value = '';
                return;
            }

            const maxSize = 20 * 1024 * 1024;
            if (file.size > maxSize) {
                showToast('File too large. Maximum size is 20MB', 'error');
                input.value = '';
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('case_id', caseId);
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('api/traffic_files.php', {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': csrfToken },
                    body: formData
                });
                const result = await response.json();
                if (result.csrf_token) csrfToken = result.csrf_token;

                if (result.success) {
                    showToast('File uploaded', 'success');
                    loadTrafficFiles(caseId);
                } else {
                    showToast(result.error || 'Upload failed', 'error');
                }
            } catch (e) {
                showToast('Upload failed', 'error');
            }

            input.value = '';
        }

        function downloadTrafficFile(fileId) {
            window.open('api/traffic_files.php?action=download&id=' + fileId, '_blank');
        }

        async function deleteTrafficFile(fileId) {
            if (!confirm('Delete this file?')) return;
            const caseId = document.getElementById('trafficCaseId').value;

            const result = await apiCall('api/traffic_files.php', 'DELETE', { id: fileId });
            if (result.success) {
                showToast('File deleted', 'success');
                loadTrafficFiles(caseId);
            } else {
                showToast(result.error || 'Failed to delete file', 'error');
            }
        }

        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            if (ext === 'pdf') return '📄';
            if (['doc', 'docx'].includes(ext)) return '📝';
            if (['xls', 'xlsx'].includes(ext)) return '📊';
            if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) return '🖼️';
            return '📎';
        }

        function formatFileSize(bytes) {
            if (!bytes) return '0 B';
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        async function deleteTrafficCase(caseId) {
            if (!confirm('Are you sure you want to delete this traffic case?')) return;

            const result = await apiCall('api/traffic.php', 'DELETE', { id: caseId });
            if (result.success) {
                showToast('Traffic case deleted', 'success');
                loadTrafficCases();
            } else {
                showToast(result.error || 'Failed to delete', 'error');
            }
        }

        function getTrafficCommission(disposition) {
            if (disposition === 'dismissed') return 150;
            if (disposition === 'amended') return 100;
            return 0;
        }

        function updateTrafficStats(cases) {
            const active = cases.filter(c => !c.disposition || c.disposition === 'pending').length;
            const dismissed = cases.filter(c => c.disposition === 'dismissed').length;
            const amended = cases.filter(c => c.disposition === 'amended').length;
            const totalComm = cases.reduce((sum, c) => sum + getTrafficCommission(c.disposition), 0);

            document.getElementById('trafficActive').textContent = active;
            document.getElementById('trafficDismissed').textContent = dismissed;
            document.getElementById('trafficAmended').textContent = amended;
            document.getElementById('trafficCommission').textContent = formatCurrency(totalComm);
        }

        function filterTrafficCases() {
            const search = (document.getElementById('trafficSearch').value || '').toLowerCase();
            let filtered = trafficCasesData;

            // Filter by status (All/Active/Done)
            if (currentTrafficStatusFilter === 'active') {
                filtered = filtered.filter(c => !c.disposition || c.disposition === 'pending');
            } else if (currentTrafficStatusFilter === 'done') {
                filtered = filtered.filter(c => c.disposition === 'dismissed' || c.disposition === 'amended');
            }

            // Filter by sidebar selection (referral/court/year)
            if (currentTrafficFilter) {
                filtered = filtered.filter(c => {
                    if (currentTrafficFilter.type === 'referral') {
                        return c.referral_source === currentTrafficFilter.value;
                    } else if (currentTrafficFilter.type === 'court') {
                        return c.court === currentTrafficFilter.value;
                    } else if (currentTrafficFilter.type === 'year') {
                        return c.court_date && c.court_date.startsWith(currentTrafficFilter.value);
                    }
                    return true;
                });
            }

            // Filter by search text
            if (search) {
                filtered = filtered.filter(c =>
                    (c.client_name || '').toLowerCase().includes(search) ||
                    (c.case_number || '').toLowerCase().includes(search) ||
                    (c.court || '').toLowerCase().includes(search)
                );
            }

            renderTrafficTable(filtered);
        }

        function setTrafficStatusFilter(status) {
            currentTrafficStatusFilter = status;

            // Update button styles - use f-chip active class
            ['all', 'active', 'done'].forEach(s => {
                const btn = document.getElementById('trafficStatusBtn-' + s);
                if (btn) {
                    btn.classList.remove('active');
                }
            });
            const activeBtn = document.getElementById('trafficStatusBtn-' + status);
            if (activeBtn) {
                activeBtn.classList.add('active');
            }

            // Update filter label
            const labels = {
                'all': 'All Cases',
                'active': 'Active Cases',
                'done': 'Done Cases'
            };
            document.getElementById('trafficFilterLabel').textContent = labels[status] || 'All Cases';

            // Re-filter the table
            filterTrafficCases();
        }

        function updateTrafficStatusCounts() {
            const allCount = trafficCasesData.length;
            const activeCount = trafficCasesData.filter(c => !c.disposition || c.disposition === 'pending').length;
            const doneCount = trafficCasesData.filter(c => c.disposition === 'dismissed' || c.disposition === 'amended').length;

            document.getElementById('trafficCountAll').textContent = allCount;
            document.getElementById('trafficCountActive').textContent = activeCount;
            document.getElementById('trafficCountDone').textContent = doneCount;
        }

        function switchSidebarTab(tab) {
            currentSidebarTab = tab;
            currentTrafficFilter = null;

            // Update filter label based on current status filter
            const statusLabels = { 'all': 'All Cases', 'active': 'Active Cases', 'done': 'Done Cases' };
            document.getElementById('trafficFilterLabel').textContent = statusLabels[currentTrafficStatusFilter] || 'All Cases';

            // Update button styles - use f-chip active class
            ['all', 'referral', 'court', 'year'].forEach(t => {
                const btn = document.getElementById('sidebarTab-' + t);
                if (btn) {
                    btn.classList.remove('active');
                }
            });
            const activeBtn = document.getElementById('sidebarTab-' + tab);
            if (activeBtn) {
                activeBtn.classList.add('active');
            }

            renderSidebarContent(tab);
            filterTrafficCases();
        }

        function renderSidebarContent(tab) {
            const container = document.getElementById('sidebarContent');
            let items = [];

            if (tab === 'all') {
                // Show summary stats for all cases
                const active = trafficCasesData.filter(c => !c.disposition || c.disposition === 'pending').length;
                const dismissed = trafficCasesData.filter(c => c.disposition === 'dismissed').length;
                const amended = trafficCasesData.filter(c => c.disposition === 'amended').length;
                const totalComm = trafficCasesData.reduce((sum, c) => sum + getTrafficCommission(c.disposition), 0);

                container.innerHTML = `
                    <div style="padding: 12px;">
                        <div style="font-weight: 600; margin-bottom: 12px; color: #1a1a2e;">All Cases Summary</div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <div style="display: flex; justify-content: space-between; padding: 8px 10px; background: #f7f9fc; border-radius: 6px;">
                                <span style="color: #5c5f73;">Total</span>
                                <span style="font-weight: 600;">${trafficCasesData.length}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 8px 10px; background: #dbeafe; border-radius: 6px;">
                                <span style="color: #1d4ed8;">Active</span>
                                <span style="font-weight: 600; color: #1d4ed8;">${active}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 8px 10px; background: #d1fae5; border-radius: 6px;">
                                <span style="color: #059669;">Dismissed</span>
                                <span style="font-weight: 600; color: #059669;">${dismissed}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 8px 10px; background: #fef3c7; border-radius: 6px;">
                                <span style="color: #d97706;">Amended</span>
                                <span style="font-weight: 600; color: #d97706;">${amended}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 10px; background: #1a1a2e; border-radius: 6px; margin-top: 4px;">
                                <span style="color: #fff;">Commission</span>
                                <span style="font-weight: 700; color: #4ade80;">${formatCurrency(totalComm)}</span>
                            </div>
                        </div>
                    </div>
                `;
                return;
            } else if (tab === 'referral') {
                const referrals = {};
                trafficCasesData.forEach(c => {
                    const ref = c.referral_source || 'Unknown';
                    referrals[ref] = (referrals[ref] || 0) + 1;
                });
                items = Object.entries(referrals).sort((a, b) => b[1] - a[1]);
            } else if (tab === 'court') {
                const courts = {};
                trafficCasesData.forEach(c => {
                    const court = c.court || 'Unknown';
                    courts[court] = (courts[court] || 0) + 1;
                });
                items = Object.entries(courts).sort((a, b) => b[1] - a[1]);
            } else if (tab === 'year') {
                const years = {};
                trafficCasesData.forEach(c => {
                    const year = c.court_date ? c.court_date.substring(0, 4) : 'Unknown';
                    years[year] = (years[year] || 0) + 1;
                });
                items = Object.entries(years).sort((a, b) => b[0].localeCompare(a[0]));
            }

            container.innerHTML = items.map(([name, count]) => `
                <div onclick="applySidebarFilter('${tab}', '${escapeJs(name)}')"
                     style="padding: 10px 12px; cursor: pointer; display: flex; justify-content: space-between; border-bottom: 1px solid #f0f0f0;"
                     onmouseover="this.style.background='#f7f9fc'" onmouseout="this.style.background=''">
                    <span style="font-size: 13px;">${escapeHtml(name)}</span>
                    <span style="font-size: 12px; background: #e5e7eb; padding: 2px 8px; border-radius: 10px;">${count}</span>
                </div>
            `).join('');
        }

        function applySidebarFilter(type, value) {
            currentTrafficFilter = { type, value };
            document.getElementById('trafficFilterLabel').textContent = value;
            filterTrafficCases();
        }

        function openTrafficModal(caseData = null) {
            document.getElementById('trafficForm').reset();
            document.getElementById('trafficCaseId').value = '';
            document.getElementById('trafficModalTitle').textContent = 'Add Traffic Case';
            document.getElementById('trafficCommissionDisplay').textContent = '$0.00';

            // Reset files section
            document.getElementById('trafficFilesSection').style.display = 'none';
            document.getElementById('trafficFilesList').innerHTML = '<div style="padding: 16px; text-align: center; color: #9ca3af; font-size: 13px;">No files attached</div>';

            if (caseData) {
                document.getElementById('trafficModalTitle').textContent = 'Edit Traffic Case';
                document.getElementById('trafficCaseId').value = caseData.id;
                document.getElementById('trafficClientName').value = caseData.client_name || '';
                document.getElementById('trafficClientPhone').value = caseData.client_phone || '';
                document.getElementById('trafficCourt').value = caseData.court || '';
                document.getElementById('trafficCourtDate').value = caseData.court_date ? caseData.court_date.replace(' ', 'T').substring(0, 16) : '';
                document.getElementById('trafficCharge').value = caseData.charge || '';
                document.getElementById('trafficCaseNumber').value = caseData.case_number || '';
                document.getElementById('trafficOffer').value = caseData.prosecutor_offer || '';
                document.getElementById('trafficDisposition').value = caseData.disposition || 'pending';
                document.getElementById('trafficStatus').value = caseData.status || 'active';
                document.getElementById('trafficReferralSource').value = caseData.referral_source || '';
                document.getElementById('trafficNoaSentDate').value = caseData.noa_sent_date || '';
                document.getElementById('trafficDiscovery').checked = caseData.discovery_received == 1;
                document.getElementById('trafficPaid').checked = caseData.paid == 1;
                document.getElementById('trafficNote').value = caseData.note || '';
                updateTrafficCommission();

                // Show files section and load files
                document.getElementById('trafficFilesSection').style.display = '';
                loadTrafficFiles(caseData.id);
            }

            openModal('trafficModal');
        }

        function editTrafficCase(id) {
            const caseData = trafficCasesData.find(c => c.id == id);
            if (caseData) {
                openTrafficModal(caseData);
            }
        }

        function updateTrafficCommission() {
            const disposition = document.getElementById('trafficDisposition').value;
            const commission = getTrafficCommission(disposition);
            document.getElementById('trafficCommissionDisplay').textContent = formatCurrency(commission);
        }

        async function submitTrafficCase(event) {
            event.preventDefault();

            const caseId = document.getElementById('trafficCaseId').value;
            const data = {
                client_name: document.getElementById('trafficClientName').value,
                client_phone: document.getElementById('trafficClientPhone').value,
                court: document.getElementById('trafficCourt').value,
                court_date: document.getElementById('trafficCourtDate').value,
                charge: document.getElementById('trafficCharge').value,
                case_number: document.getElementById('trafficCaseNumber').value,
                prosecutor_offer: document.getElementById('trafficOffer').value,
                disposition: document.getElementById('trafficDisposition').value,
                status: document.getElementById('trafficStatus').value,
                referral_source: document.getElementById('trafficReferralSource').value,
                noa_sent_date: document.getElementById('trafficNoaSentDate').value,
                discovery_received: document.getElementById('trafficDiscovery').checked,
                paid: document.getElementById('trafficPaid').checked,
                note: document.getElementById('trafficNote').value
            };

            const method = caseId ? 'PUT' : 'POST';
            const url = 'api/traffic.php';
            if (caseId) data.id = caseId;

            const result = await apiCall(url, method, data);
            if (result.success) {
                closeModal('trafficModal');
                loadTrafficCases();
                alert(caseId ? 'Traffic case updated!' : 'Traffic case added!');
            } else {
                alert('Error: ' + (result.error || 'Failed to save'));
            }
        }

    </script>
</body>
</html>
