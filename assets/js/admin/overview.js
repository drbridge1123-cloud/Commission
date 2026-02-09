/**
 * Admin Dashboard - Command Center V2.
 * Two-column layout with case flow and counsel tables.
 */

let overviewYearFilterInit = false;

function initOverviewYearFilter() {
    if (overviewYearFilterInit) return;
    overviewYearFilterInit = true;
    const sel = document.getElementById('overviewYearFilter');
    const currentYear = new Date().getFullYear();
    for (let y = currentYear; y >= currentYear - 3; y--) {
        const opt = document.createElement('option');
        opt.value = y;
        opt.textContent = y;
        sel.appendChild(opt);
    }
}

async function loadOverviewData() {
    const year = document.getElementById('overviewYearFilter').value || new Date().getFullYear();

    document.getElementById('ovMonthlyBody').innerHTML =
        '<tr><td colspan="6" class="cc-empty">Loading...</td></tr>';
    document.getElementById('ovCounselBody').innerHTML =
        '<tr><td colspan="7" class="cc-empty">Loading...</td></tr>';

    try {
        const statsResult = await apiCall(`api/approve.php?year=${year}`);

        renderCommandCenterStats(statsResult);
        renderCommissionCard(statsResult);
        renderThisVsLastMonth(statsResult);
        renderMonthlyCaseFlow(statsResult.by_month || []);
        renderCasesByCounsel(statsResult.by_counsel || []);

        document.getElementById('overviewLastUpdated').textContent =
            'Updated ' + new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });

    } catch (err) {
        console.error('Error loading command center:', err);
        document.getElementById('ovMonthlyBody').innerHTML =
            '<tr><td colspan="6" class="cc-empty" style="color:#cc3333;">Failed to load data</td></tr>';
    }
}

// ============================================
// Stats Strip
// ============================================

function renderCommandCenterStats(stats) {
    document.getElementById('ovStatTotalCases').textContent = stats.total_cases || 0;
    document.getElementById('ovStatPending').textContent = stats.pending_count || 0;
    document.getElementById('ovStatTotalComm').textContent = formatCurrency(stats.total_commission || 0);
    document.getElementById('ovStatAvgComm').textContent = formatCurrency(stats.avg_commission || 0);
    document.getElementById('ovStatCheckRate').textContent = (stats.check_received_rate || 0) + '%';
    document.getElementById('ovStatUnreceived').textContent = formatCurrency(stats.unreceived?.total || 0);
}

// ============================================
// Commission Card (left column)
// ============================================

function renderCommissionCard(stats) {
    const el = document.getElementById('ovCardComm');
    if (el) el.textContent = formatCurrency(stats.total_commission || 0);

    const countEl = document.getElementById('ovCardCaseCount');
    if (countEl) countEl.textContent = stats.total_cases || 0;

    const avgEl = document.getElementById('ovCardAvg');
    if (avgEl) avgEl.textContent = formatCurrency(stats.avg_commission || 0);

    const checkEl = document.getElementById('ovCardCheckPct');
    if (checkEl) checkEl.textContent = (stats.check_received_rate || 0) + '%';
}

// ============================================
// This Month vs Last Month
// ============================================

function renderThisVsLastMonth(stats) {
    if (stats.this_month) {
        document.getElementById('ovTmThisName').textContent = stats.this_month.name;
        document.getElementById('ovTmThisCases').textContent = stats.this_month.cases || 0;
        document.getElementById('ovTmThisComm').textContent = formatCurrency(stats.this_month.commission || 0);
        document.getElementById('ovTmThisApproved').textContent = stats.this_month.approved || 0;

        if (stats.last_month && stats.last_month.cases > 0) {
            const casesChange = ((stats.this_month.cases - stats.last_month.cases) / stats.last_month.cases * 100).toFixed(0);
            const casesEl = document.getElementById('ovTmThisCasesChange');
            if (casesChange >= 0) {
                casesEl.innerHTML = `<span class="cc-change">\u2191 ${casesChange}%</span>`;
            } else {
                casesEl.innerHTML = `<span class="cc-change down">\u2193 ${Math.abs(casesChange)}%</span>`;
            }
        }
        if (stats.last_month && stats.last_month.commission > 0) {
            const commChange = ((stats.this_month.commission - stats.last_month.commission) / stats.last_month.commission * 100).toFixed(0);
            const commEl = document.getElementById('ovTmThisCommChange');
            if (commChange >= 0) {
                commEl.innerHTML = `<span class="cc-change">\u2191 ${commChange}%</span>`;
            } else {
                commEl.innerHTML = `<span class="cc-change down">\u2193 ${Math.abs(commChange)}%</span>`;
            }
        }
    }

    if (stats.last_month) {
        document.getElementById('ovTmLastName').textContent = stats.last_month.name;
        document.getElementById('ovTmLastCases').textContent = stats.last_month.cases || 0;
        document.getElementById('ovTmLastComm').textContent = formatCurrency(stats.last_month.commission || 0);
        document.getElementById('ovTmLastApproved').textContent = stats.last_month.approved || 0;
    }
}

// ============================================
// Monthly Case Flow
// ============================================

function renderMonthlyCaseFlow(byMonth) {
    const tbody = document.getElementById('ovMonthlyBody');

    if (!byMonth || !byMonth.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="cc-empty">No data</td></tr>';
        document.getElementById('ovMonthlyCount').textContent = '0';
        document.getElementById('ovMonthlyTotalSettled').textContent = '$0';
        document.getElementById('ovMonthlyTotalDiscFee').textContent = '$0';
        document.getElementById('ovMonthlyTotalComm').textContent = '$0';
        return;
    }

    let totalSettled = 0;
    let totalDiscFee = 0;
    let totalComm = 0;

    tbody.innerHTML = byMonth.map(m => {
        const cases = parseInt(m.case_count) || 0;
        const settledCount = parseInt(m.settled_count) || 0;
        const settledAmt = parseFloat(m.settled_amount) || 0;
        const discFee = parseFloat(m.total_disc_fee) || 0;
        const commission = parseFloat(m.total_commission) || 0;
        const pct = discFee > 0 ? (commission / discFee * 100) : 0;
        totalSettled += settledAmt;
        totalDiscFee += discFee;
        totalComm += commission;

        const monthEnc = encodeURIComponent(m.month_name);

        return `<tr onclick="showMonthDrilldown('${monthEnc}', '${escapeHtml(m.month_name)}')">
            <td style="font-weight:600;">${escapeHtml(m.month_name)}</td>
            <td class="r" style="font-weight:600;">${cases}</td>
            <td class="r${settledCount === 0 ? ' dim' : ''}">${settledCount}</td>
            <td class="r${settledAmt === 0 ? ' dim' : ''}" style="${settledAmt > 0 ? 'font-weight:600;' : ''}">${formatCurrency(settledAmt)}</td>
            <td class="r${discFee === 0 ? ' dim' : ''}" style="${discFee > 0 ? 'font-weight:600;' : ''}">${formatCurrency(discFee)}</td>
            <td class="r${commission === 0 ? ' dim' : ''}" style="${commission > 0 ? 'color:#2e8b57; font-weight:700;' : ''}">${formatCurrency(commission)}</td>
            <td class="r${pct === 0 ? ' dim' : ''}" style="${pct > 0 ? 'font-weight:600;' : ''}">${pct > 0 ? pct.toFixed(1) + '%' : '—'}</td>
            <td class="r"><span class="cc-arrow">›</span></td>
        </tr>`;
    }).join('');

    document.getElementById('ovMonthlyCount').textContent = byMonth.length;
    document.getElementById('ovMonthlyTotalSettled').textContent = formatCurrency(totalSettled);
    document.getElementById('ovMonthlyTotalDiscFee').textContent = formatCurrency(totalDiscFee);
    document.getElementById('ovMonthlyTotalComm').textContent = formatCurrency(totalComm);
}

// ============================================
// Cases by Counsel
// ============================================

function renderCasesByCounsel(byCounsel) {
    const tbody = document.getElementById('ovCounselBody');

    if (!byCounsel || !byCounsel.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="cc-empty">No data</td></tr>';
        document.getElementById('ovCounselCount').textContent = '0';
        document.getElementById('ovCounselTotalComm').textContent = '$0';
        return;
    }

    const active = byCounsel.filter(c => parseInt(c.case_count) > 0);
    let totalComm = 0;

    tbody.innerHTML = active.map(c => {
        const cases = parseInt(c.case_count) || 0;
        const settledCount = parseInt(c.settled_count) || 0;
        const settledAmt = parseFloat(c.settled_amount) || 0;
        const commission = parseFloat(c.total_commission) || 0;
        const pending = parseInt(c.pending_count) || 0;
        totalComm += commission;

        const usernameEnc = encodeURIComponent(c.username);

        return `<tr onclick="showCounselDrilldown('${usernameEnc}', '${escapeHtml(c.display_name)}')">
            <td style="font-weight:600;">${escapeHtml(c.display_name)}</td>
            <td class="r" style="font-weight:600;">${cases}</td>
            <td class="r${settledCount === 0 ? ' dim' : ''}">${settledCount}</td>
            <td class="r${settledAmt === 0 ? ' dim' : ''}" style="${settledAmt > 0 ? 'font-weight:600;' : ''}">${formatCurrency(settledAmt)}</td>
            <td class="r${commission === 0 ? ' dim' : ''}" style="${commission > 0 ? 'color:#2e8b57; font-weight:700;' : ''}">${formatCurrency(commission)}</td>
            <td class="r" style="${pending > 0 ? 'color:#c8860a; font-weight:600;' : 'color:#aaa;'}">${pending}</td>
            <td class="r"><span class="cc-arrow">›</span></td>
        </tr>`;
    }).join('');

    // Inactive counsel (0 cases) at bottom, dimmed
    const inactive = byCounsel.filter(c => parseInt(c.case_count) === 0);
    if (inactive.length) {
        tbody.innerHTML += inactive.map(c => `<tr style="cursor:default;">
            <td class="dim">${escapeHtml(c.display_name)}</td>
            <td class="r dim">0</td>
            <td class="r dim">0</td>
            <td class="r dim">$0</td>
            <td class="r dim">$0</td>
            <td class="r dim">0</td>
            <td></td>
        </tr>`).join('');
    }

    document.getElementById('ovCounselCount').textContent = active.length;
    document.getElementById('ovCounselTotalComm').textContent = formatCurrency(totalComm);
}

// ============================================
// Drill-down: Month Detail
// ============================================

async function showMonthDrilldown(monthEncoded, monthDisplay) {
    const month = decodeURIComponent(monthEncoded);
    document.getElementById('ovDrillTitle').textContent = monthDisplay + ' — Cases';
    document.getElementById('ovDrillBody').innerHTML =
        '<tr><td colspan="8" style="text-align:center; padding:32px; color:#8b8fa3; font-size:12px;">Loading...</td></tr>';
    document.getElementById('ovDrillSummary').textContent = '';
    openModal('ovDrillModal');

    try {
        const result = await apiCall(`api/cases.php?month=${encodeURIComponent(month)}`);
        renderDrilldownTable(result.cases || result || []);
    } catch (err) {
        console.error('Error loading month cases:', err);
        document.getElementById('ovDrillBody').innerHTML =
            '<tr><td colspan="8" style="text-align:center; padding:32px; color:#dc2626; font-size:12px;">Failed to load</td></tr>';
    }
}

// ============================================
// Drill-down: Counsel Detail
// ============================================

async function showCounselDrilldown(usernameEncoded, displayName) {
    const username = decodeURIComponent(usernameEncoded);
    const year = document.getElementById('overviewYearFilter').value || new Date().getFullYear();
    document.getElementById('ovDrillTitle').textContent = displayName + ' — Cases';
    document.getElementById('ovDrillBody').innerHTML =
        '<tr><td colspan="8" style="text-align:center; padding:32px; color:#8b8fa3; font-size:12px;">Loading...</td></tr>';
    document.getElementById('ovDrillSummary').textContent = '';
    openModal('ovDrillModal');

    try {
        const result = await apiCall(`api/cases.php?counsel=${encodeURIComponent(username)}`);
        let cases = result.cases || result || [];
        cases = cases.filter(c => c.month && c.month.includes(year));
        renderDrilldownTable(cases);
    } catch (err) {
        console.error('Error loading counsel cases:', err);
        document.getElementById('ovDrillBody').innerHTML =
            '<tr><td colspan="8" style="text-align:center; padding:32px; color:#dc2626; font-size:12px;">Failed to load</td></tr>';
    }
}

// ============================================
// Drill-down: Render Table
// ============================================

function renderDrilldownTable(cases) {
    const tbody = document.getElementById('ovDrillBody');

    if (!cases || !cases.length) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:32px; color:#8b8fa3;">No cases found</td></tr>';
        document.getElementById('ovDrillSummary').textContent = '0 cases';
        return;
    }

    const statusMap = {
        'in_progress': '<span class="stat-badge">In Progress</span>',
        'unpaid': '<span class="stat-badge pending">Unpaid</span>',
        'paid': '<span class="stat-badge paid">Paid</span>',
        'rejected': '<span class="stat-badge rejected">Rejected</span>'
    };

    tbody.innerHTML = cases.map(c => {
        const settled = parseFloat(c.settled) || 0;
        const comm = parseFloat(c.commission) || 0;
        const badge = statusMap[c.status] || `<span class="stat-badge">${c.status}</span>`;
        const intake = c.intake_date
            ? new Date(c.intake_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
            : (c.submitted_at ? new Date(c.submitted_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-');

        return `<tr>
            <td style="padding: 8px 12px; font-size: 12px; font-weight: 500;">${escapeHtml(c.case_number || '')}</td>
            <td style="padding: 8px 12px; font-size: 12px;">${escapeHtml(c.client_name || '')}</td>
            <td style="padding: 8px 12px; font-size: 12px;">${escapeHtml(c.counsel_name || '')}</td>
            <td style="padding: 8px 12px; font-size: 12px; color: #5c5f73;">${escapeHtml(c.case_type || '-')}</td>
            <td class="r" style="padding: 8px 12px; font-size: 12px; ${settled === 0 ? 'color:#aaa;' : ''}">${formatCurrency(settled)}</td>
            <td class="r" style="padding: 8px 12px; font-size: 12px; font-weight: 700; ${comm === 0 ? 'color:#aaa;' : 'color:#2e8b57;'}">${formatCurrency(comm)}</td>
            <td class="c" style="padding: 8px 12px; font-size: 11px;">${badge}</td>
            <td style="padding: 8px 12px; font-size: 12px; white-space: nowrap;">${intake}</td>
        </tr>`;
    }).join('');

    const totalSettled = cases.reduce((s, c) => s + (parseFloat(c.settled) || 0), 0);
    const totalComm = cases.reduce((s, c) => s + (parseFloat(c.commission) || 0), 0);
    document.getElementById('ovDrillSummary').textContent =
        `${cases.length} case${cases.length !== 1 ? 's' : ''} · Settlement: ${formatCurrency(totalSettled)} · Commission: ${formatCurrency(totalComm)}`;
}
