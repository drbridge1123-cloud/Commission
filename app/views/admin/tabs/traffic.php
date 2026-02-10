        <!-- Traffic Cases Tab (Admin View) — V3 Compact Layout -->
        <div id="content-traffic" class="hidden">

            <!-- ① Top Header -->
            <div class="tv3-header">
                <span class="tv3-title">Traffic Cases</span>
                <div class="tv3-pills">
                    <button class="tv3-pill active" data-tab="active" onclick="switchTrafficPillTab('active')">Active</button>
                    <button class="tv3-pill" data-tab="all" onclick="switchTrafficPillTab('all')">All</button>
                    <button class="tv3-pill" data-tab="done" onclick="switchTrafficPillTab('done')">Done</button>
                    <button class="tv3-pill" data-tab="unpaid" onclick="switchTrafficPillTab('unpaid')">Unpaid <span class="tv3-req-count" id="tv3UnpaidCount" style="display:none;">0</span></button>
                    <button class="tv3-pill" data-tab="requests" onclick="switchTrafficPillTab('requests')">Requests <span class="tv3-req-count" id="tv3ReqCount" style="display:none;">0</span></button>
                </div>
                <button class="tv3-btn-new" onclick="openModal('trafficRequestModal')">+ New Request</button>
            </div>

            <!-- Pending Request Banner -->
            <div id="tv3PendingBanner" style="display:none; background:#fef3c7; border:1px solid #f59e0b; border-radius:8px; padding:14px 18px; margin-bottom:12px; align-items:center; gap:12px;">
                <span style="font-size:20px;">&#9888;</span>
                <span style="font-weight:700; color:#92400e; font-size:14px;" id="tv3PendingBannerText">You have pending traffic requests</span>
                <button style="margin-left:auto; background:#f59e0b; color:#fff; border:none; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:12px; font-weight:700;" onclick="switchTrafficPillTab('requests')">View Requests</button>
            </div>

            <!-- ② Stats Strip -->
            <div class="tv3-stats-strip">
                <div class="tv3-stat-card green">
                    <span class="tv3-stat-label">Active</span>
                    <span class="tv3-stat-val" id="tv3StatActive">0</span>
                </div>
                <div class="tv3-stat-card">
                    <span class="tv3-stat-label">Dismissed</span>
                    <span class="tv3-stat-val" id="tv3StatDismissed">0</span>
                </div>
                <div class="tv3-stat-card amber">
                    <span class="tv3-stat-label">Amended</span>
                    <span class="tv3-stat-val" id="tv3StatAmended">0</span>
                </div>
                <div class="tv3-stat-card" style="border-left: 3px solid #92400e;">
                    <span class="tv3-stat-label">Unpaid</span>
                    <span class="tv3-stat-val" style="color: #92400e;" id="tv3StatUnpaid">0</span>
                </div>
                <div class="tv3-stat-card">
                    <span class="tv3-stat-label">Pending Req.</span>
                    <span class="tv3-stat-val" id="tv3StatPendingReq">0</span>
                </div>
            </div>

            <!-- ③ Filter Row (hidden when Requests pill is active) -->
            <div class="tv3-filter-row" id="tv3FilterRow">
                <div class="tv3-filter-group">
                    <span class="tv3-filter-label">View</span>
                    <select class="tv3-filter-select" id="tv3ViewFilter" onchange="onTV3ViewChange()">
                        <option value="all">All</option>
                        <option value="referral">By Referral</option>
                        <option value="court">By Court</option>
                        <option value="year">By Year</option>
                    </select>
                </div>
                <div class="tv3-filter-group" id="tv3SubFilterGroup" style="display: none;">
                    <span class="tv3-filter-label" id="tv3SubFilterLabel">Select</span>
                    <select class="tv3-filter-select" id="tv3SubFilter" onchange="applyTV3Filters()">
                        <option value="all">All</option>
                    </select>
                </div>
                <div class="tv3-filter-group">
                    <span class="tv3-filter-label">Search</span>
                    <input type="text" class="tv3-filter-search" id="tv3Search" placeholder="Client, court, charge..." oninput="applyTV3Filters()">
                </div>
            </div>

            <!-- Bulk Action Bar (visible when checkboxes are selected) -->
            <div id="tv3BulkBar" style="display:none; background:#f0fdf4; border:1px solid #059669; border-radius:8px; padding:10px 16px; margin-bottom:10px; align-items:center; gap:12px;">
                <span style="font-weight:600; font-size:13px; color:#059669;" id="tv3BulkCount">0 selected</span>
                <button onclick="bulkMarkTrafficPaid()" style="background:#059669; color:#fff; border:none; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:12px; font-weight:700;">Mark All Paid</button>
                <button onclick="clearTV3Selection()" style="background:#6b7280; color:#fff; border:none; padding:6px 10px; border-radius:6px; cursor:pointer; font-size:12px;">Clear</button>
            </div>

            <!-- ④a Cases Table -->
            <div class="tv3-table-wrap" id="tv3CasesWrap">
                <table class="tv3-table">
                    <thead>
                        <tr>
                            <th id="tv3CheckAllTh" class="c" style="width:32px; display:none;"><input type="checkbox" id="tv3CheckAll" onclick="toggleTV3CheckAll(this)"></th>
                            <th data-sort="text">Client</th>
                            <th data-sort="text">Court</th>
                            <th data-sort="text">Charge</th>
                            <th data-sort="date">Issued Date</th>
                            <th class="c" data-sort="text">NOA</th>
                            <th data-sort="date">Court Date</th>
                            <th class="c" data-sort="text">Discovery</th>
                            <th data-sort="text">Disposition</th>
                            <th class="c" data-sort="text">Status</th>
                            <th data-sort="text">Requester</th>
                            <th class="c">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tv3CasesBody">
                        <tr><td colspan="12" class="tv3-empty">Loading...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- ④b Requests Table (hidden by default) -->
            <div class="tv3-table-wrap" id="tv3RequestsWrap" style="display: none;">
                <table class="tv3-table">
                    <thead>
                        <tr>
                            <th data-sort="text">Client</th>
                            <th data-sort="text">Court</th>
                            <th data-sort="date">Court Date</th>
                            <th data-sort="text">Charge</th>
                            <th data-sort="text">Requester</th>
                            <th class="c" data-sort="text">Status</th>
                            <th data-sort="date">Submitted</th>
                            <th class="c">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tv3RequestsBody">
                        <tr><td colspan="8" class="tv3-empty">Loading...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- ⑤ Footer (hidden when Requests pill is active) -->
            <div class="tv3-footer" id="tv3Footer">
                <div class="left"><span id="tv3FootCount">0</span> cases</div>
                <div class="right">
                    <div class="ft"><span class="ft-l">Dismissed:</span><span class="ft-v green" id="tv3FootDismissed">0</span></div>
                    <div class="ft"><span class="ft-l">Amended:</span><span class="ft-v amber" id="tv3FootAmended">0</span></div>
                </div>
            </div>
        </div>
