        <!-- Case Detail Modal (For All Cases Tab) -->
        <div id="allCaseDetailModal" class="modal-overlay" onclick="if(event.target === this) closeCaseModal()">
            <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 680px; max-height: 90vh; border-radius: 12px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.12); overflow: hidden; display: flex; flex-direction: column;">
                <!-- Dark Header -->
                <div style="background: #0f172a; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 3px; height: 16px; background: #22d3ee; border-radius: 2px;"></div>
                        <h2 style="font-size: 14px; font-weight: 600; color: white; margin: 0;">Case Details</h2>
                    </div>
                    <button onclick="closeCaseModal()" style="width: 26px; height: 26px; background: rgba(255,255,255,0.1); border: none; border-radius: 6px; color: rgba(255,255,255,0.7); font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
                </div>

                <!-- Content Area (Scrollable) -->
                <div style="padding: 12px 20px; overflow-y: auto; flex: 1;">
                    <div id="caseDetailContent">
                        <!-- Details will be loaded here -->
                    </div>
                    <!-- Send Message Section -->
                    <div id="caseMessageSection" style="margin-top: 14px; padding: 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <div style="font-size: 11px; font-weight: 600; color: #374151; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.3px;">
                            💬 Send Message to <span id="caseMessageRecipient"></span>
                        </div>
                        <input type="text" id="caseMessageSubject" placeholder="Subject" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; margin-bottom: 8px;">
                        <textarea id="caseMessageBody" rows="2" placeholder="Type your message..." style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; resize: vertical;"></textarea>
                        <button onclick="sendCaseMessage()" style="margin-top: 8px; padding: 7px 14px; background: #0f172a; color: white; border: none; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;">Send Message</button>
                    </div>
                </div>

                <!-- Footer (Fixed) -->
                <div style="background: #f8fafc; padding: 10px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                    <div id="caseApprovalButtons"></div>
                    <div style="display: flex; gap: 8px;">
                        <button onclick="editCaseFromModal()" style="padding: 7px 14px; background: #0f172a; color: white; border: none; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;">Edit</button>
                        <button onclick="closeCaseModal()" style="padding: 7px 12px; background: white; color: #64748b; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 11px; font-weight: 500; cursor: pointer;">Close</button>
                    </div>
                </div>
            </div>
        </div>