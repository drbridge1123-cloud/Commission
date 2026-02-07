    <!-- Add/Edit Modal -->
    <div id="caseModal" class="modal-overlay" onclick="if(event.target === this) closeModal()">
        <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 680px; max-height: 90vh; border-radius: 12px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.12); overflow: hidden; display: flex; flex-direction: column; padding: 0;">
            <!-- Blue Header -->
            <div style="background: #18181b; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 3px; height: 18px; background: #22d3ee; border-radius: 2px;"></div>
                    <h2 id="modalTitle" style="font-size: 15px; font-weight: 600; color: white; margin: 0;">Add New Case</h2>
                </div>
                <button onclick="closeModal()" style="width: 28px; height: 28px; background: rgba(255,255,255,0.15); border: none; border-radius: 6px; color: rgba(255,255,255,0.9); font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
            </div>

            <form id="caseForm" data-mode="create" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                <input type="hidden" id="caseId">

                <!-- Content Area (Scrollable) -->
                <div style="padding: 16px 20px; overflow-y: auto; flex: 1;">
                    <!-- Row 1: Client Name & Case Number -->
                    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Client Name</label>
                            <input type="text" id="clientName" required style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; outline: none;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Case Number</label>
                            <input type="text" id="caseNumber" required style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; outline: none;">
                        </div>
                    </div>

                    <!-- Intake Date -->
                    <div id="intakeDateSection" style="margin-bottom: 12px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Intake Date</label>
                                <input type="date" id="intakeDate" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; outline: none;">
                            </div>
                            <div></div>
                        </div>
                    </div>

                    <!-- Settlement Details Section (hidden in create mode) -->
                    <div id="settlementSection">
                        <!-- Row 2: Case Type & Resolution -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Case Type</label>
                                <select id="caseType" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;">
                                    <option value="Auto Accident">Auto Accident</option>
                                    <option value="Pedestrian">Pedestrian</option>
                                    <option value="Motorcycle">Motorcycle</option>
                                    <option value="Bicycle">Bicycle</option>
                                    <option value="Dog Bite">Dog Bite</option>
                                    <option value="Premise Liability">Premise Liability</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Resolution Type</label>
                                <select id="resolutionType" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;">
                                    <option value="No Offer Settle">No Offer Settle</option>
                                    <option value="File and Bump">File and Bump</option>
                                    <option value="Post Deposition Settle">Post Deposition Settle</option>
                                    <option value="Mediation">Mediation</option>
                                    <option value="Settled Post Arbitration">Settled Post Arbitration</option>
                                    <option value="Arbitration Award">Arbitration Award</option>
                                    <option value="Beasley">Beasley</option>
                                    <option value="Settlement Conference">Settlement Conference</option>
                                    <option value="Non Litigation">Non Litigation</option>
                                    <option value="Co-Counsel">Co-Counsel</option>
                                    <option value="Ongoing Case">Ongoing Case</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 3: Year, Month & Fee Rate -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Year</label>
                                <select id="caseYear" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;"></select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Month</label>
                                <select id="caseMonth" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;"></select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Fee Rate</label>
                                <select id="feeRate" onchange="calculateFees()" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;">
                                    <option value="33.33">1/3 (33.33%)</option>
                                    <option value="40">40%</option>
                                </select>
                            </div>
                        </div>

                        <!-- Financial Details Section -->
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                                <div style="width: 26px; height: 26px; background: #0f4c81; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: white;">$</div>
                                <span style="font-size: 12px; font-weight: 600; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">Financial Details</span>
                            </div>

                            <!-- Settled & Pre-Suit -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Settled Amount</label>
                                    <input type="number" step="0.01" id="settled" onchange="calculateFees()" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Pre-Suit Offer</label>
                                    <input type="number" step="0.01" id="presuitOffer" onchange="calculateFees()" value="0" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none;">
                                </div>
                            </div>

                            <!-- Calculated fields -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Difference</label>
                                    <input type="text" id="difference" readonly style="width: 100%; padding: 10px 12px; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 13px; color: #64748b; background: white; outline: none;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Legal Fee</label>
                                    <input type="text" id="legalFee" readonly style="width: 100%; padding: 10px 12px; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 13px; color: #64748b; background: white; outline: none;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Disc. Legal Fee</label>
                                    <input type="number" step="0.01" id="discountedLegalFee" onchange="calculateCommission()" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none;">
                                </div>
                            </div>

                            <!-- Commission Card -->
                            <div style="background: #18181b; border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 12px; font-weight: 500; color: rgba(255,255,255,0.9);">Your Commission (<?= $user['commission_rate'] ?>%)</span>
                                <span id="commission" style="font-size: 22px; font-weight: 700; color: #22d3ee;">$0.00</span>
                            </div>
                        </div>

                        <!-- Note -->
                        <div style="margin-bottom: 12px;">
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Note</label>
                            <textarea id="note" rows="2" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; outline: none; resize: vertical;"></textarea>
                        </div>

                        <!-- Check Received -->
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" id="checkReceived" style="width: 16px; height: 16px; cursor: pointer;">
                            <label for="checkReceived" style="font-size: 13px; color: #374151; cursor: pointer;">Check Received</label>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div style="background: #f8fafc; padding: 12px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; align-items: center; flex-shrink: 0; gap: 10px;">
                    <button type="button" onclick="closeModal()" style="padding: 9px 16px; background: white; color: #64748b; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;">Cancel</button>
                    <button type="submit" style="padding: 9px 20px; background: #18181b; color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">Submit</button>
                </div>
            </form>
        </div>
    </div>
