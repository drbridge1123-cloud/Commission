/**
 * Admin Dashboard - Deadline extension requests functions.
 */

async function loadDeadlineRequestsBadge() {
    try {
        const data = await apiCall('api/deadline_requests.php?status=pending');
        const badge = document.getElementById('deadlineRequestBadge');
        const pendingCount = data.pending_count || 0;
        badge.textContent = pendingCount;
        badge.classList.toggle('hidden', pendingCount === 0);
    } catch (err) {
        console.error('Error loading deadline requests badge:', err);
    }
}

async function loadDeadlineRequests() {
    try {
        const status = document.getElementById('filterDeadlineStatus').value;
        const data = await apiCall(`api/deadline_requests.php?status=${status}`);
        deadlineRequests = data.requests || [];
        renderDeadlineRequests();

        // Update badge
        const badge = document.getElementById('deadlineRequestBadge');
        const pendingCount = data.pending_count || 0;
        badge.textContent = pendingCount;
        badge.classList.toggle('hidden', pendingCount === 0);
    } catch (err) {
        console.error('Error loading deadline requests:', err);
    }
}

function renderDeadlineRequests() {
    const tbody = document.getElementById('deadlineRequestsBody');
    const countEl = document.getElementById('deadlineRequestsCount');

    if (!deadlineRequests || deadlineRequests.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; padding: 40px; color: #6b7280;">No deadline requests found</td></tr>`;
        countEl.textContent = '0 requests';
        return;
    }

    tbody.innerHTML = deadlineRequests.map(r => {
        const statusBadge = getStatusBadge(r.status);
        const createdDate = new Date(r.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

        return `
            <tr>
                <td>${createdDate}</td>
                <td>${escapeHtml(r.requester_name)}</td>
                <td>${escapeHtml(r.case_number)}</td>
                <td>${escapeHtml(r.client_name)}</td>
                <td>${r.current_deadline}</td>
                <td style="color: #059669; font-weight: 500;">${r.requested_deadline}</td>
                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(r.reason)}">${escapeHtml(r.reason)}</td>
                <td>${statusBadge}</td>
                <td style="text-align: center;">
                    ${r.status === 'pending' ?
                        `<button class="btn btn-sm btn-primary" onclick="openDeadlineReviewModal(${r.id})">Review</button>` :
                        `<span style="font-size: 12px; color: #6b7280;">${r.reviewer_name || '-'}</span>`
                    }
                </td>
            </tr>
        `;
    }).join('');

    countEl.textContent = `${deadlineRequests.length} request${deadlineRequests.length !== 1 ? 's' : ''}`;
}

function filterDeadlineRequestsTable() {
    const search = document.getElementById('deadlineSearchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#deadlineRequestsBody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
}

function openDeadlineReviewModal(requestId) {
    const request = deadlineRequests.find(r => r.id == requestId);
    if (!request) return;

    document.getElementById('deadlineReviewId').value = requestId;
    document.getElementById('reviewRequesterName').textContent = request.requester_name;
    document.getElementById('reviewCaseInfo').textContent = `${request.case_number} - ${request.client_name}`;
    document.getElementById('reviewCurrentDeadline').textContent = request.current_deadline;
    document.getElementById('reviewRequestedDeadline').textContent = request.requested_deadline;
    document.getElementById('reviewReason').textContent = request.reason;
    document.getElementById('deadlineAdminNote').value = '';

    openModal('deadlineReviewModal');
}

async function processDeadlineRequest(action) {
    const requestId = document.getElementById('deadlineReviewId').value;
    const adminNote = document.getElementById('deadlineAdminNote').value;

    if (!confirm(`Are you sure you want to ${action} this deadline extension request?`)) {
        return;
    }

    try {
        const result = await apiCall('api/deadline_requests.php', {
            method: 'PUT',
            body: JSON.stringify({
                id: parseInt(requestId),
                action: action,
                admin_note: adminNote
            })
        });

        if (result.success) {
            showNotification(`Request ${action}d successfully`, 'success');
            closeModal('deadlineReviewModal');
            loadDeadlineRequests();
        } else {
            showNotification(result.error || `Failed to ${action} request`, 'error');
        }
    } catch (err) {
        showNotification('Error processing request', 'error');
    }
}
