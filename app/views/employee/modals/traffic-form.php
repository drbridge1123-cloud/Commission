    <!-- Traffic Case Modal -->
    <div id="trafficModal" class="modal-overlay" onclick="if(event.target === this) closeTrafficModal()">
        <div class="modal-content m-shell" onclick="event.stopPropagation()" style="max-width: 600px;">
            <div class="m-header">
                <div class="m-header-title"><h2 id="trafficModalTitle">Add Traffic Case</h2></div>
                <button onclick="closeTrafficModal()" class="m-close">&times;</button>
            </div>

            <form id="trafficForm">
                <input type="hidden" id="trafficCaseId">
                <div class="m-body">
                    <!-- Row 1: Client Name & Phone -->
                    <div class="m-row cols-wide">
                        <div>
                            <label class="m-label">Client Name</label>
                            <input type="text" id="trafficClientName" required class="m-input">
                        </div>
                        <div>
                            <label class="m-label">Phone</label>
                            <input type="text" id="trafficClientPhone" class="m-input">
                        </div>
                    </div>

                    <!-- Row 2: Court & Court Date -->
                    <div class="m-row cols-wide">
                        <div>
                            <label class="m-label">Court</label>
                            <select id="trafficCourt" class="m-input">
                                <option value="">Select Court</option>
                                <option value="KCDC - Issaquah">KCDC - Issaquah</option>
                                <option value="KCDC - Seattle">KCDC - Seattle</option>
                                <option value="KCDC - Shoreline">KCDC - Shoreline</option>
                                <option value="KCDC - Bellevue">KCDC - Bellevue</option>
                                <option value="KCDC - Burien">KCDC - Burien</option>
                                <option value="SCD - Everett">SCD - Everett</option>
                                <option value="SCD - South">SCD - South</option>
                                <option value="SCD - Cascade">SCD - Cascade</option>
                                <option value="SCD - Evergreen">SCD - Evergreen</option>
                                <option value="Lynnwood Muni">Lynnwood Muni</option>
                                <option value="Kent Municipal">Kent Municipal</option>
                                <option value="Mill Creek Violation Bureau">Mill Creek Violation Bureau</option>
                                <option value="Brier Violation Bureau">Brier Violation Bureau</option>
                                <option value="Issaquah Muni">Issaquah Muni</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="m-label">Court Date</label>
                            <input type="datetime-local" id="trafficCourtDate" class="m-input">
                        </div>
                    </div>

                    <!-- Row 3: Charge & Case Number -->
                    <div class="m-row cols-wide">
                        <div>
                            <label class="m-label">Charge</label>
                            <select id="trafficCharge" class="m-input">
                                <option value="">Select Charge</option>
                                <option value="speeding">Speeding</option>
                                <option value="phone while driving">Phone While Driving</option>
                                <option value="inattentive driving">Inattentive Driving</option>
                                <option value="fail to obey traffic device">Fail to Obey Traffic Device</option>
                                <option value="HOV violation">HOV Violation</option>
                                <option value="seatbelt">Seatbelt</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="m-label">Case Number</label>
                            <input type="text" id="trafficCaseNumber" class="m-input">
                        </div>
                    </div>

                    <!-- Row 4: Prosecutor Offer -->
                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Prosecutor Offer</label>
                            <input type="text" id="trafficOffer" placeholder="e.g., DDS1 and dismiss" class="m-input">
                        </div>
                    </div>

                    <!-- Row 5: Disposition & Status -->
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Disposition</label>
                            <select id="trafficDisposition" onchange="updateTrafficCommission()" class="m-input">
                                <option value="pending">Pending</option>
                                <option value="dismissed">Dismissed ($150)</option>
                                <option value="amended">Amended ($100)</option>
                                <option value="other">Other ($0)</option>
                            </select>
                        </div>
                        <div>
                            <label class="m-label">Status</label>
                            <select id="trafficStatus" class="m-input">
                                <option value="active">Active</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 6: NOA Sent Date & Discovery -->
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">NOA Sent Date</label>
                            <input type="date" id="trafficNoaSentDate" class="m-input">
                        </div>
                        <div class="m-checkbox-row" style="border-top: none; margin-top: 16px; padding: 0;">
                            <input type="checkbox" id="trafficDiscovery">
                            <label for="trafficDiscovery">Discovery Received</label>
                        </div>
                    </div>

                    <!-- Row 7: Referral Source & Paid -->
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Referral Source</label>
                            <select id="trafficReferralSource" class="m-input">
                                <option value="">Select Referral</option>
                                <option value="Daniel">Daniel</option>
                                <option value="Dave">Dave</option>
                                <option value="Soyong">Soyong</option>
                                <option value="Jimi">Jimi</option>
                                <option value="Chloe">Chloe</option>
                                <option value="Chong">Chong</option>
                                <option value="Ella">Ella</option>
                                <option value="Office">Office</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="m-checkbox-row" style="border-top: none; margin-top: 16px; padding: 0;">
                            <input type="checkbox" id="trafficPaid">
                            <label for="trafficPaid">Commission Paid</label>
                        </div>
                    </div>

                    <!-- Commission Display -->
                    <div class="m-commission-card">
                        <span class="m-commission-label">Commission</span>
                        <span id="trafficCommissionDisplay" class="m-commission-value">$0.00</span>
                    </div>

                    <!-- Note -->
                    <div class="m-row cols-1" style="margin-top: 12px;">
                        <div>
                            <label class="m-label">Note</label>
                            <textarea id="trafficNote" rows="2" class="m-input" style="resize: vertical;"></textarea>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Footer -->
            <div class="m-footer">
                <button type="button" onclick="closeTrafficModal()" class="m-btn m-btn-secondary">Cancel</button>
                <button type="button" onclick="saveTrafficCase()" class="m-btn m-btn-primary">Save</button>
            </div>
        </div>
    </div>
