        <!-- Pending Tab - Ink Compact -->
        <div id="content-pending">
            <!-- Filters -->
            <div class="filters" style="margin-bottom: 12px;">
                <button onclick="bulkAction('approve')" class="act-btn approve" style="padding: 6px 14px; font-size: 12px;">Approve Selected</button>
                <button onclick="bulkAction('reject')" class="act-btn reject" style="padding: 6px 14px; font-size: 12px;">Reject Selected</button>
                <div class="f-spacer"></div>
            </div>

            <!-- Table -->
            <div class="tbl-container">
                <table class="tbl compact" id="pendingTable">
                    <thead>
                        <tr>
                            <th class="c" style="width:28px;padding:8px 4px;"><input type="checkbox" id="selectAllPending" onchange="toggleSelectAll('pending')"></th>
                            <th style="padding:8px 6px;">Counsel</th>
                            <th style="padding:8px 6px;">Month</th>
                            <th style="padding:8px 6px;">Case #</th>
                            <th style="padding:8px 6px;">Client</th>
                            <th class="r" style="padding:8px 6px;">Settled</th>
                            <th class="r" style="padding:8px 6px;">Pre-Suit</th>
                            <th class="r" style="padding:8px 6px;">Diff</th>
                            <th class="r" style="padding:8px 6px;">Legal Fee</th>
                            <th class="r" style="padding:8px 6px;">Disc. Fee</th>
                            <th class="r" style="padding:8px 6px;">Commission</th>
                            <th class="c" style="padding:8px 6px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="pendingBody"></tbody>
                </table>
                <div class="tbl-foot">
                    <span class="left" id="pendingFooterInfo">Showing 0 cases</span>
                    <div class="right">
                        <span class="ft"><span class="ft-l">Total Commission</span><span class="ft-v green" id="pendingFooterTotal">$0.00</span></span>
                    </div>
                </div>
            </div>
        </div>