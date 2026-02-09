        <!-- Admin Control Tab -->
        <div id="content-admin-control" class="hidden">
            <!-- Filters / Actions -->
            <div class="filters">
                <button onclick="openAddUserModal()" class="ink-btn ink-btn-primary ink-btn-sm">+ Add User</button>
            </div>

            <div class="tbl-container">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th data-sort="text">Username</th>
                            <th data-sort="text">Display Name</th>
                            <th class="c" data-sort="text">Role</th>
                            <th class="c" data-sort="text">Attorney</th>
                            <th class="c" data-sort="text">Manager</th>
                            <th class="r" data-sort="number">Commission Rate</th>
                            <th class="c" data-sort="text">Traffic</th>
                            <th class="c">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr><td colspan="8" style="padding: 24px; text-align: center; color: #8b8fa3; font-size: 12px;">Loading users...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>