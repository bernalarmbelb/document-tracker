/* ============================================================================
   tm-datatable.js — one place to give every DataTable the treasury look:
   search on top, "Showing … entries" + a "Rows per page" NUMBER INPUT (with
   suggestions) on the bottom-left, flat pagination on the bottom-right.

   Usage:
     var t = tmInitDataTable('#zero-config', {
         pageLength: 10,
         searchPlaceholder: 'Search resolutions...',
         order: [[0, 'asc']],
         columnDefs: [{ orderable: false, targets: [15] }]
     });
   Any DataTables option passed in `opts` is forwarded to the table.
   ============================================================================ */
(function () {
    var seq = 0;
    var seriesSortRegistered = false;

    /* -----------------------------------------------------------------------
       Custom "series number" sort type — the counter resets every year, so a
       plain string sort groups by the leading digits across years instead of
       year-then-number within that year. The format also isn't consistent:
       resolutions/minutes use "NUMBER-YEAR" (e.g. "194-2024") but ordinances
       use "YEAR-NUMBER" (e.g. "2024-01"), and minutes' own data has both.
       So: whichever digit group is exactly 4 digits is the year, wherever it
       sits — the other group is the number. Registered lazily from
       tmInitDataTable (below), since this file loads globally in
       template.blade.php before the per-page DataTables library script does
       — touching $.fn.dataTable up here would throw before it exists.
       Opt a column in via columnDefs: [{ type: 'tm-series', targets: [0] }].
    ----------------------------------------------------------------------- */
    function registerSeriesSortType() {
        if (seriesSortRegistered || !window.$ || !$.fn.dataTable) return;
        seriesSortRegistered = true;
        $.fn.dataTable.ext.type.order['tm-series-pre'] = function (data) {
            var text = (data === null || data === undefined ? '' : data.toString()).replace(/<[^>]*>/g, '').trim();
            if (text === '') return -Infinity;

            var groups = text.match(/\d+/g);
            if (!groups || groups.length < 2) {
                var n = parseFloat(text.replace(/[^0-9.\-]/g, ''));
                return isNaN(n) ? -Infinity : n;
            }

            var yearIdx = -1;
            for (var i = 0; i < groups.length; i++) {
                if (groups[i].length === 4) { yearIdx = i; break; }
            }

            var year, number;
            if (yearIdx !== -1) {
                year = parseInt(groups[yearIdx], 10);
                number = null;
                for (var j = 0; j < groups.length; j++) {
                    if (j !== yearIdx) { number = parseInt(groups[j], 10); break; }
                }
            } else {
                // No clean 4-digit year (e.g. a truncated "066-20") — best
                // effort, assume the more common NUMBER-YEAR order.
                number = parseInt(groups[0], 10);
                year = parseInt(groups[1], 10);
            }

            return (year * 100000) + number;
        };
    }

    /* -----------------------------------------------------------------------
       Custom dropdown: markup <div class="tm-dd" data-tm-dropdown [data-tm-submit]>
         <button class="tm-dd-toggle"><span class="tm-dd-label">..</span><svg class="tm-dd-caret"/></button>
         <div class="tm-dd-menu"><a class="tm-dd-option" data-value="..">..</a>...</div>
         <input type="hidden" name=".." value="..">
       data-tm-submit -> submit the surrounding form when an option is chosen.
    ----------------------------------------------------------------------- */
    function initDropdowns(root) {
        (root || document).querySelectorAll('[data-tm-dropdown]').forEach(function (dd) {
            if (dd.__tmInit) return;
            dd.__tmInit = true;
            var toggle = dd.querySelector('.tm-dd-toggle');
            var menu   = dd.querySelector('.tm-dd-menu');
            var label  = dd.querySelector('.tm-dd-label');
            var input  = dd.querySelector('input[type="hidden"]');

            toggle.addEventListener('click', function (e) {
                e.stopPropagation();
                document.querySelectorAll('[data-tm-dropdown].open').forEach(function (o) { if (o !== dd) { o.classList.remove('open'); o.classList.remove('tm-dd-flip-up'); } });
                var opening = !dd.classList.contains('open');
                dd.classList.toggle('open');
                if (opening) {
                    // Menu is display:block now that .open is set, so its real
                    // height is measurable — flip upward if it would otherwise
                    // run off the bottom of the viewport and there's more room above.
                    dd.classList.remove('tm-dd-flip-up');
                    var toggleRect = toggle.getBoundingClientRect();
                    var menuHeight = menu.offsetHeight;
                    var spaceBelow = window.innerHeight - toggleRect.bottom;
                    var spaceAbove = toggleRect.top;
                    if (spaceBelow < menuHeight + 12 && spaceAbove > spaceBelow) {
                        dd.classList.add('tm-dd-flip-up');
                    }
                }
            });

            menu.querySelectorAll('.tm-dd-option').forEach(function (opt) {
                opt.addEventListener('click', function () {
                    if (input) input.value = opt.getAttribute('data-value');
                    if (label) label.textContent = opt.textContent.trim();
                    menu.querySelectorAll('.tm-dd-option').forEach(function (o) { o.classList.remove('active'); });
                    opt.classList.add('active');
                    dd.classList.remove('open');
                    if (dd.hasAttribute('data-tm-submit') && dd.closest('form')) dd.closest('form').submit();
                });
            });
        });
    }
    window.tmInitDropdowns = initDropdowns;

    /* -----------------------------------------------------------------------
       Auto-enhance form <select class="tm-select"> into a custom dropdown so
       the OPEN list is styled too (native option lists can't be themed).
       The real <select> stays in the DOM (hidden) as the source of truth for
       form submission, `name`, `value`, `required`, and `onchange`.
    ----------------------------------------------------------------------- */
    var CARET = '<svg class="tm-dd-caret" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>';

    function enhanceSelects(root) {
        (root || document).querySelectorAll('select.tm-select').forEach(function (sel) {
            if (sel.__tmEnhanced) return;
            sel.__tmEnhanced = true;

            var wrap = document.createElement('div');
            wrap.className = 'tm-dd tm-dd-select';
            sel.parentNode.insertBefore(wrap, sel);
            wrap.appendChild(sel);
            sel.style.display = 'none';

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'tm-dd-toggle';
            var lbl = document.createElement('span');
            lbl.className = 'tm-dd-label';
            btn.appendChild(lbl);
            btn.insertAdjacentHTML('beforeend', CARET);
            wrap.appendChild(btn);

            var menu = document.createElement('div');
            menu.className = 'tm-dd-menu';
            wrap.appendChild(menu);

            function syncLabel() {
                var opt = sel.options[sel.selectedIndex];
                lbl.textContent = opt ? opt.textContent.trim() : '';
                lbl.classList.toggle('tm-dd-placeholder', !sel.value);
            }

            Array.prototype.forEach.call(sel.options, function (opt) {
                var a = document.createElement('a');
                a.className = 'tm-dd-option' + (opt.selected ? ' active' : '');
                a.setAttribute('data-value', opt.value);
                a.textContent = opt.textContent;
                a.addEventListener('click', function () {
                    sel.value = opt.value;
                    sel.dispatchEvent(new Event('change', { bubbles: true }));
                    menu.querySelectorAll('.tm-dd-option').forEach(function (o) { o.classList.remove('active'); });
                    a.classList.add('active');
                    syncLabel();
                    wrap.classList.remove('open', 'tm-dd-invalid');
                });
                menu.appendChild(a);
            });

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                document.querySelectorAll('.tm-dd.open').forEach(function (o) { if (o !== wrap) o.classList.remove('open'); });
                wrap.classList.toggle('open');
            });

            // keep label in sync if code changes the select value later
            sel.addEventListener('change', syncLabel);
            syncLabel();
        });
    }
    window.tmEnhanceSelects = enhanceSelects;

    // Validate required enhanced selects on submit (hidden selects skip native validation).
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || !form.querySelectorAll) return;
        var firstBad = null;
        form.querySelectorAll('select.tm-select[required]').forEach(function (sel) {
            var w = sel.closest('.tm-dd');
            if (!sel.value) { firstBad = firstBad || w; if (w) w.classList.add('tm-dd-invalid'); }
            else if (w) { w.classList.remove('tm-dd-invalid'); }
        });
        if (firstBad) {
            e.preventDefault();
            var t = firstBad.querySelector('.tm-dd-toggle');
            if (t) { t.focus(); t.scrollIntoView({ block: 'center', behavior: 'smooth' }); }
        }
    }, true);

    document.addEventListener('click', function () {
        document.querySelectorAll('.tm-dd.open').forEach(function (d) { d.classList.remove('open'); });
    });
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { initDropdowns(); enhanceSelects(); });
    } else {
        initDropdowns(); enhanceSelects();
    }

    /* "Previous · Page [N ▾] of M · Next" — replaces DataTables' numbered
       page buttons. The page number itself is a .tm-dd jump-to-page dropdown
       (reusing the same component as the rows-per-page selector), not plain
       text. Call after creating a table whose dom includes 'p'; pairs with
       pagingType: 'simple' so there are exactly two buttons (previous/next)
       to inject this between.
       DataTables rebuilds the whole <ul class="pagination"> from scratch on
       every draw, which wipes out anything manually inserted into it — so
       this is rebuilt on every draw, not just once. Rebuilding (rather than
       patching in place) also handles the option list changing size when a
       search or rows-per-page change alters the total page count. */
    function addPageInfo(table, wrapper) {
        function render() {
            var pag = wrapper.find('.dataTables_paginate');
            pag.find('.tm-dt-pageinfo').remove();

            var p = table.page.info();
            var totalPages = p.pages || 1;
            var currentPage = p.page + 1;

            var optsHtml = '';
            for (var i = 1; i <= totalPages; i++) {
                optsHtml += '<a class="tm-dd-option' + (i === currentPage ? ' active' : '') + '" data-value="' + i + '">' + i + '</a>';
            }

            var info = $(
                '<span class="tm-dt-pageinfo">Page ' +
                    '<span class="tm-dd tm-dd-pagejump" data-tm-dropdown>' +
                        '<button type="button" class="tm-dd-toggle"><span class="tm-dd-label">' + currentPage + '</span>' + CARET + '</button>' +
                        '<div class="tm-dd-menu">' + optsHtml + '</div>' +
                        '<input type="hidden" value="' + currentPage + '">' +
                    '</span>' +
                    ' of ' + totalPages +
                '</span>'
            );
            pag.find('.paginate_button.previous').first().after(info);

            initDropdowns(info[0]);
            info.find('.tm-dd-option').on('click', function () {
                table.page(parseInt($(this).attr('data-value'), 10) - 1).draw('page');
            });
        }
        table.on('draw.dt', render);
        render();
    }
    window.tmAddPageInfo = addPageInfo;

    /* Size the table's scroll area to fill whatever room is actually left on
       screen (down to the fixed Quick-Create bar, which itself collapses/
       expands), instead of a guessed fixed vh. There's already pagination
       and an internal scrollbar, so there's no reason to cap it short. */
    function fitTableHeight(scrollEl) {
        if (!scrollEl) return;

        // #container carries Cork's old min-height:100vh (meant for when it
        // started at the very top of the viewport). Our header now flows
        // above it instead of overlaying it, so that min-height always
        // overshoots the viewport by the header's own height — and body's
        // separate padding-bottom (reserved for the Quick-Create bar on
        // pages that DON'T size themselves) does the same again. Both are
        // redundant here since this function already fits exactly around
        // both the header and the quickbar; left in place they just make
        // the whole page scroll to reveal dead space below the table.
        var container = document.getElementById('container');
        if (container) container.style.minHeight = '0';
        document.body.style.paddingBottom = '0';
        var quickbar = document.getElementById('tmQuickbar');
        var footEl = scrollEl.parentElement ? scrollEl.parentElement.querySelector('.tm-dt-foot') : null;
        function fit() {
            var top = scrollEl.getBoundingClientRect().top;
            var quickbarHeight = quickbar ? quickbar.getBoundingClientRect().height : 0;
            var footHeight = footEl ? footEl.getBoundingClientRect().height : 0;
            var available = window.innerHeight - top - quickbarHeight - footHeight - 40;
            scrollEl.style.maxHeight = Math.max(240, available) + 'px';
        }
        fit();
        window.addEventListener('resize', fit);
        var toggle = document.getElementById('tmQuickbarToggle');
        if (toggle) toggle.addEventListener('click', function () { setTimeout(fit, 0); });
    }
    window.tmFitTableHeight = fitTableHeight;

    /* Click-and-hold-to-pan scrolling for the wide table's scroll box, as an
       alternative to the thin native scrollbar. Clicks on links/buttons/row
       actions inside the table still work normally; only a genuine drag
       (moved more than a few px) is treated as a pan and has its resulting
       click suppressed, so it doesn't also trigger the row-click menu/nav
       those pages wire up separately. */
    function enableDragScroll(el) {
        if (!el || el.__tmDragInit) return;
        el.__tmDragInit = true;

        var dragging = false, moved = false, startX, startY, startLeft, startTop;

        el.addEventListener('mousedown', function (e) {
            if (e.button !== 0 || e.target.closest('a, button, input, select, textarea, .tm-dd')) return;
            dragging = true;
            moved = false;
            el.classList.add('tm-dt-scroll-grabbing');
            startX = e.pageX;
            startY = e.pageY;
            startLeft = el.scrollLeft;
            startTop = el.scrollTop;
        });
        document.addEventListener('mousemove', function (e) {
            if (!dragging) return;
            var dx = e.pageX - startX, dy = e.pageY - startY;
            if (!moved && (Math.abs(dx) > 4 || Math.abs(dy) > 4)) moved = true;
            if (moved) {
                e.preventDefault();
                el.scrollLeft = startLeft - dx;
                el.scrollTop = startTop - dy;
            }
        });
        document.addEventListener('mouseup', function () {
            dragging = false;
            el.classList.remove('tm-dt-scroll-grabbing');
        });
        // Capture phase so a drag's trailing click never reaches the
        // delegated row-click handlers bound on tbody.
        el.addEventListener('click', function (e) {
            if (moved) { e.stopPropagation(); e.preventDefault(); moved = false; }
        }, true);
    }
    window.tmEnableDragScroll = enableDragScroll;

    /* Scrollbar fades in while the table is actually being scrolled (wheel,
       drag, native scrollbar, keyboard) and fades back out ~0.8s after it
       stops — pairs with the scrollbar-color/::-webkit-scrollbar-thumb rules
       in theme.css that only paint the thumb while .tm-dt-scroll-active. */
    function enableScrollbarFade(el) {
        if (!el || el.__tmFadeInit) return;
        el.__tmFadeInit = true;
        var hideTimer;
        el.addEventListener('scroll', function () {
            el.classList.add('tm-dt-scroll-active');
            clearTimeout(hideTimer);
            hideTimer = setTimeout(function () { el.classList.remove('tm-dt-scroll-active'); }, 800);
        });
    }
    window.tmEnableScrollbarFade = enableScrollbarFade;

    /* "Rows per page" selector — a compact .tm-dd dropdown (10/25/50/100,
       plus the table's own starting length if it isn't one of those) instead
       of a bare number input. Reuses initDropdowns() for open/close + label
       sync, then layers on the one bit it doesn't know about: changing the
       table's page length. */
    var PP_OPTIONS = [10, 25, 50, 100];
    function buildRowsPerPage(table, footLeft, currentLength) {
        var options = PP_OPTIONS.slice();
        if (options.indexOf(currentLength) === -1) {
            options.push(currentLength);
            options.sort(function (a, b) { return a - b; });
        }

        var id = 'tm-pp-' + (++seq);
        var optsHtml = options.map(function (n) {
            return '<a class="tm-dd-option' + (n === currentLength ? ' active' : '') + '" data-value="' + n + '">' + n + '</a>';
        }).join('');

        var wrap = $(
            '<div class="tm-perpage">' +
                '<span id="' + id + '-label">Rows per page</span>' +
                '<div class="tm-dd tm-dd-perpage" data-tm-dropdown aria-labelledby="' + id + '-label">' +
                    '<button type="button" class="tm-dd-toggle"><span class="tm-dd-label">' + currentLength + '</span>' + CARET + '</button>' +
                    '<div class="tm-dd-menu">' + optsHtml + '</div>' +
                    '<input type="hidden" value="' + currentLength + '">' +
                '</div>' +
            '</div>'
        );
        footLeft.append(wrap);

        initDropdowns(wrap[0]);
        wrap.find('.tm-dd-option').on('click', function () {
            table.page.len(parseInt($(this).attr('data-value'), 10)).draw();
        });
    }
    window.tmBuildRowsPerPage = buildRowsPerPage;

    window.tmInitDataTable = function (selector, opts) {
        opts = opts || {};
        registerSeriesSortType();

        var placeholder = opts.searchPlaceholder || 'Search...';
        var pageLength = opts.pageLength || 10;
        var filtersHtml = opts.filtersHtml;

        // Options we consume here, not valid DataTables keys.
        delete opts.searchPlaceholder;
        delete opts.filtersHtml;

        var config = $.extend(true, {
            pageLength: pageLength,
            pagingType: 'simple',
            dom: "<'tm-dt-head'f><'tm-dt-tablebox'<'tm-dt-scroll't>r<'tm-dt-foot'<'tm-dt-foot-left'i><'tm-dt-foot-right'p>>>",
            language: {
                search: '',
                searchPlaceholder: placeholder,
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'No entries',
                infoFiltered: '(filtered from _MAX_ total)',
                paginate: { previous: 'Previous', next: 'Next' }
            }
        }, opts);

        var table = $(selector).DataTable(config);
        var wrapper = $(selector).closest('.dataTables_wrapper');
        if (filtersHtml) {
            var filtersEl = $(filtersHtml).appendTo(wrapper.find('.tm-dt-head'));
            initDropdowns(filtersEl[0]);
        }
        addPageInfo(table, wrapper);
        buildRowsPerPage(table, wrapper.find('.tm-dt-foot-left'), config.pageLength);
        fitTableHeight(wrapper.find('.tm-dt-scroll')[0]);
        enableDragScroll(wrapper.find('.tm-dt-scroll')[0]);
        enableScrollbarFade(wrapper.find('.tm-dt-scroll')[0]);

        return table;
    };
})();
