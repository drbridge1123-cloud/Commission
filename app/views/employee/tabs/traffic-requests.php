        <div id="content-traffic-requests" class="hidden">
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; align-items: start;">
                <!-- Request Form (1/3) -->
                <div class="tbl-container" style="overflow: hidden;">
                    <div style="padding: 12px 16px; background: #1a1a2e; border-radius: 10px 10px 0 0;">
                        <h3 style="font-size: 12px; font-weight: 600; color: #fff; font-family: 'Outfit', sans-serif; margin: 0; letter-spacing: 0.3px;">Request New Traffic Case</h3>
                    </div>
                    <form id="empTrafficRequestForm" style="padding: 16px; display: flex; flex-direction: column; gap: 12px;">
                        <div>
                            <label class="ink-label" style="font-size: 10px; margin-bottom: 4px;">Client Name <span style="color: #3b82f6;">*</span></label>
                            <input type="text" id="empReqClientName" required class="ink-input" placeholder="Full name" style="padding: 8px 10px; font-size: 12px;">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 4px;">Phone</label>
                                <input type="text" id="empReqClientPhone" class="ink-input" placeholder="(000) 000-0000" style="padding: 8px 10px; font-size: 12px;">
                            </div>
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 4px;">Email</label>
                                <input type="email" id="empReqClientEmail" class="ink-input" placeholder="email@example.com" style="padding: 8px 10px; font-size: 12px;">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 4px;">Ticket #</label>
                                <input type="text" id="empReqCaseNumber" class="ink-input" style="padding: 8px 10px; font-size: 12px;">
                            </div>
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 4px;">Court</label>
                                <input type="text" id="empReqCourt" class="ink-input" style="padding: 8px 10px; font-size: 12px;">
                            </div>
                        </div>
                        <div>
                            <label class="ink-label" style="font-size: 10px; margin-bottom: 4px;">Charge</label>
                            <input type="text" id="empReqCharge" class="ink-input" style="padding: 8px 10px; font-size: 12px;">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 4px;">Issued</label>
                                <input type="date" id="empReqIssuedDate" class="ink-input" style="padding: 7px 8px; font-size: 11px;">
                            </div>
                            <div>
                                <label class="ink-label" style="font-size: 10px; margin-bottom: 4px;">Court Date</label>
                                <input type="date" id="empReqCourtDate" class="ink-input" style="padding: 7px 8px; font-size: 11px;">
                            </div>
                        </div>
                        <div>
                            <label class="ink-label" style="font-size: 10px; margin-bottom: 4px;">Note</label>
                            <textarea id="empReqNote" rows="2" class="ink-input" placeholder="Additional details..." style="padding: 8px 10px; font-size: 12px; resize: vertical;"></textarea>
                        </div>
                        <button type="submit" class="ink-btn ink-btn-primary" style="width: 100%; justify-content: center; padding: 10px 12px; font-size: 12px; font-weight: 600;">Submit Request</button>
                    </form>
                </div>

                <!-- My Requests List (2/3) -->
                <div class="tbl-container" style="align-self: start; overflow: hidden;">
                    <div style="display: flex; align-items: center; padding: 14px 20px; border-bottom: 1px solid #e2e4ea; gap: 10px;">
                        <h3 style="font-size: 15px; font-weight: 600; color: #1a1a2e; font-family: 'Outfit', sans-serif; margin: 0; white-space: nowrap;">My Requests</h3>
                        <input type="text" id="empReqSearch" class="ink-input" placeholder="Search..." oninput="filterEmpTrafficRequests()" style="width: 180px; padding: 5px 10px; font-size: 12px; border-radius: 6px;">
                        <span id="empReqCountBadge" style="font-size: 11px; color: #8b8fa3; background: #f0f1f3; padding: 3px 10px; border-radius: 12px; font-weight: 500; white-space: nowrap; margin-left: auto;">0 requests</span>
                    </div>
                    <div id="empMyTrafficRequests" style="max-height: 700px; overflow-y: auto;">
                        <p style="padding: 24px; text-align: center; color: #8b8fa3; font-size: 12px; font-family: 'Outfit', sans-serif;">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
