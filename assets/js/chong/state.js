/**
 * ChongDashboard state variables, configuration, and Chong-specific utilities.
 * NOTE: csrfToken is set inline via PHP before this file loads.
 */

// Data stores
let commissionsData = [];
let demandCasesData = [];
let litigationCasesData = [];

// Sort state
let commissionsSortColumn = 'month';
let commissionsSortDir = 'desc';
let demandSortColumn = 'demand_deadline';
let demandSortDir = 'asc';
let litigationSortColumn = 'litigation_start_date';
let litigationSortDir = 'desc';
let trafficSortColumn = 'court_date';
let trafficSortDir = 'desc';

// Filter state
let currentDemandFilter = 'all';
let currentLitigationFilter = 'all';
let currentCommissionStatus = 'all';
let notifCurrentFilter = 'all';

// Messages state
let messagesData = [];
let currentMessageId = null;
let adminUserId = null;

// Edit case state
let currentEditCaseData = null;
let currentPendingExtension = null;

// Chart instances
let commissionChart = null;
let breakdownChart = null;

// Traffic sub-tab state
let currentTrafficSubTab = 'cases';
let commTrafficSortColumn = 'court_date';
let commTrafficSortDir = 'desc';
let currentCommTrafficFilter = 'all';

// Traffic requests state
let allTrafficRequests = [];
let pendingTrafficRequests = [];
let currentRequestFilter = 'all';

// Traffic cases state
let trafficCasesData = [];
let currentSidebarTab = 'all';
let currentTrafficFilter = null;
let currentTrafficStatusFilter = 'active';

// Demand state
let selectedDemandCaseId = null;

// Resolution type configurations
const resolutionConfig = {
    'File and Bump': { feeRate: 33.33, commRate: 20, deductPresuit: true },
    'Post Deposition Settle': { feeRate: 33.33, commRate: 20, deductPresuit: true },
    'Mediation': { feeRate: 33.33, commRate: 20, deductPresuit: true },
    'Settled Post Arbitration': { feeRate: 33.33, commRate: 20, deductPresuit: true },
    'Settlement Conference': { feeRate: 33.33, commRate: 20, deductPresuit: true },
    'Arbitration Award': { feeRate: 40, commRate: 20, deductPresuit: false },
    'Beasley': { feeRate: 40, commRate: 20, deductPresuit: false },
    'Co-Counsel': { feeRate: 0, commRate: 0, variable: true },
    'Other': { feeRate: 0, commRate: 0, variable: true },
    'No Offer Settle': { feeRate: 0, commRate: 0, variable: true }
};

// Page title mapping
const pageTitles = {
    'dashboard': 'Dashboard',
    'commissions': 'Commissions',
    'demand': 'Demand Cases',
    'litigation': 'Litigation Cases',
    'traffic': 'Traffic Cases',
    'notifications': 'Notifications',
    'reports': 'Reports'
};

// Chong-specific API call (positional parameters, different from shared api.js)
async function apiCall(url, method = 'GET', data = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        }
    };
    if (data) {
        options.body = JSON.stringify(data);
    }
    const response = await fetch(url, options);
    const result = await response.json();
    if (result.csrf_token) {
        csrfToken = result.csrf_token;
    }
    return result;
}

// Toast notification
function showToast(message, type = 'info') {
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

// Override shared utils with Chong's original formats
function formatCurrency(amount) {
    return '$' + parseFloat(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
}

function formatRelativeTime(dateStr) {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = now - date;
    const mins = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days === 1) return 'Yesterday';
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

// Chong-specific utilities
function escapeJs(str) {
    if (!str) return '';
    return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"');
}

function formatFileSize(bytes) {
    if (!bytes) return '0 B';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    if (ext === 'pdf') return '📄';
    if (['doc', 'docx'].includes(ext)) return '📝';
    if (['xls', 'xlsx'].includes(ext)) return '📊';
    if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) return '🖼️';
    return '📎';
}

// Modal functions (used across all Chong JS files)
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
