/**
 * Admin Dashboard - Performance Analytics functions.
 */

async function loadPerformanceData() {
    const employeeId = document.getElementById('perfEmployeeFilter').value;
    const year = document.getElementById('perfYearFilter').value;

    // Show/hide Chong analytics panel
    const chongSection = document.getElementById('chongAnalyticsSection');
    chongSection.style.display = (employeeId == 2) ? 'block' : 'none';

    if (employeeId == 2) await loadChongAnalytics(year);
    await loadPerformanceSummary(employeeId, year);
    await loadMonthlyTrend(employeeId, year);
    await loadByEmployee(year);
}

async function loadPerformanceSummary(employeeId, year) {
    try {
        let url = `api/performance.php?action=summary&year=${year}`;
        if (employeeId > 0) url += `&employee_id=${employeeId}`;

        const result = await apiCall(url);
        if (result.summary) {
            const s = result.summary;
            const mom = result.month_over_month;

            document.getElementById('perfTotalCases').textContent = s.total_cases;
            document.getElementById('perfTotalCommission').textContent = formatCurrency(s.valid_commission);
            document.getElementById('perfAvgCommission').textContent = formatCurrency(s.avg_commission);

            const changeEl = document.getElementById('perfCommissionChange');
            if (mom && mom.change_percent !== 0) {
                const isUp = mom.change_percent > 0;
                changeEl.innerHTML = `<span class="${isUp ? 'up' : 'down'}">${isUp ? '+' : ''}${mom.change_percent.toFixed(1)}% vs last month</span>`;
            } else {
                changeEl.textContent = '';
            }
        }
    } catch (err) {
        console.error('Error loading summary:', err);
    }
}

async function loadChongAnalytics(year) {
    try {
        const result = await apiCall(`api/performance.php?action=chong&year=${year}`);
        if (result.chong_analytics) {
            const c = result.chong_analytics;

            // Phase breakdown
            document.getElementById('perfDemandActive').textContent = c.phase_breakdown.demand_active;
            document.getElementById('perfLitActive').textContent = c.phase_breakdown.litigation_active;
            document.getElementById('perfSettled').textContent = c.phase_breakdown.settled;

            // Settlement
            document.getElementById('perfDemandSettled').textContent = c.settlement_breakdown.demand_settled;
            const litEl = document.getElementById('perfLitSettled');
            litEl.textContent = c.settlement_breakdown.litigation_settled;
            litEl.className = 'pd-val' + (c.settlement_breakdown.litigation_settled == 0 ? ' dim' : '');
            document.getElementById('perfResolutionRate').textContent = c.settlement_breakdown.demand_resolution_rate + '%';

            // Efficiency
            const setMetric = (id, val) => {
                const el = document.getElementById(id);
                if (!val || val === '-' || val == 0) { el.textContent = '\u2014'; el.className = 'pd-val dim'; }
                else { el.textContent = val; el.className = 'pd-val'; }
            };
            setMetric('perfAvgDemandDays', c.efficiency.avg_demand_days);
            setMetric('perfAvgLitDays', c.efficiency.avg_litigation_days);
            setMetric('perfAvgTotalDays', c.efficiency.avg_total_days);

            // Time management
            const compEl = document.getElementById('perfDeadlineCompliance');
            const compRate = c.time_management.deadline_compliance_rate;
            compEl.textContent = compRate + '%';
            compEl.className = 'pd-val ' + (compRate < 80 ? 'amber' : 'green');

            const urgentEl = document.getElementById('perfUrgentCases');
            const urgentCount = c.current_status.urgent_cases;
            urgentEl.textContent = urgentCount;
            urgentEl.className = 'pd-val ' + (urgentCount > 0 ? 'red' : 'dim');

            // Commission
            document.getElementById('perfCommTotal').textContent = formatCurrency(c.commission_breakdown.total);
            document.getElementById('perfCommDemand').textContent = formatCurrency(c.commission_breakdown.from_demand);
            const commLitEl = document.getElementById('perfCommLit');
            commLitEl.textContent = formatCurrency(c.commission_breakdown.from_litigation);
            commLitEl.className = 'pd-val ' + (c.commission_breakdown.from_litigation == 0 ? 'amber' : 'teal');
            document.getElementById('perfActiveCases').textContent = c.current_status.active_cases;
        }
    } catch (err) {
        console.error('Error loading Chong analytics:', err);
    }
}

async function loadMonthlyTrend(employeeId, year) {
    try {
        let url = `api/performance.php?action=by_month&year=${year}`;
        if (employeeId > 0) url += `&employee_id=${employeeId}`;

        const result = await apiCall(url);
        if (result.by_month) {
            renderPerfChart(result.by_month);
        }
    } catch (err) {
        console.error('Error loading monthly trend:', err);
    }
}

function renderPerfChart(data) {
    const ctx = document.getElementById('perfCommissionChart');
    if (!ctx) return;
    if (perfChartInstance) perfChartInstance.destroy();

    const labels = data.map(d => d.month);
    const commissions = data.map(d => parseFloat(d.commission));
    const cases = data.map(d => parseInt(d.cases_count));

    perfChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Commission ($)',
                    data: commissions,
                    backgroundColor: '#1a1a2e',
                    borderRadius: 4,
                    barPercentage: 0.6,
                    order: 2,
                    yAxisID: 'y'
                },
                {
                    label: 'Cases',
                    data: cases,
                    type: 'line',
                    borderColor: '#d97706',
                    backgroundColor: 'transparent',
                    pointBackgroundColor: '#d97706',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2,
                    tension: 0.3,
                    yAxisID: 'y1',
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'start',
                    labels: { font: { family: 'Outfit', size: 11, weight: '500' }, color: '#8b8fa3', boxWidth: 12, boxHeight: 3, padding: 16 }
                },
                tooltip: {
                    backgroundColor: '#1a1a2e',
                    titleFont: { family: 'Outfit', size: 12, weight: '600' },
                    bodyFont: { family: 'Outfit', size: 11 },
                    cornerRadius: 6,
                    padding: 10,
                    callbacks: { label: ctx => ctx.dataset.label === 'Commission ($)' ? formatCurrency(ctx.raw) : ctx.raw + ' cases' }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 11 }, color: '#8b8fa3' } },
                y: {
                    position: 'left',
                    grid: { color: '#f0f1f3' },
                    ticks: { font: { family: 'Outfit', size: 11 }, color: '#8b8fa3', callback: v => '$' + v.toLocaleString() }
                },
                y1: {
                    position: 'right',
                    grid: { display: false },
                    ticks: { font: { family: 'Outfit', size: 11 }, color: '#8b8fa3', stepSize: 2 }
                }
            }
        }
    });
}

async function loadByEmployee(year) {
    try {
        const result = await apiCall(`api/performance.php?action=by_employee&year=${year}`);
        if (result.by_employee) {
            renderPerfEmployeeTable(result.by_employee);
        }
    } catch (err) {
        console.error('Error loading by employee:', err);
    }
}

function renderPerfEmployeeTable(employees) {
    const tbody = document.getElementById('perfEmployeeBody');
    if (!employees || employees.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px; color: #8b8fa3; font-size: 12px;">No data</td></tr>';
        return;
    }

    employees.sort((a, b) => parseFloat(b.total_commission) - parseFloat(a.total_commission));
    const totalCommission = employees.reduce((sum, e) => sum + parseFloat(e.total_commission || 0), 0);

    tbody.innerHTML = employees.map((e, idx) => {
        const pct = totalCommission > 0 ? ((parseFloat(e.total_commission) / totalCommission) * 100) : 0;
        const isTop = idx === 0 && parseFloat(e.total_commission) > 0;
        const isZero = parseFloat(e.total_commission) === 0;

        return `
            <tr class="${isTop ? 'top-row' : ''}">
                <td style="padding: 10px 14px; font-weight: 600; color: #1a1a2e; font-size: 13px;">${escapeHtml(e.display_name)}</td>
                <td class="r" style="padding: 10px 14px; font-size: 13px;">${e.total_cases}</td>
                <td class="r" style="padding: 10px 14px; font-size: 13px;">${e.paid_cases}</td>
                <td class="r" style="padding: 10px 14px; font-weight: 700; ${isZero ? 'color: #c4c7d0;' : 'color: #0d9488;'} font-size: 13px;">${formatCurrency(e.total_commission)}</td>
                <td class="r" style="padding: 10px 14px; font-size: 13px; ${isZero ? 'color: #c4c7d0;' : ''}">${formatCurrency(e.avg_commission)}</td>
                <td class="r" style="padding: 10px 14px;">
                    <div class="spark-bar">
                        <div class="spark"><div class="spark-fill ${pct === 0 ? 'empty' : ''}" style="width: ${pct}%;"></div></div>
                        <span class="spark-pct ${pct === 0 ? 'zero' : ''}">${pct.toFixed(1)}%</span>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}
