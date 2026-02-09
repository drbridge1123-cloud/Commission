/**
 * Admin Dashboard - Attorney Pipeline Tab.
 * Active case pipeline management and monthly case flow tracking.
 */

let pipelineYearFilterInit = false;
let pipelineAttorneyFilterInit = false;
let pipelineCases = [];
let pipelineFilter = 'all';

function initPipelineYearFilter() {
    if (pipelineYearFilterInit) return;
    pipelineYearFilterInit = true;
    const sel = document.getElementById('pipelineYearFilter');
    const currentYear = new Date().getFullYear();
    for (let y = currentYear; y >= currentYear - 3; y--) {
        const opt = document.createElement('option');
        opt.value = y;
        opt.textContent = y;
        sel.appendChild(opt);
    }
}

async function initPipelineAttorneyFilter() {
    if (pipelineAttorneyFilterInit) return;
    pipelineAttorneyFilterInit = true;
    const sel = document.getElementById('pipelineAttorneyFilter');
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

async function loadPipelineData() {
    const year = document.getElementById('pipelineYearFilter').value || new Date().getFullYear();
    const attorneyId = document.getElementById('pipelineAttorneyFilter').value;

    if (!attorneyId) return;

    document.getElementById('plPipelineBody').innerHTML =
        '<tr><td colspan="7" style="text-align:center; padding:32px; color:#8b8fa3; font-size:12px;">Loading...</td></tr>';
    document.getElementById('plFlowBody').innerHTML =
        '<tr><td colspan="6" style="text-align:center; padding:32px; color:#8b8fa3; font-size:12px;">Loading...</td></tr>';

    try {
        const result = await apiCall(`api/attorney_cases.php?attorney_id=${attorneyId}&phase=all&year=${year}`);
        pipelineCases = result.cases || [];

        renderPipelineStats();
        renderPipelineTable();
        renderMonthlyFlow(year);

        document.getElementById('pipelineLastUpdated').textContent =
            'Updated ' + new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    } catch (err) {
        console.error('Error loading pipeline:', err);
        document.getElementById('plPipelineBody').innerHTML =
            '<tr><td colspan="7" style="text-align:center; padding:32px; color:#dc2626; font-size:12px;">Failed to load data</td></tr>';
    }
}

// ============================================
// Pipeline Stats
// ============================================

function renderPipelineStats() {
    const demandActive = pipelineCases.filter(c => c.phase === 'demand');
    const litActive = pipelineCases.filter(c => c.phase === 'litigation');
    const overdue = demandActive.filter(c => c.days_until_deadline !== null && c.days_until_deadline < 0);
    const urgent = demandActive.filter(c => c.days_until_deadline !== null && c.days_until_deadline >= 0 && c.days_until_deadline <= 14);

    document.getElementById('plDemandActive').textContent = demandActive.length;
    document.getElementById('plLitActive').textContent = litActive.length;
    document.getElementById('plOverdue').textContent = overdue.length;
    document.getElementById('plUrgent').textContent = urgent.length;
}

// ============================================
// Active Cases Pipeline Table
// ============================================

function filterPipeline(filter) {
    pipelineFilter = filter;

    // Update button styles
    ['plFilterAll', 'plFilterDemand', 'plFilterLit'].forEach(id => {
        const btn = document.getElementById(id);
        btn.style.background = 'transparent';
        btn.style.color = '#3d3f4e';
        btn.style.border = '1px solid #e2e4ea';
    });
    const activeBtn = filter === 'all' ? 'plFilterAll' : filter === 'demand' ? 'plFilterDemand' : 'plFilterLit';
    const btn = document.getElementById(activeBtn);
    btn.style.background = '#1a1a2e';
    btn.style.color = '#fff';
    btn.style.border = '1px solid #1a1a2e';

    renderPipelineTable();
}

function renderPipelineTable() {
    const tbody = document.getElementById('plPipelineBody');
    const now = new Date();

    let activeCases = pipelineCases.filter(c => c.phase === 'demand' || c.phase === 'litigation');

    if (pipelineFilter === 'demand') activeCases = activeCases.filter(c => c.phase === 'demand');
    if (pipelineFilter === 'litigation') activeCases = activeCases.filter(c => c.phase === 'litigation');

    // Sort: overdue first, then by days left ASC, then litigation by duration DESC
    activeCases.sort((a, b) => {
        if (a.phase === 'demand' && b.phase === 'demand') {
            return (a.days_until_deadline || 999) - (b.days_until_deadline || 999);
        }
        if (a.phase === 'demand' && b.phase !== 'demand') return -1;
        if (a.phase !== 'demand' && b.phase === 'demand') return 1;
        // Both litigation: sort by duration desc
        const aDays = daysFromDate(a.litigation_start_date);
        const bDays = daysFromDate(b.litigation_start_date);
        return bDays - aDays;
    });

    if (!activeCases.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:32px; color:#8b8fa3;">No active cases</td></tr>';
        document.getElementById('plPipelineCount').textContent = '0';
        document.getElementById('plAvgDays').textContent = '0';
        return;
    }

    let totalDays = 0;

    tbody.innerHTML = activeCases.map(c => {
        const isDemand = c.phase === 'demand';

        // Started date
        const startDate = isDemand ? c.assigned_date : c.litigation_start_date;
        const startStr = startDate
            ? new Date(startDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
            : '—';

        // Days in phase
        const daysInPhase = daysFromDate(startDate);
        totalDays += daysInPhase;

        // Phase badge
        const phaseBadge = isDemand
            ? '<span class="stat-badge" style="background:#dbeafe; color:#1d4ed8;">Demand</span>'
            : '<span class="stat-badge" style="background:#e0e7ff; color:#4338ca;">Litigation</span>';

        // Deadline
        let deadlineStr = '—';
        let timeLeftHtml = '<span style="color:#c4c7d0;">—</span>';

        if (isDemand && c.demand_deadline) {
            const dl = new Date(c.demand_deadline);
            deadlineStr = dl.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            const days = parseInt(c.days_until_deadline);

            if (days < 0) {
                timeLeftHtml = `<span style="background:#fef2f2; color:#dc2626; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600;">${Math.abs(days)}d overdue</span>`;
            } else if (days === 0) {
                timeLeftHtml = `<span style="background:#fef2f2; color:#dc2626; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600;">Today</span>`;
            } else if (days <= 7) {
                timeLeftHtml = `<span style="background:#fef2f2; color:#dc2626; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600;">${days}d left</span>`;
            } else if (days <= 14) {
                timeLeftHtml = `<span style="background:#fffbeb; color:#d97706; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600;">${days}d left</span>`;
            } else {
                timeLeftHtml = `<span style="color:#059669; font-size:11px; font-weight:600;">${days}d left</span>`;
            }
        } else if (!isDemand) {
            timeLeftHtml = `<span style="color:#6366f1; font-size:11px; font-weight:500;">Day ${daysInPhase}</span>`;
        }

        return `<tr>
            <td style="font-size:12px; font-weight:500;">${escapeHtml(c.case_number || '')}</td>
            <td style="font-size:12px;">${escapeHtml(c.client_name || '')}</td>
            <td class="c">${phaseBadge}</td>
            <td style="font-size:12px; color:#5c5f73;">${startStr}</td>
            <td class="r" style="font-size:12px; font-weight:600; ${daysInPhase > 60 ? 'color:#d97706;' : ''}">${daysInPhase}d</td>
            <td style="font-size:12px;">${deadlineStr}</td>
            <td class="c">${timeLeftHtml}</td>
        </tr>`;
    }).join('');

    const avgDays = Math.round(totalDays / activeCases.length);
    document.getElementById('plPipelineCount').textContent = activeCases.length;
    document.getElementById('plAvgDays').textContent = avgDays + 'd';
}

function daysFromDate(dateStr) {
    if (!dateStr) return 0;
    const start = new Date(dateStr);
    const now = new Date();
    return Math.max(0, Math.floor((now - start) / 86400000));
}

// ============================================
// Monthly Case Flow
// ============================================

function renderMonthlyFlow(year) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const currentMonth = new Date().getFullYear() === parseInt(year) ? new Date().getMonth() : 11;

    const flow = months.slice(0, currentMonth + 1).map((m, i) => {
        const monthNum = i + 1;
        return {
            label: m,
            newCases: countByMonth(pipelineCases, 'assigned_date', year, monthNum),
            toLit: countByMonth(pipelineCases, 'litigation_start_date', year, monthNum),
            demandSettled: countByMonth(pipelineCases.filter(c => c.resolution_type === 'Demand Settle'), 'demand_settled_date', year, monthNum),
            litSettled: countByMonth(pipelineCases.filter(c => c.resolution_type !== 'Demand Settle' && c.phase === 'settled'), 'litigation_settled_date', year, monthNum)
        };
    });

    // Totals
    let totalNew = 0, totalToLit = 0, totalSettled = 0;

    const tbody = document.getElementById('plFlowBody');
    tbody.innerHTML = flow.map(f => {
        const netActive = f.newCases - f.demandSettled - f.litSettled;
        totalNew += f.newCases;
        totalToLit += f.toLit;
        totalSettled += f.demandSettled + f.litSettled;

        const dimStyle = 'color:#c4c7d0;';

        return `<tr>
            <td style="font-size:12px; font-weight:600;">${f.label}</td>
            <td class="r" style="font-size:12px; ${f.newCases === 0 ? dimStyle : 'font-weight:600;'}">${f.newCases}</td>
            <td class="r" style="font-size:12px; ${f.toLit === 0 ? dimStyle : 'color:#6366f1; font-weight:600;'}">${f.toLit}</td>
            <td class="r" style="font-size:12px; ${f.demandSettled === 0 ? dimStyle : 'color:#059669; font-weight:600;'}">${f.demandSettled}</td>
            <td class="r" style="font-size:12px; ${f.litSettled === 0 ? dimStyle : 'color:#0d9488; font-weight:600;'}">${f.litSettled}</td>
            <td class="r" style="font-size:12px; font-weight:600; ${netActive > 0 ? 'color:#3b82f6;' : netActive < 0 ? 'color:#059669;' : dimStyle}">${netActive > 0 ? '+' : ''}${netActive}</td>
        </tr>`;
    }).join('');

    document.getElementById('plFlowNewTotal').textContent = totalNew;
    document.getElementById('plFlowLitTotal').textContent = totalToLit;
    document.getElementById('plFlowSettledTotal').textContent = totalSettled;
}

function countByMonth(cases, dateField, year, month) {
    return cases.filter(c => {
        const val = c[dateField];
        if (!val) return false;
        const d = new Date(val);
        return d.getFullYear() === parseInt(year) && (d.getMonth() + 1) === month;
    }).length;
}
