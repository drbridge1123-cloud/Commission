/**
 * Shared shell/layout functions for width management, modals, and toasts.
 * Used across BridgeLaw, Admin, and Chong dashboards.
 */

// Width storage key — override per page before calling loadWidthPreference()
let STORAGE_WIDTH_KEY = 'dashboardWidth';

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
    localStorage.setItem(STORAGE_WIDTH_KEY, width);
}

function loadWidthPreference() {
    const savedWidth = localStorage.getItem(STORAGE_WIDTH_KEY) || '100';
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
