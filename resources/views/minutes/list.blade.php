@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<style>
    #zero-config.tm-table td, #zero-config.tm-table th { white-space: nowrap; }
    #zero-config.tm-table td.tm-wrap-cell { white-space: normal; min-width: 220px; }
</style>
@endsection

@section("content")

<div class="layout-px-spacing">
    <div class="tm-page">

        {{-- Page header + toolbar --}}
        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">Minutes</h1>
                <div class="tm-crumb"><a href="{{ url('minutes/list') }}">Minutes</a> / List</div>
            </div>
            <div class="d-flex align-items-center" style="gap:10px; flex-wrap:wrap">
                <form action="{{ url('/minutes/search/') }}" method="post" autocomplete="off" style="margin:0">
                    @csrf
                    <input id="t-text" type="text" name="keyword" value="{{ $keyword }}" placeholder="Search minutes..." class="tm-input" style="width:240px" onkeydown="if(event.key === 'Enter') this.form.submit()">
                </form>
                @if(Auth::user()->hasPermission('Add Minute'))
                    <a href="{{ url("/minutes/add/1") }}" class="tm-btn tm-btn-primary">＋ Regular Session</a>
                    <a href="{{ url("/minutes/add/2") }}" class="tm-btn tm-btn-danger">＋ Committee Hearing</a>
                    <a href="{{ url("/minutes/add/3") }}" class="tm-btn tm-btn-success">＋ Special Session</a>
                @endif
                <a href="{{ url('minutes/list_grid') }}" class="tm-btn tm-btn-outline">Grid View</a>
                <a href="{{ url('minutes/attendance_report') }}" class="tm-btn tm-btn-outline">Attendance Report</a>
            </div>
        </div>

        {{-- Table --}}
        <div class="tm-card">
            <table id="zero-config" class="tm-table" style="width:100%">
                <thead>
                    <tr>
                        <th>Series No.</th>
                        <th>Date Created</th>
                        <th>Agenda</th>
                        <th>Presiding Officer</th>
                        <th>Venue</th>
                        <th>Category</th>
                        <th class="no-content">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $item)
                        @php
                            $cat = $item->category;
                            $catClass = $cat=='Regular Session' ? 'tm-badge-info' : ($cat=='Special Session' ? 'tm-badge-ok' : ($cat=='Committee Hearing' ? 'tm-badge-bad' : 'tm-badge'));
                        @endphp
                        <tr data-id="{{ $item->id }}" data-title="{{ $item->agenda_1 }}">
                            <td><a href="{{ url("minutes/view/".$item->id) }}" style="color:var(--tm-primary);font-weight:600;text-decoration:none">{{ $item->series_number }}</a></td>
                            <td>{{ date("M d, Y", strtotime($item->date_created)) }}</td>
                            <td class="tm-col-title">{{ smart_title($item->agenda_1) }}</td>
                            <td>{{ $item->presiding_officer }}</td>
                            <td class="tm-wrap-cell">{{ $item->venue }}</td>
                            <td><span class="tm-badge {{ $catClass }}">{{ $item->category }}</span></td>
                            <td class="tm-actions-cell">
                                <ul class="tm-actions">
                                    @if(Auth::user()->hasPermission('Edit Minute'))
                                        <li><a href="{{ url("minutes/edit/".$item->id) }}" class="bs-tooltip text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 p-1 br-8 mb-1 text-success"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></a></li>
                                    @endif
                                    <li><a href="{{ url("minutes/view/".$item->id) }}" class="bs-tooltip text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye p-1 br-8 mb-1 text-primary"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a></li>
                                    @if(Auth::user()->hasPermission('Archive Minute'))
                                        <li><a href="javascript:void(0);" onclick='return confirm_archive("{{ url("minutes/move_to_archive/".$item->id) }}")' class="bs-tooltip text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Archive"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-archive p-1 br-8 mb-1 text-warning"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg></a></li>
                                    @endif
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- Row click context menu --}}
<div id="tm-row-menu" class="tm-row-menu">
    @if(Auth::user()->hasPermission('Edit Minute'))
        <a data-act="edit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg> Edit</a>
    @endif
    <a data-act="view"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg> View</a>
    @if(Auth::user()->hasPermission('Archive Minute'))
        <div class="tm-row-menu-divider"></div>
        <a data-act="archive"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg> Archive</a>
    @endif
</div>
@endsection

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/table/datatable/datatables.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>
<script src="{{ asset("js/app.js") }}"></script>

<script>
    window.onload = function() {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        const messages = @json(session('messages'));
        const showMessages = async () => {
            for (const message of messages) {
                await Swal.fire({ title: message['type'].toUpperCase(), text: message['text'], icon: message['type'], confirmButtonText: 'OK' });
            }
        };
        showMessages();
    };

    tmInitDataTable('#zero-config', {
        pageLength: 10,
        searchPlaceholder: 'Filter minutes...',
        order: [[1, 'desc']],
        columnDefs: [{ orderable: false, targets: [6] }]
    });

    // Row click -> floating action menu
    (function () {
        var menu = document.getElementById('tm-row-menu');
        if (!menu) return;
        var curId = null;
        var editBase    = "{{ url('minutes/edit') }}";
        var viewBase    = "{{ url('minutes/view') }}";
        var archiveBase = "{{ url('minutes/move_to_archive') }}";

        function hideMenu() { menu.style.display = 'none'; }

        $('#zero-config tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('a, .tm-actions, button, input').length) return;
            curId = $(this).data('id');
            if (!curId) return;
            menu.style.display = 'block';
            var mw = menu.offsetWidth, mh = menu.offsetHeight;
            var x = Math.min(e.clientX, window.innerWidth - mw - 12);
            var y = Math.min(e.clientY, window.innerHeight - mh - 12);
            menu.style.left = Math.max(x, 8) + 'px';
            menu.style.top  = Math.max(y, 8) + 'px';
            e.stopPropagation();
        });

        $(menu).on('click', '[data-act]', function () {
            var act = this.getAttribute('data-act');
            hideMenu();
            if (act === 'edit')        window.location.href = editBase + '/' + curId;
            else if (act === 'view')   window.location.href = viewBase + '/' + curId;
            else if (act === 'archive') confirm_archive(archiveBase + '/' + curId);
        });

        document.addEventListener('click', function (e) { if (!menu.contains(e.target)) hideMenu(); });
        document.addEventListener('scroll', hideMenu, true);
        window.addEventListener('resize', hideMenu);
    })();

    function confirm_archive($url) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action will move the record to the archive list.",
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, archive it!'
        }).then((result) => { if (result.isConfirmed) { window.location.href = $url; } });
    }
</script>
@endsection
