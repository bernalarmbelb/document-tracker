@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
<style>
    /* row hover + clickable cue */
    #documents-table tbody tr { cursor: pointer; }
    #trend, #bytype, #status { min-height: 230px; }
    .tm-dash { padding: 8px; }
    .tm-charts-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .tm-insights { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 6px; }
    .tm-insights .ins { border: 1px solid var(--tm-line); border-radius: 10px; padding: 12px 14px; }
    .tm-insights .ins-num { font-family: var(--tm-font-head); font-weight: 800; font-size: 24px; color: #1a1919; line-height: 1; }
    .tm-insights .ins-lbl { font-size: 12px; color: var(--tm-muted); font-weight: 600; margin-top: 5px; }
    .tm-insights .ins-sub { font-size: 11px; margin-top: 4px; }
    @media (max-width: 700px) { .tm-charts-2, .tm-insights { grid-template-columns: 1fr; } }
    /* keep DataTables from fighting the .tm-table borders */
    #documents-table.tm-table { width: 100% !important; }
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

        new ApexCharts(document.querySelector('#trend'), {
            chart: { type: 'bar', height: 230, fontFamily: font, toolbar: { show: false } },
            series: [{ name: 'Documents', data: @json($monthly_counts) }],
            xaxis: { categories: @json($monthly_labels) },
            colors: [primary], plotOptions: { bar: { borderRadius: 5, columnWidth: '55%' } },
            dataLabels: { enabled: false }, grid: { borderColor: 'rgba(51,51,51,.08)' }
        }).render();

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

        // Cap the Activity feed so the sidebar bottom aligns with the charts column.
        function alignActivityFeed() {
            const left = document.getElementById('tm-leftcol');
            const qa   = document.getElementById('tm-qa');
            const feed = document.getElementById('tm-activity-feed');
            if (!left || !qa || !feed) return;
            const card = feed.closest('.tm-card');
            feed.style.maxHeight = '0px';               // measure fixed chrome
            const chrome = card.offsetHeight;
            const target = left.offsetHeight - qa.offsetHeight - 16 /*stack gap*/ - chrome;
            feed.style.maxHeight = Math.max(target, 160) + 'px';
        }
        window.addEventListener('load', () => setTimeout(alignActivityFeed, 300));
        window.addEventListener('resize', alignActivityFeed);
    })();
</script>
@endsection
