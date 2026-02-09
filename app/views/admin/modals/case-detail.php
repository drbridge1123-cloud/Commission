        <!-- Case Detail Modal (For All Cases Tab) -->
        <div id="allCaseDetailModal" class="modal-overlay" onclick="if(event.target === this) closeCaseModal()">
            <div class="modal-content m-shell" onclick="event.stopPropagation()" style="max-width: 680px;">
                <div class="m-header">
                    <div class="m-header-title"><h2>Case Details</h2></div>
                    <button onclick="closeCaseModal()" class="m-close">&times;</button>
                </div>
                <div class="m-body">
                    <div id="caseDetailContent">
                        <!-- Details will be loaded here -->
                    </div>
                    <!-- Send Message Section (collapsible) -->
                    <div class="m-section" style="margin-top: 14px;">
                        <button type="button" class="m-bypass-toggle" onclick="toggleCaseMessage(this)">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                            Send Message to <span id="caseMessageRecipient"></span>
                        </button>
                        <div id="caseMessageFields" class="m-collapsible" style="display:none;">
                            <input type="text" id="caseMessageSubject" placeholder="Subject" class="m-input" style="margin-bottom: 8px;">
                            <textarea id="caseMessageBody" rows="2" placeholder="Type your message..." class="m-input" style="resize: vertical;"></textarea>
                            <button onclick="sendCaseMessage()" class="m-btn m-btn-primary" style="margin-top: 8px;">Send Message</button>
                        </div>
                    </div>
                </div>
                <div class="m-footer split">
                    <div id="caseApprovalButtons"></div>
                    <div style="display: flex; gap: 8px;">
                        <button onclick="editCaseFromModal()" class="m-btn m-btn-primary">Edit</button>
                        <button onclick="closeCaseModal()" class="m-btn m-btn-secondary">Close</button>
                    </div>
                </div>
            </div>
        </div>
