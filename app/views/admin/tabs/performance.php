        <!-- Performance Analytics Tab -->
        <div id="content-performance" class="hidden">

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

            <!-- ======== Attorney Sub-tab Content ======== -->
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

            <!-- ======== Employee Sub-tab Content ======== -->
            <div id="perfEmployeeContent" style="display: none;">
                <div class="tbl-container">
                    <div class="tbl-header">
                        <span class="tbl-title">Employee Performance & Goals</span>
                    </div>
                    <table class="tbl" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th style="padding: 10px 14px;">Employee</th>
                                <th class="r" style="padding: 10px 14px;">Cases</th>
                                <th style="padding: 10px 14px; width: 100px;">Progress</th>
                                <th class="r" style="padding: 10px 14px;">Legal Fee</th>
                                <th style="padding: 10px 14px; width: 100px;">Progress</th>
                                <th class="r" style="padding: 10px 14px;">Commission</th>
                                <th class="r" style="padding: 10px 14px;">Avg/Case</th>
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

            <!-- Goal Edit Modal -->
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