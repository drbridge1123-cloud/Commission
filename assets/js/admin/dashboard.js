/**
 * Admin Dashboard - Dashboard tab functions (stats, charts).
 */

async function loadStats() {
    try {
        stats = await apiCall('api/approve.php');
        renderStats();
    } catch (err) {
        console.error('Error:', err);
    }
}

function renderStats() {
    // Quick Stats Row
    document.getElementById('statTotalCases').textContent = stats.total_cases || 0;
    document.getElementById('statPending').textContent = stats.pending_count || 0;
    document.getElementById('statTotalCommission').textContent = formatCurrency(stats.total_commission || 0);
    document.getElementById('statAvgCommission').textContent = formatCurrency(stats.avg_commission || 0);
    document.getElementById('statCheckRate').textContent = (stats.check_received_rate || 0) + '%';
    document.getElementById('statUnreceived').textContent = formatCurrency(stats.unreceived?.total || 0);

    // This Month vs Last Month
    if (stats.this_month) {
        document.getElementById('thisMonthName').textContent = stats.this_month.name;
        document.getElementById('thisMonthCases').textContent = stats.this_month.cases || 0;
        document.getElementById('thisMonthComm').textContent = formatCurrency(stats.this_month.commission || 0);
        document.getElementById('thisMonthApproved').textContent = stats.this_month.approved || 0;

        if (stats.last_month && stats.last_month.cases > 0) {
            const casesChange = ((stats.this_month.cases - stats.last_month.cases) / stats.last_month.cases * 100).toFixed(0);
            const casesEl = document.getElementById('thisMonthCasesChange');
            casesEl.innerHTML = casesChange >= 0 ?
                `<span style="color: #059669;">↑ ${casesChange}%</span>` :
                `<span style="color: #dc2626;">↓ ${Math.abs(casesChange)}%</span>`;
        }
        if (stats.last_month && stats.last_month.commission > 0) {
            const commChange = ((stats.this_month.commission - stats.last_month.commission) / stats.last_month.commission * 100).toFixed(0);
            const commEl = document.getElementById('thisMonthCommChange');
            commEl.innerHTML = commChange >= 0 ?
                `<span style="color: #059669;">↑ ${commChange}%</span>` :
                `<span style="color: #dc2626;">↓ ${Math.abs(commChange)}%</span>`;
        }
    }

    if (stats.last_month) {
        document.getElementById('lastMonthName').textContent = stats.last_month.name;
        document.getElementById('lastMonthCases').textContent = stats.last_month.cases || 0;
        document.getElementById('lastMonthComm').textContent = formatCurrency(stats.last_month.commission || 0);
        document.getElementById('lastMonthApproved').textContent = stats.last_month.approved || 0;
    }

    // Monthly Trend Chart
    if (stats.by_month && stats.by_month.length > 0) {
        renderTrendChart(stats.by_month.slice(0, 6).reverse());
    }

    // Cases by Status Pie Chart
    if (stats.by_status) {
        renderStatusChart(stats.by_status);
    }

    // Counsel stats
    const counselDiv = document.getElementById('counselStats');
    if (stats.by_counsel) {
        counselDiv.innerHTML = stats.by_counsel.map(c => `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f1f3;">
                <div>
                    <span style="font-size: 13px; font-weight: 600; color: #1a1a2e; font-family: 'Outfit', sans-serif;">${c.display_name}</span>
                    ${c.pending_count > 0 ? `<span class="stat-badge pending" style="margin-left: 8px;">${c.pending_count} pending</span>` : ''}
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 14px; font-weight: 700; color: #0d9488; font-family: 'Outfit', sans-serif;">${formatCurrency(c.total_commission)}</div>
                    <div style="font-size: 11px; color: #8b8fa3;">${c.case_count} cases</div>
                </div>
            </div>
        `).join('');
    }

    // Top 5 Cases
    const topCasesDiv = document.getElementById('topCasesStats');
    if (stats.top_cases && stats.top_cases.length > 0) {
        topCasesDiv.innerHTML = stats.top_cases.map((c, i) => `
            <div style="display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f1f3; cursor: pointer;" onclick="viewCaseDetail(${c.id})">
                <div style="width: 24px; height: 24px; border-radius: 50%; background: ${i === 0 ? '#fbbf24' : i === 1 ? '#9ca3af' : i === 2 ? '#d97706' : '#e5e7eb'}; color: ${i < 3 ? '#fff' : '#6b7280'}; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; margin-right: 10px;">${i + 1}</div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 12px; font-weight: 600; color: #1a1a2e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${c.client_name}</div>
                    <div style="font-size: 10px; color: #8b8fa3;">${c.counsel_name} · ${c.case_number}</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 13px; font-weight: 700; color: #0d9488;">${formatCurrency(c.commission)}</div>
                    <span class="stat-badge ${c.status}" style="font-size: 8px;">${c.status}</span>
                </div>
            </div>
        `).join('');
    } else {
        topCasesDiv.innerHTML = '<div style="padding: 20px; text-align: center; color: #8b8fa3; font-size: 12px;">No cases found</div>';
    }

    // Upcoming Deadlines
    const deadlinesDiv = document.getElementById('upcomingDeadlines');
    let deadlinesHtml = '';

    if (stats.past_due && stats.past_due.length > 0) {
        deadlinesHtml += stats.past_due.map(c => `
            <div style="display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f1f3; background: #fef2f2; margin: 0 -20px; padding-left: 20px; padding-right: 20px;">
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 12px; font-weight: 600; color: #1a1a2e;">${c.client_name}</div>
                    <div style="font-size: 10px; color: #8b8fa3;">${c.counsel_name} · ${c.case_number}</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; font-weight: 700; color: #dc2626;">${c.days_overdue} days overdue</div>
                    <div style="font-size: 10px; color: #8b8fa3;">${c.demand_deadline}</div>
                </div>
            </div>
        `).join('');
    }

    if (stats.upcoming_deadlines && stats.upcoming_deadlines.length > 0) {
        deadlinesHtml += stats.upcoming_deadlines.map(c => `
            <div style="display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f1f3;">
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 12px; font-weight: 600; color: #1a1a2e;">${c.client_name}</div>
                    <div style="font-size: 10px; color: #8b8fa3;">${c.counsel_name} · ${c.case_number}</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; font-weight: 600; color: ${c.days_until <= 3 ? '#dc2626' : c.days_until <= 7 ? '#d97706' : '#059669'};">
                        ${c.days_until === 0 ? 'Today' : c.days_until === 1 ? 'Tomorrow' : c.days_until + ' days'}
                    </div>
                    <div style="font-size: 10px; color: #8b8fa3;">${c.demand_deadline}</div>
                </div>
            </div>
        `).join('');
    }

    if (!deadlinesHtml) {
        deadlinesHtml = '<div style="padding: 20px; text-align: center; color: #8b8fa3; font-size: 12px;">No upcoming deadlines</div>';
    }
    deadlinesDiv.innerHTML = deadlinesHtml;

    // Month stats list
    const monthDiv = document.getElementById('monthStats');
    if (stats.by_month && monthDiv) {
        monthDiv.innerHTML = stats.by_month.slice(0, 6).map(m => `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f1f3;">
                <div>
                    <span style="font-size: 13px; font-weight: 600; color: #1a1a2e; font-family: 'Outfit', sans-serif;">${m.month_name}</span>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 14px; font-weight: 700; color: #0d9488; font-family: 'Outfit', sans-serif;">${formatCurrency(m.total_commission)}</div>
                    <div style="font-size: 11px; color: #8b8fa3;">${m.case_count} cases</div>
                </div>
            </div>
        `).join('');
    }
}

// Dashboard Trend Chart
function renderTrendChart(monthData) {
    const ctx = document.getElementById('dashboardTrendChart');
    if (!ctx) return;

    if (trendChartInstance) {
        trendChartInstance.destroy();
    }

    trendChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthData.map(m => m.month_name),
            datasets: [{
                label: 'Commission',
                data: monthData.map(m => parseFloat(m.total_commission)),
                backgroundColor: '#0d9488',
                borderRadius: 4,
                barThickness: 32
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => formatCurrency(ctx.raw)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => '$' + (v / 1000).toFixed(0) + 'k',
                        font: { size: 10 }
                    },
                    grid: { color: '#f0f1f3' }
                },
                x: {
                    ticks: { font: { size: 10 } },
                    grid: { display: false }
                }
            }
        }
    });
}

// Dashboard Status Pie Chart
function renderStatusChart(statusData) {
    const ctx = document.getElementById('dashboardStatusChart');
    if (!ctx) return;

    if (statusChartInstance) {
        statusChartInstance.destroy();
    }

    const labels = [];
    const data = [];
    const colors = {
        'in_progress': '#3b82f6',
        'unpaid': '#f59e0b',
        'paid': '#10b981',
        'rejected': '#ef4444'
    };
    const bgColors = [];

    for (const [status, count] of Object.entries(statusData)) {
        labels.push(status === 'in_progress' ? 'In Progress' : status.charAt(0).toUpperCase() + status.slice(1));
        data.push(count);
        bgColors.push(colors[status] || '#94a3b8');
    }

    statusChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: bgColors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { font: { size: 10 }, boxWidth: 12 }
                }
            },
            cutout: '60%'
        }
    });
}
