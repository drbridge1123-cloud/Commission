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
let adminTrafficFilter = 'active';
let adminTrafficSearchTerm = '';
let adminTrafficSidebarTab = 'all';
let editingTrafficCaseId = null;

// Messages state
let currentViewingMessageAdmin = null;
let allMessagesAdmin = [];
let allItems = [];

// Delete confirmation state
let pendingDeleteType = null;
let pendingDeleteId = null;

// Chart instances
let perfChartInstance = null;
let trendChartInstance = null;
let statusChartInstance = null;
let reportCharts = { monthly: null, counsel: null, caseType: null };

// Goals state
let goalsYearFilterInit = false;

// Default widths for each tab (Admin page)
const TAB_DEFAULT_WIDTHS = {
    'pending': '100',
    'all': '100',
    'dashboard': '100',
    'report': '100',
    'notifications': '100',
    'history': '100',
    'traffic': '100',
    'admin-control': '100',
    'performance': '100',
    'goals': '100',
    'deadline-requests': '100',
    'database': '100'
};

// Page title mapping for sidebar navigation
const pageTitles = {
    'pending': 'Pending Review',
    'deadline-requests': 'Deadline Requests',
    'all': 'All Cases',
    'traffic': 'Traffic Cases',
    'dashboard': 'Dashboard',
    'report': 'Reports',
    'performance': 'Performance Analytics',
    'goals': 'Employee Goals',
    'admin-control': 'Admin Control',
    'history': 'History',
    'notifications': 'Notifications'
};

// Helper function for API calls with CSRF token
async function apiCall(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        }
    };

    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers
        }
    };

    const response = await fetch(url, mergedOptions);
    const text = await response.text();

    let data;
    try {
        data = JSON.parse(text);
    } catch (e) {
        console.error('Invalid JSON response:', text.substring(0, 500));
        throw new Error('Server returned invalid response. Check PHP error logs.');
    }

    // Update CSRF token if provided in response
    if (data.csrf_token) {
        csrfToken = data.csrf_token;
    }

    if (!response.ok) {
        throw new Error(data.error || 'Request failed');
    }

    return data;
}

// Override shared formatCurrency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0);
}

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

// Width control functions (admin uses 'adminWidth' localStorage key)
function setWidth(width) {
    const container = document.getElementById('mainContent');
    if (container) {
        container.className = 'page-content w-' + width;
    }

    // Update active button
    document.querySelectorAll('.sz-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-width') === width) {
            btn.classList.add('active');
        }
    });

    // Save preference
    localStorage.setItem('adminWidth', width);
}

function loadWidthPreference() {
    const savedWidth = localStorage.getItem('adminWidth') || '100';
    setWidth(savedWidth);
}

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('show');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        modal.classList.add('hidden');
    }
}

// Toast notification
function showNotification(message, type = 'info') {
    const existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
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
