/**
 * Manager Dashboard - Attorney Progress Tab
 * Read-only view of Chong's demand, litigation, and traffic cases.
 * No financial/commission data exposed.
 */

const ATTORNEY_ID = 2; // Chong
let currentAttySubTab = 'demand';
let attyDemandData = [];
let attyLitigationData = [];
let attyTrafficData = [];
let attyMyRequests = [];

function attyFmtDate(d) {
    if (!d) return '-';
    let str = d;
    if (d.includes(' ')) str = d.replace(' ', 'T');
    else if (!d.includes('T')) str = d + 'T00:00:00';
    const dt = new Date(str);
    if (isNaN(dt.getTime())) return '-';
    return String(dt.getMonth() + 1).padStart(2, '0') + '/' +
           String(dt.getDate()).padStart(2, '0') + '/' +
           dt.getFullYear();
}

function filterAttyTable(tab) {
    const searchIds = { demand: 'attyDemandSearch', litigation: 'attyLitigationSearch', traffic: 'attyTrafficSearch', requests: 'attyRequestsSearch' };
    const tbodyIds = { demand: 'attyDemandBody', litigation: 'attyLitigationBody', traffic: 'attyTrafficBody', requests: 'attyMyRequestsBody' };
    const input = document.getElementById(searchIds[tab]);
    const tbody = document.getElementById(tbodyIds[tab]);
    if (!input || !tbody) return;
    const q = input.value.toLowerCase().trim();
    const rows = tbody.querySelectorAll('tr');
    let visible = 0;
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        const text = row.textContent.toLowerCase();
        const show = !q || text.includes(q);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
}

function switchAttySubTab(tab) {
    currentAttySubTab = tab;
    ['demand', 'litigation', 'traffic', 'requests'].forEach(t => {
        const panel = document.getElementById('attySubContent-' + t);
        if (panel) panel.style.display = t === tab ? '' : 'none';
        const btn = document.getElementById('attyPill-' + t);
        if (btn) btn.classList.toggle('active', t === tab);
    });

    // Show/hide "Request New Demand" button
    const newBtn = document.getElementById('attyNewDemandBtn');
    if (newBtn) newBtn.style.display = (tab === 'demand' || tab === 'requests') ? '' : 'none';

    if (tab === 'demand') loadAttorneyDemand();
    else if (tab === 'litigation') loadAttorneyLitigation();
    else if (tab === 'traffic') loadAttorneyTraffic();
    else if (tab === 'requests') loadMyDemandRequests();
}

function initAttorneyProgress() {
    loadAttorneyStats();
    loadAttorneyDemand();
    loadMyDemandRequests(); // for badge count
}

async function loadAttorneyStats() {
    try {
        const result = await apiCall(`api/attorney_progress.php?type=stats&attorney_id=${ATTORNEY_ID}`);
        document.getElementById('attyStatDemand').textContent = result.demand_count || 0;
        document.getElementById('attyStatLitigation').textContent = result.litigation_count || 0;
        document.getElementById('attyStatTraffic').textContent = result.traffic_count || 0;
    } catch (err) {
        console.error('Error loading attorney stats:', err);
    }
}

// ============================================
// Demand
// ============================================

async function loadAttorneyDemand() {
    try {
        const result = await apiCall(`api/attorney_progress.php?type=demand&attorney_id=${ATTORNEY_ID}`);
        attyDemandData = result.cases || [];
        renderAttorneyDemand(attyDemandData);
    } catch (err) {
        console.error('Error loading attorney demand:', err);
    }
}

function renderAttorneyDemand(cases) {
    const tbody = document.getElementById('attyDemandBody');
    document.getElementById('attyDemandCount').textContent = cases.length;

    if (cases.length === 0) {
        tbody.innerHTML = '<tr><td colspan="11" style="text-align:center; padding:40px; color:#8b8fa3; font-size:12px;">No demand cases</td></tr>';
        return;
    }

    const stageColors = {
        'demand_review': { bg: '#f3e8ff', color: '#7c3aed', text: 'Demand Review' },
        'demand_write': { bg: '#e0e7ff', color: '#4338ca', text: 'Demand Write' },
        'demand_sent': { bg: '#fef3c7', color: '#b45309', text: 'Demand Sent' },
        'negotiate': { bg: '#d1fae5', color: '#059669', text: 'Negotiate' }
    };

    tbody.innerHTML = cases.map(c => {
        const ds = c.deadline_status || {};
        let daysText = ds.message || '-';
        let rowClass = '';

        if (c.top_offer_date) {
            daysText = '<span class="tv3-badge accepted">Completed</span>';
        } else if (ds.days !== undefined && ds.days !== null) {
            if (ds.days < 0) rowClass = 'row-overdue';
            else if (ds.days <= 14) rowClass = 'row-critical';
        }

        const si = stageColors[c.stage] || { bg: '#f3f4f6', color: '#6b7280', text: c.stage || '-' };
        const stageBadge = c.stage
            ? `<span class="tv3-badge" style="background:${si.bg};color:${si.color};">${si.text}</span>`
            : '-';

        const topOfferCell = c.top_offer_date
            ? `<span style="color:#059669;font-weight:600;">&#10003;</span> ${attyFmtDate(c.top_offer_date)}`
            : '-';

        const statusBadge = c.status === 'in_progress'
            ? '<span class="tv3-badge" style="background:#dbeafe;color:#1d4ed8;">in progress</span>'
            : `<span class="tv3-badge">${escapeHtml(c.status || '')}</span>`;

        return `<tr class="${rowClass}">
            <td style="font-family:monospace;font-size:12px;">${escapeHtml(c.case_number || '')}</td>
            <td style="font-weight:500;">${escapeHtml(c.client_name)}</td>
            <td>${escapeHtml(c.case_type || '-')}</td>
            <td>${stageBadge}</td>
            <td>${attyFmtDate(c.assigned_date)}</td>
            <td>${c.demand_out_date ? '<span style="color:#059669;">&#10003;</span> ' + attyFmtDate(c.demand_out_date) : '-'}</td>
            <td>${c.negotiate_date ? '<span style="color:#059669;">&#10003;</span> ' + attyFmtDate(c.negotiate_date) : '-'}</td>
            <td>${topOfferCell}</td>
            <td>${attyFmtDate(c.demand_deadline)}</td>
            <td class="${ds.class || ''}">${daysText}</td>
            <td>${statusBadge}</td>
        </tr>`;
    }).join('');
}

// ============================================
// Litigation
// ============================================

async function loadAttorneyLitigation() {
    try {
        const result = await apiCall(`api/attorney_progress.php?type=litigation&attorney_id=${ATTORNEY_ID}`);
        attyLitigationData = result.cases || [];
        renderAttorneyLitigation(attyLitigationData);
    } catch (err) {
        console.error('Error loading attorney litigation:', err);
    }
}

function renderAttorneyLitigation(cases) {
    const tbody = document.getElementById('attyLitigationBody');
    document.getElementById('attyLitigationCount').textContent = cases.length;

    if (cases.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:40px; color:#8b8fa3; font-size:12px;">No litigation cases</td></tr>';
        return;
    }

    tbody.innerHTML = cases.map(c => {
        const statusBadge = c.status === 'in_progress'
            ? '<span class="tv3-badge" style="background:#dbeafe;color:#1d4ed8;">in progress</span>'
            : `<span class="tv3-badge">${escapeHtml(c.status || '')}</span>`;

        const noteText = c.note
            ? `<span style="font-size:11px;color:#8b8fa3;" title="${escapeHtml(c.note)}">${escapeHtml(c.note.substring(0, 50))}${c.note.length > 50 ? '...' : ''}</span>`
            : '<span style="color:#c4c7d0;">—</span>';

        return `<tr>
            <td style="font-family:monospace;font-size:12px;">${escapeHtml(c.case_number || '')}</td>
            <td style="font-weight:500;">${escapeHtml(c.client_name)}</td>
            <td>${escapeHtml(c.case_type || '-')}</td>
            <td>${escapeHtml(c.resolution_type || '-')}</td>
            <td>${attyFmtDate(c.litigation_start_date)}</td>
            <td>${statusBadge}</td>
            <td>${noteText}</td>
        </tr>`;
    }).join('');
}

// ============================================
// Traffic
// ============================================

async function loadAttorneyTraffic() {
    try {
        const result = await apiCall(`api/attorney_progress.php?type=traffic&attorney_id=${ATTORNEY_ID}`);
        attyTrafficData = result.cases || [];
        renderAttorneyTraffic(attyTrafficData);
    } catch (err) {
        console.error('Error loading attorney traffic:', err);
    }
}

function renderAttorneyTraffic(cases) {
    const tbody = document.getElementById('attyTrafficBody');
    document.getElementById('attyTrafficCount').textContent = cases.length;

    if (cases.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:40px; color:#8b8fa3; font-size:12px;">No traffic cases</td></tr>';
        return;
    }

    tbody.innerHTML = cases.map(c => {
        const discoveryIcon = c.discovery == 1
            ? '<span style="color:#059669;font-weight:600;">&#10003;</span>'
            : '<span style="color:#c4c7d0;">&#9744;</span>';

        const dispColors = {
            'dismissed': { bg: '#d1fae5', color: '#059669' },
            'amended': { bg: '#fef3c7', color: '#b45309' },
            'pending': { bg: '#f3f4f6', color: '#6b7280' }
        };
        const dc = dispColors[c.disposition] || { bg: '#f3f4f6', color: '#6b7280' };
        const dispBadge = `<span class="tv3-badge" style="background:${dc.bg};color:${dc.color};">${escapeHtml(c.disposition || 'pending')}</span>`;

        const statusBadge = c.status === 'active'
            ? '<span class="tv3-badge" style="background:#dbeafe;color:#1d4ed8;">active</span>'
            : '<span class="tv3-badge" style="background:#d1fae5;color:#059669;">resolved</span>';

        return `<tr>
            <td style="font-weight:500;">${escapeHtml(c.client_name)}</td>
            <td style="font-family:monospace;font-size:11px;">${escapeHtml(c.case_number || '-')}</td>
            <td>${escapeHtml(c.court || '-')}</td>
            <td>${escapeHtml(c.charge || '-')}</td>
            <td>${attyFmtDate(c.court_date)}</td>
            <td class="c">${discoveryIcon}</td>
            <td>${dispBadge}</td>
            <td class="c">${statusBadge}</td>
            <td>${escapeHtml(c.referral_source || '-')}</td>
        </tr>`;
    }).join('');
}

// ============================================
// My Demand Requests
// ============================================

async function loadMyDemandRequests() {
    try {
        const result = await apiCall('api/demand_requests.php');
        attyMyRequests = result.requests || [];
        renderMyDemandRequests(attyMyRequests);

        // Update badge
        const pending = attyMyRequests.filter(r => r.status === 'pending').length;
        const badge = document.getElementById('attyReqBadge');
        if (pending > 0) {
            badge.textContent = pending;
            badge.style.display = 'inline';
        } else {
            badge.style.display = 'none';
        }
    } catch (err) {
        console.error('Error loading demand requests:', err);
    }
}

function renderMyDemandRequests(requests) {
    const tbody = document.getElementById('attyMyRequestsBody');
    document.getElementById('attyMyRequestsCount').textContent = requests.length;

    if (requests.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:40px; color:#8b8fa3; font-size:12px;">No requests sent yet</td></tr>';
        return;
    }

    tbody.innerHTML = requests.map(r => {
        const statusColors = {
            'pending': { bg: '#fef3c7', color: '#92400e' },
            'accepted': { bg: '#d1fae5', color: '#065f46' },
            'denied': { bg: '#fee2e2', color: '#991b1b' }
        };
        const sc = statusColors[r.status] || { bg: '#f3f4f6', color: '#6b7280' };
        const statusBadge = `<span class="tv3-badge" style="background:${sc.bg};color:${sc.color};">${r.status}</span>`;

        const reasonText = r.status === 'denied' && r.deny_reason
            ? `<span style="color:#dc2626;font-size:11px;">${escapeHtml(r.deny_reason)}</span>`
            : '<span style="color:#c4c7d0;">—</span>';

        return `<tr>
            <td style="font-weight:500;">${escapeHtml(r.client_name)}</td>
            <td style="font-family:monospace;font-size:11px;">${escapeHtml(r.case_number || '-')}</td>
            <td>${escapeHtml(r.case_type || 'Auto')}</td>
            <td style="font-size:11px;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${escapeHtml(r.note || '')}">${escapeHtml(r.note || '—')}</td>
            <td class="c">${statusBadge}</td>
            <td style="font-size:12px;">${attyFmtDate(r.created_at)}</td>
            <td style="font-size:12px;">${attyFmtDate(r.responded_at)}</td>
            <td>${reasonText}</td>
        </tr>`;
    }).join('');
}

// ============================================
// Demand Request Form
// ============================================

function openDemandRequestForm() {
    document.getElementById('demandRequestForm').reset();
    openModal('demandRequestModal');
}

async function submitDemandRequest(event) {
    event.preventDefault();
    const form = event.target;

    const data = {
        client_name: form.client_name.value,
        case_number: form.case_number.value,
        case_type: form.case_type.value,
        note: form.note.value
    };

    if (!data.client_name) {
        alert('Client name is required');
        return;
    }

    try {
        const result = await apiCall('api/demand_requests.php', {
            method: 'POST',
            body: JSON.stringify(data)
        });

        if (result.success) {
            closeModal('demandRequestModal');
            loadMyDemandRequests();
            alert('Demand request sent successfully!');
        } else {
            alert(result.error || 'Error sending request');
        }
    } catch (err) {
        console.error('Error submitting demand request:', err);
        alert(err.message || 'Error sending request');
    }
}
