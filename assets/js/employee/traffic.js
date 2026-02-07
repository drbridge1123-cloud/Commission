/**
 * Employee dashboard - Traffic tab functions.
 * Only loaded for Chong (user id == 2).
 */

function initTrafficYearFilter() {
    const yearSelect = document.getElementById('trafficYearFilter');
    if (!yearSelect) return;

    const currentYear = new Date().getFullYear();
    yearSelect.innerHTML = '<option value="all" selected>All</option>';

    // Years from current+2 down to 2019 (when traffic data starts)
    for (let y = currentYear + 2; y >= 2019; y--) {
        const option = document.createElement('option');
        option.value = y;
        option.textContent = y;
        yearSelect.appendChild(option);
    }
}

let trafficCasesInitialized = false;

async function loadTrafficCases() {
    try {
        // Always fetch all cases for stats
        const data = await apiCall(`api/traffic.php?status=all`);

        if (data.csrf_token) {
            csrfToken = data.csrf_token;
        }

        allTrafficCases = data.cases || [];

        // Set default filter to Active on first load
        if (!trafficCasesInitialized) {
            trafficCasesInitialized = true;
            currentSidebarFilter = { type: 'status', value: 'active' };
            document.getElementById('trafficFilterLabel').textContent = 'Active Cases';
        }

        filterTrafficCases();
        updateTrafficBadge();
        // Update stats cards with ALL cases (not filtered)
        updateTrafficStatsCards(allTrafficCases);

        // Also load pending requests (for Chong)
        loadPendingTrafficRequests();
    } catch (err) {
        console.error('Error loading traffic cases:', err);
        document.getElementById('trafficCasesBody').innerHTML =
            '<tr><td colspan="9" style="padding: 32px 16px; text-align: center; color: #dc2626;">Error loading cases</td></tr>';
    }
}

function filterTrafficCases() {
    const searchTerm = document.getElementById('trafficSearch')?.value.toLowerCase() || '';

    // Get base filtered cases from sidebar
    let baseFiltered = allTrafficCases;
    if (currentSidebarFilter) {
        const { type, value } = currentSidebarFilter;
        if (type === 'referral') {
            baseFiltered = allTrafficCases.filter(c => (c.referral_source || 'Unknown') === value);
        } else if (type === 'court') {
            baseFiltered = allTrafficCases.filter(c => c.court === value);
        } else if (type === 'year') {
            baseFiltered = allTrafficCases.filter(c => {
                if (!c.court_date) return false;
                return new Date(c.court_date).getFullYear() === parseInt(value);
            });
        } else if (type === 'status') {
            baseFiltered = allTrafficCases.filter(c => c.status === value);
        }
    }

    // Apply search filter on top
    let filtered = baseFiltered;
    if (searchTerm) {
        filtered = baseFiltered.filter(c => {
            const searchFields = [
                c.client_name,
                c.court,
                c.charge,
                c.case_number,
                c.prosecutor_offer,
                c.referral_source
            ].filter(Boolean).join(' ').toLowerCase();
            return searchFields.includes(searchTerm);
        });
    }

    renderTrafficCases(filtered);
    updateTrafficStats(filtered);
}

function renderTrafficCases(cases) {
    const tbody = document.getElementById('trafficCasesBody');

    if (!cases || cases.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" style="padding: 32px 16px; text-align: center;" class="text-secondary">No traffic cases found</td></tr>';
        document.getElementById('trafficCaseCount').textContent = '0 cases';
        document.getElementById('trafficTotalCommission').textContent = '$0.00';
        return;
    }

    // Sort by court date
    cases.sort((a, b) => {
        if (!a.court_date) return 1;
        if (!b.court_date) return -1;
        return new Date(a.court_date) - new Date(b.court_date);
    });

    let html = '';
    let totalCommission = 0;

    cases.forEach(c => {
        const commission = parseFloat(c.commission) || 0;
        totalCommission += commission;

        const courtDate = c.court_date ? formatTrafficDate(c.court_date) : '-';
        const dispositionClass = getDispositionClass(c.disposition);

        html += `
            <tr>
                <td style="font-weight: 500;">${escapeHtml(c.client_name || '')}</td>
                <td>${escapeHtml(c.court || '-')}</td>
                <td>${courtDate}</td>
                <td>${escapeHtml(c.charge || '-')}</td>
                <td>${escapeHtml(c.case_number || '-')}</td>
                <td style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(c.prosecutor_offer || '')}">${escapeHtml(c.prosecutor_offer || '-')}</td>
                <td><span class="status-badge ${dispositionClass}">${c.disposition || 'pending'}</span></td>
                <td>${escapeHtml(c.referral_source || '-')}</td>
                <td style="text-align: right; font-weight: 600; color: ${commission > 0 ? '#059669' : '#6b7280'};">$${commission.toFixed(2)}</td>
                <td style="text-align: center;">
                    <button onclick="editTrafficCase(${c.id})" class="action-btn edit-btn" title="Edit">\u270F\uFE0F</button>
                    <button onclick="deleteTrafficCase(${c.id})" class="action-btn delete-btn" title="Delete">\uD83D\uDDD1\uFE0F</button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    document.getElementById('trafficCaseCount').textContent = `${cases.length} case${cases.length !== 1 ? 's' : ''}`;
    document.getElementById('trafficTotalCommission').textContent = `$${totalCommission.toFixed(2)}`;
}

function formatTrafficDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    const month = date.toLocaleString('en-US', { month: 'short' });
    const day = date.getDate();
    const year = date.getFullYear();
    const time = date.toLocaleString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    return `${month} ${day}, ${year} ${time}`;
}

function getDispositionClass(disposition) {
    switch (disposition) {
        case 'dismissed': return 'status-paid';
        case 'amended': return 'status-pending';
        case 'other': return 'status-failed';
        default: return 'status-default';
    }
}

// Update top 4 stat cards (filtered data)
function updateTrafficStats(cases) {
    const active = cases.filter(c => c.status === 'active').length;
    const dismissed = cases.filter(c => c.disposition === 'dismissed').length;
    const amended = cases.filter(c => c.disposition === 'amended').length;
    const totalCommission = cases.reduce((sum, c) => sum + (parseFloat(c.commission) || 0), 0);

    document.getElementById('trafficActive').textContent = active;
    document.getElementById('trafficDismissed').textContent = dismissed;
    document.getElementById('trafficAmended').textContent = amended;
    document.getElementById('trafficCommission').textContent = `$${totalCommission.toFixed(0)}`;
}

// Track sidebar state
let currentSidebarTab = 'all';
let currentSidebarFilter = null;

// Switch sidebar tab
function switchSidebarTab(tab) {
    currentSidebarTab = tab;

    // Update tab buttons - Minimal Flat Blue Accent style
    ['all', 'referral', 'court', 'year'].forEach(t => {
        const btn = document.getElementById(`sidebarTab-${t}`);
        if (btn) {
            if (t === tab) {
                btn.style.background = '#ffffff';
                btn.style.color = '#0066ff';
                btn.style.boxShadow = '0 1px 3px rgba(0,0,0,0.08)';
            } else {
                btn.style.background = 'transparent';
                btn.style.color = '#a3acb9';
                btn.style.boxShadow = 'none';
            }
        }
    });

    // Clear filter when switching tabs
    if (tab === 'all') {
        currentSidebarFilter = null;
        document.getElementById('trafficFilterLabel').textContent = 'All Cases';
        renderTrafficCases(allTrafficCases);
        updateTrafficStats(allTrafficCases);
    }

    updateSidebarContent();
}

// Filter by sidebar item click
function filterBySidebar(type, value, label) {
    if (currentSidebarFilter?.type === type && currentSidebarFilter?.value === value) {
        // Toggle off
        currentSidebarFilter = null;
        document.getElementById('trafficFilterLabel').textContent = 'All Cases';
        renderTrafficCases(allTrafficCases);
        updateTrafficStats(allTrafficCases);
    } else {
        currentSidebarFilter = { type, value };
        document.getElementById('trafficFilterLabel').textContent = label;

        let filtered = allTrafficCases;
        if (type === 'referral') {
            filtered = allTrafficCases.filter(c => (c.referral_source || 'Unknown') === value);
        } else if (type === 'court') {
            filtered = allTrafficCases.filter(c => c.court === value);
        } else if (type === 'year') {
            filtered = allTrafficCases.filter(c => {
                if (!c.court_date) return false;
                return new Date(c.court_date).getFullYear() === parseInt(value);
            });
        } else if (type === 'status') {
            filtered = allTrafficCases.filter(c => c.status === value);
        }

        renderTrafficCases(filtered);
        updateTrafficStats(filtered);
    }

    updateSidebarContent();
}

// Update sidebar content based on current tab - Minimal Flat Blue Accent Design
function updateSidebarContent() {
    const container = document.getElementById('sidebarContent');
    if (!container) return;

    let html = '';
    const cases = allTrafficCases;

    // Color palette
    const colors = {
        bg: '#f7f9fc',
        surface: '#ffffff',
        border: '#e5e9f0',
        textPrimary: '#1a1f36',
        textSecondary: '#5e6687',
        textMuted: '#a3acb9',
        accent: '#0066ff',
        accentLight: '#f0f6ff',
        green: '#00a67e',
        amber: '#d97706'
    };

    if (currentSidebarTab === 'all') {
        const activeCount = cases.filter(c => c.status === 'active').length;
        const resolvedCount = cases.filter(c => c.status === 'resolved').length;
        const activeCommission = cases.filter(c => c.status === 'active').reduce((s, c) => s + (parseFloat(c.commission) || 0), 0);
        const resolvedCommission = cases.filter(c => c.status === 'resolved').reduce((s, c) => s + (parseFloat(c.commission) || 0), 0);

        const isAllActive = !currentSidebarFilter;
        const isActiveActive = currentSidebarFilter?.type === 'status' && currentSidebarFilter?.value === 'active';
        const isResolvedActive = currentSidebarFilter?.type === 'status' && currentSidebarFilter?.value === 'resolved';

        html = `
            <div onclick="currentSidebarFilter = null; document.getElementById('trafficFilterLabel').textContent = 'All Cases'; renderTrafficCases(allTrafficCases); updateTrafficStats(allTrafficCases); updateSidebarContent();"
                 style="padding: 12px 16px; cursor: pointer; border-bottom: 1px solid ${colors.border}; transition: background 0.15s; ${isAllActive ? `background: ${colors.accentLight}; border-left: 3px solid ${colors.accent}; padding-left: 13px;` : ''}"
                 onmouseover="this.style.background='${isAllActive ? colors.accentLight : colors.bg}'"
                 onmouseout="this.style.background='${isAllActive ? colors.accentLight : ''}'">
                <div style="font-size: 14px; font-weight: 600; color: ${isAllActive ? colors.accent : colors.textPrimary};">All Cases</div>
                <div style="font-size: 12px; color: ${colors.textMuted}; margin-top: 2px;">${cases.length} cases</div>
            </div>
            <div onclick="filterBySidebar('status', 'active', 'Active Cases')"
                 style="padding: 12px 16px; cursor: pointer; border-bottom: 1px solid ${colors.border}; transition: background 0.15s; ${isActiveActive ? `background: ${colors.accentLight}; border-left: 3px solid ${colors.accent}; padding-left: 13px;` : ''}"
                 onmouseover="this.style.background='${isActiveActive ? colors.accentLight : colors.bg}'"
                 onmouseout="this.style.background='${isActiveActive ? colors.accentLight : ''}'">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 14px; font-weight: 600; color: ${isActiveActive ? colors.accent : colors.textPrimary};">Active</span>
                    <span style="font-size: 13px; font-weight: 600; color: ${colors.accent}; font-family: 'SF Mono', Menlo, monospace;">${activeCount}</span>
                </div>
                <div style="font-size: 12px; font-weight: 600; color: ${colors.green}; font-family: 'SF Mono', Menlo, monospace; margin-top: 2px;">$${activeCommission.toLocaleString()}</div>
            </div>
            <div onclick="filterBySidebar('status', 'resolved', 'Resolved Cases')"
                 style="padding: 12px 16px; cursor: pointer; border-bottom: 1px solid ${colors.border}; transition: background 0.15s; ${isResolvedActive ? `background: ${colors.accentLight}; border-left: 3px solid ${colors.accent}; padding-left: 13px;` : ''}"
                 onmouseover="this.style.background='${isResolvedActive ? colors.accentLight : colors.bg}'"
                 onmouseout="this.style.background='${isResolvedActive ? colors.accentLight : ''}'">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 14px; font-weight: 600; color: ${isResolvedActive ? colors.accent : colors.textPrimary};">Resolved</span>
                    <span style="font-size: 13px; font-weight: 600; color: ${colors.green}; font-family: 'SF Mono', Menlo, monospace;">${resolvedCount}</span>
                </div>
                <div style="font-size: 12px; font-weight: 600; color: ${colors.green}; font-family: 'SF Mono', Menlo, monospace; margin-top: 2px;">$${resolvedCommission.toLocaleString()}</div>
            </div>
        `;
    } else if (currentSidebarTab === 'referral') {
        const stats = {};
        cases.forEach(c => {
            const ref = c.referral_source || 'Unknown';
            if (!stats[ref]) stats[ref] = { count: 0, commission: 0, dismissed: 0, amended: 0 };
            stats[ref].count++;
            stats[ref].commission += parseFloat(c.commission) || 0;
            if (c.disposition === 'dismissed') stats[ref].dismissed++;
            if (c.disposition === 'amended') stats[ref].amended++;
        });
        const sorted = Object.entries(stats).sort((a, b) => b[1].commission - a[1].commission);

        sorted.forEach(([name, data]) => {
            const isActive = currentSidebarFilter?.type === 'referral' && currentSidebarFilter?.value === name;
            html += `
                <div onclick="filterBySidebar('referral', '${name.replace(/'/g, "\\'")}', 'Referral: ${escapeHtml(name)}')"
                     style="padding: 12px 16px; cursor: pointer; border-bottom: 1px solid ${colors.border}; transition: background 0.15s; ${isActive ? `background: ${colors.accentLight}; border-left: 3px solid ${colors.accent}; padding-left: 13px;` : ''}"
                     onmouseover="this.style.background='${isActive ? colors.accentLight : colors.bg}'"
                     onmouseout="this.style.background='${isActive ? colors.accentLight : ''}'">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 14px; font-weight: 600; color: ${isActive ? colors.accent : colors.textPrimary}; flex: 1;">${escapeHtml(name)}</span>
                        <div style="display: flex; gap: 5px;">
                            ${data.dismissed > 0 ? `<span style="font-size: 10px; font-weight: 600; font-family: 'SF Mono', Menlo, monospace; padding: 3px 7px; border-radius: 4px; background: ${isActive ? '#fff' : colors.bg}; color: ${colors.green};">D${data.dismissed}</span>` : ''}
                            ${data.amended > 0 ? `<span style="font-size: 10px; font-weight: 600; font-family: 'SF Mono', Menlo, monospace; padding: 3px 7px; border-radius: 4px; background: ${isActive ? '#fff' : colors.bg}; color: ${colors.amber};">A${data.amended}</span>` : ''}
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                        <span style="font-size: 13px; font-weight: 600; color: ${colors.green}; font-family: 'SF Mono', Menlo, monospace;">$${data.commission.toLocaleString()}</span>
                        <span style="font-size: 10px; color: ${colors.textMuted};">${data.count} cases</span>
                    </div>
                </div>
            `;
        });
    } else if (currentSidebarTab === 'court') {
        const stats = {};
        cases.forEach(c => {
            const court = c.court || 'Unknown';
            if (!stats[court]) stats[court] = { count: 0, commission: 0, dismissed: 0, amended: 0 };
            stats[court].count++;
            stats[court].commission += parseFloat(c.commission) || 0;
            if (c.disposition === 'dismissed') stats[court].dismissed++;
            if (c.disposition === 'amended') stats[court].amended++;
        });
        const sorted = Object.entries(stats).sort((a, b) => b[1].commission - a[1].commission);

        sorted.forEach(([name, data]) => {
            const isActive = currentSidebarFilter?.type === 'court' && currentSidebarFilter?.value === name;
            html += `
                <div onclick="filterBySidebar('court', '${name.replace(/'/g, "\\'")}', 'Court: ${escapeHtml(name)}')"
                     style="padding: 12px 16px; cursor: pointer; border-bottom: 1px solid ${colors.border}; transition: background 0.15s; ${isActive ? `background: ${colors.accentLight}; border-left: 3px solid ${colors.accent}; padding-left: 13px;` : ''}"
                     onmouseover="this.style.background='${isActive ? colors.accentLight : colors.bg}'"
                     onmouseout="this.style.background='${isActive ? colors.accentLight : ''}'">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 14px; font-weight: 600; color: ${isActive ? colors.accent : colors.textPrimary}; flex: 1;">${escapeHtml(name)}</span>
                        <div style="display: flex; gap: 5px;">
                            ${data.dismissed > 0 ? `<span style="font-size: 10px; font-weight: 600; font-family: 'SF Mono', Menlo, monospace; padding: 3px 7px; border-radius: 4px; background: ${isActive ? '#fff' : colors.bg}; color: ${colors.green};">D${data.dismissed}</span>` : ''}
                            ${data.amended > 0 ? `<span style="font-size: 10px; font-weight: 600; font-family: 'SF Mono', Menlo, monospace; padding: 3px 7px; border-radius: 4px; background: ${isActive ? '#fff' : colors.bg}; color: ${colors.amber};">A${data.amended}</span>` : ''}
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                        <span style="font-size: 13px; font-weight: 600; color: ${colors.green}; font-family: 'SF Mono', Menlo, monospace;">$${data.commission.toLocaleString()}</span>
                        <span style="font-size: 10px; color: ${colors.textMuted};">${data.count} cases</span>
                    </div>
                </div>
            `;
        });
    } else if (currentSidebarTab === 'year') {
        const stats = {};
        cases.forEach(c => {
            if (c.court_date) {
                const year = new Date(c.court_date).getFullYear();
                if (!stats[year]) stats[year] = { count: 0, commission: 0, dismissed: 0, amended: 0 };
                stats[year].count++;
                stats[year].commission += parseFloat(c.commission) || 0;
                if (c.disposition === 'dismissed') stats[year].dismissed++;
                if (c.disposition === 'amended') stats[year].amended++;
            }
        });
        const sorted = Object.entries(stats).sort((a, b) => b[0] - a[0]);

        sorted.forEach(([year, data]) => {
            const isActive = currentSidebarFilter?.type === 'year' && currentSidebarFilter?.value === year;
            html += `
                <div onclick="filterBySidebar('year', '${year}', 'Year: ${year}')"
                     style="padding: 12px 16px; cursor: pointer; border-bottom: 1px solid ${colors.border}; transition: background 0.15s; ${isActive ? `background: ${colors.accentLight}; border-left: 3px solid ${colors.accent}; padding-left: 13px;` : ''}"
                     onmouseover="this.style.background='${isActive ? colors.accentLight : colors.bg}'"
                     onmouseout="this.style.background='${isActive ? colors.accentLight : ''}'">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 44px; font-size: 14px; font-weight: 600; color: ${isActive ? colors.accent : colors.textPrimary};">${year}</span>
                        <div style="display: flex; gap: 5px; flex: 1;">
                            ${data.dismissed > 0 ? `<span style="font-size: 10px; font-weight: 600; font-family: 'SF Mono', Menlo, monospace; padding: 3px 7px; border-radius: 4px; background: ${isActive ? '#fff' : colors.bg}; color: ${colors.green};">D${data.dismissed}</span>` : ''}
                            ${data.amended > 0 ? `<span style="font-size: 10px; font-weight: 600; font-family: 'SF Mono', Menlo, monospace; padding: 3px 7px; border-radius: 4px; background: ${isActive ? '#fff' : colors.bg}; color: ${colors.amber};">A${data.amended}</span>` : ''}
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 13px; font-weight: 600; color: ${colors.green}; font-family: 'SF Mono', Menlo, monospace;">$${data.commission.toLocaleString()}</div>
                            <div style="font-size: 10px; color: ${colors.textMuted};">${data.count} cases</div>
                        </div>
                    </div>
                </div>
            `;
        });
    }

    container.innerHTML = html || `<div style="padding: 16px; color: ${colors.textMuted}; text-align: center;">No data</div>`;
}

// Update Quick Stats card - Minimal Flat Blue Accent Design
function updateTrafficStatsCards(cases) {
    const dismissed = cases.filter(c => c.disposition === 'dismissed').length;
    const amended = cases.filter(c => c.disposition === 'amended').length;
    const totalCommission = cases.reduce((sum, c) => sum + (parseFloat(c.commission) || 0), 0);
    const resolvedCases = cases.filter(c => c.disposition === 'dismissed' || c.disposition === 'amended');
    const avgPerCase = resolvedCases.length > 0 ? totalCommission / resolvedCases.length : 0;
    const dismissRate = resolvedCases.length > 0 ? Math.round((dismissed / resolvedCases.length) * 100) : 0;

    // Color palette
    const colors = {
        bg: '#f7f9fc',
        surface: '#ffffff',
        border: '#e5e9f0',
        textPrimary: '#1a1f36',
        textMuted: '#a3acb9'
    };

    let quickStatsHtml = `
        <div style="font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: ${colors.textMuted}; margin-bottom: 8px;">STATS</div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
            <div style="background: ${colors.surface}; border: 1px solid ${colors.border}; border-radius: 6px; padding: 10px 8px; text-align: center;">
                <div style="font-size: 13px; font-weight: 600; color: ${colors.textPrimary}; font-family: 'SF Mono', Menlo, monospace;">$${Math.round(avgPerCase)}</div>
                <div style="font-size: 9px; text-transform: uppercase; color: ${colors.textMuted}; margin-top: 2px;">AVG</div>
            </div>
            <div style="background: ${colors.surface}; border: 1px solid ${colors.border}; border-radius: 6px; padding: 10px 8px; text-align: center;">
                <div style="font-size: 13px; font-weight: 600; color: ${colors.textPrimary}; font-family: 'SF Mono', Menlo, monospace;">${dismissRate}%</div>
                <div style="font-size: 9px; text-transform: uppercase; color: ${colors.textMuted}; margin-top: 2px;">DISMISS</div>
            </div>
            <div style="background: ${colors.surface}; border: 1px solid ${colors.border}; border-radius: 6px; padding: 10px 8px; text-align: center;">
                <div style="font-size: 13px; font-weight: 600; color: ${colors.textPrimary}; font-family: 'SF Mono', Menlo, monospace;">${cases.length}</div>
                <div style="font-size: 9px; text-transform: uppercase; color: ${colors.textMuted}; margin-top: 2px;">TOTAL</div>
            </div>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 10px; border-top: 1px solid ${colors.border}; font-size: 11px;">
            <span style="color: ${colors.textMuted};">D: ${dismissed}</span>
            <span style="color: ${colors.textMuted};">A: ${amended}</span>
            <span style="color: #00a67e; font-weight: 600; font-family: 'SF Mono', Menlo, monospace;">$${totalCommission.toLocaleString()}</span>
        </div>
    `;
    document.getElementById('trafficQuickStats').innerHTML = quickStatsHtml;

    // Also update sidebar content
    updateSidebarContent();
}

function updateTrafficBadge() {
    const badge = document.getElementById('trafficBadge');
    if (!badge) return;

    const activeCount = allTrafficCases.filter(c => c.status === 'active').length;
    if (activeCount > 0) {
        badge.textContent = activeCount;
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

function openAddTrafficModal() {
    document.getElementById('trafficModalTitle').textContent = 'Add Traffic Case';
    document.getElementById('trafficForm').reset();
    document.getElementById('trafficCaseId').value = '';
    document.getElementById('trafficDisposition').value = 'pending';
    document.getElementById('trafficStatus').value = 'active';
    document.getElementById('trafficNoaSentDate').value = '';
    document.getElementById('trafficDiscovery').checked = false;
    updateTrafficCommission();
    document.getElementById('trafficModal').classList.add('show');
}

function closeTrafficModal() {
    document.getElementById('trafficModal').classList.remove('show');
}

function updateTrafficCommission() {
    const disposition = document.getElementById('trafficDisposition').value;
    let commission = 0;

    if (disposition === 'dismissed') {
        commission = 150;
    } else if (disposition === 'amended') {
        commission = 100;
    }

    document.getElementById('trafficCommissionDisplay').textContent = `$${commission.toFixed(2)}`;
}

async function saveTrafficCase() {
    const caseId = document.getElementById('trafficCaseId').value;
    const data = {
        client_name: document.getElementById('trafficClientName').value,
        client_phone: document.getElementById('trafficClientPhone').value,
        court: document.getElementById('trafficCourt').value,
        court_date: document.getElementById('trafficCourtDate').value || null,
        charge: document.getElementById('trafficCharge').value,
        case_number: document.getElementById('trafficCaseNumber').value,
        prosecutor_offer: document.getElementById('trafficOffer').value,
        disposition: document.getElementById('trafficDisposition').value,
        status: document.getElementById('trafficStatus').value,
        note: document.getElementById('trafficNote').value,
        referral_source: document.getElementById('trafficReferralSource').value,
        paid: document.getElementById('trafficPaid').checked ? 1 : 0,
        noa_sent_date: document.getElementById('trafficNoaSentDate').value || null,
        discovery: document.getElementById('trafficDiscovery').checked ? 1 : 0
    };

    if (!data.client_name) {
        alert('Client name is required');
        return;
    }

    try {
        const method = caseId ? 'PUT' : 'POST';
        if (caseId) data.id = parseInt(caseId);

        const result = await apiCall('api/traffic.php', {
            method: method,
            body: JSON.stringify(data)
        });

        if (result.success) {
            closeTrafficModal();
            loadTrafficCases();
        } else {
            alert(result.error || 'Error saving case');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error saving case');
    }
}

function editTrafficCase(id) {
    const trafficCase = allTrafficCases.find(c => c.id == id);
    if (!trafficCase) return;

    document.getElementById('trafficModalTitle').textContent = 'Edit Traffic Case';
    document.getElementById('trafficCaseId').value = id;
    document.getElementById('trafficClientName').value = trafficCase.client_name || '';
    document.getElementById('trafficClientPhone').value = trafficCase.client_phone || '';
    document.getElementById('trafficCourt').value = trafficCase.court || '';

    // Format datetime for input
    if (trafficCase.court_date) {
        const dt = new Date(trafficCase.court_date);
        const formatted = dt.toISOString().slice(0, 16);
        document.getElementById('trafficCourtDate').value = formatted;
    } else {
        document.getElementById('trafficCourtDate').value = '';
    }

    document.getElementById('trafficCharge').value = trafficCase.charge || '';
    document.getElementById('trafficCaseNumber').value = trafficCase.case_number || '';
    document.getElementById('trafficOffer').value = trafficCase.prosecutor_offer || '';
    document.getElementById('trafficDisposition').value = trafficCase.disposition || 'pending';
    document.getElementById('trafficStatus').value = trafficCase.status || 'active';
    document.getElementById('trafficNote').value = trafficCase.note || '';
    document.getElementById('trafficReferralSource').value = trafficCase.referral_source || '';
    document.getElementById('trafficPaid').checked = trafficCase.paid == 1;
    document.getElementById('trafficNoaSentDate').value = trafficCase.noa_sent_date || '';
    document.getElementById('trafficDiscovery').checked = trafficCase.discovery == 1;

    updateTrafficCommission();
    document.getElementById('trafficModal').classList.add('show');
}

async function deleteTrafficCase(id) {
    if (!confirm('Are you sure you want to delete this traffic case?')) return;

    try {
        const result = await apiCall('api/traffic.php', {
            method: 'DELETE',
            body: JSON.stringify({ id: id })
        });

        if (result.success) {
            loadTrafficCases();
        } else {
            alert(result.error || 'Error deleting case');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error deleting case');
    }
}

// ===== TRAFFIC REQUEST FUNCTIONS (Chong) =====
let pendingTrafficRequests = [];

async function loadPendingTrafficRequests() {
    try {
        const data = await apiCall('api/traffic_requests.php?status=pending');
        pendingTrafficRequests = data.requests || [];
        renderPendingTrafficRequests();

        // Update badge on Traffic tab
        const badge = document.getElementById('trafficBadge');
        if (badge) {
            if (pendingTrafficRequests.length > 0) {
                badge.textContent = pendingTrafficRequests.length;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    } catch (err) {
        console.error('Error loading pending requests:', err);
    }
}

function renderPendingTrafficRequests() {
    const section = document.getElementById('pendingRequestsSection');
    const list = document.getElementById('pendingRequestsList');
    const countBadge = document.getElementById('pendingRequestCount');

    if (!section || !list) return;

    if (pendingTrafficRequests.length === 0) {
        section.style.display = 'none';
        return;
    }

    section.style.display = 'block';
    countBadge.textContent = pendingTrafficRequests.length;

    list.innerHTML = pendingTrafficRequests.map(r => {
        const courtDate = r.court_date ? new Date(r.court_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'No date';

        return `
            <div style="padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 600; color: #111827;">${r.client_name}</div>
                        <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">From: ${r.requester_name}</div>
                    </div>
                </div>
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 8px;">
                    ${r.court || 'No court'} \u2022 ${courtDate}
                    ${r.charge ? '<br>' + r.charge : ''}
                </div>
                ${r.note ? `<div style="font-size: 11px; color: #6b7280; background: #f9fafb; padding: 6px 8px; border-radius: 4px; margin-bottom: 8px;">${r.note}</div>` : ''}
                <div style="display: flex; gap: 8px;">
                    <button onclick="acceptTrafficRequest(${r.id})" style="flex: 1; padding: 6px 12px; background: #10b981; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">Accept</button>
                    <button onclick="showDenyModal(${r.id})" style="flex: 1; padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">Deny</button>
                </div>
            </div>
        `;
    }).join('');
}

async function acceptTrafficRequest(requestId) {
    if (!confirm('Accept this traffic case request?')) return;

    try {
        const result = await apiCall('api/traffic_requests.php', {
            method: 'PUT',
            body: JSON.stringify({ id: requestId, action: 'accept' })
        });

        if (result.success) {
            alert('Request accepted! Case added to your traffic cases.');
            loadPendingTrafficRequests();
            loadTrafficCases();
        } else {
            alert(result.error || 'Error accepting request');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error accepting request');
    }
}

function showDenyModal(requestId) {
    const reason = prompt('Please enter the reason for denying this request (required):');
    if (reason === null) return; // Cancelled
    if (!reason.trim()) {
        alert('Deny reason is required');
        return;
    }
    denyTrafficRequest(requestId, reason.trim());
}

async function denyTrafficRequest(requestId, reason) {
    try {
        const result = await apiCall('api/traffic_requests.php', {
            method: 'PUT',
            body: JSON.stringify({ id: requestId, action: 'deny', deny_reason: reason })
        });

        if (result.success) {
            alert('Request denied. The requester has been notified.');
            loadPendingTrafficRequests();
        } else {
            alert(result.error || 'Error denying request');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error denying request');
    }
}
