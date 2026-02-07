    <!-- Edit Case Modal -->
    <div id="editCaseModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2>Edit Case</h2>
                <button class="modal-close" onclick="closeModal('editCaseModal')">&times;</button>
            </div>
            <form id="editCaseForm" onsubmit="submitEditCase(event)">
                <input type="hidden" id="editCaseId">

                <div class="form-row">
                    <div class="form-group">
                        <label>Case Number *</label>
                        <input type="text" id="editCaseNumber" required>
                    </div>
                    <div class="form-group">
                        <label>Client Name *</label>
                        <input type="text" id="editClientName" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Phase</label>
                        <select id="editPhase" onchange="toggleEditPhaseFields()">
                            <option value="demand">Demand</option>
                            <option value="litigation">Litigation</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Month</label>
                        <select id="editMonth">
                            <?php foreach (getMonthOptions() as $month): ?>
                            <option value="<?php echo $month; ?>"><?php echo $month; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row" id="editStageRow">
                    <div class="form-group">
                        <label>Stage</label>
                        <select id="editStage">
                            <option value="">Select Stage...</option>
                            <option value="demand_review">Demand Review</option>
                            <option value="demand_write">Demand Write</option>
                            <option value="demand_sent">Demand Sent</option>
                            <option value="negotiate">Negotiate</option>
                        </select>
                    </div>
                    <div class="form-group"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Settled Amount</label>
                        <input type="number" id="editSettled" step="0.01" min="0" onchange="calculateEditCommission()">
                    </div>
                    <div class="form-group">
                        <label>Discounted Legal Fee</label>
                        <input type="number" id="editDiscLegalFee" step="0.01" min="0" onchange="calculateEditCommission()">
                    </div>
                </div>

                <div id="editLitigationFields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Pre-suit Offer</label>
                            <input type="number" id="editPresuitOffer" step="0.01" min="0" onchange="calculateEditCommission()">
                        </div>
                        <div class="form-group">
                            <label>Resolution Type</label>
                            <select id="editResolutionType" onchange="calculateEditCommission()">
                                <option value="">Select...</option>
                                <option value="TBD">TBD</option>
                                <optgroup label="33.33% Fee Rate">
                                    <option value="File and Bump">File and Bump</option>
                                    <option value="Post Deposition Settle">Post Deposition Settle</option>
                                    <option value="Mediation">Mediation</option>
                                    <option value="Settled Post Arbitration">Settled Post Arbitration</option>
                                    <option value="Settlement Conference">Settlement Conference</option>
                                </optgroup>
                                <optgroup label="40% Fee Rate">
                                    <option value="Arbitration Award">Arbitration Award</option>
                                    <option value="Beasley">Beasley</option>
                                </optgroup>
                                <optgroup label="Variable">
                                    <option value="Co-Counsel">Co-Counsel</option>
                                    <option value="Other">Other</option>
                                    <option value="No Offer Settle">No Offer Settle</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Assigned Date</label>
                        <input type="date" id="editAssignedDate">
                    </div>
                    <div class="form-group">
                        <label>Note</label>
                        <input type="text" id="editNote">
                    </div>
                </div>

                <!-- Deadline Extension Request Section -->
                <div id="deadlineSection" class="deadline-extension-section" style="display: none;">
                    <div class="form-section-title" style="margin-top: 16px;">Deadline Management</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Current Deadline</label>
                            <input type="date" id="editCurrentDeadline" disabled style="background: #f3f4f6;">
                        </div>
                        <div class="form-group">
                            <label>Days Remaining</label>
                            <input type="text" id="editDaysRemaining" disabled style="background: #f3f4f6;">
                        </div>
                    </div>
                    <div id="pendingExtensionAlert" class="pending-extension-alert" style="display: none;">
                        <span class="alert-icon">⏳</span>
                        <span class="alert-text">Deadline extension request pending approval</span>
                    </div>
                    <div id="deadlineExtensionForm" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Requested New Deadline <span class="required">*</span></label>
                                <input type="date" id="editRequestedDeadline">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Reason for Extension <span class="required">*</span></label>
                            <textarea id="editExtensionReason" rows="3" placeholder="Please explain why you need a deadline extension..."></textarea>
                        </div>
                        <div style="display: flex; gap: 8px; margin-top: 8px;">
                            <button type="button" class="btn btn-warning" onclick="submitDeadlineExtension()">Submit Extension Request</button>
                            <button type="button" class="btn btn-secondary" onclick="cancelDeadlineExtension()">Cancel</button>
                        </div>
                    </div>
                    <button type="button" id="requestExtensionBtn" class="btn btn-outline" onclick="showDeadlineExtensionForm()">
                        Request Deadline Extension
                    </button>
                </div>

                <div style="background: #f3f4f6; padding: 12px 16px; border-radius: 8px; margin: 16px 0; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600;">Commission:</span>
                    <span id="editCommissionDisplay" style="font-size: 20px; font-weight: 700; color: #059669;">$0.00</span>
                </div>

                <div class="modal-footer" style="justify-content: space-between;">
                    <button type="button" class="btn" style="background:#dc2626; color:#fff;" onclick="deleteCaseFromModal()">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 4px;"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Delete
                    </button>
                    <div style="display: flex; gap: 12px;">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('editCaseModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
