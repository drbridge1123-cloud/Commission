        <!-- Case Detail Modal (For Messages Tab) -->
        <div id="messageCaseDetailModal" class="modal-overlay">
            <div class="modal-content m-shell" style="max-width: 700px;">
                <div class="m-header">
                    <div class="m-header-title"><h3>Case Details</h3></div>
                    <button onclick="closeCaseDetailAdmin()" class="m-close">&times;</button>
                </div>
                <div class="m-body">
                    <!-- Status Badge -->
                    <div style="text-align: center; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb; margin-bottom: 16px;">
                        <span id="adminDetailStatusBadge" class="status-badge"></span>
                    </div>

                    <!-- Case Information Grid -->
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Case Number</label>
                            <div id="adminDetailCaseNumber" style="font-size: 14px; font-weight: 500; color: #0f172a; margin-top: 2px;"></div>
                        </div>
                        <div>
                            <label class="m-label">Client Name</label>
                            <div id="adminDetailClientName" style="font-size: 14px; font-weight: 500; color: #0f172a; margin-top: 2px;"></div>
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Employee</label>
                            <div id="adminDetailCounsel" style="font-size: 14px; color: #0f172a; margin-top: 2px;"></div>
                        </div>
                        <div>
                            <label class="m-label">Case Type</label>
                            <div id="adminDetailCaseType" style="font-size: 14px; color: #0f172a; margin-top: 2px;"></div>
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Resolution Type</label>
                            <div id="adminDetailResolutionType" style="font-size: 14px; color: #0f172a; margin-top: 2px;"></div>
                        </div>
                        <div>
                            <label class="m-label">Month</label>
                            <div id="adminDetailMonth" style="font-size: 14px; color: #0f172a; margin-top: 2px;"></div>
                        </div>
                    </div>
                    <div class="m-row cols-2" style="margin-bottom: 0;">
                        <div>
                            <label class="m-label">Fee Rate</label>
                            <div id="adminDetailFeeRate" style="font-size: 14px; color: #0f172a; margin-top: 2px;"></div>
                        </div>
                        <div></div>
                    </div>

                    <!-- Financial Information -->
                    <div class="m-financial-card" style="margin-top: 16px;">
                        <div class="m-financial-card-header">
                            <div class="m-financial-card-icon">$</div>
                            <span style="font-size: 12px; font-weight: 600; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">Financial Details</span>
                        </div>
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Settled Amount</label>
                                <div id="adminDetailSettled" style="font-size: 18px; font-weight: 700; color: #0f172a; margin-top: 2px;"></div>
                            </div>
                            <div>
                                <label class="m-label">Presuit Offer</label>
                                <div id="adminDetailPresuitOffer" style="font-size: 14px; color: #0f172a; margin-top: 2px;"></div>
                            </div>
                        </div>
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Difference</label>
                                <div id="adminDetailDifference" style="font-size: 14px; color: #0f172a; margin-top: 2px;"></div>
                            </div>
                            <div>
                                <label class="m-label">Legal Fee</label>
                                <div id="adminDetailLegalFee" style="font-size: 14px; color: #0f172a; margin-top: 2px;"></div>
                            </div>
                        </div>
                        <div class="m-row cols-2" style="margin-bottom: 0;">
                            <div>
                                <label class="m-label">Discounted Legal Fee</label>
                                <div id="adminDetailDiscountedLegalFee" style="font-size: 14px; color: #0f172a; margin-top: 2px;"></div>
                            </div>
                            <div>
                                <label class="m-label">Commission</label>
                                <div id="adminDetailCommission" style="font-size: 18px; font-weight: 700; color: #22d3ee; margin-top: 2px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Note -->
                    <div id="adminDetailNoteSection" style="margin-top: 16px;">
                        <label class="m-label">Note</label>
                        <div id="adminDetailNote" style="font-size: 13px; color: #374151; margin-top: 4px; padding: 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;"></div>
                    </div>

                    <!-- Dates -->
                    <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #e5e7eb;">
                        <div class="m-row cols-2" style="margin-bottom: 0;">
                            <div>
                                <span class="m-label">Submitted</span>
                                <div id="adminDetailSubmittedAt" style="font-size: 13px; color: #0f172a; margin-top: 2px;"></div>
                            </div>
                            <div id="adminDetailReviewedSection">
                                <span class="m-label">Reviewed</span>
                                <div id="adminDetailReviewedAt" style="font-size: 13px; color: #0f172a; margin-top: 2px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="m-footer">
                    <button onclick="closeCaseDetailAdmin()" class="m-btn m-btn-secondary">Close</button>
                </div>
            </div>
        </div>
