    <!-- Traffic Case Modal -->
    <div id="trafficModal" class="modal-overlay" onclick="if(event.target === this) closeTrafficModal()">
        <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 600px; max-height: 90vh; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; padding: 0;">
            <!-- Header -->
            <div style="background: #18181b; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 3px; height: 18px; background: #22d3ee; border-radius: 2px;"></div>
                    <h2 id="trafficModalTitle" style="font-size: 15px; font-weight: 600; color: white; margin: 0;">Add Traffic Case</h2>
                </div>
                <button onclick="closeTrafficModal()" style="width: 28px; height: 28px; background: rgba(255,255,255,0.15); border: none; border-radius: 6px; color: rgba(255,255,255,0.9); font-size: 18px; cursor: pointer;">&times;</button>
            </div>

            <!-- Content -->
            <form id="trafficForm" style="padding: 16px 20px; overflow-y: auto; flex: 1;">
                <input type="hidden" id="trafficCaseId">

                <!-- Row 1: Client Name & Phone -->
                <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Client Name</label>
                        <input type="text" id="trafficClientName" required style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Phone</label>
                        <input type="text" id="trafficClientPhone" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>

                <!-- Row 2: Court & Court Date -->
                <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Court</label>
                        <select id="trafficCourt" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: white;">
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
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Court Date</label>
                        <input type="datetime-local" id="trafficCourtDate" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>

                <!-- Row 3: Charge & Case Number -->
                <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Charge</label>
                        <select id="trafficCharge" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: white;">
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
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Case Number</label>
                        <input type="text" id="trafficCaseNumber" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>

                <!-- Row 4: Prosecutor Offer -->
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Prosecutor Offer</label>
                    <input type="text" id="trafficOffer" placeholder="e.g., DDS1 and dismiss" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                </div>

                <!-- Row 5: Disposition & Status -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Disposition</label>
                        <select id="trafficDisposition" onchange="updateTrafficCommission()" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="pending">Pending</option>
                            <option value="dismissed">Dismissed ($150)</option>
                            <option value="amended">Amended ($100)</option>
                            <option value="other">Other ($0)</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Status</label>
                        <select id="trafficStatus" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="active">Active</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                </div>

                <!-- Row 6: NOA Sent Date & Discovery -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">NOA Sent Date</label>
                        <input type="date" id="trafficNoaSentDate" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div style="display: flex; align-items: center; padding-top: 16px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="trafficDiscovery" style="width: 18px; height: 18px; cursor: pointer;">
                            <span style="font-size: 13px; font-weight: 500; color: #374151;">Discovery Received</span>
                        </label>
                    </div>
                </div>

                <!-- Row 7: Referral Source & Paid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Referral Source</label>
                        <select id="trafficReferralSource" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: white;">
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
                    <div style="display: flex; align-items: center; padding-top: 16px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="trafficPaid" style="width: 18px; height: 18px; cursor: pointer;">
                            <span style="font-size: 13px; font-weight: 500; color: #374151;">Commission Paid</span>
                        </label>
                    </div>
                </div>

                <!-- Commission Display -->
                <div style="background: #18181b; border-radius: 8px; padding: 12px 16px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: rgba(255,255,255,0.8);">Commission</span>
                    <span id="trafficCommissionDisplay" style="font-size: 20px; font-weight: 700; color: #22d3ee;">$0.00</span>
                </div>

                <!-- Note -->
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Note</label>
                    <textarea id="trafficNote" rows="2" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; resize: vertical;"></textarea>
                </div>
            </form>

            <!-- Footer -->
            <div style="background: #f8fafc; padding: 12px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;">
                <button type="button" onclick="closeTrafficModal()" style="padding: 9px 14px; background: white; color: #64748b; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; cursor: pointer;">Cancel</button>
                <button type="button" onclick="saveTrafficCase()" style="padding: 9px 16px; background: #18181b; color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">Save</button>
            </div>
        </div>
    </div>
