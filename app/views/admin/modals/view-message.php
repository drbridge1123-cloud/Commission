        <!-- View Message Modal -->
        <div id="viewMessageModal" class="modal-overlay">
            <div class="modal-content m-shell" style="max-width: 500px;">
                <div class="m-header">
                    <div class="m-header-title"><h3 id="viewMessageTitle">Message</h3></div>
                    <button onclick="closeViewMessage()" class="m-close">&times;</button>
                </div>
                <div class="m-body">
                    <div style="margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0;">
                        <div style="font-size: 11px; color: #64748b;">
                            <span style="font-weight: 600; text-transform: uppercase;" id="viewMessageFromLabel">From:</span> <span id="viewMessageFrom" style="color: #0f172a;"></span>
                        </div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                            <span style="font-weight: 600; text-transform: uppercase;">Subject:</span> <span id="viewMessageSubject" style="color: #0f172a;"></span>
                        </div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                            <span style="font-weight: 600; text-transform: uppercase;">Date:</span> <span id="viewMessageDate" style="color: #0f172a;"></span>
                        </div>
                    </div>
                    <div id="viewMessageBody" style="font-size: 13px; line-height: 1.6; color: #0f172a; white-space: pre-wrap;"></div>
                </div>
                <div class="m-footer">
                    <button type="button" onclick="closeViewMessage()" class="m-btn m-btn-secondary">Close</button>
                    <button type="button" onclick="deleteCurrentMessageAdmin()" class="m-btn m-btn-danger" style="background: #dc2626; color: white; border: none;">Delete</button>
                    <button type="button" onclick="replyToMessageAdmin()" class="m-btn m-btn-primary">Reply</button>
                </div>
            </div>
        </div>
