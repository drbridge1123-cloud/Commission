/**
 * Admin Dashboard - Performance Analytics functions.
 * Sub-tabs: Attorney | Employee
 */

let perfCurrentSubTab = 'attorney';
let perfAttorneyFilterInit = false;

// ============================================
// Sub-tab Switching
// ============================================

function switchPerfSubTab(tab) {
    perfCurrentSubTab = tab;

    // Update button styles
    const attyBtn = document.getElementById('perfSubTabAttorney');
    const empBtn = document.getElementById('perfSubTabEmployee');
    const attyFilter = document.getElementById('perfAttorneyFilter');

    if (tab === 'attorney') {
        attyBtn.style.background = '#1a1a2e';
        attyBtn.style.color = '#fff';
        attyBtn.style.border = '1px solid #1a1a2e';
        empBtn.style.background = 'transparent';
        empBtn.style.color = '#3d3f4e';
        empBtn.style.border = '1px solid #e2e4ea';
        document.getElementById('perfAttorneyContent').style.display = '';
        document.getElementById('perfEmployeeContent').style.display = 'none';
        attyFilter.style.display = 'inline-block';
        loadAttorneyPerformance();
    } else {
        empBtn.style.background = '#1a1a2e';
        empBtn.style.color = '#fff';
        empBtn.style.border = '1px solid #1a1a2e';
        attyBtn.style.background = 'transparent';
        attyBtn.style.color = '#3d3f4e';
        attyBtn.style.border = '1px solid #e2e4ea';
        document.getElementById('perfAttorneyContent').style.display = 'none';
        document.getElementById('perfEmployeeContent').style.display = '';
        attyFilter.style.display = 'none';
        loadEmployeePerformance();
    }
}

function loadCurrentPerfSubTab() {
    if (perfCurrentSubTab === 'attorney') {
        loadAttorneyPerformance();
    } else {
        loadEmployeePerformance();
    }
}

// ============================================
// Attorney Filter Init
// ============================================

async function initPerfAttorneyFilter() {
    if (perfAttorneyFilterInit) return;
    perfAttorneyFilterInit = true;
    const sel = document.getElementById('perfAttorneyFilter');
    try {
        const result = await apiCall('api/users.php');
        const attorneys = (result.users || []).filter(u => u.is_attorney == 1 && u.is_active == 1);
        attorneys.forEach(a => {
            const opt = document.createElement('option');
            opt.value = a.id;
            opt.textContent = a.display_name;
            sel.appendChild(opt);
        });
        if (attorneys.length > 0) {
            sel.value = attorneys[0].id;
        }
    } catch (err) {
        console.error('Error loading attorneys:', err);
    }
}

// ============================================
// Attorney Performance (formerly loadPerformanceData)
// ============================================

async function loadAttorneyPerformance() {
    const attorneyId = document.getElementById('perfAttorneyFilter').value;
    const year = document.getElementById('perfYearFilter').value;
    if (!attorneyId) return;

    await loadAttorneyAnalytics(attorneyId, year);
    await loadPerformanceSummary(attorneyId, year);
    await loadMonthlyTrend(attorneyId, year);
}

// Backward compat: called from init.js on first load
async function loadPerformanceData() {
    await initPerfAttorneyFilter();
    switchPerfSubTab('attorney');
}

// ============================================
// Employee Performance
// ============================================

async function loadEmployeePerformance() {
    const year = document.getElementById('perfYearFilter').value;
    await loadByEmployee(year);
}

// ============================================
// Summary (hero cards)
// ============================================

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

// ============================================
// Attorney Analytics (formerly loadChongAnalytics)
// ============================================

async function loadAttorneyAnalytics(attorneyId, year) {
    try {
        const result = await apiCall(`api/performance.php?action=attorney&attorney_id=${attorneyId}&year=${year}`);
        if (result.attorney_analytics) {
            const c = result.attorney_analytics;

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
        console.error('Error loading attorney analytics:', err);
    }
}

// ============================================
// Monthly Trend Chart
// ============================================

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

// ============================================
// Employee Table (with Goals)
// ============================================

async function loadByEmployee(year) {
    try {
        const [perfResult, goalsResult] = await Promise.all([
            apiCall(`api/performance.php?action=by_employee&year=${year}`),
            apiCall(`api/goals.php?action=summary&year=${year}`)
        ]);
        if (goalsResult.csrf_token) csrfToken = goalsResult.csrf_token;
        renderPerfEmployeeTable(perfResult.by_employee || [], goalsResult.employees || [], year);
    } catch (err) {
        console.error('Error loading by employee:', err);
    }
}

function renderPerfEmployeeTable(employees, goalsEmployees, year) {
    const tbody = document.getElementById('perfEmployeeBody');
    if ((!employees || employees.length === 0) && (!goalsEmployees || goalsEmployees.length === 0)) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding: 20px; color: #8b8fa3; font-size: 12px;">No data</td></tr>';
        return;
    }

    // Build goals map by employee ID
    const goalsMap = {};
    goalsEmployees.forEach(g => { goalsMap[g.id] = g; });

    // Build perf map by employee ID
    const perfMap = {};
    employees.forEach(e => { perfMap[e.id] = e; });

    // Merge: use goals employees as base (they have target info), supplement with perf data
    const allIds = new Set([...goalsEmployees.map(g => g.id), ...employees.map(e => e.id)]);
    const merged = Array.from(allIds).map(id => {
        const g = goalsMap[id] || {};
        const p = perfMap[id] || {};
        return {
            id: id,
            display_name: g.display_name || p.display_name || '',
            actual_cases: parseInt(g.actual_cases) || parseInt(p.total_cases) || 0,
            target_cases: parseInt(g.target_cases) || 50,
            cases_percent: parseFloat(g.cases_percent) || 0,
            actual_legal_fee: parseFloat(g.actual_legal_fee) || 0,
            target_legal_fee: parseFloat(g.target_legal_fee) || 500000,
            legal_fee_percent: parseFloat(g.legal_fee_percent) || 0,
            total_commission: parseFloat(p.total_commission) || 0,
            avg_commission: parseFloat(p.avg_commission) || 0,
            goal_notes: g.goal_notes || ''
        };
    });

    merged.sort((a, b) => b.total_commission - a.total_commission);
    const expectedPct = getOnPacePercent(year);
    const fmtFee = (v) => v >= 1000 ? '$' + (v / 1000).toFixed(0) + 'K' : '$' + Math.round(v);

    tbody.innerHTML = merged.map(e => {
        const casesPct = Math.min(100, e.cases_percent);
        const feePct = Math.min(100, e.legal_fee_percent);
        const avgPct = (casesPct + feePct) / 2;
        const paceColor = getPaceColor(avgPct, expectedPct);
        const paceLabel = getPaceLabel(avgPct, expectedPct);
        const isZero = e.total_commission === 0;

        const notesEscaped = (e.goal_notes || '').replace(/'/g, "\\'").replace(/\n/g, ' ');

        return `<tr>
            <td style="padding: 10px 14px; font-weight: 600; color: #1a1a2e; font-size: 12px;">${escapeHtml(e.display_name)}</td>
            <td class="r" style="padding: 10px 14px; font-size: 12px;">${e.actual_cases}/${e.target_cases}</td>
            <td style="padding: 10px 14px;">${sparkBar(casesPct)}</td>
            <td class="r" style="padding: 10px 14px; font-size: 12px;">${fmtFee(e.actual_legal_fee)}/${fmtFee(e.target_legal_fee)}</td>
            <td style="padding: 10px 14px;">${sparkBar(feePct)}</td>
            <td class="r" style="padding: 10px 14px; font-weight: 700; ${isZero ? 'color: #c4c7d0;' : 'color: #0d9488;'} font-size: 12px;">${formatCurrency(e.total_commission)}</td>
            <td class="r" style="padding: 10px 14px; font-size: 12px; ${isZero ? 'color: #c4c7d0;' : ''}">${formatCurrency(e.avg_commission)}</td>
            <td class="c" style="padding: 10px 14px;"><span style="font-size: 11px; font-weight: 600; color: ${paceColor};">${paceLabel}</span></td>
            <td class="c" style="padding: 10px 14px;">
                <button onclick="openPerfGoalModal(${e.id}, '${e.display_name.replace(/'/g, "\\'")}', {target_cases:${e.target_cases}, target_legal_fee:${e.target_legal_fee}, notes:'${notesEscaped}'})" class="act-link" style="padding: 3px 8px; font-size: 10px;">Edit</button>
            </td>
        </tr>`;
    }).join('');
}

// ============================================
// Goal Editing
// ============================================

function openPerfGoalModal(userId, displayName, goal) {
    document.getElementById('perfGoalUserId').value = userId;
    document.getElementById('perfGoalTitle').textContent = 'Edit Goal - ' + displayName;
    document.getElementById('perfGoalYear').value = document.getElementById('perfYearFilter').value;
    document.getElementById('perfGoalCases').value = goal.target_cases || 50;
    document.getElementById('perfGoalFee').value = goal.target_legal_fee || 500000;
    document.getElementById('perfGoalNotes').value = goal.notes || '';
    openModal('perfEditGoalModal');
}

async function savePerfGoal() {
    const data = {
        user_id: parseInt(document.getElementById('perfGoalUserId').value),
        year: parseInt(document.getElementById('perfGoalYear').value),
        target_cases: parseInt(document.getElementById('perfGoalCases').value),
        target_legal_fee: parseFloat(document.getElementById('perfGoalFee').value),
        notes: document.getElementById('perfGoalNotes').value.trim()
    };

    try {
        const result = await apiCall('api/goals.php', {
            method: 'POST',
            body: JSON.stringify(data)
        });
        if (result.csrf_token) csrfToken = result.csrf_token;
        closeModal('perfEditGoalModal');
        loadByEmployee(document.getElementById('perfYearFilter').value);
    } catch (err) {
        alert('Failed to save goal: ' + (err.message || err));
    }
}
