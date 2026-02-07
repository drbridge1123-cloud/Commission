    <!-- Message Detail Modal -->
    <div id="messageModal" class="modal-overlay" onclick="if(event.target === this) closeMessageModal()">
        <div class="modal-content" style="max-width: 600px;" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 id="messageModalTitle" class="text-xl font-bold text-gray-900">Message</h2>
                <button onclick="closeMessageModal()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="messageFromContainer" style="margin-bottom: 20px;">
                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;" id="messageFromLabel">From:</div>
                    <div style="font-weight: 600; color: #374151;" id="messageFrom"></div>
                </div>
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">Subject:</div>
                    <div style="font-weight: 600; color: #374151; font-size: 16px;" id="messageSubject"></div>
                </div>
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">Date:</div>
                    <div style="color: #6b7280;" id="messageDate"></div>
                </div>
                <div>
                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 8px;">Message:</div>
                    <div style="background: #f9fafb; padding: 16px; border-radius: 8px; border: 1px solid #e5e7eb; white-space: pre-wrap; line-height: 1.6;" id="messageBody"></div>
                </div>
                <div class="modal-actions" style="margin-top: 24px;">
                    <button onclick="replyToMessage()" class="btn-primary">Reply</button>
                    <button id="viewCaseBtn" onclick="viewCaseFromMessage()" class="btn-primary" style="background: #059669; display: none;">View Case</button>
                    <button onclick="deleteCurrentMessage()" class="btn-secondary" style="background: #dc2626;">Delete</button>
                    <button onclick="closeMessageModal()" class="btn-secondary">Close</button>
                </div>
            </div>
        </div>
    </div>
