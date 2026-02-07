/**
 * ChongDashboard - Dashboard tab functions.
 */

async function loadDashboard() {
    const result = await apiCall('api/chong_cases.php?stats=1');
    if (result.stats) {
        const s = result.stats;
        document.getElementById('statTotalActive').textContent = s.total_active;
        document.getElementById('statDemand').textContent = s.demand_count;
        document.getElementById('statLitigation').textContent = s.litigation_count;
        document.getElementById('statOverdue').textContent = s.overdue_count || 0;
        document.getElementById('statDue2Weeks').textContent = s.urgent_count || 0;
        document.getElementById('statMonthCommission').textContent = formatCurrency(s.month_commission);

        // Update badge
        if (s.urgent_count > 0) {
            document.getElementById('demandBadge').textContent = s.urgent_count;
            document.getElementById('demandBadge').style.display = 'inline';
        }
    }

    // Load urgent cases
    const urgentResult = await apiCall('api/chong_cases.php?urgent=1');
    if (urgentResult.cases) {
        renderUrgentCases(urgentResult.cases);
    }
}

function renderUrgentCases(cases) {
    const container = document.getElementById('urgentCasesList');
    if (cases.length === 0) {
        container.innerHTML = '<p style="color: #059669;">No urgent cases. All cases are on track!</p>';
        return;
    }

    // Build table with all details
    let html = `
        <div class="table-container" style="margin-top: 12px;">
            <table class="excel-table urgent-table">
                <thead>
                    <tr>
                        <th>Case #</th>
                        <th>Client Name</th>
                        <th>Case Type</th>
                        <th>Incident Date</th>
                        <th>Deadline</th>
                        <th>Days Left</th>
                        <th>Status</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;

    cases.forEach(c => {
        const isOverdue = c.days_until_deadline < 0;
        const daysText = isOverdue ? `${Math.abs(c.days_until_deadline)} days overdue` : `${c.days_until_deadline} days left`;
        const daysClass = isOverdue ? 'deadline-overdue' : 'deadline-critical';
        const rowClass = isOverdue ? 'urgent-row-overdue' : 'urgent-row-warning';
        const statusClass = c.status ? `status-${c.status}` : 'status-in_progress';
        const statusText = c.status ? c.status.replace('_', ' ') : 'in progress';

        html += `
            <tr class="${rowClass}">
                <td><strong>${escapeHtml(c.case_number)}</strong></td>
                <td>${escapeHtml(c.client_name)}</td>
                <td>${escapeHtml(c.case_type || '-')}</td>
                <td>${formatDate(c.incident_date) || '-'}</td>
                <td>${formatDate(c.demand_deadline) || '-'}</td>
                <td><span class="badge ${daysClass}">${daysText}</span></td>
                <td><span class="badge ${statusClass}">${statusText}</span></td>
                <td style="text-align: center;">
                    <div class="action-group center">
                        <button class="act-btn settle" data-action="settle-demand" data-id="${c.id}" data-case="${escapeHtml(c.case_number)}" data-client="${escapeHtml(c.client_name)}">Settle</button>
                        <button class="act-btn to-lit" data-action="to-litigation" data-id="${c.id}" data-case="${escapeHtml(c.case_number)}" data-client="${escapeHtml(c.client_name)}">To Lit</button>
                    </div>
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    container.innerHTML = html;
}
