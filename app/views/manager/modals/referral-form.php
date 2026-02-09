        <!-- Add/Edit Referral Modal -->
        <div id="referralFormModal" class="modal-overlay" onclick="if(event.target === this) closeModal('referralFormModal')">
            <div class="modal-content m-shell" onclick="event.stopPropagation()" style="max-width: 700px;">
                <div class="m-header">
                    <div class="m-header-title"><h2 id="referralFormTitle">New Referral</h2></div>
                    <button onclick="closeModal('referralFormModal')" class="m-close">&times;</button>
                </div>
                <form id="referralForm" onsubmit="saveReferral(event)">
                    <input type="hidden" id="refEditId">
                    <div class="m-body">
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Signed Date</label>
                                <input type="date" id="refSignedDate" class="m-input" required>
                            </div>
                            <div>
                                <label class="m-label">File Number</label>
                                <input type="text" id="refFileNumber" class="m-input" placeholder="e.g., 202398-260201">
                            </div>
                        </div>
                        <div class="m-row cols-1">
                            <div>
                                <label class="m-label">Client Name</label>
                                <input type="text" id="refClientName" class="m-input" required placeholder="Last, First (+additional parties)">
                            </div>
                        </div>
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Date of Loss</label>
                                <input type="date" id="refDateOfLoss" class="m-input">
                            </div>
                            <div>
                                <label class="m-label">Referred By</label>
                                <input type="text" id="refReferredBy" class="m-input" placeholder="e.g., Dave/OK Chiro, Google">
                            </div>
                        </div>
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Referred to Provider</label>
                                <input type="text" id="refProvider" class="m-input" placeholder="e.g., Lynnwood Chiro">
                            </div>
                            <div>
                                <label class="m-label">Referred to Body Shop</label>
                                <input type="text" id="refBodyShop" class="m-input" placeholder="e.g., Total, DRP Shop">
                            </div>
                        </div>
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Referral Type</label>
                                <input type="text" id="refType" class="m-input" placeholder="e.g., Pedestrian">
                            </div>
                            <div>
                                <label class="m-label">Case Manager</label>
                                <select id="refCaseManager" class="m-input">
                                    <option value="">Select...</option>
                                    <!-- Populated by JS from active users -->
                                </select>
                            </div>
                        </div>
                        <div class="m-row cols-1">
                            <div>
                                <label class="m-label">Remark</label>
                                <textarea id="refRemark" class="m-input" rows="2" style="resize: vertical;" placeholder="Additional notes"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="m-footer">
                        <button type="button" onclick="closeModal('referralFormModal')" class="m-btn m-btn-secondary">Cancel</button>
                        <button type="submit" class="m-btn m-btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
