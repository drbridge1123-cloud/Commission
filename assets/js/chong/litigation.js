/**
 * ChongDashboard - Litigation tab functions.
 */

async function loadLitigationCases() {
    const result = await apiCall('api/chong_cases.php?phase=litigation');
    if (result.cases) {
        litigationCasesData = result.cases;
        renderLitigationTable(litigationCasesData);
        updateLitigationStats(litigationCasesData);
    }
}

function updateLitigationStats(cases) {
    const total = cases.length;
    const active = cases.filter(c => c.status === 'active' || c.status === 'in_progress').length;
    const settled = cases.filter(c => c.status === 'settled' || c.status === 'closed').length;

    // Calculate average duration
    let totalDays = 0;
    let countWithDuration = 0;
    cases.forEach(c => {
        if (c.litigation_start_date) {
            const startDate = new Date(c.litigation_start_date);
            const endDate = c.litigation_settled_date ? new Date(c.litigation_settled_date) : new Date();
            const days = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24));
            totalDays += days;
            countWithDuration++;
        }
    });
    const avgDuration = countWithDuration > 0 ? Math.round(totalDays / countWithDuration) : 0;

    document.getElementById('litStatTotal').textContent = total;
    document.getElementById('litStatActive').textContent = active;
    document.getElementById('litStatSettled').textContent = settled;
    document.getElementById('litStatAvgDuration').textContent = avgDuration + 'd';
}

function renderLitigationTable(cases) {
    const tbody = document.getElementById('litigationTableBody');
    if (cases.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; color:#6b7280; padding: 40px;">No litigation cases</td></tr>';
        return;
    }

    tbody.innerHTML = cases.map(c => {
        const duration = c.litigation_start_date ? Math.floor((new Date() - new Date(c.litigation_start_date)) / (1000 * 60 * 60 * 24)) + ' days' : '-';
        const statusBadge = c.status === 'in_progress'
            ? '<span class="ink-badge" style="background:#dbeafe;color:#1d4ed8;">in progress</span>'
            : `<span class="ink-badge">${escapeHtml(c.status)}</span>`;

        return `
            <tr class="clickable-row" data-id="${c.id}" style="cursor:pointer;">
                <td style="width:0;padding:0;border:none;"></td>
                <td style="font-family: monospace; font-size: 12px;">${escapeHtml(c.case_number)}</td>
                <td style="font-weight: 500;">${escapeHtml(c.client_name)}</td>
                <td>${formatDate(c.litigation_start_date)}</td>
                <td>${duration}</td>
                <td style="text-align: right;">${formatCurrency(c.presuit_offer || 0)}</td>
                <td>${statusBadge}</td>
                <td class="action-cell" style="text-align: center;">
                    <div class="action-group center">
                        <button class="act-btn settle" data-action="settle-litigation" data-id="${c.id}" data-case="${escapeHtml(c.case_number)}" data-client="${escapeHtml(c.client_name)}" data-presuit="${c.presuit_offer || 0}">Settle</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    // Add row click event for editing
    tbody.querySelectorAll('.clickable-row').forEach(row => {
        row.addEventListener('click', function(e) {
            if (!e.target.closest('.action-cell') && !e.target.closest('button')) {
                const id = parseInt(this.dataset.id);
                openEditLitigationModal(id);
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
            const presuit = this.dataset.presuit;

            if (action === 'settle-litigation') {
                openSettleLitigationModal(parseInt(id), caseNum, client, parseFloat(presuit));
            }
        });
    });

    // Update footer
    const active = cases.filter(c => c.status === 'active' || c.status === 'in_progress').length;
    const settled = cases.filter(c => c.status === 'settled' || c.status === 'closed').length;
    document.getElementById('litFooterLeft').textContent = `${cases.length} litigation cases`;
    document.getElementById('litFooterRight').textContent = `Active: ${active} · Settled: ${settled}`;
}

async function deleteLitigationCase(caseId) {
    if (!confirm('Are you sure you want to delete this case?')) return;

    const result = await apiCall(`api/chong_cases.php?id=${caseId}`, 'DELETE', { csrf_token: csrfToken });
    if (result.success) {
        showToast('Case deleted', 'success');
        loadLitigationCases();
        loadDashboardStats();
    } else {
        showToast(result.error || 'Failed to delete', 'error');
    }
}

function openAddLitigationModal() {
    document.getElementById('newDemandForm').reset();
    document.getElementById('newDemandPhase').value = 'litigation';
    document.querySelector('#newDemandModal .modal-header h2').textContent = 'Add New Litigation Case';

    const today = new Date().toISOString().split('T')[0];
    document.getElementById('newDemandAssignedDate').value = today;

    document.getElementById('newDemandModal').classList.remove('hidden');
}

function openEditLitigationModal(caseId) {
    const c = litigationCasesData.find(item => item.id == caseId);
    if (!c) return;

    document.getElementById('editCaseId').value = c.id;
    document.getElementById('editCaseNumber').value = c.case_number || '';
    document.getElementById('editClientName').value = c.client_name || '';
    document.getElementById('editPhase').value = 'litigation';
    document.getElementById('editMonth').value = c.month || '';
    document.getElementById('editSettled').value = c.settled || '';
    document.getElementById('editDiscLegalFee').value = c.discounted_legal_fee || '';
    document.getElementById('editPresuitOffer').value = c.presuit_offer || '';
    document.getElementById('editResolutionType').value = c.resolution_type || '';

    toggleEditPhaseFields();
    calculateEditCommission();

    document.getElementById('editCaseModal').classList.remove('hidden');
}

// Litigation filter/sort functions
function setLitigationFilter(filter, btn) {
    currentLitigationFilter = filter;
    document.querySelectorAll('#content-litigation .f-chip').forEach(chip => {
        chip.classList.remove('active');
    });
    btn.classList.add('active');
    filterLitigationCases();
}

function filterLitigationCases() {
    const search = document.getElementById('litigationSearch').value.toLowerCase();
    let filtered = litigationCasesData.filter(c =>
        c.case_number.toLowerCase().includes(search) ||
        c.client_name.toLowerCase().includes(search)
    );

    if (currentLitigationFilter === 'active') {
        filtered = filtered.filter(c => c.status === 'active' || c.status === 'in_progress');
    } else if (currentLitigationFilter === 'settled') {
        filtered = filtered.filter(c => c.status === 'settled' || c.status === 'closed');
    }

    renderLitigationTable(filtered);
}

function sortLitigationCases(column) {
    if (litigationSortColumn === column) {
        litigationSortDir = litigationSortDir === 'asc' ? 'desc' : 'asc';
    } else {
        litigationSortColumn = column;
        litigationSortDir = 'asc';
    }

    document.querySelectorAll('#litigationTable th.sortable').forEach(th => {
        th.classList.remove('asc', 'desc');
        if (th.dataset.sort === column) {
            th.classList.add(litigationSortDir);
        }
    });

    litigationCasesData.sort((a, b) => {
        let valA = a[column];
        let valB = b[column];

        if (valA == null) valA = '';
        if (valB == null) valB = '';

        if (['presuit_offer', 'litigation_duration_days'].includes(column)) {
            valA = parseFloat(valA) || 0;
            valB = parseFloat(valB) || 0;
        } else {
            valA = String(valA).toLowerCase();
            valB = String(valB).toLowerCase();
        }

        if (valA < valB) return litigationSortDir === 'asc' ? -1 : 1;
        if (valA > valB) return litigationSortDir === 'asc' ? 1 : -1;
        return 0;
    });

    filterLitigationCases();
}

// Fee rate override state
let defaultFeeRateForResolution = null;

// Litigation settle modal
function openSettleLitigationModal(caseId, caseNumber, clientName, presuitOffer) {
    const form = document.getElementById('settleLitigationForm');
    form.reset();
    form.querySelector('[name="case_id"]').value = caseId;
    form.querySelector('[name="presuit_offer"]').value = presuitOffer;
    form.querySelector('[name="presuit_offer_hidden"]').value = presuitOffer;
    form.querySelector('[name="discounted_legal_fee"]').dataset.userModified = '';
    document.getElementById('settleLitCaseInfo').textContent = `${caseNumber} - ${clientName}`;
    document.getElementById('resolutionInfo').style.display = 'none';
    document.getElementById('variableFields').style.display = 'none';

    // Reset fee rate override state
    defaultFeeRateForResolution = null;
    document.getElementById('feeRateOverrideTag').style.display = 'none';
    updateNoteRequirement(false);

    openModal('settleLitigationModal');
}

function onResolutionTypeChange() {
    const form = document.getElementById('settleLitigationForm');
    const resType = form.resolution_type.value;
    const config = resolutionConfig[resType];

    if (config && !config.variable) {
        document.getElementById('resolutionInfo').style.display = 'block';
        document.getElementById('variableFields').style.display = 'none';

        // Set fee rate dropdown to default for this resolution type
        const feeRateSelect = document.getElementById('feeRateSelect');
        feeRateSelect.value = config.feeRate;
        defaultFeeRateForResolution = config.feeRate;

        // Clear override state
        document.getElementById('feeRateOverrideTag').style.display = 'none';
        updateNoteRequirement(false);

        document.getElementById('infoCommRate').textContent = config.commRate + '%';
    } else if (config && config.variable) {
        document.getElementById('resolutionInfo').style.display = 'none';
        document.getElementById('variableFields').style.display = 'flex';
        defaultFeeRateForResolution = null;
    } else {
        document.getElementById('resolutionInfo').style.display = 'none';
        document.getElementById('variableFields').style.display = 'none';
        defaultFeeRateForResolution = null;
    }

    // Reset disc legal fee auto-calc when resolution type changes
    form.querySelector('[name="discounted_legal_fee"]').dataset.userModified = '';
    calculateLitCommission();
}

function onFeeRateChange() {
    const currentRate = parseFloat(document.getElementById('feeRateSelect').value);
    const isOverridden = defaultFeeRateForResolution !== null && currentRate !== defaultFeeRateForResolution;

    document.getElementById('feeRateOverrideTag').style.display = isOverridden ? 'inline' : 'none';
    updateNoteRequirement(isOverridden);

    // Reset disc legal fee auto-calc so it recalculates with new fee rate
    const form = document.getElementById('settleLitigationForm');
    form.querySelector('[name="discounted_legal_fee"]').dataset.userModified = '';

    calculateLitCommission();
}

function updateNoteRequirement(required) {
    const noteLabel = document.getElementById('litNoteLabel');
    const noteInput = document.getElementById('litNote');
    const noteRequired = document.getElementById('litNoteRequired');

    if (required) {
        noteLabel.innerHTML = 'Note <span style="color:#dc2626;">*</span>';
        noteInput.style.borderColor = '#dc2626';
        noteRequired.style.display = 'block';
    } else {
        noteLabel.textContent = 'Note';
        noteInput.style.borderColor = '';
        noteRequired.style.display = 'none';
    }
}

function calculateLitCommission() {
    const form = document.getElementById('settleLitigationForm');
    const resType = form.resolution_type.value;
    const config = resolutionConfig[resType];
    if (!config) return;

    const settled = parseFloat(form.settled.value) || 0;
    const presuitOffer = parseFloat(form.presuit_offer.value) || 0;

    let difference, legalFee, commission;

    if (config.variable) {
        const manualFeeRate = parseFloat(form.manual_fee_rate.value) || 0;
        difference = settled - presuitOffer;
        legalFee = settled * (manualFeeRate / 100);
    } else {
        // Use the fee rate from the dropdown (allows override)
        const selectedFeeRate = parseFloat(document.getElementById('feeRateSelect').value);
        difference = settled - presuitOffer;

        // Use resolution type's deduction behavior (deductPresuit)
        // Presuit deducted group: base = difference, Non-deducted group: base = settled
        const base = config.deductPresuit ? difference : settled;

        if (selectedFeeRate === 40) {
            legalFee = base * 0.40;
        } else {
            legalFee = base / 3;
        }
    }

    if (!form.discounted_legal_fee.dataset.userModified) {
        form.discounted_legal_fee.value = legalFee.toFixed(2);
    }

    const discLegalFee = parseFloat(form.discounted_legal_fee.value) || 0;

    if (config.variable) {
        const manualCommRate = parseFloat(form.manual_commission_rate.value) || 0;
        commission = discLegalFee * (manualCommRate / 100);
    } else {
        commission = discLegalFee * 0.20;
    }

    form.difference_display.value = formatCurrency(difference);
    form.legal_fee_display.value = formatCurrency(legalFee);
    form.commission_display.value = formatCurrency(commission);
}

async function submitSettleLitigation(event) {
    event.preventDefault();
    const form = event.target;

    // Check fee rate override → note required
    const feeRateSelect = document.getElementById('feeRateSelect');
    const isOverridden = defaultFeeRateForResolution !== null &&
        parseFloat(feeRateSelect.value) !== defaultFeeRateForResolution;

    if (isOverridden && !form.note.value.trim()) {
        showToast('Fee rate가 변경되었습니다. Note를 입력해주세요.', 'error');
        document.getElementById('litNote').focus();
        return;
    }

    const caseId = form.case_id.value;
    const data = {
        resolution_type: form.resolution_type.value,
        settled: parseFloat(form.settled.value),
        presuit_offer: parseFloat(form.presuit_offer_hidden.value),
        discounted_legal_fee: parseFloat(form.discounted_legal_fee.value),
        manual_fee_rate: parseFloat(form.manual_fee_rate?.value) || 0,
        manual_commission_rate: parseFloat(form.manual_commission_rate?.value) || 0,
        month: form.month.value,
        check_received: form.check_received.checked,
        note: form.note.value,
        is_policy_limit: form.is_policy_limit.checked
    };

    // If fee rate overridden, send the override value
    if (isOverridden) {
        data.fee_rate_override = parseFloat(feeRateSelect.value);
    }

    const result = await apiCall(`api/chong_cases.php?id=${caseId}&action=settle_litigation`, 'PUT', data);
    if (result.success) {
        closeModal('settleLitigationModal');
        loadDashboard();
        loadLitigationCases();
        if (result.is_policy_limit) loadUimCases();
        loadCommissions();
        alert(result.is_policy_limit
            ? `Settled! Commission: ${formatCurrency(result.commission)}. Case moved to UIM.`
            : `Case settled! Commission: ${formatCurrency(result.commission)}`);
    } else {
        alert('Error: ' + (result.error || 'Failed to settle case'));
    }
}
