        <!-- Compose Message Modal (for Messages Tab) -->
        <div id="composeMessageModal" class="modal-overlay">
            <div class="modal-content" style="max-width: 500px;">
                <div class="modal-header">
                    <h3 style="font-size: 14px; font-weight: 600; font-family: 'Outfit', sans-serif;">Send Message</h3>
                    <button onclick="closeComposeMessageAdmin()" class="modal-close">&times;</button>
                </div>
                <div class="modal-body" style="padding: 16px;">
                    <form onsubmit="sendAdminMessage(event)">
                        <div style="margin-bottom: 14px;">
                            <label class="ink-label">To</label>
                            <select id="composeRecipientId" required class="ink-input">
                                <option value="">Select employee...</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 14px;">
                            <label class="ink-label">Subject</label>
                            <input type="text" id="composeSubject" required class="ink-input">
                        </div>

                        <div style="margin-bottom: 14px;">
                            <label class="ink-label">Message</label>
                            <textarea id="composeMessage" rows="5" required class="ink-input" style="resize: vertical;"></textarea>
                        </div>

                        <div style="display: flex; gap: 8px; justify-content: flex-end; padding-top: 8px;">
                            <button type="button" onclick="closeComposeMessageAdmin()" class="ink-btn ink-btn-secondary ink-btn-sm">Cancel</button>
                            <button type="submit" class="ink-btn ink-btn-primary ink-btn-sm">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>