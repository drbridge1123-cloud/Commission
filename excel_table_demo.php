<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ocean Professional Table - Excel Style</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Palette */
            --primary-dark: #0f4c81;
            --primary: #1565c0;
            --primary-light: #1e88e5;
            
            --success: #10b981;
            --success-light: #d1fae5;
            --success-dark: #047857;
            
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --warning-dark: #b45309; /* Fixed from prompt context */
            
            --danger: #dc2626;
            --danger-light: #fee2e2;
            --danger-dark: #991b1b;
            
            /* Slate Grays */
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9; /* Slate 100 background for page */
            margin: 0;
            padding: 40px;
            color: var(--slate-700);
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden; /* For rounded corners */
            border: 1px solid var(--slate-200);
        }

        /* Toolbar */
        .table-toolbar {
            background: white;
            padding: 16px 20px;
            border-bottom: 1px solid var(--slate-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .toolbar-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(21, 101, 192, 0.2);
        }
        .btn-primary:hover {
            box-shadow: 0 4px 6px rgba(21, 101, 192, 0.3);
            transform: translateY(-1px);
        }

        .btn-filter {
            background: var(--slate-100);
            color: var(--slate-600);
            border: 1px solid var(--slate-200);
        }
        .btn-filter:hover {
            background: var(--slate-200);
        }

        .search-box {
            position: relative;
        }
        .search-box input {
            padding: 8px 12px 8px 36px;
            border-radius: 6px;
            border: 1px solid var(--slate-200);
            background: var(--slate-100);
            font-size: 13px;
            width: 240px;
            color: var(--slate-700);
            outline: none;
            transition: all 0.2s;
        }
        .search-box input:focus {
            background: white;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
        }
        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--slate-400);
            width: 16px;
            height: 16px;
        }

        /* Table Wrapper for horizontal scroll */
        .table-scroll-wrapper {
            overflow-x: auto;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px; /* Ensure it doesn't squish too much */
        }

        /* Header */
        thead th {
            background: linear-gradient(180deg, var(--primary-dark) 0%, #0d3f6a 100%);
            color: white;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            padding: 14px 12px;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 10;
            border-right: 1px solid rgba(255,255,255,0.15);
            white-space: nowrap;
        }
        thead th:last-child {
            border-right: none;
        }
        
        .th-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        }
        .th-content:hover {
            background-color: rgba(255,255,255,0.1);
            margin: -14px -12px;
            padding: 14px 12px;
        }
        .sort-icon {
            opacity: 0.5;
            font-size: 10px;
            margin-left: 4px;
        }

        /* Body */
        tbody tr {
            border-bottom: 1px solid var(--slate-200);
            transition: background-color 0.1s;
        }
        tbody tr:nth-child(even) {
            background-color: var(--slate-50);
        }
        tbody tr:hover {
            background-color: #dbeafe !important;
        }
        tbody tr.selected {
            background-color: #bfdbfe !important;
        }

        tbody td {
            padding: 12px;
            font-size: 13px;
            color: var(--slate-700);
            border-right: 1px solid var(--slate-100);
            vertical-align: middle;
        }
        tbody td:last-child {
            border-right: none;
        }

        /* Specific Columns */
        .col-check {
            text-align: center;
            width: 40px;
            color: var(--slate-400);
        }
        .col-check.checked {
            color: var(--success);
            font-weight: bold;
        }

        .col-number {
            color: var(--slate-400);
            text-align: center;
            width: 50px;
            font-size: 11px;
        }

        .col-amount {
            text-align: right;
            font-variant-numeric: tabular-nums;
            font-family: 'Inter', sans-serif; /* Ensure tabular nums work */
        }

        .amount-settled {
            color: var(--primary-dark);
            font-weight: 700;
        }
        
        .amount-commission {
            color: var(--success-dark);
            font-weight: 700;
        }

        /* Status Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 600;
            line-height: 1;
        }
        .badge::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .badge-pending {
            background: var(--warning-light);
            color: #92400e; /* Specific from prompt */
            border: 1px solid #fcd34d;
        }
        .badge-pending::before { background: #92400e; }

        .badge-paid {
            background: var(--success-light);
            color: #065f46; /* Specific from prompt */
            border: 1px solid #6ee7b7;
        }
        .badge-paid::before { background: #065f46; }

        .badge-rejected {
            background: var(--danger-light);
            color: #991b1b; /* Specific from prompt */
            border: 1px solid #fca5a5;
        }
        .badge-rejected::before { background: #991b1b; }

        /* Footer */
        .table-footer {
            background: var(--slate-50);
            border-top: 1px solid var(--slate-200);
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .footer-info {
            font-size: 13px;
            color: var(--slate-500);
        }

        .footer-total {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .total-label {
            font-size: 13px;
            color: var(--slate-500);
            font-weight: 500;
        }
        .total-amount {
            font-size: 18px;
            font-weight: 700;
            color: var(--success-dark);
            font-variant-numeric: tabular-nums;
        }

    </style>
</head>
<body>

    <div class="table-container">
        <!-- Toolbar -->
        <div class="table-toolbar">
            <div class="search-box">
                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" placeholder="Search cases, clients...">
            </div>
            <div class="toolbar-actions">
                <button class="btn btn-filter">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filter
                </button>
                <button class="btn btn-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-scroll-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">
                            <div class="th-content">#</div>
                        </th>
                        <th style="width: 60px;">
                            <div class="th-content">Check</div>
                        </th>
                        <th>
                            <div class="th-content">Case ID <span class="sort-icon">▼</span></div>
                        </th>
                        <th>
                            <div class="th-content">Client Name <span class="sort-icon">▼</span></div>
                        </th>
                        <th>
                            <div class="th-content">Date <span class="sort-icon">▼</span></div>
                        </th>
                        <th style="text-align: right;">
                            <div class="th-content" style="justify-content: flex-end;">Settled Amount <span class="sort-icon">▼</span></div>
                        </th>
                        <th style="text-align: right;">
                            <div class="th-content" style="justify-content: flex-end;">Commission <span class="sort-icon">▼</span></div>
                        </th>
                        <th>
                            <div class="th-content">Status <span class="sort-icon">▼</span></div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Row 1 -->
                    <tr>
                        <td class="col-number">1</td>
                        <td class="col-check checked">✓</td>
                        <td><strong>CASE-2024-001</strong></td>
                        <td>John Smith</td>
                        <td>Nov 24, 2024</td>
                        <td class="col-amount amount-settled">$150,000.00</td>
                        <td class="col-amount amount-commission">$45,000.00</td>
                        <td><span class="badge badge-paid">Paid</span></td>
                    </tr>
                    <!-- Row 2 -->
                    <tr>
                        <td class="col-number">2</td>
                        <td class="col-check">-</td>
                        <td><strong>CASE-2024-002</strong></td>
                        <td>Sarah Johnson</td>
                        <td>Nov 23, 2024</td>
                        <td class="col-amount amount-settled">$75,500.00</td>
                        <td class="col-amount amount-commission">$22,650.00</td>
                        <td><span class="badge badge-pending">Pending</span></td>
                    </tr>
                    <!-- Row 3 (Selected) -->
                    <tr class="selected">
                        <td class="col-number">3</td>
                        <td class="col-check checked">✓</td>
                        <td><strong>CASE-2024-003</strong></td>
                        <td>Michael Brown</td>
                        <td>Nov 22, 2024</td>
                        <td class="col-amount amount-settled">$210,000.00</td>
                        <td class="col-amount amount-commission">$63,000.00</td>
                        <td><span class="badge badge-paid">Paid</span></td>
                    </tr>
                    <!-- Row 4 -->
                    <tr>
                        <td class="col-number">4</td>
                        <td class="col-check">-</td>
                        <td><strong>CASE-2024-004</strong></td>
                        <td>Emily Davis</td>
                        <td>Nov 21, 2024</td>
                        <td class="col-amount amount-settled">$0.00</td>
                        <td class="col-amount amount-commission">$0.00</td>
                        <td><span class="badge badge-rejected">Rejected</span></td>
                    </tr>
                    <!-- Row 5 -->
                    <tr>
                        <td class="col-number">5</td>
                        <td class="col-check checked">✓</td>
                        <td><strong>CASE-2024-005</strong></td>
                        <td>David Wilson</td>
                        <td>Nov 20, 2024</td>
                        <td class="col-amount amount-settled">$95,000.00</td>
                        <td class="col-amount amount-commission">$28,500.00</td>
                        <td><span class="badge badge-paid">Paid</span></td>
                    </tr>
                     <!-- Row 6 -->
                     <tr>
                        <td class="col-number">6</td>
                        <td class="col-check">-</td>
                        <td><strong>CASE-2024-006</strong></td>
                        <td>Jessica Taylor</td>
                        <td>Nov 19, 2024</td>
                        <td class="col-amount amount-settled">$42,000.00</td>
                        <td class="col-amount amount-commission">$12,600.00</td>
                        <td><span class="badge badge-pending">Pending</span></td>
                    </tr>
                     <!-- Row 7 -->
                     <tr>
                        <td class="col-number">7</td>
                        <td class="col-check">-</td>
                        <td><strong>CASE-2024-007</strong></td>
                        <td>Robert Anderson</td>
                        <td>Nov 18, 2024</td>
                        <td class="col-amount amount-settled">$125,000.00</td>
                        <td class="col-amount amount-commission">$37,500.00</td>
                        <td><span class="badge badge-pending">Pending</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="table-footer">
            <div class="footer-info">
                Showing 1-7 of 145 cases
            </div>
            <div class="footer-total">
                <span class="total-label">Total Commission:</span>
                <span class="total-amount">$209,250.00</span>
            </div>
        </div>
    </div>

</body>
</html>
