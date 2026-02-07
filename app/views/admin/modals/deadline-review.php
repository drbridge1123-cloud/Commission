        <!-- Deadline Request Review Modal -->
        <div id="deadlineReviewModal" class="modal-overlay hidden">
            <div class="modal-content" style="max-width: 550px;">
                <div class="modal-header">
                    <h2 id="deadlineReviewTitle">Review Deadline Request</h2>
                    <button class="modal-close" onclick="closeModal('deadlineReviewModal')">&times;</button>
                </div>
                <div class="modal-body" style="padding: 24px;">
                    <input type="hidden" id="deadlineReviewId">

                    <div style="background: #f3f4f6; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <span style="font-size: 12px; color: #6b7280;">Employee</span>
                                <div id="reviewRequesterName" style="font-weight: 600;"></div>
                            </div>
                            <div>
                                <span style="font-size: 12px; color: #6b7280;">Case</span>
                                <div id="reviewCaseInfo" style="font-weight: 600;"></div>
                            </div>
                            <div>
                                <span style="font-size: 12px; color: #6b7280;">Current Deadline</span>
                                <div id="reviewCurrentDeadline" style="font-weight: 600;"></div>
                            </div>
                            <div>
                                <span style="font-size: 12px; color: #6b7280;">Requested Deadline</span>
                                <div id="reviewRequestedDeadline" style="font-weight: 600; color: #059669;"></div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <span style="font-size: 12px; color: #6b7280;">Reason for Extension</span>
                        <div id="reviewReason" style="background: #fefce8; padding: 12px; border-radius: 8px; margin-top: 4px; white-space: pre-wrap;"></div>
                    </div>

                    <div class="form-group">
                        <label>Admin Note (Optional)</label>
                        <textarea id="deadlineAdminNote" rows="2" placeholder="Add a note for the employee..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="display: flex; justify-content: space-between;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deadlineReviewModal')">Cancel</button>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn btn-danger" onclick="processDeadlineRequest('reject')">Reject</button>
                        <button type="button" class="btn btn-primary" onclick="processDeadlineRequest('approve')">Approve</button>
                    </div>
                </div>
            </div>
        </div>