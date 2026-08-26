@extends("template",['menu' => $menu])

@section("content")
<div class="layout-px-spacing">
    <div class="tm-page">
        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">Attendance Report</h1>
                <div class="tm-crumb"><a href="{{ url('minutes/list') }}">Minutes</a> / Attendance Report</div>
            </div>
        </div>

        <div class="tm-card">
            <form method="get" action="{{ url('minutes/attendance_report') }}" class="row g-3 align-items-end">
                <div class="col-auto">
                    <label class="tm-label">From</label>
                    <input type="date" class="tm-input" name="from" value="{{ $from }}">
                </div>
                <div class="col-auto">
                    <label class="tm-label">To</label>
                    <input type="date" class="tm-input" name="to" value="{{ $to }}">
                </div>
                <div class="col-auto">
                    <label class="tm-label">Session type</label>
                    <select name="type" class="tm-select">
                        <option value="">All types</option>
                        @foreach($sessionTypes as $st)
                            <option {{ $type === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="tm-btn tm-btn-primary">Apply</button>
                </div>
                <div class="col-auto">
                    <a class="tm-btn tm-btn-outline"
                       href="{{ url('minutes/attendance_report') }}?from={{ $from }}&to={{ $to }}&type={{ urlencode($type) }}&export=csv">Export CSV</a>
                </div>
            </form>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-6 col-md"><div class="tm-card text-center"><div style="font-size:24px;font-weight:800">{{ $summary['totals']['sessions'] }}</div><div class="tm-muted" style="font-size:12px">Sessions held</div></div></div>
            <div class="col-6 col-md"><div class="tm-card text-center"><div style="font-size:24px;font-weight:800">{{ $summary['totals']['P'] }}</div><div class="tm-muted" style="font-size:12px">Total Present</div></div></div>
            <div class="col-6 col-md"><div class="tm-card text-center"><div style="font-size:24px;font-weight:800">{{ $summary['totals']['L'] }}</div><div class="tm-muted" style="font-size:12px">Total Late</div></div></div>
            <div class="col-6 col-md"><div class="tm-card text-center"><div style="font-size:24px;font-weight:800">{{ $summary['totals']['E'] }}</div><div class="tm-muted" style="font-size:12px">Total Excused</div></div></div>
            <div class="col-6 col-md"><div class="tm-card text-center"><div style="font-size:24px;font-weight:800">{{ $summary['totals']['A'] }}</div><div class="tm-muted" style="font-size:12px">Total Absent</div></div></div>
        </div>

        <div class="tm-card mt-3">
            <div style="margin-bottom:12px">
                <input type="text" id="reportSearch" class="tm-input" placeholder="Search by name or position..." autocomplete="off">
            </div>
            <div class="tm-table-wrap">
                <table class="tm-table" id="reportTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Member</th><th>Position</th>
                            <th style="text-align:center">Present</th><th style="text-align:center">Late</th>
                            <th style="text-align:center">Excused</th><th style="text-align:center">Absent</th>
                            <th style="text-align:center">Present rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary['members'] as $m)
                            <tr>
                                <td style="font-weight:600">{{ $m['name'] }}</td>
                                <td class="tm-muted">{{ $m['position'] }}</td>
                                <td style="text-align:center">{{ $m['P'] }}</td>
                                <td style="text-align:center">{{ $m['L'] }}</td>
                                <td style="text-align:center">{{ $m['E'] }}</td>
                                <td style="text-align:center">{{ $m['A'] }}</td>
                                <td style="text-align:center;font-weight:700;{{ $m['present_rate'] < 75 ? 'color:#C0392B' : '' }}">{{ $m['present_rate'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="tm-table-empty">No members yet. Add members under System → Members.</td></tr>
                        @endforelse
                        <tr id="reportNoMatch" style="display:none"><td colspan="7" class="tm-table-empty">No members match your search.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section("additional_footer")
<script>
    // Live search: filter report rows by member name or position
    (function () {
        const search = document.getElementById('reportSearch');
        if (!search) return;
        const noMatch = document.getElementById('reportNoMatch');
        const dataRows = Array.from(document.querySelectorAll('#reportTable tbody tr'))
            .filter(r => r.id !== 'reportNoMatch' && !r.querySelector('.tm-table-empty'));

        search.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            let visible = 0;
            dataRows.forEach(r => {
                const text = (r.children[0].textContent + ' ' + r.children[1].textContent).toLowerCase();
                const show = q === '' || text.includes(q);
                r.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            noMatch.style.display = (dataRows.length && visible === 0) ? '' : 'none';
        });
    })();
</script>
@endsection
