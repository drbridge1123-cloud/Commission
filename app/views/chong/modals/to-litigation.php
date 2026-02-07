    <!-- Move to Litigation Modal -->
    <div id="toLitigationModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Move to Litigation</h2>
                <button class="modal-close" onclick="closeModal('toLitigationModal')">&times;</button>
            </div>
            <form id="toLitigationForm" onsubmit="submitToLitigation(event)">
                <input type="hidden" name="case_id">
                <div class="form-row full">
                    <div class="form-group">
                        <label>Case: <span id="toLitCaseInfo" style="font-weight: 600;"></span></label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Litigation Start Date</label>
                        <input type="date" name="litigation_start_date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Pre-Suit Offer ($)</label>
                        <input type="number" name="presuit_offer" step="0.01" min="0" value="0">
                        <div class="help-text">The offer received before litigation</div>
                    </div>
                </div>
                <div class="form-row full">
                    <div class="form-group">
                        <label>Note</label>
                        <input type="text" name="note">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('toLitigationModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: #f59e0b;">Move to Litigation</button>
                </div>
            </form>
        </div>
    </div>
