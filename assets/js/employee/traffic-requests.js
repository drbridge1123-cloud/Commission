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

    if (empTrafficRequests.length === 0) {
        container.innerHTML = '<p style="padding: 24px; text-align: center; color: #8b8fa3; font-size: 12px; font-family: Outfit, sans-serif;">No requests yet</p>';
        return;
    }

    container.innerHTML = empTrafficRequests.map(r => {
        const statusBg = { pending: '#fef3c7', accepted: '#d1fae5', denied: '#fee2e2' };
        const statusColor = { pending: '#92400e', accepted: '#065f46', denied: '#991b1b' };
        const bg = statusBg[r.status] || '#f3f4f6';
        const color = statusColor[r.status] || '#6b7280';
        const courtDate = r.court_date ? new Date(r.court_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' }) : '-';

        return `
            <div style="padding: 12px 16px; border-bottom: 1px solid #f0f1f3; font-family: 'Outfit', sans-serif;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <span style="font-size: 13px; font-weight: 600; color: #1a1a2e;">${escapeHtml(r.client_name)}</span>
                    <span style="display:inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; background: ${bg}; color: ${color};">${r.status}</span>
                </div>
                <div style="font-size: 11px; color: #8b8fa3;">
                    ${r.court ? escapeHtml(r.court) : '-'} &bull; ${courtDate}
                    ${r.charge ? ' &bull; ' + escapeHtml(r.charge) : ''}
                </div>
                ${r.deny_reason ? `<div style="font-size: 11px; color: #dc2626; margin-top: 4px;">Reason: ${escapeHtml(r.deny_reason)}</div>` : ''}
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
