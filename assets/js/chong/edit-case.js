/**
 * ChongDashboard - Edit case modal functions.
 */

async function openEditCaseModal(caseId) {
    let caseData = commissionsData.find(c => c.id == caseId) ||
                  demandCasesData.find(c => c.id == caseId) ||
                  litigationCasesData.find(c => c.id == caseId);

    if (!caseData) {
        const result = await apiCall(`api/chong_cases.php?id=${caseId}`);
        if (result.case) {
            caseData = result.case;
        } else {
            alert('Case not found');
            return;
        }
    }

    currentEditCaseData = caseData;

    document.getElementById('editCaseId').value = caseData.id;
    document.getElementById('editCaseNumber').value = caseData.case_number || '';
    document.getElementById('editClientName').value = caseData.client_name || '';
    document.getElementById('editPhase').value = caseData.phase || 'demand';
    document.getElementById('editMonth').value = caseData.month || '';
    document.getElementById('editSettled').value = caseData.settled || '';
    document.getElementById('editDiscLegalFee').value = caseData.discounted_legal_fee || '';
    document.getElementById('editPresuitOffer').value = caseData.presuit_offer || '';
    document.getElementById('editResolutionType').value = caseData.resolution_type || '';
    document.getElementById('editAssignedDate').value = caseData.assigned_date || '';
    document.getElementById('editNote').value = caseData.note || '';
    document.getElementById('editStage').value = caseData.stage || '';

    toggleEditPhaseFields();
    calculateEditCommission();

    await setupDeadlineSection(caseData);

    openModal('editCaseModal');
}

async function setupDeadlineSection(caseData) {
    const deadlineSection = document.getElementById('deadlineSection');
    const deadlineExtensionForm = document.getElementById('deadlineExtensionForm');
    const requestExtensionBtn = document.getElementById('requestExtensionBtn');
    const pendingExtensionAlert = document.getElementById('pendingExtensionAlert');

    if (caseData.phase === 'demand' && caseData.demand_deadline) {
        deadlineSection.style.display = 'block';

        document.getElementById('editCurrentDeadline').value = caseData.demand_deadline;

        const deadline = new Date(caseData.demand_deadline);
        const today = new Date();
        const daysLeft = Math.ceil((deadline - today) / (1000 * 60 * 60 * 24));

        const daysInput = document.getElementById('editDaysRemaining');
        if (daysLeft < 0) {
            daysInput.value = `${Math.abs(daysLeft)} days overdue`;
            daysInput.style.color = '#dc2626';
        } else if (daysLeft <= 14) {
            daysInput.value = `${daysLeft} days left`;
            daysInput.style.color = '#dc2626';
        } else if (daysLeft <= 30) {
            daysInput.value = `${daysLeft} days left`;
            daysInput.style.color = '#f59e0b';
        } else {
            daysInput.value = `${daysLeft} days left`;
            daysInput.style.color = '#059669';
        }

        const result = await apiCall('api/deadline_requests.php');
        currentPendingExtension = null;

        if (result.requests) {
            currentPendingExtension = result.requests.find(r =>
                r.case_id == caseData.id && r.status === 'pending'
            );
        }

        if (currentPendingExtension) {
            pendingExtensionAlert.style.display = 'flex';
            pendingExtensionAlert.querySelector('.alert-text').textContent =
                `Pending request: ${currentPendingExtension.current_deadline} → ${currentPendingExtension.requested_deadline}`;
            requestExtensionBtn.style.display = 'none';
        } else {
            pendingExtensionAlert.style.display = 'none';
            requestExtensionBtn.style.display = 'inline-block';
        }

        deadlineExtensionForm.style.display = 'none';
        document.getElementById('editRequestedDeadline').value = '';
        document.getElementById('editExtensionReason').value = '';
    } else {
        deadlineSection.style.display = 'none';
    }
}

function showDeadlineExtensionForm() {
    document.getElementById('deadlineExtensionForm').style.display = 'block';
    document.getElementById('requestExtensionBtn').style.display = 'none';

    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('editRequestedDeadline').min = tomorrow.toISOString().split('T')[0];
}

function cancelDeadlineExtension() {
    document.getElementById('deadlineExtensionForm').style.display = 'none';
    document.getElementById('requestExtensionBtn').style.display = 'inline-block';
    document.getElementById('editRequestedDeadline').value = '';
    document.getElementById('editExtensionReason').value = '';
}

async function submitDeadlineExtension() {
    const caseId = document.getElementById('editCaseId').value;
    const requestedDeadline = document.getElementById('editRequestedDeadline').value;
    const reason = document.getElementById('editExtensionReason').value;

    if (!requestedDeadline || !reason.trim()) {
        alert('Please provide both a new deadline and a reason for the extension.');
        return;
    }

    const result = await apiCall('api/deadline_requests.php', 'POST', {
        case_id: caseId,
        requested_deadline: requestedDeadline,
        reason: reason.trim()
    });

    if (result.success) {
        showToast('Deadline extension request submitted!', 'success');
        await setupDeadlineSection(currentEditCaseData);
    } else {
        alert('Error: ' + (result.error || 'Failed to submit request'));
    }
}

function toggleEditPhaseFields() {
    const phase = document.getElementById('editPhase').value;
    document.getElementById('editLitigationFields').style.display = phase === 'litigation' ? 'block' : 'none';
    document.getElementById('editStageRow').style.display = phase === 'demand' ? 'flex' : 'none';
    calculateEditCommission();
}

function calculateEditCommission() {
    const phase = document.getElementById('editPhase').value;
    const discLegalFee = parseFloat(document.getElementById('editDiscLegalFee').value) || 0;
    let commission = 0;

    if (phase === 'demand') {
        commission = discLegalFee * 0.05;
    } else {
        commission = discLegalFee * 0.20;
    }

    document.getElementById('editCommissionDisplay').textContent = formatCurrency(commission);
}

async function submitEditCase(event) {
    event.preventDefault();

    const caseId = document.getElementById('editCaseId').value;
    const phase = document.getElementById('editPhase').value;

    const data = {
        id: caseId,
        case_number: document.getElementById('editCaseNumber').value,
        client_name: document.getElementById('editClientName').value,
        phase: phase,
        month: document.getElementById('editMonth').value,
        settled: parseFloat(document.getElementById('editSettled').value) || 0,
        discounted_legal_fee: parseFloat(document.getElementById('editDiscLegalFee').value) || 0,
        assigned_date: document.getElementById('editAssignedDate').value,
        note: document.getElementById('editNote').value
    };

    if (phase === 'demand') {
        data.stage = document.getElementById('editStage').value;
    }

    if (phase === 'litigation') {
        data.presuit_offer = parseFloat(document.getElementById('editPresuitOffer').value) || 0;
        data.resolution_type = document.getElementById('editResolutionType').value;
    }

    const result = await apiCall('api/cases.php', 'PUT', data);
    if (result.success) {
        closeModal('editCaseModal');
        loadDemandCases();
        loadLitigationCases();
        loadCommissions();
        loadDashboard();
        alert('Case updated successfully!');
    } else {
        alert('Error: ' + (result.error || 'Failed to update case'));
    }
}

async function deleteCaseFromModal() {
    const caseId = document.getElementById('editCaseId').value;
    const caseNumber = document.getElementById('editCaseNumber').value;

    if (!confirm(`Are you sure you want to delete case "${caseNumber}"?\n\nThis action cannot be undone.`)) {
        return;
    }

    const result = await apiCall(`api/cases.php?id=${caseId}`, 'DELETE', { csrf_token: csrfToken });

    if (result.success) {
        closeModal('editCaseModal');
        showToast('Case deleted successfully', 'success');
        loadDemandCases();
        loadLitigationCases();
        loadCommissions();
        loadDashboard();
    } else {
        showToast(result.error || 'Failed to delete case', 'error');
    }
}
