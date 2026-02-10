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
        let cases = result.cases;
        if (monthFilter !== 'all') {
            cases = cases.filter(c => c.month && c.month.startsWith(monthFilter));
        }

        // Sort newest-to-oldest by month (Dec→Jan, then by year desc)
        const monthOrder = {Jan:1,Feb:2,Mar:3,Apr:4,May:5,Jun:6,Jul:7,Aug:8,Sep:9,Oct:10,Nov:11,Dec:12};
        cases.sort((a, b) => {
            const parseMonth = (m) => {
                if (!m) return {year: 0, month: 0};
                const parts = m.split('. ');
                return {year: parseInt(parts[1]) || 0, month: monthOrder[parts[0]] || 0};
            };
            const am = parseMonth(a.month);
            const bm = parseMonth(b.month);
            if (bm.year !== am.year) return bm.year - am.year;
            return bm.month - am.month;
        });

        commissionsData = cases;
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
        tbody.innerHTML = '<tr><td colspan="13" style="text-align:center; padding: 40px; color:#8b8fa3;">No commissions found</td></tr>';
        return;
    }

    // Group cases by month
    const groups = [];
    let currentMonth = null;
    let currentGroup = null;
    cases.forEach(c => {
        const m = c.month || 'Unknown';
        if (m !== currentMonth) {
            currentMonth = m;
            currentGroup = { month: m, cases: [], totalComm: 0, count: 0 };
            groups.push(currentGroup);
        }
        currentGroup.cases.push(c);
        currentGroup.totalComm += parseFloat(c.commission || 0);
        currentGroup.count++;
    });

    let html = '';
    groups.forEach(g => {
        // Month header row
        html += `<tr style="background:#f5f5f5; border-top:2px solid #e2e4ea;">
            <td style="width:0;padding:0;border:none;"></td>
            <td colspan="2" style="padding:8px 12px; font-weight:700; font-size:13px; color:#1a1a1a;">${escapeHtml(g.month)}</td>
            <td colspan="9" style="text-align:right; padding:8px 12px; font-size:12px; color:#6b7280;">${g.count} cases | <span style="font-weight:700; color:#0d9488;">${formatCurrency(g.totalComm)}</span></td>
            <td colspan="2"></td>
        </tr>`;

        // Case rows
        g.cases.forEach(c => {
            const isPaid = c.status === 'paid';
            const statusBadge = isPaid
                ? `<span class="ink-badge paid">PAID</span>`
                : `<span class="ink-badge unpaid clickable" onclick="toggleCommissionStatus(${c.id})" title="Click to mark paid">UNPAID</span>`;
            const checkIcon = c.check_received == 1
                ? `<span onclick="toggleCheckReceived(${c.id})" style="cursor:pointer;font-size:11px;font-weight:600;color:#059669;" title="Click to unmark">Received</span>`
                : `<span onclick="toggleCheckReceived(${c.id})" style="cursor:pointer;font-size:11px;font-weight:600;color:#d1d5db;" title="Click to mark received">Pending</span>`;

            const settled = parseFloat(c.settled || 0);
            const preSuitOffer = parseFloat(c.presuit_offer || 0);
            const difference = settled - preSuitOffer;
            const legalFee = parseFloat(c.legal_fee || 0);
            const discFee = parseFloat(c.discounted_legal_fee || 0);
            const commission = parseFloat(c.commission || 0);

            const resType = c.resolution_type || '-';
            let resBadge = '';
            const rt = resType.toLowerCase();
            if (rt.includes('demand')) {
                resBadge = '<span style="display:inline-block;width:8px;height:8px;background:#3b82f6;border-radius:50%;margin-right:6px;"></span>';
            } else if (rt.includes('mediation')) {
                resBadge = '<span style="display:inline-block;width:8px;height:8px;background:#d97706;border-radius:50%;margin-right:6px;"></span>';
            } else if (rt.includes('arb')) {
                resBadge = '<span style="display:inline-block;width:8px;height:8px;background:#8b5cf6;border-radius:50%;margin-right:6px;"></span>';
            } else if (rt.includes('file and bump') || rt.includes('bump')) {
                resBadge = '<span style="display:inline-block;width:8px;height:8px;background:#10b981;border-radius:50%;margin-right:6px;"></span>';
            } else if (rt.includes('no offer')) {
                resBadge = '<span style="display:inline-block;width:8px;height:8px;background:#f43f5e;border-radius:50%;margin-right:6px;"></span>';
            } else if (rt.includes('post dep')) {
                resBadge = '<span style="display:inline-block;width:8px;height:8px;background:#06b6d4;border-radius:50%;margin-right:6px;"></span>';
            } else if (rt.includes('referral')) {
                resBadge = '<span style="display:inline-block;width:8px;height:8px;background:#a855f7;border-radius:50%;margin-right:6px;"></span>';
            } else if (resType !== '-') {
                resBadge = '<span style="display:inline-block;width:8px;height:8px;background:#9ca3af;border-radius:50%;margin-right:6px;"></span>';
            }

            html += `
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
                <td style="text-align:center;">${checkIcon}</td>
                <td style="text-align:center;">
                    ${!isPaid ? `<div class="action-group center">
                        <button class="act-icon edit" onclick="editCommission(${c.id})" title="Edit">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="act-icon danger" onclick="deleteCommission(${c.id})" title="Delete">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>` : ''}
                </td>
            </tr>`;
        });
    });

    tbody.innerHTML = html;

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
    document.getElementById('editCommPreSuitOffer').value = c.presuit_offer || '';
    document.getElementById('editCommLegalFee').value = c.legal_fee || '';
    document.getElementById('editCommDiscountedFee').value = c.discounted_legal_fee || '';
    document.getElementById('editCommCommission').value = c.commission || '';
    document.getElementById('editCommMonth').value = c.month || '';
    document.getElementById('editCommStatus').value = c.status || 'unpaid';
    document.getElementById('editCommCheckReceived').checked = c.check_received == 1;

    document.getElementById('editCommissionModal').classList.add('show');
}

async function saveCommission() {
    const caseId = document.getElementById('editCommCaseId').value;
    const data = {
        client_name: document.getElementById('editCommClientName').value,
        resolution_type: document.getElementById('editCommResolutionType').value,
        settled: document.getElementById('editCommSettled').value,
        presuit_offer: document.getElementById('editCommPreSuitOffer').value,
        legal_fee: document.getElementById('editCommLegalFee').value,
        discounted_legal_fee: document.getElementById('editCommDiscountedFee').value,
        commission: document.getElementById('editCommCommission').value,
        month: document.getElementById('editCommMonth').value,
        status: document.getElementById('editCommStatus').value,
        check_received: document.getElementById('editCommCheckReceived').checked ? 1 : 0,
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

async function toggleCommissionStatus(caseId) {
    const c = commissionsData.find(item => item.id == caseId);
    if (!c) return;
    const newStatus = c.status === 'paid' ? 'unpaid' : 'paid';
    const result = await apiCall(`api/chong_cases.php?id=${caseId}`, 'PUT', {
        status: newStatus,
        csrf_token: csrfToken
    });
    if (result.success) {
        c.status = newStatus;
        renderCommissionsTable(commissionsData);
        loadDashboardStats();
    } else {
        showToast(result.error || 'Failed to update', 'error');
    }
}

async function toggleCheckReceived(caseId) {
    const c = commissionsData.find(item => item.id == caseId);
    if (!c) return;
    const newVal = c.check_received == 1 ? 0 : 1;
    const result = await apiCall(`api/chong_cases.php?id=${caseId}`, 'PUT', {
        check_received: newVal,
        csrf_token: csrfToken
    });
    if (result.success) {
        c.check_received = newVal;
        renderCommissionsTable(commissionsData);
    } else {
        showToast(result.error || 'Failed to update', 'error');
    }
}
