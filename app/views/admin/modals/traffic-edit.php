        <!-- Admin Traffic Case Edit Modal -->
        <div id="adminTrafficModal" class="modal-overlay" style="display: none;" onclick="if(event.target === this) closeAdminTrafficModal()">
            <div class="modal-content m-shell" onclick="event.stopPropagation()" style="max-width: 560px;">
                <div class="m-header">
                    <div class="m-header-title"><h3>Edit Traffic Case</h3></div>
                    <button onclick="closeAdminTrafficModal()" class="m-close">&times;</button>
                </div>
                <div class="m-body">
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Client Name</label>
                            <input type="text" id="adminTrafficClientName" class="m-input">
                        </div>
                        <div>
                            <label class="m-label">Client Phone</label>
                            <input type="text" id="adminTrafficClientPhone" class="m-input">
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Court</label>
                            <input type="text" id="adminTrafficCourt" class="m-input">
                        </div>
                        <div>
                            <label class="m-label">Court Date</label>
                            <input type="date" id="adminTrafficCourtDate" class="m-input">
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Charge</label>
                            <input type="text" id="adminTrafficCharge" class="m-input">
                        </div>
                        <div>
                            <label class="m-label">Ticket #</label>
                            <input type="text" id="adminTrafficCaseNumber" class="m-input">
                        </div>
                    </div>
                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Prosecutor Offer</label>
                            <input type="text" id="adminTrafficOffer" class="m-input">
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Disposition</label>
                            <select id="adminTrafficDisposition" class="m-input">
                                <option value="pending">Pending</option>
                                <option value="dismissed">Dismissed</option>
                                <option value="amended">Amended</option>
                            </select>
                        </div>
                        <div>
                            <label class="m-label">Status</label>
                            <select id="adminTrafficStatus" class="m-input">
                                <option value="active">Active</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Ticket Issued</label>
                            <input type="date" id="adminTrafficTicketIssuedDate" class="m-input">
                        </div>
                        <div>
                            <label class="m-label">NOA Sent</label>
                            <input type="date" id="adminTrafficNoaSentDate" class="m-input">
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Referral Source</label>
                            <input type="text" id="adminTrafficReferralSource" class="m-input">
                        </div>
                        <div class="m-checkbox-row" style="border-top: none; margin-top: 16px; padding: 0;">
                            <input type="checkbox" id="adminTrafficDiscovery">
                            <label for="adminTrafficDiscovery">Discovery Received</label>
                        </div>
                    </div>
                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Note</label>
                            <textarea id="adminTrafficNote" class="m-input" rows="2" style="resize: vertical;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="m-footer">
                    <button type="button" onclick="closeAdminTrafficModal()" class="m-btn m-btn-secondary">Cancel</button>
                    <button type="button" onclick="saveAdminTrafficCase()" class="m-btn m-btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
