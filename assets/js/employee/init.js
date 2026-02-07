/**
 * Employee dashboard initialization.
 * Must be loaded last after all other employee scripts.
 */

function switchTab(tab) {
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
    if (tab === 'reports') {
        initReportDropdowns();
        generateReport();
    } else if (tab === 'history') {
        initHistoryFilters();
        loadHistory();
    } else if (tab === 'notifications') {
        loadNotifications();
    } else if (tab === 'traffic') {
        initTrafficYearFilter();
        loadTrafficCases();
    } else if (tab === 'goals') {
        initMyGoalsYearFilter();
        loadMyGoals();
    } else if (tab === 'traffic-requests') {
        loadEmpTrafficRequests();
    }
}

// Sidebar navigation click handlers
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const tabName = this.dataset.tab;
        if (tabName) {
            switchTab(tabName);
        }
    });
});

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    initMonthDropdowns();
    loadCases();
    checkNotifications();
    // Check for new notifications every 30 seconds
    setInterval(checkNotifications, 30000);
    // Set initial width for cases tab (first tab) - 100%
    setWidth(TAB_DEFAULT_WIDTHS['cases'] || '100');
});
