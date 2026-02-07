        <div id="content-notifications" class="tab-content hidden">
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
                <button class="f-btn-primary" data-action="new-message">
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

        <!-- New Message Modal -->
        <div id="newMessageModal" class="modal-overlay">
            <div class="modal-content" style="max-width:500px;">
                <div class="modal-header">
                    <h2>New Message</h2>
                    <button class="modal-close" onclick="closeModal('newMessageModal')">&times;</button>
                </div>
                <form id="newMessageForm" onsubmit="sendMessage(event)" style="padding:20px;">
                    <div style="margin-bottom:16px;">
                        <label class="compose-label">To</label>
                        <input type="text" class="compose-input" value="Admin" disabled style="background:#f8f9fa;">
                    </div>
                    <div style="margin-bottom:16px;">
                        <label class="compose-label">Subject *</label>
                        <input type="text" name="subject" class="compose-input" required maxlength="200" placeholder="Enter subject">
                    </div>
                    <div style="margin-bottom:20px;">
                        <label class="compose-label">Message *</label>
                        <textarea name="message" class="compose-input" required rows="5" maxlength="5000" placeholder="Enter your message..." style="resize:vertical;"></textarea>
                    </div>
                    <div class="modal-actions-ink" style="border-top:none; padding-top:0;">
                        <button type="button" class="act-btn-secondary" onclick="closeModal('newMessageModal')">Cancel</button>
                        <button type="submit" class="act-btn-primary">Send Message</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Message Modal -->
        <div id="viewMessageModal" class="modal-overlay">
            <div class="modal-content" style="max-width:560px;">
                <div class="modal-header">
                    <h2>Message</h2>
                    <button class="modal-close" onclick="closeModal('viewMessageModal')">&times;</button>
                </div>
                <div class="modal-body-ink">
                    <div class="msg-detail-row">
                        <span class="msg-detail-label">From:</span>
                        <span class="msg-detail-value" id="viewMessageFrom"></span>
                    </div>
                    <div class="msg-detail-row">
                        <span class="msg-detail-label">Subject:</span>
                        <span class="msg-detail-value" id="viewMessageSubject"></span>
                    </div>
                    <div class="msg-detail-row">
                        <span class="msg-detail-label">Date:</span>
                        <span class="msg-detail-value" id="viewMessageDate"></span>
                    </div>
                    <div class="msg-detail-body" id="viewMessageBody"></div>
                    <div class="modal-actions-ink">
                        <button type="button" class="act-btn-primary" id="replyBtn" onclick="replyToMessage()">Reply</button>
                        <button type="button" class="act-btn-danger" onclick="deleteCurrentMessage()">Delete</button>
                        <div style="flex:1;"></div>
                        <button type="button" class="act-btn-secondary" onclick="closeModal('viewMessageModal')">Close</button>
                    </div>
                </div>
            </div>
        </div>