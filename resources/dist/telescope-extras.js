(function () {
    'use strict';

    var FILTER_CLASS = 'telescope-extras-query-filter';

    /**
     * Match the needles in order, "%" works like SQL LIKE's wildcard.
     */
    function isMatch(source, needles) {
        var currentIndex = 0;

        for (var i = 0; i < needles.length; i++) {
            if (needles[i] === '') continue;

            var foundIndex = source.indexOf(needles[i], currentIndex);

            if (foundIndex === -1) return false;

            currentIndex = foundIndex + needles[i].length;
        }

        return true;
    }

    /**
     * The related entries card renders one table per tab; the queries one
     * links each row to the query preview screen.
     */
    function findQueriesTable(card) {
        var link = card.querySelector('table tbody a[href*="/queries/"]');

        return link ? link.closest('table') : null;
    }

    function applyFilter(table, value) {
        var needles = value.toLowerCase().split('%');

        table.querySelectorAll('tbody > tr').forEach(function (row) {
            var cell = row.querySelector('td[title]');
            var sql = (cell ? cell.getAttribute('title') : row.textContent).toLowerCase();

            setDisplay(row, value === '' || isMatch(sql, needles));
        });
    }

    /**
     * Only write on change, so our own writes don't keep the observer busy.
     */
    function setDisplay(element, visible) {
        var display = visible ? '' : 'none';

        if (element.style.display !== display) element.style.display = display;
    }

    function createFilter() {
        var wrapper = document.createElement('li');
        wrapper.className = FILTER_CLASS + ' ml-auto mr-3 d-flex align-items-center';
        wrapper.innerHTML =
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon text-muted" fill="currentColor">' +
            '<path fill-rule="evenodd" clip-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"/>' +
            '</svg>' +
            '<input type="text" class="form-control" placeholder="Search Query" ' +
            'title="Filter SQL queries. Use % as a wildcard for flexible matching">';

        return wrapper;
    }

    function sync() {
        document.querySelectorAll('.card > ul.nav-pills').forEach(function (nav) {
            var table = findQueriesTable(nav.parentElement);
            var filter = nav.querySelector('.' + FILTER_CLASS);

            if (! table) {
                if (filter) filter.remove();
                return;
            }

            if (! filter) {
                filter = createFilter();
                filter.querySelector('input').addEventListener('input', function (event) {
                    applyFilter(findQueriesTable(nav.parentElement), event.target.value);
                });
                nav.appendChild(filter);
            }

            // Vue toggles tabs with v-show, so follow the queries table's visibility.
            setDisplay(filter, table.style.display !== 'none');

            // Vue reuses rows when entries change, so re-apply on every render.
            applyFilter(table, filter.querySelector('input').value);
        });
    }

    // Records are already batched per microtask, and sync() is idempotent.
    new MutationObserver(sync).observe(document.body, {
        childList: true,
        subtree: true,
        characterData: true,
        // Tab switches only flip v-show's inline style.
        attributes: true,
        attributeFilter: ['style'],
    });
})();
