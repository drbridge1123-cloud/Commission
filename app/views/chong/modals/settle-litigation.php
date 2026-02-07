    <!-- Settle Litigation Modal -->
    <div id="settleLitigationModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 650px;">
            <div class="modal-header">
                <h2>Settle Litigation Case</h2>
                <button class="modal-close" onclick="closeModal('settleLitigationModal')">&times;</button>
            </div>
            <form id="settleLitigationForm" onsubmit="submitSettleLitigation(event)">
                <input type="hidden" name="case_id">
                <input type="hidden" name="presuit_offer_hidden">

                <div class="form-row full">
                    <div class="form-group">
                        <label>Case: <span id="settleLitCaseInfo" style="font-weight: 600;"></span></label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Resolution Type *</label>
                        <select name="resolution_type" required onchange="onResolutionTypeChange()">
                            <option value="">-- Select --</option>
                            <option value="TBD">TBD</option>
                            <optgroup label="33.33% (Pre-Suit Deducted)">
                                <option value="File and Bump">File and Bump</option>
                                <option value="Post Deposition Settle">Post Deposition Settle</option>
                                <option value="Mediation">Mediation</option>
                                <option value="Settled Post Arbitration">Settled Post Arbitration</option>
                                <option value="Settlement Conference">Settlement Conference</option>
                            </optgroup>
                            <optgroup label="40% (No Deduction)">
                                <option value="Arbitration Award">Arbitration Award</option>
                                <option value="Beasley">Beasley</option>
                            </optgroup>
                            <optgroup label="Variable (Manual)">
                                <option value="Co-Counsel">Co-Counsel</option>
                                <option value="Other">Other</option>
                                <option value="No Offer Settle">No Offer Settle</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Pre-Suit Offer ($)</label>
                        <input type="number" name="presuit_offer" step="0.01" readonly class="calculated">
                    </div>
                </div>

                <div id="resolutionInfo" class="resolution-info" style="display:none;">
                    <span class="label">Fee Rate:</span> <span class="value" id="infoFeeRate">-</span> |
                    <span class="label">Commission Rate:</span> <span class="value" id="infoCommRate">-</span>
                </div>

                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Settled Amount ($) *</label>
                            <input type="number" name="settled" step="0.01" min="0" required onchange="calculateLitCommission()">
                        </div>
                        <div class="form-group">
                            <label>Difference ($)</label>
                            <input type="text" name="difference_display" class="calculated" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Legal Fee (Reference)</label>
                            <input type="text" name="legal_fee_display" class="calculated" readonly>
                        </div>
                        <div class="form-group">
                            <label>Disc. Legal Fee ($) * <span style="font-size:11px;color:#6b7280;">(Editable)</span></label>
                            <input type="number" name="discounted_legal_fee" step="0.01" min="0" required oninput="this.dataset.userModified='true'; calculateLitCommission()">
                        </div>
                    </div>

                    <!-- Variable fields (hidden by default) -->
                    <div id="variableFields" class="form-row" style="display:none;">
                        <div class="form-group">
                            <label>Manual Fee Rate (%)</label>
                            <input type="number" name="manual_fee_rate" step="0.01" min="0" max="100" onchange="calculateLitCommission()">
                        </div>
                        <div class="form-group">
                            <label>Manual Commission Rate (%)</label>
                            <input type="number" name="manual_commission_rate" step="0.01" min="0" max="100" onchange="calculateLitCommission()">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Commission</label>
                            <input type="text" name="commission_display" class="calculated" readonly style="font-size: 18px; font-weight: 700; color: #059669;">
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
                </div>

                <div style="display: flex; align-items: center; gap: 8px; padding: 12px 0; margin-top: 8px; border-top: 1px solid #e5e7eb;">
                    <input type="checkbox" name="check_received" id="litCheckReceived" style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="litCheckReceived" style="font-size: 13px; font-weight: 500; color: #374151; cursor: pointer; margin: 0;">Check Received</label>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('settleLitigationModal')">Cancel</button>
                    <button type="submit" class="act-btn settle" style="padding: 8px 20px; font-size: 13px;">Settle Case</button>
                </div>
            </form>
        </div>
    </div>
