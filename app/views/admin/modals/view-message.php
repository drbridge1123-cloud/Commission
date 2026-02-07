        <!-- View Message Modal -->
        <div id="viewMessageModal" class="modal-overlay">
            <div class="modal-content" style="max-width: 500px;">
                <div class="modal-header">
                    <h3 style="font-size: 14px; font-weight: 600; font-family: 'Outfit', sans-serif;" id="viewMessageTitle">Message</h3>
                    <button onclick="closeViewMessage()" class="modal-close">&times;</button>
                </div>
                <div class="modal-body" style="padding: 16px;">
                    <div style="margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid #e2e4ea;">
                        <div style="font-size: 11px; color: #8b8fa3; font-family: 'Outfit', sans-serif;">
                            <span style="font-weight: 600; text-transform: uppercase;" id="viewMessageFromLabel">From:</span> <span id="viewMessageFrom" style="color: #3d3f4e;"></span>
                        </div>
                        <div style="font-size: 11px; color: #8b8fa3; margin-top: 4px; font-family: 'Outfit', sans-serif;">
                            <span style="font-weight: 600; text-transform: uppercase;">Subject:</span> <span id="viewMessageSubject" style="color: #3d3f4e;"></span>
                        </div>
                        <div style="font-size: 11px; color: #8b8fa3; margin-top: 4px; font-family: 'Outfit', sans-serif;">
                            <span style="font-weight: 600; text-transform: uppercase;">Date:</span> <span id="viewMessageDate" style="color: #3d3f4e;"></span>
                        </div>
                    </div>
                    <div id="viewMessageBody" style="font-size: 13px; line-height: 1.6; color: #3d3f4e; white-space: pre-wrap; font-family: 'Outfit', sans-serif;"></div>
                    <div style="display: flex; gap: 8px; justify-content: flex-end; padding-top: 14px; margin-top: 14px; border-top: 1px solid #e2e4ea;">
                        <button type="button" onclick="closeViewMessage()" class="ink-btn ink-btn-secondary ink-btn-sm">Close</button>
                        <button type="button" onclick="deleteCurrentMessageAdmin()" class="ink-btn ink-btn-danger ink-btn-sm">Delete</button>
                        <button type="button" onclick="replyToMessageAdmin()" class="ink-btn ink-btn-primary ink-btn-sm">Reply</button>
                    </div>
                </div>
            </div>
        </div>