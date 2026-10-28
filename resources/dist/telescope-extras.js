(function () {
    'use strict';

    var FILTER_CLASS = 'telescope-extras-query-filter';
    var DUPLICATE_CLASS = 'telescope-extras-duplicate-query';
    var CURL_CLASS = 'telescope-extras-copy-curl';

    // Needles must appear in order, like SQL LIKE with "%".
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

    function findQueriesTable(card) {
        var link = card.querySelector('table tbody a[href*="/queries/"]');

        return link ? link.closest('table') : null;
    }

    function applyFilter(table, value) {
        var needles = value.toLowerCase().split('%');

        table.querySelectorAll('tbody > tr').forEach(function (row) {
            var sql = row.querySelector('td[title]').getAttribute('title').toLowerCase();

            setDisplay(row, value === '' || isMatch(sql, needles));
        });
    }

    function highlightDuplicates(card, table) {
        var vm = card.__vue__;
        var duplicateIds = {};
        var seen = {};

        if (vm && Array.isArray(vm.queries)) {
            vm.queries.forEach(function (q) {
                var key = q.content ? (q.content.hash || q.content.sql) + '-' + (q.content.connection || '') : null;
                if (key) {
                    if (seen[key]) duplicateIds[q.id] = true;
                    else seen[key] = true;
                }
            });
        }

        table.querySelectorAll('tbody > tr').forEach(function (row) {
            var isDup = false;

            if (vm && Array.isArray(vm.queries)) {
                var link = row.querySelector('a[href*="/queries/"]');
                var id = link ? link.getAttribute('href').split('/').pop() : null;
                isDup = !!(id && duplicateIds[id]);
            } else {
                var td = row.querySelector('td[title]');
                var sql = td ? td.getAttribute('title') : null;
                if (sql) {
                    if (seen[sql]) isDup = true;
                    else seen[sql] = true;
                }
            }

            if (row.classList.contains(DUPLICATE_CLASS) !== isDup) {
                row.classList.toggle(DUPLICATE_CLASS, isDup);
            }
        });
    }

    // Write only on change, otherwise our own writes re-trigger the observer.
    function setDisplay(element, visible) {
        var display = visible ? '' : 'none';

        if (element.style.display !== display) element.style.display = display;
    }

    function createFilter() {
        var wrapper = document.createElement('li');
        // No "d-flex": its display !important would beat the inline display:none.
        wrapper.className = FILTER_CLASS + ' ml-auto mr-3';
        wrapper.innerHTML =
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon text-muted" fill="currentColor">' +
            '<path fill-rule="evenodd" clip-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"/>' +
            '</svg>' +
            '<input type="text" class="form-control" placeholder="Search Query" ' +
            'title="Filter SQL queries. Use % as a wildcard for flexible matching">';

        return wrapper;
    }

    function buildCurl(entry) {
        if (!entry || !entry.content) return '';

        var content = entry.content;
        var method = (content.method || 'GET').toUpperCase();
        var headers = content.headers || {};
        var proto = headers['x-forwarded-proto'] || (location.protocol ? location.protocol.replace(':', '') : 'http');
        var host = headers['host'] || location.host || 'localhost';
        var uri = content.uri || '/';
        var url = uri.startsWith('http://') || uri.startsWith('https://')
            ? uri
            : proto + '://' + host + (uri.startsWith('/') ? '' : '/') + uri;

        var parts = ['curl -X ' + method];

        var skipped = ['host', 'content-length', 'connection', 'cache-control'];
        for (var key in headers) {
            if (!Object.prototype.hasOwnProperty.call(headers, key)) continue;
            if (skipped.indexOf(key.toLowerCase()) !== -1) continue;
            var val = headers[key];
            if (Array.isArray(val)) val = val.join(', ');
            parts.push("-H '" + key + ": " + ('' + val).replace(/'/g, "'\\''") + "'");
        }

        if (content.payload && method !== 'GET' && method !== 'HEAD') {
            var body = typeof content.payload === 'object'
                ? JSON.stringify(content.payload)
                : '' + content.payload;
            if (body && body !== '{}' && body !== '[]') {
                parts.push("--data '" + body.replace(/'/g, "'\\''") + "'");
            }
        }

        parts.push("'" + url.replace(/'/g, "'\\''") + "'");

        return parts.join(' \\\n  ');
    }

    function copyText(text, btn) {
        function done() {
            var prev = btn.textContent;
            btn.textContent = 'Copied!';
            btn.disabled = true;
            setTimeout(function () {
                btn.textContent = prev;
                btn.disabled = false;
            }, 1500);
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done, function () {
                fallbackCopy(text, done);
            });
        } else {
            fallbackCopy(text, done);
        }
    }

    function fallbackCopy(text, cb) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            cb();
        } catch (e) {
            // ignore
        }
        ta.remove();
    }

    function syncCopyCurl() {
        document.querySelectorAll('.card-header').forEach(function (header) {
            var h2 = header.querySelector('h2');
            if (!h2 || h2.textContent.trim() !== 'Request Details') return;

            var card = header.parentElement;
            var vm = card ? card.__vue__ : null;
            var entry = vm ? vm.entry : null;

            var existing = header.querySelector('.' + CURL_CLASS);

            if (!entry) {
                if (existing) existing.remove();
                return;
            }

            if (!existing) {
                existing = document.createElement('button');
                existing.type = 'button';
                existing.className = CURL_CLASS + ' btn btn-outline-secondary btn-sm ml-auto';
                existing.textContent = 'Copy as cURL';
                header.appendChild(existing);
            }

            if (existing.dataset.entryId !== String(entry.id)) {
                existing.dataset.entryId = String(entry.id);
                existing.onclick = function () {
                    copyText(buildCurl(entry), existing);
                };
            }
        });
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

            var input = filter.querySelector('input');

            // Vue reuses the card and its rows across entries: reset per entry, re-filter per render.
            if (filter.dataset.path !== location.pathname) {
                filter.dataset.path = location.pathname;
                input.value = '';
            }

            setDisplay(filter, table.style.display !== 'none');
            applyFilter(table, input.value);

            if (table.style.display !== 'none') {
                highlightDuplicates(nav.parentElement, table);
            }
        });

        syncCopyCurl();
    }

    new MutationObserver(sync).observe(document.body, {
        childList: true,
        subtree: true,
        characterData: true,
        // Tab switches only flip v-show's inline style.
        attributes: true,
        attributeFilter: ['style'],
    });
})();
