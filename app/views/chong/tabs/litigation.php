        <div id="content-litigation" class="tab-content hidden">
            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="qs-card">
                    <span class="qs-label">Total Litigation</span>
                    <span class="qs-val" id="litStatTotal">0</span>
                </div>
                <div class="qs-card">
                    <span class="qs-label">Active Cases</span>
                    <span class="qs-val blue" id="litStatActive">0</span>
                </div>
                <div class="qs-card">
                    <span class="qs-label">Settled</span>
                    <span class="qs-val green" id="litStatSettled">0</span>
                </div>
                <div class="qs-card">
                    <span class="qs-label">Avg Duration</span>
                    <span class="qs-val amber" id="litStatAvgDuration">0d</span>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <span class="f-chip active" data-filter="all" onclick="setLitigationFilter('all', this)">All</span>
                <span class="f-chip" data-filter="active" onclick="setLitigationFilter('active', this)">Active</span>
                <span class="f-chip" data-filter="settled" onclick="setLitigationFilter('settled', this)">Settled</span>
                <div class="f-spacer"></div>
                <input class="f-search" type="text" id="litigationSearch" placeholder="Search..." onkeyup="filterLitigationCases()">
                <button class="f-btn" data-action="add-litigation" onclick="openAddLitigationModal()">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Case
                </button>
            </div>

            <!-- Table -->
            <div class="tbl-container">
                <table class="tbl" id="litigationTable">
                    <thead>
                        <tr>
                            <th style="width:0;padding:0;border:none;"></th>
                            <th><span class="th-sort" onclick="sortLitigationCases('case_number')">Case # <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortLitigationCases('client_name')">Client <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortLitigationCases('litigation_start_date')">Lit. Start <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortLitigationCases('litigation_duration_days')">Duration <span class="sort-arrow">▼</span></span></th>
                            <th class="r"><span class="th-sort" onclick="sortLitigationCases('presuit_offer')">Pre-Suit Offer <span class="sort-arrow">▼</span></span></th>
                            <th><span class="th-sort" onclick="sortLitigationCases('status')">Status <span class="sort-arrow">▼</span></span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="litigationTableBody">
                        <tr><td colspan="8" style="text-align:center; padding: 40px; color: #8b8fa3;">Loading...</td></tr>
                    </tbody>
                </table>
                <div class="tbl-foot">
                    <span class="left" id="litFooterLeft">0 litigation cases</span>
                    <span class="left" id="litFooterRight">Active: 0 · Settled: 0</span>
                </div>
            </div>
        </div>