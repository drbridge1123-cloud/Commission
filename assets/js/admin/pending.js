/**
 * Admin Dashboard - Pending cases tab functions.
 */

async function loadPendingCases() {
    try {
        const data = await apiCall('api/cases.php?status=unpaid');
        pendingCases = data.cases || [];
        renderPendingCases();
        document.getElementById('pendingBadge').textContent = pendingCases.length;
    } catch (err) {
        console.error('Error:', err);
    }
}

function sortPendingCases(column) {
    if (pendingSortColumn === column) {
        pendingSortDirection = pendingSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        pendingSortColumn = column;
        pendingSortDirection = 'asc';
    }

    pendingCases.sort((a, b) => {
        let valA, valB;

        switch(column) {
            case 'counsel':
                valA = (a.counsel_name || '').toLowerCase();
                valB = (b.counsel_name || '').toLowerCase();
                break;
            case 'status':
                valA = (a.status || '').toLowerCase();
                valB = (b.status || '').toLowerCase();
                break;
            case 'month':
                valA = parseMonthForSort(a.month);
                valB = parseMonthForSort(b.month);
                break;
            case 'case_type':
                valA = (a.case_type || '').toLowerCase();
                valB = (b.case_type || '').toLowerCase();
                break;
            case 'case_number':
                valA = (a.case_number || '').toLowerCase();
                valB = (b.case_number || '').toLowerCase();
                break;
            case 'client_name':
                valA = (a.client_name || '').toLowerCase();
                valB = (b.client_name || '').toLowerCase();
                break;
            case 'resolution_type':
                valA = (a.resolution_type || '').toLowerCase();
                valB = (b.resolution_type || '').toLowerCase();
                break;
            case 'fee_rate':
                valA = parseFloat(a.fee_rate) || 0;
                valB = parseFloat(b.fee_rate) || 0;
                break;
            case 'settled_amount':
                valA = parseFloat(a.settled) || 0;
                valB = parseFloat(b.settled) || 0;
                break;
            case 'pre_suit_demand':
                valA = parseFloat(a.presuit_offer) || 0;
                valB = parseFloat(b.presuit_offer) || 0;
                break;
            case 'difference':
                valA = parseFloat(a.difference) || 0;
                valB = parseFloat(b.difference) || 0;
                break;
            case 'legal_fee':
                valA = parseFloat(a.legal_fee) || 0;
                valB = parseFloat(b.legal_fee) || 0;
                break;
            case 'discounted_legal_fee':
                valA = parseFloat(a.discounted_legal_fee) || 0;
                valB = parseFloat(b.discounted_legal_fee) || 0;
                break;
            case 'commission':
                valA = parseFloat(a.commission) || 0;
                valB = parseFloat(b.commission) || 0;
                break;
            default:
                valA = '';
                valB = '';
        }

        if (valA < valB) return pendingSortDirection === 'asc' ? -1 : 1;
        if (valA > valB) return pendingSortDirection === 'asc' ? 1 : -1;
        return 0;
    });

    updatePendingSortIcons(column);
    renderPendingCases();
}

function updatePendingSortIcons(activeColumn) {
    document.querySelectorAll('#pendingTable .th-content.sortable .sort-icon').forEach(icon => {
        icon.textContent = '▼';
        icon.style.opacity = '0.3';
    });

    const columnMap = {
        'counsel': 0, 'status': 1, 'month': 2, 'case_type': 3, 'case_number': 4,
        'client_name': 5, 'resolution_type': 6, 'fee_rate': 7, 'settled_amount': 8,
        'pre_suit_demand': 9, 'difference': 10, 'legal_fee': 11, 'discounted_legal_fee': 12, 'commission': 13
    };

    const icons = document.querySelectorAll('#pendingTable .th-content.sortable .sort-icon');
    const idx = columnMap[activeColumn];
    if (icons[idx]) {
        icons[idx].textContent = pendingSortDirection === 'asc' ? '▲' : '▼';
        icons[idx].style.opacity = '1';
    }
}

function renderPendingCases() {
    const tbody = document.getElementById('pendingBody');
    const footerInfo = document.getElementById('pendingFooterInfo');
    const footerTotal = document.getElementById('pendingFooterTotal');

    if (pendingCases.length === 0) {
        tbody.innerHTML = `<tr><td colspan="12" style="padding: 32px 16px; text-align: center;" class="text-secondary">No pending cases</td></tr>`;
        if (footerInfo) footerInfo.textContent = 'Showing 0 cases';
        if (footerTotal) footerTotal.textContent = formatCurrency(0);
        return;
    }

    if (footerInfo) footerInfo.textContent = `Showing ${pendingCases.length} case${pendingCases.length !== 1 ? 's' : ''}`;

    tbody.innerHTML = pendingCases.map(c => `
        <tr onclick="viewPendingCaseDetail(${c.id})" data-case-id="${c.id}" style="cursor:pointer;">
            <td style="text-align:center;padding:6px 4px;" onclick="event.stopPropagation()">
                <input type="checkbox" class="pending-checkbox" value="${c.id}">
            </td>
            <td style="font-size:12px;padding:6px;">${c.counsel_name}</td>
            <td style="font-size:12px;padding:6px;">${c.month}</td>
            <td style="font-weight:600;font-size:12px;padding:6px;">${c.case_number}</td>
            <td style="font-size:12px;padding:6px;">${c.client_name}</td>
            <td style="text-align:right;font-weight:600;font-size:12px;padding:6px;">${formatCurrency(c.settled)}</td>
            <td style="text-align:right;font-size:12px;padding:6px;">${formatCurrency(c.presuit_offer || 0)}</td>
            <td style="text-align:right;font-size:12px;padding:6px;">${formatCurrency(c.difference || 0)}</td>
            <td style="text-align:right;font-size:12px;padding:6px;">${formatCurrency(c.legal_fee || 0)}</td>
            <td style="text-align:right;font-size:12px;padding:6px;">${formatCurrency(c.discounted_legal_fee)}</td>
            <td style="text-align:right;font-weight:700;color:#059669;font-size:12px;padding:6px;">${formatCurrency(c.commission)}</td>
            <td style="text-align:center;padding:6px;" onclick="event.stopPropagation()">
                <div class="action-group center">
                    <button class="act-btn approve" onclick="approveCase(${c.id})" title="Approve">Approve</button>
                    <button class="act-btn reject" onclick="rejectCase(${c.id})" title="Reject">Reject</button>
                </div>
            </td>
        </tr>
    `).join('');

    const totalCommission = pendingCases.reduce((sum, c) => sum + parseFloat(c.commission), 0);
    if (footerTotal) footerTotal.textContent = formatCurrency(totalCommission);
}

async function approveCase(id) {
    await processCase(id, 'approve');
}

async function rejectCase(id) {
    if (!confirm('Are you sure you want to reject this case?')) return;
    await processCase(id, 'reject');
}

async function processCase(id, action) {
    try {
        const result = await apiCall('api/approve.php', {
            method: 'POST',
            body: JSON.stringify({ case_id: id, action })
        });

        if (result.success) {
            loadPendingCases();
            loadStats();
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error processing case');
    }
}

function toggleSelectAll(type) {
    const checked = document.getElementById(`selectAll${type.charAt(0).toUpperCase() + type.slice(1)}`).checked;
    document.querySelectorAll(`.${type}-checkbox`).forEach(cb => cb.checked = checked);
}

async function deletePendingCase(id) {
    if (!confirm('Are you sure you want to delete this case?')) return;

    try {
        const result = await apiCall(`api/cases.php?id=${id}`, {
            method: 'DELETE'
        });

        if (result.success) {
            loadPendingCases();
            loadStats();
            alert('Case deleted successfully');
        } else {
            alert(result.error || 'Error deleting case');
        }
    } catch (err) {
        alert(err.message || 'Error deleting case');
    }
}

async function deleteFromEditModal() {
    if (!currentCaseId) return;
    if (!confirm('Are you sure you want to delete this case? This action cannot be undone.')) return;

    try {
        const result = await apiCall(`api/cases.php?id=${currentCaseId}`, {
            method: 'DELETE'
        });

        if (result.success) {
            closeEditModal();
            loadPendingCases();
            loadAllCases();
            loadStats();
            alert('Case deleted successfully');
        } else {
            alert(result.error || 'Error deleting case');
        }
    } catch (err) {
        alert(err.message || 'Error deleting case');
    }
}

async function bulkAction(action) {
    const selected = Array.from(document.querySelectorAll('.pending-checkbox:checked')).map(cb => parseInt(cb.value));

    if (selected.length === 0) {
        alert('Please select at least one case');
        return;
    }

    if (action === 'reject' && !confirm(`Are you sure you want to reject ${selected.length} cases?`)) {
        return;
    }

    try {
        const result = await apiCall('api/approve.php', {
            method: 'PUT',
            body: JSON.stringify({ case_ids: selected, action })
        });

        if (result.success) {
            loadPendingCases();
            loadStats();
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error processing cases');
    }
}
