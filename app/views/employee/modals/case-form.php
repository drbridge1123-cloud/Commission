    <!-- Add/Edit Modal -->
    <div id="caseModal" class="modal-overlay" onclick="if(event.target === this) closeCaseModal()">
        <div class="modal-content m-shell" onclick="event.stopPropagation()" style="max-width: 680px;">
            <div class="m-header">
                <div class="m-header-title"><h2 id="modalTitle">Add New Case</h2></div>
                <button onclick="closeCaseModal()" class="m-close">&times;</button>
            </div>

            <form id="caseForm" data-mode="create" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                <input type="hidden" id="caseId">

                <div class="m-body">
                    <!-- Row 1: Client Name & Case Number -->
                    <div class="m-row cols-wide">
                        <div>
                            <label class="m-label">Client Name</label>
                            <input type="text" id="clientName" required class="m-input">
                        </div>
                        <div>
                            <label class="m-label">Case Number</label>
                            <input type="text" id="caseNumber" required class="m-input">
                        </div>
                    </div>

                    <!-- Intake Date -->
                    <div id="intakeDateSection">
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Intake Date</label>
                                <input type="date" id="intakeDate" class="m-input">
                            </div>
                            <div></div>
                        </div>
                    </div>

                    <!-- Settlement Details Section (hidden in create mode) -->
                    <div id="settlementSection">
                        <!-- Row 2: Case Type & Resolution -->
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Case Type</label>
                                <select id="caseType" class="m-input">
                                    <option value="Auto">Auto</option>
                                    <option value="Pedestrian">Pedestrian</option>
                                    <option value="Motorcycle">Motorcycle</option>
                                    <option value="Bicycle">Bicycle</option>
                                    <option value="Dog Bite">Dog Bite</option>
                                    <option value="Premise Liability">Premise Liability</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="m-label">Resolution Type</label>
                                <select id="resolutionType" class="m-input">
                                    <option value="">Select...</option>
                                    <option value="Ongoing Case">Ongoing Case</option>
                                    <option value="Demand Settled">Demand Settled</option>
                                    <option value="No Offer Settle">No Offer Settle</option>
                                    <option value="File and Bump">File and Bump</option>
                                    <option value="Post Deposition Settle">Post Deposition Settle</option>
                                    <option value="Mediation">Mediation</option>
                                    <option value="Settled Post Arbitration">Settled Post Arbitration</option>
                                    <option value="Arbitration Award">Arbitration Award</option>
                                    <option value="Beasley">Beasley</option>
                                    <option value="Settlement Conference">Settlement Conference</option>
                                    <option value="Co-Counsel">Co-Counsel</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 3: Year, Month & Fee Rate -->
                        <div class="m-row cols-3">
                            <div>
                                <label class="m-label">Year</label>
                                <select id="caseYear" class="m-input"></select>
                            </div>
                            <div>
                                <label class="m-label">Month</label>
                                <select id="caseMonth" class="m-input"></select>
                            </div>
                            <div>
                                <label class="m-label">Fee Rate</label>
                                <select id="feeRate" onchange="calculateFees()" class="m-input">
                                    <option value="33.33">1/3 (33.33%)</option>
                                    <option value="40">40%</option>
                                </select>
                            </div>
                        </div>

                        <!-- Financial Details Section -->
                        <div class="m-financial-card">
                            <div class="m-financial-card-header">
                                <div class="m-financial-card-icon">$</div>
                                <span style="font-size: 12px; font-weight: 600; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">Financial Details</span>
                            </div>

                            <!-- Settled & Pre-Suit -->
                            <div class="m-row cols-2">
                                <div>
                                    <label class="m-label">Settled Amount</label>
                                    <input type="number" step="0.01" id="settled" onchange="calculateFees()" class="m-input">
                                </div>
                                <div>
                                    <label class="m-label">Pre-Suit Offer</label>
                                    <input type="number" step="0.01" id="presuitOffer" onchange="calculateFees()" value="0" class="m-input">
                                </div>
                            </div>

                            <!-- Calculated fields -->
                            <div class="m-row cols-3">
                                <div>
                                    <label class="m-label">Difference</label>
                                    <input type="text" id="difference" readonly class="m-input calculated">
                                </div>
                                <div>
                                    <label class="m-label">Legal Fee</label>
                                    <input type="text" id="legalFee" readonly class="m-input calculated">
                                </div>
                                <div>
                                    <label class="m-label">Disc. Legal Fee</label>
                                    <input type="number" step="0.01" id="discountedLegalFee" onchange="calculateCommission()" class="m-input">
                                </div>
                            </div>

                            <!-- Commission Card -->
                            <div class="m-commission-card">
                                <span class="m-commission-label" id="commissionLabel">Your Commission (<?= $user['commission_rate'] ?>%)</span>
                                <span id="commission" class="m-commission-value">$0.00</span>
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="m-row cols-1" style="margin-top: 14px;">
                            <div>
                                <label class="m-label">Note</label>
                                <textarea id="note" rows="2" class="m-input" style="resize: vertical;"></textarea>
                            </div>
                        </div>

                        <!-- Check Received -->
                        <div class="m-checkbox-row">
                            <input type="checkbox" id="checkReceived">
                            <label for="checkReceived">Check Received</label>
                        </div>
                        <?php if (!$user['is_attorney']): ?>
                        <div class="m-checkbox-row">
                            <input type="checkbox" id="isMarketing" onchange="calculateCommission()">
                            <label for="isMarketing">Marketing Case (5%)</label>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Footer -->
                <div class="m-footer">
                    <button type="button" onclick="closeCaseModal()" class="m-btn m-btn-secondary">Cancel</button>
                    <button type="submit" class="m-btn m-btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
