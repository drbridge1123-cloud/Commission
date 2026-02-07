        <!-- Admin Traffic Case Edit Modal -->
        <div id="adminTrafficModal" class="modal-overlay" style="display: none;" onclick="if(event.target === this) closeAdminTrafficModal()">
            <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 560px;">
                <div class="modal-header">
                    <h3 style="font-size: 14px; font-weight: 600; font-family: 'Outfit', sans-serif;">Edit Traffic Case</h3>
                    <button onclick="closeAdminTrafficModal()" class="modal-close">&times;</button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 16px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="ink-label">Client Name</label>
                            <input type="text" id="adminTrafficClientName" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">Client Phone</label>
                            <input type="text" id="adminTrafficClientPhone" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">Court</label>
                            <input type="text" id="adminTrafficCourt" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">Court Date</label>
                            <input type="date" id="adminTrafficCourtDate" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">Charge</label>
                            <input type="text" id="adminTrafficCharge" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">Ticket #</label>
                            <input type="text" id="adminTrafficCaseNumber" class="ink-input">
                        </div>
                        <div style="grid-column: span 2;">
                            <label class="ink-label">Prosecutor Offer</label>
                            <input type="text" id="adminTrafficOffer" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">Disposition</label>
                            <select id="adminTrafficDisposition" class="ink-input">
                                <option value="pending">Pending</option>
                                <option value="dismissed">Dismissed</option>
                                <option value="amended">Amended</option>
                            </select>
                        </div>
                        <div>
                            <label class="ink-label">Status</label>
                            <select id="adminTrafficStatus" class="ink-input">
                                <option value="active">Active</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>
                        <div>
                            <label class="ink-label">Ticket Issued</label>
                            <input type="date" id="adminTrafficTicketIssuedDate" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">NOA Sent</label>
                            <input type="date" id="adminTrafficNoaSentDate" class="ink-input">
                        </div>
                        <div>
                            <label class="ink-label">Referral Source</label>
                            <input type="text" id="adminTrafficReferralSource" class="ink-input">
                        </div>
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-top: 18px; font-family: 'Outfit', sans-serif;">
                                <input type="checkbox" id="adminTrafficDiscovery" style="width: 16px; height: 16px;">
                                <span style="font-size: 12px; color: #3d3f4e;">Discovery Received</span>
                            </label>
                        </div>
                        <div style="grid-column: span 2;">
                            <label class="ink-label">Note</label>
                            <textarea id="adminTrafficNote" class="ink-input" rows="2" style="resize: vertical;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="display: flex; gap: 8px; justify-content: flex-end; padding: 12px 16px; border-top: 1px solid #e2e4ea;">
                    <button type="button" onclick="closeAdminTrafficModal()" class="ink-btn ink-btn-secondary ink-btn-sm">Cancel</button>
                    <button type="button" onclick="saveAdminTrafficCase()" class="ink-btn ink-btn-primary ink-btn-sm">Save Changes</button>
                </div>
            </div>
        </div>