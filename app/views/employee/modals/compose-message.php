    <!-- Compose Message Modal -->
    <div id="composeMessageModal" class="modal-overlay" onclick="if(event.target === this) closeComposeMessage()">
        <div class="modal-content m-shell" style="max-width: 600px;" onclick="event.stopPropagation()">
            <div class="m-header">
                <div class="m-header-title"><h2>Send Message to Admin</h2></div>
                <button onclick="closeComposeMessage()" class="m-close">&times;</button>
            </div>
            <form onsubmit="sendEmployeeMessage(event)">
                <div class="m-body">
                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Subject</label>
                            <input type="text" id="composeSubject" required class="m-input">
                        </div>
                    </div>
                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Message</label>
                            <textarea id="composeMessage" rows="6" required class="m-input" style="resize: vertical;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="m-footer">
                    <button type="button" onclick="closeComposeMessage()" class="m-btn m-btn-secondary">Cancel</button>
                    <button type="submit" class="m-btn m-btn-primary">Send Message</button>
                </div>
            </form>
        </div>
    </div>
