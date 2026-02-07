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