        <!-- Traffic Cases Tab — V3 Compact Layout -->
        <div id="content-traffic" class="tab-content hidden">

            <!-- ① Header -->
            <div class="tv3-header">
                <span class="tv3-title">Traffic Cases</span>
                <div class="tv3-pills">
                    <button class="tv3-pill active" id="tv3PillTab-cases" onclick="switchTrafficSubTab('cases')">Cases</button>
                    <button class="tv3-pill" id="tv3PillTab-commission" onclick="switchTrafficSubTab('commission')">Commission</button>
                    <button class="tv3-pill" id="tv3PillTab-requests" onclick="switchTrafficSubTab('requests')">Requests <span class="tv3-req-count" id="requestsSubTabBadge" style="display:none;">0</span></button>
                </div>
                <span class="tv3-date"><?php echo date('M j, Y'); ?></span>
            </div>

            <!-- ========== SUB-TAB 1: CASES ========== -->
            <div id="trafficSubContent-cases">

                <!-- Pending Requests Alert -->
                <div id="pendingRequestsSection" style="display: none;">
                    <div class="tv3-pending-alert">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                            <div style="background: #f59e0b; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <div style="font-size: 14px; font-weight: 700; color: #92400e;">Pending Requests</div>
                                <div style="font-size: 12px; color: #b45309;">Review and accept new traffic case requests</div>
                            </div>
                        </div>
                        <div id="pendingRequestsCards" style="display: flex; flex-direction: column; gap: 8px;"></div>
                    </div>
                </div>

                <!-- Stats Strip -->
                <div class="tv3-stats-strip">
                    <div class="tv3-stat-card green">
                        <span class="tv3-stat-label">Active</span>
                        <span class="tv3-stat-val" id="trafficActive">0</span>
                    </div>
                    <div class="tv3-stat-card">
                        <span class="tv3-stat-label">Dismissed</span>
                        <span class="tv3-stat-val" id="trafficDismissed">0</span>
                    </div>
                    <div class="tv3-stat-card amber">
                        <span class="tv3-stat-label">Amended</span>
                        <span class="tv3-stat-val" id="trafficAmended">0</span>
                    </div>
                    <div class="tv3-stat-card">
                        <span class="tv3-stat-label">Commission</span>
                        <span class="tv3-stat-val" id="trafficCommission">$0</span>
                    </div>
                </div>

                <!-- Filter Row -->
                <div class="tv3-filter-row">
                    <div class="tv3-filter-group">
                        <span class="tv3-filter-label">Status</span>
                        <select class="tv3-filter-select" id="tv3StatusFilter" onchange="filterTrafficCases()">
                            <option value="active" selected>Active</option>
                            <option value="all">All</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                    <div class="tv3-filter-group">
                        <span class="tv3-filter-label">View</span>
                        <select class="tv3-filter-select" id="tv3ViewFilter" onchange="onTrafficViewChange()">
                            <option value="all">All</option>
                            <option value="referral">By Requester</option>
                            <option value="court">By Court</option>
                            <option value="year">By Year</option>
                        </select>
                    </div>
                    <div class="tv3-filter-group" id="tv3SubFilterGroup" style="display: none;">
                        <span class="tv3-filter-label" id="tv3SubFilterLabel">Select</span>
                        <select class="tv3-filter-select" id="tv3SubFilter" onchange="filterTrafficCases()">
                            <option value="all">All</option>
                        </select>
                    </div>
                    <div class="tv3-filter-group">
                        <span class="tv3-filter-label">Search</span>
                        <input type="text" class="tv3-filter-search" id="trafficSearch" placeholder="Client, court, charge..." oninput="filterTrafficCases()">
                    </div>
                </div>

                <!-- Cases Table -->
                <div class="tv3-table-wrap">
                    <table class="tv3-table" id="trafficTable">
                        <thead>
                            <tr>
                                <th data-sort="text">Client</th>
                                <th data-sort="text">Case #</th>
                                <th data-sort="text">Court</th>
                                <th data-sort="text">Charge</th>
                                <th data-sort="date">Issued Date</th>
                                <th data-sort="date">NOA Date</th>
                                <th data-sort="date">Court Date</th>
                                <th class="c" data-sort="text">Discovery</th>
                                <th data-sort="text">Disposition</th>
                                <th class="c" data-sort="text">Status</th>
                                <th data-sort="text">Requester</th>
                                <th class="c">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="trafficTableBody">
                            <tr><td colspan="12" class="tv3-empty">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="tv3-footer">
                    <div class="left"><span id="trafficCaseCount">0</span> cases</div>
                    <div class="right">
                        <div class="ft"><span class="ft-l">Dismissed:</span><span class="ft-v green" id="tv3FootDismissed">0</span></div>
                        <div class="ft"><span class="ft-l">Amended:</span><span class="ft-v amber" id="tv3FootAmended">0</span></div>
                    </div>
                </div>
            </div>

            <!-- ========== SUB-TAB 2: COMMISSION ========== -->
            <div id="trafficSubContent-commission" style="display: none;">

                <!-- Stats Strip -->
                <div class="tv3-stats-strip">
                    <div class="tv3-stat-card">
                        <span class="tv3-stat-label">Total Commission</span>
                        <span class="tv3-stat-val" id="commTotalCommission">$0</span>
                    </div>
                    <div class="tv3-stat-card green">
                        <span class="tv3-stat-label">Paid</span>
                        <span class="tv3-stat-val" id="commPaidTotal">$0</span>
                    </div>
                    <div class="tv3-stat-card amber">
                        <span class="tv3-stat-label">Unpaid</span>
                        <span class="tv3-stat-val" id="commUnpaidTotal">$0</span>
                    </div>
                    <div class="tv3-stat-card">
                        <span class="tv3-stat-label">Resolved Cases</span>
                        <span class="tv3-stat-val" id="commCaseCount">0</span>
                    </div>
                </div>

                <!-- Filter Row -->
                <div class="tv3-filter-row">
                    <div class="tv3-filter-group">
                        <span class="tv3-filter-label">Status</span>
                        <select class="tv3-filter-select" id="tv3CommStatusFilter" onchange="setCommTrafficFilter(this.value)">
                            <option value="all">All</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                    </div>
                    <div class="tv3-filter-group">
                        <span class="tv3-filter-label">Year</span>
                        <select class="tv3-filter-select" id="commYearFilter" onchange="filterCommTraffic()">
                            <option value="">All Years</option>
                        </select>
                    </div>
                    <div class="tv3-filter-group">
                        <span class="tv3-filter-label">Month</span>
                        <select class="tv3-filter-select" id="commMonthFilter" onchange="filterCommTraffic()">
                            <option value="">All Months</option>
                        </select>
                    </div>
                    <div class="tv3-filter-group">
                        <span class="tv3-filter-label">Search</span>
                        <input type="text" class="tv3-filter-search" id="commTrafficSearch" placeholder="Client, court..." oninput="filterCommTraffic()">
                    </div>
                    <div class="tv3-filter-spacer"></div>
                    <button class="tv3-btn-export" onclick="exportTrafficCommissions()">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Export
                    </button>
                </div>

                <!-- Commission Table -->
                <div class="tv3-table-wrap">
                    <table class="tv3-table" id="commTrafficTable">
                        <colgroup>
                            <col class="col-requester">
                            <col class="col-client">
                            <col class="col-court">
                            <col class="col-courtdate">
                            <col class="col-disp">
                            <col class="col-amount">
                            <col class="col-paid">
                            <col class="col-paiddate">
                        </colgroup>
                        <thead>
                            <tr>
                                <th data-sort="text">Requester</th>
                                <th data-sort="text">Client</th>
                                <th data-sort="text">Court</th>
                                <th data-sort="date">Court Date</th>
                                <th data-sort="text">Disposition</th>
                                <th style="text-align:right;" data-sort="number">Amount</th>
                                <th class="c" data-sort="text">Paid</th>
                                <th data-sort="date">Paid Date</th>
                            </tr>
                        </thead>
                        <tbody id="commTrafficTableBody">
                            <tr><td colspan="8" class="tv3-empty">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="tv3-footer">
                    <div class="left"><span id="commTrafficCaseCount">0 cases</span></div>
                    <div class="right">
                        <div class="ft"><span class="ft-l">Total:</span><span class="ft-v green" id="commTrafficTotal">$0.00</span></div>
                    </div>
                </div>
            </div>

            <!-- ========== SUB-TAB 3: REQUESTS ========== -->
            <div id="trafficSubContent-requests" style="display: none;">

                <!-- Stats Strip -->
                <div class="tv3-stats-strip">
                    <div class="tv3-stat-card amber">
                        <span class="tv3-stat-label">Pending</span>
                        <span class="tv3-stat-val" id="reqPendingCount">0</span>
                    </div>
                    <div class="tv3-stat-card green">
                        <span class="tv3-stat-label">Accepted</span>
                        <span class="tv3-stat-val" id="reqAcceptedCount">0</span>
                    </div>
                    <div class="tv3-stat-card">
                        <span class="tv3-stat-label">Denied</span>
                        <span class="tv3-stat-val" style="color: #dc2626;" id="reqDeniedCount">0</span>
                    </div>
                    <div class="tv3-stat-card">
                        <span class="tv3-stat-label">Total</span>
                        <span class="tv3-stat-val" id="reqTotalCount">0</span>
                    </div>
                </div>

                <!-- Filter Row -->
                <div class="tv3-filter-row">
                    <div class="tv3-filter-group">
                        <span class="tv3-filter-label">Status</span>
                        <select class="tv3-filter-select" id="tv3ReqStatusFilter" onchange="setRequestFilter(this.value)">
                            <option value="all">All</option>
                            <option value="pending">Pending</option>
                            <option value="accepted">Accepted</option>
                            <option value="denied">Denied</option>
                        </select>
                    </div>
                    <div class="tv3-filter-group">
                        <span class="tv3-filter-label">Search</span>
                        <input type="text" class="tv3-filter-search" id="requestsSearch" placeholder="Client, court..." oninput="filterRequests()">
                    </div>
                </div>

                <!-- Requests Table -->
                <div class="tv3-table-wrap">
                    <table class="tv3-table">
                        <thead>
                            <tr>
                                <th data-sort="text">Requester</th>
                                <th data-sort="date">Submitted</th>
                                <th data-sort="text">Client</th>
                                <th data-sort="text">Phone</th>
                                <th data-sort="text">Court</th>
                                <th data-sort="text">Charge</th>
                                <th data-sort="text">Ticket #</th>
                                <th data-sort="date">Court Date</th>
                                <th class="c" data-sort="text">Status</th>
                                <th data-sort="text">Notes</th>
                                <th class="c">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="requestsTableBody">
                            <tr><td colspan="11" class="tv3-empty">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="tv3-footer">
                    <div class="left"><span id="requestsCaseCount">0 requests</span></div>
                </div>
            </div>

        </div>
