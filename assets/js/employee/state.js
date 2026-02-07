/**
 * Employee dashboard state variables and constants.
 * NOTE: USER and csrfToken are set inline via PHP.
 */

let allCases = [];
let allMessages = [];
let lastCheckedTime = new Date().toISOString();
let sortColumn = null;
let sortDirection = 'asc';
let currentStatusFilter = 'all';
let notifCurrentFilter = 'all';
let allTrafficCases = [];

// Default widths for each tab (Employee page)
const TAB_DEFAULT_WIDTHS = {
    'cases': '100',
    'reports': '100',
    'history': '100',
    'notifications': '100',
    'goals': '100',
    'traffic': '100'
};

// Page title mapping for sidebar navigation
const pageTitles = {
    'cases': 'My Cases',
    'reports': 'Reports',
    'history': 'History',
    'notifications': 'Notifications',
    'goals': 'My Goals',
    'traffic': 'Traffic Cases',
    'traffic-requests': 'Traffic Requests'
};
