    <!-- Move to Litigation Modal -->
    <div id="toLitigationModal" class="modal-overlay hidden">
        <div class="modal-content m-shell" style="max-width: 500px;">
            <div class="m-header">
                <div class="m-header-title"><h2>Move to Litigation</h2></div>
                <button class="m-close" onclick="closeModal('toLitigationModal')">&times;</button>
            </div>
            <form id="toLitigationForm" onsubmit="submitToLitigation(event)">
                <input type="hidden" name="case_id">
                <div class="m-body">
                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Case: <span id="toLitCaseInfo" style="font-weight: 600;"></span></label>
                        </div>
                    </div>
                    <div class="m-row cols-2">
                        <div>
                            <label class="m-label">Litigation Start Date</label>
                            <input type="date" name="litigation_start_date" class="m-input" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div>
                            <label class="m-label">Pre-Suit Offer ($)</label>
                            <input type="number" name="presuit_offer" class="m-input" step="0.01" min="0" value="0">
                            <div class="m-help-text">The offer received before litigation</div>
                        </div>
                    </div>
                    <div class="m-row cols-1">
                        <div>
                            <label class="m-label">Note</label>
                            <input type="text" name="note" class="m-input">
                        </div>
                    </div>
                </div>
                <div class="m-footer">
                    <button type="button" class="m-btn m-btn-secondary" onclick="closeModal('toLitigationModal')">Cancel</button>
                    <button type="submit" class="m-btn m-btn-warning">Move to Litigation</button>
                </div>
            </form>
        </div>
    </div>
