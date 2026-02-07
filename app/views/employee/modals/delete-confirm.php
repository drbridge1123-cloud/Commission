    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal-overlay" style="display: none;">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">Confirm Delete</h3>
                <button onclick="closeDeleteConfirmModal()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px; color: #374151; margin-bottom: 20px;">Are you sure you want to delete this notification? This action cannot be undone.</p>
                <div class="modal-actions">
                    <button onclick="confirmDeleteNotification()" class="btn-danger">Yes, Delete</button>
                    <button onclick="closeDeleteConfirmModal()" class="btn-secondary">Cancel</button>
                </div>
            </div>
        </div>
    </div>
