        <!-- Traffic Cases Tab -->
        <div id="content-traffic" class="hidden">
            <!-- Stats Cards -->
            <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
                <div class="stat-card">
                    <p class="stat-label">Active Cases</p>
                    <p class="stat-value stat-blue" id="trafficActive">0</p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Dismissed</p>
                    <p class="stat-value stat-green" id="trafficDismissed">0</p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Amended</p>
                    <p class="stat-value stat-amber" id="trafficAmended">0</p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Total Commission</p>
                    <p class="stat-value stat-green" id="trafficCommission">$0</p>
                </div>
            </div>

            <!-- Traffic Layout with Sidebar -->
            <div style="display: flex; gap: 16px; margin-top: 16px;">
                <!-- Sidebar - Minimal Flat Blue Accent Design -->
                <div class="traffic-sidebar" style="width: 300px; flex-shrink: 0;">
                    <!-- Pending Requests Section -->
                    <div id="pendingRequestsSection" style="background: #ffffff; border: 1px solid #e5e9f0; border-radius: 10px; overflow: hidden; margin-bottom: 12px; display: none;">
                        <div style="padding: 12px 16px; background: #fef3c7; border-bottom: 1px solid #fcd34d;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 13px; font-weight: 600; color: #92400e;">Pending Requests</span>
                                <span id="pendingRequestCount" style="background: #f59e0b; color: white; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 10px;">0</span>
                            </div>
                        </div>
                        <div id="pendingRequestsList" style="max-height: 250px; overflow-y: auto;">
                            <!-- Populated by JS -->
                        </div>
                    </div>

                    <!-- Sidebar Container -->
                    <div style="background: #ffffff; border: 1px solid #e5e9f0; border-radius: 10px; overflow: hidden;">
                        <!-- Tab Navigation -->
                        <div style="padding: 6px; background: #f7f9fc; display: flex; gap: 2px;">
                            <button onclick="switchSidebarTab('all')" id="sidebarTab-all" class="sidebar-tab-btn active" style="flex: 1; padding: 9px 6px; font-size: 11px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; transition: all 0.15s; font-family: Inter, -apple-system, sans-serif;">All</button>
                            <button onclick="switchSidebarTab('referral')" id="sidebarTab-referral" class="sidebar-tab-btn" style="flex: 1; padding: 9px 6px; font-size: 11px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; transition: all 0.15s; font-family: Inter, -apple-system, sans-serif;">Referral</button>
                            <button onclick="switchSidebarTab('court')" id="sidebarTab-court" class="sidebar-tab-btn" style="flex: 1; padding: 9px 6px; font-size: 11px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; transition: all 0.15s; font-family: Inter, -apple-system, sans-serif;">Court</button>
                            <button onclick="switchSidebarTab('year')" id="sidebarTab-year" class="sidebar-tab-btn" style="flex: 1; padding: 9px 6px; font-size: 11px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; transition: all 0.15s; font-family: Inter, -apple-system, sans-serif;">Year</button>
                        </div>

                        <!-- Sidebar Content List -->
                        <div id="sidebarContent" style="max-height: 350px; overflow-y: auto;">
                            <!-- Populated by JS -->
                        </div>

                        <!-- Stats Section -->
                        <div id="trafficQuickStats" style="padding: 14px 16px; background: #f7f9fc; border-top: 1px solid #e5e9f0;">
                            <!-- Populated by JS -->
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div style="flex: 1; min-width: 0;">
                    <div class="container-card" style="padding: 0;">
                        <div class="card-header flex justify-between items-center" style="padding: 12px 16px;">
                            <div>
                                <h2 class="card-title" style="font-size: 16px;">Traffic Cases</h2>
                                <p class="card-subtitle" id="trafficFilterLabel" style="font-size: 12px;">All Cases</p>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <input type="text" id="trafficSearch" placeholder="Search..." onkeyup="filterTrafficCases()" class="form-input" style="padding: 8px 12px; width: 150px; font-size: 13px;">
                                <button onclick="openAddTrafficModal()" class="btn-primary" style="padding: 8px 16px; white-space: nowrap; font-size: 13px;">+ New Case</button>
                            </div>
                        </div>

                        <div class="table-scroll-wrapper">
                            <table class="excel-table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th><div class="th-content">Client Name</div></th>
                                        <th><div class="th-content">Court</div></th>
                                        <th><div class="th-content">Court Date</div></th>
                                        <th><div class="th-content">Charge</div></th>
                                        <th><div class="th-content">Case #</div></th>
                                        <th><div class="th-content">Offer</div></th>
                                        <th><div class="th-content">Disposition</div></th>
                                        <th><div class="th-content">Referral</div></th>
                                        <th style="text-align: right;"><div class="th-content" style="justify-content: flex-end;">Commission</div></th>
                                        <th style="text-align: center;"><div class="th-content" style="justify-content: center;">Actions</div></th>
                                    </tr>
                                </thead>
                                <tbody id="trafficCasesBody">
                                    <tr><td colspan="10" style="padding: 32px 16px; text-align: center;" class="text-secondary">Loading traffic cases...</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer -->
                        <div class="table-footer" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-top: 1px solid #e5e7eb;">
                            <span id="trafficCaseCount" class="text-secondary">0 cases</span>
                            <span class="total-label">Total Commission: <span id="trafficTotalCommission" class="total-amount">$0.00</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden filters for compatibility -->
            <select id="trafficYearFilter" onchange="filterTrafficCases()" style="display: none;">
            </select>
            <select id="trafficStatusFilter" onchange="filterTrafficCases()" style="display: none;">
                <option value="all">All</option>
                <option value="active" selected>Active</option>
                <option value="resolved">Resolved</option>
            </select>

        </div>
