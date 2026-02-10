/**
 * ChongDashboard - Demand tab functions.
 */

// ============================================
// Demand Requests (Accept/Deny from Jimi/Admin)
// ============================================

async function loadDemandRequests() {
    try {
        const result = await apiCall('api/demand_requests.php?status=all');
        allDemandRequests = result.requests || [];
        pendingDemandRequests = allDemandRequests.filter(r => r.status === 'pending');

        // Update sidebar badge
        const badge = document.getElementById('demandBadge');
        if (pendingDemandRequests.length > 0) {
            badge.textContent = pendingDemandRequests.length;
            badge.style.display = 'inline';
        } else {
            badge.style.display = 'none';
        }

        renderPendingDemandRequests();
    } catch (err) {
        console.error('Error loading demand requests:', err);
    }
}

function renderPendingDemandRequests() {
    const section = document.getElementById('pendingDemandRequestsSection');
    const container = document.getElementById('pendingDemandRequestsCards');

    if (!pendingDemandRequests.length) {
        section.style.display = 'none';
        return;
    }

    section.style.display = 'block';
    const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '\u2014';

    container.innerHTML = pendingDemandRequests.map(r => {
        const reqName = r.requester_name || '\u2014';
        return `
            <div class="tv3-pending-card">
                <div class="tv3-pending-card-grid">
                    <div>
                        <div class="tv3-pending-label">Requester</div>
                        <div class="tv3-pending-val">${escapeHtml(reqName)}</div>
                    </div>
                    <div>
                        <div class="tv3-pending-label">Client</div>
                        <div class="tv3-pending-val">${escapeHtml(r.client_name)}</div>
                    </div>
                    <div>
                        <div class="tv3-pending-label">Case #</div>
                        <div class="tv3-pending-val dim">${escapeHtml(r.case_number || '\u2014')}</div>
                    </div>
                    <div>
                        <div class="tv3-pending-label">Case Type</div>
                        <div class="tv3-pending-val dim">${escapeHtml(r.case_type || 'Auto')}</div>
                    </div>
                    <div>
                        <div class="tv3-pending-label">Submitted</div>
                        <div class="tv3-pending-val dim">${fmtDate(r.created_at)}</div>
                    </div>
                    <div>
                        <div class="tv3-pending-label">Note</div>
                        <div class="tv3-pending-val dim" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${escapeHtml(r.note || '')}">${escapeHtml(r.note || '\u2014')}</div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button class="tv3-btn-accept" onclick="acceptDemandRequest(${r.id})">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        Accept
                    </button>
                    <button class="tv3-btn-deny" onclick="denyDemandRequest(${r.id}, '${escapeJs(r.client_name)}')">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        Deny
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

async function acceptDemandRequest(id) {
    try {
        const result = await apiCall('api/demand_requests.php', 'PUT', { id, action: 'accept' });
        if (result.success) {
            showToast('Demand request accepted', 'success');
            loadDemandRequests();
            loadDemandCases();
            loadDashboard();
        } else {
            alert(result.error || 'Error accepting request');
        }
    } catch (err) {
        console.error('Error accepting demand request:', err);
        alert('Error accepting request');
    }
}

async function denyDemandRequest(id, clientName) {
    const reason = prompt(`Reason for denying "${clientName}":`);
    if (reason === null) return;
    if (!reason.trim()) {
        alert('Deny reason is required');
        return;
    }
    try {
        const result = await apiCall('api/demand_requests.php', 'PUT', { id, action: 'deny', deny_reason: reason.trim() });
        if (result.success) {
            showToast('Demand request denied', 'success');
            loadDemandRequests();
        } else {
            alert(result.error || 'Error denying request');
        }
    } catch (err) {
        console.error('Error denying demand request:', err);
        alert('Error denying request');
    }
}

// ============================================
// Demand Cases
// ============================================

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
        if (c.top_offer_date) return false;
        if (!c.deadline_status || c.deadline_status.days === null) return false;
        return c.deadline_status.days >= 0 && c.deadline_status.days <= 14;
    }).length;

    const overdue = cases.filter(c => {
        if (c.top_offer_date) return false;
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
        tbody.innerHTML = '<tr><td colspan="13" style="text-align:center; padding: 40px; color:#8b8fa3;">No demand cases</td></tr>';
        return;
    }

    tbody.innerHTML = cases.map(c => {
        const deadlineStatus = c.deadline_status || {};
        let daysClass = deadlineStatus.class || '';
        let daysText = deadlineStatus.message || '-';

        // Determine row highlight class based on days left
        let rowClass = '';
        if (c.top_offer_date) {
            // Top offer received - show completed badge, no highlight
            daysText = '<span class="ink-badge" style="background:#d1fae5;color:#059669;">Completed</span>';
            daysClass = '';
        } else if (deadlineStatus.days !== undefined && deadlineStatus.days !== null) {
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

        // Inline checkbox cells for Demand Out and Negotiate
        const demandOutCell = c.demand_out_date
            ? `<td class="date-check" onclick="toggleDemandDate(event,${c.id},'demand_out_date')" style="cursor:pointer;white-space:nowrap;"><span style="color:#059669;font-weight:600;">&#10003;</span> <span style="font-size:13px;color:#3d3f4e;">${formatDateShort(c.demand_out_date)}</span></td>`
            : `<td class="date-check" onclick="toggleDemandDate(event,${c.id},'demand_out_date')" style="cursor:pointer;text-align:center;"><span style="color:#c4c7d0;font-size:15px;">&#9744;</span></td>`;

        const negotiateCell = c.negotiate_date
            ? `<td class="date-check" onclick="toggleDemandDate(event,${c.id},'negotiate_date')" style="cursor:pointer;white-space:nowrap;"><span style="color:#059669;font-weight:600;">&#10003;</span> <span style="font-size:13px;color:#3d3f4e;">${formatDateShort(c.negotiate_date)}</span></td>`
            : `<td class="date-check" onclick="toggleDemandDate(event,${c.id},'negotiate_date')" style="cursor:pointer;text-align:center;"><span style="color:#c4c7d0;font-size:15px;">&#9744;</span></td>`;

        // Top Offer cell
        const topOfferCell = c.top_offer_date
            ? `<td style="white-space:nowrap;"><span style="color:#059669;font-weight:600;">&#10003;</span> <span style="font-size:13px;color:#3d3f4e;">${formatDateShort(c.top_offer_date)}</span></td>`
            : `<td style="text-align:center;color:#c4c7d0;">-</td>`;

        // Top button only shown if no top offer yet
        const topBtn = !c.top_offer_date
            ? `<button class="act-btn top-offer" data-action="top-offer" data-id="${c.id}" data-case="${escapeHtml(c.case_number)}" data-client="${escapeHtml(c.client_name)}">Top</button>`
            : '';

        return `
            <tr class="${rowClass} ${isSelected} clickable-row" data-id="${c.id}" data-stage="${c.stage || ''}" style="cursor: pointer;">
                <td style="width:0;padding:0;border:none;"></td>
                <td style="font-family: monospace; font-size: 12px;">${escapeHtml(c.case_number)}</td>
                <td style="font-weight: 500;">${escapeHtml(c.client_name)}</td>
                <td>${escapeHtml(c.case_type || '-')}</td>
                <td>${stageBadge}</td>
                <td>${formatDate(c.assigned_date)}</td>
                ${demandOutCell}
                ${negotiateCell}
                ${topOfferCell}
                <td>${formatDate(c.demand_deadline)}</td>
                <td class="${daysClass}">${daysText}</td>
                <td>${statusBadge}</td>
                <td class="action-cell" style="text-align: center;">
                    <div class="action-group center">
                        ${topBtn}
                        <button class="act-btn settle" data-action="settle-demand" data-id="${c.id}" data-case="${escapeHtml(c.case_number)}" data-client="${escapeHtml(c.client_name)}">Settle</button>
                        <button class="act-btn to-lit" data-action="to-litigation" data-id="${c.id}" data-case="${escapeHtml(c.case_number)}" data-client="${escapeHtml(c.client_name)}">To Lit</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    // Add row click event - open edit modal
    tbody.querySelectorAll('.clickable-row').forEach(row => {
        row.addEventListener('click', function(e) {
            if (!e.target.closest('.action-cell') && !e.target.closest('button') && !e.target.closest('.date-check')) {
                const id = parseInt(this.dataset.id);
                openEditCaseModal(id);
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
            } else if (action === 'top-offer') {
                openTopOfferModal(id, caseNum, client);
            } else if (action === 'to-litigation') {
                openToLitigationModal(id, caseNum, client);
            }
        });
    });

    // Update footer using deadline_status from API (exclude completed top offer cases)
    const dueIn2Weeks = cases.filter(c => {
        if (c.top_offer_date) return false;
        if (!c.deadline_status || c.deadline_status.days === null) return false;
        return c.deadline_status.days >= 0 && c.deadline_status.days <= 14;
    }).length;
    const overdue = cases.filter(c => {
        if (c.top_offer_date) return false;
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

    const overdue = cases.filter(c => !c.top_offer_date && c.deadline_status && c.deadline_status.days < 0).length;
    const critical = cases.filter(c => !c.top_offer_date && c.deadline_status && c.deadline_status.days >= 0 && c.deadline_status.days <= 14).length;

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

    const dateColumns = ['assigned_date', 'demand_out_date', 'negotiate_date', 'top_offer_date', 'demand_deadline'];

    demandCasesData.sort((a, b) => {
        let valA = a[column];
        let valB = b[column];

        if (valA == null) valA = '';
        if (valB == null) valB = '';

        if (column === 'days_left') {
            valA = a.demand_deadline ? Math.ceil((new Date(a.demand_deadline) - new Date()) / (1000 * 60 * 60 * 24)) : 999;
            valB = b.demand_deadline ? Math.ceil((new Date(b.demand_deadline) - new Date()) / (1000 * 60 * 60 * 24)) : 999;
            valA = parseFloat(valA) || 0;
            valB = parseFloat(valB) || 0;
        } else if (dateColumns.includes(column)) {
            valA = valA ? new Date(valA + 'T00:00:00').getTime() : 0;
            valB = valB ? new Date(valB + 'T00:00:00').getTime() : 0;
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

// Inline date checkbox toggle for Demand Out / Negotiate columns
async function toggleDemandDate(event, caseId, field) {
    event.stopPropagation();
    const c = demandCasesData.find(x => x.id == caseId);
    if (!c) return;

    const currentVal = c[field];
    const newDate = currentVal ? null : new Date().toISOString().split('T')[0];
    const fieldLabel = field === 'demand_out_date' ? 'Demand Out' : 'Negotiate';
    const action = newDate ? `Mark "${fieldLabel}" as today?` : `Clear "${fieldLabel}" date?`;
    if (!confirm(action)) return;

    const result = await apiCall(`api/chong_cases.php?id=${caseId}`, 'PUT', {
        action: 'stage_date_toggle',
        field: field,
        date: newDate,
        csrf_token: csrfToken
    });

    if (result.success) {
        c[field] = newDate;
        if (newDate) {
            const stageMap = { demand_out_date: 'demand_sent', negotiate_date: 'negotiate' };
            c.stage = stageMap[field];
        }
        renderDemandTable(demandCasesData);
    } else {
        showToast(result.error || 'Failed to update', 'error');
    }
}

function formatDateShort(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' });
}

// Top Offer modal functions
let cachedUsers = null;

async function openTopOfferModal(caseId, caseNumber, clientName) {
    document.getElementById('topOfferCaseId').value = caseId;
    document.getElementById('topOfferCaseInfo').textContent = `${caseNumber} - ${clientName}`;
    document.getElementById('topOfferAmount').value = '';
    document.getElementById('topOfferNote').value = '';

    // Load users for assignee dropdown (cache after first load)
    const select = document.getElementById('topOfferAssignee');
    if (!cachedUsers) {
        const result = await apiCall('api/users.php');
        if (result.users) {
            cachedUsers = result.users.filter(u => u.is_active !== 0);
        }
    }
    if (cachedUsers) {
        select.innerHTML = '<option value="">Select Employee...</option>';
        cachedUsers.forEach(u => {
            select.innerHTML += `<option value="${u.id}">${escapeHtml(u.display_name)}</option>`;
        });
    }

    openModal('topOfferModal');
}

async function submitTopOffer(event) {
    event.preventDefault();
    const caseId = document.getElementById('topOfferCaseId').value;
    const amount = parseFloat(document.getElementById('topOfferAmount').value);
    const assigneeId = document.getElementById('topOfferAssignee').value;
    const note = document.getElementById('topOfferNote').value;

    if (!amount || amount <= 0) {
        alert('Please enter a valid top offer amount.');
        return;
    }
    if (!assigneeId) {
        alert('Please select an employee to assign.');
        return;
    }

    const result = await apiCall(`api/chong_cases.php?id=${caseId}`, 'PUT', {
        action: 'submit_top_offer',
        top_offer_amount: amount,
        assignee_id: parseInt(assigneeId),
        note: note.trim(),
        csrf_token: csrfToken
    });

    if (result.success) {
        closeModal('topOfferModal');
        // Update local data
        const c = demandCasesData.find(x => x.id == caseId);
        if (c) {
            c.top_offer_amount = amount;
            c.top_offer_date = result.top_offer_date;
            c.top_offer_assignee_id = parseInt(assigneeId);
            c.top_offer_note = note.trim();
            c.deadline_status = { class: 'deadline-complete', message: 'Completed', days: null, urgent: false };
        }
        renderDemandTable(demandCasesData);
        updateDemandStats(demandCasesData);
        updateDemandAlertBar(demandCasesData);
        showToast('Top offer submitted successfully!', 'success');
    } else {
        alert('Error: ' + (result.error || 'Failed to submit top offer'));
    }
}
