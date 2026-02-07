        <div id="content-dashboard" class="tab-content">
            <!-- Quick Stats -->
            <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 16px; margin-bottom: 24px;">
                <div class="qs-card">
                    <div><div class="qs-label">Total Active</div><div class="qs-val" id="statTotalActive">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Demand Cases</div><div class="qs-val blue" id="statDemand">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Litigation Cases</div><div class="qs-val amber" id="statLitigation">0</div></div>
                </div>
                <div class="qs-card" style="border-left: 3px solid #dc2626;">
                    <div><div class="qs-label">Past Due</div><div class="qs-val red" id="statOverdue">0</div></div>
                </div>
                <div class="qs-card" style="border-left: 3px solid #f59e0b;">
                    <div><div class="qs-label">Due in 2 Weeks</div><div class="qs-val amber" id="statDue2Weeks">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">This Month</div><div class="qs-val teal" id="statMonthCommission">$0</div></div>
                </div>
            </div>

            <!-- Urgent Cases Section -->
            <div class="tbl-container" id="urgentSection" style="margin-bottom: 24px;">
                <div class="tbl-header">
                    <span class="tbl-title"><span style="color: #dc2626; margin-right: 8px;">⚠</span>Cases Needing Attention</span>
                    <button class="f-btn" data-action="new-demand">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Demand Case
                    </button>
                </div>
                <div id="urgentCasesList" style="padding: 16px;">Loading...</div>
            </div>
        </div>