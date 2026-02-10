/**
 * Manager Dashboard - Referrals Tab
 * CRUD operations for referral entries
 */

let referralFiltersInit = false;

function initReferralFilters() {
    if (referralFiltersInit) return;
    referralFiltersInit = true;

    // Year filter
    const yearSel = document.getElementById('referralYearFilter');
    const currentYear = new Date().getFullYear();
    for (let y = currentYear; y >= currentYear - 3; y--) {
        const opt = document.createElement('option');
        opt.value = y;
        opt.textContent = y;
        yearSel.appendChild(opt);
    }

    // Case manager filter - load from API
    loadCaseManagerFilter();
}

async function loadCaseManagerFilter() {
    try {
        if (!referralUsersCache) {
            const result = await apiCall('api/users.php');
            referralUsersCache = (result.users || []).filter(u => u.is_active == 1);
        }
        const sel = document.getElementById('referralManagerFilter');
        // Keep the "All" option, clear the rest
        while (sel.options.length > 1) sel.remove(1);
        referralUsersCache.forEach(u => {
            const opt = document.createElement('option');
            opt.value = u.id;
            opt.textContent = u.display_name;
            sel.appendChild(opt);
        });

        // Also populate referral form Case Manager + Lead dropdowns
        const formSel = document.getElementById('refCaseManager');
        if (formSel) {
            while (formSel.options.length > 1) formSel.remove(1);
            referralUsersCache.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = u.display_name;
                formSel.appendChild(opt);
            });
        }
        const leadSel = document.getElementById('refLead');
        if (leadSel) {
            // Keep static options (Select, Office, Prior Client), remove dynamic employee options
            while (leadSel.options.length > 3) leadSel.remove(3);
            referralUsersCache.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = u.display_name;
                leadSel.appendChild(opt);
            });
        }
    } catch (err) {
        console.error('Error loading users for filter:', err);
    }
}

async function loadReferrals() {
    const year = document.getElementById('referralYearFilter').value || new Date().getFullYear();
    const month = document.getElementById('referralMonthFilter').value || 0;
    const caseManagerId = document.getElementById('referralManagerFilter').value || 'all';

    const tbody = document.getElementById('referralsTableBody');
    tbody.innerHTML = '<tr><td colspan="12" style="text-align: center; padding: 40px; color: #8b8fa3; font-size: 12px;">Loading...</td></tr>';

    try {
        let url = `api/referrals.php?action=list&year=${year}`;
        if (month > 0) url += `&month=${month}`;
        if (caseManagerId !== 'all') url += `&case_manager_id=${caseManagerId}`;

        const result = await apiCall(url);
        allReferrals = result.entries || [];
        renderReferrals();
    } catch (err) {
        console.error('Error loading referrals:', err);
        tbody.innerHTML = '<tr><td colspan="12" style="text-align: center; padding: 40px; color: #ef4444; font-size: 12px;">Error loading referrals</td></tr>';
    }
}

function renderReferrals() {
    const tbody = document.getElementById('referralsTableBody');
    document.getElementById('refTableCount').textContent = `${allReferrals.length} entries`;

    if (allReferrals.length === 0) {
        tbody.innerHTML = '<tr><td colspan="12" style="text-align: center; padding: 40px; color: #8b8fa3; font-size: 12px;">No referral entries found</td></tr>';
        return;
    }

    tbody.innerHTML = allReferrals.map(r => {
        const signed = r.signed_date ? new Date(r.signed_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '';
        const dol = r.date_of_loss ? new Date(r.date_of_loss + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '';

        return `<tr>
            <td class="c" style="color: #8b8fa3;">${r.row_number || ''}</td>
            <td style="font-weight: 500;">${escapeHtml(r.lead_name || r.referral_type || '')}</td>
            <td style="white-space: nowrap;">${escapeHtml(signed)}</td>
            <td style="font-family: 'Space Mono', monospace; font-size: 11px;">${escapeHtml(r.file_number || '')}</td>
            <td style="font-weight: 500; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(r.client_name)}">${escapeHtml(r.client_name)}</td>
            <td style="white-space: nowrap;">${escapeHtml(dol)}</td>
            <td style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(r.referred_by || '')}">${escapeHtml(r.referred_by || '')}</td>
            <td style="max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(r.referred_to_provider || '')}">${escapeHtml(r.referred_to_provider || '')}</td>
            <td>${escapeHtml(r.referred_to_body_shop || '')}</td>
            <td style="font-weight: 500;">${escapeHtml(r.case_manager_name || '')}</td>
            <td style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #8b8fa3;" title="${escapeHtml(r.remark || '')}">${escapeHtml(r.remark || '')}</td>
            <td class="c" style="white-space: nowrap;">
                <button onclick="editReferral(${r.id})" class="act-link" style="margin-right: 4px;">Edit</button>
                <button onclick="deleteReferral(${r.id})" class="act-link danger">Del</button>
            </td>
        </tr>`;
    }).join('');
}

async function loadReferralSummary() {
    const year = document.getElementById('referralYearFilter').value || new Date().getFullYear();
    try {
        const result = await apiCall(`api/referrals.php?action=summary&year=${year}`);
        document.getElementById('refMonthCount').textContent = result.total_month || 0;
        document.getElementById('refYearCount').textContent = result.total_year || 0;

        const topSource = (result.by_source && result.by_source.length > 0)
            ? result.by_source[0].referred_by
            : '—';
        document.getElementById('refTopSource').textContent = topSource;
    } catch (err) {
        console.error('Error loading referral summary:', err);
    }
}

function openReferralForm(id) {
    document.getElementById('refEditId').value = '';
    document.getElementById('referralFormTitle').textContent = 'New Referral';
    document.getElementById('refSignedDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('refFileNumber').value = '';
    document.getElementById('refClientName').value = '';
    document.getElementById('refDateOfLoss').value = '';
    document.getElementById('refReferredBy').value = '';
    document.getElementById('refProvider').value = '';
    document.getElementById('refBodyShop').value = '';
    document.getElementById('refLead').value = '';
    document.getElementById('refCaseManager').value = '';
    document.getElementById('refRemark').value = '';

    // Ensure case manager dropdown is populated
    loadCaseManagerFilter();

    openModal('referralFormModal');
}

function editReferral(id) {
    const r = allReferrals.find(e => e.id == id);
    if (!r) return;

    document.getElementById('refEditId').value = r.id;
    document.getElementById('referralFormTitle').textContent = 'Edit Referral';
    document.getElementById('refSignedDate').value = r.signed_date || '';
    document.getElementById('refFileNumber').value = r.file_number || '';
    document.getElementById('refClientName').value = r.client_name || '';
    document.getElementById('refDateOfLoss').value = r.date_of_loss || '';
    document.getElementById('refReferredBy').value = r.referred_by || '';
    document.getElementById('refProvider').value = r.referred_to_provider || '';
    document.getElementById('refBodyShop').value = r.referred_to_body_shop || '';
    if (r.lead_id) {
        document.getElementById('refLead').value = r.lead_id;
    } else if (r.referral_type === 'Office') {
        document.getElementById('refLead').value = 'office';
    } else if (r.referral_type === 'Prior client') {
        document.getElementById('refLead').value = 'prior_client';
    } else {
        document.getElementById('refLead').value = '';
    }
    document.getElementById('refCaseManager').value = r.case_manager_id || '';
    document.getElementById('refRemark').value = r.remark || '';

    loadCaseManagerFilter();
    openModal('referralFormModal');
}

async function saveReferral(e) {
    e.preventDefault();

    const editId = document.getElementById('refEditId').value;
    const leadVal = document.getElementById('refLead').value;
    let leadId = null;
    let referralType = '';
    if (leadVal === 'office') {
        referralType = 'Office';
    } else if (leadVal === 'prior_client') {
        referralType = 'Prior client';
    } else if (leadVal) {
        leadId = leadVal;
    }
    const data = {
        signed_date: document.getElementById('refSignedDate').value,
        file_number: document.getElementById('refFileNumber').value,
        client_name: document.getElementById('refClientName').value,
        date_of_loss: document.getElementById('refDateOfLoss').value,
        referred_by: document.getElementById('refReferredBy').value,
        referred_to_provider: document.getElementById('refProvider').value,
        referred_to_body_shop: document.getElementById('refBodyShop').value,
        lead_id: leadId,
        referral_type: referralType,
        case_manager_id: document.getElementById('refCaseManager').value || null,
        remark: document.getElementById('refRemark').value
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
            closeModal('referralFormModal');
            loadReferrals();
            loadReferralSummary();
        } else {
            alert(result.error || 'Error saving referral');
        }
    } catch (err) {
        console.error('Error saving referral:', err);
        alert(err.message || 'Error saving referral');
    }
}

async function deleteReferral(id) {
    if (!confirm('Delete this referral entry?')) return;

    try {
        const result = await apiCall(`api/referrals.php?id=${id}`, { method: 'DELETE' });
        if (result.success) {
            loadReferrals();
            loadReferralSummary();
        } else {
            alert(result.error || 'Error deleting referral');
        }
    } catch (err) {
        console.error('Error deleting referral:', err);
        alert(err.message || 'Error deleting referral');
    }
}

function exportReferrals() {
    if (allReferrals.length === 0) {
        alert('No data to export');
        return;
    }

    const rows = allReferrals.map(r => ({
        '#': r.row_number || '',
        'Lead': r.lead_name || r.referral_type || '',
        'Signed Date': r.signed_date || '',
        'File Number': r.file_number || '',
        'Client Name': r.client_name || '',
        'Date of Loss': r.date_of_loss || '',
        'Referred By': r.referred_by || '',
        'Referred To': r.referred_to_provider || '',
        'Body Shop': r.referred_to_body_shop || '',
        'Case Manager': r.case_manager_name || '',
        'Remark': r.remark || ''
    }));

    const ws = XLSX.utils.json_to_sheet(rows);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Referrals');
    const year = document.getElementById('referralYearFilter').value || new Date().getFullYear();
    XLSX.writeFile(wb, `Referrals_${year}.xlsx`);
}
