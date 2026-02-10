/**
 * Admin Dashboard state variables, configuration, and admin-specific utilities.
 * NOTE: csrfToken is set inline via PHP before this file loads.
 */

// Data stores
let allCases = [];
let pendingCases = [];
let stats = {};

// Sort state
let allCasesSortColumn = null;
let allCasesSortDirection = 'asc';
let pendingSortColumn = null;
let pendingSortDirection = 'asc';

// Case detail state
let currentCaseId = null;

// Deadline requests state
let deadlineRequests = [];

// History state
let historyCases = [];
let historyDropdownsInitialized = false;

// User management state
let allUsers = [];

// Traffic state
let adminTrafficCases = [];
let adminTrafficAllCases = [];
let myTrafficRequests = [];
let editingTrafficCaseId = null;
let tv3PillTab = 'active';

// Messages state
let currentViewingMessageAdmin = null;
let allMessagesAdmin = [];
let allItems = [];

// Delete confirmation state
let pendingDeleteType = null;
let pendingDeleteId = null;

// All Commissions sub-tab state
let currentAllCommSubTab = 'pending';

// Analytics sub-tab state
let currentAnalyticsSubTab = 'overview';

// Pipeline sub-tab state
let currentPipelineSubTab = 'pipeline';

// Demand requests state
let myDemandRequests = [];

// Chart instances
let perfChartInstance = null;
let trendChartInstance = null;
let reportCharts = { monthly: null, counsel: null, caseType: null };

// Default widths for each tab (Admin page)
const TAB_DEFAULT_WIDTHS = {
    'all': '100',
    'report': '100',
    'notifications': '100',
    'traffic': '100',
    'admin-control': '100',
    'pipeline': '100',
    'database': '100',
    'referrals': '100'
};

// Page title mapping for sidebar navigation
const pageTitles = {
    'all': 'All Commissions',
    'traffic': 'Traffic Cases',
    'report': 'Analytics',
    'pipeline': 'Case Tracker',
    'admin-control': 'Admin Control',
    'notifications': 'Notifications',
    'referrals': 'Referrals'
};

// Override shared formatDate - admin version takes a Date object and returns relative time
function formatDate(date) {
    const now = new Date();
    const diff = now - date;
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

    if (days === 0) {
        const hours = Math.floor(diff / (1000 * 60 * 60));
        if (hours === 0) {
            const minutes = Math.floor(diff / (1000 * 60));
            return minutes <= 1 ? 'Just now' : `${minutes}m ago`;
        }
        return `${hours}h ago`;
    } else if (days === 1) {
        return 'Yesterday';
    } else if (days < 7) {
        return `${days}d ago`;
    } else {
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
}

// Status badge helper
function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="stat-badge pending">Pending</span>',
        'approved': '<span class="stat-badge paid">Approved</span>',
        'denied': '<span class="stat-badge rejected">Denied</span>'
    };
    return badges[status] || `<span class="stat-badge">${status}</span>`;
}

// Fixed scrollbar at bottom of screen
function initFixedScrollbar() {
    const fixedScrollbar = document.createElement('div');
    fixedScrollbar.className = 'scrollbar-fixed';
    fixedScrollbar.id = 'fixedScrollbar';
    fixedScrollbar.innerHTML = '<div class="scrollbar-fixed-inner" id="scrollbarInner"></div>';
    document.body.appendChild(fixedScrollbar);

    const wrappers = document.querySelectorAll('.table-scroll-wrapper');

    wrappers.forEach(wrapper => {
        wrapper.addEventListener('scroll', () => {
            if (isElementInViewport(wrapper)) {
                fixedScrollbar.scrollLeft = wrapper.scrollLeft;
            }
        });
    });

    fixedScrollbar.addEventListener('scroll', () => {
        const activeWrapper = getVisibleWrapper();
        if (activeWrapper) {
            activeWrapper.scrollLeft = fixedScrollbar.scrollLeft;
        }
    });

    updateFixedScrollbar();
    window.addEventListener('resize', updateFixedScrollbar);

    const observer = new MutationObserver(updateFixedScrollbar);
    document.querySelectorAll('[id^="content-"]').forEach(el => {
        observer.observe(el, { attributes: true, attributeFilter: ['class'] });
    });
}

function getVisibleWrapper() {
    const wrappers = document.querySelectorAll('.table-scroll-wrapper');
    for (let wrapper of wrappers) {
        if (wrapper.offsetParent !== null && isElementInViewport(wrapper)) {
            return wrapper;
        }
    }
    return null;
}

function isElementInViewport(el) {
    const rect = el.getBoundingClientRect();
    return rect.top < window.innerHeight && rect.bottom > 0;
}

function updateFixedScrollbar() {
    const fixedScrollbar = document.getElementById('fixedScrollbar');
    const scrollbarInner = document.getElementById('scrollbarInner');
    const activeWrapper = getVisibleWrapper();

    if (activeWrapper && fixedScrollbar && scrollbarInner) {
        const table = activeWrapper.querySelector('table');
        if (table) {
            scrollbarInner.style.width = table.scrollWidth + 'px';
            fixedScrollbar.style.display = 'block';
            fixedScrollbar.scrollLeft = activeWrapper.scrollLeft;
        }
    } else if (fixedScrollbar) {
        fixedScrollbar.style.display = 'none';
    }
}

function initMonthDropdown() {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const year = new Date().getFullYear();
    const select = document.getElementById('filterAllMonth');

    months.forEach(m => {
        select.innerHTML += `<option value="${m}. ${year}">${m}. ${year}</option>`;
    });
}

// Spark bar progress indicator (used by overview + performance)
function sparkBar(pct) {
    const color = pct >= 75 ? '#0d9488' : pct >= 50 ? '#d97706' : '#e2e4ea';
    return `<div class="spark-bar">
        <div class="spark" style="width:70px;">
            <div class="spark-fill" style="width:${pct}%; background:${color};"></div>
        </div>
        <span class="spark-pct ${pct === 0 ? 'zero' : ''}">${pct.toFixed(0)}%</span>
    </div>`;
}

// Pace calculation helpers (used by overview + performance)
function getOnPacePercent(year) {
    const now = new Date();
    const currentYear = now.getFullYear();
    if (parseInt(year) < currentYear) return 100;
    if (parseInt(year) > currentYear) return 0;
    const monthsPassed = now.getMonth() + 1;
    return (monthsPassed / 12) * 100;
}

function getPaceColor(actualPct, expectedPct) {
    if (expectedPct === 0) return '#8b8fa3';
    const ratio = actualPct / expectedPct;
    if (ratio >= 0.85) return '#0d9488';
    if (ratio >= 0.6) return '#d97706';
    return '#dc2626';
}

function getPaceLabel(actualPct, expectedPct) {
    if (expectedPct === 0) return '-';
    const ratio = actualPct / expectedPct;
    if (ratio >= 0.85) return 'On Pace';
    if (ratio >= 0.6) return 'Behind';
    return 'Far Behind';
}

function parseMonthForSort(monthStr) {
    if (!monthStr || monthStr === 'TBD') return 0;
    const months = {'jan': 1, 'feb': 2, 'mar': 3, 'apr': 4, 'may': 5, 'jun': 6, 'jul': 7, 'aug': 8, 'sep': 9, 'oct': 10, 'nov': 11, 'dec': 12};
    const match = monthStr.toLowerCase().match(/([a-z]+)\.?\s*(\d{4})/);
    if (match) {
        const monthNum = months[match[1].substring(0, 3)] || 0;
        const year = parseInt(match[2]) || 0;
        return year * 100 + monthNum;
    }
    return 0;
}
