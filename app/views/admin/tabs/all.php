        <!-- All Commissions Tab (Pending + All sub-tabs) -->
        <div id="content-all">
            <!-- Pill Sub-tabs -->
            <div class="ac-pills">
                <button class="ac-pill active" id="acPill-pending" onclick="switchAllCommSubTab('pending')">
                    Pending <span id="acPillPendingCount" class="ac-pill-count">0</span>
                </button>
                <button class="ac-pill" id="acPill-all" onclick="switchAllCommSubTab('all')">
                    All Commissions
                </button>
                <button class="ac-pill" id="acPill-history" onclick="switchAllCommSubTab('history')">
                    History
                </button>
            </div>

            <!-- ========== SUB-TAB: PENDING ========== -->
            <div id="acSub-pending">
                <div class="filters" style="margin-bottom: 12px;">
                    <button onclick="bulkAction('approve')" class="act-btn approve" style="padding: 6px 14px; font-size: 12px;">Approve Selected</button>
                    <button onclick="bulkAction('reject')" class="act-btn reject" style="padding: 6px 14px; font-size: 12px;">Reject Selected</button>
                    <div class="f-spacer"></div>
                </div>

                <div class="tbl-container">
                    <table class="tbl compact" id="pendingTable">
                        <thead>
                            <tr>
                                <th class="c" style="width:28px;padding:8px 4px;"><input type="checkbox" id="selectAllPending" onchange="toggleSelectAll('pending')"></th>
                                <th data-sort="text" style="padding:8px 6px;">Counsel</th>
                                <th data-sort="date" style="padding:8px 6px;">Month</th>
                                <th data-sort="text" style="padding:8px 6px;">Case #</th>
                                <th data-sort="text" style="padding:8px 6px;">Client</th>
                                <th data-sort="text" style="padding:8px 6px;">Resolution</th>
                                <th class="r" data-sort="number" style="padding:8px 6px;">Settled</th>
                                <th class="r" data-sort="number" style="padding:8px 6px;">Pre-Suit</th>
                                <th class="r" data-sort="number" style="padding:8px 6px;">Diff</th>
                                <th class="r" data-sort="number" style="padding:8px 6px;">Legal Fee</th>
                                <th class="r" data-sort="number" style="padding:8px 6px;">Disc. Fee</th>
                                <th class="r" data-sort="number" style="padding:8px 6px;">Commission</th>
                                <th class="c" style="padding:8px 6px;">Check</th>
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

            <!-- ========== SUB-TAB: ALL COMMISSIONS ========== -->
            <div id="acSub-all" style="display:none;">
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

                <div class="tbl-container">
                    <div id="allCasesTableWrapper">
                        <table class="tbl" id="allCasesTable" style="table-layout: auto;">
                            <thead>
                                <tr>
                                    <th data-sort="text" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('counsel_name')">Counsel</span></th>
                                    <th class="c" data-sort="text" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('status')">Status</span></th>
                                    <th data-sort="date" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('month')">Month</span></th>
                                    <th data-sort="text" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('case_number')">Case #</span></th>
                                    <th data-sort="text" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('client_name')">Client</span></th>
                                    <th data-sort="text" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('resolution_type')">Resolution</span></th>
                                    <th class="r" data-sort="number" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('settled')">Settled</span></th>
                                    <th class="r" data-sort="number" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('presuit_offer')">Pre-Suit</span></th>
                                    <th class="r" data-sort="number" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('difference')">Diff</span></th>
                                    <th class="r" data-sort="number" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('legal_fee')">Legal Fee</span></th>
                                    <th class="r" data-sort="number" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('discounted_legal_fee')">Disc. Fee</span></th>
                                    <th class="r" data-sort="number" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('commission')">Commission</span></th>
                                    <th class="c" data-sort="text" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('check_received')">Check</span></th>
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

            <!-- ========== SUB-TAB: HISTORY (Paid cases archive) ========== -->
            <div id="acSub-history" style="display:none;">
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
