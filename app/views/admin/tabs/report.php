        <!-- Reports Tab -->
        <div id="content-report" class="hidden">
            <!-- Filters -->
            <div class="filters" style="margin-bottom: 16px;">
                <button onclick="exportReportToExcel()" class="ink-btn ink-btn-secondary ink-btn-sm">Export Excel</button>
            </div>

            <div id="reportCard">

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="qs-card">
                    <div><div class="qs-label">Monthly Summary</div><div class="qs-val blue" id="report-monthly-amount">$0</div></div>
                    <div style="font-size: 11px; color: #8b8fa3;" id="report-monthly-cases">0 cases</div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Year-to-Date</div><div class="qs-val green" id="report-ytd-amount">$0</div></div>
                    <div style="font-size: 11px; color: #8b8fa3;" id="report-ytd-cases">0 cases</div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Average Commission</div><div class="qs-val" id="report-avg-amount">$0</div></div>
                    <div style="font-size: 11px; color: #8b8fa3;">per case</div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Pending Payments</div><div class="qs-val amber" id="report-pending-amount">$0</div></div>
                    <div style="font-size: 11px; color: #8b8fa3;" id="report-pending-cases">0 cases</div>
                </div>
            </div>

            <!-- Commission Trend Chart -->
            <div class="ink-chart-container" style="margin-bottom: 16px;">
                <h3>Commission by Month</h3>
                <div style="height: 280px;">
                    <canvas id="commissionByMonthChart"></canvas>
                </div>
            </div>

            <!-- Analysis Tables Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <!-- By Counsel Table -->
                <div class="tbl-container">
                    <div style="padding: 12px 16px; background: #1a1a2e; border-radius: 10px 10px 0 0;">
                        <h3 style="font-size: 13px; font-weight: 600; color: #fff; font-family: 'Outfit', sans-serif;">Commission by Counsel</h3>
                    </div>
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Counsel</th>
                                <th class="r">Cases</th>
                                <th class="r">Total</th>
                                <th class="r">Avg</th>
                                <th class="r">%</th>
                            </tr>
                        </thead>
                        <tbody id="counselTableBody">
                            <tr><td colspan="5" style="text-align: center; padding: 20px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- By Case Type Table -->
                <div class="tbl-container">
                    <div style="padding: 12px 16px; background: #1a1a2e; border-radius: 10px 10px 0 0;">
                        <h3 style="font-size: 13px; font-weight: 600; color: #fff; font-family: 'Outfit', sans-serif;">Commission by Case Type</h3>
                    </div>
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th class="r">Cases</th>
                                <th class="r">Total</th>
                                <th class="r">Avg</th>
                                <th class="r">%</th>
                            </tr>
                        </thead>
                        <tbody id="caseTypeTableBody">
                            <tr><td colspan="5" style="text-align: center; padding: 20px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Monthly Breakdown Table -->
            <div class="tbl-container">
                <div style="padding: 12px 16px; background: #1a1a2e; border-radius: 10px 10px 0 0;">
                    <h3 style="font-size: 13px; font-weight: 600; color: #fff; font-family: 'Outfit', sans-serif;">Monthly Breakdown</h3>
                </div>
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="r">Cases</th>
                            <th class="r">Total</th>
                            <th class="r">Avg</th>
                            <th class="r">Received</th>
                            <th class="r">Pending</th>
                        </tr>
                    </thead>
                    <tbody id="monthlyBreakdownTableBody">
                        <tr><td colspan="6" style="text-align: center; padding: 20px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
            </div>
        </div>