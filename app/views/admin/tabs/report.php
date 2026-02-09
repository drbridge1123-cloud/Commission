        <!-- Analytics Tab (Command Center + Performance + Reports) -->
        <div id="content-report" class="hidden">

            <!-- Pills -->
            <div class="ac-pills">
                <button class="ac-pill active" id="anPill-overview" onclick="switchAnalyticsSubTab('overview')">Command Center</button>
                <button class="ac-pill" id="anPill-performance" onclick="switchAnalyticsSubTab('performance')">Performance</button>
                <button class="ac-pill" id="anPill-report" onclick="switchAnalyticsSubTab('report')">Reports</button>
            </div>

            <!-- ========== SUB-TAB: COMMAND CENTER ========== -->
            <div id="anSub-overview" class="cc">

                <!-- Year Filter -->
                <div class="filters" style="margin-bottom: 16px;">
                    <select id="overviewYearFilter" class="f-select" onchange="loadOverviewData()"></select>
                    <button class="f-btn" onclick="loadOverviewData()">Refresh</button>
                    <span class="f-spacer"></span>
                    <span id="overviewLastUpdated" style="font-size: 11px; color: #777;"></span>
                </div>

                <!-- Stats Strip -->
                <div class="cc-strip">
                    <div class="cc-strip-item">
                        <div class="cc-strip-label">Total Cases</div>
                        <div class="cc-strip-val" id="ovStatTotalCases">0</div>
                    </div>
                    <div class="cc-strip-item">
                        <div class="cc-strip-label">Pending</div>
                        <div class="cc-strip-val cc-amber" id="ovStatPending">0</div>
                    </div>
                    <div class="cc-strip-item">
                        <div class="cc-strip-label">Total Commission</div>
                        <div class="cc-strip-val cc-green" id="ovStatTotalComm">$0</div>
                    </div>
                    <div class="cc-strip-item">
                        <div class="cc-strip-label">Avg Commission</div>
                        <div class="cc-strip-val" id="ovStatAvgComm">$0</div>
                    </div>
                    <div class="cc-strip-item">
                        <div class="cc-strip-label">Check Received</div>
                        <div class="cc-strip-val" id="ovStatCheckRate">0%</div>
                    </div>
                    <div class="cc-strip-item">
                        <div class="cc-strip-label">Unreceived</div>
                        <div class="cc-strip-val cc-red" id="ovStatUnreceived">$0</div>
                    </div>
                </div>

                <!-- Two Column Layout -->
                <div class="cc-grid">

                    <!-- Left Column (sticky) -->
                    <div class="cc-left">

                        <!-- Commission Card -->
                        <div class="cc-card">
                            <div class="cc-card-label">Total Commission</div>
                            <div class="cc-card-val" id="ovCardComm">$0</div>
                            <div class="cc-card-desc">
                                <div><strong id="ovCardCaseCount">0</strong> total cases</div>
                                <div>Avg <strong id="ovCardAvg">$0</strong>/case</div>
                                <div><strong id="ovCardCheckPct">0%</strong> checks received</div>
                            </div>
                        </div>

                        <!-- This Month Card -->
                        <div class="cc-month-card cc-month-current">
                            <div class="cc-month-header">
                                <span class="cc-month-title">This Month</span>
                                <span class="cc-month-period" id="ovTmThisName"></span>
                            </div>
                            <div class="cc-month-row">
                                <span class="cc-month-label">Cases</span>
                                <span class="cc-month-val">
                                    <span id="ovTmThisCases">0</span>
                                    <span id="ovTmThisCasesChange"></span>
                                </span>
                            </div>
                            <div class="cc-month-row">
                                <span class="cc-month-label">Commission</span>
                                <span class="cc-month-val">
                                    <span class="cc-green" id="ovTmThisComm">$0</span>
                                    <span id="ovTmThisCommChange"></span>
                                </span>
                            </div>
                            <div class="cc-month-row cc-month-row-last">
                                <span class="cc-month-label">Approved</span>
                                <span class="cc-month-val"><span id="ovTmThisApproved">0</span></span>
                            </div>
                        </div>

                        <!-- Last Month Card -->
                        <div class="cc-month-card">
                            <div class="cc-month-header">
                                <span class="cc-month-title">Last Month</span>
                                <span class="cc-month-period" id="ovTmLastName"></span>
                            </div>
                            <div class="cc-month-row">
                                <span class="cc-month-label">Cases</span>
                                <span class="cc-month-val"><span id="ovTmLastCases">0</span></span>
                            </div>
                            <div class="cc-month-row">
                                <span class="cc-month-label">Commission</span>
                                <span class="cc-month-val"><span class="cc-green" id="ovTmLastComm">$0</span></span>
                            </div>
                            <div class="cc-month-row cc-month-row-last">
                                <span class="cc-month-label">Approved</span>
                                <span class="cc-month-val"><span id="ovTmLastApproved">0</span></span>
                            </div>
                        </div>

                    </div><!-- /cc-left -->

                    <!-- Right Column -->
                    <div class="cc-right">

                        <!-- Monthly Case Flow -->
                        <div class="cc-section">
                            <div class="cc-section-header">Monthly Case Flow</div>
                            <table class="cc-table">
                                <thead>
                                    <tr>
                                        <th data-sort="date">Month</th>
                                        <th class="r" data-sort="number">Cases Filed</th>
                                        <th class="r" data-sort="number">Settled</th>
                                        <th class="r" data-sort="number">Settlement $</th>
                                        <th class="r" data-sort="number">Disc. Fee</th>
                                        <th class="r" data-sort="number">Commission</th>
                                        <th class="r" data-sort="number">%</th>
                                        <th class="r" style="width: 30px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="ovMonthlyBody">
                                    <tr><td colspan="8" class="cc-empty">Loading...</td></tr>
                                </tbody>
                            </table>
                            <div class="cc-table-foot">
                                <span><span id="ovMonthlyCount">0</span> months</span>
                                <span>Total Settled: <strong class="cc-green" id="ovMonthlyTotalSettled">$0</strong> &nbsp; Disc. Fee: <strong class="cc-green" id="ovMonthlyTotalDiscFee">$0</strong> &nbsp; Total Comm: <strong class="cc-green" id="ovMonthlyTotalComm">$0</strong></span>
                            </div>
                        </div>

                        <!-- Cases by Counsel -->
                        <div class="cc-section">
                            <div class="cc-section-header">Cases by Counsel</div>
                            <table class="cc-table">
                                <thead>
                                    <tr>
                                        <th data-sort="text">Counsel</th>
                                        <th class="r" data-sort="number">Cases</th>
                                        <th class="r" data-sort="number">Settled</th>
                                        <th class="r" data-sort="number">Settlement $</th>
                                        <th class="r" data-sort="number">Commission</th>
                                        <th class="r" data-sort="number">Pending</th>
                                        <th class="r" style="width: 30px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="ovCounselBody">
                                    <tr><td colspan="7" class="cc-empty">Loading...</td></tr>
                                </tbody>
                            </table>
                            <div class="cc-table-foot">
                                <span><span id="ovCounselCount">0</span> counsel</span>
                                <span>Total Comm: <strong class="cc-green" id="ovCounselTotalComm">$0</strong></span>
                            </div>
                        </div>

                    </div><!-- /cc-right -->

                </div><!-- /cc-grid -->

            </div><!-- /anSub-overview -->

            <!-- ========== SUB-TAB: PERFORMANCE ========== -->
            <div id="anSub-performance" style="display:none;">

                <!-- Sub-tab Buttons + Filters -->
                <div class="filters" style="margin-bottom: 16px;">
                    <div style="display: flex; gap: 0; margin-right: 12px;">
                        <button id="perfSubTabAttorney" class="f-btn" onclick="switchPerfSubTab('attorney')" style="border-radius: 6px 0 0 6px; background: #1a1a2e; color: #fff; border: 1px solid #1a1a2e;">Attorney</button>
                        <button id="perfSubTabEmployee" class="f-btn" onclick="switchPerfSubTab('employee')" style="border-radius: 0 6px 6px 0; background: transparent; color: #3d3f4e; border: 1px solid #e2e4ea;">Employee</button>
                    </div>
                    <select id="perfAttorneyFilter" class="f-select" onchange="loadAttorneyPerformance()" style="display: inline-block;">
                        <!-- Populated by JS -->
                    </select>
                    <select id="perfYearFilter" class="f-select" onchange="loadCurrentPerfSubTab()">
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                    <span class="f-spacer"></span>
                    <button class="f-btn" onclick="loadCurrentPerfSubTab()" style="background: #1a1a2e; color: #fff; border: none;">Refresh</button>
                </div>

                <!-- Attorney Sub-tab Content -->
                <div id="perfAttorneyContent">
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
                    <div class="panel" id="perfAnalyticsPanel">
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
                </div>

                <!-- Employee Sub-tab Content -->
                <div id="perfEmployeeContent" style="display: none;">
                    <div class="tbl-container">
                        <div class="tbl-header">
                            <span class="tbl-title">Employee Performance & Goals</span>
                        </div>
                        <table class="tbl" style="table-layout: auto;">
                            <thead>
                                <tr>
                                    <th data-sort="text" style="padding: 10px 14px;">Employee</th>
                                    <th class="r" data-sort="number" style="padding: 10px 14px;">Cases</th>
                                    <th style="padding: 10px 14px; width: 100px;">Progress</th>
                                    <th class="r" data-sort="number" style="padding: 10px 14px;">Legal Fee</th>
                                    <th style="padding: 10px 14px; width: 100px;">Progress</th>
                                    <th class="r" data-sort="number" style="padding: 10px 14px;">Commission</th>
                                    <th class="r" data-sort="number" style="padding: 10px 14px;">Avg/Case</th>
                                    <th class="c" style="padding: 10px 14px;">Pace</th>
                                    <th class="c" style="padding: 10px 14px; width: 50px;">Edit</th>
                                </tr>
                            </thead>
                            <tbody id="perfEmployeeBody">
                                <tr><td colspan="9" style="text-align: center; padding: 20px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div><!-- /anSub-performance -->

            <!-- ========== SUB-TAB: REPORTS ========== -->
            <div id="anSub-report" style="display:none;">

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
                                    <th data-sort="text">Counsel</th>
                                    <th class="r" data-sort="number">Cases</th>
                                    <th class="r" data-sort="number">Total</th>
                                    <th class="r" data-sort="number">Avg</th>
                                    <th class="r" data-sort="number">%</th>
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
                                    <th data-sort="text">Type</th>
                                    <th class="r" data-sort="number">Cases</th>
                                    <th class="r" data-sort="number">Total</th>
                                    <th class="r" data-sort="number">Avg</th>
                                    <th class="r" data-sort="number">%</th>
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
                                <th data-sort="date">Month</th>
                                <th class="r" data-sort="number">Cases</th>
                                <th class="r" data-sort="number">Total</th>
                                <th class="r" data-sort="number">Avg</th>
                                <th class="r" data-sort="number">Received</th>
                                <th class="r" data-sort="number">Pending</th>
                            </tr>
                        </thead>
                        <tbody id="monthlyBreakdownTableBody">
                            <tr><td colspan="6" style="text-align: center; padding: 20px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                </div>

            </div><!-- /anSub-report -->

            <!-- Drill-down Modal (Command Center month/counsel detail) -->
            <div id="ovDrillModal" class="modal-overlay" onclick="if(event.target === this) closeModal('ovDrillModal')">
                <div class="modal-content m-shell" style="max-width: 900px;" onclick="event.stopPropagation()">
                    <div class="m-header">
                        <div class="m-header-title"><h2 id="ovDrillTitle">Cases</h2></div>
                        <button onclick="closeModal('ovDrillModal')" class="m-close">&times;</button>
                    </div>
                    <div class="m-body" style="padding: 0; max-height: 500px; overflow-y: auto;">
                        <table class="tbl" style="table-layout: auto;">
                            <thead>
                                <tr>
                                    <th data-sort="text" style="padding: 8px 12px;">Case #</th>
                                    <th data-sort="text" style="padding: 8px 12px;">Client</th>
                                    <th data-sort="text" style="padding: 8px 12px;">Counsel</th>
                                    <th data-sort="text" style="padding: 8px 12px;">Type</th>
                                    <th class="r" data-sort="number" style="padding: 8px 12px;">Settled $</th>
                                    <th class="r" data-sort="number" style="padding: 8px 12px;">Commission</th>
                                    <th class="c" data-sort="text" style="padding: 8px 12px;">Status</th>
                                    <th data-sort="date" style="padding: 8px 12px;">Intake</th>
                                </tr>
                            </thead>
                            <tbody id="ovDrillBody">
                                <tr><td colspan="8" style="text-align:center; padding:32px; color:#8b8fa3;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="m-footer">
                        <span id="ovDrillSummary" style="font-size: 12px; color: #8b8fa3;"></span>
                        <span style="flex:1;"></span>
                        <button onclick="closeModal('ovDrillModal')" class="m-btn m-btn-secondary">Close</button>
                    </div>
                </div>
            </div>

            <!-- Goal Edit Modal (Performance employee goals) -->
            <div id="perfEditGoalModal" class="modal-overlay hidden" onclick="if(event.target === this) closeModal('perfEditGoalModal')">
                <div class="modal-content m-shell" style="max-width: 400px;" onclick="event.stopPropagation()">
                    <div class="m-header">
                        <div class="m-header-title"><h2 id="perfGoalTitle">Edit Goal</h2></div>
                        <button onclick="closeModal('perfEditGoalModal')" class="m-close">&times;</button>
                    </div>
                    <div class="m-body">
                        <input type="hidden" id="perfGoalUserId">
                        <div style="margin-bottom: 12px;">
                            <label class="ink-label">Year</label>
                            <input type="number" id="perfGoalYear" class="ink-input" min="2020" max="2030">
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label class="ink-label">Target Cases</label>
                            <input type="number" id="perfGoalCases" class="ink-input" min="1" max="999" value="50">
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label class="ink-label">Target Legal Fee ($)</label>
                            <input type="number" id="perfGoalFee" class="ink-input" min="0" step="1000" value="500000">
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label class="ink-label">Notes</label>
                            <textarea id="perfGoalNotes" class="ink-input" rows="2" style="resize: vertical;"></textarea>
                        </div>
                    </div>
                    <div class="m-footer">
                        <span style="flex: 1;"></span>
                        <button onclick="closeModal('perfEditGoalModal')" class="m-btn m-btn-secondary">Cancel</button>
                        <button onclick="savePerfGoal()" class="m-btn m-btn-primary">Save</button>
                    </div>
                </div>
            </div>

        </div>
