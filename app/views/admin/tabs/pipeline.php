<!-- Case Tracker Tab (Pipeline + Deadline Requests) -->
<div id="content-pipeline" class="tab-content hidden">

    <!-- Pills -->
    <div class="ac-pills">
        <button class="ac-pill active" id="plPill-pipeline" onclick="switchPipelineSubTab('pipeline')">Pipeline</button>
        <button class="ac-pill" id="plPill-deadline" onclick="switchPipelineSubTab('deadline')">
            Deadline Requests <span id="plPillDeadlineCount" class="ac-pill-count" style="display:none;">0</span>
        </button>
    </div>

    <!-- ========== SUB-TAB: PIPELINE ========== -->
    <div id="plSub-pipeline">

        <!-- Filters -->
        <div class="filters" style="margin-bottom: 16px;">
            <select id="pipelineAttorneyFilter" class="f-select" onchange="loadPipelineData()"></select>
            <select id="pipelineYearFilter" class="f-select" onchange="loadPipelineData()"></select>
            <button class="f-btn" onclick="loadPipelineData()">Refresh</button>
            <span class="f-spacer"></span>
            <span id="pipelineLastUpdated" style="font-size: 11px; color: #8b8fa3;"></span>
        </div>

        <!-- Pipeline Stats -->
        <div class="quick-stats" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
            <div class="qs-card"><div><div class="qs-label">Demand Active</div><div class="qs-val blue" id="plDemandActive">0</div></div></div>
            <div class="qs-card"><div><div class="qs-label">Litigation Active</div><div class="qs-val" style="color: #6366f1;" id="plLitActive">0</div></div></div>
            <div class="qs-card"><div><div class="qs-label">Overdue</div><div class="qs-val red" id="plOverdue">0</div></div></div>
            <div class="qs-card"><div><div class="qs-label">At Risk (≤14d)</div><div class="qs-val amber" id="plUrgent">0</div></div></div>
        </div>

        <!-- Active Cases Pipeline -->
        <div style="margin-bottom: 20px;">
            <div class="tbl-container">
                <div class="tbl-header">
                    <span class="tbl-title">Active Cases Pipeline</span>
                    <div class="filters" style="margin: 0; gap: 4px;">
                        <button class="f-btn" id="plFilterAll" onclick="filterPipeline('all')" style="font-size: 11px; padding: 3px 10px;">All</button>
                        <button class="f-btn" id="plFilterDemand" onclick="filterPipeline('demand')" style="font-size: 11px; padding: 3px 10px; background: transparent; color: #3d3f4e; border: 1px solid #e2e4ea;">Demand</button>
                        <button class="f-btn" id="plFilterLit" onclick="filterPipeline('litigation')" style="font-size: 11px; padding: 3px 10px; background: transparent; color: #3d3f4e; border: 1px solid #e2e4ea;">Litigation</button>
                    </div>
                </div>
                <div style="max-height: 500px; overflow-y: auto;">
                    <table class="tbl" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th data-sort="text">Case #</th>
                                <th data-sort="text">Client</th>
                                <th class="c" data-sort="text">Phase</th>
                                <th data-sort="date">Started</th>
                                <th class="r" data-sort="number">Days in Phase</th>
                                <th data-sort="date">Deadline</th>
                                <th class="c" data-sort="number">Time Left</th>
                            </tr>
                        </thead>
                        <tbody id="plPipelineBody">
                            <tr><td colspan="7" style="text-align: center; padding: 32px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="tbl-foot">
                    <div class="left"><span id="plPipelineCount">0</span> active cases</div>
                    <div class="right">
                        <div class="ft"><span class="ft-l">Avg Days:</span><span class="ft-v" id="plAvgDays">0</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Case Flow -->
        <div>
            <div class="tbl-container">
                <div class="tbl-header">
                    <span class="tbl-title">Monthly Case Flow</span>
                </div>
                <table class="tbl" style="table-layout: auto;">
                    <thead>
                        <tr>
                            <th data-sort="date">Month</th>
                            <th class="r" data-sort="number">New Cases</th>
                            <th class="r" data-sort="number">→ Litigation</th>
                            <th class="r" data-sort="number">Demand Settled</th>
                            <th class="r" data-sort="number">Lit. Settled</th>
                            <th class="r" data-sort="number">Net Active</th>
                        </tr>
                    </thead>
                    <tbody id="plFlowBody">
                        <tr><td colspan="6" style="text-align: center; padding: 32px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                    </tbody>
                </table>
                <div class="tbl-foot">
                    <div class="left">Year total</div>
                    <div class="right">
                        <div class="ft"><span class="ft-l">New:</span><span class="ft-v" id="plFlowNewTotal">0</span></div>
                        <div class="ft"><span class="ft-l">→Lit:</span><span class="ft-v" id="plFlowLitTotal">0</span></div>
                        <div class="ft"><span class="ft-l">Settled:</span><span class="ft-v green" id="plFlowSettledTotal">0</span></div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /plSub-pipeline -->

    <!-- ========== SUB-TAB: DEADLINE REQUESTS ========== -->
    <div id="plSub-deadline" style="display:none;">

        <div class="filters" style="margin-bottom: 12px;">
            <select id="filterDeadlineStatus" onchange="loadDeadlineRequests()" class="f-select">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="all">All Requests</option>
            </select>
            <span class="f-spacer"></span>
            <input type="text" id="deadlineSearchInput" placeholder="Search requests..." class="f-search" onkeyup="filterDeadlineRequestsTable()">
        </div>

        <div class="tbl-container">
            <div class="table-scroll-wrapper scrollbar-fixed">
                <table class="tbl" id="deadlineRequestsTable" style="table-layout: auto;">
                    <thead>
                        <tr>
                            <th data-sort="date" style="padding: 10px 14px;">Date Requested</th>
                            <th data-sort="text" style="padding: 10px 14px;">Employee</th>
                            <th data-sort="text" style="padding: 10px 14px;">Case #</th>
                            <th data-sort="text" style="padding: 10px 14px;">Client</th>
                            <th data-sort="date" style="padding: 10px 14px;">Current Deadline</th>
                            <th data-sort="date" style="padding: 10px 14px;">Requested Deadline</th>
                            <th data-sort="text" style="padding: 10px 14px;">Reason</th>
                            <th class="c" data-sort="text" style="padding: 10px 14px;">Status</th>
                            <th class="c" style="padding: 10px 14px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="deadlineRequestsBody">
                        <tr><td colspan="9" style="text-align: center; padding: 40px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="tbl-foot">
                <div class="left"><span id="deadlineRequestsCount">0 requests</span></div>
            </div>
        </div>

    </div><!-- /plSub-deadline -->

</div>
