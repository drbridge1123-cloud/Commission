/**
 * ChongDashboard - Traffic tab functions (cases, commission, requests sub-tabs).
 * V3 compact layout — no sidebar, dropdown-based filters.
 */

// ============================================
// Traffic Sub-Tab System (V3 Pills)
// ============================================

function switchTrafficSubTab(subTab) {
    currentTrafficSubTab = subTab;
    ['cases', 'commission', 'requests'].forEach(t => {
        const panel = document.getElementById('trafficSubContent-' + t);
        if (panel) panel.style.display = t === subTab ? '' : 'none';
        const btn = document.getElementById('tv3PillTab-' + t);
        if (btn) btn.classList.toggle('active', t === subTab);
    });
    if (subTab === 'cases') {
        filterTrafficCases();
    } else if (subTab === 'commission') {
        updateCommissionStats();
        filterCommTraffic();
    } else if (subTab === 'requests') {
        loadAllTrafficRequests();
    }
}

// ============================================
// Traffic Cases — Load, Filter, Render
// ============================================

async function loadTrafficCases() {
    try {
        const result = await apiCall('api/traffic.php');
        if (result.cases) {
            trafficCasesData = result.cases;
            updateTrafficStats(trafficCasesData);
            populateTrafficViewDropdowns();
            filterTrafficCases();
            if (currentTrafficSubTab === 'commission') {
                updateCommissionStats();
                filterCommTraffic();
            }
        }
    } catch (err) {
        console.error('Error loading traffic cases:', err);
    }
}

function populateTrafficViewDropdowns() {
    // Populate sub-filter dropdown options based on current data
    // Options are built dynamically when view changes
}

function onTrafficViewChange() {
    const view = document.getElementById('tv3ViewFilter').value;
    const subGroup = document.getElementById('tv3SubFilterGroup');
    const subLabel = document.getElementById('tv3SubFilterLabel');
    const subSelect = document.getElementById('tv3SubFilter');

    if (view === 'all') {
        subGroup.style.display = 'none';
        filterTrafficCases();
        return;
    }

    subGroup.style.display = '';
    let items = [];

    if (view === 'referral') {
        subLabel.textContent = 'Requester';
        const refs = {};
        trafficCasesData.forEach(c => {
            const ref = c.referral_source || 'Unknown';
            refs[ref] = (refs[ref] || 0) + 1;
        });
        items = Object.entries(refs).sort((a, b) => b[1] - a[1]);
    } else if (view === 'court') {
        subLabel.textContent = 'Court';
        const courts = {};
        trafficCasesData.forEach(c => {
            const court = c.court || 'Unknown';
            courts[court] = (courts[court] || 0) + 1;
        });
        items = Object.entries(courts).sort((a, b) => a[0].localeCompare(b[0]));
    } else if (view === 'year') {
        subLabel.textContent = 'Year';
        const years = {};
        trafficCasesData.forEach(c => {
            const year = c.court_date ? c.court_date.substring(0, 4) : 'Unknown';
            years[year] = (years[year] || 0) + 1;
        });
        items = Object.entries(years).sort((a, b) => b[0].localeCompare(a[0]));
    }

    subSelect.innerHTML = '<option value="all">All</option>' +
        items.map(([name, count]) =>
            `<option value="${escapeHtml(name)}">${escapeHtml(name)} (${count})</option>`
        ).join('');

    filterTrafficCases();
}

function filterTrafficCases() {
    const search = (document.getElementById('trafficSearch').value || '').toLowerCase();
    const statusFilter = document.getElementById('tv3StatusFilter')?.value || 'active';
    const viewFilter = document.getElementById('tv3ViewFilter')?.value || 'all';
    const subFilter = document.getElementById('tv3SubFilter')?.value || 'all';

    // Update state for external references
    currentTrafficStatusFilter = statusFilter;

    let filtered = trafficCasesData;

    // Status filter
    if (statusFilter === 'active') {
        filtered = filtered.filter(c => !c.disposition || c.disposition === 'pending');
    } else if (statusFilter === 'done') {
        filtered = filtered.filter(c => c.disposition === 'dismissed' || c.disposition === 'amended');
    }

    // View sub-filter
    if (viewFilter !== 'all' && subFilter !== 'all') {
        filtered = filtered.filter(c => {
            if (viewFilter === 'referral') {
                return (c.referral_source || 'Unknown') === subFilter;
            } else if (viewFilter === 'court') {
                return (c.court || 'Unknown') === subFilter;
            } else if (viewFilter === 'year') {
                const year = c.court_date ? c.court_date.substring(0, 4) : 'Unknown';
                return year === subFilter;
            }
            return true;
        });
    }

    // Search
    if (search) {
        filtered = filtered.filter(c =>
            (c.client_name || '').toLowerCase().includes(search) ||
            (c.case_number || '').toLowerCase().includes(search) ||
            (c.court || '').toLowerCase().includes(search) ||
            (c.charge || '').toLowerCase().includes(search)
        );
    }

    renderTrafficTable(filtered);
    updateTV3CasesFooter(filtered);
}

function updateTrafficStats(cases) {
    const active = cases.filter(c => !c.disposition || c.disposition === 'pending').length;
    const dismissed = cases.filter(c => c.disposition === 'dismissed').length;
    const amended = cases.filter(c => c.disposition === 'amended').length;
    const totalComm = cases.reduce((sum, c) => sum + getTrafficCommission(c.disposition), 0);

    document.getElementById('trafficActive').textContent = active;
    document.getElementById('trafficDismissed').textContent = dismissed;
    document.getElementById('trafficAmended').textContent = amended;
    document.getElementById('trafficCommission').textContent = formatCurrency(totalComm);
}

function renderTrafficTable(cases) {
    const tbody = document.getElementById('trafficTableBody');
    if (!cases || cases.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="tv3-empty">No traffic cases</td></tr>';
        document.getElementById('trafficCaseCount').textContent = '0';
        return;
    }

    tbody.innerHTML = cases.map(c => {
        const discoveryBadge = c.discovery == 1
            ? '<span class="tv3-badge dismissed">Received</span>'
            : '';
        const statusBadge = c.status === 'resolved'
            ? '<span class="tv3-badge dismissed">Resolved</span>'
            : '<span class="tv3-badge active">Active</span>';

        return `
            <tr onclick="openTrafficModal(trafficCasesData.find(x => x.id == ${c.id}))">
                <td>${escapeHtml(c.client_name)}</td>
                <td style="font-family: monospace; font-size: 12px;">${escapeHtml(c.case_number || '-')}</td>
                <td>${escapeHtml(c.court || '-')}</td>
                <td>${escapeHtml(c.charge || '-')}</td>
                <td>${c.court_date ? formatDate(c.court_date) : '-'}</td>
                <td>${c.noa_sent_date || '-'}</td>
                <td style="text-align:center;">${discoveryBadge}</td>
                <td style="text-align:center;">${statusBadge}</td>
                <td>${escapeHtml(c.referral_source || '-')}</td>
                <td style="text-align:center;" onclick="event.stopPropagation();">
                    <div style="display:flex; gap:4px; justify-content:center;">
                        <button class="tv3-icon-btn" onclick="downloadTrafficCasePDF(${c.id})" title="Download PDF">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                        </button>
                        <button class="tv3-icon-btn danger" onclick="deleteTrafficCase(${c.id})" title="Delete">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    document.getElementById('trafficCaseCount').textContent = cases.length;
}

function updateTV3CasesFooter(cases) {
    const dismissed = cases.filter(c => c.disposition === 'dismissed').length;
    const amended = cases.filter(c => c.disposition === 'amended').length;
    const elDismissed = document.getElementById('tv3FootDismissed');
    const elAmended = document.getElementById('tv3FootAmended');
    if (elDismissed) elDismissed.textContent = dismissed;
    if (elAmended) elAmended.textContent = amended;
}

// ============================================
// Traffic Commission Sub-Tab
// ============================================

function updateCommissionStats() {
    const commCases = trafficCasesData.filter(c =>
        c.disposition === 'dismissed' || c.disposition === 'amended'
    );
    const total = commCases.reduce((sum, c) => sum + getTrafficCommission(c.disposition), 0);
    const paid = commCases.filter(c => c.paid == 1);
    const unpaid = commCases.filter(c => c.paid != 1);
    const paidTotal = paid.reduce((sum, c) => sum + getTrafficCommission(c.disposition), 0);
    const unpaidTotal = unpaid.reduce((sum, c) => sum + getTrafficCommission(c.disposition), 0);

    document.getElementById('commTotalCommission').textContent = formatCurrency(total);
    document.getElementById('commPaidTotal').textContent = formatCurrency(paidTotal);
    document.getElementById('commUnpaidTotal').textContent = formatCurrency(unpaidTotal);
    document.getElementById('commCaseCount').textContent = commCases.length;

    populateCommissionDropdowns(commCases);
}

function populateCommissionDropdowns(cases) {
    const months = new Set();
    const years = new Set();
    cases.forEach(c => {
        if (c.court_date) {
            const d = new Date(c.court_date);
            if (!isNaN(d)) {
                years.add(d.getFullYear().toString());
                months.add(d.getMonth().toString());
            }
        }
    });
    const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    const monthSelect = document.getElementById('commMonthFilter');
    const curMonth = monthSelect.value;
    monthSelect.innerHTML = '<option value="">All Months</option>' +
        [...months].sort((a,b) => a-b).map(m =>
            `<option value="${m}"${m === curMonth ? ' selected' : ''}>${monthNames[parseInt(m)]}</option>`
        ).join('');

    const yearSelect = document.getElementById('commYearFilter');
    const curYear = yearSelect.value;
    yearSelect.innerHTML = '<option value="">All Years</option>' +
        [...years].sort().reverse().map(y =>
            `<option value="${y}"${y === curYear ? ' selected' : ''}>${y}</option>`
        ).join('');
}

function setCommTrafficFilter(filter) {
    currentCommTrafficFilter = filter;
    filterCommTraffic();
}

function filterCommTraffic() {
    const search = (document.getElementById('commTrafficSearch').value || '').toLowerCase();
    const monthVal = document.getElementById('commMonthFilter').value;
    const yearVal = document.getElementById('commYearFilter').value;

    let filtered = trafficCasesData.filter(c =>
        c.disposition === 'dismissed' || c.disposition === 'amended'
    );

    if (currentCommTrafficFilter === 'paid') {
        filtered = filtered.filter(c => c.paid == 1);
    } else if (currentCommTrafficFilter === 'unpaid') {
        filtered = filtered.filter(c => c.paid != 1);
    }

    if (monthVal !== '') {
        filtered = filtered.filter(c => {
            if (!c.court_date) return false;
            return new Date(c.court_date).getMonth().toString() === monthVal;
        });
    }
    if (yearVal) {
        filtered = filtered.filter(c => {
            if (!c.court_date) return false;
            return new Date(c.court_date).getFullYear().toString() === yearVal;
        });
    }
    if (search) {
        filtered = filtered.filter(c =>
            (c.client_name || '').toLowerCase().includes(search) ||
            (c.court || '').toLowerCase().includes(search) ||
            (c.referral_source || '').toLowerCase().includes(search)
        );
    }

    renderCommissionTable(filtered);
}

function renderCommissionTable(cases) {
    const tbody = document.getElementById('commTrafficTableBody');
    if (!cases || !cases.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="tv3-empty">No commission records</td></tr>';
        document.getElementById('commTrafficCaseCount').textContent = '0 cases';
        document.getElementById('commTrafficTotal').textContent = '$0.00';
        return;
    }

    const sorted = [...cases].sort((a, b) => {
        let valA = a[commTrafficSortColumn] ?? '';
        let valB = b[commTrafficSortColumn] ?? '';
        if (commTrafficSortColumn === 'commission') {
            valA = getTrafficCommission(a.disposition);
            valB = getTrafficCommission(b.disposition);
        } else if (commTrafficSortColumn === 'paid') {
            valA = a.paid ? 1 : 0;
            valB = b.paid ? 1 : 0;
        } else if (commTrafficSortColumn === 'court_date' || commTrafficSortColumn === 'resolved_at') {
            valA = valA ? new Date(valA).getTime() : 0;
            valB = valB ? new Date(valB).getTime() : 0;
        } else {
            valA = String(valA).toLowerCase();
            valB = String(valB).toLowerCase();
        }
        if (valA < valB) return commTrafficSortDir === 'asc' ? -1 : 1;
        if (valA > valB) return commTrafficSortDir === 'asc' ? 1 : -1;
        return 0;
    });

    tbody.innerHTML = sorted.map(c => {
        const commission = getTrafficCommission(c.disposition);
        const dispBadge = c.disposition === 'dismissed'
            ? '<span class="tv3-badge dismissed">dismissed</span>'
            : '<span class="tv3-badge amended">amended</span>';
        const paidBadge = c.paid == 1
            ? `<span class="tv3-badge paid" onclick="event.stopPropagation(); toggleTrafficPaid(${c.id}, 0)">PAID</span>`
            : `<span class="tv3-badge unpaid" onclick="event.stopPropagation(); toggleTrafficPaid(${c.id}, 1)">UNPAID</span>`;

        return `
            <tr onclick="openTrafficModal(trafficCasesData.find(x => x.id == ${c.id}))">
                <td>${escapeHtml(c.client_name)}</td>
                <td>${escapeHtml(c.court || '-')}</td>
                <td>${c.court_date ? formatDate(c.court_date) : '-'}</td>
                <td>${c.resolved_at ? formatDate(c.resolved_at) : '-'}</td>
                <td>${dispBadge}</td>
                <td>${escapeHtml(c.referral_source || '-')}</td>
                <td class="r" style="font-weight:600; color:#059669;">${formatCurrency(commission)}</td>
                <td style="text-align:center;">${paidBadge}</td>
            </tr>
        `;
    }).join('');

    const total = sorted.reduce((sum, c) => sum + getTrafficCommission(c.disposition), 0);
    document.getElementById('commTrafficCaseCount').textContent = sorted.length + ' cases';
    document.getElementById('commTrafficTotal').textContent = formatCurrency(total);
}

function sortCommTraffic(column) {
    if (commTrafficSortColumn === column) {
        commTrafficSortDir = commTrafficSortDir === 'asc' ? 'desc' : 'asc';
    } else {
        commTrafficSortColumn = column;
        commTrafficSortDir = 'asc';
    }
    filterCommTraffic();
}

async function toggleTrafficPaid(caseId, newPaidValue) {
    const caseData = trafficCasesData.find(c => c.id == caseId);
    if (!caseData) return;

    const data = {
        client_name: caseData.client_name,
        client_phone: caseData.client_phone || '',
        court: caseData.court || '',
        court_date: caseData.court_date || '',
        charge: caseData.charge || '',
        case_number: caseData.case_number || '',
        prosecutor_offer: caseData.prosecutor_offer || '',
        disposition: caseData.disposition || 'pending',
        status: caseData.status || 'active',
        referral_source: caseData.referral_source || '',
        noa_sent_date: caseData.noa_sent_date || '',
        discovery: caseData.discovery == 1,
        paid: newPaidValue == 1,
        note: caseData.note || ''
    };

    data.id = caseId;
    const result = await apiCall('api/traffic.php', 'PUT', data);
    if (result.success) {
        caseData.paid = newPaidValue;
        showToast(newPaidValue ? 'Marked as paid' : 'Marked as unpaid', 'success');
        updateCommissionStats();
        filterCommTraffic();
    } else {
        showToast(result.error || 'Failed to update', 'error');
    }
}

function exportTrafficCommissions() {
    const commCases = trafficCasesData.filter(c =>
        c.disposition === 'dismissed' || c.disposition === 'amended'
    );
    if (!commCases.length) { showToast('No data to export', 'error'); return; }

    const data = commCases.map(c => ({
        'Client': c.client_name,
        'Court': c.court || '',
        'Court Date': c.court_date ? formatDate(c.court_date) : '',
        'Requester': c.referral_source || '',
        'Disposition': c.disposition,
        'Commission': getTrafficCommission(c.disposition),
        'Paid': c.paid == 1 ? 'Yes' : 'No'
    }));

    let csv = Object.keys(data[0]).join(',') + '\n';
    data.forEach(row => {
        csv += Object.values(row).map(v => `"${v}"`).join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `traffic_commissions_${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}

// ============================================
// Traffic Requests Sub-Tab
// ============================================

async function loadAllTrafficRequests() {
    try {
        const result = await apiCall('api/traffic_requests.php');
        allTrafficRequests = result.requests || [];
        pendingTrafficRequests = allTrafficRequests.filter(r => r.status === 'pending');

        const badge = document.getElementById('trafficBadge');
        if (pendingTrafficRequests.length > 0) {
            badge.textContent = pendingTrafficRequests.length;
            badge.style.display = 'inline';
        } else {
            badge.style.display = 'none';
        }

        const subBadge = document.getElementById('requestsSubTabBadge');
        if (pendingTrafficRequests.length > 0) {
            subBadge.textContent = pendingTrafficRequests.length;
            subBadge.style.display = 'inline';
        } else {
            subBadge.style.display = 'none';
        }

        renderPendingRequestsInCases();
        updateRequestStats();
        filterRequests();
    } catch (err) {
        console.error('Error loading traffic requests:', err);
    }
}

function renderPendingRequestsInCases() {
    const section = document.getElementById('pendingRequestsSection');
    const container = document.getElementById('pendingRequestsCards');

    if (!pendingTrafficRequests.length) {
        section.style.display = 'none';
        return;
    }

    section.style.display = 'block';
    const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '\u2014';

    container.innerHTML = pendingTrafficRequests.map(r => {
        const reqName = (r.referral_source || r.requester_name || '').replace(/\s*\(.*?\)\s*$/, '');
        return `
            <div class="tv3-pending-card">
                <div class="tv3-pending-card-grid">
                    <div>
                        <div class="tv3-pending-label">Requester</div>
                        <div class="tv3-pending-val">${escapeHtml(reqName || '\u2014')}</div>
                    </div>
                    <div>
                        <div class="tv3-pending-label">Client</div>
                        <div class="tv3-pending-val">${escapeHtml(r.client_name)}</div>
                    </div>
                    <div>
                        <div class="tv3-pending-label">Court</div>
                        <div class="tv3-pending-val dim">${escapeHtml(r.court || '\u2014')}</div>
                    </div>
                    <div>
                        <div class="tv3-pending-label">Ticket Issued</div>
                        <div class="tv3-pending-val dim">${fmtDate(r.citation_issued_date)}</div>
                    </div>
                    <div>
                        <div class="tv3-pending-label">Court Date</div>
                        <div class="tv3-pending-val dim">${fmtDate(r.court_date)}</div>
                    </div>
                    <div>
                        <div class="tv3-pending-label">Charge</div>
                        <div class="tv3-pending-val dim" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${escapeHtml(r.charge || '')}">${escapeHtml(r.charge || '\u2014')}</div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button class="tv3-btn-accept" onclick="acceptTrafficRequest(${r.id})">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        Accept
                    </button>
                    <button class="tv3-btn-deny" onclick="denyTrafficRequest(${r.id}, '${escapeJs(r.client_name)}')">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        Deny
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

function updateRequestStats() {
    const pending = allTrafficRequests.filter(r => r.status === 'pending').length;
    const accepted = allTrafficRequests.filter(r => r.status === 'accepted').length;
    const denied = allTrafficRequests.filter(r => r.status === 'denied').length;

    document.getElementById('reqPendingCount').textContent = pending;
    document.getElementById('reqAcceptedCount').textContent = accepted;
    document.getElementById('reqDeniedCount').textContent = denied;
    document.getElementById('reqTotalCount').textContent = allTrafficRequests.length;
}

function setRequestFilter(filter) {
    currentRequestFilter = filter;
    filterRequests();
}

function filterRequests() {
    const search = (document.getElementById('requestsSearch').value || '').toLowerCase();
    let filtered = allTrafficRequests;

    if (currentRequestFilter !== 'all') {
        filtered = filtered.filter(r => r.status === currentRequestFilter);
    }
    if (search) {
        filtered = filtered.filter(r =>
            (r.client_name || '').toLowerCase().includes(search) ||
            (r.court || '').toLowerCase().includes(search) ||
            (r.requester_name || '').toLowerCase().includes(search)
        );
    }
    renderAllRequests(filtered);
}

function renderAllRequests(requests) {
    const tbody = document.getElementById('requestsTableBody');
    if (!requests.length) {
        tbody.innerHTML = '<tr><td colspan="11" class="tv3-empty">No requests found</td></tr>';
        document.getElementById('requestsCaseCount').textContent = '0 requests';
        return;
    }

    tbody.innerHTML = requests.map(r => {
        const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
        const courtDate = fmtDate(r.court_date);
        const created = fmtDate(r.created_at);

        const statusClass = r.status === 'pending' ? 'pending'
            : r.status === 'accepted' ? 'accepted' : 'denied';
        const statusBadge = `<span class="tv3-badge ${statusClass}">${r.status}</span>`;

        const actions = r.status === 'pending' ? `
            <div style="display:flex; gap:4px; justify-content:center;">
                <button class="tv3-btn-accept" onclick="acceptTrafficRequest(${r.id})" title="Accept">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                </button>
                <button class="tv3-btn-deny" onclick="denyTrafficRequest(${r.id}, '${escapeJs(r.client_name)}')" title="Deny">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        ` : '<span class="tv3-dim">\u2014</span>';

        const noteText = r.status === 'denied' && r.deny_reason
            ? '<span style="color:#dc2626;">' + escapeHtml(r.deny_reason) + '</span>'
            : (r.note ? escapeHtml(r.note) : '<span class="tv3-dim">\u2014</span>');

        const ov = 'white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:120px;';
        const reqName = (r.referral_source || r.requester_name || '').replace(/\s*\(.*?\)\s*$/, '');
        return `
            <tr>
                <td style="${ov}" title="${escapeHtml(reqName)}">${escapeHtml(reqName || '\u2014')}</td>
                <td style="font-size:12px;">${created || '\u2014'}</td>
                <td style="font-weight:600;">${escapeHtml(r.client_name)}</td>
                <td style="font-size:12px;">${escapeHtml(r.client_phone || '\u2014')}</td>
                <td style="${ov}" title="${escapeHtml(r.court || '')}">${escapeHtml(r.court || '\u2014')}</td>
                <td style="${ov}" title="${escapeHtml(r.charge || '')}">${escapeHtml(r.charge || '\u2014')}</td>
                <td style="font-family:monospace; font-size:11px;">${escapeHtml(r.case_number || '\u2014')}</td>
                <td style="font-size:12px;">${courtDate || '\u2014'}</td>
                <td style="text-align:center;">${statusBadge}</td>
                <td style="font-size:11px; ${ov}" title="${escapeHtml(r.note || r.deny_reason || '')}">${noteText}</td>
                <td style="text-align:center;">${actions}</td>
            </tr>
        `;
    }).join('');

    document.getElementById('requestsCaseCount').textContent = requests.length + ' requests';
}

async function acceptTrafficRequest(id) {
    try {
        const result = await apiCall('api/traffic_requests.php', 'PUT', { id, action: 'accept' });
        if (result.success) {
            loadAllTrafficRequests();
            loadTrafficCases();
        } else {
            alert(result.error || 'Error accepting request');
        }
    } catch (err) {
        console.error('Error accepting request:', err);
        alert('Error accepting request');
    }
}

async function denyTrafficRequest(id, clientName) {
    const reason = prompt(`Reason for denying "${clientName}":`);
    if (reason === null) return;
    if (!reason.trim()) {
        alert('Deny reason is required');
        return;
    }
    try {
        const result = await apiCall('api/traffic_requests.php', 'PUT', { id, action: 'deny', deny_reason: reason.trim() });
        if (result.success) {
            loadAllTrafficRequests();
        } else {
            alert(result.error || 'Error denying request');
        }
    } catch (err) {
        console.error('Error denying request:', err);
        alert('Error denying request');
    }
}

// ============================================
// Traffic Case CRUD
// ============================================

function openTrafficModal(caseData = null) {
    document.getElementById('trafficForm').reset();
    document.getElementById('trafficCaseId').value = '';
    document.getElementById('trafficModalTitle').textContent = 'Add Traffic Case';
    document.getElementById('trafficCommissionDisplay').textContent = '$0.00';

    document.getElementById('trafficFilesSection').style.display = 'none';
    document.getElementById('trafficFilesList').innerHTML = '<div style="padding: 16px; text-align: center; color: #9ca3af; font-size: 13px;">No files attached</div>';

    if (caseData) {
        document.getElementById('trafficModalTitle').textContent = 'Edit Traffic Case';
        document.getElementById('trafficCaseId').value = caseData.id;
        document.getElementById('trafficClientName').value = caseData.client_name || '';
        document.getElementById('trafficClientPhone').value = caseData.client_phone || '';
        document.getElementById('trafficCourt').value = caseData.court || '';
        document.getElementById('trafficCourtDate').value = caseData.court_date ? caseData.court_date.replace(' ', 'T').substring(0, 16) : '';
        document.getElementById('trafficCharge').value = caseData.charge || '';
        document.getElementById('trafficCaseNumber').value = caseData.case_number || '';
        document.getElementById('trafficOffer').value = caseData.prosecutor_offer || '';
        document.getElementById('trafficDisposition').value = caseData.disposition || 'pending';
        document.getElementById('trafficStatus').value = caseData.status || 'active';
        document.getElementById('trafficReferralSource').value = caseData.referral_source || '';
        document.getElementById('trafficNoaSentDate').value = caseData.noa_sent_date || '';
        document.getElementById('trafficDiscovery').checked = caseData.discovery_received == 1;
        document.getElementById('trafficPaid').checked = caseData.paid == 1;
        document.getElementById('trafficNote').value = caseData.note || '';
        updateTrafficCommission();

        document.getElementById('trafficFilesSection').style.display = '';
        loadTrafficFiles(caseData.id);
    }

    openModal('trafficModal');
}

function editTrafficCase(id) {
    const caseData = trafficCasesData.find(c => c.id == id);
    if (caseData) {
        openTrafficModal(caseData);
    }
}

function updateTrafficCommission() {
    const disposition = document.getElementById('trafficDisposition').value;
    const commission = getTrafficCommission(disposition);
    document.getElementById('trafficCommissionDisplay').textContent = formatCurrency(commission);
}

async function submitTrafficCase(event) {
    event.preventDefault();

    const caseId = document.getElementById('trafficCaseId').value;
    const data = {
        client_name: document.getElementById('trafficClientName').value,
        client_phone: document.getElementById('trafficClientPhone').value,
        court: document.getElementById('trafficCourt').value,
        court_date: document.getElementById('trafficCourtDate').value,
        charge: document.getElementById('trafficCharge').value,
        case_number: document.getElementById('trafficCaseNumber').value,
        prosecutor_offer: document.getElementById('trafficOffer').value,
        disposition: document.getElementById('trafficDisposition').value,
        status: document.getElementById('trafficStatus').value,
        referral_source: document.getElementById('trafficReferralSource').value,
        noa_sent_date: document.getElementById('trafficNoaSentDate').value,
        discovery_received: document.getElementById('trafficDiscovery').checked,
        paid: document.getElementById('trafficPaid').checked,
        note: document.getElementById('trafficNote').value
    };

    const method = caseId ? 'PUT' : 'POST';
    const url = 'api/traffic.php';
    if (caseId) data.id = caseId;

    const result = await apiCall(url, method, data);
    if (result.success) {
        closeModal('trafficModal');
        loadTrafficCases();
        alert(caseId ? 'Traffic case updated!' : 'Traffic case added!');
    } else {
        alert('Error: ' + (result.error || 'Failed to save'));
    }
}

async function deleteTrafficCase(caseId) {
    if (!confirm('Are you sure you want to delete this traffic case?')) return;

    const result = await apiCall('api/traffic.php', 'DELETE', { id: caseId });
    if (result.success) {
        showToast('Traffic case deleted', 'success');
        loadTrafficCases();
    } else {
        showToast(result.error || 'Failed to delete', 'error');
    }
}

function getTrafficCommission(disposition) {
    if (disposition === 'dismissed') return 150;
    if (disposition === 'amended') return 100;
    return 0;
}

// Sort Traffic Cases
function sortTrafficCases(column) {
    if (trafficSortColumn === column) {
        trafficSortDir = trafficSortDir === 'asc' ? 'desc' : 'asc';
    } else {
        trafficSortColumn = column;
        trafficSortDir = 'asc';
    }

    trafficCasesData.sort((a, b) => {
        let valA = a[column];
        let valB = b[column];

        if (valA == null) valA = '';
        if (valB == null) valB = '';

        if (['commission'].includes(column)) {
            valA = parseFloat(valA) || 0;
            valB = parseFloat(valB) || 0;
        } else if (column === 'court_date') {
            valA = valA ? new Date(valA).getTime() : 0;
            valB = valB ? new Date(valB).getTime() : 0;
        } else {
            valA = String(valA).toLowerCase();
            valB = String(valB).toLowerCase();
        }

        if (valA < valB) return trafficSortDir === 'asc' ? -1 : 1;
        if (valA > valB) return trafficSortDir === 'asc' ? 1 : -1;
        return 0;
    });

    filterTrafficCases();
}

// ============================================
// PDF Generation
// ============================================

function downloadTrafficCasePDF(caseId) {
    const c = trafficCasesData.find(x => x.id == caseId);
    if (!c) return;

    if (!window.jspdf) {
        alert('PDF library is still loading. Please try again.');
        return;
    }
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    let y = 20;

    doc.setFontSize(18);
    doc.setFont('helvetica', 'bold');
    doc.text('Traffic Case Report', 105, y, { align: 'center' });
    y += 12;

    doc.setDrawColor(200);
    doc.setLineWidth(0.5);
    doc.line(20, y, 190, y);
    y += 10;

    doc.setFontSize(11);
    const addField = (label, value) => {
        if (y > 270) { doc.addPage(); y = 20; }
        doc.setFont('helvetica', 'bold');
        doc.text(label + ':', 25, y);
        doc.setFont('helvetica', 'normal');
        doc.text(String(value || '-'), 80, y);
        y += 8;
    };

    addField('Client Name', c.client_name);
    addField('Phone', c.client_phone);
    addField('Court', c.court);
    addField('Court Date', c.court_date ? formatDate(c.court_date) : '-');
    addField('Charge', c.charge);
    addField('Case Number', c.case_number);
    addField('Disposition', c.disposition);
    addField('Status', c.status);
    addField('Requester', c.referral_source);
    addField('Discovery', c.discovery == 1 ? 'Received' : 'Not Received');
    addField('NOA Sent', c.noa_sent_date || '-');
    addField('Prosecutor Offer', c.prosecutor_offer);
    addField('Commission', formatCurrency(getTrafficCommission(c.disposition)));
    addField('Paid', c.paid == 1 ? 'Yes' : 'No');

    if (c.note) {
        y += 4;
        doc.setFont('helvetica', 'bold');
        doc.text('Note:', 25, y);
        y += 7;
        doc.setFont('helvetica', 'normal');
        const lines = doc.splitTextToSize(c.note, 155);
        doc.text(lines, 25, y);
        y += lines.length * 6;
    }

    y += 10;
    doc.setDrawColor(200);
    doc.line(20, y, 190, y);
    y += 6;
    doc.setFontSize(8);
    doc.setTextColor(150);
    doc.text('Generated on ' + new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }), 25, y);

    const clientSlug = (c.client_name || 'case').replace(/[^a-zA-Z0-9]/g, '_').substring(0, 30);
    doc.save(`traffic_case_${clientSlug}.pdf`);
}

// ============================================
// File Attachments
// ============================================

async function loadTrafficFiles(caseId) {
    const container = document.getElementById('trafficFilesList');
    container.innerHTML = '<div style="padding: 16px; text-align: center; color: #9ca3af; font-size: 13px;">Loading...</div>';

    try {
        const result = await apiCall('api/traffic_files.php?case_id=' + caseId);
        if (result.files && result.files.length > 0) {
            container.innerHTML = result.files.map(f => `
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 16px; border-bottom: 1px solid #f3f4f6;" data-file-id="${f.id}">
                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1;">
                        <span style="font-size: 18px; flex-shrink: 0;">${getFileIcon(f.original_name)}</span>
                        <div style="min-width: 0;">
                            <div style="font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${f.original_name}">${f.original_name}</div>
                            <div style="font-size: 11px; color: #9ca3af;">${formatFileSize(f.file_size)} &middot; ${formatDate(f.uploaded_at)}</div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 6px; flex-shrink: 0;">
                        <button type="button" class="tv3-icon-btn" onclick="downloadTrafficFile(${f.id})" title="Download">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </button>
                        <button type="button" class="tv3-icon-btn danger" onclick="deleteTrafficFile(${f.id})" title="Delete">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div style="padding: 16px; text-align: center; color: #9ca3af; font-size: 13px;">No files attached</div>';
        }
    } catch (e) {
        container.innerHTML = '<div style="padding: 16px; text-align: center; color: #ef4444; font-size: 13px;">Failed to load files</div>';
    }
}

async function uploadTrafficFile(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const caseId = document.getElementById('trafficCaseId').value;

    if (!caseId) {
        showToast('Please save the case first before uploading files', 'error');
        input.value = '';
        return;
    }

    const maxSize = 20 * 1024 * 1024;
    if (file.size > maxSize) {
        showToast('File too large. Maximum size is 20MB', 'error');
        input.value = '';
        return;
    }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('case_id', caseId);
    formData.append('csrf_token', csrfToken);

    try {
        const response = await fetch('api/traffic_files.php', {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrfToken },
            body: formData
        });
        const result = await response.json();
        if (result.csrf_token) csrfToken = result.csrf_token;

        if (result.success) {
            showToast('File uploaded', 'success');
            loadTrafficFiles(caseId);
        } else {
            showToast(result.error || 'Upload failed', 'error');
        }
    } catch (e) {
        showToast('Upload failed', 'error');
    }

    input.value = '';
}

function downloadTrafficFile(fileId) {
    window.open('api/traffic_files.php?action=download&id=' + fileId, '_blank');
}

async function deleteTrafficFile(fileId) {
    if (!confirm('Delete this file?')) return;
    const caseId = document.getElementById('trafficCaseId').value;

    const result = await apiCall('api/traffic_files.php', 'DELETE', { id: fileId });
    if (result.success) {
        showToast('File deleted', 'success');
        loadTrafficFiles(caseId);
    } else {
        showToast(result.error || 'Failed to delete file', 'error');
    }
}
