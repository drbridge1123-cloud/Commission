/**
 * Employee dashboard - Reports tab functions.
 */

function initReportDropdowns() {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const currentYear = new Date().getFullYear();
    const reportMonth = document.getElementById('reportMonth');
    const reportYear = document.getElementById('reportYear');

    // Add years (2021 to current year), current year selected by default
    for (let y = currentYear; y >= 2021; y--) {
        reportYear.innerHTML += `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`;
    }

    // Add months (month name only)
    months.forEach(m => {
        reportMonth.innerHTML += `<option value="${m}">${m}</option>`;
    });
}

function generateReport() {
    const type = document.getElementById('reportType').value;
    const reportMonthSelect = document.getElementById('reportMonth');

    if (!type) {
        document.getElementById('reportContent').innerHTML = '<p style="text-align: center; color: #94a3b8; padding: 48px;">Select a report type to generate</p>';
        return;
    }

    // Show/hide month filter based on report type
    if (type === 'monthly') {
        reportMonthSelect.style.display = 'block';
    } else {
        reportMonthSelect.style.display = 'none';
    }

    const paid = allCases.filter(c => c.status === 'paid');
    let filtered = paid;
    let title = '';
    const year = document.getElementById('reportYear').value;

    if (type === 'monthly') {
        const month = document.getElementById('reportMonth').value;
        filtered = paid.filter(c => c.month.includes(year));

        if (month !== 'all') {
            const fullMonth = `${month}. ${year}`;
            filtered = filtered.filter(c => c.month === fullMonth);
            title = fullMonth;
        } else {
            title = `${year} All Months`;
        }
    } else if (type === 'yearly') {
        filtered = paid.filter(c => c.month.includes(year));
        title = `Year ${year}`;
    }

    if (filtered.length === 0) {
        document.getElementById('reportContent').innerHTML = `
            <div style="text-align: center; padding: 80px 40px;">
                <div style="width: 64px; height: 64px; background: #f1f5f9; border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center;">
                    <svg width="32" height="32" fill="none" stroke="#94a3b8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <p style="color: #64748b; font-size: 14px;">No data available for this period</p>
            </div>
        `;
        return;
    }

    // === Calculate Stats ===
    const totalCommission = filtered.reduce((sum, c) => sum + parseFloat(c.commission), 0);
    const totalCases = filtered.length;
    const avgCommission = totalCommission / totalCases;
    const totalSettled = filtered.reduce((sum, c) => sum + parseFloat(c.settled), 0);

    // Status Analysis
    const paidCount = filtered.filter(c => c.status === 'paid').length;
    const pendingCount = filtered.filter(c => c.status === 'unpaid').length;
    const checkReceivedCount = filtered.filter(c => c.check_received == 1).length;
    const checkPendingCount = filtered.filter(c => c.check_received == 0).length;

    // Case Type Analysis
    const byCaseType = {};
    filtered.forEach(c => {
        const ctype = c.case_type || 'Unknown';
        if (!byCaseType[ctype]) byCaseType[ctype] = { count: 0, commission: 0 };
        byCaseType[ctype].count++;
        byCaseType[ctype].commission += parseFloat(c.commission);
    });

    // Resolution Type Analysis
    const byResolution = {};
    filtered.forEach(c => {
        const res = c.resolution_type || 'Unknown';
        if (!byResolution[res]) byResolution[res] = { count: 0, settled: 0 };
        byResolution[res].count++;
        byResolution[res].settled += parseFloat(c.settled);
    });

    // Fee Rate Analysis
    const byFeeRate = {};
    filtered.forEach(c => {
        const rate = c.fee_rate + '%';
        if (!byFeeRate[rate]) byFeeRate[rate] = { count: 0, commission: 0 };
        byFeeRate[rate].count++;
        byFeeRate[rate].commission += parseFloat(c.commission);
    });

    // Group by Month
    const byMonth = {};
    filtered.forEach(c => {
        if (!byMonth[c.month]) {
            byMonth[c.month] = { count: 0, commission: 0 };
        }
        byMonth[c.month].count++;
        byMonth[c.month].commission += parseFloat(c.commission);
    });

    // Sort months chronologically
    const sortedMonths = Object.entries(byMonth).sort((a, b) => {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const aMonth = a[0].split('. ')[0];
        const bMonth = b[0].split('. ')[0];
        const aYear = a[0].split('. ')[1] || '2025';
        const bYear = b[0].split('. ')[1] || '2025';
        if (aYear !== bYear) return aYear - bYear;
        return months.indexOf(aMonth) - months.indexOf(bMonth);
    });

    const maxCommission = Math.max(...sortedMonths.map(([, d]) => d.commission));

    // Case type colors
    const caseTypeColors = {
        'MVA': '#2563eb',
        'Slip & Fall': '#7c3aed',
        'Dog Bite': '#dc2626',
        'Wrongful Death': '#1e293b',
        'Medical Malpractice': '#0891b2',
        'Product Liability': '#ea580c',
        'Unknown': '#94a3b8'
    };

    // Build HTML with Ink Compact Design
    let html = `
        <!-- Quick Stats -->
        <div class="quick-stats" style="margin-bottom: 20px;">
            <div class="qs-card">
                <span class="qs-label">Total Cases</span>
                <span class="qs-val">${totalCases}</span>
            </div>
            <div class="qs-card">
                <span class="qs-label">Total Commission</span>
                <span class="qs-val green">${formatCurrency(totalCommission)}</span>
            </div>
            <div class="qs-card">
                <span class="qs-label">Avg / Case</span>
                <span class="qs-val">${formatCurrency(avgCommission)}</span>
            </div>
            <div class="qs-card">
                <span class="qs-label">Paid / Pending</span>
                <span class="qs-val">${paidCount} <span style="color:#8b8fa3; font-size:14px;">/ ${pendingCount}</span></span>
            </div>
        </div>

        <!-- Charts Row -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div class="tbl-container">
                <div class="tbl-header">
                    <span class="tbl-title">Monthly Commission</span>
                </div>
                <div style="padding: 16px;">
                    <canvas id="monthlyChart" height="200"></canvas>
                </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div class="tbl-container" style="flex: 1;">
                    <div class="tbl-header"><span class="tbl-title">Payment Status</span></div>
                    <div style="padding: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div style="background: #d1fae5; border-radius: 8px; padding: 12px; text-align: center;">
                            <div style="font-size: 22px; font-weight: 700; color: #065f46;">${paidCount}</div>
                            <div style="font-size: 11px; color: #065f46; margin-top: 2px;">Paid</div>
                        </div>
                        <div style="background: #fef3c7; border-radius: 8px; padding: 12px; text-align: center;">
                            <div style="font-size: 22px; font-weight: 700; color: #b45309;">${pendingCount}</div>
                            <div style="font-size: 11px; color: #b45309; margin-top: 2px;">Pending</div>
                        </div>
                    </div>
                </div>
                <div class="tbl-container" style="flex: 1;">
                    <div class="tbl-header"><span class="tbl-title">Check Status</span></div>
                    <div style="padding: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div style="background: #dbeafe; border-radius: 8px; padding: 12px; text-align: center;">
                            <div style="font-size: 22px; font-weight: 700; color: #1e40af;">${checkReceivedCount}</div>
                            <div style="font-size: 11px; color: #1e40af; margin-top: 2px;">Received</div>
                        </div>
                        <div style="background: #f3f4f6; border-radius: 8px; padding: 12px; text-align: center;">
                            <div style="font-size: 22px; font-weight: 700; color: #6b7280;">${checkPendingCount}</div>
                            <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">Pending</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="tbl-container">
                <div class="tbl-header"><span class="tbl-title">By Case Type</span></div>
                <div style="padding: 16px; display: flex; flex-direction: column; gap: 10px;">
                    ${Object.entries(byCaseType).sort((a, b) => b[1].commission - a[1].commission).map(([ctype, data]) => {
                        const color = caseTypeColors[ctype] || '#8b8fa3';
                        return `
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 10px; height: 10px; background: ${color}; border-radius: 3px; flex-shrink: 0;"></div>
                                <span style="flex: 1; font-size: 13px; color: #1a1a2e;">${ctype}</span>
                                <span style="font-size: 12px; color: #8b8fa3;">${data.count} cases</span>
                                <span style="font-size: 13px; font-weight: 700; color: #0d9488;">${formatCurrency(data.commission)}</span>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
            <div class="tbl-container">
                <div class="tbl-header"><span class="tbl-title">Monthly Breakdown</span></div>
                <table class="excel-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th style="text-align: center;">Cases</th>
                            <th style="text-align: right;">Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${sortedMonths.slice().reverse().map(([month, data]) => `
                            <tr>
                                <td>${month}</td>
                                <td style="text-align: center;">${data.count}</td>
                                <td style="text-align: right; font-weight: 600; color: #0d9488;">${formatCurrency(data.commission)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;

    document.getElementById('reportContent').innerHTML = html;

    // Render Chart with Design Guide colors
    const ctx = document.getElementById('monthlyChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: sortedMonths.map(([m]) => m.split('. ')[0]),
                datasets: [{
                    label: 'Commission',
                    data: sortedMonths.map(([, d]) => d.commission),
                    backgroundColor: '#1a1a2e',
                    borderRadius: { topLeft: 4, topRight: 4 },
                    barThickness: 24
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: { size: 12 },
                        bodyFont: { size: 13, family: 'Outfit' },
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return '$' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11 },
                            color: '#94a3b8'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 11, family: 'JetBrains Mono' },
                            color: '#94a3b8',
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
}
