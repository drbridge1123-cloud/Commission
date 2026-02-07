/**
 * Shared shell/layout functions for width management.
 * Used across BridgeLaw, Admin, and Chong dashboards.
 */

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
    localStorage.setItem('dashboardWidth', width);
}

function loadWidthPreference() {
    const savedWidth = localStorage.getItem('dashboardWidth') || '100';
    setWidth(savedWidth);
}
