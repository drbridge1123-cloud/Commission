        <!-- Traffic Cases Tab (Admin View) -->
        <div id="content-traffic" class="hidden">
            <!-- Quick Stats -->
            <div class="quick-stats" style="grid-template-columns: repeat(4, 1fr);">
                <div class="qs-card"><div><div class="qs-label">Active Cases</div><div class="qs-val blue" id="trafficStatActive">0</div></div></div>
                <div class="qs-card"><div><div class="qs-label">Dismissed</div><div class="qs-val green" id="trafficStatDismissed">0</div></div></div>
                <div class="qs-card"><div><div class="qs-label">Amended</div><div class="qs-val amber" id="trafficStatAmended">0</div></div></div>
                <div class="qs-card"><div><div class="qs-label">Pending Req.</div><div class="qs-val" style="color: #8b5cf6;" id="trafficStatPendingReq">0</div></div></div>
            </div>

            <div style="display: flex; gap: 12px;">
                <!-- Left Sidebar: Overview & Filters -->
                <div style="width: 280px; flex-shrink: 0; display: flex; flex-direction: column; gap: 10px;">
                    <!-- Tab Filter Card -->
                    <div style="background: white; border: 1px solid #e2e4ea; border-radius: 10px; overflow: hidden;">
                        <div style="padding: 6px; background: #f8f9fa; display: flex; gap: 2px;">
                            <button onclick="switchAdminTrafficTab('all')" id="adminTrafficTab-all" class="f-chip active" style="flex: 1; padding: 6px 4px; border-radius: 6px; font-size: 11px;">All</button>
                            <button onclick="switchAdminTrafficTab('referral')" id="adminTrafficTab-referral" class="f-chip" style="flex: 1; padding: 6px 4px; border-radius: 6px; font-size: 11px;">Referral</button>
                            <button onclick="switchAdminTrafficTab('court')" id="adminTrafficTab-court" class="f-chip" style="flex: 1; padding: 6px 4px; border-radius: 6px; font-size: 11px;">Court</button>
                            <button onclick="switchAdminTrafficTab('year')" id="adminTrafficTab-year" class="f-chip" style="flex: 1; padding: 6px 4px; border-radius: 6px; font-size: 11px;">Year</button>
                        </div>
                        <div id="adminTrafficSidebarContent" style="padding: 12px;">
                            <div style="font-size: 11px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Overview</div>
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                <div style="display: flex; justify-content: space-between; font-family: 'Outfit', sans-serif;">
                                    <span style="font-size: 12px; color: #3d3f4e;">Total Cases</span>
                                    <span style="font-size: 13px; font-weight: 700; color: #1a1a2e;" id="trafficStatTotal">0</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-family: 'Outfit', sans-serif;">
                                    <span style="font-size: 12px; color: #3b82f6;">Active</span>
                                    <span style="font-size: 13px; font-weight: 700; color: #3b82f6;" id="trafficOverviewActive">0</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-family: 'Outfit', sans-serif;">
                                    <span style="font-size: 12px; color: #0d9488;">Done</span>
                                    <span style="font-size: 13px; font-weight: 700; color: #0d9488;" id="trafficOverviewDone">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Request Form - Compact -->
                    <div class="tbl-container">
                        <div style="padding: 10px 14px; background: #1a1a2e; border-radius: 10px 10px 0 0;">
                            <h3 style="font-size: 12px; font-weight: 600; color: #fff; font-family: 'Outfit', sans-serif; margin: 0;">Request New Traffic Case</h3>
                        </div>
                        <form id="trafficRequestForm" style="padding: 12px 14px; display: flex; flex-direction: column; gap: 8px;">
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Client Name *</label>
                                <input type="text" id="reqClientName" required class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Phone</label>
                                    <input type="text" id="reqClientPhone" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Email</label>
                                    <input type="email" id="reqClientEmail" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Ticket #</label>
                                    <input type="text" id="reqCaseNumber" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Court</label>
                                    <input type="text" id="reqCourt" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Charge</label>
                                    <input type="text" id="reqCharge" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Requester</label>
                                    <input type="text" id="reqReferralSource" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Issued</label>
                                    <input type="date" id="reqCitationIssuedDate" class="ink-input" style="padding: 5px 6px; font-size: 11px;">
                                </div>
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Court Date</label>
                                    <input type="date" id="reqCourtDate" class="ink-input" style="padding: 5px 6px; font-size: 11px;">
                                </div>
                            </div>
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Note</label>
                                <textarea id="reqNote" rows="2" class="ink-input" style="padding: 6px 8px; font-size: 12px; resize: none;"></textarea>
                            </div>
                            <button type="submit" class="ink-btn ink-btn-primary" style="width: 100%; justify-content: center; padding: 8px 12px; font-size: 12px;">Submit Request</button>
                        </form>
                    </div>

                    <!-- All Requests History -->
                    <div class="tbl-container">
                        <div style="padding: 10px 14px; background: #1a1a2e; border-radius: 10px 10px 0 0; display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="font-size: 12px; font-weight: 600; color: #fff; font-family: 'Outfit', sans-serif; margin: 0;">All Requests</h3>
                        </div>
                        <div style="padding: 8px 10px; border-bottom: 1px solid #e2e4ea;">
                            <input type="text" id="myRequestsSearch" placeholder="Search..."
                                style="width: 100%; padding: 5px 8px; border: 1px solid #e2e4ea; border-radius: 5px; font-size: 11px; font-family: 'Outfit', sans-serif;"
                                oninput="filterMyRequests(this.value)">
                        </div>
                        <div id="myTrafficRequests" style="max-height: 300px; overflow-y: auto;">
                            <p style="padding: 12px; text-align: center; color: #8b8fa3; font-size: 11px; font-family: 'Outfit', sans-serif;">Loading...</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: All Traffic Cases -->
                <div style="flex: 1; min-width: 0;">
                    <div class="tbl-container">
                        <div style="padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e4ea;">
                            <div>
                                <h3 style="font-size: 14px; font-weight: 600; color: #1a1a2e; font-family: 'Outfit', sans-serif;">Traffic Cases</h3>
                                <p style="font-size: 11px; color: #8b8fa3;" id="trafficFilterLabel">All Cases</p>
                            </div>
                            <div style="display: flex; gap: 6px;">
                                <input type="text" class="f-search" id="adminTrafficSearch" placeholder="Search..." oninput="searchAdminTraffic(this.value)" style="width: 140px;">
                            </div>
                        </div>

                        <!-- Status Filter Tabs -->
                        <div style="padding: 8px 16px; background: #f8f9fa; border-bottom: 1px solid #e2e4ea; display: flex; gap: 6px;">
                            <span class="f-chip" onclick="filterAdminTraffic('all', this)" id="adminTrafficStatusBtn-all">
                                All <span id="trafficCountAll" style="opacity: 0.7;">0</span>
                            </span>
                            <span class="f-chip active" onclick="filterAdminTraffic('active', this)" id="adminTrafficStatusBtn-active">
                                Active <span id="trafficCountActive" style="opacity: 0.7;">0</span>
                            </span>
                            <span class="f-chip" onclick="filterAdminTraffic('resolved', this)" id="adminTrafficStatusBtn-resolved">
                                Done <span id="trafficCountDone" style="opacity: 0.7;">0</span>
                            </span>
                        </div>

                        <div style="max-height: 730px; overflow-y: auto;">
                            <table class="tbl" id="adminTrafficTable">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Court</th>
                                        <th>Court Date</th>
                                        <th>Charge</th>
                                        <th class="c">NOA</th>
                                        <th class="c">Discovery</th>
                                        <th>Disposition</th>
                                        <th class="c">Status</th>
                                        <th>Requester</th>
                                        <th class="c">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="adminTrafficTableBody">
                                    <tr><td colspan="10" style="padding: 32px 16px; text-align: center; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tbl-foot">
                            <div class="left"><span id="trafficTableCount">0</span> cases</div>
                            <div class="right">
                                <div class="ft"><span class="ft-l">Dismissed:</span><span class="ft-v green" id="trafficFootDismissed">0</span></div>
                                <div class="ft"><span class="ft-l">Amended:</span><span class="ft-v amber" id="trafficFootAmended">0</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>