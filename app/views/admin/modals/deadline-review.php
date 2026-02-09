        <!-- Deadline Request Review Modal -->
        <div id="deadlineReviewModal" class="modal-overlay hidden">
            <div class="modal-content m-shell" style="max-width: 550px;">
                <div class="m-header">
                    <div class="m-header-title"><h2 id="deadlineReviewTitle">Review Deadline Request</h2></div>
                    <button class="m-close" onclick="closeModal('deadlineReviewModal')">&times;</button>
                </div>
                <div class="m-body">
                    <input type="hidden" id="deadlineReviewId">

                    <div class="m-info-box" style="margin-bottom: 16px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <span class="m-label" style="margin-bottom: 2px;">Employee</span>
                                <div id="reviewRequesterName" style="font-weight: 600; font-size: 13px;"></div>
                            </div>
                            <div>
                                <span class="m-label" style="margin-bottom: 2px;">Case</span>
                                <div id="reviewCaseInfo" style="font-weight: 600; font-size: 13px;"></div>
                            </div>
                            <div>
                                <span class="m-label" style="margin-bottom: 2px;">Current Deadline</span>
                                <div id="reviewCurrentDeadline" style="font-weight: 600; font-size: 13px;"></div>
                            </div>
                            <div>
                                <span class="m-label" style="margin-bottom: 2px;">Requested Deadline</span>
                                <div id="reviewRequestedDeadline" style="font-weight: 600; font-size: 13px; color: #059669;"></div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label class="m-label">Reason for Extension</label>
                        <div id="reviewReason" style="background: #fefce8; padding: 12px; border-radius: 8px; margin-top: 4px; white-space: pre-wrap; font-size: 13px;"></div>
                    </div>

                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Admin Note (Optional)</label>
                            <textarea id="deadlineAdminNote" rows="2" class="m-input" placeholder="Add a note for the employee..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="m-footer split">
                    <button type="button" class="m-btn m-btn-secondary" onclick="closeModal('deadlineReviewModal')">Cancel</button>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="m-btn m-btn-danger" style="background: #dc2626; color: white; border: none;" onclick="processDeadlineRequest('reject')">Reject</button>
                        <button type="button" class="m-btn m-btn-primary" onclick="processDeadlineRequest('approve')">Approve</button>
                    </div>
                </div>
            </div>
        </div>
