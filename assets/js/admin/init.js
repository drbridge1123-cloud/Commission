/**
 * Admin Dashboard - Initialization, tab switching, and event handlers.
 * Must be loaded LAST after all other admin JS files.
 */

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Set initial width for all commissions tab (first tab)
    setWidth(TAB_DEFAULT_WIDTHS['all'] || '100');
    initMonthDropdown();
    loadPendingCases();
    loadMessages();
    loadDeadlineRequestsBadge();
    initFixedScrollbar();
});

// Tab switching
async function switchTab(tab) {
    // Hide all content
    document.querySelectorAll('[id^="content-"]').forEach(el => el.classList.add('hidden'));
    // Remove active from all nav links
    document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));

    // Show selected content
    document.getElementById(`content-${tab}`).classList.remove('hidden');
    // Activate nav link
    const navLink = document.querySelector(`.nav-link[data-tab="${tab}"]`);
    if (navLink) navLink.classList.add('active');

    // Update page title
    document.getElementById('pageTitle').textContent = pageTitles[tab] || tab;

    // Set default width for this tab
    const defaultWidth = TAB_DEFAULT_WIDTHS[tab] || '100';
    setWidth(defaultWidth);

    // Load data for tab
    if (tab === 'all') {
        loadPendingCases();
        if (currentAllCommSubTab === 'all') loadAllCases();
        if (currentAllCommSubTab === 'history') loadHistory();
    }
    if (tab === 'report') {
        if (currentAnalyticsSubTab === 'overview') {
            initOverviewYearFilter();
            loadOverviewData();
        } else if (currentAnalyticsSubTab === 'performance') {
            loadPerformanceData();
        } else {
            await loadAllCases();
            generateComprehensiveReport();
        }
    }
    if (tab === 'notifications') loadMessages();
    if (tab === 'admin-control') loadUsers();
    if (tab === 'traffic') {
        loadAdminTrafficCases();
        loadMyTrafficRequests();
    }
    if (tab === 'pipeline') {
        if (currentPipelineSubTab === 'pipeline') {
            initPipelineAttorneyFilter().then(() => {
                initPipelineYearFilter();
                loadPipelineData();
            });
        } else if (currentPipelineSubTab === 'deadline') {
            loadDeadlineRequests();
        } else if (currentPipelineSubTab === 'demandReq') {
            loadMyDemandRequests();
        }
    }
    if (tab === 'referrals') {
        initAdminRefFilters();
        loadAdminReferrals();
        loadAdminRefSummary();
    }
    if (tab === 'database') {
        const frame = document.getElementById('databaseFrame');
        if (frame && !frame.src.includes('check_db.php')) {
            frame.src = 'check_db.php';
        }
    }
}

// All Commissions sub-tab switching (Pending / All / History)
function switchAllCommSubTab(subTab) {
    currentAllCommSubTab = subTab;
    ['pending', 'all', 'history'].forEach(t => {
        const panel = document.getElementById('acSub-' + t);
        if (panel) panel.style.display = t === subTab ? '' : 'none';
        const pill = document.getElementById('acPill-' + t);
        if (pill) pill.classList.toggle('active', t === subTab);
    });
    if (subTab === 'all') loadAllCases();
    if (subTab === 'history') loadHistory();
}

// Analytics sub-tab switching (Command Center / Performance / Reports)
function switchAnalyticsSubTab(subTab) {
    currentAnalyticsSubTab = subTab;
    ['overview', 'performance', 'report'].forEach(t => {
        const panel = document.getElementById('anSub-' + t);
        if (panel) panel.style.display = t === subTab ? '' : 'none';
        const pill = document.getElementById('anPill-' + t);
        if (pill) pill.classList.toggle('active', t === subTab);
    });
    if (subTab === 'overview') { initOverviewYearFilter(); loadOverviewData(); }
    if (subTab === 'performance') loadPerformanceData();
    if (subTab === 'report') { loadAllCases().then(() => generateComprehensiveReport()); }
}

// Pipeline sub-tab switching (Pipeline / Deadline Requests / Demand Requests)
function switchPipelineSubTab(subTab) {
    currentPipelineSubTab = subTab;
    ['pipeline', 'deadline', 'demandReq'].forEach(t => {
        const panel = document.getElementById('plSub-' + t);
        if (panel) panel.style.display = t === subTab ? '' : 'none';
        const pill = document.getElementById('plPill-' + t);
        if (pill) pill.classList.toggle('active', t === subTab);
    });

    // Show/hide "Send Demand to Chong" button
    const sendBtn = document.getElementById('plSendDemandBtn');
    if (sendBtn) sendBtn.style.display = (subTab === 'demandReq') ? '' : 'none';

    if (subTab === 'pipeline') {
        initPipelineAttorneyFilter().then(() => {
            initPipelineYearFilter();
            loadPipelineData();
        });
    }
    if (subTab === 'deadline') loadDeadlineRequests();
    if (subTab === 'demandReq') loadMyDemandRequests();
}

// Sidebar navigation click handlers
document.querySelectorAll('.nav-link[data-tab]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const tabName = this.dataset.tab;
        if (tabName) {
            switchTab(tabName);
        }
    });
});

// Close modals on outside click
window.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
});
