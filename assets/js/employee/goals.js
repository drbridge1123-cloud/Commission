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
        document.getElementById('goalIntakeContent').innerHTML = '<p style="text-align:center; padding:40px; color:#dc2626; font-size:12px;">Failed to load goals data</p>';
        document.getElementById('goalFeeContent').innerHTML = '<p style="text-align:center; padding:40px; color:#dc2626; font-size:12px;">Failed to load goals data</p>';
    }
}

function renderMyGoals(data, year) {
    const goal = data.goal;
    const progress = data.progress;
    const monthlyIntake = data.monthly_intake || [];
    const monthlyFee = data.monthly_fee || [];

    const formatFee = (v) => '$' + v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Cases card
    document.getElementById('goalCasesActual').textContent = progress.actual_cases;
    document.getElementById('goalCasesTarget').textContent = goal.target_cases;
    const casesPct = Math.min(100, progress.cases_percent || 0);
    document.getElementById('goalCasesBar').style.width = casesPct + '%';
    document.getElementById('goalCasesBar').style.background = casesPct >= 75 ? '#0d9488' : casesPct >= 50 ? '#d97706' : '#0d9488';
    document.getElementById('goalCasesPercent').textContent = casesPct.toFixed(0) + '% complete';

    // Legal fee card
    const feeActual = progress.actual_legal_fee || 0;
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

    // --- Intake Breakdown Table (by intake_date) ---
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    const intakeData = {};
    monthlyIntake.forEach(m => { intakeData[m.month] = m; });

    let cumCases = 0;
    let intakeRows = '';
    months.forEach((m, idx) => {
        const key = m + '. ' + year;
        const d = intakeData[key];
        const cases = d ? parseInt(d.cases_count) : 0;
        cumCases += cases;
        if (cases > 0) {
            intakeRows += `<tr onclick="toggleMonthDetail(this, ${year}, ${idx + 1}, 'intake')" style="cursor:pointer;" title="Click to expand">
                <td style="font-size:12px; font-weight:500;"><span class="goal-arrow" style="display:inline-block; width:12px; font-size:10px; transition:transform 0.2s;">&#9654;</span> ${key}</td>
                <td class="r" style="font-size:12px;">${cases}</td>
                <td class="r" style="font-size:12px; font-weight:600;">${cumCases}</td>
            </tr>`;
        }
    });

    if (intakeRows) {
        document.getElementById('goalIntakeContent').innerHTML = `
            <table class="tbl" style="table-layout: auto;">
                <thead><tr>
                    <th>Month</th>
                    <th class="r">New Cases</th>
                    <th class="r">Cumulative</th>
                </tr></thead>
                <tbody>${intakeRows}</tbody>
                <tfoot>
                    <tr class="tbl-foot">
                        <td style="font-weight:700; font-size:12px;">Total</td>
                        <td class="r" style="font-weight:700; font-size:12px;">${cumCases}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>`;
    } else {
        document.getElementById('goalIntakeContent').innerHTML = '<p style="text-align:center; padding:40px; color:#8b8fa3; font-size:12px;">No intake data for this year</p>';
    }

    // --- Paid Fee Breakdown Table (by reviewed_at / paid date) ---
    const formatExact = (v) => '$' + v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const feeData = {};
    monthlyFee.forEach(m => { feeData[m.month] = m; });

    let cumFee = 0;
    let feeRows = '';
    months.forEach((m, idx) => {
        const key = m + '. ' + year;
        const d = feeData[key];
        const fee = d ? parseFloat(d.legal_fee_total) : 0;
        const feeCases = d ? parseInt(d.cases_count) : 0;
        cumFee += fee;
        if (fee > 0) {
            feeRows += `<tr onclick="toggleMonthDetail(this, ${year}, ${idx + 1}, 'fee')" style="cursor:pointer;" title="Click to expand">
                <td style="font-size:12px; font-weight:500;"><span class="goal-arrow" style="display:inline-block; width:12px; font-size:10px; transition:transform 0.2s;">&#9654;</span> ${key}</td>
                <td class="r" style="font-size:12px;">${feeCases}</td>
                <td class="r" style="font-size:12px;">${formatExact(fee)}</td>
                <td class="r" style="font-size:12px; font-weight:600;">${formatExact(cumFee)}</td>
            </tr>`;
        }
    });

    if (feeRows) {
        document.getElementById('goalFeeContent').innerHTML = `
            <table class="tbl" style="table-layout: auto;">
                <thead><tr>
                    <th>Month</th>
                    <th class="r">Cases</th>
                    <th class="r">Disc. Legal Fee</th>
                    <th class="r">Cumulative</th>
                </tr></thead>
                <tbody>${feeRows}</tbody>
                <tfoot>
                    <tr class="tbl-foot">
                        <td style="font-weight:700; font-size:12px;">Total</td>
                        <td></td>
                        <td class="r" style="font-weight:700; font-size:12px;">${formatExact(cumFee)}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>`;
    } else {
        document.getElementById('goalFeeContent').innerHTML = '<p style="text-align:center; padding:40px; color:#8b8fa3; font-size:12px;">No paid fee data for this year</p>';
    }
}

async function toggleMonthDetail(row, year, month, type) {
    const arrow = row.querySelector('.goal-arrow');
    const nextRow = row.nextElementSibling;

    // If detail row already exists, toggle it
    if (nextRow && nextRow.classList.contains('goal-detail-row')) {
        nextRow.remove();
        arrow.style.transform = 'rotate(0deg)';
        return;
    }

    // Create detail row
    const colSpan = type === 'intake' ? 3 : 4;
    const detailRow = document.createElement('tr');
    detailRow.className = 'goal-detail-row';
    detailRow.innerHTML = `<td colspan="${colSpan}" style="padding:0; background:#f8f9fa;">
        <div style="padding:8px 12px 8px 24px; font-size:11px; color:#8b8fa3;">Loading...</div>
    </td>`;
    row.after(detailRow);
    arrow.style.transform = 'rotate(90deg)';

    try {
        const result = await apiCall(`api/goals.php?action=month_cases&year=${year}&month=${month}&type=${type}`);
        const cases = result.cases || [];

        if (cases.length === 0) {
            detailRow.innerHTML = `<td colspan="${colSpan}" style="padding:0; background:#f8f9fa;">
                <div style="padding:8px 12px 8px 24px; font-size:11px; color:#8b8fa3;">No cases found</div>
            </td>`;
            return;
        }

        const formatExact = (v) => '$' + parseFloat(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        let subRows = '';

        if (type === 'intake') {
            cases.forEach(c => {
                const statusColor = c.status === 'paid' ? '#0d9488' : c.status === 'unpaid' ? '#d97706' : '#6b7280';
                subRows += `<tr>
                    <td style="font-size:11px; padding:4px 8px;">${c.case_number || '-'}</td>
                    <td style="font-size:11px; padding:4px 8px;">${c.client_name}</td>
                    <td style="font-size:11px; padding:4px 8px;">${c.resolution_type || 'TBD'}</td>
                    <td style="font-size:11px; padding:4px 8px;"><span style="color:${statusColor}; font-weight:500;">${c.status}</span></td>
                </tr>`;
            });
            detailRow.innerHTML = `<td colspan="${colSpan}" style="padding:0; background:#f8f9fa;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead><tr style="background:#e5e7eb;">
                        <th style="font-size:10px; padding:4px 8px; text-align:left; font-weight:600; color:#6b7280;">CASE #</th>
                        <th style="font-size:10px; padding:4px 8px; text-align:left; font-weight:600; color:#6b7280;">CLIENT</th>
                        <th style="font-size:10px; padding:4px 8px; text-align:left; font-weight:600; color:#6b7280;">RESOLUTION</th>
                        <th style="font-size:10px; padding:4px 8px; text-align:left; font-weight:600; color:#6b7280;">STATUS</th>
                    </tr></thead>
                    <tbody>${subRows}</tbody>
                </table>
            </td>`;
        } else {
            cases.forEach(c => {
                subRows += `<tr>
                    <td style="font-size:11px; padding:4px 8px;">${c.case_number || '-'}</td>
                    <td style="font-size:11px; padding:4px 8px;">${c.client_name}</td>
                    <td style="font-size:11px; padding:4px 8px;">${c.resolution_type || 'TBD'}</td>
                    <td style="font-size:11px; padding:4px 8px; text-align:right;">${formatExact(c.discounted_legal_fee)}</td>
                </tr>`;
            });
            detailRow.innerHTML = `<td colspan="${colSpan}" style="padding:0; background:#f8f9fa;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead><tr style="background:#e5e7eb;">
                        <th style="font-size:10px; padding:4px 8px; text-align:left; font-weight:600; color:#6b7280;">CASE #</th>
                        <th style="font-size:10px; padding:4px 8px; text-align:left; font-weight:600; color:#6b7280;">CLIENT</th>
                        <th style="font-size:10px; padding:4px 8px; text-align:left; font-weight:600; color:#6b7280;">RESOLUTION</th>
                        <th style="font-size:10px; padding:4px 8px; text-align:right; font-weight:600; color:#6b7280;">DISC. LEGAL FEE</th>
                    </tr></thead>
                    <tbody>${subRows}</tbody>
                </table>
            </td>`;
        }
    } catch (err) {
        console.error('Error loading month cases:', err);
        detailRow.innerHTML = `<td colspan="${colSpan}" style="padding:0; background:#f8f9fa;">
            <div style="padding:8px 12px 8px 24px; font-size:11px; color:#dc2626;">Failed to load cases</div>
        </td>`;
    }
}
