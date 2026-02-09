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
                                <th data-sort="date">Date</th>
                                <th data-sort="text">Client</th>
                                <th data-sort="text">Type</th>
                                <th data-sort="text">Resolution</th>
                                <th data-sort="number" style="text-align: right;">Settled</th>
                                <th data-sort="number" style="text-align: right;">Commission</th>
                            </tr>
                        </thead>
                        <tbody id="recentSettlementsBody">
                            <tr><td colspan="6" style="text-align:center; padding: 30px; color: #8b8fa3;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>