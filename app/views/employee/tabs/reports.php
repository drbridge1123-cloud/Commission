        <!-- Reports Tab -->
        <div id="content-reports" class="hidden">
            <!-- Filters -->
            <div class="filters" style="margin-bottom: 16px;">
                <select id="reportType" onchange="generateReport()" class="f-select" style="min-width: 100px;">
                    <option value="monthly" selected>Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
                <select id="reportYear" onchange="generateReport()" class="f-select"></select>
                <select id="reportMonth" onchange="generateReport()" class="f-select" style="min-width: 115px;">
                    <option value="all" selected>All Months</option>
                </select>
            </div>

            <!-- Report Content -->
            <div id="reportContent">
                <p style="text-align: center; color: #8b8fa3; padding: 48px;">Loading...</p>
            </div>
        </div>
