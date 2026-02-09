<!-- Command Center Tab -->
<div id="content-overview" class="tab-content hidden">

    <!-- Year Filter -->
    <div class="filters" style="margin-bottom: 16px;">
        <select id="overviewYearFilter" class="f-select" onchange="loadOverviewData()"></select>
        <button class="f-btn" onclick="loadOverviewData()">Refresh</button>
        <span class="f-spacer"></span>
        <span id="overviewLastUpdated" style="font-size: 11px; color: #8b8fa3;"></span>
    </div>

    <!-- Section 0: Quick Stats -->
    <div class="quick-stats" style="grid-template-columns: repeat(6, 1fr); margin-bottom: 16px;">
        <div class="qs-card"><div><div class="qs-label">Total Cases</div><div class="qs-val" id="ovStatTotalCases">0</div></div></div>
        <div class="qs-card"><div><div class="qs-label">Pending</div><div class="qs-val amber" id="ovStatPending">0</div></div></div>
        <div class="qs-card"><div><div class="qs-label">Total Commission</div><div class="qs-val green" id="ovStatTotalComm">$0</div></div></div>
        <div class="qs-card"><div><div class="qs-label">Avg Commission</div><div class="qs-val blue" id="ovStatAvgComm">$0</div></div></div>
        <div class="qs-card"><div><div class="qs-label">Check Received</div><div class="qs-val" id="ovStatCheckRate">0%</div></div></div>
        <div class="qs-card"><div><div class="qs-label">Unreceived</div><div class="qs-val red" id="ovStatUnreceived">$0</div></div></div>
    </div>

    <!-- Section 0.5: This Month vs Last Month -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
        <div class="ink-chart-container" style="padding: 16px 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="margin: 0;">This Month</h3>
                <span id="ovTmThisName" style="font-size: 11px; color: #8b8fa3;"></span>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                <div>
                    <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Cases</div>
                    <div style="font-size: 20px; font-weight: 700; color: #1a1a2e;" id="ovTmThisCases">0</div>
                    <div style="font-size: 10px; margin-top: 2px;" id="ovTmThisCasesChange"></div>
                </div>
                <div>
                    <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Commission</div>
                    <div style="font-size: 20px; font-weight: 700; color: #0d9488;" id="ovTmThisComm">$0</div>
                    <div style="font-size: 10px; margin-top: 2px;" id="ovTmThisCommChange"></div>
                </div>
                <div>
                    <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Approved</div>
                    <div style="font-size: 20px; font-weight: 700; color: #3b82f6;" id="ovTmThisApproved">0</div>
                    <div style="font-size: 10px; margin-top: 2px;" id="ovTmThisApprovedChange"></div>
                </div>
            </div>
        </div>
        <div class="ink-chart-container" style="padding: 16px 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="margin: 0;">Last Month</h3>
                <span id="ovTmLastName" style="font-size: 11px; color: #8b8fa3;"></span>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                <div>
                    <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Cases</div>
                    <div style="font-size: 20px; font-weight: 700; color: #1a1a2e;" id="ovTmLastCases">0</div>
                </div>
                <div>
                    <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Commission</div>
                    <div style="font-size: 20px; font-weight: 700; color: #0d9488;" id="ovTmLastComm">$0</div>
                </div>
                <div>
                    <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 4px;">Approved</div>
                    <div style="font-size: 20px; font-weight: 700; color: #3b82f6;" id="ovTmLastApproved">0</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Monthly Case Flow -->
    <div style="margin-bottom: 20px;">
        <div class="tbl-container">
            <div class="tbl-header">
                <span class="tbl-title">Monthly Case Flow</span>
            </div>
            <table class="tbl" style="table-layout: auto;">
                <thead>
                    <tr>
                        <th style="padding: 10px 14px;">Month</th>
                        <th class="r" style="padding: 10px 14px;">Cases Filed</th>
                        <th class="r" style="padding: 10px 14px;">Settled</th>
                        <th class="r" style="padding: 10px 14px;">Settlement $</th>
                        <th class="r" style="padding: 10px 14px;">Commission</th>
                        <th class="c" style="padding: 10px 14px; width: 40px;"></th>
                    </tr>
                </thead>
                <tbody id="ovMonthlyBody">
                    <tr><td colspan="6" style="text-align: center; padding: 32px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                </tbody>
            </table>
            <div class="tbl-foot">
                <div class="left"><span id="ovMonthlyCount">0</span> months</div>
                <div class="right">
                    <div class="ft"><span class="ft-l">Total Settled:</span><span class="ft-v green" id="ovMonthlyTotalSettled">$0</span></div>
                    <div class="ft"><span class="ft-l">Total Comm:</span><span class="ft-v green" id="ovMonthlyTotalComm">$0</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Cases by Counsel -->
    <div style="margin-bottom: 20px;">
        <div class="tbl-container">
            <div class="tbl-header">
                <span class="tbl-title">Cases by Counsel</span>
            </div>
            <table class="tbl" style="table-layout: auto;">
                <thead>
                    <tr>
                        <th style="padding: 10px 14px;">Counsel</th>
                        <th class="r" style="padding: 10px 14px;">Cases</th>
                        <th class="r" style="padding: 10px 14px;">Settled</th>
                        <th class="r" style="padding: 10px 14px;">Settlement $</th>
                        <th class="r" style="padding: 10px 14px;">Commission</th>
                        <th class="r" style="padding: 10px 14px;">Pending</th>
                        <th class="c" style="padding: 10px 14px; width: 40px;"></th>
                    </tr>
                </thead>
                <tbody id="ovCounselBody">
                    <tr><td colspan="7" style="text-align: center; padding: 32px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                </tbody>
            </table>
            <div class="tbl-foot">
                <div class="left"><span id="ovCounselCount">0</span> counsel</div>
                <div class="right">
                    <div class="ft"><span class="ft-l">Total Comm:</span><span class="ft-v green" id="ovCounselTotalComm">$0</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Monthly Commission Trend -->
    <div>
        <div class="ink-chart-container">
            <h3>Monthly Commission Trend</h3>
            <div style="height: 220px;">
                <canvas id="ovTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Drill-down Modal (for Month and Counsel detail views) -->
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
                            <th style="padding: 8px 12px;">Case #</th>
                            <th style="padding: 8px 12px;">Client</th>
                            <th style="padding: 8px 12px;">Counsel</th>
                            <th style="padding: 8px 12px;">Type</th>
                            <th class="r" style="padding: 8px 12px;">Settled $</th>
                            <th class="r" style="padding: 8px 12px;">Commission</th>
                            <th class="c" style="padding: 8px 12px;">Status</th>
                            <th style="padding: 8px 12px;">Intake</th>
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

</div>
