/**
 * Employee dashboard - Cases tab functions.
 */

function initMonthDropdowns() {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth();

    // Case form dropdowns (separate Year and Month)
    const caseYear = document.getElementById('caseYear');
    const caseMonth = document.getElementById('caseMonth');

    // Year options (2021 ~ current year + 2 years)
    for (let y = 2021; y <= currentYear + 2; y++) {
        caseYear.innerHTML += `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`;
    }

    // Month options
    months.forEach((m, i) => {
        caseMonth.innerHTML += `<option value="${m}" ${i === currentMonth ? 'selected' : ''}>${m}</option>`;
    });
    caseMonth.innerHTML += `<option value="TBD">TBD</option>`;

    // Filter dropdown - month abbreviation
    const filterMonth = document.getElementById('filterMonth');
    filterMonth.innerHTML = '<option value="all">All Month</option>';
    months.forEach(m => {
        filterMonth.innerHTML += `<option value="${m}">${m}</option>`;
    });
}

function initYearFilter() {
    const filterYear = document.getElementById('filterYear');
    const currentYear = new Date().getFullYear();

    // Get unique years from cases data
    const years = [...new Set(allCases.map(c => {
        const match = (c.month || '').match(/\d{4}/);
        return match ? parseInt(match[0]) : null;
    }).filter(Boolean))];

    // Always include current year
    if (!years.includes(currentYear)) {
        years.push(currentYear);
    }

    // Sort descending (newest first)
    years.sort((a, b) => b - a);

    filterYear.innerHTML = '<option value="all">All</option>';
    years.forEach(y => {
        filterYear.innerHTML += `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`;
    });
}


async function loadCases() {
    try {
        const data = await apiCall('api/cases.php');
        allCases = data.cases || [];
        initYearFilter();
        renderCases();
        updateStats();
    } catch (err) {
        console.error('Error loading cases:', err);
    }
}

function sortCases(column) {
    // Toggle sort direction if clicking the same column
    if (sortColumn === column) {
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn = column;
        sortDirection = 'asc';
    }

    // Update sort arrow indicators
    document.querySelectorAll('#casesTable .sort-arrow').forEach(arrow => {
        arrow.className = 'sort-arrow';
    });

    // Find the clicked header's arrow
    const clickedHeader = event.target.closest('th');
    if (clickedHeader) {
        const arrow = clickedHeader.querySelector('.sort-arrow');
        if (arrow) {
            arrow.className = `sort-arrow ${sortDirection}`;
        }
    }

    renderCases();
}

function setStatusFilter(status, btn) {
    currentStatusFilter = status;
    document.querySelectorAll('#content-cases .f-chip[id^="statusChip-"]').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    renderCases();
}

function renderCases() {
    const year = document.getElementById('filterYear').value;
    const monthFilter = document.getElementById('filterMonth').value;
    const search = document.getElementById('searchInput').value.toLowerCase();

    // Only show active cases (exclude paid and rejected — those go to History)
    let filtered = allCases.filter(c => c.status === 'in_progress' || c.status === 'unpaid');

    // Status filter from chips
    if (currentStatusFilter !== 'all') {
        filtered = filtered.filter(c => c.status === currentStatusFilter);
    }

    // Year filter (always show TBD cases - unsettled)
    if (year !== 'all') {
        filtered = filtered.filter(c => c.month === 'TBD' || (c.month || '').includes(year));
    }

    // Month filter (always show TBD cases)
    if (monthFilter !== 'all') {
        filtered = filtered.filter(c => c.month === 'TBD' || (c.month && c.month.startsWith(monthFilter)));
    }

    // Search
    if (search) {
        filtered = filtered.filter(c =>
            (c.client_name || '').toLowerCase().includes(search) ||
            (c.case_number || '').toLowerCase().includes(search) ||
            (c.resolution_type || '').toLowerCase().includes(search)
        );
    }

    // Apply sorting
    if (sortColumn) {
        filtered.sort((a, b) => {
            let aVal = a[sortColumn];
            let bVal = b[sortColumn];

            if (aVal == null) aVal = '';
            if (bVal == null) bVal = '';

            if (sortColumn === 'intake_date') {
                aVal = aVal || '';
                bVal = bVal || '';
            } else if (['settled', 'presuit_offer', 'difference', 'legal_fee', 'discounted_legal_fee', 'commission'].includes(sortColumn)) {
                aVal = parseFloat(aVal) || 0;
                bVal = parseFloat(bVal) || 0;
            } else {
                aVal = String(aVal).toLowerCase();
                bVal = String(bVal).toLowerCase();
            }

            if (aVal < bVal) return sortDirection === 'asc' ? -1 : 1;
            if (aVal > bVal) return sortDirection === 'asc' ? 1 : -1;
            return 0;
        });
    }

    const tbody = document.getElementById('casesBody');

    // Calculate stats for filtered data
    const totalComm = filtered.reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);
    const pendingComm = filtered.filter(c => c.status === 'in_progress').reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);
    const unpaidComm = filtered.filter(c => c.status === 'unpaid').reduce((sum, c) => sum + parseFloat(c.commission || 0), 0);

    // Update stats cards
    document.getElementById('totalCases').textContent = filtered.length;
    document.getElementById('totalCommission').textContent = formatCurrency(totalComm);
    document.getElementById('pendingCommission').textContent = formatCurrency(pendingComm);
    document.getElementById('unpaidCommission').textContent = formatCurrency(unpaidComm);

    // Update footer
    document.getElementById('footerInfo').textContent = `${filtered.length} cases`;
    document.getElementById('footerTotal').textContent = formatCurrency(totalComm);
    document.getElementById('footerPending').textContent = formatCurrency(pendingComm);
    document.getElementById('footerUnpaid').textContent = formatCurrency(unpaidComm);

    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="13" style="text-align:center; padding: 40px; color: #8b8fa3;">No cases found</td></tr>';
        return;
    }

    tbody.innerHTML = filtered.map(c => {
        const statusBadge = c.status === 'in_progress'
            ? '<span class="stat-badge in_progress">IN PROGRESS</span>'
            : c.status === 'unpaid'
            ? '<span class="stat-badge unpaid">UNPAID</span>'
            : c.status === 'paid'
            ? '<span class="stat-badge paid">PAID</span>'
            : '<span class="stat-badge" style="background:#fee2e2;color:#991b1b;">REJECTED</span>';

        const settled = parseFloat(c.settled || 0);
        const presuitOffer = parseFloat(c.presuit_offer || 0);
        const difference = parseFloat(c.difference || 0);
        const legalFee = parseFloat(c.legal_fee || 0);
        const discFee = parseFloat(c.discounted_legal_fee || 0);
        const commission = parseFloat(c.commission || 0);

        // Resolution type color dot
        const resType = c.resolution_type || '-';
        let dotColor = '#94a3b8';
        if (resType.toLowerCase().includes('demand')) dotColor = '#3b82f6';
        else if (resType.toLowerCase().includes('mediation')) dotColor = '#d97706';
        else if (resType.toLowerCase().includes('arb')) dotColor = '#8b5cf6';
        else if (resType.toLowerCase().includes('trial')) dotColor = '#dc2626';

        const canEdit = c.status === 'in_progress' || c.status === 'unpaid';

        return `
            <tr onclick="viewCaseDetail(${c.id})" style="cursor:pointer;${c.check_received == 1 ? 'background:#d1fae5;' : ''}">
                <td style="width:0;padding:0;border:none;"></td>
                <td class="c">${statusBadge}</td>
                <td>${c.intake_date ? new Date(c.intake_date + 'T00:00:00').toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: '2-digit'}) : '\u2014'}</td>
                <td>${escapeHtml(c.case_number || '-')}</td>
                <td>${escapeHtml(c.client_name)}</td>
                <td><span style="display:inline-block;width:8px;height:8px;background:${dotColor};border-radius:50%;margin-right:6px;"></span>${escapeHtml(resType)}</td>
                <td class="r" style="font-weight:600;">${formatCurrency(settled)}</td>
                <td class="r" style="color:#8b8fa3;">${presuitOffer > 0 ? formatCurrency(presuitOffer) : '\u2014'}</td>
                <td class="r">${difference > 0 ? formatCurrency(difference) : '\u2014'}</td>
                <td class="r">${formatCurrency(legalFee)}</td>
                <td class="r">${formatCurrency(discFee)}</td>
                <td class="r" style="font-weight:700; color:#0d9488;">${formatCurrency(commission)}</td>
                <td>${escapeHtml(c.month || '-')}</td>
                <td class="c">
                    <div style="display:flex; gap:4px; justify-content:center;">
                        ${canEdit ? `<button class="act-link" onclick="event.stopPropagation(); editCase(${c.id})" title="Edit" style="padding:4px 6px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>` : ''}
                        ${canEdit ? `<button class="act-link danger" onclick="event.stopPropagation(); deleteCase(${c.id})" title="Delete" style="padding:4px 6px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>` : ''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function updateStats() {
    // Stats are now updated inside renderCases()
}

function filterTable() {
    renderCases();
}

function showAddForm() {
    document.getElementById('modalTitle').textContent = 'Add New Case';
    document.getElementById('caseForm').reset();
    document.getElementById('caseForm').dataset.mode = 'create';
    document.getElementById('caseId').value = '';

    // Set intake date to today
    document.getElementById('intakeDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('intakeDate').removeAttribute('readonly');
    document.getElementById('intakeDateSection').style.display = '';

    // Hide settlement section in create mode
    document.getElementById('settlementSection').style.display = 'none';

    document.getElementById('caseModal').classList.add('show');
}

function closeModal() {
    document.getElementById('caseModal').classList.remove('show');
}

function viewCaseDetail(id) {
    const c = allCases.find(x => x.id == id);
    if (!c) return;

    const statusBadge = {
        'in_progress': '<span style="display: inline-flex; align-items: center; padding: 4px 10px; background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 4px; font-size: 11px; font-weight: 600;"><span style="width: 6px; height: 6px; background: #1e40af; border-radius: 50%; margin-right: 6px;"></span>In Progress</span>',
        'unpaid': '<span style="display: inline-flex; align-items: center; padding: 4px 10px; background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; border-radius: 4px; font-size: 11px; font-weight: 600;"><span style="width: 6px; height: 6px; background: #92400e; border-radius: 50%; margin-right: 6px;"></span>Unpaid</span>',
        'paid': '<span style="display: inline-flex; align-items: center; padding: 4px 10px; background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; border-radius: 4px; font-size: 11px; font-weight: 600;"><span style="width: 6px; height: 6px; background: #065f46; border-radius: 50%; margin-right: 6px;"></span>Paid</span>',
        'rejected': '<span style="display: inline-flex; align-items: center; padding: 4px 10px; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; border-radius: 4px; font-size: 11px; font-weight: 600;"><span style="width: 6px; height: 6px; background: #991b1b; border-radius: 50%; margin-right: 6px;"></span>Rejected</span>'
    };

    const content = document.getElementById('caseDetailContent');
    content.innerHTML = `
        <!-- Case Information Section -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
            <div>
                <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Client Name</div>
                <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; font-weight: 500;">${c.client_name}</div>
            </div>
            <div>
                <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Case Number</div>
                <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; font-weight: 500;">${c.case_number}</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
            <div>
                <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Case Type</div>
                <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${c.case_type || '-'}</div>
            </div>
            <div>
                <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Resolution</div>
                <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${c.resolution_type || '-'}</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
            <div>
                <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Intake Date</div>
                <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${c.intake_date ? new Date(c.intake_date + 'T00:00:00').toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'}) : '-'}</div>
            </div>
            <div>
                <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Settlement Month</div>
                <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${c.month}</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 14px;">
            <div>
                <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Fee Rate</div>
                <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${c.fee_rate || '-'}%</div>
            </div>
            <div>
                <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Status</div>
                <div style="padding: 6px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">${statusBadge[c.status] || c.status}</div>
            </div>
            <div></div>
        </div>

        <!-- Financial Details Section -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                <div style="width: 26px; height: 26px; background: #0f172a; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px;">&#x1F4B0;</div>
                <span style="font-size: 12px; font-weight: 600; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">Financial Details</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div>
                    <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Settled Amount</div>
                    <div style="padding: 8px 12px; background: white; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; font-weight: 600;">${formatCurrency(c.settled)}</div>
                </div>
                <div>
                    <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Pre-Suit Offer</div>
                    <div style="padding: 8px 12px; background: white; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${formatCurrency(c.presuit_offer || 0)}</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div>
                    <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Difference</div>
                    <div style="padding: 8px 12px; background: white; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 13px; color: #64748b;">${formatCurrency(c.difference || 0)}</div>
                </div>
                <div>
                    <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Legal Fee</div>
                    <div style="padding: 8px 12px; background: white; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 13px; color: #64748b;">${formatCurrency(c.legal_fee || 0)}</div>
                </div>
                <div>
                    <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Disc. Legal Fee</div>
                    <div style="padding: 8px 12px; background: white; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; font-weight: 600;">${formatCurrency(c.discounted_legal_fee || 0)}</div>
                </div>
            </div>

            <!-- Commission Card -->
            <div style="background: #18181b; border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; position: relative; overflow: hidden;">
                <div style="position: absolute; right: 0; top: 0; bottom: 0; width: 80px; background: linear-gradient(135deg, rgba(34, 211, 238, 0.1), rgba(168, 85, 247, 0.1));"></div>
                <div>
                    <span style="font-size: 11px; font-weight: 500; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.5px;">Commission</span>
                    <div style="font-size: 11px; color: rgba(255,255,255,0.5); margin-top: 2px;">${c.check_received == 1 ? '\u2713 Check Received' : '\u23F3 Check Pending'}</div>
                </div>
                <span style="font-size: 22px; font-weight: 700; color: #22d3ee; position: relative; z-index: 1;">${formatCurrency(c.commission)}</span>
            </div>
        </div>

        <!-- Note -->
        ${c.note ? `
        <div>
            <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Note</div>
            <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${c.note}</div>
        </div>
        ` : ''}
    `;

    // Show Edit button only for in_progress or unpaid cases
    const editBtn = document.getElementById('editCaseDetailBtn');
    if (c.status === 'in_progress' || c.status === 'unpaid') {
        editBtn.style.display = 'inline-block';
        window.currentDetailCaseId = c.id;
    } else {
        editBtn.style.display = 'none';
        window.currentDetailCaseId = null;
    }

    document.getElementById('caseDetailModal').classList.add('show');
}

function editCaseFromDetail() {
    const caseId = window.currentDetailCaseId;
    if (!caseId) return;

    closeCaseDetail();
    editCase(caseId);
}

function calculateFees() {
    const settled = parseFloat(document.getElementById('settled').value) || 0;
    const presuit = parseFloat(document.getElementById('presuitOffer').value) || 0;
    const feeRate = parseFloat(document.getElementById('feeRate').value);

    const base = USER.uses_presuit_offer ? (settled - presuit) : settled;
    const difference = settled - presuit;
    const legalFee = feeRate === 33.33 ? (base / 3) : (base * 0.4);

    document.getElementById('difference').value = formatCurrency(difference);
    document.getElementById('legalFee').value = formatCurrency(legalFee);
    document.getElementById('discountedLegalFee').value = legalFee.toFixed(2);

    calculateCommission();
}

function calculateCommission() {
    const discLegalFee = parseFloat(document.getElementById('discountedLegalFee').value) || 0;
    const commission = discLegalFee * (USER.commission_rate / 100);
    document.getElementById('commission').textContent = formatCurrency(commission);
}

// Form submission
document.getElementById('caseForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const form = document.getElementById('caseForm');
    const mode = form.dataset.mode;
    const caseId = document.getElementById('caseId').value;

    let data;

    if (mode === 'create') {
        // Create mode: only send name, case#, intake_date
        data = {
            case_number: document.getElementById('caseNumber').value,
            client_name: document.getElementById('clientName').value,
            intake_date: document.getElementById('intakeDate').value
        };
    } else {
        // Edit mode: send all fields
        const caseMonthVal = document.getElementById('caseMonth').value;
        const caseYearVal = document.getElementById('caseYear').value;
        const monthValue = caseMonthVal === 'TBD' ? 'TBD' : `${caseMonthVal}. ${caseYearVal}`;
        data = {
            case_type: document.getElementById('caseType').value,
            resolution_type: document.getElementById('resolutionType').value,
            case_number: document.getElementById('caseNumber').value,
            client_name: document.getElementById('clientName').value,
            month: monthValue,
            fee_rate: parseFloat(document.getElementById('feeRate').value),
            settled: parseFloat(document.getElementById('settled').value) || 0,
            presuit_offer: parseFloat(document.getElementById('presuitOffer').value) || 0,
            discounted_legal_fee: parseFloat(document.getElementById('discountedLegalFee').value) || 0,
            note: document.getElementById('note').value,
            check_received: document.getElementById('checkReceived').checked
        };
    }

    if (caseId) {
        data.id = parseInt(caseId);
    }

    try {
        const result = await apiCall('api/cases.php', {
            method: caseId ? 'PUT' : 'POST',
            body: JSON.stringify(data)
        });

        if (result.success) {
            closeModal();
            loadCases();
        } else {
            alert(result.error || 'Error saving case');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error saving case');
    }
});

function editCase(id) {
    const c = allCases.find(x => x.id == id);
    if (!c) return;

    document.getElementById('modalTitle').textContent = 'Edit Case';
    document.getElementById('caseForm').dataset.mode = 'edit';
    document.getElementById('caseId').value = c.id;
    document.getElementById('caseType').value = c.case_type;
    document.getElementById('resolutionType').value = c.resolution_type;
    document.getElementById('caseNumber').value = c.case_number;
    document.getElementById('clientName').value = c.client_name;

    // Show intake date (readonly in edit mode)
    document.getElementById('intakeDate').value = c.intake_date || '';
    document.getElementById('intakeDate').setAttribute('readonly', true);
    document.getElementById('intakeDateSection').style.display = '';

    // Show settlement section in edit mode
    document.getElementById('settlementSection').style.display = '';

    // Parse month value (e.g., "Dec. 2025" or "TBD")
    if (c.month === 'TBD') {
        document.getElementById('caseMonth').value = 'TBD';
        document.getElementById('caseYear').value = new Date().getFullYear();
    } else {
        const parts = c.month.split('. ');
        document.getElementById('caseMonth').value = parts[0];
        document.getElementById('caseYear').value = parts[1];
    }
    document.getElementById('feeRate').value = c.fee_rate;
    document.getElementById('settled').value = c.settled;
    document.getElementById('presuitOffer').value = c.presuit_offer;
    document.getElementById('discountedLegalFee').value = c.discounted_legal_fee;
    document.getElementById('note').value = c.note || '';
    document.getElementById('checkReceived').checked = c.check_received == 1;

    calculateFees();

    document.getElementById('caseModal').classList.add('show');
}

async function deleteCase(id) {
    if (!confirm('Are you sure you want to delete this case?')) return;

    try {
        const result = await apiCall(`api/cases.php?id=${id}`, { method: 'DELETE' });

        if (result.success) {
            loadCases();
        } else {
            alert(result.error || 'Error deleting case');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error deleting case');
    }
}

function exportToExcel() {
    // Export currently filtered data
    const year = document.getElementById('filterYear').value;
    const monthFilter = document.getElementById('filterMonth').value;
    let filtered = allCases.filter(c => c.status === 'in_progress' || c.status === 'unpaid');

    if (currentStatusFilter !== 'all') {
        filtered = filtered.filter(c => c.status === currentStatusFilter);
    }
    if (year !== 'all') {
        filtered = filtered.filter(c => (c.month || '').includes(year));
    }
    if (monthFilter !== 'all') {
        filtered = filtered.filter(c => c.month && c.month.startsWith(monthFilter));
    }

    const data = filtered.map(c => ({
        'Resolution Type': c.resolution_type || '',
        'Client': c.client_name,
        'Intake Date': c.intake_date || '',
        'Settled': c.settled || 0,
        'Pre-Suit Offer': c.presuit_offer || 0,
        'Difference': c.difference || 0,
        'Legal Fee': c.legal_fee || 0,
        'Disc. Legal Fee': c.discounted_legal_fee || 0,
        'Commission': c.commission || 0,
        'Month': c.month || '',
        'Status': c.status
    }));

    if (data.length === 0) {
        alert('No data to export');
        return;
    }

    const ws = XLSX.utils.json_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'My Cases');
    XLSX.writeFile(wb, `my-cases-${new Date().toISOString().split('T')[0]}.xlsx`);
}

function closeCaseDetail() {
    document.getElementById('caseDetailModal').classList.remove('show');
}
