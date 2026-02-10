    <!-- Demand Request Modal -->
    <div id="demandRequestModal" class="modal-overlay" onclick="if(event.target===this) closeModal('demandRequestModal')">
        <div class="modal-content m-shell" onclick="event.stopPropagation()" style="max-width: 480px;">
            <div class="m-header">
                <div class="m-header-title"><h2>Request New Demand Case</h2></div>
                <button onclick="closeModal('demandRequestModal')" class="m-close">&times;</button>
            </div>
            <form id="demandRequestForm" onsubmit="submitDemandRequest(event)">
                <div class="m-body">
                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Client Name *</label>
                            <input type="text" name="client_name" required class="m-input" placeholder="Full name">
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Case Number</label>
                            <input type="text" name="case_number" class="m-input" placeholder="e.g. 24-CV-1234">
                        </div>
                        <div>
                            <label class="m-label">Case Type</label>
                            <select name="case_type" class="m-input">
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
                            <textarea name="note" class="m-input" rows="3" style="resize: vertical;" placeholder="Additional details for Chong"></textarea>
                        </div>
                    </div>
                </div>
                <div class="m-footer">
                    <button type="button" onclick="closeModal('demandRequestModal')" class="m-btn m-btn-secondary">Cancel</button>
                    <button type="submit" class="m-btn m-btn-primary">Send Request</button>
                </div>
            </form>
        </div>
    </div>
