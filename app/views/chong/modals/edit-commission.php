    <!-- Edit Commission Modal -->
    <div id="editCommissionModal" class="modal-overlay hidden">
        <div class="modal-content m-shell" style="max-width: 600px;">
            <div class="m-header">
                <div class="m-header-title"><h2>Edit Commission</h2></div>
                <button class="m-close" onclick="closeModal('editCommissionModal')">&times;</button>
            </div>
            <div class="m-body">
                <input type="hidden" id="editCommCaseId">

                <div class="m-row cols-2">
                    <div>
                        <label class="m-label">Client Name</label>
                        <input type="text" id="editCommClientName" class="m-input">
                    </div>
                    <div>
                        <label class="m-label">Resolution Type</label>
                        <select id="editCommResolutionType" class="m-input">
                            <option value="">Select...</option>
                            <option value="Ongoing Case">Ongoing Case</option>
                            <option value="Demand Settled">Demand Settled</option>
                            <option value="No Offer Settle">No Offer Settle</option>
                            <option value="File and Bump">File and Bump</option>
                            <option value="Post Deposition Settle">Post Deposition Settle</option>
                            <option value="Mediation">Mediation</option>
                            <option value="Settled Post Arbitration">Settled Post Arbitration</option>
                            <option value="Arbitration Award">Arbitration Award</option>
                            <option value="Beasley">Beasley</option>
                            <option value="Settlement Conference">Settlement Conference</option>
                            <option value="Co-Counsel">Co-Counsel</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="m-row cols-2">
                    <div>
                        <label class="m-label">Settled Amount</label>
                        <input type="number" id="editCommSettled" class="m-input" step="0.01" min="0">
                    </div>
                    <div>
                        <label class="m-label">Pre-Suit Offer</label>
                        <input type="number" id="editCommPreSuitOffer" class="m-input" step="0.01" min="0">
                    </div>
                </div>

                <div class="m-row cols-2">
                    <div>
                        <label class="m-label">Legal Fee</label>
                        <input type="number" id="editCommLegalFee" class="m-input" step="0.01" min="0">
                    </div>
                    <div>
                        <label class="m-label">Discounted Legal Fee</label>
                        <input type="number" id="editCommDiscountedFee" class="m-input" step="0.01" min="0">
                    </div>
                </div>

                <div class="m-row cols-2">
                    <div>
                        <label class="m-label">Commission</label>
                        <input type="number" id="editCommCommission" class="m-input" step="0.01" min="0">
                    </div>
                    <div>
                        <label class="m-label">Month</label>
                        <select id="editCommMonth" class="m-input">
                            <?php foreach (getMonthOptions() as $month): ?>
                            <option value="<?php echo $month; ?>"><?php echo $month; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="m-row cols-2">
                    <div>
                        <label class="m-label">Status</label>
                        <select id="editCommStatus" class="m-input">
                            <option value="unpaid">Unpaid</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                    <div>
                        <label class="m-label">Check Received</label>
                        <label class="m-toggle">
                            <input type="checkbox" id="editCommCheckReceived">
                            <span class="m-toggle-label">Check received</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="m-footer">
                <button type="button" class="m-btn m-btn-secondary" onclick="closeModal('editCommissionModal')">Cancel</button>
                <button type="button" class="m-btn m-btn-primary" onclick="saveCommission()">Save Changes</button>
            </div>
        </div>
    </div>
