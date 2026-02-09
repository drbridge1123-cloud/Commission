        <!-- Referrals Tab -->
        <div id="content-referrals">
            <!-- Filters -->
            <div class="filters" style="margin-bottom: 16px;">
                <select id="referralYearFilter" class="f-select" onchange="loadReferrals(); loadReferralSummary();">
                </select>
                <select id="referralMonthFilter" class="f-select" onchange="loadReferrals()">
                    <option value="0">All Months</option>
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
                <select id="referralManagerFilter" class="f-select" onchange="loadReferrals()">
                    <option value="all">All Case Managers</option>
                </select>
                <span class="f-spacer"></span>
                <button onclick="exportReferrals()" class="f-btn">Export</button>
                <button onclick="openReferralForm()" class="ink-btn ink-btn-primary ink-btn-sm">+ New Referral</button>
            </div>

            <!-- Summary Cards -->
            <div class="hero-row" style="margin-bottom: 16px;">
                <div class="hero-card accent-dark">
                    <div class="hero-label">This Month</div>
                    <div class="hero-val" id="refMonthCount">0</div>
                </div>
                <div class="hero-card accent-teal">
                    <div class="hero-label">YTD Total</div>
                    <div class="hero-val teal" id="refYearCount">0</div>
                </div>
                <div class="hero-card accent-blue">
                    <div class="hero-label">Top Source</div>
                    <div class="hero-val" id="refTopSource" style="font-size: 16px;">—</div>
                </div>
            </div>

            <!-- Referrals Table -->
            <div class="tbl-container">
                <div class="tbl-header">
                    <span class="tbl-title">Referral Entries</span>
                    <span class="tbl-count" id="refTableCount">0 entries</span>
                </div>
                <table class="tbl" style="table-layout: auto;">
                    <thead>
                        <tr>
                            <th style="width: 30px;" data-sort="number">#</th>
                            <th data-sort="text">Lead</th>
                            <th data-sort="date">Signed Date</th>
                            <th data-sort="text">File #</th>
                            <th data-sort="text">Client Name</th>
                            <th data-sort="date">Date of Loss</th>
                            <th data-sort="text">Referred By</th>
                            <th data-sort="text">Referred To</th>
                            <th data-sort="text">Body Shop</th>
                            <th data-sort="text">Case Mgr</th>
                            <th data-sort="text">Remark</th>
                            <th class="c" style="width: 80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="referralsTableBody">
                        <tr><td colspan="12" style="text-align: center; padding: 40px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
