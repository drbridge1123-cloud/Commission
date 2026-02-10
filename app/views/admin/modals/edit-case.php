        <!-- Edit Case Modal -->
        <div id="editCaseModal" class="modal-overlay" onclick="if(event.target === this) closeEditModal()">
            <div class="modal-content m-shell" onclick="event.stopPropagation()" style="max-width: 680px;">
                <div class="m-header">
                    <div class="m-header-title"><h2>Edit Case</h2></div>
                    <button onclick="closeEditModal()" class="m-close">&times;</button>
                </div>

                <form id="editCaseForm" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                    <input type="hidden" id="editCaseId">

                    <div class="m-body">
                        <!-- Row 1: Client Name & Case Number -->
                        <div class="m-row cols-wide">
                            <div>
                                <label class="m-label">Client Name</label>
                                <input type="text" id="editClientName" required class="m-input">
                            </div>
                            <div>
                                <label class="m-label">Case Number</label>
                                <input type="text" id="editCaseNumber" required class="m-input">
                            </div>
                        </div>

                        <!-- Row 2: Case Type & Resolution -->
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Case Type</label>
                                <select id="editCaseType" class="m-input">
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
                                <label class="m-label">Resolution</label>
                                <select id="editResolutionType" class="m-input">
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

                        <!-- Row 3: Month, Fee Rate & Status -->
                        <div class="m-row cols-3">
                            <div>
                                <label class="m-label">Month</label>
                                <select id="editMonth" class="m-input"></select>
                            </div>
                            <div>
                                <label class="m-label">Fee Rate</label>
                                <select id="editFeeRate" class="m-input">
                                    <option value="33.33">1/3 (33.33%)</option>
                                    <option value="40">40%</option>
                                </select>
                            </div>
                            <div>
                                <label class="m-label">Status</label>
                                <select id="editStatus" class="m-input">
                                    <option value="in_progress">In Progress</option>
                                    <option value="unpaid">Unpaid</option>
                                    <option value="paid">Paid</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>

                        <!-- Financial Details Section -->
                        <div class="m-financial-card">
                            <div class="m-financial-card-header">
                                <div class="m-financial-card-icon">$</div>
                                <span style="font-size: 12px; font-weight: 600; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">Financial Details</span>
                            </div>

                            <!-- Row: Settled & Pre-Suit -->
                            <div class="m-row cols-2">
                                <div>
                                    <label class="m-label">Settled Amount</label>
                                    <input type="number" step="0.01" id="editSettled" required class="m-input">
                                </div>
                                <div>
                                    <label class="m-label">Pre-Suit Offer</label>
                                    <input type="number" step="0.01" id="editPresuitOffer" class="m-input">
                                </div>
                            </div>

                            <!-- Row: Calculated fields -->
                            <div class="m-row cols-3">
                                <div>
                                    <label class="m-label">Difference</label>
                                    <input type="text" id="editDifference" readonly class="m-input calculated">
                                </div>
                                <div>
                                    <label class="m-label">Legal Fee</label>
                                    <input type="text" id="editLegalFee" readonly class="m-input calculated">
                                </div>
                                <div>
                                    <label class="m-label">Disc. Legal Fee</label>
                                    <input type="number" step="0.01" id="editDiscountedLegalFee" class="m-input">
                                </div>
                            </div>

                            <!-- Commission Card -->
                            <div class="m-commission-card">
                                <span class="m-commission-label">Commission</span>
                                <span class="m-commission-value" id="editCommission">$0.00</span>
                            </div>
                        </div>

                        <!-- Note & Check Received -->
                        <div style="display: flex; gap: 12px; align-items: flex-end; margin-top: 14px;">
                            <div style="flex: 1;">
                                <label class="m-label">Note</label>
                                <input type="text" id="editNote" class="m-input" placeholder="Optional note...">
                            </div>
                            <div class="m-checkbox-row" style="border-top: none; margin-top: 0; padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                                <input type="checkbox" id="editCheckReceived">
                                <label for="editCheckReceived">Check Received</label>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="m-footer split">
                        <button type="button" onclick="deleteFromEditModal()" class="m-btn m-btn-danger">Delete</button>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" onclick="closeEditModal()" class="m-btn m-btn-secondary">Cancel</button>
                            <button type="submit" class="m-btn m-btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
