    <!-- Traffic Case Modal -->
    <div id="trafficModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 id="trafficModalTitle">Add Traffic Case</h2>
                <button class="modal-close" onclick="closeModal('trafficModal')">&times;</button>
            </div>
            <form id="trafficForm" onsubmit="submitTrafficCase(event)">
                <input type="hidden" id="trafficCaseId">

                <div class="form-row">
                    <div class="form-group">
                        <label>Client Name *</label>
                        <input type="text" id="trafficClientName" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" id="trafficClientPhone">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Court</label>
                        <select id="trafficCourt">
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
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Court Date</label>
                        <input type="datetime-local" id="trafficCourtDate">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Charge</label>
                        <select id="trafficCharge">
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
                    <div class="form-group">
                        <label>Case Number</label>
                        <input type="text" id="trafficCaseNumber">
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label>Prosecutor Offer</label>
                        <input type="text" id="trafficOffer" placeholder="e.g., DDS1 and dismiss">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Disposition</label>
                        <select id="trafficDisposition" onchange="updateTrafficCommission()">
                            <option value="pending">Pending</option>
                            <option value="dismissed">Dismissed ($150)</option>
                            <option value="amended">Amended ($100)</option>
                            <option value="other">Other ($0)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="trafficStatus">
                            <option value="active">Active</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Requester</label>
                        <select id="trafficReferralSource">
                            <option value="">Select Requester</option>
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
                    <div class="form-group">
                        <label>NOA Sent Date</label>
                        <input type="date" id="trafficNoaSentDate">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><input type="checkbox" id="trafficDiscovery"> Discovery Received</label>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" id="trafficPaid"> Commission Paid</label>
                    </div>
                </div>

                <div style="background: #f3f4f6; padding: 12px 16px; border-radius: 8px; margin: 16px 0; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600;">Commission:</span>
                    <span id="trafficCommissionDisplay" style="font-size: 20px; font-weight: 700; color: #059669;">$0.00</span>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label>Note</label>
                        <textarea id="trafficNote" rows="2" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;"></textarea>
                    </div>
                </div>

                <!-- File Attachments Section (only shown when editing) -->
                <div id="trafficFilesSection" style="display: none; margin: 16px 0; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                    <div style="background: #f9fafb; padding: 10px 16px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 600; font-size: 14px;">Attachments</span>
                        <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: #2563eb; color: white; border-radius: 6px; font-size: 12px; font-weight: 500;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Upload
                            <input type="file" id="trafficFileInput" style="display: none;" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif" onchange="uploadTrafficFile(this)">
                        </label>
                    </div>
                    <div id="trafficFilesList" style="max-height: 200px; overflow-y: auto;">
                        <div style="padding: 16px; text-align: center; color: #9ca3af; font-size: 13px;">No files attached</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('trafficModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Case</button>
                </div>
            </form>
        </div>
    </div>
