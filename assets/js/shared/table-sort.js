/**
 * Universal Table Sort — auto-binds to any <th data-sort="type">
 * Types: text, number, date
 * Add data-sort-default="asc|desc" to set initial sort on page load.
 * Add data-no-sort or omit data-sort to skip a column.
 *
 * Usage: just include this script — it auto-initializes on DOMContentLoaded
 * and observes DOM for dynamically added tables.
 */

(function () {
    'use strict';

    const SORT_ASC = 'asc';
    const SORT_DESC = 'desc';

    /**
     * Parse cell value based on sort type
     */
    function parseCellValue(cell, type) {
        const text = (cell.textContent || '').trim();

        if (type === 'number') {
            // Strip $, commas, %, whitespace — parse as float
            const num = parseFloat(text.replace(/[$,%\s,]/g, '')) || 0;
            return num;
        }

        if (type === 'date') {
            // Handle month names (e.g. "September", "Jan", "Oct. 2025")
            const monthNames = {'january':1,'february':2,'march':3,'april':4,'may':5,'june':6,'july':7,'august':8,'september':9,'october':10,'november':11,'december':12,'jan':1,'feb':2,'mar':3,'apr':4,'jun':6,'jul':7,'aug':8,'sep':9,'oct':10,'nov':11,'dec':12};
            const lc = text.replace(/[.]/g, '').toLowerCase().trim();
            const parts = lc.split(/\s+/);
            if (monthNames[parts[0]]) {
                const year = parts[1] ? parseInt(parts[1]) : 0;
                return year * 100 + monthNames[parts[0]];
            }
            // Handle various date formats
            const d = new Date(text);
            return isNaN(d.getTime()) ? 0 : d.getTime();
        }

        // Default: text (case-insensitive)
        return text.toLowerCase();
    }

    /**
     * Sort a table by column index
     */
    function sortTable(table, colIndex, type, direction) {
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));

        // Skip if only 1 row or empty/loading row
        if (rows.length <= 1) {
            const firstCell = rows[0]?.querySelector('td');
            if (firstCell && (firstCell.colSpan > 1 || firstCell.classList.contains('cc-empty') || firstCell.classList.contains('tv3-empty'))) {
                return;
            }
        }

        const mult = direction === SORT_ASC ? 1 : -1;

        rows.sort((a, b) => {
            const cellA = a.cells[colIndex];
            const cellB = b.cells[colIndex];
            if (!cellA || !cellB) return 0;

            const valA = parseCellValue(cellA, type);
            const valB = parseCellValue(cellB, type);

            if (type === 'text') {
                return mult * valA.localeCompare(valB);
            }
            return mult * (valA - valB);
        });

        // Re-append rows in sorted order
        rows.forEach(row => tbody.appendChild(row));
    }

    /**
     * Update sort indicators on headers
     */
    function updateIndicators(table, activeTh, direction) {
        table.querySelectorAll('th[data-sort]').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc', 'sort-active');
        });
        activeTh.classList.add('sort-active', direction === SORT_ASC ? 'sort-asc' : 'sort-desc');
    }

    /**
     * Initialize sort on a single table
     */
    function initTable(table) {
        if (table._sortInitialized) return;
        table._sortInitialized = true;

        const headers = table.querySelectorAll('th[data-sort]');
        if (!headers.length) return;

        headers.forEach(th => {
            th.style.cursor = 'pointer';
            th.setAttribute('title', 'Click to sort');

            th.addEventListener('click', function () {
                const colIndex = Array.from(th.parentElement.children).indexOf(th);
                const type = th.dataset.sort || 'text';

                // Toggle direction
                let dir;
                if (th.classList.contains('sort-asc')) {
                    dir = SORT_DESC;
                } else if (th.classList.contains('sort-desc')) {
                    dir = SORT_ASC;
                } else {
                    // Default: number/date start desc, text starts asc
                    dir = (type === 'number' || type === 'date') ? SORT_DESC : SORT_ASC;
                }

                sortTable(table, colIndex, type, dir);
                updateIndicators(table, th, dir);
            });
        });
    }

    /**
     * Scan and initialize all tables
     */
    function initAll() {
        document.querySelectorAll('table').forEach(initTable);
    }

    // Init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    // Observe DOM for dynamically added tables
    const observer = new MutationObserver(function (mutations) {
        let shouldScan = false;
        for (const m of mutations) {
            for (const node of m.addedNodes) {
                if (node.nodeType === 1) {
                    if (node.tagName === 'TABLE' || node.querySelector?.('table')) {
                        shouldScan = true;
                        break;
                    }
                }
            }
            if (shouldScan) break;
        }
        if (shouldScan) initAll();
    });
    observer.observe(document.body || document.documentElement, { childList: true, subtree: true });

    // Expose for manual re-init after dynamic content load
    window.initTableSort = initAll;
})();
