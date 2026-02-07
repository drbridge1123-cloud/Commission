/**
 * Admin Dashboard - History tab functions.
 */

async function loadHistory() {
    try {
        const data = await apiCall('api/cases.php');
        const allHistoryCases = data.cases || [];

        const paidCases = allHistoryCases.filter(c => c.status === 'paid');
        if (!historyDropdownsInitialized) {
            populateHistoryDropdowns(paidCases);
            historyDropdownsInitialized = true;
        }

        const searchText = (document.getElementById('historySearch')?.value || '').toLowerCase();
        const employeeFilter = document.getElementById('historyEmployee')?.value || 'all';
        const monthFilter = document.getElementById('historyMonth')?.value || 'all';

        let filtered = paidCases;

        if (employeeFilter !== 'all') {
            filtered = filtered.filter(c => c.counsel_name?.toLowerCase() === employeeFilter.toLowerCase());
        }

        if (monthFilter !== 'all') {
            filtered = filtered.filter(c => c.month === monthFilter);
        }

        if (searchText) {
            filtered = filtered.filter(c =>
                (c.case_number || '').toLowerCase().includes(searchText) ||
                (c.client_name || '').toLowerCase().includes(searchText) ||
                (c.note || '').toLowerCase().includes(searchText)
            );
        }

        historyCases = filtered;

        filtered.sort((a, b) => {
            const aDate = a.reviewed_at || a.submitted_at;
            const bDate = b.reviewed_at || b.submitted_at;
            return new Date(bDate) - new Date(aDate);
        });

        const content = document.getElementById('historyContent');

        if (filtered.length === 0) {
            content.innerHTML = '<p style="text-align: center; color: #8b8fa3; padding: 32px; font-size: 12px; font-family: Outfit, sans-serif;">No cases found</p>';
            return;
        }

        const totalCommission = filtered.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);

        const formatPaymentDate = (dateStr) => {
            if (!dateStr) return '<span class="mute">-</span>';
            const date = new Date(dateStr);
            return date.toLocaleString('en-US', { month: 'short', year: 'numeric' });
        };

        const getHistoryStatusBadge = (status) => {
            if (status === 'paid') return '<span class="stat-badge paid">Paid</span>';
            if (status === 'pending') return '<span class="stat-badge pending">Pending</span>';
            if (status === 'in_progress') return '<span class="stat-badge in_progress">In Progress</span>';
            if (status === 'rejected') return '<span class="stat-badge rejected">Rejected</span>';
            return `<span class="stat-badge">${status}</span>`;
        };

        const casesByMonth = {};
        filtered.forEach(c => {
            const monthKey = c.month || 'Unknown';
            if (!casesByMonth[monthKey]) casesByMonth[monthKey] = [];
            casesByMonth[monthKey].push(c);
        });

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
            <table class="tbl" id="historyTable" style="table-layout: auto;">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Case #</th>
                        <th>Client</th>
                        <th>Resolution</th>
                        <th class="r">Settled</th>
                        <th class="r">Disc. Fee</th>
                        <th class="r">Commission</th>
                        <th>Paid Date</th>
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
                    <td colspan="9" style="padding: 10px 12px; font-weight: 700; font-size: 12px; color: #1a1a2e; font-family: 'Outfit', sans-serif;">
                        ${monthKey}
                        <span style="float: right; color: #0d9488; font-size: 11px;">
                            ${caseCount} case${caseCount !== 1 ? 's' : ''} · $${monthTotal.toLocaleString('en-US', {minimumFractionDigits: 2})}
                        </span>
                    </td>
                </tr>
            `;

            cases.forEach(c => {
                tableHtml += `
                    <tr onclick="viewHistoryDetail(${c.id})" style="cursor:pointer;">
                        <td style="font-weight: 600;">${c.counsel_name || '-'}</td>
                        <td style="font-weight: 600;">${c.case_number}</td>
                        <td>${c.client_name}</td>
                        <td style="font-size:11px;">${c.resolution_type || '-'}</td>
                        <td class="r">${formatCurrency(c.settled || 0)}</td>
                        <td class="r">${formatCurrency(c.discounted_legal_fee || 0)}</td>
                        <td class="r em">${formatCurrency(c.commission || 0)}</td>
                        <td>${formatPaymentDate(c.reviewed_at || c.submitted_at)}</td>
                        <td class="c">${c.check_received ? '<span style="display:inline-block;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:700;background:#d1fae5;color:#065f46;">Received</span>' : '<span style="display:inline-block;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:700;background:#fef3c7;color:#92400e;">Pending</span>'}</td>
                    </tr>
                `;
            });
        });

        tableHtml += `</tbody></table>`;

        tableHtml += `
            <div class="tbl-foot">
                <div class="left">${filtered.length} case${filtered.length !== 1 ? 's' : ''}</div>
                <div class="right">
                    <div class="ft"><span class="ft-l">Total:</span><span class="ft-v green">$${totalCommission.toLocaleString('en-US', {minimumFractionDigits: 2})}</span></div>
                </div>
            </div>
        `;

        content.innerHTML = tableHtml;
    } catch (err) {
        console.error('Error loading history:', err);
        document.getElementById('historyContent').innerHTML =
            '<p style="text-align: center; color: #ef4444; padding: 32px;">Error loading data</p>';
    }
}

function populateHistoryDropdowns(cases) {
    const employeeSelect = document.getElementById('historyEmployee');
    const employees = [...new Set(cases.map(c => c.counsel_name).filter(Boolean))].sort();
    employees.forEach(emp => {
        const option = document.createElement('option');
        option.value = emp;
        option.textContent = emp;
        employeeSelect.appendChild(option);
    });

    const monthSelect = document.getElementById('historyMonth');
    const months = [...new Set(cases.map(c => c.month).filter(Boolean))];

    months.sort((a, b) => {
        const parseMonth = (monthStr) => {
            const parts = monthStr.split('. ');
            if (parts.length !== 2) return new Date(0);
            const [monthAbbr, year] = parts;
            const monthMap = {'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'May': 4, 'Jun': 5,
                             'Jul': 6, 'Aug': 7, 'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11};
            return new Date(parseInt(year), monthMap[monthAbbr] || 0);
        };
        return parseMonth(b) - parseMonth(a);
    });

    months.forEach(month => {
        const option = document.createElement('option');
        option.value = month;
        option.textContent = month;
        monthSelect.appendChild(option);
    });
}

function resetHistoryFilters() {
    document.getElementById('historySearch').value = '';
    document.getElementById('historyEmployee').value = 'all';
    document.getElementById('historyMonth').value = 'all';
    loadHistory();
}

function exportHistoryAdmin() {
    const employeeFilter = document.getElementById('historyEmployee')?.value || 'all';
    const monthFilter = document.getElementById('historyMonth')?.value || 'all';
    const searchText = document.getElementById('historySearch')?.value || '';

    let url = `api/export.php?type=history&status=paid`;
    if (employeeFilter !== 'all') url += `&employee=${encodeURIComponent(employeeFilter)}`;
    if (monthFilter !== 'all') url += `&month=${encodeURIComponent(monthFilter)}`;
    if (searchText) url += `&search=${encodeURIComponent(searchText)}`;

    window.location.href = url;
}

function viewHistoryDetail(id) {
    const c = historyCases.find(x => x.id == id);
    if (!c) {
        const ac = allCases.find(x => x.id == id);
        if (ac) { viewCaseDetail(id); return; }
        return;
    }

    const modal = document.getElementById('historyDetailModal');
    const content = document.getElementById('historyDetailContent');

    const formatDetailDate = (d) => {
        if (!d) return '-';
        return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    content.innerHTML = `
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Counsel</div>
                <div style="font-size: 13px; font-weight: 600; color: #1a1a2e;">${c.counsel_name || '-'}</div>
            </div>
            <div>
                <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Month</div>
                <div style="font-size: 13px; font-weight: 600; color: #1a1a2e;">${c.month}</div>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Case Number</div>
                <div style="font-size: 13px; font-weight: 600; color: #1a1a2e;">${c.case_number}</div>
            </div>
            <div>
                <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Case Type</div>
                <div style="font-size: 13px; color: #1a1a2e;">${c.case_type || '-'}</div>
            </div>
        </div>
        <div style="margin-bottom: 16px;">
            <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Client Name</div>
            <div style="font-size: 14px; font-weight: 700; color: #1a1a2e;">${c.client_name}</div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Resolution</div>
                <div style="font-size: 13px; color: #1a1a2e;">${c.resolution_type || '-'}</div>
            </div>
            <div>
                <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Fee Rate</div>
                <div style="font-size: 13px; color: #1a1a2e;">${c.fee_rate}%</div>
            </div>
        </div>
        <div style="background: #f8fafc; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 8px;">
                <div>
                    <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase;">Settled</div>
                    <div style="font-size: 14px; font-weight: 700; color: #1a1a2e;">${formatCurrency(c.settled)}</div>
                </div>
                <div>
                    <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase;">Pre-Suit</div>
                    <div style="font-size: 14px; font-weight: 600; color: #1a1a2e;">${formatCurrency(c.presuit_offer || 0)}</div>
                </div>
                <div>
                    <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase;">Difference</div>
                    <div style="font-size: 14px; font-weight: 600; color: #1a1a2e;">${formatCurrency(c.difference || 0)}</div>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                <div>
                    <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase;">Legal Fee</div>
                    <div style="font-size: 14px; font-weight: 600; color: #1a1a2e;">${formatCurrency(c.legal_fee || 0)}</div>
                </div>
                <div>
                    <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase;">Disc. Fee</div>
                    <div style="font-size: 14px; font-weight: 600; color: #1a1a2e;">${formatCurrency(c.discounted_legal_fee || 0)}</div>
                </div>
                <div>
                    <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase;">Commission</div>
                    <div style="font-size: 14px; font-weight: 700; color: #0d9488;">${formatCurrency(c.commission)}</div>
                </div>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div>
                <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Paid Date</div>
                <div style="font-size: 13px; color: #1a1a2e;">${formatDetailDate(c.reviewed_at)}</div>
            </div>
            <div>
                <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Check Received</div>
                <div style="font-size: 13px; color: ${c.check_received ? '#059669' : '#dc2626'}; font-weight: 600;">${c.check_received ? 'Yes' : 'No'}</div>
            </div>
            <div>
                <div style="font-size: 10px; color: #8b8fa3; text-transform: uppercase; margin-bottom: 2px;">Submitted</div>
                <div style="font-size: 13px; color: #1a1a2e;">${formatDetailDate(c.submitted_at)}</div>
            </div>
        </div>
        ${c.note ? `
        <div style="margin-top: 16px; padding: 10px 14px; background: #fffbeb; border-radius: 6px; border: 1px solid #fde68a;">
            <div style="font-size: 10px; color: #92400e; text-transform: uppercase; margin-bottom: 2px;">Note</div>
            <div style="font-size: 12px; color: #78350f;">${c.note}</div>
        </div>` : ''}
    `;

    modal.classList.add('show');
}

function showCaseDetailAdmin(caseId) {
    let caseData = allCases.find(c => c.id === caseId);

    if (!caseData) {
        const item = allItems.find(i => i.type === 'case_notification' && i.case_id === caseId);
        if (item && item.caseData) {
            caseData = item.caseData;
        }
    }

    if (!caseData) {
        alert('Case not found');
        return;
    }

    const statusBadge = document.getElementById('adminDetailStatusBadge');
    statusBadge.className = 'status-badge';
    if (caseData.status === 'paid') {
        statusBadge.classList.add('badge-paid');
        statusBadge.textContent = 'Approved / Paid';
    } else if (caseData.status === 'rejected') {
        statusBadge.classList.add('badge-rejected');
        statusBadge.textContent = 'Rejected';
    } else {
        statusBadge.classList.add('badge-pending');
        statusBadge.textContent = 'Pending';
    }

    document.getElementById('adminDetailCaseNumber').textContent = caseData.case_number;
    document.getElementById('adminDetailClientName').textContent = caseData.client_name;
    document.getElementById('adminDetailCounsel').textContent = caseData.counsel_name || '-';
    document.getElementById('adminDetailCaseType').textContent = caseData.case_type || '-';
    document.getElementById('adminDetailResolutionType').textContent = caseData.resolution_type || '-';
    document.getElementById('adminDetailMonth').textContent = caseData.month;
    document.getElementById('adminDetailFeeRate').textContent = caseData.fee_rate + '%';

    document.getElementById('adminDetailSettled').textContent = '$' + parseFloat(caseData.settled).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('adminDetailPresuitOffer').textContent = '$' + parseFloat(caseData.presuit_offer).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('adminDetailDifference').textContent = '$' + parseFloat(caseData.difference).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('adminDetailLegalFee').textContent = '$' + parseFloat(caseData.legal_fee).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('adminDetailDiscountedLegalFee').textContent = '$' + parseFloat(caseData.discounted_legal_fee).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('adminDetailCommission').textContent = '$' + parseFloat(caseData.commission).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    const noteSection = document.getElementById('adminDetailNoteSection');
    const noteDiv = document.getElementById('adminDetailNote');
    if (caseData.note && caseData.note.trim()) {
        noteDiv.textContent = caseData.note;
        noteSection.style.display = 'block';
    } else {
        noteSection.style.display = 'none';
    }

    document.getElementById('adminDetailSubmittedAt').textContent = new Date(caseData.submitted_at).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    const reviewedSection = document.getElementById('adminDetailReviewedSection');
    if (caseData.reviewed_at) {
        document.getElementById('adminDetailReviewedAt').textContent = new Date(caseData.reviewed_at).toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        reviewedSection.style.display = 'block';
    } else {
        reviewedSection.style.display = 'none';
    }

    document.getElementById('messageCaseDetailModal').style.display = 'flex';
}

function closeCaseDetailAdmin() {
    document.getElementById('messageCaseDetailModal').style.display = 'none';
}
