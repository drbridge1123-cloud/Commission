        <!-- Edit Case Modal -->
        <div id="editCaseModal" class="modal-overlay" onclick="if(event.target === this) closeEditModal()">
            <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 680px; max-height: 90vh; border-radius: 12px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.12); overflow: hidden; display: flex; flex-direction: column;">
                <!-- Dark Header -->
                <div style="background: #0f172a; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 3px; height: 16px; background: #22d3ee; border-radius: 2px;"></div>
                        <h2 style="font-size: 14px; font-weight: 600; color: white; margin: 0;">Edit Case</h2>
                    </div>
                    <button onclick="closeEditModal()" style="width: 26px; height: 26px; background: rgba(255,255,255,0.1); border: none; border-radius: 6px; color: rgba(255,255,255,0.7); font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
                </div>

                <form id="editCaseForm" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                    <input type="hidden" id="editCaseId">

                    <!-- Content Area (Scrollable) -->
                    <div style="padding: 12px 20px; overflow-y: auto; flex: 1;">
                        <!-- Row 1: Client Name & Case Number -->
                        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Client Name</label>
                                <input type="text" id="editClientName" required style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; outline: none; transition: all 0.2s;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Case Number</label>
                                <input type="text" id="editCaseNumber" required style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; outline: none; transition: all 0.2s;">
                            </div>
                        </div>

                        <!-- Row 2: Case Type & Resolution -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Case Type</label>
                                <select id="editCaseType" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;">
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
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Resolution</label>
                                <select id="editResolutionType" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;">
                                    <option value="TBD">TBD</option>
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

                        <!-- Row 3: Month, Fee Rate & Status -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                            <div>
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Month</label>
                                <select id="editMonth" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;"></select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Fee Rate</label>
                                <select id="editFeeRate" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;">
                                    <option value="33.33">1/3 (33.33%)</option>
                                    <option value="40">40%</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Status</label>
                                <select id="editStatus" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer;">
                                    <option value="in_progress">In Progress</option>
                                    <option value="unpaid">Unpaid</option>
                                    <option value="paid">Paid</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>

                        <!-- Financial Details Section -->
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
                            <!-- Section Header -->
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                                <div style="width: 26px; height: 26px; background: #0f172a; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px;">💰</div>
                                <span style="font-size: 12px; font-weight: 600; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">Financial Details</span>
                            </div>

                            <!-- Row: Settled & Pre-Suit -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                <div>
                                    <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Settled Amount</label>
                                    <input type="number" step="0.01" id="editSettled" required style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Pre-Suit Offer</label>
                                    <input type="number" step="0.01" id="editPresuitOffer" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none;">
                                </div>
                            </div>

                            <!-- Row: Calculated fields -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                <div>
                                    <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Difference</label>
                                    <input type="text" id="editDifference" readonly style="width: 100%; padding: 8px 12px; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 13px; color: #64748b; background: #f8fafc; outline: none;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Legal Fee</label>
                                    <input type="text" id="editLegalFee" readonly style="width: 100%; padding: 8px 12px; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 13px; color: #64748b; background: #f8fafc; outline: none;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Disc. Legal Fee</label>
                                    <input type="number" step="0.01" id="editDiscountedLegalFee" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; background: white; outline: none;">
                                </div>
                            </div>

                            <!-- Commission Card -->
                            <div style="background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; position: relative; overflow: hidden;">
                                <div style="position: absolute; right: 0; top: 0; bottom: 0; width: 80px; background: linear-gradient(135deg, rgba(34, 211, 238, 0.1), rgba(168, 85, 247, 0.1));"></div>
                                <span style="font-size: 11px; font-weight: 500; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.5px;">Commission</span>
                                <span style="font-size: 22px; font-weight: 700; color: #22d3ee; position: relative; z-index: 1;" id="editCommission">$0.00</span>
                            </div>
                        </div>

                        <!-- Note & Check Received -->
                        <div style="display: flex; gap: 12px; align-items: flex-end;">
                            <div style="flex: 1;">
                                <label style="display: block; font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Note</label>
                                <input type="text" id="editNote" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; outline: none;" placeholder="Optional note...">
                            </div>
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; display: flex; align-items: center; gap: 6px;">
                                <input type="checkbox" id="editCheckReceived" style="width: 14px; height: 14px; accent-color: #0f172a; cursor: pointer;">
                                <label for="editCheckReceived" style="font-size: 12px; color: #374151; white-space: nowrap; cursor: pointer;">Check Received</label>
                            </div>
                        </div>
                    </div>

                    <!-- Footer (Fixed) -->
                    <div style="background: #f8fafc; padding: 10px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                        <button type="button" onclick="deleteFromEditModal()" style="padding: 7px 12px; background: transparent; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; font-size: 11px; font-weight: 500; cursor: pointer; transition: all 0.2s;">Delete</button>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" onclick="closeEditModal()" style="padding: 7px 12px; background: white; color: #64748b; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 11px; font-weight: 500; cursor: pointer; transition: all 0.2s;">Cancel</button>
                            <button type="submit" style="padding: 7px 16px; background: #0f172a; color: white; border: none; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.2s;">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>