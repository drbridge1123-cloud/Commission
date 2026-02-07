/**
 * Admin Dashboard - Reports tab functions.
 */

function generateReport() {
    const type = document.getElementById('reportType').value;
    if (!type) return;

    document.getElementById('reportCounsel').classList.toggle('hidden', type !== 'counsel');

    const content = document.getElementById('reportContent');
    content.innerHTML = '<p style="text-align: center; padding: 16px 0;" class="text-secondary">Loading report...</p>';

    loadAllCases().then(() => {
        const paid = allCases.filter(c => c.status === 'paid');
        let html = '';

        if (type === 'counsel') {
            const byCounsel = {};
            paid.forEach(c => {
                if (!byCounsel[c.counsel_name]) byCounsel[c.counsel_name] = { count: 0, commission: 0 };
                byCounsel[c.counsel_name].count++;
                byCounsel[c.counsel_name].commission += parseFloat(c.commission);
            });

            html = `<table class="w-full"><thead><tr style="border-bottom: 1px solid #e5e7eb;"><th style="text-align: left; padding: 12px 0;" class="text-sm font-semibold text-secondary">Counsel</th><th style="text-align: right; padding: 12px 0;" class="text-sm font-semibold text-secondary">Cases</th><th style="text-align: right; padding: 12px 0;" class="text-sm font-semibold text-secondary">Commission</th></tr></thead><tbody>`;
            Object.entries(byCounsel).forEach(([name, data]) => {
                html += `<tr style="border-bottom: 1px solid #f3f4f6;"><td style="padding: 12px 0;" class="text-primary">${name}</td><td style="text-align: right; padding: 12px 0;">${data.count}</td><td style="text-align: right; padding: 12px 0;" class="text-green-600 font-bold text-money">${formatCurrency(data.commission)}</td></tr>`;
            });
            html += '</tbody></table>';
        }

        if (type === 'month') {
            const byMonth = {};
            paid.forEach(c => {
                if (!byMonth[c.month]) byMonth[c.month] = { count: 0, commission: 0 };
                byMonth[c.month].count++;
                byMonth[c.month].commission += parseFloat(c.commission);
            });

            html = `<table class="w-full"><thead><tr style="border-bottom: 1px solid #e5e7eb;"><th style="text-align: left; padding: 12px 0;" class="text-sm font-semibold text-secondary">Month</th><th style="text-align: right; padding: 12px 0;" class="text-sm font-semibold text-secondary">Cases</th><th style="text-align: right; padding: 12px 0;" class="text-sm font-semibold text-secondary">Commission</th></tr></thead><tbody>`;
            Object.entries(byMonth).forEach(([month, data]) => {
                html += `<tr style="border-bottom: 1px solid #f3f4f6;"><td style="padding: 12px 0;" class="text-primary">${month}</td><td style="text-align: right; padding: 12px 0;">${data.count}</td><td style="text-align: right; padding: 12px 0;" class="text-green-600 font-bold text-money">${formatCurrency(data.commission)}</td></tr>`;
            });
            html += '</tbody></table>';
        }

        content.innerHTML = html || '<p style="text-align: center; padding: 16px 0;" class="text-secondary">No data available</p>';
    });
}

function generateComprehensiveReport() {
    const paid = allCases.filter(c => c.status === 'paid');
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().toLocaleDateString('en-US', { month: 'short' });
    const currentMonthStr = `${currentMonth}. ${currentYear}`;

    const monthlyData = paid.filter(c => c.month === currentMonthStr);
    const ytdData = paid.filter(c => {
        const caseYear = c.month.split('. ')[1];
        return caseYear == currentYear;
    });
    const pendingPayment = paid.filter(c => !c.check_received);

    const monthlyCommission = monthlyData.reduce((sum, c) => sum + parseFloat(c.commission), 0);
    const ytdCommission = ytdData.reduce((sum, c) => sum + parseFloat(c.commission), 0);
    const avgCommission = paid.length > 0 ? paid.reduce((sum, c) => sum + parseFloat(c.commission), 0) / paid.length : 0;
    const pendingCommission = pendingPayment.reduce((sum, c) => sum + parseFloat(c.commission), 0);

    document.getElementById('report-monthly-amount').textContent = formatCurrency(monthlyCommission);
    document.getElementById('report-monthly-cases').textContent = `${monthlyData.length} cases`;
    document.getElementById('report-ytd-amount').textContent = formatCurrency(ytdCommission);
    document.getElementById('report-ytd-cases').textContent = `${ytdData.length} cases`;
    document.getElementById('report-avg-amount').textContent = formatCurrency(avgCommission);
    document.getElementById('report-pending-amount').textContent = formatCurrency(pendingCommission);
    document.getElementById('report-pending-cases').textContent = `${pendingPayment.length} cases`;

    generateMonthlyChart(paid);
    generateCounselTable(paid);
    generateCaseTypeTable(paid);
    generateMonthlyBreakdownTable(paid);
}

function generateMonthlyChart(cases) {
    const ctx = document.getElementById('commissionByMonthChart');
    if (!ctx) return;

    if (reportCharts.monthly) {
        reportCharts.monthly.destroy();
    }

    const byMonth = {};
    cases.forEach(c => {
        if (!byMonth[c.month]) byMonth[c.month] = 0;
        byMonth[c.month] += parseFloat(c.commission);
    });

    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const sorted = Object.entries(byMonth).sort((a, b) => {
        const aMonth = a[0].split('. ')[0];
        const bMonth = b[0].split('. ')[0];
        const aYear = parseInt(a[0].split('. ')[1]);
        const bYear = parseInt(b[0].split('. ')[1]);
        if (aYear !== bYear) return aYear - bYear;
        return months.indexOf(aMonth) - months.indexOf(bMonth);
    });

    const labels = sorted.map(([month]) => month);
    const data = sorted.map(([, commission]) => commission);

    reportCharts.monthly = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Commission',
                data: data,
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgba(34, 197, 94, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return formatCurrency(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + (value / 1000).toFixed(0) + 'k';
                        }
                    }
                }
            }
        }
    });
}

function generateCounselChart(cases) {
    const ctx = document.getElementById('counselChart');
    if (!ctx) return;

    if (reportCharts.counsel) {
        reportCharts.counsel.destroy();
    }

    const byCounsel = {};
    cases.forEach(c => {
        const counsel = c.counsel_name || 'Unknown';
        if (!byCounsel[counsel]) byCounsel[counsel] = 0;
        byCounsel[counsel] += parseFloat(c.commission);
    });

    const sorted = Object.entries(byCounsel).sort((a, b) => b[1] - a[1]);
    const labels = sorted.map(([counsel]) => counsel);
    const data = sorted.map(([, commission]) => commission);

    const colors = [
        'rgba(59, 130, 246, 0.8)', 'rgba(34, 197, 94, 0.8)', 'rgba(251, 191, 36, 0.8)',
        'rgba(168, 85, 247, 0.8)', 'rgba(239, 68, 68, 0.8)', 'rgba(20, 184, 166, 0.8)', 'rgba(244, 63, 94, 0.8)'
    ];

    reportCharts.counsel = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{ data: data, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = formatCurrency(context.parsed);
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

function generateCaseTypeChart(cases) {
    const ctx = document.getElementById('caseTypeChart');
    if (!ctx) return;

    if (reportCharts.caseType) {
        reportCharts.caseType.destroy();
    }

    const byType = {};
    cases.forEach(c => {
        const type = c.case_type || 'Unknown';
        if (!byType[type]) byType[type] = 0;
        byType[type] += parseFloat(c.commission);
    });

    const sorted = Object.entries(byType).sort((a, b) => b[1] - a[1]);
    const labels = sorted.map(([type]) => type);
    const data = sorted.map(([, commission]) => commission);

    const colors = [
        'rgba(59, 130, 246, 0.8)', 'rgba(34, 197, 94, 0.8)', 'rgba(251, 191, 36, 0.8)',
        'rgba(168, 85, 247, 0.8)', 'rgba(239, 68, 68, 0.8)', 'rgba(20, 184, 166, 0.8)', 'rgba(244, 63, 94, 0.8)'
    ];

    reportCharts.caseType = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{ data: data, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = formatCurrency(context.parsed);
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

function generateCounselTable(cases) {
    const tbody = document.getElementById('counselTableBody');
    if (!tbody) return;

    const byCounsel = {};
    cases.forEach(c => {
        const counsel = c.counsel_name || 'Unknown';
        if (!byCounsel[counsel]) byCounsel[counsel] = { count: 0, commission: 0 };
        byCounsel[counsel].count++;
        byCounsel[counsel].commission += parseFloat(c.commission);
    });

    const totalCommission = cases.reduce((sum, c) => sum + parseFloat(c.commission), 0);
    const sorted = Object.entries(byCounsel).sort((a, b) => b[1].commission - a[1].commission);

    tbody.innerHTML = sorted.map(([counsel, data]) => {
        const avg = data.commission / data.count;
        const percentage = (data.commission / totalCommission * 100).toFixed(1);
        return `
            <tr>
                <td class="font-medium">${counsel}</td>
                <td class="text-right">${data.count}</td>
                <td class="text-right font-semibold text-green-600">${formatCurrency(data.commission)}</td>
                <td class="text-right">${formatCurrency(avg)}</td>
                <td class="text-right text-blue-600 font-medium">${percentage}%</td>
            </tr>
        `;
    }).join('');
}

function generateCaseTypeTable(cases) {
    const tbody = document.getElementById('caseTypeTableBody');
    if (!tbody) return;

    const byType = {};
    cases.forEach(c => {
        const type = c.case_type || 'Unknown';
        if (!byType[type]) byType[type] = { count: 0, commission: 0 };
        byType[type].count++;
        byType[type].commission += parseFloat(c.commission);
    });

    const totalCommission = cases.reduce((sum, c) => sum + parseFloat(c.commission), 0);
    const sorted = Object.entries(byType).sort((a, b) => b[1].commission - a[1].commission);

    tbody.innerHTML = sorted.map(([type, data]) => {
        const avg = data.commission / data.count;
        const percentage = (data.commission / totalCommission * 100).toFixed(1);
        return `
            <tr>
                <td class="font-medium">${type}</td>
                <td class="text-right">${data.count}</td>
                <td class="text-right font-semibold text-green-600">${formatCurrency(data.commission)}</td>
                <td class="text-right">${formatCurrency(avg)}</td>
                <td class="text-right text-blue-600 font-medium">${percentage}%</td>
            </tr>
        `;
    }).join('');
}

function generateMonthlyBreakdownTable(cases) {
    const tbody = document.getElementById('monthlyBreakdownTableBody');
    if (!tbody) return;

    const byMonth = {};
    cases.forEach(c => {
        if (!byMonth[c.month]) byMonth[c.month] = { count: 0, commission: 0, checkReceived: 0, checkPending: 0 };
        byMonth[c.month].count++;
        byMonth[c.month].commission += parseFloat(c.commission);
        if (c.check_received) {
            byMonth[c.month].checkReceived++;
        } else {
            byMonth[c.month].checkPending++;
        }
    });

    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const sorted = Object.entries(byMonth).sort((a, b) => {
        const aMonth = a[0].split('. ')[0];
        const bMonth = b[0].split('. ')[0];
        const aYear = parseInt(a[0].split('. ')[1]);
        const bYear = parseInt(b[0].split('. ')[1]);
        if (aYear !== bYear) return bYear - aYear;
        return months.indexOf(bMonth) - months.indexOf(aMonth);
    });

    tbody.innerHTML = sorted.map(([month, data]) => {
        const avg = data.commission / data.count;
        return `
            <tr>
                <td class="font-medium">${month}</td>
                <td class="text-right">${data.count}</td>
                <td class="text-right font-semibold text-green-600">${formatCurrency(data.commission)}</td>
                <td class="text-right">${formatCurrency(avg)}</td>
                <td class="text-right text-blue-600">${data.checkReceived}</td>
                <td class="text-right text-amber-600">${data.checkPending}</td>
            </tr>
        `;
    }).join('');
}

function exportReportToExcel() {
    const paid = allCases.filter(c => c.status === 'paid');

    if (paid.length === 0) {
        alert('No paid cases to export');
        return;
    }

    const mainData = paid.map(c => ({
        'Month': c.month,
        'Counsel': c.counsel_name,
        'Case #': c.case_number,
        'Client': c.client_name,
        'Case Type': c.case_type,
        'Resolution': c.resolution_type,
        'Settled': c.settled,
        'Pre-Suit Offer': c.presuit_offer,
        'Difference': c.difference,
        'Legal Fee': c.legal_fee,
        'Disc. Legal Fee': c.discounted_legal_fee,
        'Commission': c.commission,
        'Check Received': c.check_received ? 'Yes' : 'No'
    }));

    const wb = XLSX.utils.book_new();

    const ws1 = XLSX.utils.json_to_sheet(mainData);
    XLSX.utils.book_append_sheet(wb, ws1, 'All Cases');

    const byMonth = {};
    paid.forEach(c => {
        if (!byMonth[c.month]) byMonth[c.month] = { count: 0, commission: 0 };
        byMonth[c.month].count++;
        byMonth[c.month].commission += parseFloat(c.commission);
    });

    const monthlyData = Object.entries(byMonth).map(([month, data]) => ({
        'Month': month,
        'Cases': data.count,
        'Total Commission': data.commission,
        'Average': data.commission / data.count
    }));

    const ws2 = XLSX.utils.json_to_sheet(monthlyData);
    XLSX.utils.book_append_sheet(wb, ws2, 'Monthly Summary');

    const byCounsel = {};
    paid.forEach(c => {
        const counsel = c.counsel_name || 'Unknown';
        if (!byCounsel[counsel]) byCounsel[counsel] = { count: 0, commission: 0 };
        byCounsel[counsel].count++;
        byCounsel[counsel].commission += parseFloat(c.commission);
    });

    const counselData = Object.entries(byCounsel).map(([counsel, data]) => ({
        'Counsel': counsel,
        'Cases': data.count,
        'Total Commission': data.commission,
        'Average': data.commission / data.count
    }));

    const ws3 = XLSX.utils.json_to_sheet(counselData);
    XLSX.utils.book_append_sheet(wb, ws3, 'By Counsel');

    XLSX.writeFile(wb, `admin-comprehensive-report-${new Date().toISOString().split('T')[0]}.xlsx`);
}
