        <!-- Send Message Modal -->
        <div id="messageModal" class="modal-overlay">
            <div class="modal-content" style="max-width: 600px;">
                <div class="modal-header">
                    <h3 class="modal-title">Send Message to Employee</h3>
                    <button onclick="closeMessageModal()" class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <form onsubmit="sendMessage(event)">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">To:</label>
                            <div id="messageRecipientName" style="font-weight: 600; color: #2563eb; font-size: 15px;"></div>
                            <input type="hidden" id="messageRecipientId">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Subject:</label>
                            <input type="text" id="messageSubject" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Message:</label>
                            <textarea id="messageBody" rows="6" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; resize: vertical;"></textarea>
                        </div>

                        <div class="modal-actions">
                            <button type="submit" class="btn-primary">Send Message</button>
                            <button type="button" onclick="closeMessageModal()" class="btn-secondary">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>