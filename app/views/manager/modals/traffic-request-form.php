    <!-- Traffic Request Modal -->
    <div id="trafficRequestModal" class="modal-overlay" onclick="if(event.target === this) closeModal('trafficRequestModal')">
        <div class="modal-content m-shell" onclick="event.stopPropagation()" style="max-width: 560px;">
            <div class="m-header">
                <div class="m-header-title"><h2>Request New Traffic Case</h2></div>
                <button onclick="closeModal('trafficRequestModal')" class="m-close">&times;</button>
            </div>
            <form id="trafficRequestForm" onsubmit="submitTrafficRequest(event)">
                <div class="m-body">
                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Client Name *</label>
                            <input type="text" id="reqClientName" class="m-input" required placeholder="Full name">
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Phone</label>
                            <input type="text" id="reqClientPhone" class="m-input" placeholder="(xxx) xxx-xxxx">
                        </div>
                        <div>
                            <label class="m-label">Email</label>
                            <input type="email" id="reqClientEmail" class="m-input" placeholder="email@example.com">
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Ticket #</label>
                            <input type="text" id="reqCaseNumber" class="m-input">
                        </div>
                        <div>
                            <label class="m-label">Court</label>
                            <input type="text" id="reqCourt" class="m-input">
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Charge</label>
                            <input type="text" id="reqCharge" class="m-input">
                        </div>
                        <div>
                            <label class="m-label">Requester</label>
                            <input type="text" id="reqReferralSource" class="m-input">
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Issued</label>
                            <input type="date" id="reqCitationIssuedDate" class="m-input">
                        </div>
                        <div>
                            <label class="m-label">Court Date</label>
                            <input type="date" id="reqCourtDate" class="m-input">
                        </div>
                    </div>
                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Note</label>
                            <textarea id="reqNote" class="m-input" rows="2" style="resize: vertical;" placeholder="Additional notes"></textarea>
                        </div>
                    </div>
                </div>
                <div class="m-footer">
                    <button type="button" onclick="closeModal('trafficRequestModal')" class="m-btn m-btn-secondary">Cancel</button>
                    <button type="submit" class="m-btn m-btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
