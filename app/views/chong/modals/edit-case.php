    <!-- Edit Case Modal -->
    <div id="editCaseModal" class="modal-overlay hidden">
        <div class="modal-content m-shell" style="max-width: 700px;">
            <div class="m-header">
                <div class="m-header-title"><h2>Edit Case</h2></div>
                <button class="m-close" onclick="closeModal('editCaseModal')">&times;</button>
            </div>
            <form id="editCaseForm" onsubmit="submitEditCase(event)">
                <input type="hidden" id="editCaseId">
                <div class="m-body">
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Case Number *</label>
                            <input type="text" id="editCaseNumber" class="m-input" required>
                        </div>
                        <div>
                            <label class="m-label">Client Name *</label>
                            <input type="text" id="editClientName" class="m-input" required>
                        </div>
                    </div>

                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Phase</label>
                            <select id="editPhase" class="m-input" onchange="toggleEditPhaseFields()">
                                <option value="demand">Demand</option>
                                <option value="litigation">Litigation</option>
                            </select>
                        </div>
                        <div>
                            <label class="m-label">Month</label>
                            <select id="editMonth" class="m-input">
                                <?php foreach (getMonthOptions() as $month): ?>
                                <option value="<?php echo $month; ?>"><?php echo $month; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="m-row cols-2" id="editStageRow">
                        <div>
                            <label class="m-label">Stage</label>
                            <select id="editStage" class="m-input">
                                <option value="">Select Stage...</option>
                                <option value="demand_review">Demand Review</option>
                                <option value="demand_write">Demand Write</option>
                                <option value="demand_sent">Demand Sent</option>
                                <option value="negotiate">Negotiate</option>
                            </select>
                        </div>
                        <div></div>
                    </div>

                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Settled Amount</label>
                            <input type="number" id="editSettled" class="m-input" step="0.01" min="0" onchange="calculateEditCommission()">
                        </div>
                        <div>
                            <label class="m-label">Discounted Legal Fee</label>
                            <input type="number" id="editDiscLegalFee" class="m-input" step="0.01" min="0" onchange="calculateEditCommission()">
                        </div>
                    </div>

                    <div id="editLitigationFields" style="display: none;">
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Pre-suit Offer</label>
                                <input type="number" id="editPresuitOffer" class="m-input" step="0.01" min="0" onchange="calculateEditCommission()">
                            </div>
                            <div>
                                <label class="m-label">Resolution Type</label>
                                <select id="editResolutionType" class="m-input" onchange="calculateEditCommission()">
                                    <option value="">Select...</option>
                                    <option value="Ongoing Case">Ongoing Case</option>
                                    <option value="Demand Settled">Demand Settled</option>
                                    <optgroup label="33.33% Fee Rate">
                                        <option value="No Offer Settle">No Offer Settle</option>
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
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Assigned Date</label>
                            <input type="date" id="editAssignedDate" class="m-input">
                        </div>
                        <div>
                            <label class="m-label">Note</label>
                            <input type="text" id="editNote" class="m-input">
                        </div>
                    </div>

                    <!-- Deadline Extension Request Section -->
                    <div id="deadlineSection" class="m-section" style="display: none;">
                        <div class="m-section-title">Deadline Management</div>
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Current Deadline</label>
                                <input type="date" id="editCurrentDeadline" class="m-input calculated" disabled>
                            </div>
                            <div>
                                <label class="m-label">Days Remaining</label>
                                <input type="text" id="editDaysRemaining" class="m-input calculated" disabled>
                            </div>
                        </div>
                        <div id="pendingExtensionAlert" class="m-info-box" style="display: none; background: #fef3c7; border: 1px solid #f59e0b;">
                            <span>&#9203;</span>
                            <span>Deadline extension request pending approval</span>
                        </div>
                        <div id="deadlineExtensionForm" style="display: none;">
                            <div class="m-row cols-1">
                                <div>
                                    <label class="m-label">Requested New Deadline *</label>
                                    <input type="date" id="editRequestedDeadline" class="m-input">
                                </div>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <label class="m-label">Reason for Extension *</label>
                                <textarea id="editExtensionReason" rows="3" class="m-input" placeholder="Please explain why you need a deadline extension..."></textarea>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" class="m-btn m-btn-warning" onclick="submitDeadlineExtension()">Submit Extension Request</button>
                                <button type="button" class="m-btn m-btn-secondary" onclick="cancelDeadlineExtension()">Cancel</button>
                            </div>
                        </div>
                        <button type="button" id="requestExtensionBtn" class="m-btn m-btn-secondary" onclick="showDeadlineExtensionForm()" style="margin-top: 8px;">
                            Request Deadline Extension
                        </button>
                    </div>

                    <div class="m-commission-card" style="margin-top: 16px;">
                        <div class="m-commission-label">Commission</div>
                        <span id="editCommissionDisplay" class="m-commission-value">$0.00</span>
                    </div>
                </div>

                <div class="m-footer split">
                    <button type="button" class="m-btn m-btn-danger" onclick="deleteCaseFromModal()">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Delete
                    </button>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="m-btn m-btn-secondary" onclick="closeModal('editCaseModal')">Cancel</button>
                        <button type="submit" class="m-btn m-btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
