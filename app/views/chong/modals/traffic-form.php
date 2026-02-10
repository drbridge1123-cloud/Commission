    <!-- Traffic Case Modal — Ink Minimal Accordion -->
    <div id="trafficModal" class="hidden" onclick="if(event.target===this) closeModal('trafficModal')">
        <div class="tem-modal" onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="tem-header">
                <div class="tem-header-left">
                    <div class="tem-accent-bar"></div>
                    <span class="tem-header-title" id="trafficModalTitle">Edit Traffic Case</span>
                </div>
                <button type="button" class="tem-close" onclick="closeModal('trafficModal')">&#10005;</button>
            </div>

            <form id="trafficForm" onsubmit="submitTrafficCase(event)">
                <input type="hidden" id="trafficCaseId">

                <div class="tem-body">
                    <!-- Section 1: Client -->
                    <div class="tem-section open">
                        <div class="tem-section-trigger" onclick="toggleTEMSection(this)">
                            <div class="tem-section-left">
                                <span class="tem-section-tag">CLIENT</span>
                                <span class="tem-section-count">2 fields</span>
                            </div>
                            <svg class="tem-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                        </div>
                        <div class="tem-section-body">
                            <div class="tem-grid">
                                <div class="tem-cell">
                                    <label class="tem-label">Client Name *</label>
                                    <input type="text" id="trafficClientName" class="tem-input" required>
                                </div>
                                <div class="tem-cell">
                                    <label class="tem-label">Phone</label>
                                    <input type="text" id="trafficClientPhone" class="tem-input">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Case Details -->
                    <div class="tem-section open">
                        <div class="tem-section-trigger" onclick="toggleTEMSection(this)">
                            <div class="tem-section-left">
                                <span class="tem-section-tag">CASE DETAILS</span>
                                <span class="tem-section-count">6 fields</span>
                            </div>
                            <svg class="tem-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                        </div>
                        <div class="tem-section-body">
                            <div class="tem-grid">
                                <div class="tem-cell alt">
                                    <label class="tem-label">Court</label>
                                    <select id="trafficCourt" class="tem-input tem-select">
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
                                        <option value="Issaquah">Issaquah</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="tem-cell alt">
                                    <label class="tem-label">Court Date</label>
                                    <input type="datetime-local" id="trafficCourtDate" class="tem-input">
                                </div>
                                <div class="tem-cell">
                                    <label class="tem-label">Charge</label>
                                    <select id="trafficCharge" class="tem-input tem-select">
                                        <option value="">Select Charge</option>
                                        <option value="speeding">Speeding</option>
                                        <option value="Speeding 5mph over">Speeding 5mph over</option>
                                        <option value="phone while driving">Phone While Driving</option>
                                        <option value="inattentive driving">Inattentive Driving</option>
                                        <option value="fail to obey traffic device">Fail to Obey Traffic Device</option>
                                        <option value="HOV violation">HOV Violation</option>
                                        <option value="seatbelt">Seatbelt</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="tem-cell">
                                    <label class="tem-label">Case Number</label>
                                    <input type="text" id="trafficCaseNumber" class="tem-input">
                                </div>
                                <div class="tem-cell alt">
                                    <label class="tem-label">Issued Date</label>
                                    <input type="date" id="trafficIssuedDate" class="tem-input">
                                </div>
                                <div class="tem-cell alt">
                                    <label class="tem-label">Prosecutor Offer</label>
                                    <input type="text" id="trafficOffer" class="tem-input">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Resolution -->
                    <div class="tem-section open">
                        <div class="tem-section-trigger" onclick="toggleTEMSection(this)">
                            <div class="tem-section-left">
                                <span class="tem-section-tag">RESOLUTION</span>
                                <span class="tem-section-count">4 fields + 2 checks</span>
                            </div>
                            <svg class="tem-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                        </div>
                        <div class="tem-section-body">
                            <div class="tem-grid">
                                <div class="tem-cell">
                                    <label class="tem-label">Disposition</label>
                                    <select id="trafficDisposition" class="tem-input tem-select" onchange="updateTrafficCommission()">
                                        <option value="pending">Pending</option>
                                        <option value="dismissed">Dismissed ($150)</option>
                                        <option value="amended">Amended ($100)</option>
                                        <option value="other">Other ($0)</option>
                                    </select>
                                </div>
                                <div class="tem-cell">
                                    <label class="tem-label">Status</label>
                                    <select id="trafficStatus" class="tem-input tem-select">
                                        <option value="active">Active</option>
                                        <option value="resolved">Resolved</option>
                                    </select>
                                </div>
                                <div class="tem-cell alt">
                                    <label class="tem-label">Requester</label>
                                    <select id="trafficReferralSource" class="tem-input tem-select">
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
                                <div class="tem-cell alt">
                                    <label class="tem-label">NOA Sent Date</label>
                                    <input type="date" id="trafficNoaSentDate" class="tem-input">
                                </div>
                            </div>
                            <div class="tem-checkbox-grid">
                                <div class="tem-checkbox-cell">
                                    <input type="checkbox" id="trafficDiscovery">
                                    <label for="trafficDiscovery">Discovery Received</label>
                                </div>
                                <div class="tem-checkbox-cell">
                                    <input type="checkbox" id="trafficPaid">
                                    <label for="trafficPaid">Commission Paid</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Commission Strip -->
                    <div class="tem-commission-strip">
                        <span class="tem-commission-label">Commission</span>
                        <span class="tem-commission-value" id="trafficCommissionDisplay">$0.00</span>
                    </div>

                    <!-- Section 4: Notes & Attachments -->
                    <div class="tem-section open">
                        <div class="tem-section-trigger" onclick="toggleTEMSection(this)">
                            <div class="tem-section-left">
                                <span class="tem-section-tag">NOTES</span>
                                <span class="tem-section-count">note + attachments</span>
                            </div>
                            <svg class="tem-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                        </div>
                        <div class="tem-section-body">
                            <div class="tem-grid">
                                <div class="tem-cell full">
                                    <label class="tem-label">Note</label>
                                    <textarea id="trafficNote" class="tem-textarea" rows="2"></textarea>
                                </div>
                            </div>
                            <!-- Attachments (only shown when editing) -->
                            <div id="trafficFilesSection" style="display: none;">
                                <div class="tem-attachments">
                                    <div class="tem-attach-left">
                                        Attachments <span class="tem-attach-muted" id="trafficFilesCount">&mdash; No files attached</span>
                                    </div>
                                    <label class="tem-btn-upload">
                                        + Upload
                                        <input type="file" id="trafficFileInput" style="display: none;" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif" onchange="uploadTrafficFile(this)">
                                    </label>
                                </div>
                                <div class="tem-files-list" id="trafficFilesList"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tem-footer">
                    <button type="button" class="tem-btn-delete" id="trafficDeleteBtn" style="display:none;" onclick="deleteTrafficCaseFromModal()">Delete</button>
                    <button type="button" class="tem-btn-cancel" onclick="closeModal('trafficModal')">Cancel</button>
                    <button type="submit" class="tem-btn-save">Save Case</button>
                </div>
            </form>
        </div>
    </div>
