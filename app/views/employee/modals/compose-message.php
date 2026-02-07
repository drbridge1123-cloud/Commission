    <!-- Compose Message Modal -->
    <div id="composeMessageModal" class="modal-overlay" onclick="if(event.target === this) closeComposeMessage()">
        <div class="modal-content" style="max-width: 600px;" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 class="text-xl font-bold text-gray-900">Send Message to Admin</h2>
                <button onclick="closeComposeMessage()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form onsubmit="sendEmployeeMessage(event)">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Subject:</label>
                        <input type="text" id="composeSubject" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Message:</label>
                        <textarea id="composeMessage" rows="6" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; resize: vertical;"></textarea>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Send Message</button>
                        <button type="button" onclick="closeComposeMessage()" class="btn-secondary">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
