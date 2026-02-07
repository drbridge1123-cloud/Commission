        <!-- Goals Tab -->
        <div id="content-goals" class="hidden">
            <!-- Filter -->
            <div style="margin-bottom: 16px;">
                <select id="myGoalsYearFilter" class="f-select" style="width:auto;" onchange="loadMyGoals()">
                </select>
            </div>

            <!-- Progress Cards -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <!-- Cases Goal Card -->
                <div class="qs-card" style="flex-direction: column; align-items: stretch; padding: 20px;">
                    <div class="qs-label" style="margin-bottom: 12px;">Cases Goal</div>
                    <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 8px;">
                        <span class="qs-val" id="goalCasesActual" style="font-size: 28px;">0</span>
                        <span style="font-size: 14px; color: #8b8fa3;">/ <span id="goalCasesTarget">50</span></span>
                    </div>
                    <div style="height: 8px; background: #f0f1f3; border-radius: 4px; overflow: hidden; margin-bottom: 6px;">
                        <div id="goalCasesBar" style="height: 100%; background: #0d9488; border-radius: 4px; width: 0%; transition: width 0.5s;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #8b8fa3; font-family: 'Outfit', sans-serif;">
                        <span id="goalCasesPercent">0% complete</span>
                        <span id="goalCasesPace">-</span>
                    </div>
                </div>

                <!-- Legal Fee Goal Card -->
                <div class="qs-card" style="flex-direction: column; align-items: stretch; padding: 20px;">
                    <div class="qs-label" style="margin-bottom: 12px;">Legal Fee Goal</div>
                    <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 8px;">
                        <span class="qs-val teal" id="goalFeeActual" style="font-size: 28px;">$0</span>
                        <span style="font-size: 14px; color: #8b8fa3;">/ <span id="goalFeeTarget">$500K</span></span>
                    </div>
                    <div style="height: 8px; background: #f0f1f3; border-radius: 4px; overflow: hidden; margin-bottom: 6px;">
                        <div id="goalFeeBar" style="height: 100%; background: #3b82f6; border-radius: 4px; width: 0%; transition: width 0.5s;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #8b8fa3; font-family: 'Outfit', sans-serif;">
                        <span id="goalFeePercent">0% complete</span>
                        <span id="goalFeePace">-</span>
                    </div>
                </div>
            </div>

            <!-- Breakdown Tables (side by side) -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; align-items: start;">
                <!-- Intake Breakdown (Left) -->
                <div class="tbl-container">
                    <div class="tbl-header"><span class="tbl-title">Intake Breakdown</span></div>
                    <div id="goalIntakeContent" style="padding: 0;">
                        <p style="text-align: center; padding: 40px; color: #8b8fa3; font-size: 12px;">Loading...</p>
                    </div>
                </div>

                <!-- Paid Fee Breakdown (Right) -->
                <div class="tbl-container">
                    <div class="tbl-header"><span class="tbl-title">Paid Fee Breakdown</span></div>
                    <div id="goalFeeContent" style="padding: 0;">
                        <p style="text-align: center; padding: 40px; color: #8b8fa3; font-size: 12px;">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
