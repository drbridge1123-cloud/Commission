    <!-- Top Offer Modal -->
    <div id="topOfferModal" class="modal-overlay hidden">
        <div class="modal-content m-shell" style="max-width: 500px;">
            <div class="m-header">
                <div class="m-header-title"><h2>Submit Top Offer</h2></div>
                <button class="m-close" onclick="closeModal('topOfferModal')">&times;</button>
            </div>
            <form id="topOfferForm" onsubmit="submitTopOffer(event)">
                <input type="hidden" id="topOfferCaseId">
                <div class="m-body">
                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Case: <span id="topOfferCaseInfo" style="font-weight: 600;"></span></label>
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Top Offer Amount ($) *</label>
                            <input type="number" id="topOfferAmount" class="m-input" step="0.01" min="0" required>
                        </div>
                        <div>
                            <label class="m-label">Assign To *</label>
                            <select id="topOfferAssignee" class="m-input" required>
                                <option value="">Select Employee...</option>
                            </select>
                        </div>
                    </div>
                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Note</label>
                            <textarea id="topOfferNote" rows="3" class="m-input" placeholder="Optional note..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="m-footer">
                    <button type="button" class="m-btn m-btn-secondary" onclick="closeModal('topOfferModal')">Cancel</button>
                    <button type="submit" class="m-btn m-btn-primary">Submit Top Offer</button>
                </div>
            </form>
        </div>
    </div>
