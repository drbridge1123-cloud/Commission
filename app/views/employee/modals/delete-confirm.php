    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal-overlay" style="display: none;">
        <div class="modal-content m-shell" style="max-width: 400px;">
            <div class="m-header">
                <div class="m-header-title"><h3>Confirm Delete</h3></div>
                <button onclick="closeDeleteConfirmModal()" class="m-close">&times;</button>
            </div>
            <div class="m-body">
                <p style="font-size: 14px; color: #374151;">Are you sure you want to delete this notification? This action cannot be undone.</p>
            </div>
            <div class="m-footer">
                <button onclick="closeDeleteConfirmModal()" class="m-btn m-btn-secondary">Cancel</button>
                <button onclick="confirmDeleteNotification()" class="m-btn m-btn-danger" style="background: #dc2626; color: white; border: none;">Yes, Delete</button>
            </div>
        </div>
    </div>
