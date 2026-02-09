        <div id="content-demand" class="tab-content hidden">
            <!-- Overdue Alert -->
            <div id="demandOverdueAlert" class="alert-overdue" style="display:none;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><span class="count" id="overdueCount">0</span> case overdue — Immediate action required</span>
            </div>

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="qs-card" onclick="clickDemandStat('all')" style="cursor:pointer;">
                    <span class="qs-label">Total Demand</span>
                    <span class="qs-val" id="demandStatTotal">0</span>
                </div>
                <div class="qs-card" onclick="clickDemandStat('due2weeks')" style="cursor:pointer;">
                    <span class="qs-label">Due in 2 Weeks</span>
                    <span class="qs-val amber" id="demandStatDue2Weeks">0</span>
                </div>
                <div class="qs-card" onclick="clickDemandStat('overdue')" style="cursor:pointer;">
                    <span class="qs-label">Overdue</span>
                    <span class="qs-val red" id="demandStatOverdue">0</span>
                </div>
                <div class="qs-card" id="demandStageCard">
                    <span class="qs-label">Stage</span>
                    <span class="qs-val" id="demandStatStage" style="font-size: 14px; color: #8b8fa3;">Select a case</span>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <span class="f-chip active" data-filter="all" onclick="setDemandFilter('all', this)">All</span>
                <span class="f-chip" data-filter="due2weeks" onclick="setDemandFilter('due2weeks', this)" style="background:#fef3c7;color:#b45309;border-color:#fde68a;">Due in 2 Weeks</span>
                <span class="f-chip" data-filter="overdue" onclick="setDemandFilter('overdue', this)" style="background:#fef2f2;color:#b91c1c;border-color:#fecaca;">Overdue</span>
                <div class="f-spacer"></div>
                <input class="f-search" type="text" id="demandSearch" placeholder="Search..." onkeyup="filterDemandCases()">
                <button class="f-btn" data-action="new-demand">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Demand Case
                </button>
            </div>

            <!-- Table -->
            <div class="tbl-container">
                <table class="tbl" id="demandTable">
                    <thead>
                        <tr>
                            <th style="width:0;padding:0;border:none;"></th>
                            <th data-sort="text"><span class="th-sort" onclick="sortDemandCases('case_number')">Case # <span class="sort-arrow">▼</span></span></th>
                            <th data-sort="text"><span class="th-sort" onclick="sortDemandCases('client_name')">Client Name <span class="sort-arrow">▼</span></span></th>
                            <th data-sort="text"><span class="th-sort" onclick="sortDemandCases('case_type')">Case Type <span class="sort-arrow">▼</span></span></th>
                            <th data-sort="text"><span class="th-sort" onclick="sortDemandCases('stage')">Stage <span class="sort-arrow">▼</span></span></th>
                            <th data-sort="text"><span class="th-sort" onclick="sortDemandCases('assigned_date')">Assigned <span class="sort-arrow">▼</span></span></th>
                            <th data-sort="date"><span class="th-sort" onclick="sortDemandCases('demand_deadline')">Deadline <span class="sort-arrow">▼</span></span></th>
                            <th data-sort="number"><span class="th-sort" onclick="sortDemandCases('days_left')">Days Left <span class="sort-arrow">▼</span></span></th>
                            <th data-sort="text"><span class="th-sort" onclick="sortDemandCases('status')">Status <span class="sort-arrow">▼</span></span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="demandTableBody">
                        <tr><td colspan="10" style="text-align:center; padding: 40px; color: #8b8fa3;">Loading...</td></tr>
                    </tbody>
                </table>
                <div class="tbl-foot">
                    <span class="left" id="demandFooterLeft">0 demand cases</span>
                    <span class="left" id="demandFooterRight">Due in 2 Weeks: 0 · Overdue: 0</span>
                </div>
            </div>
        </div>