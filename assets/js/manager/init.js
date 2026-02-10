/**
 * Manager dashboard initialization.
 * Must be loaded last after all other manager scripts.
 * Overrides employee switchTab with manager-specific tab handling.
 */

function switchTab(tab) {
    // Hide all content
    document.querySelectorAll('[id^="content-"]').forEach(el => el.classList.add('hidden'));
    // Remove active from all nav links
    document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));

    // Show selected content
    const el = document.getElementById(`content-${tab}`);
    if (el) el.classList.remove('hidden');

    // Activate nav link
    const navLink = document.querySelector(`.nav-link[data-tab="${tab}"]`);
    if (navLink) navLink.classList.add('active');

    // Update page title
    document.getElementById('pageTitle').textContent = pageTitles[tab] || tab;

    // Set default width for this tab
    const defaultWidth = TAB_DEFAULT_WIDTHS[tab] || '100';
    setWidth(defaultWidth);

    // Load data for tab
    if (tab === 'referrals') {
        initReferralFilters();
        loadReferrals();
        loadReferralSummary();
    } else if (tab === 'cases') {
        initMonthDropdowns();
        loadCases();
    } else if (tab === 'reports') {
        initReportDropdowns();
        generateReport();
    } else if (tab === 'history') {
        initHistoryFilters();
        loadHistory();
    } else if (tab === 'notifications') {
        loadNotifications();
    } else if (tab === 'goals') {
        initTeamGoalsYearFilter();
        loadTeamGoals();
    } else if (tab === 'attorney-progress') {
        initAttorneyProgress();
    }
}

// Sidebar navigation click handlers
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const tabName = this.dataset.tab;
        if (tabName) switchTab(tabName);
    });
});

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    switchTab('referrals');
    checkNotifications();
    setInterval(checkNotifications, 30000);
});
