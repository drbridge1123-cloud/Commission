    <!-- Case Detail Modal -->
    <div id="caseDetailModal" class="modal-overlay" onclick="if(event.target === this) closeCaseDetail()">
        <div class="modal-content m-shell" onclick="event.stopPropagation()" style="max-width: 680px;">
            <div class="m-header">
                <div class="m-header-title"><h2>Case Details</h2></div>
                <button onclick="closeCaseDetail()" class="m-close">&times;</button>
            </div>
            <div class="m-body">
                <div id="caseDetailContent"></div>
            </div>
            <div class="m-footer">
                <button id="editCaseDetailBtn" onclick="editCaseFromDetail()" class="m-btn m-btn-primary" style="display: none;">Edit</button>
                <button onclick="closeCaseDetail()" class="m-btn m-btn-secondary">Close</button>
            </div>
        </div>
    </div>
