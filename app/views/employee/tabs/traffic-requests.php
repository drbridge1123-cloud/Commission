        <div id="content-traffic-requests" class="hidden">
            <div style="display: grid; grid-template-columns: 320px 1fr; gap: 16px;">
                <!-- Request Form -->
                <div>
                    <div class="tbl-container">
                        <div style="padding: 10px 14px; background: #1a1a2e; border-radius: 10px 10px 0 0;">
                            <h3 style="font-size: 12px; font-weight: 600; color: #fff; font-family: 'Outfit', sans-serif; margin: 0;">Request New Traffic Case</h3>
                        </div>
                        <form id="empTrafficRequestForm" style="padding: 12px 14px; display: flex; flex-direction: column; gap: 8px;">
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Client Name *</label>
                                <input type="text" id="empReqClientName" required class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Phone</label>
                                    <input type="text" id="empReqClientPhone" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Email</label>
                                    <input type="email" id="empReqClientEmail" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Ticket #</label>
                                    <input type="text" id="empReqCaseNumber" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Court</label>
                                    <input type="text" id="empReqCourt" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                                </div>
                            </div>
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Charge</label>
                                <input type="text" id="empReqCharge" class="ink-input" style="padding: 6px 8px; font-size: 12px;">
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Issued</label>
                                    <input type="date" id="empReqIssuedDate" class="ink-input" style="padding: 5px 6px; font-size: 11px;">
                                </div>
                                <div>
                                    <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Court Date</label>
                                    <input type="date" id="empReqCourtDate" class="ink-input" style="padding: 5px 6px; font-size: 11px;">
                                </div>
                            </div>
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 2px;">Note</label>
                                <textarea id="empReqNote" rows="2" class="ink-input" style="padding: 6px 8px; font-size: 12px; resize: none;"></textarea>
                            </div>
                            <button type="submit" class="ink-btn ink-btn-primary" style="width: 100%; justify-content: center; padding: 8px 12px; font-size: 12px;">Submit Request</button>
                        </form>
                    </div>
                </div>

                <!-- My Requests List -->
                <div class="tbl-container" style="align-self: start;">
                    <div style="padding: 12px 16px; border-bottom: 1px solid #e2e4ea;">
                        <h3 style="font-size: 14px; font-weight: 600; color: #1a1a2e; font-family: 'Outfit', sans-serif; margin: 0;">My Requests</h3>
                    </div>
                    <div id="empMyTrafficRequests" style="max-height: 700px; overflow-y: auto;">
                        <p style="padding: 24px; text-align: center; color: #8b8fa3; font-size: 12px; font-family: 'Outfit', sans-serif;">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
