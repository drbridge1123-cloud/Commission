        <!-- Notifications Tab - Ink Compact -->
        <div id="content-notifications" class="hidden">
            <!-- Quick Stats -->
            <div class="notif-stats">
                <div class="qs-card">
                    <div><div class="qs-label">Total Messages</div><div class="qs-val" id="notifStatTotal">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Unread</div><div class="qs-val blue" id="notifStatUnread">0</div></div>
                </div>
                <div class="qs-card">
                    <div><div class="qs-label">Sent</div><div class="qs-val" id="notifStatSent">0</div></div>
                </div>
            </div>

            <!-- Filters -->
            <div class="notif-filters">
                <span class="f-chip active" data-filter="all" onclick="filterNotifications('all')">All</span>
                <span class="f-chip" data-filter="unread" onclick="filterNotifications('unread')">Unread</span>
                <span class="f-chip" data-filter="sent" onclick="filterNotifications('sent')">Sent</span>
                <span class="f-chip" data-filter="read" onclick="filterNotifications('read')">Read</span>
                <div class="f-spacer"></div>
                <button class="f-btn-ghost" onclick="markAllRead()">Mark All Read</button>
                <button class="f-btn-primary" onclick="openComposeMessage()">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                    New Message
                </button>
            </div>

            <!-- Table -->
            <div class="tbl-container">
                <table class="tbl" id="notificationsTable">
                    <thead>
                        <tr>
                            <th style="width:24px;padding:10px 6px 10px 14px;"></th>
                            <th>Type</th>
                            <th>From / To</th>
                            <th>Subject</th>
                            <th>Time</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="notificationsBody">
                        <tr><td colspan="6" style="text-align:center; padding:40px; color:#8b8fa3;">Loading...</td></tr>
                    </tbody>
                </table>
                <div class="tbl-foot">
                    <span id="notifFootLeft">0 messages</span>
                    <span id="notifFootRight">0 unread</span>
                </div>
            </div>
        </div>
