/**
 * Employee dashboard - Goals tab functions.
 */

let myGoalsYearFilterInit = false;

function initMyGoalsYearFilter() {
    if (myGoalsYearFilterInit) return;
    myGoalsYearFilterInit = true;
    const sel = document.getElementById('myGoalsYearFilter');
    const currentYear = new Date().getFullYear();
    for (let y = currentYear; y >= currentYear - 3; y--) {
        const opt = document.createElement('option');
        opt.value = y;
        opt.textContent = y;
        sel.appendChild(opt);
    }
}

async function loadMyGoals() {
    const year = document.getElementById('myGoalsYearFilter').value || new Date().getFullYear();
    try {
        const result = await apiCall(`api/goals.php?year=${year}`);
        if (result.csrf_token) csrfToken = result.csrf_token;
        renderMyGoals(result, year);
    } catch (err) {
        console.error('Error loading goals:', err);
        document.getElementById('goalMonthlyContent').innerHTML = '<p style="text-align:center; padding:40px; color:#dc2626; font-size:12px;">Failed to load goals data</p>';
    }
}

function renderMyGoals(data, year) {
    const goal = data.goal;
    const progress = data.progress;
    const monthly = data.monthly || [];

    // Cases card
    document.getElementById('goalCasesActual').textContent = progress.actual_cases;
    document.getElementById('goalCasesTarget').textContent = goal.target_cases;
    const casesPct = Math.min(100, progress.cases_percent || 0);
    document.getElementById('goalCasesBar').style.width = casesPct + '%';
    document.getElementById('goalCasesBar').style.background = casesPct >= 75 ? '#0d9488' : casesPct >= 50 ? '#d97706' : '#0d9488';
    document.getElementById('goalCasesPercent').textContent = casesPct.toFixed(0) + '% complete';

    // Legal fee card
    const feeActual = progress.actual_legal_fee || 0;
    const formatFee = (v) => v >= 1000000 ? '$' + (v/1000000).toFixed(1) + 'M' : v >= 1000 ? '$' + (v/1000).toFixed(0) + 'K' : '$' + v.toFixed(0);
    document.getElementById('goalFeeActual').textContent = formatFee(feeActual);
    document.getElementById('goalFeeTarget').textContent = formatFee(goal.target_legal_fee);
    const feePct = Math.min(100, progress.legal_fee_percent || 0);
    document.getElementById('goalFeeBar').style.width = feePct + '%';
    document.getElementById('goalFeePercent').textContent = feePct.toFixed(0) + '% complete';

    // Pace calculation
    const now = new Date();
    const currentYear = now.getFullYear();
    let expectedPct = 100;
    if (parseInt(year) === currentYear) {
        const monthsPassed = now.getMonth() + 1;
        expectedPct = (monthsPassed / 12) * 100;
    } else if (parseInt(year) > currentYear) {
        expectedPct = 0;
    }

    const expectedCases = Math.round(goal.target_cases * expectedPct / 100);
    const expectedFee = goal.target_legal_fee * expectedPct / 100;

    const casesPaceEl = document.getElementById('goalCasesPace');
    if (progress.actual_cases >= expectedCases) {
        casesPaceEl.innerHTML = '<span style="color:#0d9488;">On pace</span>';
    } else {
        casesPaceEl.innerHTML = `<span style="color:#d97706;">Expected: ${expectedCases} by now</span>`;
    }

    const feePaceEl = document.getElementById('goalFeePace');
    if (feeActual >= expectedFee) {
        feePaceEl.innerHTML = '<span style="color:#0d9488;">On pace</span>';
    } else {
        feePaceEl.innerHTML = `<span style="color:#d97706;">Expected: ${formatFee(expectedFee)} by now</span>`;
    }

    // Monthly breakdown table
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const monthData = {};
    monthly.forEach(m => { monthData[m.month] = m; });

    let cumCases = 0;
    let cumFee = 0;
    let tableRows = '';

    months.forEach(m => {
        const key = m + '. ' + year;
        const d = monthData[key];
        const cases = d ? parseInt(d.cases_count) : 0;
        const fee = d ? parseFloat(d.legal_fee_total) : 0;
        cumCases += cases;
        cumFee += fee;

        if (cases > 0 || fee > 0) {
            tableRows += `<tr>
                <td style="font-size:12px; font-weight:500;">${key}</td>
                <td class="r" style="font-size:12px;">${cases}</td>
                <td class="r" style="font-size:12px;">${formatFee(fee)}</td>
                <td class="r" style="font-size:12px; font-weight:600;">${cumCases}</td>
                <td class="r" style="font-size:12px; font-weight:600;">${formatFee(cumFee)}</td>
            </tr>`;
        }
    });

    if (!tableRows) {
        document.getElementById('goalMonthlyContent').innerHTML = '<p style="text-align:center; padding:40px; color:#8b8fa3; font-size:12px;">No data for this year</p>';
        return;
    }

    document.getElementById('goalMonthlyContent').innerHTML = `
        <table class="tbl" style="table-layout: auto;">
            <thead><tr>
                <th>Month</th>
                <th class="r">Cases</th>
                <th class="r">Legal Fee</th>
                <th class="r">Cumulative Cases</th>
                <th class="r">Cumulative Fee</th>
            </tr></thead>
            <tbody>${tableRows}</tbody>
            <tfoot>
                <tr class="tbl-foot">
                    <td style="font-weight:700; font-size:12px;">Total</td>
                    <td class="r" style="font-weight:700; font-size:12px;">${cumCases}</td>
                    <td class="r" style="font-weight:700; font-size:12px;">${formatFee(cumFee)}</td>
                    <td></td><td></td>
                </tr>
            </tfoot>
        </table>`;
}
