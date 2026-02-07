        <!-- Performance Analytics Tab -->
        <div id="content-performance" class="hidden">

            <!-- Filters -->
            <div class="filters" style="margin-bottom: 16px;">
                <select id="perfEmployeeFilter" class="f-select" onchange="loadPerformanceData()">
                    <option value="2">Chong</option>
                    <option value="0">All Employees</option>
                </select>
                <select id="perfYearFilter" class="f-select" onchange="loadPerformanceData()">
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                </select>
                <span class="f-spacer"></span>
                <button class="f-btn" onclick="loadPerformanceData()" style="background: #1a1a2e; color: #fff; border: none;">Refresh</button>
            </div>

            <!-- Hero Cards -->
            <div class="hero-row">
                <div class="hero-card accent-dark">
                    <div class="hero-label">Total Cases (YTD)</div>
                    <div class="hero-val" id="perfTotalCases">—</div>
                </div>
                <div class="hero-card accent-teal">
                    <div class="hero-label">Total Commission (YTD)</div>
                    <div class="hero-val teal" id="perfTotalCommission">—</div>
                    <div class="hero-sub" id="perfCommissionChange"></div>
                </div>
                <div class="hero-card accent-blue">
                    <div class="hero-label">Avg Commission / Case</div>
                    <div class="hero-val" id="perfAvgCommission">—</div>
                </div>
            </div>

            <!-- Analytics Panel -->
            <div class="panel" id="chongAnalyticsSection">
                <div class="panel-section">
                    <div class="panel-label"><div class="panel-label-text">Phase<br>Breakdown</div></div>
                    <div class="panel-data">
                        <div class="pd-cell"><div class="pd-label">Demand Active</div><div class="pd-val blue" id="perfDemandActive">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Litigation Active</div><div class="pd-val indigo" id="perfLitActive">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Settled (YTD)</div><div class="pd-val teal" id="perfSettled">—</div></div>
                    </div>
                </div>
                <div class="panel-section">
                    <div class="panel-label"><div class="panel-label-text">Settlement<br>Breakdown</div></div>
                    <div class="panel-data">
                        <div class="pd-cell"><div class="pd-label">Demand Settled</div><div class="pd-val" id="perfDemandSettled">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Litigation Settled</div><div class="pd-val" id="perfLitSettled">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Resolution Rate</div><div class="pd-val green" id="perfResolutionRate">—</div></div>
                    </div>
                </div>
                <div class="panel-section">
                    <div class="panel-label"><div class="panel-label-text">Efficiency<br>Metrics</div></div>
                    <div class="panel-data">
                        <div class="pd-cell"><div class="pd-label">Avg Demand Days</div><div class="pd-val dim" id="perfAvgDemandDays">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Avg Lit Days</div><div class="pd-val dim" id="perfAvgLitDays">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Avg Total Days</div><div class="pd-val dim" id="perfAvgTotalDays">—</div></div>
                    </div>
                </div>
                <div class="panel-section">
                    <div class="panel-label"><div class="panel-label-text">Time<br>Management</div></div>
                    <div class="panel-data">
                        <div class="pd-cell"><div class="pd-label">Deadline Compliance</div><div class="pd-val green" id="perfDeadlineCompliance">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Urgent Cases</div><div class="pd-val dim" id="perfUrgentCases">—</div></div>
                        <div class="pd-cell"></div>
                    </div>
                </div>
                <div class="panel-section">
                    <div class="panel-label"><div class="panel-label-text">Commission<br>Breakdown</div></div>
                    <div class="panel-data">
                        <div class="pd-cell"><div class="pd-label">Total</div><div class="pd-val teal" id="perfCommTotal">—</div></div>
                        <div class="pd-cell"><div class="pd-label">From Demand (5%)</div><div class="pd-val" id="perfCommDemand">—</div></div>
                        <div class="pd-cell"><div class="pd-label">From Litigation (20%)</div><div class="pd-val" id="perfCommLit">—</div></div>
                        <div class="pd-cell"><div class="pd-label">Active Cases</div><div class="pd-val" id="perfActiveCases">—</div></div>
                    </div>
                </div>
            </div>

            <!-- Chart Panel -->
            <div class="panel" style="margin-bottom: 12px;">
                <div class="panel-head">Monthly Commission Trend</div>
                <div class="chart-wrap">
                    <canvas id="perfCommissionChart"></canvas>
                </div>
            </div>

            <!-- Employee Table -->
            <div class="tbl-container">
                <table class="tbl" style="table-layout: auto;">
                    <thead>
                        <tr>
                            <th style="padding: 10px 14px;">Employee</th>
                            <th class="r" style="padding: 10px 14px;">Cases</th>
                            <th class="r" style="padding: 10px 14px;">Paid</th>
                            <th class="r" style="padding: 10px 14px;">Commission</th>
                            <th class="r" style="padding: 10px 14px;">Avg/Case</th>
                            <th class="r" style="padding: 10px 14px;">Share</th>
                        </tr>
                    </thead>
                    <tbody id="perfEmployeeBody">
                        <tr><td colspan="6" style="text-align: center; padding: 20px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>