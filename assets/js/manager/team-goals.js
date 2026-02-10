/**
 * Manager Dashboard - Team Goals Tab
 * Shows managed employees' goal progress
 */

let teamGoalsYearInit = false;

function initTeamGoalsYearFilter() {
    if (teamGoalsYearInit) return;
    teamGoalsYearInit = true;

    const sel = document.getElementById('teamGoalsYearFilter');
    const currentYear = new Date().getFullYear();
    for (let y = currentYear; y >= currentYear - 3; y--) {
        const opt = document.createElement('option');
        opt.value = y;
        opt.textContent = y;
        sel.appendChild(opt);
    }
}

async function loadTeamGoals() {
    const year = document.getElementById('teamGoalsYearFilter').value || new Date().getFullYear();
    const container = document.getElementById('teamGoalsContent');
    container.innerHTML = '<p style="text-align: center; padding: 40px; color: #8b8fa3; font-size: 12px; grid-column: 1/-1;">Loading team goals...</p>';

    try {
        const result = await apiCall(`api/goals.php?action=team&year=${year}`);
        const employees = result.employees || [];
        renderTeamGoals(employees, year);
    } catch (err) {
        console.error('Error loading team goals:', err);
        container.innerHTML = '<p style="text-align: center; padding: 40px; color: #ef4444; font-size: 12px; grid-column: 1/-1;">Error loading team goals</p>';
    }
}

function renderTeamGoals(employees, year) {
    // Calculate totals
    let totalCases = 0, totalTargetCases = 0;
    let totalFee = 0, totalTargetFee = 0;

    employees.forEach(emp => {
        totalCases += parseInt(emp.actual_cases) || 0;
        totalTargetCases += parseInt(emp.target_cases) || 0;
        totalFee += parseFloat(emp.actual_legal_fee) || 0;
        totalTargetFee += parseFloat(emp.target_legal_fee) || 0;
    });

    // Update summary cards
    document.getElementById('teamCasesTotal').textContent = totalCases;
    document.getElementById('teamCasesTarget').textContent = `Target: ${totalTargetCases}`;
    const teamFeePercent = totalTargetFee > 0 ? ((totalFee / totalTargetFee) * 100).toFixed(1) : '0.0';
    document.getElementById('teamFeeTotal').textContent = teamFeePercent + '%';
    document.getElementById('teamFeeTarget').textContent = '';

    // Calculate team pace
    const monthsElapsed = new Date().getMonth() + 1;
    const expectedCases = Math.round((monthsElapsed / 12) * totalTargetCases);
    const paceEl = document.getElementById('teamPaceLabel');
    if (totalCases >= expectedCases) {
        paceEl.textContent = 'On Pace';
        paceEl.style.color = '#10b981';
    } else {
        paceEl.textContent = `${totalCases}/${expectedCases}`;
        paceEl.style.color = '#f59e0b';
    }

    // Render individual cards
    const container = document.getElementById('teamGoalsContent');
    if (employees.length === 0) {
        container.innerHTML = '<p style="text-align: center; padding: 40px; color: #8b8fa3; font-size: 12px; grid-column: 1/-1;">No team members assigned. Contact admin to set up your team.</p>';
        return;
    }

    container.innerHTML = employees.map(emp => {
        const casesPercent = Math.min(100, parseFloat(emp.cases_percent) || 0);
        const feePercent = Math.min(100, parseFloat(emp.legal_fee_percent) || 0);
        const actualFee = parseFloat(emp.actual_legal_fee) || 0;
        const targetFee = parseFloat(emp.target_legal_fee) || 0;

        const expectedEmpCases = Math.round((monthsElapsed / 12) * (parseInt(emp.target_cases) || 0));
        const casesOnPace = (parseInt(emp.actual_cases) || 0) >= expectedEmpCases;

        const expectedEmpFee = (monthsElapsed / 12) * targetFee;
        const feeOnPace = actualFee >= expectedEmpFee;

        return `
        <div class="panel" style="padding: 20px;">
            <div style="font-size: 16px; font-weight: 700; color: #1a1a2e; margin-bottom: 16px;">${escapeHtml(emp.display_name)}</div>

            <!-- Cases Goal -->
            <div style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="font-size: 12px; color: #8b8fa3;">Cases Goal</span>
                    <span style="font-size: 12px; font-weight: 600;">${emp.actual_cases} / ${emp.target_cases}</span>
                </div>
                <div style="height: 8px; background: #f0f1f3; border-radius: 4px; overflow: hidden;">
                    <div style="height: 100%; width: ${casesPercent}%; background: ${casesOnPace ? '#10b981' : '#f59e0b'}; border-radius: 4px; transition: width 0.3s;"></div>
                </div>
                <div style="text-align: right; margin-top: 4px;">
                    <span style="font-size: 11px; color: ${casesOnPace ? '#10b981' : '#f59e0b'}; font-weight: 500;">
                        ${casesOnPace ? 'On pace' : 'Behind'}
                    </span>
                </div>
            </div>

            <!-- Fee Goal -->
            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="font-size: 12px; color: #8b8fa3;">Legal Fee Goal</span>
                    <span style="font-size: 12px; font-weight: 600;">${feePercent.toFixed(1)}%</span>
                </div>
                <div style="height: 8px; background: #f0f1f3; border-radius: 4px; overflow: hidden;">
                    <div style="height: 100%; width: ${feePercent}%; background: ${feeOnPace ? '#6366f1' : '#f59e0b'}; border-radius: 4px; transition: width 0.3s;"></div>
                </div>
                <div style="text-align: right; margin-top: 4px;">
                    <span style="font-size: 11px; color: ${feeOnPace ? '#6366f1' : '#f59e0b'}; font-weight: 500;">
                        ${feeOnPace ? 'On pace' : 'Behind'}
                    </span>
                </div>
            </div>
        </div>`;
    }).join('');
}
