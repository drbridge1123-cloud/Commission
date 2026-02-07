/**
 * ChongDashboard - Reports tab functions.
 */

async function loadReports() {
    const selectedYear = document.getElementById('reportYearFilter')?.value || new Date().getFullYear();
    const result = await apiCall('api/chong_cases.php?phase=settled&status=all');

    if (result.cases) {
        const cases = result.cases;
        const ytdCases = cases.filter(c => c.month && c.month.includes(selectedYear));

        const demandCases = ytdCases.filter(c => c.commission_type === 'demand_5pct');
        const litCases = ytdCases.filter(c => c.commission_type && c.commission_type.startsWith('litigation'));
        const demandComm = demandCases.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);
        const litComm = litCases.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);
        const totalCommission = ytdCases.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);

        document.getElementById('reportTotalCases').textContent = ytdCases.length;
        document.getElementById('reportDemandSettled').textContent = demandCases.length;
        document.getElementById('reportLitSettled').textContent = litCases.length;
        document.getElementById('reportTotalCommission').textContent = formatCurrency(totalCommission);

        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const monthlyData = months.map(m => {
            const monthCases = ytdCases.filter(c => c.month && c.month.startsWith(m));
            return monthCases.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);
        });

        renderCommissionChart(months, monthlyData);
        renderBreakdownChart(demandComm, litComm);
        renderRecentSettlements(ytdCases.slice(0, 10));
    }
}

function renderCommissionChart(labels, data) {
    const ctx = document.getElementById('commissionChart');
    if (!ctx) return;
    if (commissionChart) commissionChart.destroy();

    commissionChart = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Commission',
                data: data,
                backgroundColor: '#1a1a2e',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => '$' + v.toLocaleString() }
                }
            }
        }
    });
}

function renderBreakdownChart(demand, litigation) {
    const ctx = document.getElementById('breakdownChart');
    if (!ctx) return;
    if (breakdownChart) breakdownChart.destroy();

    breakdownChart = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Demand', 'Litigation'],
            datasets: [{
                data: [demand, litigation],
                backgroundColor: ['#3b82f6', '#f59e0b'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.label + ': $' + ctx.raw.toLocaleString()
                    }
                }
            }
        }
    });
}

function renderRecentSettlements(cases) {
    const tbody = document.getElementById('recentSettlementsBody');
    if (!tbody) return;

    if (!cases || cases.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 30px; color: #8b8fa3;">No settlements found</td></tr>';
        return;
    }

    tbody.innerHTML = cases.map(c => {
        const isLit = c.commission_type && c.commission_type.startsWith('litigation');
        const typeBadge = isLit
            ? '<span class="ink-badge" style="background:#fef3c7;color:#d97706;">Litigation</span>'
            : '<span class="ink-badge" style="background:#dbeafe;color:#1d4ed8;">Demand</span>';

        return `
            <tr>
                <td>${escapeHtml(c.month || '-')}</td>
                <td style="font-weight: 500;">${escapeHtml(c.client_name)}</td>
                <td>${typeBadge}</td>
                <td>${escapeHtml(c.resolution_type || '-')}</td>
                <td style="text-align: right;">${formatCurrency(c.settled)}</td>
                <td style="text-align: right; font-weight: 600; color: #059669;">${formatCurrency(c.commission)}</td>
            </tr>
        `;
    }).join('');
}

function exportReportToCSV() {
    const year = document.getElementById('reportYearFilter')?.value || new Date().getFullYear();
    const data = commissionsData.filter(c => c.month && c.month.includes(year));

    if (data.length === 0) {
        alert('No data to export');
        return;
    }

    let csv = 'Month,Client,Type,Resolution,Settled,Commission\n';
    data.forEach(c => {
        const type = c.commission_type?.startsWith('litigation') ? 'Litigation' : 'Demand';
        csv += `"${c.month || ''}","${c.client_name || ''}","${type}","${c.resolution_type || ''}",${c.settled || 0},${c.commission || 0}\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `commission_report_${year}.csv`;
    a.click();
}

// Excel Export (for Reports tab)
function exportToExcel() {
    exportCommissionsToExcel();
}
