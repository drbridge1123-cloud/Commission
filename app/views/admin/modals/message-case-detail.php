        <!-- Case Detail Modal (For Messages Tab) -->
        <div id="messageCaseDetailModal" class="modal-overlay">
            <div class="modal-content" style="max-width: 700px;">
                <div class="modal-header">
                    <h3 class="modal-title">Case Details</h3>
                    <button onclick="closeCaseDetailAdmin()" class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <!-- Status Badge -->
                        <div style="text-center; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;">
                            <span id="adminDetailStatusBadge" class="status-badge"></span>
                        </div>

                        <!-- Case Information Grid -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Case Number</label>
                                <div id="adminDetailCaseNumber" style="font-size: 15px; font-weight: 500; color: #111827; margin-top: 4px;"></div>
                            </div>
                            <div>
                                <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Client Name</label>
                                <div id="adminDetailClientName" style="font-size: 15px; font-weight: 500; color: #111827; margin-top: 4px;"></div>
                            </div>
                            <div>
                                <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Employee</label>
                                <div id="adminDetailCounsel" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                            </div>
                            <div>
                                <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Case Type</label>
                                <div id="adminDetailCaseType" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                            </div>
                            <div>
                                <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Resolution Type</label>
                                <div id="adminDetailResolutionType" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                            </div>
                            <div>
                                <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Month</label>
                                <div id="adminDetailMonth" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                            </div>
                            <div>
                                <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Fee Rate</label>
                                <div id="adminDetailFeeRate" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div style="padding-top: 16px; border-top: 1px solid #e5e7eb;">
                            <h3 style="font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 12px;">Financial Details</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div>
                                    <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Settled Amount</label>
                                    <div id="adminDetailSettled" style="font-size: 18px; font-weight: 700; color: #111827; margin-top: 4px;"></div>
                                </div>
                                <div>
                                    <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Presuit Offer</label>
                                    <div id="adminDetailPresuitOffer" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                                </div>
                                <div>
                                    <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Difference</label>
                                    <div id="adminDetailDifference" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                                </div>
                                <div>
                                    <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Legal Fee</label>
                                    <div id="adminDetailLegalFee" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                                </div>
                                <div>
                                    <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Discounted Legal Fee</label>
                                    <div id="adminDetailDiscountedLegalFee" style="font-size: 15px; color: #111827; margin-top: 4px;"></div>
                                </div>
                                <div>
                                    <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Commission</label>
                                    <div id="adminDetailCommission" style="font-size: 18px; font-weight: 700; color: #059669; margin-top: 4px;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Note -->
                        <div style="padding-top: 16px; border-top: 1px solid #e5e7eb;" id="adminDetailNoteSection">
                            <label style="font-size: 13px; font-weight: 600; color: #6b7280;">Note</label>
                            <div id="adminDetailNote" style="font-size: 14px; color: #374151; margin-top: 8px; padding: 12px; background: #f9fafb; border-radius: 8px;"></div>
                        </div>

                        <!-- Dates -->
                        <div style="padding-top: 16px; border-top: 1px solid #e5e7eb;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 13px;">
                                <div>
                                    <span style="color: #6b7280;">Submitted:</span>
                                    <span id="adminDetailSubmittedAt" style="color: #111827; margin-left: 8px;"></span>
                                </div>
                                <div id="adminDetailReviewedSection">
                                    <span style="color: #6b7280;">Reviewed:</span>
                                    <span id="adminDetailReviewedAt" style="color: #111827; margin-left: 8px;"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-actions" style="margin-top: 24px;">
                        <button onclick="closeCaseDetailAdmin()" class="btn-secondary">Close</button>
                    </div>
                </div>
            </div>
        </div>