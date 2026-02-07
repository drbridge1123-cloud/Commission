/**
 * Admin Dashboard - Traffic Cases functions.
 */

async function loadAdminTrafficCases() {
    try {
        const data = await apiCall('api/traffic.php?status=all');
        adminTrafficAllCases = data.cases || [];
        updateTrafficStats();
        applyAdminTrafficFilters();
    } catch (err) {
        console.error('Error loading traffic cases:', err);
    }
}

function updateTrafficStats() {
    const total = adminTrafficAllCases.length;
    const active = adminTrafficAllCases.filter(c => c.status === 'active').length;
    const resolved = adminTrafficAllCases.filter(c => c.status === 'resolved').length;
    const dismissed = adminTrafficAllCases.filter(c => c.disposition === 'dismissed').length;
    const amended = adminTrafficAllCases.filter(c => c.disposition === 'amended').length;
    const pendingReq = myTrafficRequests.filter(r => r.status === 'pending').length;

    const setEl = (id, val) => { const el = document.getElementById(id); if(el) el.textContent = val; };
    setEl('trafficStatTotal', total);
    setEl('trafficStatActive', active);
    setEl('trafficStatDismissed', dismissed);
    setEl('trafficStatAmended', amended);
    setEl('trafficStatPendingReq', pendingReq);

    setEl('trafficOverviewActive', active);
    setEl('trafficOverviewDone', resolved);

    setEl('trafficCountAll', total);
    setEl('trafficCountActive', active);
    setEl('trafficCountDone', resolved);
}

function switchAdminTrafficTab(tab) {
    adminTrafficSidebarTab = tab;

    document.querySelectorAll('[id^="adminTrafficTab-"]').forEach(btn => btn.classList.remove('active'));
    document.getElementById(`adminTrafficTab-${tab}`)?.classList.add('active');

    const contentEl = document.getElementById('adminTrafficSidebarContent');
    if (!contentEl) return;

    if (tab === 'all') {
        const total = adminTrafficAllCases.length;
        const active = adminTrafficAllCases.filter(c => c.status === 'active').length;
        const done = adminTrafficAllCases.filter(c => c.status === 'resolved').length;

        contentEl.innerHTML = `
            <div style="font-size: 11px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Overview</div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <div style="display: flex; justify-content: space-between; font-family: 'Outfit', sans-serif;">
                    <span style="font-size: 12px; color: #3d3f4e;">Total Cases</span>
                    <span style="font-size: 13px; font-weight: 700; color: #1a1a2e;">${total}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-family: 'Outfit', sans-serif;">
                    <span style="font-size: 12px; color: #3b82f6;">Active</span>
                    <span style="font-size: 13px; font-weight: 700; color: #3b82f6;">${active}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-family: 'Outfit', sans-serif;">
                    <span style="font-size: 12px; color: #0d9488;">Done</span>
                    <span style="font-size: 13px; font-weight: 700; color: #0d9488;">${done}</span>
                </div>
            </div>
        `;
    } else if (tab === 'referral') {
        const grouped = {};
        adminTrafficAllCases.forEach(c => {
            const ref = c.referral_source || 'Unknown';
            if (!grouped[ref]) grouped[ref] = { count: 0 };
            grouped[ref].count++;
        });

        const sorted = Object.entries(grouped).sort((a, b) => b[1].count - a[1].count);
        contentEl.innerHTML = `
            <div style="font-size: 11px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">By Referral</div>
            <div style="display: flex; flex-direction: column; gap: 4px; max-height: 250px; overflow-y: auto;">
                ${sorted.map(([name, data]) => `
                    <div onclick="filterByReferral('${name}')" style="display: flex; justify-content: space-between; padding: 6px 8px; border-radius: 6px; cursor: pointer; font-family: 'Outfit', sans-serif; transition: background 0.1s;" onmouseover="this.style.background='#f5f5f7'" onmouseout="this.style.background='transparent'">
                        <span style="font-size: 12px; color: #3d3f4e;">${name}</span>
                        <span style="font-size: 12px; font-weight: 600; color: #5c5f73;">${data.count}</span>
                    </div>
                `).join('')}
            </div>
        `;
    } else if (tab === 'court') {
        const grouped = {};
        adminTrafficAllCases.forEach(c => {
            const court = c.court || 'Unknown';
            if (!grouped[court]) grouped[court] = { count: 0 };
            grouped[court].count++;
        });

        const sorted = Object.entries(grouped).sort((a, b) => b[1].count - a[1].count);
        contentEl.innerHTML = `
            <div style="font-size: 11px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">By Court</div>
            <div style="display: flex; flex-direction: column; gap: 4px; max-height: 250px; overflow-y: auto;">
                ${sorted.map(([name, data]) => `
                    <div onclick="filterByCourt('${name}')" style="display: flex; justify-content: space-between; padding: 6px 8px; border-radius: 6px; cursor: pointer; font-family: 'Outfit', sans-serif; transition: background 0.1s;" onmouseover="this.style.background='#f5f5f7'" onmouseout="this.style.background='transparent'">
                        <span style="font-size: 12px; color: #3d3f4e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${name}</span>
                        <span style="font-size: 12px; font-weight: 600; color: #5c5f73; flex-shrink: 0; margin-left: 8px;">${data.count}</span>
                    </div>
                `).join('')}
            </div>
        `;
    } else if (tab === 'year') {
        const grouped = {};
        adminTrafficAllCases.forEach(c => {
            const year = c.court_date ? new Date(c.court_date).getFullYear() : 'Unknown';
            if (!grouped[year]) grouped[year] = { count: 0 };
            grouped[year].count++;
        });

        const sorted = Object.entries(grouped).sort((a, b) => b[0] - a[0]);
        contentEl.innerHTML = `
            <div style="font-size: 11px; color: #8b8fa3; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">By Year</div>
            <div style="display: flex; flex-direction: column; gap: 4px;">
                ${sorted.map(([year, data]) => `
                    <div onclick="filterByYear('${year}')" style="display: flex; justify-content: space-between; padding: 6px 8px; border-radius: 6px; cursor: pointer; font-family: 'Outfit', sans-serif; transition: background 0.1s;" onmouseover="this.style.background='#f5f5f7'" onmouseout="this.style.background='transparent'">
                        <span style="font-size: 12px; color: #3d3f4e;">${year}</span>
                        <span style="font-size: 12px; font-weight: 600; color: #5c5f73;">${data.count}</span>
                    </div>
                `).join('')}
            </div>
        `;
    }
}

function filterByReferral(name) {
    document.getElementById('trafficFilterLabel').textContent = `Referral: ${name}`;
    adminTrafficCases = adminTrafficAllCases.filter(c => (c.referral_source || 'Unknown') === name);
    renderAdminTrafficCases();
}

function filterByCourt(name) {
    document.getElementById('trafficFilterLabel').textContent = `Court: ${name}`;
    adminTrafficCases = adminTrafficAllCases.filter(c => (c.court || 'Unknown') === name);
    renderAdminTrafficCases();
}

function filterByYear(year) {
    document.getElementById('trafficFilterLabel').textContent = `Year: ${year}`;
    if (year === 'Unknown') {
        adminTrafficCases = adminTrafficAllCases.filter(c => !c.court_date);
    } else {
        adminTrafficCases = adminTrafficAllCases.filter(c => c.court_date && new Date(c.court_date).getFullYear() == year);
    }
    renderAdminTrafficCases();
}

function filterAdminTraffic(status, el) {
    adminTrafficFilter = status;
    document.getElementById('trafficFilterLabel').textContent = status === 'all' ? 'All Cases' : (status === 'active' ? 'Active Cases' : 'Done Cases');

    document.querySelectorAll('[id^="adminTrafficStatusBtn-"]').forEach(c => c.classList.remove('active'));
    if (el) el.classList.add('active');
    applyAdminTrafficFilters();
}

function searchAdminTraffic(term) {
    adminTrafficSearchTerm = term.toLowerCase();
    applyAdminTrafficFilters();
}

function applyAdminTrafficFilters() {
    let filtered = [...adminTrafficAllCases];

    if (adminTrafficFilter !== 'all') {
        filtered = filtered.filter(c => c.status === adminTrafficFilter);
    }

    if (adminTrafficSearchTerm) {
        filtered = filtered.filter(c =>
            (c.client_name || '').toLowerCase().includes(adminTrafficSearchTerm) ||
            (c.court || '').toLowerCase().includes(adminTrafficSearchTerm) ||
            (c.charge || '').toLowerCase().includes(adminTrafficSearchTerm) ||
            (c.case_number || '').toLowerCase().includes(adminTrafficSearchTerm) ||
            (c.requester_name || '').toLowerCase().includes(adminTrafficSearchTerm)
        );
    }

    adminTrafficCases = filtered;
    renderAdminTrafficCases();
}

function renderAdminTrafficCases() {
    const tbody = document.getElementById('adminTrafficTableBody');
    if (!tbody) return;

    if (adminTrafficCases.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" style="padding: 32px 16px; text-align: center; color: #8b8fa3; font-size: 12px; font-family: Outfit, sans-serif;">No traffic cases found</td></tr>';
        updateTrafficFooter([]);
        return;
    }

    tbody.innerHTML = adminTrafficCases.map(c => {
        const courtDate = c.court_date ? new Date(c.court_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' }) : '-';
        const noaDate = c.noa_sent_date ? new Date(c.noa_sent_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '<span class="mute">-</span>';
        const discoveryStatus = c.discovery ? '<span style="color: #0d9488; font-weight: 600;">✓</span>' : '<span class="mute">-</span>';

        let dispositionBadge = '';
        if (c.disposition === 'dismissed') {
            dispositionBadge = '<span class="stat-badge paid">Dismissed</span>';
        } else if (c.disposition === 'amended') {
            dispositionBadge = '<span class="stat-badge unpaid">Amended</span>';
        } else {
            dispositionBadge = '<span class="stat-badge pending">Pending</span>';
        }

        let statusBadge = c.status === 'active'
            ? '<span class="stat-badge in_progress">Active</span>'
            : '<span style="background: #f0f1f3; color: #5c5f73; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase;">Done</span>';

        return `
            <tr onclick="editAdminTrafficCase(${c.id})">
                <td style="font-weight: 600;">${c.client_name || '-'}</td>
                <td>${c.court || '-'}</td>
                <td>${courtDate}</td>
                <td>${c.charge || '-'}</td>
                <td class="c">${noaDate}</td>
                <td class="c">${discoveryStatus}</td>
                <td>${dispositionBadge}</td>
                <td class="c">${statusBadge}</td>
                <td class="mute" style="font-size: 11px;">${c.requester_name || '-'}</td>
                <td class="c">
                    <button onclick="event.stopPropagation(); editAdminTrafficCase(${c.id})" class="act-link" title="Edit">Edit</button>
                </td>
            </tr>
        `;
    }).join('');

    updateTrafficFooter(adminTrafficCases);
}

function updateTrafficFooter(cases) {
    const count = cases.length;
    const dismissed = cases.filter(c => c.disposition === 'dismissed').length;
    const amended = cases.filter(c => c.disposition === 'amended').length;

    const setEl = (id, val) => { const el = document.getElementById(id); if(el) el.textContent = val; };
    setEl('trafficTableCount', count);
    setEl('trafficFootDismissed', dismissed);
    setEl('trafficFootAmended', amended);
}

async function loadMyTrafficRequests() {
    try {
        const data = await apiCall('api/traffic_requests.php');
        myTrafficRequests = data.requests || [];
        renderMyTrafficRequests();
        updateTrafficStats();
    } catch (err) {
        console.error('Error loading traffic requests:', err);
    }
}

function filterMyRequests(searchTerm) {
    renderMyTrafficRequests(searchTerm);
}

function renderMyTrafficRequests(searchTerm = '') {
    const container = document.getElementById('myTrafficRequests');
    if (!container) return;

    updateTrafficStats();

    const filteredRequests = searchTerm.trim()
        ? myTrafficRequests.filter(r => {
            const term = searchTerm.toLowerCase();
            return (r.client_name || '').toLowerCase().includes(term) ||
                   (r.court || '').toLowerCase().includes(term) ||
                   (r.case_number || '').toLowerCase().includes(term) ||
                   (r.requester_name || '').toLowerCase().includes(term);
        })
        : myTrafficRequests;

    if (filteredRequests.length === 0) {
        container.innerHTML = searchTerm.trim()
            ? '<p style="padding: 12px; text-align: center; color: #8b8fa3; font-size: 11px; font-family: Outfit, sans-serif;">No matching requests</p>'
            : '<p style="padding: 12px; text-align: center; color: #8b8fa3; font-size: 11px; font-family: Outfit, sans-serif;">No requests yet</p>';
        return;
    }

    container.innerHTML = filteredRequests.map(r => {
        let statusClass = r.status === 'pending' ? 'pending' : (r.status === 'accepted' ? 'approved' : 'denied');
        let statusText = r.status === 'pending' ? 'Pending' : (r.status === 'accepted' ? 'Accepted' : 'Denied');
        const courtDateStr = r.court_date ? new Date(r.court_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '-';
        const requesterName = r.requester_name || 'Unknown';
        const respondedAt = r.responded_at ? new Date(r.responded_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '';

        return `
            <div class="req-item" onclick="viewTrafficRequest(${r.id})">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <span class="req-name">${escapeHtml(r.client_name)}</span>
                        <span class="req-status ${statusClass}">${statusText}</span>
                    </div>
                </div>
                <div class="req-meta">${escapeHtml(requesterName)} → Chong · ${escapeHtml(r.court || '-')} · ${courtDateStr}${respondedAt ? ' · ' + respondedAt : ''}</div>
            </div>
        `;
    }).join('');
}

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

// Traffic Request Form Submit
document.getElementById('trafficRequestForm')?.addEventListener('submit', async function(e) {
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
            alert('Request submitted successfully! Chong will receive a notification.');
            this.reset();
            loadMyTrafficRequests();
        } else {
            alert(result.error || 'Error submitting request');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error submitting request');
    }
});

// Admin Traffic Case Edit/Delete Functions
function editAdminTrafficCase(id) {
    const c = adminTrafficCases.find(c => c.id == id);
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
