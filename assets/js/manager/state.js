/**
 * Manager dashboard state variables and constants.
 * Extends employee state (loaded before this file).
 */

let allReferrals = [];
let referralUsersCache = null;

// Extend pageTitles for manager-specific tabs
Object.assign(pageTitles, {
    'referrals': 'Referrals',
    'goals': 'Team Goals'
});

// Extend TAB_DEFAULT_WIDTHS
Object.assign(TAB_DEFAULT_WIDTHS, {
    'referrals': '100',
    'goals': '100'
});
