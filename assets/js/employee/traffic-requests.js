/**
 * Employee dashboard - Traffic Requests tab functions.
 * Only loaded for employees with can_request_traffic permission (not Chong).
 */

let empTrafficRequests = [];

async function loadEmpTrafficRequests() {
    try {
        const data = await apiCall('api/traffic_requests.php?type=sent');
        empTrafficRequests = data.requests || [];
        renderEmpTrafficRequests();
    } catch (err) {
        console.error('Error loading requests:', err);
    }
}

function renderEmpTrafficRequests() {
    const container = document.getElementById('empMyTrafficRequests');
    if (!container) return;

    // Update count badge
    const badge = document.getElementById('empReqCountBadge');
    if (badge) badge.textContent = `${empTrafficRequests.length} request${empTrafficRequests.length !== 1 ? 's' : ''}`;

    if (empTrafficRequests.length === 0) {
        container.innerHTML = '<p style="padding: 24px; text-align: center; color: #8b8fa3; font-size: 12px; font-family: Outfit, sans-serif;">No requests yet</p>';
        return;
    }

    container.innerHTML = empTrafficRequests.map(r => {
        const statusBg = { pending: '#fef3c7', accepted: '#d1fae5', denied: '#fee2e2' };
        const statusColor = { pending: '#92400e', accepted: '#065f46', denied: '#991b1b' };
        const statusBorder = { pending: '#fde68a', accepted: '#a7f3d0', denied: '#fecaca' };
        const statusLabel = { pending: 'Pending', accepted: 'Accepted', denied: 'Denied' };
        const bg = statusBg[r.status] || '#f3f4f6';
        const color = statusColor[r.status] || '#6b7280';
        const border = statusBorder[r.status] || '#e5e7eb';
        const label = statusLabel[r.status] || r.status;
        const courtDate = r.court_date ? new Date(r.court_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' }) : '-';

        return `
            <div style="padding: 14px 20px; display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 16px; border-bottom: 1px solid #f0f1f3; font-family: 'Outfit', sans-serif; transition: background 0.15s; cursor: pointer;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                <div>
                    <div style="font-size: 13px; font-weight: 600; color: #1a1a2e; margin-bottom: 3px;">${escapeHtml(r.client_name)}</div>
                    <div style="font-size: 11px; color: #8b8fa3; display: flex; align-items: center; gap: 5px;">
                        <span>${r.case_number || r.court || '-'}</span>
                        <span style="color: #c4c7d0; font-size: 9px;">&middot;</span>
                        <span>${courtDate}</span>
                        ${r.charge ? `<span style="color: #c4c7d0; font-size: 9px;">&middot;</span><span>${escapeHtml(r.charge)}</span>` : ''}
                    </div>
                    ${r.deny_reason ? `<div style="font-size: 11px; color: #dc2626; margin-top: 4px;">Reason: ${escapeHtml(r.deny_reason)}</div>` : ''}
                </div>
                <span style="font-size: 11px; font-weight: 600; padding: 4px 11px; border-radius: 20px; background: ${bg}; color: ${color}; border: 1px solid ${border};">${label}</span>
            </div>
        `;
    }).join('');
}

function filterEmpTrafficRequests() {
    const q = (document.getElementById('empReqSearch')?.value || '').toLowerCase();
    if (!q) {
        renderEmpTrafficRequests();
        return;
    }
    const filtered = empTrafficRequests.filter(r =>
        (r.client_name || '').toLowerCase().includes(q) ||
        (r.court || '').toLowerCase().includes(q) ||
        (r.charge || '').toLowerCase().includes(q) ||
        (r.case_number || '').toLowerCase().includes(q) ||
        (r.status || '').toLowerCase().includes(q)
    );
    renderEmpTrafficRequestsList(filtered);
}

function renderEmpTrafficRequestsList(list) {
    const container = document.getElementById('empMyTrafficRequests');
    if (!container) return;

    const badge = document.getElementById('empReqCountBadge');
    if (badge) badge.textContent = `${list.length} request${list.length !== 1 ? 's' : ''}`;

    if (list.length === 0) {
        container.innerHTML = '<p style="padding: 24px; text-align: center; color: #8b8fa3; font-size: 12px; font-family: Outfit, sans-serif;">No matching requests</p>';
        return;
    }

    container.innerHTML = list.map(r => {
        const statusBg = { pending: '#fef3c7', accepted: '#d1fae5', denied: '#fee2e2' };
        const statusColor = { pending: '#92400e', accepted: '#065f46', denied: '#991b1b' };
        const statusBorder = { pending: '#fde68a', accepted: '#a7f3d0', denied: '#fecaca' };
        const statusLabel = { pending: 'Pending', accepted: 'Accepted', denied: 'Denied' };
        const bg = statusBg[r.status] || '#f3f4f6';
        const color = statusColor[r.status] || '#6b7280';
        const border = statusBorder[r.status] || '#e5e7eb';
        const label = statusLabel[r.status] || r.status;
        const courtDate = r.court_date ? new Date(r.court_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' }) : '-';

        return `
            <div style="padding: 14px 20px; display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 16px; border-bottom: 1px solid #f0f1f3; font-family: 'Outfit', sans-serif; transition: background 0.15s; cursor: pointer;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                <div>
                    <div style="font-size: 13px; font-weight: 600; color: #1a1a2e; margin-bottom: 3px;">${escapeHtml(r.client_name)}</div>
                    <div style="font-size: 11px; color: #8b8fa3; display: flex; align-items: center; gap: 5px;">
                        <span>${r.case_number || r.court || '-'}</span>
                        <span style="color: #c4c7d0; font-size: 9px;">&middot;</span>
                        <span>${courtDate}</span>
                        ${r.charge ? `<span style="color: #c4c7d0; font-size: 9px;">&middot;</span><span>${escapeHtml(r.charge)}</span>` : ''}
                    </div>
                    ${r.deny_reason ? `<div style="font-size: 11px; color: #dc2626; margin-top: 4px;">Reason: ${escapeHtml(r.deny_reason)}</div>` : ''}
                </div>
                <span style="font-size: 11px; font-weight: 600; padding: 4px 11px; border-radius: 20px; background: ${bg}; color: ${color}; border: 1px solid ${border};">${label}</span>
            </div>
        `;
    }).join('');
}

document.getElementById('empTrafficRequestForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const data = {
        client_name: document.getElementById('empReqClientName').value.trim(),
        client_phone: document.getElementById('empReqClientPhone').value.trim(),
        client_email: document.getElementById('empReqClientEmail').value.trim(),
        court: document.getElementById('empReqCourt').value.trim(),
        court_date: document.getElementById('empReqCourtDate').value || null,
        charge: document.getElementById('empReqCharge').value.trim(),
        case_number: document.getElementById('empReqCaseNumber').value.trim(),
        citation_issued_date: document.getElementById('empReqIssuedDate').value || null,
        note: document.getElementById('empReqNote').value.trim()
    };

    if (!data.client_name) {
        alert('Client name is required');
        return;
    }

    try {
        const result = await apiCall('api/traffic_requests.php', {
            method: 'POST',
            body: JSON.stringify(data)
        });

        if (result.success) {
            alert('Request submitted successfully!');
            this.reset();
            loadEmpTrafficRequests();
        } else {
            alert(result.error || 'Error submitting request');
        }
    } catch (err) {
        alert(err.message || 'Error submitting request');
    }
});
