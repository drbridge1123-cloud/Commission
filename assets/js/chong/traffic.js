/**
 * ChongDashboard - Traffic tab functions (cases, commission, requests sub-tabs).
 */

// ============================================
// Traffic Sub-Tab System
// ============================================

function switchTrafficSubTab(subTab) {
    currentTrafficSubTab = subTab;
    ['cases', 'commission', 'requests'].forEach(t => {
        const panel = document.getElementById('trafficSubContent-' + t);
        if (panel) panel.style.display = t === subTab ? '' : 'none';
        const btn = document.getElementById('trafficSubTab-' + t);
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

    document.getElementById('commCountAll').textContent = commCases.length;
    document.getElementById('commCountPaid').textContent = paid.length;
    document.getElementById('commCountUnpaid').textContent = unpaid.length;

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
    ['all', 'paid', 'unpaid'].forEach(f => {
        const btn = document.getElementById('commFilterBtn-' + f);
        if (btn) btn.classList.toggle('active', f === filter);
    });
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
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; color:#6b7280; padding:40px;">No commission records</td></tr>';
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
            ? '<span class="ink-badge" style="background:#d1fae5;color:#059669;">dismissed</span>'
            : '<span class="ink-badge" style="background:#fef3c7;color:#d97706;">amended</span>';
        const paidBadge = c.paid == 1
            ? `<span class="ink-badge paid" style="cursor:pointer;" onclick="event.stopPropagation(); toggleTrafficPaid(${c.id}, 0)">PAID</span>`
            : `<span class="ink-badge unpaid" style="cursor:pointer;" onclick="event.stopPropagation(); toggleTrafficPaid(${c.id}, 1)">UNPAID</span>`;

        return `
            <tr class="clickable-row" onclick="openTrafficModal(trafficCasesData.find(x => x.id == ${c.id}))" style="cursor:pointer;">
                <td style="width:0;padding:0;border:none;"></td>
                <td>${escapeHtml(c.client_name)}</td>
                <td>${escapeHtml(c.court || '-')}</td>
                <td>${c.court_date ? formatDate(c.court_date) : '-'}</td>
                <td>${c.resolved_at ? formatDate(c.resolved_at) : '-'}</td>
                <td>${dispBadge}</td>
                <td>${escapeHtml(c.referral_source || '-')}</td>
                <td style="text-align:right; font-weight:600; color:#059669;">${formatCurrency(commission)}</td>
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
    const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';

    container.innerHTML = pendingTrafficRequests.map(r => {
        const reqName = (r.referral_source || r.requester_name || '').replace(/\s*\(.*?\)\s*$/, '');
        return `
            <div style="background: white; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="flex: 1; display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr; gap: 12px; align-items: center;">
                    <div>
                        <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px;">Requester</div>
                        <div style="font-size: 13px; font-weight: 600; color: #1a1a2e;">${escapeHtml(reqName || '—')}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px;">Client</div>
                        <div style="font-size: 13px; font-weight: 600; color: #1a1a2e;">${escapeHtml(r.client_name)}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px;">Court</div>
                        <div style="font-size: 13px; color: #3d3f4e;">${escapeHtml(r.court || '—')}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px;">Ticket Issued</div>
                        <div style="font-size: 13px; color: #3d3f4e;">${fmtDate(r.citation_issued_date)}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px;">Court Date</div>
                        <div style="font-size: 13px; color: #3d3f4e;">${fmtDate(r.court_date)}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px;">Charge</div>
                        <div style="font-size: 12px; color: #5c5f73; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${escapeHtml(r.charge || '')}">${escapeHtml(r.charge || '—')}</div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button onclick="acceptTrafficRequest(${r.id})" style="background: #059669; color: white; border: none; border-radius: 6px; padding: 8px 16px; font-size: 12px; font-weight: 600; cursor: pointer; font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 6px;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        Accept
                    </button>
                    <button onclick="denyTrafficRequest(${r.id}, '${escapeJs(r.client_name)}')" style="background: white; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; padding: 8px 16px; font-size: 12px; font-weight: 600; cursor: pointer; font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 6px;">
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
    document.getElementById('reqBadgePending').textContent = pending;
}

function setRequestFilter(filter) {
    currentRequestFilter = filter;
    ['all', 'pending', 'accepted', 'denied'].forEach(f => {
        const btn = document.getElementById('reqFilterBtn-' + f);
        if (btn) btn.classList.toggle('active', f === filter);
    });
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
        tbody.innerHTML = '<tr><td colspan="11" style="text-align:center; color:#8b8fa3; padding:40px;">No requests found</td></tr>';
        document.getElementById('requestsCaseCount').textContent = '0 requests';
        return;
    }

    tbody.innerHTML = requests.map(r => {
        const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
        const courtDate = fmtDate(r.court_date);
        const citationDate = fmtDate(r.citation_issued_date);
        const responded = fmtDate(r.responded_at);

        const statusBadge = r.status === 'pending'
            ? '<span class="stat-badge" style="background:#fef3c7;color:#92400e;">Pending</span>'
            : r.status === 'accepted'
            ? '<span class="stat-badge" style="background:#d1fae5;color:#065f46;">Accepted</span>'
            : '<span class="stat-badge" style="background:#fee2e2;color:#991b1b;">Denied</span>';

        const actions = r.status === 'pending' ? `
            <div style="display:flex; gap:4px;">
                <button onclick="acceptTrafficRequest(${r.id})" class="ink-icon-btn" title="Accept" style="color:#059669; border:1px solid #d1fae5; border-radius:6px; padding:4px 8px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                </button>
                <button onclick="denyTrafficRequest(${r.id}, '${escapeJs(r.client_name)}')" class="ink-icon-btn" title="Deny" style="color:#dc2626; border:1px solid #fecaca; border-radius:6px; padding:4px 8px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        ` : '<span style="color:#c4c7d0;">—</span>';

        const noteText = r.status === 'denied' && r.deny_reason
            ? '<span style="color:#dc2626;">' + escapeHtml(r.deny_reason) + '</span>'
            : (r.note ? escapeHtml(r.note) : '<span style="color:#c4c7d0;">—</span>');

        const created = fmtDate(r.created_at);

        const ov = 'white-space:nowrap; overflow:hidden; text-overflow:ellipsis;';
        const reqName = (r.referral_source || r.requester_name || '').replace(/\s*\(.*?\)\s*$/, '');
        return `
            <tr>
                <td style="${ov}" title="${escapeHtml(reqName)}">${escapeHtml(reqName || '—')}</td>
                <td style="font-size:12px;">${created || '—'}</td>
                <td style="font-weight:600; color:#1a1a2e; ${ov}" title="${escapeHtml(r.client_name)}">${escapeHtml(r.client_name)}</td>
                <td style="font-size:12px; ${ov}">${escapeHtml(r.client_phone || '—')}</td>
                <td style="font-size:11px; ${ov}" title="${escapeHtml(r.client_email || '')}">${escapeHtml(r.client_email || '—')}</td>
                <td style="${ov}" title="${escapeHtml(r.court || '')}">${escapeHtml(r.court || '—')}</td>
                <td style="${ov}" title="${escapeHtml(r.charge || '')}">${escapeHtml(r.charge || '—')}</td>
                <td style="font-family:monospace; font-size:11px; ${ov}">${escapeHtml(r.case_number || '—')}</td>
                <td style="font-size:12px;">${courtDate || '—'}</td>
                <td>${statusBadge}${r.status === 'pending' ? '<div style="margin-top:4px;">' + actions + '</div>' : ''}</td>
                <td style="font-size:11px; color:#5c5f73; ${ov}" title="${escapeHtml(r.note || r.deny_reason || '')}">${noteText}</td>
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
// Traffic Cases Functions
// ============================================

async function loadTrafficCases() {
    try {
        const result = await apiCall('api/traffic.php');
        if (result.cases) {
            trafficCasesData = result.cases;
            updateTrafficStatusCounts();
            filterTrafficCases();
            updateTrafficStats(trafficCasesData);
            populateTrafficFilters();
            if (typeof renderSidebarContent === 'function') {
                renderSidebarContent(currentSidebarTab || 'all');
            }
            if (currentTrafficSubTab === 'commission') {
                updateCommissionStats();
                filterCommTraffic();
            }
        }
    } catch (err) {
        console.error('Error loading traffic cases:', err);
    }
}

function populateTrafficFilters() {
    const courts = [...new Set(trafficCasesData.map(c => c.court).filter(Boolean))].sort();
    const courtSelect = document.getElementById('trafficCourtFilter');
    if (courtSelect) {
        courtSelect.innerHTML = '<option value="">All Courts</option>' +
            courts.map(c => `<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`).join('');
    }

    const referrals = [...new Set(trafficCasesData.map(c => c.referral_source).filter(Boolean))].sort();
    const referralSelect = document.getElementById('trafficReferralFilter');
    if (referralSelect) {
        referralSelect.innerHTML = '<option value="">All Requesters</option>' +
            referrals.map(r => `<option value="${escapeHtml(r)}">${escapeHtml(r)}</option>`).join('');
    }
}

function filterTrafficByDropdown() {
    const court = document.getElementById('trafficCourtFilter')?.value || '';
    const referral = document.getElementById('trafficReferralFilter')?.value || '';

    if (court) {
        currentTrafficFilter = { type: 'court', value: court };
    } else if (referral) {
        currentTrafficFilter = { type: 'referral', value: referral };
    } else {
        currentTrafficFilter = null;
    }
    filterTrafficCases();
}

function renderTrafficTable(cases) {
    const tbody = document.getElementById('trafficTableBody');
    if (!cases || cases.length === 0) {
        tbody.innerHTML = '<tr><td colspan="12" style="text-align:center; color:#6b7280; padding: 40px;">No traffic cases</td></tr>';
        document.getElementById('trafficCaseCount').textContent = '0 cases';
        return;
    }

    tbody.innerHTML = cases.map(c => {
        return `
            <tr class="clickable-row" onclick="openTrafficModal(trafficCasesData.find(x => x.id == ${c.id}))" style="cursor:pointer;">
                <td style="width:0;padding:0;border:none;"></td>
                <td>${c.created_at ? formatDate(c.created_at) : '-'}</td>
                <td>${escapeHtml(c.client_name)}</td>
                <td style="font-family: monospace; font-size: 12px;">${escapeHtml(c.case_number || '-')}</td>
                <td>${escapeHtml(c.court || '-')}</td>
                <td>${escapeHtml(c.charge || '-')}</td>
                <td>${c.court_date ? formatDate(c.court_date) : '-'}</td>
                <td>${c.noa_sent_date || '-'}</td>
                <td style="text-align:center;">${c.discovery == 1
                    ? '<span class="ink-badge" style="background:#d1fae5;color:#059669;">Received</span>'
                    : ''
                }</td>
                <td>${c.status === 'resolved'
                    ? '<span class="ink-badge" style="background:#d1fae5;color:#059669;">Resolved</span>'
                    : '<span class="ink-badge" style="background:#dbeafe;color:#1d4ed8;">Active</span>'
                }</td>
                <td>${escapeHtml(c.referral_source || '-')}</td>
                <td style="text-align: center;" onclick="event.stopPropagation();">
                    <div style="display:flex; gap:4px; justify-content:center;">
                        <button class="ink-icon-btn" onclick="downloadTrafficCasePDF(${c.id})" title="Download PDF" style="color:#3b82f6;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                        </button>
                        <button class="ink-icon-btn ink-icon-btn-danger" onclick="deleteTrafficCase(${c.id})" title="Delete">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    document.getElementById('trafficCaseCount').textContent = cases.length + ' cases';
}

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

// Traffic Case File Attachments
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
                        <button type="button" onclick="downloadTrafficFile(${f.id})" title="Download" style="background: none; border: 1px solid #d1d5db; border-radius: 6px; padding: 4px 8px; cursor: pointer; color: #2563eb; font-size: 14px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </button>
                        <button type="button" onclick="deleteTrafficFile(${f.id})" title="Delete" style="background: none; border: 1px solid #fecaca; border-radius: 6px; padding: 4px 8px; cursor: pointer; color: #dc2626; font-size: 14px;">
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

function filterTrafficCases() {
    const search = (document.getElementById('trafficSearch').value || '').toLowerCase();
    let filtered = trafficCasesData;

    if (currentTrafficStatusFilter === 'active') {
        filtered = filtered.filter(c => !c.disposition || c.disposition === 'pending');
    } else if (currentTrafficStatusFilter === 'done') {
        filtered = filtered.filter(c => c.disposition === 'dismissed' || c.disposition === 'amended');
    }

    if (currentTrafficFilter) {
        filtered = filtered.filter(c => {
            if (currentTrafficFilter.type === 'referral') {
                return c.referral_source === currentTrafficFilter.value;
            } else if (currentTrafficFilter.type === 'court') {
                return c.court === currentTrafficFilter.value;
            } else if (currentTrafficFilter.type === 'year') {
                return c.court_date && c.court_date.startsWith(currentTrafficFilter.value);
            }
            return true;
        });
    }

    if (search) {
        filtered = filtered.filter(c =>
            (c.client_name || '').toLowerCase().includes(search) ||
            (c.case_number || '').toLowerCase().includes(search) ||
            (c.court || '').toLowerCase().includes(search)
        );
    }

    renderTrafficTable(filtered);
}

function setTrafficStatusFilter(status) {
    currentTrafficStatusFilter = status;

    ['all', 'active', 'done'].forEach(s => {
        const btn = document.getElementById('trafficStatusBtn-' + s);
        if (btn) {
            btn.classList.remove('active');
        }
    });
    const activeBtn = document.getElementById('trafficStatusBtn-' + status);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }

    const labels = {
        'all': 'All Cases',
        'active': 'Active Cases',
        'done': 'Done Cases'
    };
    document.getElementById('trafficFilterLabel').textContent = labels[status] || 'All Cases';

    filterTrafficCases();
}

function updateTrafficStatusCounts() {
    const allCount = trafficCasesData.length;
    const activeCount = trafficCasesData.filter(c => !c.disposition || c.disposition === 'pending').length;
    const doneCount = trafficCasesData.filter(c => c.disposition === 'dismissed' || c.disposition === 'amended').length;

    document.getElementById('trafficCountAll').textContent = allCount;
    document.getElementById('trafficCountActive').textContent = activeCount;
    document.getElementById('trafficCountDone').textContent = doneCount;
}

function switchSidebarTab(tab) {
    currentSidebarTab = tab;
    currentTrafficFilter = null;

    const statusLabels = { 'all': 'All Cases', 'active': 'Active Cases', 'done': 'Done Cases' };
    document.getElementById('trafficFilterLabel').textContent = statusLabels[currentTrafficStatusFilter] || 'All Cases';

    ['all', 'referral', 'court', 'year'].forEach(t => {
        const btn = document.getElementById('sidebarTab-' + t);
        if (btn) {
            btn.classList.remove('active');
        }
    });
    const activeBtn = document.getElementById('sidebarTab-' + tab);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }

    renderSidebarContent(tab);
    filterTrafficCases();
}

function renderSidebarContent(tab) {
    const container = document.getElementById('sidebarContent');
    let items = [];

    if (tab === 'all') {
        const active = trafficCasesData.filter(c => !c.disposition || c.disposition === 'pending').length;
        const dismissed = trafficCasesData.filter(c => c.disposition === 'dismissed').length;
        const amended = trafficCasesData.filter(c => c.disposition === 'amended').length;
        const totalComm = trafficCasesData.reduce((sum, c) => sum + getTrafficCommission(c.disposition), 0);

        container.innerHTML = `
            <div style="padding: 12px;">
                <div style="font-weight: 600; margin-bottom: 12px; color: #1a1a2e;">All Cases Summary</div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; justify-content: space-between; padding: 8px 10px; background: #f7f9fc; border-radius: 6px;">
                        <span style="color: #5c5f73;">Total</span>
                        <span style="font-weight: 600;">${trafficCasesData.length}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 10px; background: #dbeafe; border-radius: 6px;">
                        <span style="color: #1d4ed8;">Active</span>
                        <span style="font-weight: 600; color: #1d4ed8;">${active}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 10px; background: #d1fae5; border-radius: 6px;">
                        <span style="color: #059669;">Dismissed</span>
                        <span style="font-weight: 600; color: #059669;">${dismissed}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 10px; background: #fef3c7; border-radius: 6px;">
                        <span style="color: #d97706;">Amended</span>
                        <span style="font-weight: 600; color: #d97706;">${amended}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px; background: #1a1a2e; border-radius: 6px; margin-top: 4px;">
                        <span style="color: #fff;">Commission</span>
                        <span style="font-weight: 700; color: #4ade80;">${formatCurrency(totalComm)}</span>
                    </div>
                </div>
            </div>
        `;
        return;
    } else if (tab === 'referral') {
        const referrals = {};
        trafficCasesData.forEach(c => {
            const ref = c.referral_source || 'Unknown';
            referrals[ref] = (referrals[ref] || 0) + 1;
        });
        items = Object.entries(referrals).sort((a, b) => b[1] - a[1]);
    } else if (tab === 'court') {
        const courts = {};
        trafficCasesData.forEach(c => {
            const court = c.court || 'Unknown';
            courts[court] = (courts[court] || 0) + 1;
        });
        items = Object.entries(courts).sort((a, b) => b[1] - a[1]);
    } else if (tab === 'year') {
        const years = {};
        trafficCasesData.forEach(c => {
            const year = c.court_date ? c.court_date.substring(0, 4) : 'Unknown';
            years[year] = (years[year] || 0) + 1;
        });
        items = Object.entries(years).sort((a, b) => b[0].localeCompare(a[0]));
    }

    container.innerHTML = items.map(([name, count]) => `
        <div onclick="applySidebarFilter('${tab}', '${escapeJs(name)}')"
             style="padding: 10px 12px; cursor: pointer; display: flex; justify-content: space-between; border-bottom: 1px solid #f0f0f0;"
             onmouseover="this.style.background='#f7f9fc'" onmouseout="this.style.background=''">
            <span style="font-size: 13px;">${escapeHtml(name)}</span>
            <span style="font-size: 12px; background: #e5e7eb; padding: 2px 8px; border-radius: 10px;">${count}</span>
        </div>
    `).join('');
}

function applySidebarFilter(type, value) {
    currentTrafficFilter = { type, value };
    document.getElementById('trafficFilterLabel').textContent = value;
    filterTrafficCases();
}

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

// Sort Traffic Cases
function sortTrafficCases(column) {
    if (trafficSortColumn === column) {
        trafficSortDir = trafficSortDir === 'asc' ? 'desc' : 'asc';
    } else {
        trafficSortColumn = column;
        trafficSortDir = 'asc';
    }

    document.querySelectorAll('#trafficTable th.sortable').forEach(th => {
        th.classList.remove('asc', 'desc');
        if (th.dataset.sort === column) {
            th.classList.add(trafficSortDir);
        }
    });

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
