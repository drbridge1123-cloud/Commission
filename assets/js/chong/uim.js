/**
 * ChongDashboard - UIM (Underinsured Motorist) tab functions.
 * Cases transition here after settling demand/litigation with Policy Limit checked.
 */

async function loadUimCases() {
    const result = await apiCall('api/chong_cases.php?phase=uim');
    if (result.cases) {
        uimCasesData = result.cases;
        renderUimTable(uimCasesData);

        // Update badge
        const badge = document.getElementById('uimBadge');
        if (badge) {
            badge.textContent = uimCasesData.length;
            badge.style.display = uimCasesData.length > 0 ? '' : 'none';
        }
    }
}

function renderUimTable(cases) {
    const tbody = document.getElementById('uimTableBody');

    // Stats
    document.getElementById('uimStatTotal').textContent = cases.length;
    const totalPrevSettled = cases.reduce((sum, c) => sum + parseFloat(c.settled || 0), 0);
    const totalPrevComm = cases.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);
    document.getElementById('uimStatPrevSettled').textContent = formatCurrency(totalPrevSettled);
    document.getElementById('uimStatPrevComm').textContent = formatCurrency(totalPrevComm);

    if (cases.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align:center; padding: 40px; color: #8b8fa3;">No UIM cases</td></tr>';
        document.getElementById('uimFooterLeft').textContent = '0 UIM cases';
        return;
    }

    // Sort
    const sorted = [...cases].sort((a, b) => {
        let aVal = a[uimSortColumn], bVal = b[uimSortColumn];
        if (uimSortColumn === 'settled' || uimSortColumn === 'uim_days') {
            aVal = parseFloat(aVal) || 0;
            bVal = parseFloat(bVal) || 0;
        } else {
            aVal = (aVal || '').toString().toLowerCase();
            bVal = (bVal || '').toString().toLowerCase();
        }
        if (aVal < bVal) return uimSortDir === 'asc' ? -1 : 1;
        if (aVal > bVal) return uimSortDir === 'asc' ? 1 : -1;
        return 0;
    });

    tbody.innerHTML = sorted.map(c => {
        const today = new Date();
        const uimStart = c.uim_start_date ? new Date(c.uim_start_date + 'T00:00:00') : null;
        const daysInUim = uimStart ? Math.floor((today - uimStart) / 86400000) : '-';

        // UIM Demand Out checkbox cell
        const uimDemandOutCell = buildUimDateCell(c, 'uim_demand_out_date');
        // UIM Negotiate checkbox cell
        const uimNegotiateCell = buildUimDateCell(c, 'uim_negotiate_date');

        const prevSettled = formatCurrency(c.settled || 0);
        const resType = c.resolution_type || '-';

        return `
            <tr class="clickable-row" data-id="${c.id}">
                <td style="width:0;padding:0;border:none;"></td>
                <td style="font-family: monospace; font-size: 12px;">${escapeHtml(c.case_number)}</td>
                <td style="font-weight: 500;">${escapeHtml(c.client_name)}</td>
                <td style="text-align: right; font-weight: 600;">${prevSettled}</td>
                <td><span style="font-size: 11px; color: #6b7280;">${escapeHtml(resType)}</span></td>
                <td>${formatDate(c.uim_start_date)}</td>
                ${uimDemandOutCell}
                ${uimNegotiateCell}
                <td style="font-weight: 600;">${daysInUim}</td>
                <td class="action-cell" style="text-align: center;">
                    <div class="action-group center">
                        <button class="act-btn settle" data-action="settle-uim" data-id="${c.id}" data-case="${escapeHtml(c.case_number)}" data-client="${escapeHtml(c.client_name)}" data-settled="${c.settled || 0}" data-commission="${c.commission || 0}">Settle</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    // Row click → edit case modal
    tbody.querySelectorAll('.clickable-row').forEach(row => {
        row.addEventListener('click', function(e) {
            if (!e.target.closest('.action-cell') && !e.target.closest('button') && !e.target.closest('.date-check')) {
                openEditCaseModal(parseInt(this.dataset.id));
            }
        });
    });

    // Action button events
    tbody.querySelectorAll('.act-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (this.dataset.action === 'settle-uim') {
                openSettleUimModal(
                    this.dataset.id,
                    this.dataset.case,
                    this.dataset.client,
                    parseFloat(this.dataset.settled),
                    parseFloat(this.dataset.commission)
                );
            }
        });
    });

    document.getElementById('uimFooterLeft').textContent = `${cases.length} UIM case${cases.length !== 1 ? 's' : ''}`;
}

function buildUimDateCell(c, field) {
    const dateVal = c[field];
    if (dateVal) {
        const d = new Date(dateVal + 'T00:00:00');
        const display = d.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit' });
        return `<td class="date-check" style="text-align:center; cursor:pointer;" onclick="toggleUimDate(event, ${c.id}, '${field}')">
            <span style="color:#059669; font-weight:600; font-size:12px;">${display}</span>
        </td>`;
    } else {
        return `<td class="date-check" style="text-align:center; cursor:pointer;" onclick="toggleUimDate(event, ${c.id}, '${field}')">
            <span style="color:#d1d5db; font-size:16px;">&#9633;</span>
        </td>`;
    }
}

async function toggleUimDate(event, caseId, field) {
    event.stopPropagation();
    const c = uimCasesData.find(x => x.id == caseId);
    if (!c) return;

    const currentVal = c[field];
    const newDate = currentVal ? null : new Date().toISOString().split('T')[0];

    const result = await apiCall(`api/chong_cases.php?id=${caseId}&action=toggle_uim_date`, 'PUT', {
        field: field,
        date: newDate
    });

    if (result.success) {
        loadUimCases();
    }
}

function sortUimCases(column) {
    if (uimSortColumn === column) {
        uimSortDir = uimSortDir === 'asc' ? 'desc' : 'asc';
    } else {
        uimSortColumn = column;
        uimSortDir = 'asc';
    }
    renderUimTable(uimCasesData);
}

// Settle UIM Modal
function openSettleUimModal(caseId, caseNumber, clientName, prevSettled, prevCommission) {
    const form = document.getElementById('settleUimForm');
    form.reset();
    form.querySelector('[name="case_id"]').value = caseId;
    form.querySelector('[name="discounted_legal_fee"]').dataset.userModified = '';
    form.querySelector('[name="legal_fee_display"]').value = '';
    form.querySelector('[name="commission_display"]').value = '';
    document.getElementById('settleUimCaseInfo').textContent = `${caseNumber} - ${clientName}`;
    document.getElementById('settleUimPrevSettled').textContent = formatCurrency(prevSettled);
    document.getElementById('settleUimPrevComm').textContent = formatCurrency(prevCommission);
    openModal('settleUimModal');
}

function updateSettleUimLegalFee() {
    const form = document.getElementById('settleUimForm');
    const settled = parseFloat(form.settled.value) || 0;
    const legalFee = settled / 3;

    form.legal_fee_display.value = formatCurrency(legalFee);

    if (!form.discounted_legal_fee.dataset.userModified) {
        form.discounted_legal_fee.value = legalFee.toFixed(2);
    }

    calculateSettleUimCommission();
}

function calculateSettleUimCommission() {
    const form = document.getElementById('settleUimForm');
    const discLegalFee = parseFloat(form.discounted_legal_fee.value) || 0;
    const commission = discLegalFee * 0.05;
    form.commission_display.value = formatCurrency(commission);
}

async function submitSettleUim(event) {
    event.preventDefault();
    const form = event.target;
    const caseId = form.case_id.value;
    const data = {
        settled: parseFloat(form.settled.value),
        discounted_legal_fee: parseFloat(form.discounted_legal_fee.value),
        month: form.month.value,
        check_received: form.check_received.checked
    };

    const result = await apiCall(`api/chong_cases.php?id=${caseId}&action=settle_uim`, 'PUT', data);
    if (result.success) {
        closeModal('settleUimModal');
        loadDashboard();
        loadUimCases();
        loadCommissions();
        alert(`UIM Settled! UIM Commission: ${formatCurrency(result.uim_commission)}\nTotal Commission: ${formatCurrency(result.total_commission)}`);
    } else {
        alert('Error: ' + (result.error || 'Failed to settle UIM case'));
    }
}
