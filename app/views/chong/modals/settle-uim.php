    <!-- Settle UIM Modal -->
    <div id="settleUimModal" class="modal-overlay hidden">
        <div class="modal-content m-shell" style="max-width: 500px;">
            <div class="m-header">
                <div class="m-header-title"><h2>Settle UIM Case</h2></div>
                <button class="m-close" onclick="closeModal('settleUimModal')">&times;</button>
            </div>
            <form id="settleUimForm" onsubmit="submitSettleUim(event)">
                <input type="hidden" name="case_id">
                <div class="m-body">
                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Case: <span id="settleUimCaseInfo" style="font-weight: 600;"></span></label>
                        </div>
                    </div>
                    <div class="m-row cols-1">
                        <div class="m-info-box" style="background: #f5f3ff; border-color: #c4b5fd;">
                            <span style="color: #7c3aed; font-weight: 600;">1st Settlement:</span>
                            <span id="settleUimPrevSettled" style="font-weight: 700;">$0</span>
                            &nbsp;|&nbsp;
                            <span style="color: #7c3aed; font-weight: 600;">1st Commission:</span>
                            <span id="settleUimPrevComm" style="font-weight: 700;">$0</span>
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">UIM Settled Amount ($) *</label>
                            <input type="number" name="settled" class="m-input" step="0.01" min="0" required oninput="updateSettleUimLegalFee()">
                        </div>
                        <div>
                            <label class="m-label">Legal Fee (33.33%)</label>
                            <input type="text" name="legal_fee_display" class="m-input calculated" readonly>
                        </div>
                    </div>
                    <div class="m-row cols-3">
                        <div>
                            <label class="m-label">Disc. Legal Fee ($) * <span style="font-size:11px;color:#6b7280;">(Editable)</span></label>
                            <input type="number" name="discounted_legal_fee" class="m-input" step="0.01" min="0" required oninput="this.dataset.userModified='true'; calculateSettleUimCommission()">
                        </div>
                        <div>
                            <label class="m-label">UIM Commission (5%)</label>
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
                        <input type="checkbox" name="check_received" id="uimCheckReceived">
                        <label for="uimCheckReceived">Check Received</label>
                    </div>
                </div>
                <div class="m-footer">
                    <button type="button" class="m-btn m-btn-secondary" onclick="closeModal('settleUimModal')">Cancel</button>
                    <button type="submit" class="m-btn m-btn-success">Settle UIM</button>
                </div>
            </form>
        </div>
    </div>
