    <!-- New Demand Case Modal -->
    <div id="newDemandModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Add New Demand Case</h2>
                <button class="modal-close" onclick="closeModal('newDemandModal')">&times;</button>
            </div>
            <form id="newDemandForm" onsubmit="submitNewDemand(event)">
                <input type="hidden" id="newDemandPhase" name="phase" value="demand">
                <input type="hidden" id="newDemandStage" name="stage" value="demand_review">
                <div class="form-row">
                    <div class="form-group">
                        <label>Case Number *</label>
                        <input type="text" name="case_number" required>
                    </div>
                    <div class="form-group">
                        <label>Client Name *</label>
                        <input type="text" name="client_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Case Type</label>
                        <select name="case_type">
                            <option value="Auto Accident">Auto Accident</option>
                            <option value="Pedestrian">Pedestrian</option>
                            <option value="Slip and Fall">Slip and Fall</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assigned Date</label>
                        <input type="date" id="newDemandAssignedDate" name="assigned_date" value="<?php echo date('Y-m-d'); ?>">
                        <div class="help-text">Deadline auto-calculated: +90 days</div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Settlement (Optional - fill if settling now)</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Settled Amount ($)</label>
                            <input type="number" name="settled" step="0.01" min="0" onchange="calculateDemandCommission()">
                        </div>
                        <div class="form-group">
                            <label>Discounted Legal Fee ($)</label>
                            <input type="number" name="discounted_legal_fee" step="0.01" min="0" onchange="calculateDemandCommission()">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Commission (5% of Disc. Legal Fee)</label>
                            <input type="text" name="commission_display" class="calculated" readonly>
                        </div>
                        <div class="form-group">
                            <label>Month</label>
                            <select name="month">
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

                <div class="form-row full">
                    <div class="form-group">
                        <label>Note</label>
                        <input type="text" name="note">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('newDemandModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Case</button>
                </div>
            </form>
        </div>
    </div>
