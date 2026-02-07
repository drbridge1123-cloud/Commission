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
                            <th>Username</th>
                            <th>Display Name</th>
                            <th class="c">Role</th>
                            <th class="r">Commission Rate</th>
                            <th class="c">Traffic</th>
                            <th class="c">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr><td colspan="6" style="padding: 24px; text-align: center; color: #8b8fa3; font-size: 12px;">Loading users...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>