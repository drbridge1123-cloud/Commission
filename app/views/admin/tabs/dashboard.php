        <!-- Dashboard Tab -->
        <div id="content-dashboard" class="hidden">
            <div id="dashboardCard">
            <!-- Row 1: Quick Stats (6 cards) -->
            <div class="quick-stats" style="grid-template-columns: repeat(6, 1fr);">
                <div class="qs-card">
                    <div><div class="qs-label">Total Cases</div><div class="qs-val" id="statTotalCases">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Pending</div><div class="qs-val amber" id="statPending">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Total Commission</div><div class="qs-val green" id="statTotalCommission">$0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Avg Commission</div><div class="qs-val blue" id="statAvgCommission">$0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Check Received</div><div class="qs-val" id="statCheckRate">0%</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Unreceived</div><div class="qs-val red" id="statUnreceived">$0</div></div>
                </div>
            </div>

            <!-- Row 2: This Month vs Last Month -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="ink-chart-container" style="padding: 16px 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h3 style="margin: 0;">This Month</h3>
                        <span id="thisMonthName" style="font-size: 11px; color: #8b8fa3;"></span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Cases</div>
                            <div style="font-size: 20px; font-weight: 700; color: #1a1a2e;" id="thisMonthCases">0</div>
                            <div style="font-size: 10px; margin-top: 2px;" id="thisMonthCasesChange"></div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Commission</div>
                            <div style="font-size: 20px; font-weight: 700; color: #0d9488;" id="thisMonthComm">$0</div>
                            <div style="font-size: 10px; margin-top: 2px;" id="thisMonthCommChange"></div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Approved</div>
                            <div style="font-size: 20px; font-weight: 700; color: #3b82f6;" id="thisMonthApproved">0</div>
                            <div style="font-size: 10px; margin-top: 2px;" id="thisMonthApprovedChange"></div>
                        </div>
                    </div>
                </div>
                <div class="ink-chart-container" style="padding: 16px 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h3 style="margin: 0;">Last Month</h3>
                        <span id="lastMonthName" style="font-size: 11px; color: #8b8fa3;"></span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Cases</div>
                            <div style="font-size: 20px; font-weight: 700; color: #1a1a2e;" id="lastMonthCases">0</div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Commission</div>
                            <div style="font-size: 20px; font-weight: 700; color: #0d9488;" id="lastMonthComm">$0</div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Approved</div>
                            <div style="font-size: 20px; font-weight: 700; color: #3b82f6;" id="lastMonthApproved">0</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Monthly Trend Chart & Cases by Status -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="ink-chart-container">
                    <h3>Monthly Commission Trend</h3>
                    <div style="height: 220px;">
                        <canvas id="dashboardTrendChart"></canvas>
                    </div>
                </div>
                <div class="ink-chart-container">
                    <h3>Cases by Status</h3>
                    <div style="height: 220px; display: flex; align-items: center; justify-content: center;">
                        <canvas id="dashboardStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Row 4: Commission by Counsel & Top 5 Cases -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="ink-chart-container">
                    <h3>Commission by Counsel</h3>
                    <div id="counselStats"></div>
                </div>
                <div class="ink-chart-container">
                    <h3>Top 5 Highest Commission Cases</h3>
                    <div id="topCasesStats">
                        <div style="padding: 20px; text-align: center; color: #8b8fa3; font-size: 12px;">Loading...</div>
                    </div>
                </div>
            </div>

            <!-- Row 5: Upcoming Deadlines & Recent Activity -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="ink-chart-container">
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #dc2626;">⚠</span> Upcoming Deadlines
                    </h3>
                    <div id="upcomingDeadlines">
                        <div style="padding: 20px; text-align: center; color: #8b8fa3; font-size: 12px;">Loading...</div>
                    </div>
                </div>
                <div class="ink-chart-container">
                    <h3>Commission by Month</h3>
                    <div id="monthStats"></div>
                </div>
            </div>
            </div>
        </div>