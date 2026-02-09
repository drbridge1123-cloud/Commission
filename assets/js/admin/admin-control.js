/**
 * Admin Dashboard - Admin Control (User Management) functions.
 */

async function loadUsers() {
    try {
        const response = await fetch('api/users.php');
        const data = await response.json();
        allUsers = data.users || [];
        renderUsers();
    } catch (err) {
        console.error('Error loading users:', err);
        document.getElementById('usersTableBody').innerHTML = `
            <tr><td colspan="5" style="padding: 32px 16px; text-align: center; color: #ef4444;">Error loading users</td></tr>
        `;
    }
}

function renderUsers() {
    const tbody = document.getElementById('usersTableBody');

    if (allUsers.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="padding: 24px; text-align: center; color: #8b8fa3; font-size: 12px;">No users found</td></tr>`;
        return;
    }

    tbody.innerHTML = allUsers.map(user => {
        const perms = user.permissions || {};
        const isTrafficOn = user.role === 'admin' || !!perms.can_request_traffic;
        const isAdminUser = user.role === 'admin';

        return `
        <tr>
            <td style="font-weight: 600;">${user.username}</td>
            <td>${user.display_name}</td>
            <td class="c">
                <span class="stat-badge ${isAdminUser ? 'pending' : 'paid'}">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span>
            </td>
            <td class="c">
                ${user.is_attorney == 1 ? '<span class="stat-badge" style="background:#e0e7ff; color:#4338ca;">Attorney</span>' : ''}
            </td>
            <td class="c">
                ${user.is_manager == 1 ? '<span class="stat-badge" style="background:#fef3c7; color:#92400e;">Manager</span>' : ''}
            </td>
            <td class="r">${user.commission_rate || 0}%</td>
            <td class="c">
                <label class="toggle-switch" title="Traffic Request Access">
                    <input type="checkbox" ${isTrafficOn ? 'checked' : ''} ${isAdminUser ? 'disabled' : ''}
                        onchange="togglePermission(${user.id}, 'can_request_traffic', this.checked)">
                    <span class="toggle-slider"></span>
                </label>
            </td>
            <td class="c">
                <button onclick="editUser(${user.id})" class="act-link" style="margin-right: 4px;">Edit</button>
                <button onclick="resetPassword(${user.id})" class="act-link" style="background: #d97706; margin-right: 4px;">Reset</button>
                ${!isAdminUser ? `<button onclick="deleteUser(${user.id})" class="act-link danger">Delete</button>` : ''}
            </td>
        </tr>`;
    }).join('');
}

async function togglePermission(userId, permission, enabled) {
    try {
        const result = await apiCall(`api/users.php?id=${userId}`, {
            method: 'PUT',
            body: JSON.stringify({ permissions: { [permission]: enabled } })
        });
        if (result.success) {
            const user = allUsers.find(u => u.id === userId);
            if (user) {
                if (!user.permissions) user.permissions = {};
                user.permissions[permission] = enabled;
            }
        } else {
            alert(result.error || 'Error updating permission');
            loadUsers();
        }
    } catch (err) {
        console.error('Error toggling permission:', err);
        alert(err.message || 'Error updating permission');
        loadUsers();
    }
}

function openAddUserModal() {
    document.getElementById('userModalTitle').textContent = 'Add User';
    document.getElementById('editUserId').value = '';
    document.getElementById('userUsername').value = '';
    document.getElementById('userDisplayName').value = '';
    document.getElementById('userPassword').value = '';
    document.getElementById('userPassword').required = true;
    document.getElementById('passwordHint').textContent = 'Required for new users';
    document.getElementById('userRole').value = 'employee';
    document.getElementById('userCommissionRate').value = '0';
    document.getElementById('userIsAttorney').checked = false;
    document.getElementById('userIsManager').checked = false;
    toggleTeamSection();
    document.getElementById('userModal').classList.add('show');
}

function editUser(id) {
    const user = allUsers.find(u => u.id === id);
    if (!user) return;

    document.getElementById('userModalTitle').textContent = 'Edit User';
    document.getElementById('editUserId').value = user.id;
    document.getElementById('userUsername').value = user.username;
    document.getElementById('userDisplayName').value = user.display_name;
    document.getElementById('userPassword').value = '';
    document.getElementById('userPassword').required = false;
    document.getElementById('passwordHint').textContent = 'Leave blank to keep current password';
    document.getElementById('userRole').value = user.role;
    document.getElementById('userCommissionRate').value = user.commission_rate || 0;
    document.getElementById('userIsAttorney').checked = user.is_attorney == 1;
    document.getElementById('userIsManager').checked = user.is_manager == 1;
    toggleTeamSection();
    if (user.is_manager == 1) {
        loadTeamMembersForUser(user.id);
    }
    document.getElementById('userModal').classList.add('show');
}

function toggleTeamSection() {
    const isManager = document.getElementById('userIsManager').checked;
    document.getElementById('teamMembersSection').style.display = isManager ? 'block' : 'none';
}

async function loadTeamMembersForUser(managerId) {
    const container = document.getElementById('teamMembersList');
    try {
        const [teamResult, usersResult] = await Promise.all([
            apiCall(`api/manager_team.php?manager_id=${managerId}`),
            apiCall('api/users.php')
        ]);
        const teamIds = (teamResult.members || []).map(m => m.employee_id);
        const employees = (usersResult.users || []).filter(u => u.is_active == 1 && u.role === 'employee' && u.id != managerId);

        container.innerHTML = employees.map(emp => `
            <label style="display: flex; align-items: center; gap: 8px; padding: 4px 0; cursor: pointer;">
                <input type="checkbox" class="team-member-cb" value="${emp.id}" ${teamIds.includes(emp.id) ? 'checked' : ''} style="width: 14px; height: 14px;">
                <span style="font-size: 13px;">${escapeHtml(emp.display_name)}</span>
            </label>
        `).join('');
    } catch (err) {
        console.error('Error loading team members:', err);
        container.innerHTML = '<span style="color: #ef4444; font-size: 12px;">Error loading team members</span>';
    }
}

async function saveTeamMembers(managerId) {
    const checkboxes = document.querySelectorAll('.team-member-cb:checked');
    const employeeIds = Array.from(checkboxes).map(cb => parseInt(cb.value));

    try {
        await apiCall('api/manager_team.php', {
            method: 'POST',
            body: JSON.stringify({ manager_id: managerId, employee_ids: employeeIds })
        });
    } catch (err) {
        console.error('Error saving team members:', err);
    }
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('show');
}

document.getElementById('userForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const userId = document.getElementById('editUserId').value;
    const username = document.getElementById('userUsername').value;
    const displayName = document.getElementById('userDisplayName').value;
    const password = document.getElementById('userPassword').value;
    const role = document.getElementById('userRole').value;
    const commissionRate = document.getElementById('userCommissionRate').value;

    const isAttorney = document.getElementById('userIsAttorney').checked;
    const isManager = document.getElementById('userIsManager').checked;

    const userData = {
        username,
        display_name: displayName,
        role,
        commission_rate: parseFloat(commissionRate),
        is_attorney: isAttorney,
        is_manager: isManager
    };

    if (password) {
        userData.password = password;
    }

    try {
        const url = userId ? `api/users.php?id=${userId}` : 'api/users.php';
        const method = userId ? 'PUT' : 'POST';

        const result = await apiCall(url, {
            method,
            body: JSON.stringify(userData)
        });

        if (result.success) {
            const savedUserId = userId || result.user_id;
            if (isManager && savedUserId) {
                await saveTeamMembers(savedUserId);
            }
            closeUserModal();
            loadUsers();
            alert(userId ? 'User updated successfully!' : 'User added successfully!');
        } else {
            alert(result.error || 'Error saving user');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error saving user');
    }
});

async function deleteUser(id) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;

    try {
        const result = await apiCall(`api/users.php?id=${id}`, {
            method: 'DELETE'
        });

        if (result.success) {
            loadUsers();
            alert('User deleted successfully!');
        } else {
            alert(result.error || 'Error deleting user');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error deleting user');
    }
}

async function resetPassword(id) {
    const newPassword = prompt('Enter new password for this user:');
    if (!newPassword) return;

    if (newPassword.length < 4) {
        alert('Password must be at least 4 characters long');
        return;
    }

    try {
        const result = await apiCall(`api/users.php?id=${id}`, {
            method: 'PUT',
            body: JSON.stringify({ password: newPassword })
        });

        if (result.success) {
            alert('Password reset successfully!');
        } else {
            alert(result.error || 'Error resetting password');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error resetting password');
    }
}
