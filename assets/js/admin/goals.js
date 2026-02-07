/**
 * Admin Dashboard - Employee Goals functions.
 */

function initGoalsYearFilter() {
    if (goalsYearFilterInit) return;
    goalsYearFilterInit = true;
    const sel = document.getElementById('goalsYearFilter');
    const currentYear = new Date().getFullYear();
    for (let y = currentYear; y >= currentYear - 3; y--) {
        const opt = document.createElement('option');
        opt.value = y;
        opt.textContent = y;
        sel.appendChild(opt);
    }
}

async function loadGoalsData() {
    const year = document.getElementById('goalsYearFilter').value || new Date().getFullYear();
    try {
        const result = await apiCall(`api/goals.php?action=summary&year=${year}`);
        if (result.csrf_token) csrfToken = result.csrf_token;
        const employees = result.employees || [];
        renderGoalsTable(employees, year);
        updateGoalsHeroCards(employees);
    } catch (err) {
        console.error('Error loading goals:', err);
        document.getElementById('goalsTableBody').innerHTML = '<tr><td colspan="7" style="text-align:center; padding:40px; color:#dc2626;">Failed to load goals data</td></tr>';
    }
}

function getOnPacePercent(year) {
    const now = new Date();
    const currentYear = now.getFullYear();
    if (parseInt(year) < currentYear) return 100;
    if (parseInt(year) > currentYear) return 0;
    const monthsPassed = now.getMonth() + 1;
    return (monthsPassed / 12) * 100;
}

function getPaceColor(actualPct, expectedPct) {
    if (expectedPct === 0) return '#8b8fa3';
    const ratio = actualPct / expectedPct;
    if (ratio >= 0.85) return '#0d9488';
    if (ratio >= 0.6) return '#d97706';
    return '#dc2626';
}

function getPaceLabel(actualPct, expectedPct) {
    if (expectedPct === 0) return '-';
    const ratio = actualPct / expectedPct;
    if (ratio >= 0.85) return 'On Pace';
    if (ratio >= 0.6) return 'Behind';
    return 'Far Behind';
}

function renderGoalsTable(employees, year) {
    const tbody = document.getElementById('goalsTableBody');
    const expectedPct = getOnPacePercent(year);

    if (!employees.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:40px; color:#8b8fa3;">No employees found</td></tr>';
        return;
    }

    tbody.innerHTML = employees.map(emp => {
        const casesPct = Math.min(100, parseFloat(emp.cases_percent) || 0);
        const feePct = Math.min(100, parseFloat(emp.legal_fee_percent) || 0);
        const avgPct = (casesPct + feePct) / 2;
        const paceColor = getPaceColor(avgPct, expectedPct);
        const paceLabel = getPaceLabel(avgPct, expectedPct);

        const feeActual = parseFloat(emp.actual_legal_fee) || 0;
        const feeTarget = parseFloat(emp.target_legal_fee) || 500000;

        const formatFee = (v) => v >= 1000 ? '$' + (v/1000).toFixed(0) + 'K' : '$' + v.toFixed(0);

        return `<tr>
            <td style="font-weight:600; font-size:12px;">${emp.display_name}</td>
            <td class="r" style="font-size:12px;">${emp.actual_cases}/${emp.target_cases}</td>
            <td>
                <div class="spark-bar">
                    <div class="spark" style="width:70px;">
                        <div class="spark-fill" style="width:${casesPct}%; background:${casesPct >= 75 ? '#0d9488' : casesPct >= 50 ? '#d97706' : '#e2e4ea'};"></div>
                    </div>
                    <span class="spark-pct ${casesPct === 0 ? 'zero' : ''}">${casesPct.toFixed(0)}%</span>
                </div>
            </td>
            <td class="r" style="font-size:12px;">${formatFee(feeActual)}/${formatFee(feeTarget)}</td>
            <td>
                <div class="spark-bar">
                    <div class="spark" style="width:70px;">
                        <div class="spark-fill" style="width:${feePct}%; background:${feePct >= 75 ? '#0d9488' : feePct >= 50 ? '#d97706' : '#e2e4ea'};"></div>
                    </div>
                    <span class="spark-pct ${feePct === 0 ? 'zero' : ''}">${feePct.toFixed(0)}%</span>
                </div>
            </td>
            <td class="c"><span style="font-size:11px; font-weight:600; color:${paceColor};">${paceLabel}</span></td>
            <td class="c">
                <button onclick="openEditGoalModal(${emp.id}, '${emp.display_name.replace(/'/g, "\\'")}', {target_cases:${emp.target_cases}, target_legal_fee:${emp.target_legal_fee}, notes:'${(emp.goal_notes||'').replace(/'/g, "\\'").replace(/\n/g, ' ')}'})" class="act-link" style="padding:3px 8px; font-size:10px;">Edit</button>
            </td>
        </tr>`;
    }).join('');
}

function updateGoalsHeroCards(employees) {
    document.getElementById('goalsHeroCount').textContent = employees.length;

    if (employees.length > 0) {
        const avgCases = employees.reduce((s, e) => s + (parseFloat(e.cases_percent) || 0), 0) / employees.length;
        const avgFee = employees.reduce((s, e) => s + (parseFloat(e.legal_fee_percent) || 0), 0) / employees.length;
        document.getElementById('goalsHeroCases').textContent = avgCases.toFixed(1) + '%';
        document.getElementById('goalsHeroFee').textContent = avgFee.toFixed(1) + '%';
    } else {
        document.getElementById('goalsHeroCases').textContent = '0%';
        document.getElementById('goalsHeroFee').textContent = '0%';
    }
}

function openEditGoalModal(userId, displayName, goal) {
    document.getElementById('goalEditUserId').value = userId;
    document.getElementById('editGoalTitle').textContent = 'Edit Goal - ' + displayName;
    document.getElementById('goalEditYear').value = document.getElementById('goalsYearFilter').value || new Date().getFullYear();
    document.getElementById('goalEditCases').value = goal.target_cases || 50;
    document.getElementById('goalEditFee').value = goal.target_legal_fee || 500000;
    document.getElementById('goalEditNotes').value = goal.notes || '';
    document.getElementById('editGoalModal').style.display = 'flex';
}

function closeGoalModal() {
    document.getElementById('editGoalModal').style.display = 'none';
}

async function saveGoal() {
    const data = {
        user_id: parseInt(document.getElementById('goalEditUserId').value),
        year: parseInt(document.getElementById('goalEditYear').value),
        target_cases: parseInt(document.getElementById('goalEditCases').value),
        target_legal_fee: parseFloat(document.getElementById('goalEditFee').value),
        notes: document.getElementById('goalEditNotes').value.trim()
    };

    try {
        const result = await apiCall('api/goals.php', {
            method: 'POST',
            body: JSON.stringify(data)
        });
        if (result.csrf_token) csrfToken = result.csrf_token;
        closeGoalModal();
        loadGoalsData();
    } catch (err) {
        alert('Failed to save goal: ' + (err.message || err));
    }
}
