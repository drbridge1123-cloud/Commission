        <!-- Send Message Modal -->
        <div id="messageModal" class="modal-overlay">
            <div class="modal-content m-shell" style="max-width: 600px;">
                <div class="m-header">
                    <div class="m-header-title"><h3>Send Message to Employee</h3></div>
                    <button onclick="closeMessageModal()" class="m-close">&times;</button>
                </div>
                <form onsubmit="sendMessage(event)">
                    <div class="m-body">
                        <div style="margin-bottom: 14px;">
                            <label class="m-label">To</label>
                            <div id="messageRecipientName" style="font-weight: 600; color: #22d3ee; font-size: 15px;"></div>
                            <input type="hidden" id="messageRecipientId">
                        </div>
                        <div class="m-row cols-1">
                            <div>
                                <label class="m-label">Subject</label>
                                <input type="text" id="messageSubject" required class="m-input">
                            </div>
                        </div>
                        <div class="m-row cols-1">
                            <div>
                                <label class="m-label">Message</label>
                                <textarea id="messageBody" rows="6" required class="m-input" style="resize: vertical;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="m-footer">
                        <button type="button" onclick="closeMessageModal()" class="m-btn m-btn-secondary">Cancel</button>
                        <button type="submit" class="m-btn m-btn-primary">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
