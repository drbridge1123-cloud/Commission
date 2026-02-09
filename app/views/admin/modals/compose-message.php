        <!-- Compose Message Modal (for Messages Tab) -->
        <div id="composeMessageModal" class="modal-overlay">
            <div class="modal-content m-shell" style="max-width: 500px;">
                <div class="m-header">
                    <div class="m-header-title"><h3>Send Message</h3></div>
                    <button onclick="closeComposeMessageAdmin()" class="m-close">&times;</button>
                </div>
                <form onsubmit="sendAdminMessage(event)">
                    <div class="m-body">
                        <div class="m-row cols-1">
                            <div>
                                <label class="m-label">To</label>
                                <select id="composeRecipientId" required class="m-input">
                                    <option value="">Select employee...</option>
                                </select>
                            </div>
                        </div>
                        <div class="m-row cols-1">
                            <div>
                                <label class="m-label">Subject</label>
                                <input type="text" id="composeSubject" required class="m-input">
                            </div>
                        </div>
                        <div class="m-row cols-1">
                            <div>
                                <label class="m-label">Message</label>
                                <textarea id="composeMessage" rows="5" required class="m-input" style="resize: vertical;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="m-footer">
                        <button type="button" onclick="closeComposeMessageAdmin()" class="m-btn m-btn-secondary">Cancel</button>
                        <button type="submit" class="m-btn m-btn-primary">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
