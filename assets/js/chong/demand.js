/**
 * ChongDashboard - Demand tab functions.
 */

function toggleSettlementSection(btn) {
    const fields = document.getElementById('newDemandSettlementFields');
    const isHidden = fields.style.display === 'none';
    fields.style.display = isHidden ? '' : 'none';
    btn.classList.toggle('active', isHidden);

    // Clear fields when collapsing
    if (!isHidden) {
        const form = document.getElementById('newDemandForm');
        if (form.settled) form.settled.value = '';
        if (form.discounted_legal_fee) form.discounted_legal_fee.value = '';
        if (form.commission_display) form.commission_display.value = '';
    }
}

async function loadDemandCases() {
    const result = await apiCall('api/chong_cases.php?phase=demand');
    if (result.cases) {
        demandCasesData = result.cases;
        renderDemandTable(demandCasesData);
        updateDemandAlertBar(demandCasesData);
        updateDemandStats(demandCasesData);
    }
}

function updateDemandStats(cases) {
    const total = cases.length;

    const dueIn2Weeks = cases.filter(c => {
        if (!c.deadline_status || c.deadline_status.days === null) return false;
        return c.deadline_status.days >= 0 && c.deadline_status.days <= 14;
    }).length;

    const overdue = cases.filter(c => {
        if (!c.deadline_status || c.deadline_status.days === null) return false;
        return c.deadline_status.days < 0;
    }).length;

    document.getElementById('demandStatTotal').textContent = total;
    document.getElementById('demandStatDue2Weeks').textContent = dueIn2Weeks;
    document.getElementById('demandStatOverdue').textContent = overdue;
}

function renderDemandTable(cases) {
    const tbody = document.getElementById('demandTableBody');
    if (cases.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align:center; padding: 40px; color:#8b8fa3;">No demand cases</td></tr>';
        return;
    }

    tbody.innerHTML = cases.map(c => {
        const deadlineStatus = c.deadline_status || {};
        const daysClass = deadlineStatus.class || '';
        const daysText = deadlineStatus.message || '-';

        // Determine row highlight class based on days left
        let rowClass = '';
        if (deadlineStatus.days !== undefined && deadlineStatus.days !== null) {
            if (deadlineStatus.days < 0) {
                rowClass = 'row-overdue';  // Red - overdue
            } else if (deadlineStatus.days <= 14) {
                rowClass = 'row-critical';  // Yellow - due within 2 weeks
            }
        }

        const statusBadge = c.status === 'in_progress'
            ? '<span class="ink-badge" style="background:#dbeafe;color:#1d4ed8;">in progress</span>'
            : `<span class="ink-badge">${escapeHtml(c.status || 'unpaid')}</span>`;

        // Stage badge
        const stageColors = {
            'demand_review': { bg: '#f3e8ff', color: '#7c3aed', text: 'Demand Review' },
            'demand_write': { bg: '#e0e7ff', color: '#4338ca', text: 'Demand Write' },
            'demand_sent': { bg: '#fef3c7', color: '#b45309', text: 'Demand Sent' },
            'negotiate': { bg: '#d1fae5', color: '#059669', text: 'Negotiate' }
        };
        const stageInfo = stageColors[c.stage] || { bg: '#f3f4f6', color: '#6b7280', text: c.stage || '-' };
        const stageBadge = c.stage
            ? `<span class="ink-badge" style="background:${stageInfo.bg};color:${stageInfo.color};">${stageInfo.text}</span>`
            : '-';

        const isSelected = selectedDemandCaseId === c.id ? 'selected-row' : '';

        return `
            <tr class="${rowClass} ${isSelected} clickable-row" data-id="${c.id}" data-stage="${c.stage || ''}" style="cursor: pointer;">
                <td style="width:0;padding:0;border:none;"></td>
                <td style="font-family: monospace; font-size: 12px;">${escapeHtml(c.case_number)}</td>
                <td style="font-weight: 500;">${escapeHtml(c.client_name)}</td>
                <td>${escapeHtml(c.case_type || '-')}</td>
                <td>${stageBadge}</td>
                <td>${formatDate(c.assigned_date)}</td>
                <td>${formatDate(c.demand_deadline)}</td>
                <td class="${daysClass}">${daysText}</td>
                <td>${statusBadge}</td>
                <td class="action-cell" style="text-align: center;">
                    <div class="action-group center">
                        <button class="act-btn edit" data-action="edit" data-id="${c.id}">Edit</button>
                        <button class="act-btn settle" data-action="settle-demand" data-id="${c.id}" data-case="${escapeHtml(c.case_number)}" data-client="${escapeHtml(c.client_name)}">Settle</button>
                        <button class="act-btn to-lit" data-action="to-litigation" data-id="${c.id}" data-case="${escapeHtml(c.case_number)}" data-client="${escapeHtml(c.client_name)}">To Lit</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    // Add row click event for selecting
    tbody.querySelectorAll('.clickable-row').forEach(row => {
        row.addEventListener('click', function(e) {
            if (!e.target.closest('.action-cell') && !e.target.closest('button')) {
                const id = parseInt(this.dataset.id);
                const stage = this.dataset.stage;
                selectDemandRow(id, stage);
            }
        });
    });

    // Add action button click events
    tbody.querySelectorAll('.act-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const action = this.dataset.action;
            const id = this.dataset.id;
            const caseNum = this.dataset.case;
            const client = this.dataset.client;

            if (action === 'edit') {
                openEditCaseModal(parseInt(id));
            } else if (action === 'settle-demand') {
                openSettleDemandModal(id, caseNum, client);
            } else if (action === 'to-litigation') {
                openToLitigationModal(id, caseNum, client);
            }
        });
    });

    // Update footer using deadline_status from API
    const dueIn2Weeks = cases.filter(c => {
        if (!c.deadline_status || c.deadline_status.days === null) return false;
        return c.deadline_status.days >= 0 && c.deadline_status.days <= 14;
    }).length;
    const overdue = cases.filter(c => {
        if (!c.deadline_status || c.deadline_status.days === null) return false;
        return c.deadline_status.days < 0;
    }).length;

    document.getElementById('demandFooterLeft').textContent = `${cases.length} demand cases`;
    document.getElementById('demandFooterRight').textContent = `Due in 2 Weeks: ${dueIn2Weeks} · Overdue: ${overdue}`;
}

function selectDemandRow(id, stage) {
    selectedDemandCaseId = id;

    // Update row highlighting
    document.querySelectorAll('#demandTableBody .clickable-row').forEach(row => {
        row.classList.remove('selected-row');
        if (parseInt(row.dataset.id) === id) {
            row.classList.add('selected-row');
        }
    });

    // Update Stage card
    const stageLabels = {
        'demand_review': 'Demand Review',
        'demand_write': 'Demand Write',
        'demand_sent': 'Demand Sent',
        'negotiate': 'Negotiate'
    };
    const stageText = stageLabels[stage] || stage || 'Not set';
    document.getElementById('demandStatStage').textContent = stageText;
    document.getElementById('demandStatStage').style.color = stage ? '#1a1a2e' : '#8b8fa3';
    document.getElementById('demandStatStage').style.fontSize = '16px';
}

function updateDemandAlertBar(cases) {
    const bar = document.getElementById('demandAlertBar');
    if (!bar) return;

    const overdue = cases.filter(c => c.deadline_status && c.deadline_status.days < 0).length;
    const critical = cases.filter(c => c.deadline_status && c.deadline_status.days >= 0 && c.deadline_status.days <= 14).length;

    if (overdue > 0) {
        bar.className = 'urgent-bar critical';
        bar.innerHTML = `<span>&#9888;</span> ${overdue} case(s) OVERDUE - Immediate action required!`;
        bar.style.display = 'flex';
    } else if (critical > 0) {
        bar.className = 'urgent-bar red';
        bar.innerHTML = `<span>&#9888;</span> ${critical} case(s) due within 2 weeks`;
        bar.style.display = 'flex';
    } else {
        bar.style.display = 'none';
    }
}

async function deleteDemandCase(caseId) {
    if (!confirm('Are you sure you want to delete this case?')) return;

    const result = await apiCall(`api/chong_cases.php?id=${caseId}`, 'DELETE', { csrf_token: csrfToken });
    if (result.success) {
        showToast('Case deleted', 'success');
        loadDemandCases();
    } else {
        showToast(result.error || 'Failed to delete', 'error');
    }
}

// Demand filter/sort functions
function setDemandFilter(filter, btn) {
    currentDemandFilter = filter;
    // Update active state on chips - reset all styles first
    document.querySelectorAll('#content-demand .f-chip').forEach(chip => {
        chip.classList.remove('active');
        const chipFilter = chip.dataset.filter;
        if (chipFilter === 'due2weeks') {
            chip.style.background = '#fef3c7';
            chip.style.color = '#b45309';
            chip.style.borderColor = '#fde68a';
        } else if (chipFilter === 'overdue') {
            chip.style.background = '#fef2f2';
            chip.style.color = '#b91c1c';
            chip.style.borderColor = '#fecaca';
        }
    });
    btn.classList.add('active');
    // Set active style
    if (filter === 'overdue') {
        btn.style.background = '#b91c1c';
        btn.style.color = '#fff';
        btn.style.borderColor = '#b91c1c';
    } else if (filter === 'due2weeks') {
        btn.style.background = '#b45309';
        btn.style.color = '#fff';
        btn.style.borderColor = '#b45309';
    }
    filterDemandCases();
}

function clickDemandStat(filter) {
    const chip = document.querySelector(`#content-demand .f-chip[data-filter="${filter}"]`);
    if (chip) {
        setDemandFilter(filter, chip);
    }
}

function filterDemandCases() {
    const search = document.getElementById('demandSearch').value.toLowerCase();
    let filtered = demandCasesData.filter(c =>
        c.case_number.toLowerCase().includes(search) ||
        c.client_name.toLowerCase().includes(search)
    );

    if (currentDemandFilter === 'due2weeks') {
        filtered = filtered.filter(c => {
            if (!c.deadline_status || c.deadline_status.days === null) return false;
            return c.deadline_status.days >= 0 && c.deadline_status.days <= 14;
        });
    } else if (currentDemandFilter === 'overdue') {
        filtered = filtered.filter(c => {
            if (!c.deadline_status || c.deadline_status.days === null) return false;
            return c.deadline_status.days < 0;
        });
    }

    renderDemandTable(filtered);
}

function sortDemandCases(column) {
    if (demandSortColumn === column) {
        demandSortDir = demandSortDir === 'asc' ? 'desc' : 'asc';
    } else {
        demandSortColumn = column;
        demandSortDir = 'asc';
    }

    document.querySelectorAll('#demandTable th.sortable').forEach(th => {
        th.classList.remove('asc', 'desc');
        if (th.dataset.sort === column) {
            th.classList.add(demandSortDir);
        }
    });

    demandCasesData.sort((a, b) => {
        let valA = a[column];
        let valB = b[column];

        if (valA == null) valA = '';
        if (valB == null) valB = '';

        if (column === 'days_left') {
            valA = a.demand_deadline ? Math.ceil((new Date(a.demand_deadline) - new Date()) / (1000 * 60 * 60 * 24)) : 999;
            valB = b.demand_deadline ? Math.ceil((new Date(b.demand_deadline) - new Date()) / (1000 * 60 * 60 * 24)) : 999;
        }

        if (['days_left'].includes(column)) {
            valA = parseFloat(valA) || 0;
            valB = parseFloat(valB) || 0;
        } else {
            valA = String(valA).toLowerCase();
            valB = String(valB).toLowerCase();
        }

        if (valA < valB) return demandSortDir === 'asc' ? -1 : 1;
        if (valA > valB) return demandSortDir === 'asc' ? 1 : -1;
        return 0;
    });

    filterDemandCases();
}

// Demand modal functions
function openNewDemandModal() {
    document.getElementById('newDemandForm').reset();
    document.querySelector('#newDemandForm [name="assigned_date"]').value = new Date().toISOString().split('T')[0];
    openModal('newDemandModal');
}

function openSettleDemandModal(caseId, caseNumber, clientName) {
    const form = document.getElementById('settleDemandForm');
    form.reset();
    form.querySelector('[name="case_id"]').value = caseId;
    form.querySelector('[name="discounted_legal_fee"]').dataset.userModified = '';
    form.querySelector('[name="legal_fee_display"]').value = '';
    form.querySelector('[name="commission_display"]').value = '';
    document.getElementById('settleDemandCaseInfo').textContent = `${caseNumber} - ${clientName}`;
    openModal('settleDemandModal');
}

function openToLitigationModal(caseId, caseNumber, clientName) {
    document.getElementById('toLitigationForm').reset();
    document.querySelector('#toLitigationForm [name="case_id"]').value = caseId;
    document.querySelector('#toLitigationForm [name="litigation_start_date"]').value = new Date().toISOString().split('T')[0];
    document.getElementById('toLitCaseInfo').textContent = `${caseNumber} - ${clientName}`;
    openModal('toLitigationModal');
}

// Demand calculation functions
function calculateDemandCommission() {
    const form = document.getElementById('newDemandForm');
    const discLegalFee = parseFloat(form.discounted_legal_fee.value) || 0;
    const commission = discLegalFee * 0.05;
    form.commission_display.value = formatCurrency(commission);
}

function calculateSettleDemandCommission() {
    const form = document.getElementById('settleDemandForm');
    const discLegalFee = parseFloat(form.discounted_legal_fee.value) || 0;
    const commission = discLegalFee * 0.05;
    form.commission_display.value = formatCurrency(commission);
}

function updateSettleDemandLegalFee() {
    const form = document.getElementById('settleDemandForm');
    const settled = parseFloat(form.settled.value) || 0;
    const legalFee = settled / 3; // 33.33%

    form.legal_fee_display.value = formatCurrency(legalFee);

    if (!form.discounted_legal_fee.dataset.userModified) {
        form.discounted_legal_fee.value = legalFee.toFixed(2);
    }

    calculateSettleDemandCommission();
}

// Demand form submissions
async function submitNewDemand(event) {
    event.preventDefault();
    const form = event.target;
    const data = {
        case_number: form.case_number.value,
        client_name: form.client_name.value,
        case_type: form.case_type.value,
        phase: form.phase.value,
        stage: form.stage.value,
        assigned_date: form.assigned_date.value,
        settled: parseFloat(form.settled.value) || 0,
        discounted_legal_fee: parseFloat(form.discounted_legal_fee.value) || 0,
        month: form.month.value,
        note: form.note.value
    };

    const result = await apiCall('api/chong_cases.php', 'POST', data);
    if (result.success) {
        closeModal('newDemandModal');
        loadDashboard();
        loadDemandCases();
        loadCommissions();
        alert('Demand case added successfully!');
    } else {
        alert('Error: ' + (result.error || 'Failed to add case'));
    }
}

async function submitSettleDemand(event) {
    event.preventDefault();
    const form = event.target;
    const caseId = form.case_id.value;
    const data = {
        settled: parseFloat(form.settled.value),
        discounted_legal_fee: parseFloat(form.discounted_legal_fee.value),
        month: form.month.value,
        check_received: form.check_received.checked
    };

    const result = await apiCall(`api/chong_cases.php?id=${caseId}&action=settle_demand`, 'PUT', data);
    if (result.success) {
        closeModal('settleDemandModal');
        loadDashboard();
        loadDemandCases();
        loadCommissions();
        alert(`Case settled! Commission: ${formatCurrency(result.commission)}`);
    } else {
        alert('Error: ' + (result.error || 'Failed to settle case'));
    }
}

async function submitToLitigation(event) {
    event.preventDefault();
    const form = event.target;
    const caseId = form.case_id.value;
    const data = {
        litigation_start_date: form.litigation_start_date.value,
        presuit_offer: parseFloat(form.presuit_offer.value) || 0,
        note: form.note.value
    };

    const result = await apiCall(`api/chong_cases.php?id=${caseId}&action=to_litigation`, 'PUT', data);
    if (result.success) {
        closeModal('toLitigationModal');
        loadDashboard();
        loadDemandCases();
        loadLitigationCases();
        loadCommissions();
        alert('Case moved to Litigation!');
    } else {
        alert('Error: ' + (result.error || 'Failed to move case'));
    }
}
