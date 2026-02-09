        <!-- Deadline Requests Tab -->
        <div id="content-deadline-requests" class="hidden">
            <div class="table-container">
                <div class="table-toolbar">
                    <div class="toolbar-actions">
                        <select id="filterDeadlineStatus" onchange="loadDeadlineRequests()" class="filter-select">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="all">All Requests</option>
                        </select>
                    </div>
                    <div class="search-box">
                        <input type="text" id="deadlineSearchInput" placeholder="Search requests..." onkeyup="filterDeadlineRequestsTable()">
                    </div>
                </div>

                <div class="table-scroll-wrapper scrollbar-fixed">
                    <table class="excel-table" id="deadlineRequestsTable">
                        <thead>
                            <tr>
                                <th data-sort="date">Date Requested</th>
                                <th data-sort="text">Employee</th>
                                <th data-sort="text">Case #</th>
                                <th data-sort="text">Client</th>
                                <th data-sort="date">Current Deadline</th>
                                <th data-sort="date">Requested Deadline</th>
                                <th data-sort="text">Reason</th>
                                <th data-sort="text">Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="deadlineRequestsBody">
                            <tr><td colspan="9" style="text-align: center; padding: 40px; color: #6b7280;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div class="footer-info">
                        <span id="deadlineRequestsCount">0 requests</span>
                    </div>
                </div>
            </div>
        </div>