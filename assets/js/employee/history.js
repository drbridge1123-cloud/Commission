/**
 * Employee dashboard - History tab functions.
 */

function initHistoryFilters() {
    const yearSel = document.getElementById('historyYear');
    const monthSel = document.getElementById('historyMonth');
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth();
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    // Populate years from cases data
    const years = [...new Set(
        allCases.filter(c => c.status === 'paid' || c.status === 'rejected')
            .map(c => {
                const match = (c.month || '').match(/\d{4}/);
                return match ? parseInt(match[0]) : null;
            }).filter(Boolean)
    )];
    if (!years.includes(currentYear)) years.push(currentYear);
    years.sort((a, b) => b - a);

    yearSel.innerHTML = '';
    years.forEach(y => {
        yearSel.innerHTML += `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`;
    });

    // Populate months
    monthSel.innerHTML = '<option value="all">All Months</option>';
    months.forEach(m => {
        monthSel.innerHTML += `<option value="${m}">${m}</option>`;
    });

    // Default: Monthly, current year, All Months
    document.getElementById('historyPeriod').value = 'monthly';
    onHistoryPeriodChange();
}

function onHistoryPeriodChange() {
    const period = document.getElementById('historyPeriod').value;
    const yearSel = document.getElementById('historyYear');
    const monthSel = document.getElementById('historyMonth');

    if (period === 'all') {
        yearSel.style.display = 'none';
        monthSel.style.display = 'none';
    } else if (period === 'yearly') {
        yearSel.style.display = '';
        monthSel.style.display = 'none';
    } else {
        // monthly
        yearSel.style.display = '';
        monthSel.style.display = '';
    }

    loadHistory();
}

function getHistoryFiltered() {
    const period = document.getElementById('historyPeriod').value;
    const year = document.getElementById('historyYear').value;
    const month = document.getElementById('historyMonth').value;

    let cases = allCases.filter(c => c.status === 'paid' || c.status === 'rejected');

    if (period === 'yearly') {
        cases = cases.filter(c => (c.month || '').includes(year));
    } else if (period === 'monthly') {
        cases = cases.filter(c => (c.month || '').includes(year));
        if (month !== 'all') {
            cases = cases.filter(c => c.month && c.month.startsWith(month));
        }
    }
    // 'all' → no filter

    // Sort newest first
    cases.sort((a, b) => {
        const aDate = a.reviewed_at || a.submitted_at;
        const bDate = b.reviewed_at || b.submitted_at;
        return new Date(bDate) - new Date(aDate);
    });

    return cases;
}

function loadHistory() {
    const paid = getHistoryFiltered();
    const content = document.getElementById('historyContent');

    if (paid.length === 0) {
        content.innerHTML = '<p style="text-align: center; color: #8b8fa3; padding: 32px; font-size: 12px; font-family: Outfit, sans-serif;">No completed cases yet</p>';
        return;
    }

    const totalCommission = paid.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);

    const formatPaymentDate = (dateStr) => {
        if (!dateStr) return '<span class="mute">-</span>';
        const date = new Date(dateStr);
        return date.toLocaleString('en-US', { month: 'short', year: 'numeric' });
    };

    // Group cases by month
    const casesByMonth = {};
    paid.forEach(c => {
        const monthKey = c.month || 'Unknown';
        if (!casesByMonth[monthKey]) casesByMonth[monthKey] = [];
        casesByMonth[monthKey].push(c);
    });

    // Sort months from newest to oldest
    const sortedMonths = Object.keys(casesByMonth).sort((a, b) => {
        const parseMonth = (monthStr) => {
            if (!monthStr || monthStr === 'Unknown') return new Date(0);
            const parts = monthStr.split('. ');
            if (parts.length !== 2) return new Date(0);
            const [monthAbbr, year] = parts;
            const monthMap = {'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'May': 4, 'Jun': 5, 'Jul': 6, 'Aug': 7, 'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11};
            return new Date(parseInt(year), monthMap[monthAbbr] || 0);
        };
        return parseMonth(b) - parseMonth(a);
    });

    let tableHtml = `
        <table class="tbl">
            <thead>
                <tr>
                    <th style="width:0;padding:0;border:none;"></th>
                    <th>Resolution</th>
                    <th>Client</th>
                    <th class="c">Settled</th>
                    <th class="c">Legal Fee</th>
                    <th class="c">Disc. Legal Fee</th>
                    <th class="c">Commission</th>
                    <th class="c">Status</th>
                    <th class="c">Paid Date</th>
                    <th class="c">Check</th>
                </tr>
            </thead>
            <tbody>
    `;

    sortedMonths.forEach(monthKey => {
        const cases = casesByMonth[monthKey];
        const caseCount = cases.length;
        const monthTotal = cases.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);

        tableHtml += `
            <tr style="background: #f0f1f3;">
                <td colspan="10" style="padding: 10px 12px; font-weight: 700; font-size: 12px; color: #1a1a2e; font-family: 'Outfit', sans-serif;">
                    ${monthKey}
                    <span style="float: right; color: #0d9488; font-size: 11px;">
                        ${caseCount} case${caseCount !== 1 ? 's' : ''} · $${monthTotal.toLocaleString('en-US', {minimumFractionDigits: 2})}
                    </span>
                </td>
            </tr>
        `;

        cases.forEach(c => {
            tableHtml += `
                <tr onclick="viewCaseDetail(${c.id})" style="cursor:pointer;">
                    <td style="width:0;padding:0;border:none;"></td>
                    <td>${c.resolution_type || '-'}</td>
                    <td>${c.client_name}</td>
                    <td class="c" style="font-weight:600;">${formatCurrency(c.settled || 0)}</td>
                    <td class="c">${formatCurrency(c.legal_fee || 0)}</td>
                    <td class="c">${formatCurrency(c.discounted_legal_fee || 0)}</td>
                    <td class="c" style="font-weight:700; color:#0d9488;">${formatCurrency(c.commission || 0)}</td>
                    <td class="c">${c.status === 'rejected' ? '<span class="stat-badge" style="background:#fee2e2;color:#991b1b;">Rejected</span>' : '<span class="stat-badge paid">Paid</span>'}</td>
                    <td class="c">${formatPaymentDate(c.reviewed_at || c.submitted_at)}</td>
                    <td class="c">${c.check_received ? '<span style="display:inline-block;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:700;background:#d1fae5;color:#065f46;">Received</span>' : '<span style="display:inline-block;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:700;background:#fef3c7;color:#92400e;">Pending</span>'}</td>
                </tr>
            `;
        });
    });

    tableHtml += `</tbody></table>`;

    tableHtml += `
        <div class="tbl-foot">
            <div class="left">${paid.length} case${paid.length !== 1 ? 's' : ''}</div>
            <div class="right">
                <div class="ft"><span class="ft-l">Total:</span><span class="ft-v green">$${totalCommission.toLocaleString('en-US', {minimumFractionDigits: 2})}</span></div>
            </div>
        </div>
    `;

    content.innerHTML = tableHtml;
}

// Export history function
function exportHistory() {
    const paid = getHistoryFiltered();

    if (paid.length === 0) {
        alert('No data to export');
        return;
    }

    // Create CSV content
    const headers = ['Case #', 'Client', 'Month', 'Status', 'Payment Date', 'Commission'];
    const rows = paid.map(c => [
        c.case_number,
        c.client_name,
        c.month,
        c.status === 'rejected' ? 'Rejected' : 'Paid',
        c.reviewed_at || c.submitted_at,
        c.commission
    ]);

    const csvContent = [
        headers.join(','),
        ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
    ].join('\n');

    // Download CSV
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `commission-history-${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
