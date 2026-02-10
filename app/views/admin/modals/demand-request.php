        <!-- Demand Request Modal -->
        <div id="adminDemandRequestModal" class="modal-overlay" onclick="if(event.target === this) closeModal('adminDemandRequestModal')">
            <div class="modal-content m-shell" onclick="event.stopPropagation()" style="max-width: 480px;">
                <div class="m-header">
                    <div class="m-header-title"><h2>Send Demand Request to Chong</h2></div>
                    <button onclick="closeModal('adminDemandRequestModal')" class="m-close">&times;</button>
                </div>
                <form id="adminDemandRequestForm" onsubmit="submitAdminDemandRequest(event)">
                    <div class="m-body">
                        <div class="m-row cols-1">
                            <div>
                                <label class="m-label">Client Name *</label>
                                <input type="text" id="admDemReqClientName" class="m-input" required placeholder="Full name">
                            </div>
                        </div>
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Case Number</label>
                                <input type="text" id="admDemReqCaseNumber" class="m-input" placeholder="e.g. 24-CV-1234">
                            </div>
                            <div>
                                <label class="m-label">Case Type</label>
                                <select id="admDemReqCaseType" class="m-input">
                                    <option value="Auto">Auto</option>
                                    <option value="Slip and Fall">Slip and Fall</option>
                                    <option value="Dog Bite">Dog Bite</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="m-row cols-1">
                            <div>
                                <label class="m-label">Note</label>
                                <textarea id="admDemReqNote" class="m-input" rows="2" style="resize: vertical;" placeholder="Additional notes for Chong"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="m-footer">
                        <button type="button" onclick="closeModal('adminDemandRequestModal')" class="m-btn m-btn-secondary">Cancel</button>
                        <button type="submit" class="m-btn m-btn-primary">Send Request</button>
                    </div>
                </form>
            </div>
        </div>
