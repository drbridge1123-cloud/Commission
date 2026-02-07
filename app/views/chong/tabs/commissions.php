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