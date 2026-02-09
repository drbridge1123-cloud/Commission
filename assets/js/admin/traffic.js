/**
 * Admin Dashboard - Traffic Cases (V3 Compact Layout)
 */

// ── Data Loading ──

async function loadAdminTrafficCases() {
    try {
        const data = await apiCall('api/traffic.php?status=all');
        adminTrafficAllCases = data.cases || [];
        updateTV3Stats();
        populateTV3FilterDropdowns();
        applyTV3Filters();
    } catch (err) {
        console.error('Error loading traffic cases:', err);
    }
}

async function loadMyTrafficRequests() {
    try {
        const data = await apiCall('api/traffic_requests.php');
        myTrafficRequests = data.requests || [];
        updateTV3Stats();
        if (tv3PillTab === 'requests') renderTV3Requests();
    } catch (err) {
        console.error('Error loading traffic requests:', err);
    }
}

// ── Stats ──

function updateTV3Stats() {
    const active = adminTrafficAllCases.filter(c => c.status === 'active').length;
    const dismissed = adminTrafficAllCases.filter(c => c.disposition === 'dismissed').length;
    const amended = adminTrafficAllCases.filter(c => c.disposition === 'amended').length;
    const pendingReq = myTrafficRequests.filter(r => r.status === 'pending').length;

    const setEl = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    setEl('tv3StatActive', active);
    setEl('tv3StatDismissed', dismissed);
    setEl('tv3StatAmended', amended);
    setEl('tv3StatPendingReq', pendingReq);

    // Request count badge on pill
    const reqBadge = document.getElementById('tv3ReqCount');
    if (reqBadge) {
        if (pendingReq > 0) {
            reqBadge.textContent = pendingReq;
            reqBadge.style.display = '';
        } else {
            reqBadge.style.display = 'none';
        }
    }

    // Sidebar nav badge
    const navBadge = document.getElementById('trafficRequestBadge');
    if (navBadge) {
        if (pendingReq > 0) {
            navBadge.textContent = pendingReq;
            navBadge.style.display = '';
        } else {
            navBadge.style.display = 'none';
        }
    }
}

// ── Pill Tabs ──

function switchTrafficPillTab(tab) {
    tv3PillTab = tab;

    // Update pill active state
    document.querySelectorAll('#content-traffic .tv3-pill').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tab);
    });

    const filterRow = document.getElementById('tv3FilterRow');
    const casesWrap = document.getElementById('tv3CasesWrap');
    const requestsWrap = document.getElementById('tv3RequestsWrap');
    const footer = document.getElementById('tv3Footer');

    if (tab === 'requests') {
        filterRow.style.display = 'none';
        casesWrap.style.display = 'none';
        requestsWrap.style.display = '';
        footer.style.display = 'none';
        renderTV3Requests();
    } else {
        filterRow.style.display = '';
        casesWrap.style.display = '';
        requestsWrap.style.display = 'none';
        footer.style.display = '';
        applyTV3Filters();
    }
}

// ── Filter Dropdowns ──

function populateTV3FilterDropdowns() {
    // This will be called when view filter changes to repopulate sub-filter
}

function onTV3ViewChange() {
    const view = document.getElementById('tv3ViewFilter').value;
    const subGroup = document.getElementById('tv3SubFilterGroup');
    const subLabel = document.getElementById('tv3SubFilterLabel');
    const subSelect = document.getElementById('tv3SubFilter');

    if (view === 'all') {
        subGroup.style.display = 'none';
        applyTV3Filters();
        return;
    }

    subGroup.style.display = '';

    // Build options from data
    let items = [];
    if (view === 'referral') {
        subLabel.textContent = 'Referral';
        const grouped = {};
        adminTrafficAllCases.forEach(c => {
            const ref = c.referral_source || 'Unknown';
            grouped[ref] = (grouped[ref] || 0) + 1;
        });
        items = Object.entries(grouped).sort((a, b) => b[1] - a[1]);
    } else if (view === 'court') {
        subLabel.textContent = 'Court';
        const grouped = {};
        adminTrafficAllCases.forEach(c => {
            const court = c.court || 'Unknown';
            grouped[court] = (grouped[court] || 0) + 1;
        });
        items = Object.entries(grouped).sort((a, b) => b[1] - a[1]);
    } else if (view === 'year') {
        subLabel.textContent = 'Year';
        const grouped = {};
        adminTrafficAllCases.forEach(c => {
            const year = c.court_date ? new Date(c.court_date).getFullYear() : 'Unknown';
            grouped[year] = (grouped[year] || 0) + 1;
        });
        items = Object.entries(grouped).sort((a, b) => b[0] - a[0]);
    }

    subSelect.innerHTML = '<option value="all">All</option>';
    items.forEach(([name, count]) => {
        const opt = document.createElement('option');
        opt.value = name;
        opt.textContent = `${name} (${count})`;
        subSelect.appendChild(opt);
    });

    applyTV3Filters();
}

// ── Filtering ──

function applyTV3Filters() {
    if (tv3PillTab === 'requests') return;

    let filtered = [...adminTrafficAllCases];

    // Pill tab status filter
    if (tv3PillTab === 'active') {
        filtered = filtered.filter(c => c.status === 'active');
    } else if (tv3PillTab === 'done') {
        filtered = filtered.filter(c => c.status === 'resolved');
    }

    // View + sub-filter
    const view = document.getElementById('tv3ViewFilter')?.value || 'all';
    const sub = document.getElementById('tv3SubFilter')?.value || 'all';

    if (view !== 'all' && sub !== 'all') {
        if (view === 'referral') {
            filtered = filtered.filter(c => (c.referral_source || 'Unknown') === sub);
        } else if (view === 'court') {
            filtered = filtered.filter(c => (c.court || 'Unknown') === sub);
        } else if (view === 'year') {
            if (sub === 'Unknown') {
                filtered = filtered.filter(c => !c.court_date);
            } else {
                filtered = filtered.filter(c => c.court_date && new Date(c.court_date).getFullYear() == sub);
            }
        }
    }

    // Search
    const searchTerm = (document.getElementById('tv3Search')?.value || '').toLowerCase().trim();
    if (searchTerm) {
        filtered = filtered.filter(c =>
            (c.client_name || '').toLowerCase().includes(searchTerm) ||
            (c.court || '').toLowerCase().includes(searchTerm) ||
            (c.charge || '').toLowerCase().includes(searchTerm) ||
            (c.case_number || '').toLowerCase().includes(searchTerm) ||
            (c.requester_name || '').toLowerCase().includes(searchTerm)
        );
    }

    adminTrafficCases = filtered;
    renderTV3Cases();
    updateTV3Footer(filtered);
}

// ── Render Cases ──

function renderTV3Cases() {
    const tbody = document.getElementById('tv3CasesBody');
    if (!tbody) return;

    if (adminTrafficCases.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="tv3-empty">No traffic cases found</td></tr>';
        return;
    }

    tbody.innerHTML = adminTrafficCases.map(c => {
        const courtDate = c.court_date ? new Date(c.court_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' }) : '<span class="tv3-dim">-</span>';
        const noaDate = c.noa_sent_date ? new Date(c.noa_sent_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '<span class="tv3-dim">-</span>';
        const discoveryIcon = c.discovery ? '<span style="color: var(--tv3-green); font-weight: 600;">✓</span>' : '<span class="tv3-dim">-</span>';

        let dispBadge;
        if (c.disposition === 'dismissed') {
            dispBadge = '<span class="tv3-badge dismissed">Dismissed</span>';
        } else if (c.disposition === 'amended') {
            dispBadge = '<span class="tv3-badge amended">Amended</span>';
        } else {
            dispBadge = '<span class="tv3-badge pending">Pending</span>';
        }

        const statusBadge = c.status === 'active'
            ? '<span class="tv3-badge active">Active</span>'
            : '<span class="tv3-badge done">Done</span>';

        return `<tr onclick="editAdminTrafficCase(${c.id})">
            <td style="font-weight: 600;">${escapeHtml(c.client_name || '-')}</td>
            <td>${escapeHtml(c.court || '-')}</td>
            <td>${courtDate}</td>
            <td>${escapeHtml(c.charge || '-')}</td>
            <td class="c">${noaDate}</td>
            <td class="c">${discoveryIcon}</td>
            <td>${dispBadge}</td>
            <td class="c">${statusBadge}</td>
            <td style="color: var(--tv3-text-sec); font-size: 11px;">${escapeHtml(c.requester_name || '-')}</td>
            <td class="c">
                <button onclick="event.stopPropagation(); editAdminTrafficCase(${c.id})" class="tv3-edit-btn">Edit</button>
            </td>
        </tr>`;
    }).join('');
}

// ── Render Requests ──

function renderTV3Requests() {
    const tbody = document.getElementById('tv3RequestsBody');
    if (!tbody) return;

    if (myTrafficRequests.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="tv3-empty">No requests yet</td></tr>';
        return;
    }

    tbody.innerHTML = myTrafficRequests.map(r => {
        const courtDate = r.court_date ? new Date(r.court_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '<span class="tv3-dim">-</span>';
        const submitted = r.created_at ? new Date(r.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '-';

        let statusBadge;
        if (r.status === 'pending') {
            statusBadge = '<span class="tv3-badge pending">Pending</span>';
        } else if (r.status === 'accepted') {
            statusBadge = '<span class="tv3-badge accepted">Accepted</span>';
        } else if (r.status === 'denied') {
            statusBadge = '<span class="tv3-badge denied">Denied</span>';
        } else {
            statusBadge = `<span class="tv3-badge">${escapeHtml(r.status)}</span>`;
        }

        return `<tr onclick="viewTrafficRequest(${r.id})">
            <td style="font-weight: 600;">${escapeHtml(r.client_name || '-')}</td>
            <td>${escapeHtml(r.court || '-')}</td>
            <td>${courtDate}</td>
            <td>${escapeHtml(r.charge || '-')}</td>
            <td style="color: var(--tv3-text-sec); font-size: 11px;">${escapeHtml(r.requester_name || '-')}</td>
            <td class="c">${statusBadge}</td>
            <td style="color: var(--tv3-text-sec); font-size: 11px;">${submitted}</td>
            <td class="c" style="white-space: nowrap;">
                <button onclick="event.stopPropagation(); viewTrafficRequest(${r.id})" class="tv3-edit-btn" style="background: var(--tv3-blue);">View</button>
                <button onclick="event.stopPropagation(); deleteMyTrafficRequest(${r.id}, '${escapeHtml(r.client_name)}')" class="tv3-edit-btn" style="background: var(--tv3-red);">Del</button>
            </td>
        </tr>`;
    }).join('');
}

// ── Footer ──

function updateTV3Footer(cases) {
    const count = cases.length;
    const dismissed = cases.filter(c => c.disposition === 'dismissed').length;
    const amended = cases.filter(c => c.disposition === 'amended').length;

    const setEl = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    setEl('tv3FootCount', count);
    setEl('tv3FootDismissed', dismissed);
    setEl('tv3FootAmended', amended);
}

// ── Request Form Submit ──

async function submitTrafficRequest(e) {
    e.preventDefault();

    const data = {
        client_name: document.getElementById('reqClientName').value.trim(),
        client_phone: document.getElementById('reqClientPhone').value.trim(),
        client_email: document.getElementById('reqClientEmail').value.trim(),
        court: document.getElementById('reqCourt').value.trim(),
        court_date: document.getElementById('reqCourtDate').value || null,
        charge: document.getElementById('reqCharge').value.trim(),
        case_number: document.getElementById('reqCaseNumber').value.trim(),
        citation_issued_date: document.getElementById('reqCitationIssuedDate').value || null,
        note: document.getElementById('reqNote').value.trim(),
        referral_source: document.getElementById('reqReferralSource').value.trim()
    };

    if (!data.client_name) {
        alert('Client name is required');
        return;
    }

    try {
        const result = await apiCall('api/traffic_requests.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (result.success) {
            closeModal('trafficRequestModal');
            document.getElementById('trafficRequestForm').reset();
            loadMyTrafficRequests();
        } else {
            alert(result.error || 'Error submitting request');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error submitting request');
    }
}

// ── View Request Detail ──

function viewTrafficRequest(id) {
    const req = myTrafficRequests.find(r => r.id == id);
    if (!req) return;

    const courtDate = req.court_date ? new Date(req.court_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-';
    const citationDate = req.citation_issued_date ? new Date(req.citation_issued_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-';
    const createdAt = req.created_at ? new Date(req.created_at).toLocaleString() : '-';
    const respondedAt = req.responded_at ? new Date(req.responded_at).toLocaleString() : '-';

    let statusText = req.status.charAt(0).toUpperCase() + req.status.slice(1);
    const requesterName = req.requester_name || 'Unknown';

    let details = `Status: ${statusText}
Requested by: ${requesterName}
Requested: ${createdAt}${req.status === 'accepted' ? '\nAccepted: ' + respondedAt : ''}${req.status === 'denied' ? '\nDenied: ' + respondedAt : ''}

Client: ${req.client_name || '-'}
Phone: ${req.client_phone || '-'}
Email: ${req.client_email || '-'}

Court: ${req.court || '-'}
Court Date: ${courtDate}
Charge: ${req.charge || '-'}
Ticket #: ${req.case_number || '-'}
Issued: ${citationDate}

Note: ${req.note || '-'}`;

    if (req.status === 'denied' && req.deny_reason) {
        details += `\n\nDeny Reason: ${req.deny_reason}`;
    }

    alert(details);
}

// ── Delete Request ──

async function deleteMyTrafficRequest(id, clientName) {
    if (!confirm(`Delete request for "${clientName}"?`)) return;

    try {
        const result = await apiCall('api/traffic_requests.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        if (result.success) {
            loadMyTrafficRequests();
        } else {
            alert(result.error || 'Failed to delete request');
        }
    } catch (err) {
        console.error('Error deleting request:', err);
        alert('Error deleting request');
    }
}

// ── Edit / Save / Delete Traffic Case ──

function editAdminTrafficCase(id) {
    const c = adminTrafficAllCases.find(c => c.id == id);
    if (!c) return;

    editingTrafficCaseId = id;

    document.getElementById('adminTrafficClientName').value = c.client_name || '';
    document.getElementById('adminTrafficClientPhone').value = c.client_phone || '';
    document.getElementById('adminTrafficCourt').value = c.court || '';
    document.getElementById('adminTrafficCourtDate').value = c.court_date ? c.court_date.split(' ')[0] : '';
    document.getElementById('adminTrafficCharge').value = c.charge || '';
    document.getElementById('adminTrafficCaseNumber').value = c.case_number || '';
    document.getElementById('adminTrafficOffer').value = c.prosecutor_offer || '';
    document.getElementById('adminTrafficDisposition').value = c.disposition || 'pending';
    document.getElementById('adminTrafficStatus').value = c.status || 'active';
    document.getElementById('adminTrafficTicketIssuedDate').value = c.citation_issued_date || '';
    document.getElementById('adminTrafficNoaSentDate').value = c.noa_sent_date || '';
    document.getElementById('adminTrafficDiscovery').checked = c.discovery == 1;
    document.getElementById('adminTrafficNote').value = c.note || '';
    document.getElementById('adminTrafficReferralSource').value = c.referral_source || '';

    document.getElementById('adminTrafficModal').style.display = 'flex';
}

function closeAdminTrafficModal() {
    document.getElementById('adminTrafficModal').style.display = 'none';
    editingTrafficCaseId = null;
}

async function saveAdminTrafficCase() {
    if (!editingTrafficCaseId) return;

    const data = {
        id: editingTrafficCaseId,
        client_name: document.getElementById('adminTrafficClientName').value.trim(),
        client_phone: document.getElementById('adminTrafficClientPhone').value.trim(),
        court: document.getElementById('adminTrafficCourt').value.trim(),
        court_date: document.getElementById('adminTrafficCourtDate').value || null,
        charge: document.getElementById('adminTrafficCharge').value.trim(),
        case_number: document.getElementById('adminTrafficCaseNumber').value.trim(),
        prosecutor_offer: document.getElementById('adminTrafficOffer').value.trim(),
        disposition: document.getElementById('adminTrafficDisposition').value,
        status: document.getElementById('adminTrafficStatus').value,
        citation_issued_date: document.getElementById('adminTrafficTicketIssuedDate').value || null,
        noa_sent_date: document.getElementById('adminTrafficNoaSentDate').value || null,
        discovery: document.getElementById('adminTrafficDiscovery').checked,
        note: document.getElementById('adminTrafficNote').value.trim(),
        referral_source: document.getElementById('adminTrafficReferralSource').value.trim(),
        paid: false
    };

    try {
        const result = await apiCall('api/traffic.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (result.success) {
            closeAdminTrafficModal();
            loadAdminTrafficCases();
        } else {
            alert(result.error || 'Error saving case');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error saving case');
    }
}

async function deleteAdminTrafficCase(id, clientName) {
    if (!confirm(`Delete traffic case for "${clientName}"?`)) return;

    try {
        const result = await apiCall('api/traffic.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        if (result.success) {
            loadAdminTrafficCases();
        } else {
            alert(result.error || 'Error deleting case');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error deleting case');
    }
}
