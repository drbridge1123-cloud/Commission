        <!-- Goals Tab -->
        <div id="content-goals" class="hidden">
            <!-- Filters -->
            <div class="filters" style="margin-bottom: 16px;">
                <select id="goalsYearFilter" class="f-select" onchange="loadGoalsData()">
                </select>
                <button onclick="loadGoalsData()" class="f-btn" style="padding: 5px 12px; font-size: 11px;">Refresh</button>
            </div>

            <!-- Hero Cards -->
            <div class="hero-row" style="margin-bottom: 16px;">
                <div class="qs-card"><div><div class="qs-label">Employees Tracked</div><div class="qs-val" id="goalsHeroCount">0</div></div></div>
                <div class="qs-card"><div><div class="qs-label">Avg Cases Progress</div><div class="qs-val" id="goalsHeroCases">0%</div></div></div>
                <div class="qs-card"><div><div class="qs-label">Avg Legal Fee Progress</div><div class="qs-val" id="goalsHeroFee">0%</div></div></div>
            </div>

            <!-- Goals Table -->
            <div class="tbl-container">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th data-sort="text">Employee</th>
                            <th class="r" data-sort="number">Cases</th>
                            <th style="width:120px;">Progress</th>
                            <th class="r" data-sort="number">Legal Fee</th>
                            <th style="width:120px;">Progress</th>
                            <th class="c">Pace</th>
                            <th class="c" style="width:60px;">Edit</th>
                        </tr>
                    </thead>
                    <tbody id="goalsTableBody">
                        <tr><td colspan="7" style="text-align:center; padding:40px; color:#8b8fa3;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Edit Goal Modal -->
            <div id="editGoalModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; display:none; align-items:center; justify-content:center;">
                <div style="background:#fff; border-radius:10px; width:400px; max-width:90vw; overflow:hidden;">
                    <div class="modal-header" style="padding:14px 20px; display:flex; justify-content:space-between; align-items:center;">
                        <h3 style="margin:0; font-size:15px;" id="editGoalTitle">Edit Goal</h3>
                        <span class="modal-close" onclick="closeGoalModal()" style="cursor:pointer; font-size:20px;">&times;</span>
                    </div>
                    <div style="padding:20px;">
                        <input type="hidden" id="goalEditUserId">
                        <div style="margin-bottom:12px;">
                            <label class="ink-label">Year</label>
                            <input type="number" id="goalEditYear" class="ink-input" min="2020" max="2030">
                        </div>
                        <div style="margin-bottom:12px;">
                            <label class="ink-label">Target Cases</label>
                            <input type="number" id="goalEditCases" class="ink-input" min="1" max="999" value="50">
                        </div>
                        <div style="margin-bottom:12px;">
                            <label class="ink-label">Target Legal Fee ($)</label>
                            <input type="number" id="goalEditFee" class="ink-input" min="0" step="1000" value="500000">
                        </div>
                        <div style="margin-bottom:16px;">
                            <label class="ink-label">Notes</label>
                            <textarea id="goalEditNotes" class="ink-input" rows="2" style="resize:vertical;"></textarea>
                        </div>
                        <div style="display:flex; gap:8px; justify-content:flex-end;">
                            <button onclick="closeGoalModal()" class="f-btn" style="padding:6px 16px; font-size:12px;">Cancel</button>
                            <button onclick="saveGoal()" class="ink-btn ink-btn-primary ink-btn-sm">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>