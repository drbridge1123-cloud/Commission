    <!-- Settle Demand Modal -->
    <div id="settleDemandModal" class="modal-overlay hidden">
        <div class="modal-content m-shell" style="max-width: 500px;">
            <div class="m-header">
                <div class="m-header-title"><h2>Settle Demand Case</h2></div>
                <button class="m-close" onclick="closeModal('settleDemandModal')">&times;</button>
            </div>
            <form id="settleDemandForm" onsubmit="submitSettleDemand(event)">
                <input type="hidden" name="case_id">
                <div class="m-body">
                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Case: <span id="settleDemandCaseInfo" style="font-weight: 600;"></span></label>
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Settled Amount ($) *</label>
                            <input type="number" name="settled" class="m-input" step="0.01" min="0" required oninput="updateSettleDemandLegalFee()">
                        </div>
                        <div>
                            <label class="m-label">Legal Fee (33.33%)</label>
                            <input type="text" name="legal_fee_display" class="m-input calculated" readonly>
                        </div>
                    </div>
                    <div class="m-row cols-3">
                        <div>
                            <label class="m-label">Disc. Legal Fee ($) * <span style="font-size:11px;color:#6b7280;">(Editable)</span></label>
                            <input type="number" name="discounted_legal_fee" class="m-input" step="0.01" min="0" required oninput="this.dataset.userModified='true'; calculateSettleDemandCommission()">
                        </div>
                        <div>
                            <label class="m-label">Commission (5%)</label>
                            <input type="text" name="commission_display" class="m-input calculated" readonly>
                        </div>
                        <div>
                            <label class="m-label">Month</label>
                            <select name="month" class="m-input">
                                <?php foreach ($months as $m): ?>
                                    <option value="<?php echo $m; ?>" <?php echo ($m === $currentMonth) ? 'selected' : ''; ?>><?php echo $m; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="m-checkbox-row">
                        <input type="checkbox" name="check_received" id="demandCheckReceived">
                        <label for="demandCheckReceived">Check Received</label>
                    </div>
                    <div class="m-checkbox-row">
                        <input type="checkbox" name="is_policy_limit" id="demandPolicyLimit">
                        <label for="demandPolicyLimit" style="color: #7c3aed; font-weight: 500;">Policy Limit → UIM</label>
                    </div>
                </div>
                <div class="m-footer">
                    <button type="button" class="m-btn m-btn-secondary" onclick="closeModal('settleDemandModal')">Cancel</button>
                    <button type="submit" class="m-btn m-btn-success">Settle Case</button>
                </div>
            </form>
        </div>
    </div>
