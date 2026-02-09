/**
 * Admin Dashboard - All Cases tab functions (includes case detail modal and edit case).
 */

async function loadAllCases() {
    const counselEl = document.getElementById('filterCounsel');
    const monthEl = document.getElementById('filterAllMonth');
    const statusEl = document.getElementById('filterAllStatus');

    let url = 'api/cases.php?';
    if (counselEl && counselEl.value !== 'all') url += `counsel=${counselEl.value}&`;
    if (monthEl && monthEl.value !== 'all') url += `month=${encodeURIComponent(monthEl.value)}&`;
    if (statusEl && statusEl.value !== 'all') url += `status=${statusEl.value}&`;

    try {
        const res = await fetch(url);
        const data = await res.json();
        allCases = data.cases || [];
        if (counselEl) renderAllCases();
    } catch (err) {
        console.error('Error:', err);
    }
}

function filterAllCases() {
    renderAllCases();
}

function sortAllCases(column) {
    if (allCasesSortColumn === column) {
        allCasesSortDirection = allCasesSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        allCasesSortColumn = column;
        // Month and numeric columns default to desc (newest/largest first)
        allCasesSortDirection = (column === 'month' || ['settled', 'presuit_offer', 'difference', 'legal_fee', 'discounted_legal_fee', 'commission', 'fee_rate'].includes(column)) ? 'desc' : 'asc';
    }

    document.querySelectorAll('#allCasesTable .sort-arrow').forEach(arrow => {
        arrow.className = 'sort-arrow';
    });

    const clickedHeader = event.target.closest('th');
    if (clickedHeader) {
        const arrow = clickedHeader.querySelector('.sort-arrow');
        if (arrow) {
            arrow.className = `sort-arrow ${allCasesSortDirection}`;
        }
    }

    renderAllCases();
}

function renderAllCases() {
    const search = document.getElementById('searchAll').value.toLowerCase();
    const monthFilter = document.getElementById('filterAllMonth').value;
    let filtered = allCases;

    if (search) {
        filtered = filtered.filter(c =>
            c.client_name.toLowerCase().includes(search) ||
            c.case_number.toLowerCase().includes(search) ||
            c.counsel_name.toLowerCase().includes(search)
        );
    }

    if (allCasesSortColumn) {
        filtered.sort((a, b) => {
            let aVal = a[allCasesSortColumn];
            let bVal = b[allCasesSortColumn];

            if (aVal == null) aVal = '';
            if (bVal == null) bVal = '';

            if (['settled', 'presuit_offer', 'difference', 'legal_fee', 'discounted_legal_fee', 'commission', 'fee_rate'].includes(allCasesSortColumn)) {
                aVal = parseFloat(aVal) || 0;
                bVal = parseFloat(bVal) || 0;
            } else if (allCasesSortColumn === 'check_received') {
                aVal = parseInt(aVal) || 0;
                bVal = parseInt(bVal) || 0;
            } else if (allCasesSortColumn === 'month') {
                const monthOrder = {'january':1,'february':2,'march':3,'april':4,'may':5,'june':6,'july':7,'august':8,'september':9,'october':10,'november':11,'december':12};
                aVal = monthOrder[String(aVal).toLowerCase()] || 0;
                bVal = monthOrder[String(bVal).toLowerCase()] || 0;
            } else {
                aVal = String(aVal).toLowerCase();
                bVal = String(bVal).toLowerCase();
            }

            if (aVal < bVal) return allCasesSortDirection === 'asc' ? -1 : 1;
            if (aVal > bVal) return allCasesSortDirection === 'asc' ? 1 : -1;
            return 0;
        });
    }

    const tbody = document.getElementById('allCasesBody');
    const footerInfo = document.getElementById('allCasesFooterInfo');
    const footerTotal = document.getElementById('allCasesFooterTotal');

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="14" style="padding: 32px 16px; text-align: center;" class="text-secondary">No cases found</td></tr>`;
        if (footerInfo) footerInfo.textContent = 'Showing 0 cases';
        if (footerTotal) footerTotal.textContent = formatCurrency(0);
        return;
    }

    if (footerInfo) footerInfo.textContent = `Showing ${filtered.length} case${filtered.length !== 1 ? 's' : ''}`;

    if (monthFilter === 'all') {
        const casesByMonth = {};
        filtered.forEach(c => {
            if (!casesByMonth[c.month]) {
                casesByMonth[c.month] = [];
            }
            casesByMonth[c.month].push(c);
        });

        const monthOrder = {'january':1,'february':2,'march':3,'april':4,'may':5,'june':6,'july':7,'august':8,'september':9,'october':10,'november':11,'december':12,'jan':1,'feb':2,'mar':3,'apr':4,'jun':6,'jul':7,'aug':8,'sep':9,'oct':10,'nov':11,'dec':12};
        const sortedMonths = Object.keys(casesByMonth).sort((a, b) => {
            const pa = a.replace('.', '').toLowerCase().split(' ');
            const pb = b.replace('.', '').toLowerCase().split(' ');
            const yearA = pa[1] ? parseInt(pa[1]) : 0;
            const yearB = pb[1] ? parseInt(pb[1]) : 0;
            if (yearA !== yearB) return yearB - yearA;
            return (monthOrder[pb[0]] || 0) - (monthOrder[pa[0]] || 0);
        });

        let html = '';
        sortedMonths.forEach(monthKey => {
            const cases = casesByMonth[monthKey];
            const caseCount = cases.length;
            const monthTotal = cases.reduce((sum, c) => sum + parseFloat(c.commission), 0);

            html += `
                <tr class="month-header-row">
                    <td colspan="14" style="background: #f8fafc; padding: 10px 12px; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 700; font-size: 12px; color: #0f172a;">${monthKey}</span>
                            <span style="font-size: 11px; color: #047857; font-weight: 600;">
                                ${caseCount} case${caseCount !== 1 ? 's' : ''} | ${formatCurrency(monthTotal)}
                            </span>
                        </div>
                    </td>
                </tr>
            `;

            html += cases.map(c => `
                <tr onclick="viewCaseDetail(${c.id})" style="cursor:pointer;">
                    <td style="font-size:12px;padding:6px;">${c.counsel_name}</td>
                    <td class="c" style="padding:6px;"><span class="stat-badge ${c.status}">${c.status === 'in_progress' ? 'Prog' : c.status.charAt(0).toUpperCase() + c.status.slice(1)}</span></td>
                    <td style="font-size:12px;padding:6px;">${c.month}</td>
                    <td style="font-weight:600;font-size:12px;padding:6px;">${c.case_number}</td>
                    <td style="font-size:12px;padding:6px;">${c.client_name}</td>
                    <td style="font-size:11px;padding:6px;">${(c.resolution_type || '-').replace('No Offer Settle', 'No Offer').replace('File and Bump', 'File/Bump').replace('Post Deposition Settle', 'Post Dep')}</td>
                    <td class="r" style="font-weight:600;font-size:12px;padding:6px;">${formatCurrency(c.settled)}</td>
                    <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.presuit_offer || 0)}</td>
                    <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.difference || 0)}</td>
                    <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.legal_fee || 0)}</td>
                    <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.discounted_legal_fee || 0)}</td>
                    <td class="r" style="font-weight:700;color:#059669;font-size:12px;padding:6px;">${formatCurrency(c.commission)}</td>
                    <td class="c" style="padding:6px;">${c.check_received ? '<span style="color:#059669;font-size:11px;font-weight:600;">Received</span>' : '<span style="color:#94a3b8;font-size:11px;">—</span>'}</td>
                    <td class="c" style="padding:6px;" onclick="event.stopPropagation()">
                        <span onclick="editCaseFromRow(${c.id})" style="color: #2563eb; cursor: pointer; font-size: 10px;">Edit</span>
                        <span onclick="deleteCaseFromRow(${c.id})" style="color: #dc2626; cursor: pointer; font-size: 10px; margin-left: 6px;">Delete</span>
                    </td>
                </tr>
            `).join('');
        });

        tbody.innerHTML = html;
    } else {
        tbody.innerHTML = filtered.map(c => `
            <tr onclick="viewCaseDetail(${c.id})" style="cursor:pointer;">
                <td style="font-size:12px;padding:6px;">${c.counsel_name}</td>
                <td class="c" style="padding:6px;"><span class="stat-badge ${c.status}">${c.status === 'in_progress' ? 'Prog' : c.status.charAt(0).toUpperCase() + c.status.slice(1)}</span></td>
                <td style="font-size:12px;padding:6px;">${c.month}</td>
                <td style="font-weight:600;font-size:12px;padding:6px;">${c.case_number}</td>
                <td style="font-size:12px;padding:6px;">${c.client_name}</td>
                <td style="font-size:11px;padding:6px;">${(c.resolution_type || '-').replace('No Offer Settle', 'No Offer').replace('File and Bump', 'File/Bump').replace('Post Deposition Settle', 'Post Dep')}</td>
                <td class="r" style="font-weight:600;font-size:12px;padding:6px;">${formatCurrency(c.settled)}</td>
                <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.presuit_offer || 0)}</td>
                <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.difference || 0)}</td>
                <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.legal_fee || 0)}</td>
                <td class="r" style="font-size:12px;padding:6px;">${formatCurrency(c.discounted_legal_fee || 0)}</td>
                <td class="r" style="font-weight:700;color:#059669;font-size:12px;padding:6px;">${formatCurrency(c.commission)}</td>
                <td class="c" style="padding:6px;">${c.check_received ? '<span style="color:#059669;font-size:11px;font-weight:600;">Received</span>' : '<span style="color:#94a3b8;font-size:11px;">—</span>'}</td>
                <td class="c" style="padding:6px;" onclick="event.stopPropagation()">
                    <span onclick="editCaseFromRow(${c.id})" style="color: #2563eb; cursor: pointer; font-size: 10px;">Edit</span>
                    <span onclick="deleteCaseFromRow(${c.id})" style="color: #dc2626; cursor: pointer; font-size: 10px; margin-left: 6px;">Delete</span>
                </td>
            </tr>
        `).join('');
    }

    const filteredCommission = filtered.reduce((sum, c) => sum + parseFloat(c.commission), 0);
    if (footerTotal) footerTotal.textContent = formatCurrency(filteredCommission);
}

function exportAllToExcel() {
    const data = allCases.map(c => ({
        'Status': c.status,
        'Counsel': c.counsel_name,
        'Case Type': c.case_type || '',
        'Case #': c.case_number,
        'Client Name': c.client_name,
        'Resolution Type': c.resolution_type || '',
        'Fee Rate': c.fee_rate || '',
        'Month': c.month,
        'Settled': c.settled,
        'Pre-Suit Offer': c.presuit_offer || 0,
        'Difference': c.difference || 0,
        'Legal Fee': c.legal_fee || 0,
        'Disc. Legal Fee': c.discounted_legal_fee || 0,
        'Commission': c.commission,
        'Check Received': c.check_received == 1 ? 'Yes' : 'No',
        'Note': c.note || ''
    }));

    const ws = XLSX.utils.json_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'All Cases');
    XLSX.writeFile(wb, `all-cases-${new Date().toISOString().split('T')[0]}.xlsx`);
}

function exportReport() {
    exportAllToExcel();
}

// Case Detail Modal Functions
function viewPendingCaseDetail(id) {
    const c = pendingCases.find(x => x.id == id);
    if (!c) return;

    currentCaseId = id;
    displayCaseDetailModal(c);
}

function viewCaseDetail(id) {
    const c = allCases.find(x => x.id == id);
    if (!c) return;

    currentCaseId = id;
    displayCaseDetailModal(c);
}

function displayCaseDetailModal(c) {
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

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 14px;">
            <div>
                <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Month</div>
                <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${c.month}</div>
            </div>
            <div>
                <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Fee Rate</div>
                <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${c.fee_rate || '-'}%</div>
            </div>
            <div>
                <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Status</div>
                <div style="padding: 6px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">${statusBadge[c.status] || c.status}</div>
            </div>
        </div>

        <!-- Financial Details Section -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                <div style="width: 26px; height: 26px; background: #0f172a; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px;">💰</div>
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
            <div style="background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; position: relative; overflow: hidden;">
                <div style="position: absolute; right: 0; top: 0; bottom: 0; width: 80px; background: linear-gradient(135deg, rgba(34, 211, 238, 0.1), rgba(168, 85, 247, 0.1));"></div>
                <div>
                    <span style="font-size: 11px; font-weight: 500; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.5px;">Commission</span>
                    <div style="font-size: 11px; margin-top: 2px; cursor: pointer;" onclick="toggleCheckFromModal(${c.id})" title="Click to toggle check status">${c.check_received == 1 ? '<span style="color:#22d3ee;">&#10003; Check Received</span>' : '<span style="color:rgba(255,255,255,0.5);">&#9203; Check Pending — click to mark received</span>'}</div>
                </div>
                <span style="font-size: 22px; font-weight: 700; color: #22d3ee; position: relative; z-index: 1;">${formatCurrency(c.commission)}</span>
            </div>
        </div>

        <!-- Counsel & Note -->
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px;">
            <div>
                <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Counsel</div>
                <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a; font-weight: 500;">${c.counsel_name}</div>
            </div>
            <div>
                <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Note</div>
                <div style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #0f172a;">${c.note || '-'}</div>
            </div>
        </div>
    `;

    const approvalButtonsDiv = document.getElementById('caseApprovalButtons');
    let approvalHtml = `<button onclick="deleteCaseFromModal(${c.id})" style="padding: 7px 14px; background: #dc2626; color: white; border: none; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;">Delete</button>`;
    if (c.status === 'unpaid') {
        approvalHtml += ` <button onclick="approveCaseFromModal()" style="padding: 7px 14px; background: #059669; color: white; border: none; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;">Approve</button>`;
    }
    approvalButtonsDiv.innerHTML = approvalHtml;

    document.getElementById('caseMessageRecipient').textContent = c.counsel_name;
    document.getElementById('caseMessageSubject').value = `Case #${c.case_number} - ${c.client_name}`;
    document.getElementById('caseMessageBody').value = '';

    window.currentCaseUserId = c.user_id;

    document.getElementById('allCaseDetailModal').classList.add('show');
}

async function toggleCheckFromModal(caseId) {
    const c = allCases.find(x => x.id == caseId) || pendingCases.find(x => x.id == caseId);
    if (!c) return;
    const newVal = c.check_received ? 0 : 1;
    const result = await apiCall('api/cases.php', {
        method: 'PUT',
        body: JSON.stringify({ id: parseInt(caseId), check_received: newVal })
    });
    if (result.success) {
        c.check_received = newVal;
        displayCaseDetailModal(c);
        renderAllCases();
        renderPendingCases();
    } else {
        alert(result.error || 'Failed to update');
    }
}

function closeCaseModal() {
    document.getElementById('allCaseDetailModal').classList.remove('show');
    currentCaseId = null;
    window.currentCaseUserId = null;
}

function toggleCaseMessage(btn) {
    const fields = document.getElementById('caseMessageFields');
    const isHidden = fields.style.display === 'none';
    fields.style.display = isHidden ? '' : 'none';
    btn.classList.toggle('active', isHidden);
    if (!isHidden) {
        document.getElementById('caseMessageSubject').value = '';
        document.getElementById('caseMessageBody').value = '';
    }
}

async function sendCaseMessage() {
    const subject = document.getElementById('caseMessageSubject').value.trim();
    const message = document.getElementById('caseMessageBody').value.trim();
    const toUserId = window.currentCaseUserId;

    if (!subject || !message) {
        alert('Please enter both subject and message');
        return;
    }

    if (!toUserId) {
        alert('Unable to determine recipient');
        return;
    }

    try {
        const result = await apiCall('api/messages.php', {
            method: 'POST',
            body: JSON.stringify({
                to_user_id: toUserId,
                subject: subject,
                message: message
            })
        });

        if (result.success) {
            alert('Message sent successfully!');
            document.getElementById('caseMessageBody').value = '';
            loadMessages();
        } else {
            alert(result.error || 'Error sending message');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error sending message');
    }
}

function editCaseFromModal() {
    if (!currentCaseId) return;
    const caseId = currentCaseId;
    closeCaseModal();
    editCaseFromRow(caseId);
}

function deleteCaseFromModal() {
    if (!currentCaseId) return;
    const caseId = currentCaseId;
    closeCaseModal();
    deleteCaseFromRow(caseId);
}

function approveCaseFromModal() {
    if (!currentCaseId) return;
    const caseId = currentCaseId;
    closeCaseModal();
    approveCase(caseId);
}

function rejectCaseFromModal() {
    if (!currentCaseId) return;
    const caseId = currentCaseId;
    closeCaseModal();
    rejectCase(caseId);
}

function editCaseFromRow(id) {
    let c = allCases.find(x => x.id == id);
    if (!c) {
        c = pendingCases.find(x => x.id == id);
    }
    if (!c) return;

    currentCaseId = id;

    const monthSelect = document.getElementById('editMonth');
    if (monthSelect.options.length === 0) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const year = new Date().getFullYear();
        months.forEach(m => {
            monthSelect.innerHTML += `<option value="${m}. ${year}">${m}. ${year}</option>`;
        });
        monthSelect.innerHTML += `<option value="TBD">TBD</option>`;
    }

    document.getElementById('editCaseId').value = c.id;
    document.getElementById('editCaseType').value = c.case_type || 'Auto Accident';
    document.getElementById('editResolutionType').value = c.resolution_type || '';
    document.getElementById('editCaseNumber').value = c.case_number;
    document.getElementById('editClientName').value = c.client_name;
    document.getElementById('editMonth').value = c.month;
    document.getElementById('editFeeRate').value = c.fee_rate || '33.33';
    document.getElementById('editSettled').value = c.settled;
    document.getElementById('editPresuitOffer').value = c.presuit_offer || 0;
    document.getElementById('editDifference').value = formatCurrency(c.difference || 0);
    document.getElementById('editLegalFee').value = formatCurrency(c.legal_fee || 0);
    document.getElementById('editDiscountedLegalFee').value = c.discounted_legal_fee || 0;
    document.getElementById('editNote').value = c.note || '';
    document.getElementById('editCheckReceived').checked = c.check_received == 1;
    document.getElementById('editStatus').value = c.status || 'pending';

    calculateEditCommission();

    document.getElementById('editCaseModal').classList.add('show');
}

function calculateEditCommission() {
    const settled = parseFloat(document.getElementById('editSettled').value) || 0;
    const presuit = parseFloat(document.getElementById('editPresuitOffer').value) || 0;
    const feeRate = parseFloat(document.getElementById('editFeeRate').value);
    const discLegalFee = parseFloat(document.getElementById('editDiscountedLegalFee').value) || 0;

    const difference = settled - presuit;
    const base = settled - presuit;
    const legalFee = feeRate === 33.33 ? (base / 3) : (base * 0.4);

    document.getElementById('editDifference').value = formatCurrency(difference);
    document.getElementById('editLegalFee').value = formatCurrency(legalFee);

    let c = allCases.find(x => x.id == currentCaseId);
    if (!c) {
        c = pendingCases.find(x => x.id == currentCaseId);
    }
    if (c) {
        const originalCommissionRate = c.commission && c.discounted_legal_fee ?
            (c.commission / c.discounted_legal_fee) * 100 : 10;
        const commission = discLegalFee * (originalCommissionRate / 100);
        document.getElementById('editCommission').textContent = formatCurrency(commission);
    } else {
        const commission = discLegalFee * 0.1;
        document.getElementById('editCommission').textContent = formatCurrency(commission);
    }
}

function closeEditModal() {
    document.getElementById('editCaseModal').classList.remove('show');
    currentCaseId = null;
}

async function deleteCaseFromModal(id) {
    await deleteCaseFromRow(id);
}

async function deleteCaseFromRow(id) {
    if (!confirm('Are you sure you want to delete this case?')) return;

    try {
        const result = await apiCall(`api/cases.php?id=${id}`, { method: 'DELETE' });

        if (result.success) {
            loadAllCases();
            loadPendingCases();
            if (typeof loadStats === 'function') loadStats();
            if (typeof loadOverviewData === 'function') loadOverviewData();
            closeCaseModal();
            closeEditModal();
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error deleting case');
    }
}

// Edit form submission
document.getElementById('editCaseForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const caseId = document.getElementById('editCaseId').value;
    const data = {
        id: parseInt(caseId),
        case_type: document.getElementById('editCaseType').value,
        resolution_type: document.getElementById('editResolutionType').value,
        case_number: document.getElementById('editCaseNumber').value,
        client_name: document.getElementById('editClientName').value,
        month: document.getElementById('editMonth').value,
        fee_rate: parseFloat(document.getElementById('editFeeRate').value),
        settled: parseFloat(document.getElementById('editSettled').value) || 0,
        presuit_offer: parseFloat(document.getElementById('editPresuitOffer').value) || 0,
        discounted_legal_fee: parseFloat(document.getElementById('editDiscountedLegalFee').value) || 0,
        note: document.getElementById('editNote').value,
        check_received: document.getElementById('editCheckReceived').checked,
        status: document.getElementById('editStatus').value
    };

    try {
        const result = await apiCall('api/cases.php', {
            method: 'PUT',
            body: JSON.stringify(data)
        });

        if (result.success) {
            closeEditModal();
            loadAllCases();
            loadPendingCases();
            if (typeof loadStats === 'function') loadStats();
            if (typeof loadOverviewData === 'function') loadOverviewData();
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error updating case');
    }
});

// Add event listeners for edit form calculations
document.getElementById('editSettled')?.addEventListener('change', calculateEditCommission);
document.getElementById('editPresuitOffer')?.addEventListener('change', calculateEditCommission);
document.getElementById('editFeeRate')?.addEventListener('change', calculateEditCommission);
document.getElementById('editDiscountedLegalFee')?.addEventListener('change', calculateEditCommission);
