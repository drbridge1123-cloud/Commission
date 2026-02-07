    <!-- Case Detail Modal -->
    <div id="caseDetailModal" class="modal-overlay" onclick="if(event.target === this) closeCaseDetail()">
        <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 680px; max-height: 90vh; border-radius: 12px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.12); overflow: hidden; display: flex; flex-direction: column; padding: 0;">
            <!-- Blue Header -->
            <div style="background: #18181b; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 3px; height: 18px; background: #22d3ee; border-radius: 2px;"></div>
                    <h2 style="font-size: 15px; font-weight: 600; color: white; margin: 0;">Case Details</h2>
                </div>
                <button onclick="closeCaseDetail()" style="width: 28px; height: 28px; background: rgba(255,255,255,0.15); border: none; border-radius: 6px; color: rgba(255,255,255,0.9); font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
            </div>

            <!-- Content Area (Scrollable) -->
            <div style="padding: 16px 20px; overflow-y: auto; flex: 1;">
                <div id="caseDetailContent"></div>
            </div>

            <!-- Footer (Fixed) -->
            <div style="background: #f8fafc; padding: 12px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; align-items: center; flex-shrink: 0; gap: 10px;">
                <button id="editCaseDetailBtn" onclick="editCaseFromDetail()" style="padding: 9px 16px; background: #18181b; color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: none;">Edit</button>
                <button onclick="closeCaseDetail()" style="padding: 9px 14px; background: white; color: #64748b; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;">Close</button>
            </div>
        </div>
    </div>
