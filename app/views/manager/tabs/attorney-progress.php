        <!-- Attorney Progress Tab -->
        <div id="content-attorney-progress" class="tab-content hidden">

            <!-- Stats Strip -->
            <div class="quick-stats" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 20px;">
                <div class="qs-card"><div><div class="qs-label">Demand Active</div><div class="qs-val blue" id="attyStatDemand">0</div></div></div>
                <div class="qs-card"><div><div class="qs-label">Litigation Active</div><div class="qs-val" style="color: #6366f1;" id="attyStatLitigation">0</div></div></div>
                <div class="qs-card"><div><div class="qs-label">Traffic Active</div><div class="qs-val amber" id="attyStatTraffic">0</div></div></div>
            </div>

            <!-- Sub-tab filters + button -->
            <div class="filters" style="margin-bottom: 16px;">
                <span class="f-chip active" id="attyPill-demand" onclick="switchAttySubTab('demand')">Demand</span>
                <span class="f-chip" id="attyPill-litigation" onclick="switchAttySubTab('litigation')">Litigation</span>
                <span class="f-chip" id="attyPill-traffic" onclick="switchAttySubTab('traffic')">Traffic</span>
                <span class="f-chip" id="attyPill-requests" onclick="switchAttySubTab('requests')">
                    My Requests <span class="ac-pill-count" id="attyReqBadge" style="display:none;">0</span>
                </span>
                <span class="f-chip" id="attyPill-traffic-requests" onclick="switchAttySubTab('traffic-requests')">
                    Traffic Requests <span class="ac-pill-count" id="attyTrafficReqBadge" style="display:none;">0</span>
                </span>
                <span class="f-spacer"></span>
                <button class="f-btn" onclick="openDemandRequestForm()" id="attyNewDemandBtn" style="font-size:11px; padding:4px 12px;">+ Request New Demand</button>
                <button class="f-btn" onclick="openTrafficRequestForm()" id="attyNewTrafficBtn" style="font-size:11px; padding:4px 12px; display:none;">+ Request New Traffic</button>
            </div>

            <!-- ===== DEMAND SUB-TAB ===== -->
            <div id="attySubContent-demand">
                <div class="tbl-container">
                    <div class="tbl-header">
                        <span class="tbl-title">Demand Cases</span>
                        <input type="text" id="attyDemandSearch" class="f-search" placeholder="Search..." style="width:225px !important; margin-left:12px; margin-right:auto;" onkeyup="filterAttyTable('demand')">
                        <span class="tbl-count"><span id="attyDemandCount">0</span> cases</span>
                    </div>
                    <div style="max-height: 600px; overflow-y: auto;">
                        <table class="tbl" style="table-layout: auto;">
                            <thead>
                                <tr>
                                    <th data-sort="text">Case #</th>
                                    <th data-sort="text">Client</th>
                                    <th data-sort="text">Type</th>
                                    <th data-sort="text">Stage</th>
                                    <th data-sort="date">Assigned</th>
                                    <th data-sort="date">Demand Out</th>
                                    <th data-sort="date">Negotiate</th>
                                    <th data-sort="date">Top Offer</th>
                                    <th data-sort="date">Deadline</th>
                                    <th data-sort="number">Days Left</th>
                                    <th data-sort="text">Status</th>
                                </tr>
                            </thead>
                            <tbody id="attyDemandBody">
                                <tr><td colspan="11" style="text-align:center; padding:40px; color:#8b8fa3; font-size:12px;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="tbl-foot">
                        <div class="left"><span id="attyDemandCount2">0</span> demand cases</div>
                    </div>
                </div>
            </div>

            <!-- ===== LITIGATION SUB-TAB ===== -->
            <div id="attySubContent-litigation" style="display:none;">
                <div class="tbl-container">
                    <div class="tbl-header">
                        <span class="tbl-title">Litigation Cases</span>
                        <input type="text" id="attyLitigationSearch" class="f-search" placeholder="Search..." style="width:225px !important; margin-left:12px; margin-right:auto;" onkeyup="filterAttyTable('litigation')">
                        <span class="tbl-count"><span id="attyLitigationCount">0</span> cases</span>
                    </div>
                    <table class="tbl" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th data-sort="text">Case #</th>
                                <th data-sort="text">Client</th>
                                <th data-sort="text">Type</th>
                                <th data-sort="text">Resolution</th>
                                <th data-sort="date">Lit Start</th>
                                <th data-sort="text">Status</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody id="attyLitigationBody">
                            <tr><td colspan="7" style="text-align:center; padding:40px; color:#8b8fa3; font-size:12px;">Loading...</td></tr>
                        </tbody>
                    </table>
                    <div class="tbl-foot">
                        <div class="left"><span id="attyLitigationCount3">0</span> litigation cases</div>
                    </div>
                </div>
            </div>

            <!-- ===== TRAFFIC SUB-TAB ===== -->
            <div id="attySubContent-traffic" style="display:none;">
                <div class="tbl-container">
                    <div class="tbl-header">
                        <span class="tbl-title">Traffic Cases</span>
                        <input type="text" id="attyTrafficSearch" class="f-search" placeholder="Search..." style="width:225px !important; margin-left:12px; margin-right:auto;" onkeyup="filterAttyTable('traffic')">
                        <span class="tbl-count"><span id="attyTrafficCount">0</span> cases</span>
                    </div>
                    <table class="tbl" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th data-sort="text">Client</th>
                                <th data-sort="text">Case #</th>
                                <th data-sort="text">Court</th>
                                <th data-sort="text">Charge</th>
                                <th data-sort="date">Court Date</th>
                                <th class="c" data-sort="text">Discovery</th>
                                <th data-sort="text">Disposition</th>
                                <th class="c" data-sort="text">Status</th>
                                <th data-sort="text">Requester</th>
                            </tr>
                        </thead>
                        <tbody id="attyTrafficBody">
                            <tr><td colspan="9" style="text-align:center; padding:40px; color:#8b8fa3; font-size:12px;">Loading...</td></tr>
                        </tbody>
                    </table>
                    <div class="tbl-foot">
                        <div class="left"><span id="attyTrafficCount3">0</span> traffic cases</div>
                    </div>
                </div>
            </div>

            <!-- ===== MY REQUESTS SUB-TAB ===== -->
            <div id="attySubContent-requests" style="display:none;">
                <div class="tbl-container">
                    <div class="tbl-header">
                        <span class="tbl-title">My Demand Requests</span>
                        <input type="text" id="attyRequestsSearch" class="f-search" placeholder="Search..." style="width:225px !important; margin-left:12px; margin-right:auto;" onkeyup="filterAttyTable('requests')">
                        <span class="tbl-count"><span id="attyMyRequestsCount">0</span> requests</span>
                    </div>
                    <table class="tbl" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th data-sort="text">Client</th>
                                <th data-sort="text">Case #</th>
                                <th data-sort="text">Case Type</th>
                                <th>Note</th>
                                <th class="c" data-sort="text">Status</th>
                                <th data-sort="date">Submitted</th>
                                <th data-sort="date">Responded</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody id="attyMyRequestsBody">
                            <tr><td colspan="8" style="text-align:center; padding:40px; color:#8b8fa3; font-size:12px;">Loading...</td></tr>
                        </tbody>
                    </table>
                    <div class="tbl-foot">
                        <div class="left"><span id="attyMyRequestsCount3">0</span> requests</div>
                    </div>
                </div>
            </div>

            <!-- ===== TRAFFIC REQUESTS SUB-TAB ===== -->
            <div id="attySubContent-traffic-requests" style="display:none;">
                <div class="tbl-container">
                    <div class="tbl-header">
                        <span class="tbl-title">My Traffic Requests</span>
                        <input type="text" id="attyTrafficRequestsSearch" class="f-search" placeholder="Search..." style="width:225px !important; margin-left:12px; margin-right:auto;" onkeyup="filterAttyTable('traffic-requests')">
                        <span class="tbl-count"><span id="attyMyTrafficRequestsCount">0</span> requests</span>
                    </div>
                    <table class="tbl" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th data-sort="text">Client</th>
                                <th data-sort="text">Ticket #</th>
                                <th data-sort="text">Court</th>
                                <th data-sort="text">Charge</th>
                                <th data-sort="date">Court Date</th>
                                <th>Note</th>
                                <th class="c" data-sort="text">Status</th>
                                <th data-sort="date">Submitted</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody id="attyMyTrafficRequestsBody">
                            <tr><td colspan="9" style="text-align:center; padding:40px; color:#8b8fa3; font-size:12px;">Loading...</td></tr>
                        </tbody>
                    </table>
                    <div class="tbl-foot">
                        <div class="left"><span id="attyMyTrafficRequestsCount3">0</span> requests</div>
                    </div>
                </div>
            </div>

        </div>
