        <!-- History Tab -->
        <div id="content-history" class="hidden">
            <div id="historyCard">
                <!-- Filters -->
                <div class="filters" style="margin-bottom: 12px;">
                    <input type="text" id="historySearch" placeholder="Search..." class="f-search" onkeyup="loadHistory()">
                    <select id="historyEmployee" onchange="loadHistory()" class="f-select">
                        <option value="all">All Employees</option>
                    </select>
                    <select id="historyMonth" onchange="loadHistory()" class="f-select">
                        <option value="all">All Months</option>
                    </select>
                    <button onclick="resetHistoryFilters()" class="f-btn">Reset</button>
                    <span class="f-spacer"></span>
                    <button onclick="exportHistoryAdmin()" class="f-btn">Export</button>
                </div>

                <div class="tbl-container">
                    <div id="historyTableContainer" style="overflow-x: auto;">
                        <div id="historyContent">
                            <!-- History will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>