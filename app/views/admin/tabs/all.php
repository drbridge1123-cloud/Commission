        <!-- All Cases Tab - Ink Compact -->
        <div id="content-all" class="hidden">
            <!-- Filters - Compact Row -->
            <div class="filters" style="margin-bottom: 12px;">
                <select id="filterCounsel" onchange="loadAllCases()" class="f-select">
                    <option value="all">All Counsel</option>
                    <option value="charb">Charb</option>
                    <option value="chong">Chong</option>
                    <option value="soyong">Soyong</option>
                    <option value="dave">Dave</option>
                    <option value="ella">Ella</option>
                    <option value="jimi">Jimi</option>
                </select>
                <select id="filterAllMonth" onchange="loadAllCases()" class="f-select">
                    <option value="all">All Months</option>
                </select>
                <select id="filterAllStatus" onchange="loadAllCases()" class="f-select">
                    <option value="all">All Status</option>
                    <option value="in_progress">In Progress</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="paid">Paid</option>
                    <option value="rejected">Rejected</option>
                </select>
                <div class="f-spacer"></div>
                <input type="text" id="searchAll" placeholder="Search..." class="f-search" onkeyup="filterAllCases()">
                <button onclick="exportAllToExcel()" class="f-btn">Export</button>
            </div>

            <!-- Table - Optimized Columns -->
            <div class="tbl-container">
                <div id="allCasesTableWrapper">
                    <table class="tbl" id="allCasesTable" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('counsel_name')">Counsel</span></th>
                                <th class="c" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('status')">Status</span></th>
                                <th style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('month')">Month</span></th>
                                <th style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('case_number')">Case #</span></th>
                                <th style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('client_name')">Client</span></th>
                                <th style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('resolution_type')">Resolution</span></th>
                                <th class="r" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('settled')">Settled</span></th>
                                <th class="r" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('presuit_offer')">Pre-Suit</span></th>
                                <th class="r" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('difference')">Diff</span></th>
                                <th class="r" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('legal_fee')">Legal Fee</span></th>
                                <th class="r" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('discounted_legal_fee')">Disc. Fee</span></th>
                                <th class="r" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('commission')">Commission</span></th>
                                <th class="c" style="padding:8px 6px;"><span class="th-sort" onclick="sortAllCases('check_received')">Check</span></th>
                                <th class="c" style="padding:8px 6px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="allCasesBody"></tbody>
                    </table>
                </div>
                <div class="tbl-foot">
                    <span class="left" id="allCasesFooterInfo">Showing 0 cases</span>
                    <div class="right">
                        <span class="ft"><span class="ft-l">Total Commission</span><span class="ft-v green" id="allCasesFooterTotal">$0.00</span></span>
                    </div>
                </div>
            </div>
        </div>