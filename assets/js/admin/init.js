/**
 * Admin Dashboard - Initialization, tab switching, and event handlers.
 * Must be loaded LAST after all other admin JS files.
 */

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Set initial width for pending tab (first tab)
    setWidth(TAB_DEFAULT_WIDTHS['pending'] || '75');
    initMonthDropdown();
    loadPendingCases();
    loadStats();
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
    if (tab === 'all') loadAllCases();
    if (tab === 'dashboard') loadStats();
    if (tab === 'report') {
        await loadAllCases();
        generateComprehensiveReport();
    }
    if (tab === 'notifications') loadMessages();
    if (tab === 'history') loadHistory();
    if (tab === 'admin-control') loadUsers();
    if (tab === 'traffic') {
        loadAdminTrafficCases();
        loadMyTrafficRequests();
    }
    if (tab === 'deadline-requests') {
        loadDeadlineRequests();
    }
    if (tab === 'performance') {
        loadPerformanceData();
    }
    if (tab === 'goals') {
        initGoalsYearFilter();
        loadGoalsData();
    }
    if (tab === 'database') {
        const frame = document.getElementById('databaseFrame');
        if (frame && !frame.src.includes('check_db.php')) {
            frame.src = 'check_db.php';
        }
    }
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
