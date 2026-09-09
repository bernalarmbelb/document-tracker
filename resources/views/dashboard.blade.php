@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
<style>
    /* row hover + clickable cue */
    #documents-table tbody tr { cursor: pointer; }
    #trend, #bytype, #status { min-height: 230px; }
    .tm-dash { padding: 8px; }
    /* Dashboard needs room for a third (calendar) column — widen past the
       default 1320px content width used by other pages. */
    .tm-wrap.tm-dash { max-width: 1680px; }
    .tm-grid-main { grid-template-columns: 1fr 360px 320px; }
    @media (max-width: 1100px) { .tm-grid-main { grid-template-columns: 1fr; } }
    .tm-charts-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .tm-insights { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 6px; }
    .tm-insights .ins { border: 1px solid var(--tm-line); border-radius: 10px; padding: 12px 14px; }
    .tm-insights .ins-num { font-family: var(--tm-font-head); font-weight: 800; font-size: 24px; color: #1a1919; line-height: 1; }
    .tm-insights .ins-lbl { font-size: 12px; color: var(--tm-muted); font-weight: 600; margin-top: 5px; }
    .tm-insights .ins-sub { font-size: 11px; margin-top: 4px; }
    @media (max-width: 700px) { .tm-charts-2, .tm-insights { grid-template-columns: 1fr; } }
    /* keep DataTables from fighting the .tm-table borders */
    #documents-table.tm-table { width: 100% !important; }

    /* Calendar of Events widget */
    .cal-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .cal-nav .mo { font-family: var(--tm-font-head); font-weight: 700; font-size: 13px; color: #1a1919; }
    .cal-nav button {
        width: 24px; height: 24px; border-radius: 6px; border: 1px solid var(--tm-line); background: transparent;
        color: var(--tm-muted); font-size: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center;
        font-family: var(--tm-font); line-height: 1; padding: 0;
    }
    .cal-nav button:hover { background: rgba(66, 122, 181, 0.10); color: var(--tm-primary); }
    .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px; }
    .cal-dow { text-align: center; font-size: 9.5px; font-weight: 700; letter-spacing: .03em; color: var(--tm-muted); text-transform: uppercase; padding-bottom: 4px; }
    .cal-day {
        aspect-ratio: 1; display: flex; align-items: center; justify-content: center; position: relative;
        font-size: 11.5px; font-weight: 600; color: #1a1919; border-radius: 8px;
    }
    .cal-day.out { color: var(--tm-muted); opacity: .38; font-weight: 500; }
    .cal-day.has-event.past::after { content: ""; position: absolute; bottom: 3px; width: 4px; height: 4px; border-radius: 50%; background: var(--tm-muted); opacity: .7; }
    .cal-day.has-event.upcoming { background: rgba(66, 122, 181, 0.14); color: var(--tm-primary); font-weight: 800; cursor: pointer; }
    .cal-day.has-event.upcoming::after { content: ""; position: absolute; bottom: 3px; width: 4px; height: 4px; border-radius: 50%; background: var(--tm-primary); }
    .cal-day.has-event.past { cursor: pointer; }
    .cal-day.today { box-shadow: inset 0 0 0 1.5px #B8860B; }
    .cal-legend { display: flex; gap: 14px; margin: 12px 0 4px; padding-top: 12px; border-top: 1px solid var(--tm-line); font-size: 10.5px; color: var(--tm-muted); font-weight: 600; }
    .cal-legend span { display: inline-flex; align-items: center; gap: 5px; }
    .cal-legend i { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
    .cal-eyebrow { font-size: 10px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--tm-primary); margin: 14px 0 8px; }
    .cal-eyebrow.muted { color: var(--tm-muted); }
    .cal-spot, .cal-list .row {
        display: flex; gap: 12px; padding: 10px; border-radius: 10px; cursor: pointer;
    }
    .cal-spot { background: rgba(66, 122, 181, 0.08); margin-bottom: 4px; }
    .cal-list { display: flex; flex-direction: column; }
    .cal-list .row { padding: 8px 2px; border-bottom: 1px solid var(--tm-line); border-radius: 0; }
    .cal-list .row:last-child { border-bottom: none; }
    .cal-list .row:hover, .cal-spot:hover { background: rgba(66, 122, 181, 0.06); }
    .cal-chip { width: 42px; height: 42px; border-radius: 9px; flex-shrink: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--tm-primary); color: #fff; }
    .cal-chip.done { background: var(--tm-line); color: var(--tm-muted); }
    .cal-chip .d { font-family: var(--tm-font-head); font-weight: 800; font-size: 16px; line-height: 1; }
    .cal-chip .m { font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; opacity: .85; margin-top: 1px; }
    .cal-body { min-width: 0; flex: 1; }
    .cal-row-top { display: flex; justify-content: space-between; gap: 8px; align-items: baseline; }
    .cal-name { font-size: 13px; font-weight: 700; color: #1a1919; flex: 1 1 auto; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cal-meta { font-size: 11px; color: var(--tm-muted); margin-top: 2px; font-weight: 600; }
    .tm-badge-acc { background: rgba(214, 170, 20, 0.16); color: #B8860B; }
    .cal-row-top .tm-badge { flex-shrink: 0; }
</style>
@endsection

@section("content")

<div class="layout-px-spacing">
    <div class="tm-wrap tm-dash">

        {{-- Page header --}}
        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">Dashboard</h1>
                <div class="tm-sub">Overview of legislative documents and system activity</div>
            </div>
            <div class="tm-crumb"><b>Home</b> / Dashboard</div>
        </div>

        {{-- KPI row --}}
        <div class="tm-kpis">
            <div class="tm-kpi">
                <div class="chip tm-chip-pri">📄</div>
                <div><div class="num">{{ $resolutions_ctr }}</div><div class="lbl">Resolutions</div>
                    <div class="sub"><span class="dot" style="background:var(--tm-success)"></span>{{ $resolutions_approved_ctr }} approved</div></div>
            </div>
            <div class="tm-kpi">
                <div class="chip tm-chip-sec">📕</div>
                <div><div class="num">{{ $ordinances_ctr }}</div><div class="lbl">Ordinances</div>
                    <div class="sub"><span class="dot" style="background:var(--tm-success)"></span>{{ $ordinances_approved_ctr }} approved</div></div>
            </div>
            <div class="tm-kpi">
                <div class="chip tm-chip-acc">📝</div>
                <div><div class="num">{{ $minutes_ctr }}</div><div class="lbl">Minutes</div>
                    <div class="sub tm-muted">meeting records</div></div>
            </div>
            <div class="tm-kpi">
                <div class="chip tm-chip-dark">✉️</div>
                <div><div class="num">{{ $communications_ctr }}</div><div class="lbl">Communications</div>
                    <div class="sub tm-muted">in &amp; out</div></div>
            </div>
            <div class="tm-kpi">
                <div class="chip tm-chip-suc">📅</div>
                <div><div class="num">{{ $activities_ctr }}</div><div class="lbl">Activities</div>
                    <div class="sub tm-muted">sangguniang</div></div>
            </div>
        </div>

        {{-- Main charts + sidebar (equal height) --}}
        <div class="tm-grid-main">

            {{-- Left: charts --}}
            <div class="tm-stack" id="tm-leftcol">
                <div class="tm-card">
                    <h3>Documents Created per Month <span class="tm-muted" style="font-weight:500;font-size:12px">{{ $trend_year }}</span></h3>
                    <div id="trend"></div>
                </div>

                <div class="tm-charts-2">
                    <div class="tm-card"><h3>Records by Type</h3><div id="bytype"></div></div>
                    <div class="tm-card"><h3>Approval Status</h3><div id="status"></div></div>
                </div>
            </div>

            {{-- Right: quick actions + activity history (capped to align bottom with charts) --}}
            <div class="tm-stack">
                <div class="tm-card" id="tm-qa">
                    <h3>Quick Actions</h3>
                    @if(Auth::user()->hasPermission('Add Resolution'))
                        <a class="tm-btn tm-btn-primary tm-btn-block" style="margin-bottom:10px" href="{{ url('/resolutions/add') }}">＋ New Resolution</a>
                    @endif
                    @if(Auth::user()->hasPermission('Add Ordinance'))
                        <a class="tm-btn tm-btn-secondary tm-btn-block" style="margin-bottom:10px" href="{{ url('/ordinances/add') }}">＋ New Ordinance</a>
                    @endif
                    @if(Auth::user()->hasPermission('Add Minute'))
                        <a class="tm-btn tm-btn-accent tm-btn-block" style="margin-bottom:10px" href="{{ url('/minutes/list') }}">＋ New Minutes</a>
                    @endif
                    @if(Auth::user()->hasPermission('Search Document'))
                        <a class="tm-btn tm-btn-dark tm-btn-block" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#global-search">🔍 Search Documents</a>
                    @endif
                </div>

                <div class="tm-card">
                    <h3>Activity History <span class="tm-muted" style="font-weight:500;font-size:12px">recent</span></h3>
                    <div class="tm-feed" id="tm-activity-feed">
                        @forelse($activity_feed as $f)
                            @php
                                $a = strtolower($f->action ?? '');
                                $ic = str_contains($a,'add') ? '＋' : (str_contains($a,'update') ? '✎' : (str_contains($a,'delete') ? '🗑' : (str_contains($a,'login') ? '🔑' : (str_contains($a,'logout') ? '⇥' : ((str_contains($a,'export')||str_contains($a,'pdf')) ? '📄' : '👁')))));
                            @endphp
                            <div class="item">
                                <div class="ic">{{ $ic }}</div>
                                <div>
                                    <div class="act">{{ $f->action }}</div>
                                    <div class="meta">{{ $f->username ?? 'System' }} · {{ $f->activity_date ? \Carbon\Carbon::parse($f->activity_date)->diffForHumans() : '' }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="tm-table-empty">No activity yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Far right: calendar of events, its own column --}}
            <div class="tm-stack">
                <div class="tm-card" id="tm-cal-card" data-view-base="{{ url('/activities/view') }}">
                    <h3>Calendar of Events</h3>
                    <div class="cal-nav">
                        <button type="button" id="cal-prev" aria-label="Previous month">‹</button>
                        <div class="mo" id="cal-mo-label"></div>
                        <button type="button" id="cal-next" aria-label="Next month">›</button>
                    </div>
                    <div class="cal-grid">
                        <div class="cal-dow">S</div><div class="cal-dow">M</div><div class="cal-dow">T</div>
                        <div class="cal-dow">W</div><div class="cal-dow">T</div><div class="cal-dow">F</div><div class="cal-dow">S</div>
                    </div>
                    <div class="cal-grid" id="cal-days"></div>
                    <div class="cal-legend">
                        <span><i style="background:var(--tm-primary)"></i>Upcoming</span>
                        <span><i style="background:var(--tm-muted);opacity:.5"></i>Past</span>
                    </div>
                    <div id="cal-agenda"></div>
                </div>
            </div>

        </div>

        {{-- Insights + Top Authors (full width) --}}
        <div class="tm-card" style="margin-top:16px">
            <h3>Insights &amp; Top Authors</h3>
            <div class="tm-insights">
                <div class="ins">
                    <div class="ins-num">{{ $docs_this_year }}</div>
                    <div class="ins-lbl">Documents this year</div>
                    @php $delta = $docs_this_year - $docs_last_year; @endphp
                    <div class="ins-sub">
                        <span style="color:{{ $delta>=0 ? 'var(--tm-success)' : 'var(--tm-danger)' }};font-weight:700">{{ $delta>=0 ? '▲' : '▼' }} {{ abs($delta) }}</span>
                        <span class="tm-muted">vs last year ({{ $docs_last_year }})</span>
                    </div>
                </div>
                <div class="ins">
                    <div class="ins-num">{{ $docs_this_month }}</div>
                    <div class="ins-lbl">Added this month</div>
                    <div class="ins-sub tm-muted">{{ now()->format('F Y') }}</div>
                </div>
                <div class="ins">
                    <div class="ins-num">{{ $avg_approval_days !== null ? $avg_approval_days : '—' }}<span style="font-size:13px;font-weight:600">{{ $avg_approval_days !== null ? ' days' : '' }}</span></div>
                    <div class="ins-lbl">Avg time to approve</div>
                    <div class="ins-sub tm-muted">resolutions &amp; ordinances</div>
                </div>
            </div>
            <div style="margin-top:8px">
                <div class="tm-muted" style="font-size:12px;font-weight:600;margin-bottom:2px">Top Authors</div>
                <div id="top_authors"></div>
            </div>
        </div>

        {{-- Documents (full width, paginated DataTable) --}}
        <div class="tm-card" style="margin-top:16px">
            <h3>Documents</h3>
            <div class="table-responsive">
                <table id="documents-table" class="tm-table" style="width:100%">
                    <thead><tr><th>Type</th><th>No.</th><th>Title</th><th>Author</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    @foreach($documents as $doc)
                        @php
                            $isRes = $doc->record_type === 'RESOLUTION';
                            $st = strtoupper($doc->status ?? '');
                            $stClass = $st === 'APPROVED' ? 'tm-badge-ok' : (in_array($st, ['INVALID','DISAPPROVED']) ? 'tm-badge-bad' : 'tm-badge');
                        @endphp
                        <tr data-href="{{ $isRes ? url('/resolutions/view/'.$doc->id) : url('/ordinances/view/'.$doc->id) }}">
                            <td><span class="tm-badge {{ $isRes ? 'tm-badge-info' : 'tm-badge-sec' }}">{{ $isRes ? 'RES' : 'ORD' }}</span></td>
                            <td style="white-space:nowrap">{{ $doc->series_number }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($doc->title, 90) }}</td>
                            <td class="tm-muted" style="white-space:nowrap">{{ \Illuminate\Support\Str::limit($doc->author_name, 28) }}</td>
                            <td><span class="tm-badge {{ $stClass }}">{{ $doc->status ?: '—' }}</span></td>
                            <td class="tm-muted" style="white-space:nowrap" data-order="{{ $doc->date_created ? \Carbon\Carbon::parse($doc->date_created)->timestamp : 0 }}">{{ $doc->date_created ? \Carbon\Carbon::parse($doc->date_created)->format('M d, Y') : '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/apex/apexcharts.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/table/datatable/datatables.js") }}"></script>
<script>
    // Documents DataTable — treasury-style layout (search top; info + rows-per-page
    // bottom-left, pagination bottom-right). 15 per page, newest first, searchable.
    $(function () {
        var docsTable = $('#documents-table').DataTable({
            pageLength: 15,
            order: [[5, 'desc']],
            columnDefs: [{ orderable: false, targets: [0] }],
            dom: "<'tm-dt-head'f>t<'tm-dt-foot'<'tm-dt-foot-left'i><'tm-dt-foot-right'p>>",
            language: {
                search: '',
                searchPlaceholder: 'Search documents...',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'No entries',
                infoFiltered: '(filtered from _MAX_ total)',
                paginate: { previous: 'Previous', next: 'Next' }
            }
        });

        // Custom "Rows per page" — a number input with suggestions (type any value).
        var perPage = $(
            '<div class="tm-perpage">' +
                '<label for="tm-perpage-input">Rows per page</label>' +
                '<input id="tm-perpage-input" class="tm-perpage-input" type="number" min="1" step="5" value="15" list="tm-perpage-list">' +
                '<datalist id="tm-perpage-list">' +
                    '<option value="10"></option><option value="15"></option><option value="25"></option>' +
                    '<option value="50"></option><option value="100"></option>' +
                '</datalist>' +
            '</div>'
        );
        $('.tm-dt-foot-left').append(perPage);
        function applyPerPage() {
            var v = parseInt(perPage.find('.tm-perpage-input').val(), 10);
            if (!v || v < 1) { v = 15; perPage.find('.tm-perpage-input').val(15); }
            docsTable.page.len(v).draw();
        }
        perPage.find('.tm-perpage-input').on('change', applyPerPage);
        perPage.find('.tm-perpage-input').on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); applyPerPage(); } });

        // Click a row to open that document (works across pages via delegation).
        $('#documents-table tbody').on('click', 'tr', function () {
            var href = $(this).data('href');
            if (href) window.location.href = href;
        });
    });
</script>
<script>
    (function () {
        const font = 'Manrope, sans-serif';
        const primary = '#427AB5', secondary = '#406AAF', accent = '#F7DD7D', dark = '#333', success = '#0FA958', warning = '#E84E46';

        const trendChart = new ApexCharts(document.querySelector('#trend'), {
            chart: { type: 'bar', height: 230, fontFamily: font, toolbar: { show: false } },
            series: [{ name: 'Documents', data: @json($monthly_counts) }],
            xaxis: { categories: @json($monthly_labels) },
            colors: [primary], plotOptions: { bar: { borderRadius: 5, columnWidth: '55%' } },
            dataLabels: { enabled: false }, grid: { borderColor: 'rgba(51,51,51,.08)' }
        });
        trendChart.render();
        window.__tmTrendChart = trendChart;

        new ApexCharts(document.querySelector('#bytype'), {
            chart: { type: 'donut', height: 230, fontFamily: font },
            series: [{{ $resolutions_ctr }}, {{ $ordinances_ctr }}, {{ $minutes_ctr }}, {{ $communications_ctr }}, {{ $activities_ctr }}],
            labels: ['Resolutions', 'Ordinances', 'Minutes', 'Communications', 'Activities'],
            colors: [primary, secondary, accent, dark, success],
            legend: { position: 'bottom', fontSize: '11px' }, dataLabels: { enabled: false },
            plotOptions: { pie: { donut: { size: '62%' } } }
        }).render();

        new ApexCharts(document.querySelector('#status'), {
            chart: { type: 'bar', height: 230, fontFamily: font, toolbar: { show: false }, stacked: true },
            series: [
                { name: 'Approved', data: [{{ $resolutions_approved_ctr }}, {{ $ordinances_approved_ctr }}] },
                { name: 'Other', data: [{{ max($resolutions_ctr - $resolutions_approved_ctr, 0) }}, {{ max($ordinances_ctr - $ordinances_approved_ctr, 0) }}] }
            ],
            xaxis: { categories: ['Resolutions', 'Ordinances'] },
            colors: [success, warning],
            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '45%' } },
            legend: { position: 'bottom', fontSize: '11px' }, dataLabels: { enabled: false },
            grid: { borderColor: 'rgba(51,51,51,.08)' }
        }).render();

        // Top authors (horizontal bar)
        var authorNames = @json($top_authors->keys()->map(fn ($a) => smart_title($a))->values());
        var authorCounts = @json($top_authors->values());
        if (authorNames.length) {
            new ApexCharts(document.querySelector('#top_authors'), {
                chart: { type: 'bar', height: Math.max(180, authorNames.length * 42), fontFamily: font, toolbar: { show: false } },
                series: [{ name: 'Documents', data: authorCounts }],
                xaxis: { categories: authorNames },
                colors: [secondary],
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
                dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#fff'] } },
                grid: { borderColor: 'rgba(51,51,51,.08)' },
                tooltip: { y: { formatter: function (v) { return v + ' document' + (v == 1 ? '' : 's'); } } }
            }).render();
        } else {
            document.querySelector('#top_authors').innerHTML = '<div class="tm-table-empty">No author data yet.</div>';
        }

    })();
</script>
<script>
    // Calendar of Events — renders a mini month calendar plus an agenda list from
    // Sangguniang Activities. "Upcoming" vs "Past" is computed from activity_date
    // rather than trusting the stored status field, so a stale UPCOMING flag on a
    // date that has already passed doesn't misplace it in the calendar.
    (function () {
        const calCard = document.getElementById('tm-cal-card');
        if (!calCard) return;

        const events = @json($calendar_activities);

        const viewBase = calCard.dataset.viewBase;
        const monthLabelEl = document.getElementById('cal-mo-label');
        const daysEl = document.getElementById('cal-days');
        const agendaEl = document.getElementById('cal-agenda');
        const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

        const now = new Date();
        const todayKey = dateKey(now.getFullYear(), now.getMonth(), now.getDate());

        events.forEach(e => { e._d = e.activity_date ? new Date(e.activity_date.replace(' ', 'T')) : null; });
        const upcoming = events.filter(e => e._d && e._d >= now).sort((a, b) => a._d - b._d);
        const past = events.filter(e => e._d && e._d < now).sort((a, b) => b._d - a._d);

        // Default the visible month to the soonest upcoming activity, else today.
        let viewYear = now.getFullYear();
        let viewMonth = now.getMonth();
        if (upcoming.length) { viewYear = upcoming[0]._d.getFullYear(); viewMonth = upcoming[0]._d.getMonth(); }

        function dateKey(y, m, d) { return y + '-' + m + '-' + d; }

        function badgeClass(status) {
            const s = (status || '').toUpperCase();
            if (s === 'COMPLETED') return 'tm-badge-ok';
            if (s === 'ONGOING') return 'tm-badge-acc';
            if (s === 'UPCOMING') return 'tm-badge-info';
            return 'tm-badge';
        }

        function goToActivity(id) { window.location.href = viewBase + '/' + id; }

        function renderCalendar() {
            monthLabelEl.textContent = monthNames[viewMonth] + ' ' + viewYear;

            const eventsByDay = {};
            events.forEach(e => {
                if (!e._d) return;
                const k = dateKey(e._d.getFullYear(), e._d.getMonth(), e._d.getDate());
                (eventsByDay[k] = eventsByDay[k] || []).push(e);
            });

            const firstDow = new Date(viewYear, viewMonth, 1).getDay();
            const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
            const prevMonthDays = new Date(viewYear, viewMonth, 0).getDate();

            let html = '';
            for (let i = firstDow - 1; i >= 0; i--) {
                html += '<div class="cal-day out">' + (prevMonthDays - i) + '</div>';
            }
            for (let d = 1; d <= daysInMonth; d++) {
                const k = dateKey(viewYear, viewMonth, d);
                const dayEvents = eventsByDay[k];
                let cls = 'cal-day';
                if (k === todayKey) cls += ' today';
                if (dayEvents && dayEvents.length) {
                    cls += ' has-event ' + (dayEvents[0]._d >= now ? 'upcoming' : 'past');
                }
                const clickAttr = (dayEvents && dayEvents.length === 1) ? ' data-id="' + dayEvents[0].id + '"' : '';
                const titleAttr = dayEvents ? ' title="' + dayEvents.map(e => e.activity_title).join(', ').replace(/"/g, '&quot;') + '"' : '';
                html += '<div class="' + cls + '"' + clickAttr + titleAttr + '>' + d + '</div>';
            }
            const trailing = (7 - ((firstDow + daysInMonth) % 7)) % 7;
            for (let d = 1; d <= trailing; d++) {
                html += '<div class="cal-day out">' + d + '</div>';
            }
            daysEl.innerHTML = html;

            daysEl.querySelectorAll('.cal-day[data-id]').forEach(el => {
                el.addEventListener('click', () => goToActivity(el.dataset.id));
            });
        }

        function chip(e, done) {
            const m = e._d.toLocaleString('en-US', { month: 'short' });
            return '<div class="cal-chip' + (done ? ' done' : '') + '"><div class="d">' + e._d.getDate() + '</div><div class="m">' + m + '</div></div>';
        }

        function row(e, done, spot) {
            const meta = [e.location, e.duration].filter(Boolean).join(' · ');
            const title = escapeHtml(e.activity_title || '');
            return (
                '<div class="' + (spot ? 'cal-spot' : 'row') + '" data-id="' + e.id + '">' +
                    chip(e, done) +
                    '<div class="cal-body">' +
                        '<div class="cal-row-top"><div class="cal-name" title="' + title + '">' + title + '</div>' +
                        '<span class="tm-badge ' + badgeClass(e.status) + '">' + escapeHtml(e.status || '—') + '</span></div>' +
                        (meta ? '<div class="cal-meta">' + escapeHtml(meta) + '</div>' : '') +
                    '</div>' +
                '</div>'
            );
        }

        function escapeHtml(s) { return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); }

        function renderAgenda() {
            let html = '';
            html += '<div class="cal-eyebrow">Next up</div>';
            html += upcoming.length
                ? row(upcoming[0], false, true)
                : '<div class="tm-table-empty">No upcoming activities scheduled.</div>';

            if (past.length) {
                html += '<div class="cal-eyebrow muted">Recent</div>';
                html += '<div class="cal-list">' + past.slice(0, 2).map(e => row(e, true, false)).join('') + '</div>';
            }
            agendaEl.innerHTML = html;
            agendaEl.querySelectorAll('[data-id]').forEach(el => {
                el.addEventListener('click', () => goToActivity(el.dataset.id));
            });
        }

        document.getElementById('cal-prev').addEventListener('click', () => {
            viewMonth--; if (viewMonth < 0) { viewMonth = 11; viewYear--; }
            renderCalendar();
            alignDashboardColumns();
        });
        document.getElementById('cal-next').addEventListener('click', () => {
            viewMonth++; if (viewMonth > 11) { viewMonth = 0; viewYear++; }
            renderCalendar();
            alignDashboardColumns();
        });

        renderCalendar();
        renderAgenda();

        // Even out the dashboard columns against the Quick Actions + Activity
        // History stack (the one predictable height on the page): grow the trend
        // chart to fill the left column's shortfall with real chart content, and
        // either pad or clamp the calendar card to the same target — padded when
        // its natural content falls short, clamped with an internal scroll when a
        // long title or a busy activities table would otherwise balloon it taller
        // than a normal card.
        const trendBaseHeight = 230;
        function alignDashboardColumns() {
            const left = document.getElementById('tm-leftcol');
            const qa = document.getElementById('tm-qa');
            if (!left || !qa || !calCard) return;
            const midStack = qa.closest('.tm-stack');

            calCard.style.paddingBottom = '';
            agendaEl.style.maxHeight = '';
            agendaEl.style.overflowY = '';
            if (window.__tmTrendChart) window.__tmTrendChart.updateOptions({ chart: { height: trendBaseHeight } }, false, false);

            // Below 1100px the columns stack into one — nothing to even out.
            if (window.innerWidth <= 1100) return;

            requestAnimationFrame(() => {
                const target = midStack.offsetHeight;

                const leftShort = target - left.offsetHeight;
                if (leftShort > 4 && window.__tmTrendChart) {
                    window.__tmTrendChart.updateOptions({ chart: { height: trendBaseHeight + leftShort } }, false, false);
                }

                const chrome = calCard.offsetHeight - agendaEl.offsetHeight; // nav + grid + legend + card padding
                const calShort = target - calCard.offsetHeight;
                if (calShort > 4) {
                    calCard.style.paddingBottom = (20 + calShort) + 'px';
                } else if (calShort < -4) {
                    const budget = Math.max(target - chrome, 80);
                    agendaEl.style.maxHeight = budget + 'px';
                    agendaEl.style.overflowY = 'auto';
                }
            });
        }

        window.addEventListener('load', () => setTimeout(alignDashboardColumns, 300));
        let alignResizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(alignResizeTimer);
            alignResizeTimer = setTimeout(alignDashboardColumns, 150);
        });
    })();
</script>
@endsection
