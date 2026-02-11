        <div id="content-uim" class="tab-content hidden">
            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="qs-card">
                    <span class="qs-label">UIM Cases</span>
                    <span class="qs-val" id="uimStatTotal">0</span>
                </div>
                <div class="qs-card">
                    <span class="qs-label">Previous Settlement</span>
                    <span class="qs-val teal" id="uimStatPrevSettled" style="font-size: 16px;">$0</span>
                </div>
                <div class="qs-card">
                    <span class="qs-label">Previous Commission</span>
                    <span class="qs-val" id="uimStatPrevComm" style="font-size: 16px;">$0</span>
                </div>
            </div>

            <!-- Table -->
            <div class="tbl-container">
                <table class="tbl" id="uimTable">
                    <thead>
                        <tr>
                            <th style="width:0;padding:0;border:none;"></th>
                            <th><span class="th-sort" onclick="sortUimCases('case_number')">Case # <span class="sort-arrow">&#9660;</span></span></th>
                            <th><span class="th-sort" onclick="sortUimCases('client_name')">Client Name <span class="sort-arrow">&#9660;</span></span></th>
                            <th><span class="th-sort" onclick="sortUimCases('settled')">1st Settlement <span class="sort-arrow">&#9660;</span></span></th>
                            <th><span class="th-sort" onclick="sortUimCases('resolution_type')">Resolution <span class="sort-arrow">&#9660;</span></span></th>
                            <th><span class="th-sort" onclick="sortUimCases('uim_start_date')">UIM Start <span class="sort-arrow">&#9660;</span></span></th>
                            <th>UIM Demand Out</th>
                            <th>UIM Negotiate</th>
                            <th><span class="th-sort" onclick="sortUimCases('uim_days')">Days in UIM <span class="sort-arrow">&#9660;</span></span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="uimTableBody">
                        <tr><td colspan="10" style="text-align:center; padding: 40px; color: #8b8fa3;">Loading...</td></tr>
                    </tbody>
                </table>
                <div class="tbl-foot">
                    <span class="left" id="uimFooterLeft">0 UIM cases</span>
                </div>
            </div>
        </div>
