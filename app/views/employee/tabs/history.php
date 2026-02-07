        <!-- History Tab - Ink Compact -->
        <div id="content-history" class="hidden">
            <!-- Filters -->
            <div class="filters" style="margin-bottom: 12px;">
                <select id="historyPeriod" onchange="onHistoryPeriodChange()" class="f-select" style="width: 110px;">
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                    <option value="all">All Time</option>
                </select>
                <select id="historyYear" onchange="loadHistory()" class="f-select" style="width: 85px;">
                    <!-- Years populated by JS -->
                </select>
                <select id="historyMonth" onchange="loadHistory()" class="f-select" style="width: 120px;">
                    <option value="all">All Months</option>
                </select>
                <span class="f-spacer"></span>
                <button onclick="exportHistory()" class="f-btn">Export</button>
            </div>
            <div class="tbl-container">
                <div id="historyContent">
                    <!-- History will be loaded here -->
                </div>
            </div>
        </div>
