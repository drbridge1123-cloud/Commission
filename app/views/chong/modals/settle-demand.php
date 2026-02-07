    <!-- Settle Demand Modal -->
    <div id="settleDemandModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Settle Demand Case</h2>
                <button class="modal-close" onclick="closeModal('settleDemandModal')">&times;</button>
            </div>
            <form id="settleDemandForm" onsubmit="submitSettleDemand(event)">
                <input type="hidden" name="case_id">
                <div class="form-row full">
                    <div class="form-group">
                        <label>Case: <span id="settleDemandCaseInfo" style="font-weight: 600;"></span></label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Settled Amount ($) *</label>
                        <input type="number" name="settled" step="0.01" min="0" required oninput="updateSettleDemandLegalFee()">
                    </div>
                    <div class="form-group">
                        <label>Legal Fee (33.33%)</label>
                        <input type="text" name="legal_fee_display" class="calculated" readonly style="background: #f3f4f6;">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Disc. Legal Fee ($) * <span style="font-size:11px;color:#6b7280;">(Editable)</span></label>
                        <input type="number" name="discounted_legal_fee" step="0.01" min="0" required oninput="this.dataset.userModified='true'; calculateSettleDemandCommission()">
                    </div>
                    <div class="form-group">
                        <label>Commission (5%)</label>
                        <input type="text" name="commission_display" class="calculated" readonly>
                    </div>
                    <div class="form-group">
                        <label>Month</label>
                        <select name="month">
                            <?php foreach ($months as $m): ?>
                                <option value="<?php echo $m; ?>" <?php echo ($m === $currentMonth) ? 'selected' : ''; ?>><?php echo $m; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; padding: 12px 0; margin-top: 8px; border-top: 1px solid #e5e7eb;">
                    <input type="checkbox" name="check_received" id="demandCheckReceived" style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="demandCheckReceived" style="font-size: 13px; font-weight: 500; color: #374151; cursor: pointer; margin: 0;">Check Received</label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('settleDemandModal')">Cancel</button>
                    <button type="submit" class="act-btn settle" style="padding: 8px 20px; font-size: 13px;">Settle Case</button>
                </div>
            </form>
        </div>
    </div>
