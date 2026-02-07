        <!-- Add/Edit User Modal -->
        <div id="userModal" class="modal-overlay" onclick="if(event.target === this) closeUserModal()">
            <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 500px;">
                <div class="modal-header">
                    <h2 class="text-xl font-bold text-gray-900" id="userModalTitle">Add User</h2>
                    <button onclick="closeUserModal()" class="modal-close">&times;</button>
                </div>
                <form id="userForm" class="modal-body" style="display: flex; flex-direction: column; gap: 16px;">
                    <input type="hidden" id="editUserId">

                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Username</label>
                        <input type="text" id="userUsername" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Display Name</label>
                        <input type="text" id="userDisplayName" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Password</label>
                        <input type="password" id="userPassword" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        <p style="font-size: 12px; color: #6b7280; margin-top: 4px;" id="passwordHint">Leave blank to keep current password</p>
                    </div>

                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Role</label>
                        <select id="userRole" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                            <option value="employee">Employee</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Commission Rate (%)</label>
                        <input type="number" step="0.01" id="userCommissionRate" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Save</button>
                        <button type="button" onclick="closeUserModal()" class="btn-secondary">Cancel</button>
                    </div>
                </form>
            </div>
        </div>