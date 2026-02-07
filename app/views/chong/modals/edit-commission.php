    <!-- Edit Commission Modal -->
    <div id="editCommissionModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Edit Commission</h2>
                <button class="modal-close" onclick="closeModal('editCommissionModal')">&times;</button>
            </div>
            <div style="padding: 20px;">
                <input type="hidden" id="editCommCaseId">

                <div class="form-row">
                    <div class="form-group">
                        <label>Client Name</label>
                        <input type="text" id="editCommClientName">
                    </div>
                    <div class="form-group">
                        <label>Resolution Type</label>
                        <select id="editCommResolutionType">
                            <option value="">Select...</option>
                            <option value="File and Bump">File and Bump</option>
                            <option value="Post Deposition Settle">Post Deposition Settle</option>
                            <option value="Mediation">Mediation</option>
                            <option value="Settled Post Arbitration">Settled Post Arbitration</option>
                            <option value="Settlement Conference">Settlement Conference</option>
                            <option value="Arbitration Award">Arbitration Award</option>
                            <option value="Beasley">Beasley</option>
                            <option value="Co-Counsel">Co-Counsel</option>
                            <option value="Other">Other</option>
                            <option value="No Offer Settle">No Offer Settle</option>
                            <option value="Demand">Demand</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Settled Amount</label>
                        <input type="number" id="editCommSettled" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Pre-Suit Offer</label>
                        <input type="number" id="editCommPreSuitOffer" step="0.01" min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Legal Fee</label>
                        <input type="number" id="editCommLegalFee" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Discounted Legal Fee</label>
                        <input type="number" id="editCommDiscountedFee" step="0.01" min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Commission</label>
                        <input type="number" id="editCommCommission" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Month</label>
                        <select id="editCommMonth">
                            <?php foreach (getMonthOptions() as $month): ?>
                            <option value="<?php echo $month; ?>"><?php echo $month; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="modal-footer" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #e2e4ea;">
                    <button type="button" class="ink-btn ink-btn-secondary" onclick="closeModal('editCommissionModal')">Cancel</button>
                    <button type="button" class="ink-btn ink-btn-primary" onclick="saveCommission()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
