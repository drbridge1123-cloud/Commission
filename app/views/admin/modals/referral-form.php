        <!-- Admin Add/Edit Referral Modal -->
        <div id="adminReferralFormModal" class="modal-overlay" onclick="if(event.target === this) closeModal('adminReferralFormModal')">
            <div class="modal-content m-shell" onclick="event.stopPropagation()" style="max-width: 700px;">
                <div class="m-header">
                    <div class="m-header-title"><h2 id="adminRefFormTitle">New Referral</h2></div>
                    <button onclick="closeModal('adminReferralFormModal')" class="m-close">&times;</button>
                </div>
                <form id="adminReferralForm" onsubmit="saveAdminReferral(event)">
                    <input type="hidden" id="adminRefEditId">
                    <div class="m-body">
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Signed Date</label>
                                <input type="date" id="adminRefSignedDate" class="m-input" required>
                            </div>
                            <div>
                                <label class="m-label">File Number</label>
                                <input type="text" id="adminRefFileNumber" class="m-input" placeholder="e.g., 202398-260201">
                            </div>
                        </div>
                        <div class="m-row cols-1">
                            <div>
                                <label class="m-label">Client Name</label>
                                <input type="text" id="adminRefClientName" class="m-input" required placeholder="Last, First (+additional parties)">
                            </div>
                        </div>
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Date of Loss</label>
                                <input type="date" id="adminRefDateOfLoss" class="m-input">
                            </div>
                            <div>
                                <label class="m-label">Referred By</label>
                                <input type="text" id="adminRefReferredBy" class="m-input" placeholder="e.g., Dave/OK Chiro, Google">
                            </div>
                        </div>
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Referred to Provider</label>
                                <input type="text" id="adminRefProvider" class="m-input" placeholder="e.g., Lynnwood Chiro">
                            </div>
                            <div>
                                <label class="m-label">Referred to Body Shop</label>
                                <input type="text" id="adminRefBodyShop" class="m-input" placeholder="e.g., Total, DRP Shop">
                            </div>
                        </div>
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Referral Type</label>
                                <input type="text" id="adminRefType" class="m-input" placeholder="e.g., Pedestrian">
                            </div>
                            <div>
                                <label class="m-label">Case Manager</label>
                                <select id="adminRefCaseManager" class="m-input">
                                    <option value="">Select...</option>
                                </select>
                            </div>
                        </div>
                        <div class="m-row cols-1">
                            <div>
                                <label class="m-label">Remark</label>
                                <textarea id="adminRefRemark" class="m-input" rows="2" style="resize: vertical;" placeholder="Additional notes"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="m-footer">
                        <button type="button" onclick="closeModal('adminReferralFormModal')" class="m-btn m-btn-secondary">Cancel</button>
                        <button type="submit" class="m-btn m-btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
