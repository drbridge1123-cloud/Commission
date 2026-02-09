    <!-- Message Detail Modal -->
    <div id="messageModal" class="modal-overlay" onclick="if(event.target === this) closeMessageModal()">
        <div class="modal-content m-shell" style="max-width: 600px;" onclick="event.stopPropagation()">
            <div class="m-header">
                <div class="m-header-title"><h2 id="messageModalTitle">Message</h2></div>
                <button onclick="closeMessageModal()" class="m-close">&times;</button>
            </div>
            <div class="m-body">
                <div id="messageFromContainer" style="margin-bottom: 14px;">
                    <label class="m-label" id="messageFromLabel">From</label>
                    <div style="font-weight: 600; color: #0f172a;" id="messageFrom"></div>
                </div>
                <div style="margin-bottom: 14px;">
                    <label class="m-label">Subject</label>
                    <div style="font-weight: 600; color: #0f172a; font-size: 15px;" id="messageSubject"></div>
                </div>
                <div style="margin-bottom: 14px;">
                    <label class="m-label">Date</label>
                    <div style="color: #64748b; font-size: 13px;" id="messageDate"></div>
                </div>
                <div>
                    <label class="m-label">Message</label>
                    <div style="background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; white-space: pre-wrap; line-height: 1.6; font-size: 13px; margin-top: 4px;" id="messageBody"></div>
                </div>
            </div>
            <div class="m-footer">
                <button onclick="closeMessageModal()" class="m-btn m-btn-secondary">Close</button>
                <button onclick="deleteCurrentMessage()" class="m-btn m-btn-danger" style="background: #dc2626; color: white; border: none;">Delete</button>
                <button id="viewCaseBtn" onclick="viewCaseFromMessage()" class="m-btn m-btn-success" style="display: none;">View Case</button>
                <button onclick="replyToMessage()" class="m-btn m-btn-primary">Reply</button>
            </div>
        </div>
    </div>
