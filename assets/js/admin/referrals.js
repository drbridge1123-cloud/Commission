/**
 * Admin Dashboard - Referrals Tab
 * View and manage all referral entries
 */

let adminRefFiltersInit = false;
let adminRefUsersCache = null;
let adminAllReferrals = [];

function initAdminRefFilters() {
    if (adminRefFiltersInit) return;
    adminRefFiltersInit = true;

    const yearSel = document.getElementById('adminRefYearFilter');
    const currentYear = new Date().getFullYear();
    for (let y = currentYear; y >= currentYear - 3; y--) {
        const opt = document.createElement('option');
        opt.value = y;
        opt.textContent = y;
        yearSel.appendChild(opt);
    }

    // Load case managers
    loadAdminRefManagerFilter();
}

async function loadAdminRefManagerFilter() {
    try {
        if (!adminRefUsersCache) {
            const result = await apiCall('api/users.php');
            adminRefUsersCache = (result.users || []).filter(u => u.is_active == 1);
        }
        const sel = document.getElementById('adminRefManagerFilter');
        while (sel.options.length > 1) sel.remove(1);
        adminRefUsersCache.forEach(u => {
            const opt = document.createElement('option');
            opt.value = u.id;
            opt.textContent = u.display_name;
            sel.appendChild(opt);
        });

        // Also populate the admin referral form Case Manager dropdown
        const formSel = document.getElementById('adminRefCaseManager');
        if (formSel) {
            while (formSel.options.length > 1) formSel.remove(1);
            adminRefUsersCache.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = u.display_name;
                formSel.appendChild(opt);
            });
        }
    } catch (err) {
        console.error('Error loading users for admin ref filter:', err);
    }
}

async function loadAdminReferrals() {
    const year = document.getElementById('adminRefYearFilter').value || new Date().getFullYear();
    const month = document.getElementById('adminRefMonthFilter').value || 0;
    const managerId = document.getElementById('adminRefManagerFilter').value || 'all';

    const tbody = document.getElementById('adminReferralsBody');
    tbody.innerHTML = '<tr><td colspan="12" style="text-align: center; padding: 40px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>';

    try {
        let url = `api/referrals.php?action=list&year=${year}`;
        if (month > 0) url += `&month=${month}`;
        if (managerId !== 'all') url += `&case_manager_id=${managerId}`;

        const result = await apiCall(url);
        adminAllReferrals = result.entries || [];
        renderAdminReferrals();
    } catch (err) {
        console.error('Error loading admin referrals:', err);
        tbody.innerHTML = '<tr><td colspan="12" style="text-align: center; padding: 40px; color: #ef4444; font-size: 12px;">Error loading referrals</td></tr>';
    }
}

function renderAdminReferrals() {
    const tbody = document.getElementById('adminReferralsBody');
    document.getElementById('adminRefTableCount').textContent = `${adminAllReferrals.length} entries`;

    if (adminAllReferrals.length === 0) {
        tbody.innerHTML = '<tr><td colspan="12" style="text-align: center; padding: 40px; color: #8b8fa3; font-size: 12px;">No referral entries found</td></tr>';
        return;
    }

    tbody.innerHTML = adminAllReferrals.map(r => {
        const signed = r.signed_date ? new Date(r.signed_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '';
        const dol = r.date_of_loss ? new Date(r.date_of_loss + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '';

        return `<tr>
            <td class="c" style="color: #8b8fa3;">${r.row_number || ''}</td>
            <td>${escapeHtml(r.referral_type || '')}</td>
            <td style="white-space: nowrap;">${escapeHtml(signed)}</td>
            <td style="font-family: monospace; font-size: 11px;">${escapeHtml(r.file_number || '')}</td>
            <td style="font-weight: 500; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(r.client_name)}">${escapeHtml(r.client_name)}</td>
            <td style="white-space: nowrap;">${escapeHtml(dol)}</td>
            <td style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(r.referred_by || '')}">${escapeHtml(r.referred_by || '')}</td>
            <td style="max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(r.referred_to_provider || '')}">${escapeHtml(r.referred_to_provider || '')}</td>
            <td>${escapeHtml(r.referred_to_body_shop || '')}</td>
            <td style="font-weight: 500;">${escapeHtml(r.case_manager_name || '')}</td>
            <td style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #8b8fa3;" title="${escapeHtml(r.remark || '')}">${escapeHtml(r.remark || '')}</td>
            <td class="c" style="white-space: nowrap;">
                <button onclick="editAdminReferral(${r.id})" class="act-link" style="margin-right: 4px;">Edit</button>
                <button onclick="deleteAdminReferral(${r.id})" class="act-link danger">Del</button>
            </td>
        </tr>`;
    }).join('');
}

async function loadAdminRefSummary() {
    const year = document.getElementById('adminRefYearFilter').value || new Date().getFullYear();
    try {
        const result = await apiCall(`api/referrals.php?action=summary&year=${year}`);
        document.getElementById('adminRefMonthCount').textContent = result.total_month || 0;
        document.getElementById('adminRefYearCount').textContent = result.total_year || 0;

        const topSource = (result.by_source && result.by_source.length > 0)
            ? result.by_source[0].referred_by
            : '—';
        document.getElementById('adminRefTopSource').textContent = topSource;
    } catch (err) {
        console.error('Error loading admin referral summary:', err);
    }
}

function openAdminReferralForm() {
    document.getElementById('adminRefEditId').value = '';
    document.getElementById('adminRefFormTitle').textContent = 'New Referral';
    document.getElementById('adminRefSignedDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('adminRefFileNumber').value = '';
    document.getElementById('adminRefClientName').value = '';
    document.getElementById('adminRefDateOfLoss').value = '';
    document.getElementById('adminRefReferredBy').value = '';
    document.getElementById('adminRefProvider').value = '';
    document.getElementById('adminRefBodyShop').value = '';
    document.getElementById('adminRefType').value = '';
    document.getElementById('adminRefCaseManager').value = '';
    document.getElementById('adminRefRemark').value = '';

    loadAdminRefManagerFilter();
    document.getElementById('adminReferralFormModal').classList.add('show');
}

function editAdminReferral(id) {
    const r = adminAllReferrals.find(e => e.id == id);
    if (!r) return;

    document.getElementById('adminRefEditId').value = r.id;
    document.getElementById('adminRefFormTitle').textContent = 'Edit Referral';
    document.getElementById('adminRefSignedDate').value = r.signed_date || '';
    document.getElementById('adminRefFileNumber').value = r.file_number || '';
    document.getElementById('adminRefClientName').value = r.client_name || '';
    document.getElementById('adminRefDateOfLoss').value = r.date_of_loss || '';
    document.getElementById('adminRefReferredBy').value = r.referred_by || '';
    document.getElementById('adminRefProvider').value = r.referred_to_provider || '';
    document.getElementById('adminRefBodyShop').value = r.referred_to_body_shop || '';
    document.getElementById('adminRefType').value = r.referral_type || '';
    document.getElementById('adminRefCaseManager').value = r.case_manager_id || '';
    document.getElementById('adminRefRemark').value = r.remark || '';

    loadAdminRefManagerFilter();
    document.getElementById('adminReferralFormModal').classList.add('show');
}

async function saveAdminReferral(e) {
    e.preventDefault();

    const editId = document.getElementById('adminRefEditId').value;
    const data = {
        signed_date: document.getElementById('adminRefSignedDate').value,
        file_number: document.getElementById('adminRefFileNumber').value,
        client_name: document.getElementById('adminRefClientName').value,
        date_of_loss: document.getElementById('adminRefDateOfLoss').value,
        referred_by: document.getElementById('adminRefReferredBy').value,
        referred_to_provider: document.getElementById('adminRefProvider').value,
        referred_to_body_shop: document.getElementById('adminRefBodyShop').value,
        referral_type: document.getElementById('adminRefType').value,
        case_manager_id: document.getElementById('adminRefCaseManager').value || null,
        remark: document.getElementById('adminRefRemark').value
    };

    if (!data.client_name) {
        alert('Client name is required');
        return;
    }

    try {
        let result;
        if (editId) {
            data.id = editId;
            result = await apiCall('api/referrals.php', {
                method: 'PUT',
                body: JSON.stringify(data)
            });
        } else {
            result = await apiCall('api/referrals.php', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        }

        if (result.success) {
            closeModal('adminReferralFormModal');
            loadAdminReferrals();
            loadAdminRefSummary();
        } else {
            alert(result.error || 'Error saving referral');
        }
    } catch (err) {
        console.error('Error saving referral:', err);
        alert(err.message || 'Error saving referral');
    }
}

async function deleteAdminReferral(id) {
    if (!confirm('Delete this referral entry?')) return;
    try {
        const result = await apiCall(`api/referrals.php?id=${id}`, { method: 'DELETE' });
        if (result.success) {
            loadAdminReferrals();
            loadAdminRefSummary();
        } else {
            alert(result.error || 'Error deleting referral');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error deleting referral');
    }
}
