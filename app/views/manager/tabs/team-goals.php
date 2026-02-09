        <!-- Team Goals Tab (Manager) -->
        <div id="content-goals" class="hidden">
            <div class="filters" style="margin-bottom: 16px;">
                <select id="teamGoalsYearFilter" class="f-select" style="width: auto;" onchange="loadTeamGoals()">
                </select>
                <span class="f-spacer"></span>
                <button onclick="loadTeamGoals()" class="f-btn">Refresh</button>
            </div>

            <!-- Team Overall Summary -->
            <div class="hero-row" style="margin-bottom: 16px;">
                <div class="hero-card accent-dark">
                    <div class="hero-label">Team Cases</div>
                    <div class="hero-val" id="teamCasesTotal">0</div>
                    <div class="hero-sub" id="teamCasesTarget"></div>
                </div>
                <div class="hero-card accent-teal">
                    <div class="hero-label">Team Legal Fee</div>
                    <div class="hero-val teal" id="teamFeeTotal">$0</div>
                    <div class="hero-sub" id="teamFeeTarget"></div>
                </div>
                <div class="hero-card accent-blue">
                    <div class="hero-label">Team Pace</div>
                    <div class="hero-val" id="teamPaceLabel">—</div>
                </div>
            </div>

            <!-- Individual Employee Goals (grid) -->
            <div id="teamGoalsContent" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <p style="text-align: center; padding: 40px; color: #8b8fa3; font-size: 12px; grid-column: 1/-1;">Loading team goals...</p>
            </div>
        </div>
