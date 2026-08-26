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
                document.querySelectorAll('[data-tm-dropdown].open').forEach(function (o) { if (o !== dd) o.classList.remove('open'); });
                dd.classList.toggle('open');
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

    window.tmInitDataTable = function (selector, opts) {
        opts = opts || {};

        var placeholder = opts.searchPlaceholder || 'Search...';
        var pageLength = opts.pageLength || 10;

        // Options we consume here, not valid DataTables keys.
        delete opts.searchPlaceholder;

        var config = $.extend(true, {
            pageLength: pageLength,
            dom: "<'tm-dt-head'f><'tm-dt-scroll't>r<'tm-dt-foot'<'tm-dt-foot-left'i><'tm-dt-foot-right'p>>",
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

        // Build the custom "Rows per page" number input with suggestions.
        var id = 'tm-pp-' + (++seq);
        var wrapper = $(selector).closest('.dataTables_wrapper');
        var footLeft = wrapper.find('.tm-dt-foot-left');
        var per = $(
            '<div class="tm-perpage">' +
                '<label for="' + id + '">Rows per page</label>' +
                '<input id="' + id + '" class="tm-perpage-input" type="number" min="1" step="5" value="' + config.pageLength + '" list="' + id + '-list">' +
                '<datalist id="' + id + '-list">' +
                    '<option value="10"></option><option value="15"></option><option value="25"></option>' +
                    '<option value="50"></option><option value="100"></option>' +
                '</datalist>' +
            '</div>'
        );
        footLeft.append(per);

        function apply() {
            var v = parseInt(per.find('.tm-perpage-input').val(), 10);
            if (!v || v < 1) { v = config.pageLength; per.find('.tm-perpage-input').val(v); }
            table.page.len(v).draw();
        }
        per.find('.tm-perpage-input').on('change', apply);
        per.find('.tm-perpage-input').on('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); apply(); }
        });

        return table;
    };
})();
