/**
 * ChongDashboard - Initialization and tab switching.
 * This file must be loaded LAST after all other Chong JS files.
 */

function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));

    // Remove active from all sidebar links
    document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));

    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');

    // Activate sidebar link
    const navLink = document.querySelector('.nav-link[data-tab="' + tabName + '"]');
    if (navLink) navLink.classList.add('active');

    // Update page title
    document.getElementById('pageTitle').textContent = pageTitles[tabName] || tabName;

    // Load data for specific tabs
    if (tabName === 'demand') {
        loadDemandCases();
        loadDemandRequests();
    }
    if (tabName === 'commissions') {
        loadCommissions();
    }
    if (tabName === 'reports') {
        loadReports();
    }
    if (tabName === 'traffic') {
        loadTrafficCases();
        loadAllTrafficRequests();
    }
    if (tabName === 'notifications') {
        loadMessages();
    }
    setWidth('100');
}

document.addEventListener('DOMContentLoaded', function() {
    loadDashboard();
    loadDemandCases();
    loadDemandRequests();
    loadLitigationCases();
    loadUnreadCount();
    loadAllTrafficRequests();

    // Sidebar nav-link click handler
    document.querySelectorAll('.nav-link[data-tab]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            switchTab(this.dataset.tab);
        });
    });

    // Event delegation for dynamically created buttons
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        const action = btn.dataset.action;
        const id = btn.dataset.id;
        const caseNum = btn.dataset.case || '';
        const client = btn.dataset.client || '';
        const presuit = parseFloat(btn.dataset.presuit) || 0;

        switch (action) {
            case 'new-traffic':
                openTrafficModal();
                break;
            case 'edit':
                openEditCaseModal(parseInt(id));
                break;
            case 'settle-demand':
                openSettleDemandModal(parseInt(id), caseNum, client);
                break;
            case 'to-litigation':
                openToLitigationModal(parseInt(id), caseNum, client);
                break;
            case 'settle-litigation':
                openSettleLitigationModal(parseInt(id), caseNum, client, presuit);
                break;
            case 'edit-traffic':
                editTrafficCase(parseInt(id));
                break;
            case 'delete-traffic':
                deleteTrafficCase(parseInt(id));
                break;
            case 'new-message':
                openNewMessageModal();
                break;
            case 'view-message':
                viewMessage(parseInt(id));
                break;
            default:
                console.warn('Unknown action:', action);
        }
    });
});
