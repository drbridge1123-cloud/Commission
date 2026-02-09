    <!-- Settle Litigation Modal -->
    <div id="settleLitigationModal" class="modal-overlay hidden">
        <div class="modal-content m-shell" style="max-width: 650px;">
            <div class="m-header">
                <div class="m-header-title"><h2>Settle Litigation Case</h2></div>
                <button class="m-close" onclick="closeModal('settleLitigationModal')">&times;</button>
            </div>
            <form id="settleLitigationForm" onsubmit="submitSettleLitigation(event)">
                <input type="hidden" name="case_id">
                <input type="hidden" name="presuit_offer_hidden">
                <div class="m-body">
                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Case: <span id="settleLitCaseInfo" style="font-weight: 600;"></span></label>
                        </div>
                    </div>

                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Resolution Type *</label>
                            <select name="resolution_type" class="m-input" required onchange="onResolutionTypeChange()">
                                <option value="">-- Select --</option>
                                <option value="Ongoing Case">Ongoing Case</option>
                                <option value="Demand Settled">Demand Settled</option>
                                <optgroup label="33.33% (Pre-Suit Deducted)">
                                    <option value="No Offer Settle">No Offer Settle</option>
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
                                </optgroup>
                            </select>
                        </div>
                        <div>
                            <label class="m-label">Pre-Suit Offer ($)</label>
                            <input type="number" name="presuit_offer" class="m-input calculated" step="0.01" readonly>
                        </div>
                    </div>

                    <div id="resolutionInfo" class="m-info-box" style="display:none;">
                        <span class="m-info-label">Fee Rate:</span>
                        <select id="feeRateSelect" style="display:inline-block;width:auto;padding:2px 8px;font-size:13px;font-weight:700;border:1px solid #d1d5db;border-radius:4px;background:#fff;cursor:pointer;margin:0 2px;" onchange="onFeeRateChange()">
                            <option value="33.33">33.33%</option>
                            <option value="40">40%</option>
                        </select>
                        <span id="feeRateOverrideTag" style="display:none;color:#e65100;font-size:11px;font-weight:600;margin-left:2px;">(Override)</span>
                        &nbsp;|&nbsp;
                        <span class="m-info-label">Commission Rate:</span> <span class="m-info-value" id="infoCommRate">-</span>
                    </div>

                    <div class="m-section">
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Settled Amount ($) *</label>
                                <input type="number" name="settled" class="m-input" step="0.01" min="0" required onchange="calculateLitCommission()">
                            </div>
                            <div>
                                <label class="m-label">Difference ($)</label>
                                <input type="text" name="difference_display" class="m-input calculated" readonly>
                            </div>
                        </div>
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Legal Fee (Reference)</label>
                                <input type="text" name="legal_fee_display" class="m-input calculated" readonly>
                            </div>
                            <div>
                                <label class="m-label">Disc. Legal Fee ($) * <span style="font-size:11px;color:#6b7280;">(Editable)</span></label>
                                <input type="number" name="discounted_legal_fee" class="m-input" step="0.01" min="0" required oninput="this.dataset.userModified='true'; calculateLitCommission()">
                            </div>
                        </div>

                        <!-- Variable fields (hidden by default) -->
                        <div id="variableFields" class="m-row cols-2" style="display:none;">
                            <div>
                                <label class="m-label">Manual Fee Rate (%)</label>
                                <input type="number" name="manual_fee_rate" class="m-input" step="0.01" min="0" max="100" onchange="calculateLitCommission()">
                            </div>
                            <div>
                                <label class="m-label">Manual Commission Rate (%)</label>
                                <input type="number" name="manual_commission_rate" class="m-input" step="0.01" min="0" max="100" onchange="calculateLitCommission()">
                            </div>
                        </div>

                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Commission</label>
                                <input type="text" name="commission_display" class="m-input calculated" readonly style="font-size: 18px; font-weight: 700; color: #059669;">
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
                    </div>

                    <div class="m-row cols-1">
                        <div id="litNoteContainer">
                            <label class="m-label" id="litNoteLabel">Note</label>
                            <input type="text" name="note" id="litNote" class="m-input" placeholder="Add a note...">
                            <div id="litNoteRequired" style="display:none;color:#dc2626;font-size:11px;margin-top:4px;">* Required when fee rate is overridden</div>
                        </div>
                    </div>

                    <div class="m-checkbox-row">
                        <input type="checkbox" name="check_received" id="litCheckReceived">
                        <label for="litCheckReceived">Check Received</label>
                    </div>
                </div>

                <div class="m-footer">
                    <button type="button" class="m-btn m-btn-secondary" onclick="closeModal('settleLitigationModal')">Cancel</button>
                    <button type="submit" class="m-btn m-btn-success">Settle Case</button>
                </div>
            </form>
        </div>
    </div>
