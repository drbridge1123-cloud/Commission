/**
 * ChongDashboard - Commissions tab functions.
 */

function setCommissionFilter(filterType, value, btn) {
    const chips = btn.parentElement.querySelectorAll(`.f-chip[data-filter="${filterType}"]`);
    chips.forEach(c => c.classList.remove('active'));
    btn.classList.add('active');

    currentCommissionStatus = value;
    loadCommissions();
}

async function loadCommissions() {
    const status = currentCommissionStatus;
    const year = document.getElementById('commissionYearFilter').value;
    const monthFilter = document.getElementById('commissionMonthFilter').value;

    let url = `api/chong_cases.php?phase=settled&status=${status}`;
    if (year !== 'all') url += `&year=${year}`;

    const result = await apiCall(url);
    if (result.cases) {
        if (monthFilter !== 'all') {
            commissionsData = result.cases.filter(c => c.month && c.month.startsWith(monthFilter));
        } else {
            commissionsData = result.cases;
        }
        renderCommissionsTable(commissionsData);
    }
}

function renderCommissionsTable(cases) {
    const tbody = document.getElementById('commissionsTableBody');

    const totalCommission = cases.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);
    const paidCommission = cases.filter(c => c.status === 'paid').reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);
    const unpaidCommission = cases.filter(c => c.status === 'unpaid').reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);

    document.getElementById('commStatCases').textContent = cases.length;
    document.getElementById('commStatTotal').textContent = formatCurrency(totalCommission);
    document.getElementById('commStatPaid').textContent = formatCurrency(paidCommission);
    document.getElementById('commStatUnpaid').textContent = formatCurrency(unpaidCommission);

    if (cases.length === 0) {
        tbody.innerHTML = '<tr><td colspan="11" style="text-align:center; padding: 40px; color:#8b8fa3;">No commissions found</td></tr>';
        return;
    }

    tbody.innerHTML = cases.map(c => {
        const statusBadge = c.status === 'paid'
            ? '<span class="ink-badge paid">PAID</span>'
            : '<span class="ink-badge unpaid">UNPAID</span>';

        const settled = parseFloat(c.settled || 0);
        const preSuitOffer = parseFloat(c.pre_suit_offer || 0);
        const difference = settled - preSuitOffer;
        const legalFee = parseFloat(c.legal_fee || 0);
        const discFee = parseFloat(c.discounted_legal_fee || 0);
        const commission = parseFloat(c.commission || 0);

        const resType = c.resolution_type || '-';
        let resBadge = '';
        if (resType.toLowerCase().includes('demand')) {
            resBadge = '<span style="display:inline-block;width:8px;height:8px;background:#3b82f6;border-radius:50%;margin-right:6px;"></span>';
        } else if (resType.toLowerCase().includes('mediation')) {
            resBadge = '<span style="display:inline-block;width:8px;height:8px;background:#d97706;border-radius:50%;margin-right:6px;"></span>';
        } else if (resType.toLowerCase().includes('arb')) {
            resBadge = '<span style="display:inline-block;width:8px;height:8px;background:#8b5cf6;border-radius:50%;margin-right:6px;"></span>';
        }

        return `
            <tr>
                <td style="width:0;padding:0;border:none;"></td>
                <td>${resBadge}${escapeHtml(resType)}</td>
                <td>${escapeHtml(c.client_name)}</td>
                <td style="text-align:right; font-weight:600;">${formatCurrency(settled)}</td>
                <td style="text-align:right; color:#8b8fa3;">${preSuitOffer > 0 ? formatCurrency(preSuitOffer) : '—'}</td>
                <td style="text-align:right;">${difference > 0 ? formatCurrency(difference) : '—'}</td>
                <td style="text-align:right;">${formatCurrency(legalFee)}</td>
                <td style="text-align:right;">${formatCurrency(discFee)}</td>
                <td style="text-align:right; font-weight:700; color:#0d9488;">${formatCurrency(commission)}</td>
                <td>${escapeHtml(c.month || '-')}</td>
                <td style="text-align:center;">${statusBadge}</td>
                <td style="text-align:center;">
                    <div class="action-group center">
                        <button class="act-icon edit" onclick="editCommission(${c.id})" title="Edit">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="act-icon danger" onclick="deleteCommission(${c.id})" title="Delete">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    document.getElementById('commTableCount').textContent = `${cases.length} cases`;
    document.getElementById('commTableTotal').textContent = formatCurrency(totalCommission);
    document.getElementById('commTablePaid').textContent = formatCurrency(paidCommission);
    document.getElementById('commTableUnpaid').textContent = formatCurrency(unpaidCommission);
}

function filterCommissions() {
    const search = document.getElementById('commissionSearch').value.toLowerCase();
    const filtered = commissionsData.filter(c =>
        c.case_number.toLowerCase().includes(search) ||
        c.client_name.toLowerCase().includes(search)
    );
    renderCommissionsTable(filtered);
}

function sortCommissions(column) {
    if (commissionsSortColumn === column) {
        commissionsSortDir = commissionsSortDir === 'asc' ? 'desc' : 'asc';
    } else {
        commissionsSortColumn = column;
        commissionsSortDir = 'asc';
    }

    document.querySelectorAll('#commissionsTable th.sortable').forEach(th => {
        th.classList.remove('asc', 'desc');
        if (th.dataset.sort === column) {
            th.classList.add(commissionsSortDir);
        }
    });

    const numericCols = ['settled', 'commission'];
    commissionsData.sort((a, b) => {
        let valA = a[column] ?? '';
        let valB = b[column] ?? '';

        if (numericCols.includes(column)) {
            valA = parseFloat(valA) || 0;
            valB = parseFloat(valB) || 0;
        } else {
            valA = String(valA).toLowerCase();
            valB = String(valB).toLowerCase();
        }

        if (valA < valB) return commissionsSortDir === 'asc' ? -1 : 1;
        if (valA > valB) return commissionsSortDir === 'asc' ? 1 : -1;
        return 0;
    });

    filterCommissions();
}

function editCommission(caseId) {
    const c = commissionsData.find(item => item.id == caseId);
    if (!c) return;

    document.getElementById('editCommCaseId').value = c.id;
    document.getElementById('editCommClientName').value = c.client_name || '';
    document.getElementById('editCommResolutionType').value = c.resolution_type || '';
    document.getElementById('editCommSettled').value = c.settled || '';
    document.getElementById('editCommPreSuitOffer').value = c.pre_suit_offer || '';
    document.getElementById('editCommLegalFee').value = c.legal_fee || '';
    document.getElementById('editCommDiscountedFee').value = c.discounted_legal_fee || '';
    document.getElementById('editCommCommission').value = c.commission || '';
    document.getElementById('editCommMonth').value = c.month || '';

    document.getElementById('editCommissionModal').classList.add('show');
}

async function saveCommission() {
    const caseId = document.getElementById('editCommCaseId').value;
    const data = {
        client_name: document.getElementById('editCommClientName').value,
        resolution_type: document.getElementById('editCommResolutionType').value,
        settled: document.getElementById('editCommSettled').value,
        pre_suit_offer: document.getElementById('editCommPreSuitOffer').value,
        legal_fee: document.getElementById('editCommLegalFee').value,
        discounted_legal_fee: document.getElementById('editCommDiscountedFee').value,
        commission: document.getElementById('editCommCommission').value,
        month: document.getElementById('editCommMonth').value,
        csrf_token: csrfToken
    };

    const result = await apiCall(`api/chong_cases.php?id=${caseId}`, 'PUT', data);

    if (result.success) {
        showToast('Commission updated', 'success');
        document.getElementById('editCommissionModal').classList.remove('show');
        loadCommissions();
        loadDashboardStats();
    } else {
        showToast(result.error || 'Failed to update', 'error');
    }
}

async function deleteCommission(caseId) {
    if (!confirm('Are you sure you want to delete this commission? This cannot be undone.')) return;

    const result = await apiCall(`api/chong_cases.php?id=${caseId}`, 'DELETE', { csrf_token: csrfToken });

    if (result.success) {
        showToast('Commission deleted', 'success');
        loadCommissions();
        loadDashboardStats();
    } else {
        showToast(result.error || 'Failed to delete', 'error');
    }
}

function exportCommissionsToExcel() {
    const data = commissionsData.map(c => ({
        'Case #': c.case_number,
        'Client': c.client_name,
        'Resolution': c.resolution_type || '',
        'Settled': c.settled || 0,
        'Commission': c.commission || 0,
        'Month': c.month || '',
        'Status': c.status
    }));

    let csv = Object.keys(data[0] || {}).join(',') + '\n';
    data.forEach(row => {
        csv += Object.values(row).map(v => `"${v}"`).join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `commissions_${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
}
