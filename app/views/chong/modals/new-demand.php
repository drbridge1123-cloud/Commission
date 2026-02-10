    <!-- New Demand Case Modal -->
    <div id="newDemandModal" class="modal-overlay hidden">
        <div class="modal-content m-shell" style="max-width: 600px;">
            <div class="m-header">
                <div class="m-header-title"><h2>Add New Demand Case</h2></div>
                <button class="m-close" onclick="closeModal('newDemandModal')">&times;</button>
            </div>
            <form id="newDemandForm" onsubmit="submitNewDemand(event)">
                <input type="hidden" id="newDemandPhase" name="phase" value="demand">
                <input type="hidden" id="newDemandStage" name="stage" value="demand_review">
                <div class="m-body">
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Case Number *</label>
                            <input type="text" name="case_number" class="m-input" required>
                        </div>
                        <div>
                            <label class="m-label">Client Name *</label>
                            <input type="text" name="client_name" class="m-input" required>
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Case Type</label>
                            <select name="case_type" class="m-input">
                                <option value="Auto">Auto</option>
                                <option value="Pedestrian">Pedestrian</option>
                                <option value="Slip and Fall">Slip and Fall</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="m-label">Assigned Date</label>
                            <input type="date" id="newDemandAssignedDate" name="assigned_date" class="m-input" value="<?php echo date('Y-m-d'); ?>">
                            <div class="m-help-text">Deadline auto-calculated: +90 days</div>
                        </div>
                    </div>

                    <div class="m-section">
                        <button type="button" class="m-bypass-toggle" onclick="toggleSettlementSection(this)">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                            Settle now? (Optional)
                        </button>
                        <div id="newDemandSettlementFields" class="m-collapsible" style="display:none;">
                            <div class="m-row cols-2">
                                <div>
                                    <label class="m-label">Settled Amount ($)</label>
                                    <input type="number" name="settled" class="m-input" step="0.01" min="0" onchange="calculateDemandCommission()">
                                </div>
                                <div>
                                    <label class="m-label">Discounted Legal Fee ($)</label>
                                    <input type="number" name="discounted_legal_fee" class="m-input" step="0.01" min="0" onchange="calculateDemandCommission()">
                                </div>
                            </div>
                            <div class="m-row cols-2">
                                <div>
                                    <label class="m-label">Commission (5% of Disc. Legal Fee)</label>
                                    <input type="text" name="commission_display" class="m-input calculated" readonly>
                                </div>
                                <div>
                                    <label class="m-label">Month</label>
                                    <select name="month" class="m-input">
                                        <?php
                                        $months = getMonthOptions();
                                        $currentMonth = getCurrentMonth();
                                        foreach ($months as $m) {
                                            $selected = ($m === $currentMonth) ? 'selected' : '';
                                            echo "<option value=\"$m\" $selected>$m</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Note</label>
                            <input type="text" name="note" class="m-input">
                        </div>
                    </div>
                </div>

                <div class="m-footer">
                    <button type="button" class="m-btn m-btn-secondary" onclick="closeModal('newDemandModal')">Cancel</button>
                    <button type="submit" class="m-btn m-btn-primary">Add Case</button>
                </div>
            </form>
        </div>
    </div>
