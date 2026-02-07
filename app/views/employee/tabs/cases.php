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
                    <span class="qs-label">Pending</span>
                    <span class="qs-val" style="color:#d97706;" id="pendingCommission">$0.00</span>
                </div>
                <div class="qs-card">
                    <span class="qs-label">Unpaid</span>
                    <span class="qs-val" style="color:#dc2626;" id="unpaidCommission">$0.00</span>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <span class="f-chip active" id="statusChip-all" onclick="setStatusFilter('all', this)">All</span>
                <span class="f-chip" id="statusChip-in_progress" onclick="setStatusFilter('in_progress', this)">In Progress</span>
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
                <button onclick="showAddForm()" class="ink-btn ink-btn-sm" style="background:#3b82f6; color:#fff;">+ New Case</button>
            </div>

            <!-- Table -->
            <div class="tbl-container">
                <div style="overflow-x: auto;">
                    <table id="casesTable" class="tbl">
                        <thead>
                            <tr>
                                <th style="width:0;padding:0;border:none;"></th>
                                <th class="c"><span class="th-sort" onclick="sortCases('status')">Status <span class="sort-arrow">▼</span></span></th>
                                <th><span class="th-sort" onclick="sortCases('intake_date')">Intake Date <span class="sort-arrow">▼</span></span></th>
                                <th><span class="th-sort" onclick="sortCases('case_number')">Case # <span class="sort-arrow">▼</span></span></th>
                                <th><span class="th-sort" onclick="sortCases('client_name')">Client Name <span class="sort-arrow">▼</span></span></th>
                                <th><span class="th-sort" onclick="sortCases('resolution_type')">Resolution Type <span class="sort-arrow">▼</span></span></th>
                                <th class="r"><span class="th-sort" onclick="sortCases('settled')">Settled <span class="sort-arrow">▼</span></span></th>
                                <th class="r"><span class="th-sort" onclick="sortCases('presuit_offer')">Pre Suit Offer <span class="sort-arrow">▼</span></span></th>
                                <th class="r"><span class="th-sort" onclick="sortCases('difference')">Difference <span class="sort-arrow">▼</span></span></th>
                                <th class="r"><span class="th-sort" onclick="sortCases('legal_fee')">Legal Fee <span class="sort-arrow">▼</span></span></th>
                                <th class="r"><span class="th-sort" onclick="sortCases('discounted_legal_fee')">Disc. Legal Fee <span class="sort-arrow">▼</span></span></th>
                                <th class="r"><span class="th-sort" onclick="sortCases('commission')">Commission <span class="sort-arrow">▼</span></span></th>
                                <th><span class="th-sort" onclick="sortCases('month')">Month <span class="sort-arrow">▼</span></span></th>
                                <th class="c">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="casesBody">
                            <tr><td colspan="13" style="text-align:center; padding: 40px; color: #8b8fa3;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="tbl-foot">
                    <span class="left" id="footerInfo">0 cases</span>
                    <div class="right">
                        <span class="ft"><span class="ft-l">Total</span> <span class="ft-v" id="footerTotal">$0.00</span></span>
                        <span class="ft"><span class="ft-l">Pending</span> <span class="ft-v" style="color:#d97706;" id="footerPending">$0.00</span></span>
                        <span class="ft"><span class="ft-l">Unpaid</span> <span class="ft-v" style="color:#dc2626;" id="footerUnpaid">$0.00</span></span>
                    </div>
                </div>
            </div>
        </div>
