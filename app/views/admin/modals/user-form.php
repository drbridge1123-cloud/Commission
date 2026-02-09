        <!-- Add/Edit User Modal -->
        <div id="userModal" class="modal-overlay" onclick="if(event.target === this) closeUserModal()">
            <div class="modal-content m-shell" onclick="event.stopPropagation()" style="max-width: 500px;">
                <div class="m-header">
                    <div class="m-header-title"><h2 id="userModalTitle">Add User</h2></div>
                    <button onclick="closeUserModal()" class="m-close">&times;</button>
                </div>
                <form id="userForm">
                    <input type="hidden" id="editUserId">
                    <div class="m-body">
                        <div class="m-row cols-1">
                            <div>
                                <label class="m-label">Username</label>
                                <input type="text" id="userUsername" required class="m-input">
                            </div>
                        </div>
                        <div class="m-row cols-1">
                            <div>
                                <label class="m-label">Display Name</label>
                                <input type="text" id="userDisplayName" required class="m-input">
                            </div>
                        </div>
                        <div class="m-row cols-1">
                            <div>
                                <label class="m-label">Password</label>
                                <input type="password" id="userPassword" class="m-input">
                                <span class="m-help-text" id="passwordHint">Leave blank to keep current password</span>
                            </div>
                        </div>
                        <div class="m-row cols-2">
                            <div>
                                <label class="m-label">Role</label>
                                <select id="userRole" required class="m-input">
                                    <option value="employee">Employee</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div>
                                <label class="m-label">Commission Rate (%)</label>
                                <input type="number" step="0.01" id="userCommissionRate" required class="m-input">
                            </div>
                        </div>
                        <div class="m-row cols-1">
                            <div>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="userIsAttorney" style="width: 16px; height: 16px;">
                                    <span class="m-label" style="margin: 0;">Contract Attorney</span>
                                </label>
                            </div>
                        </div>
                        <div class="m-row cols-1">
                            <div>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="userIsManager" style="width: 16px; height: 16px;" onchange="toggleTeamSection()">
                                    <span class="m-label" style="margin: 0;">Office Manager</span>
                                </label>
                            </div>
                        </div>
                        <div class="m-row cols-1" id="teamMembersSection" style="display: none;">
                            <div>
                                <label class="m-label">Team Members</label>
                                <div id="teamMembersList" style="max-height: 150px; overflow-y: auto; border: 1px solid #e2e4ea; border-radius: 6px; padding: 8px;">
                                    <span style="color: #8b8fa3; font-size: 12px;">Save user first, then assign team members</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="m-footer">
                        <button type="button" onclick="closeUserModal()" class="m-btn m-btn-secondary">Cancel</button>
                        <button type="submit" class="m-btn m-btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
