@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<style>
    #zero-config.tm-table td, #zero-config.tm-table th { white-space: nowrap; }
    #zero-config.tm-table td.tm-wrap-cell { white-space: normal; min-width: 220px; }
    .tm-segbtns { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
</style>
@endsection

@section("content")

<div class="layout-px-spacing">
    <div class="tm-page">

        {{-- Page header + toolbar --}}
        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">Sangguniang Activities</h1>
                <div class="tm-crumb"><a href="{{ url('dashboard') }}">Home</a> / Activities</div>
            </div>
            <div class="d-flex align-items-center" style="gap:10px; flex-wrap:wrap">
                @if(Auth::user()->hasPermission('Add Activity'))
                    <a href="{{ url("/activities/add") }}" class="tm-btn tm-btn-secondary">＋ New Activity</a>
                @endif
                <a href="{{ url('activities/print_list/'.$mode) }}" class="tm-btn tm-btn-outline" title="Export list">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg> Export
                </a>
            </div>
        </div>

        {{-- Status filter segmented buttons --}}
        <div class="tm-segbtns">
            <a href="{{ url("activities/all") }}" class="tm-btn {{ $menu=='Activities - All' ? 'tm-btn-primary' : 'tm-btn-outline' }}">All</a>
            <a href="{{ url("activities/upcoming") }}" class="tm-btn {{ $menu=='Activities - Upcoming' ? 'tm-btn-primary' : 'tm-btn-outline' }}">Upcoming</a>
            <a href="{{ url("activities/ongoing") }}" class="tm-btn {{ $menu=='Activities - Ongoing' ? 'tm-btn-primary' : 'tm-btn-outline' }}">Ongoing</a>
            <a href="{{ url("activities/completed") }}" class="tm-btn {{ $menu=='Activities - Completed' ? 'tm-btn-primary' : 'tm-btn-outline' }}">Completed</a>
        </div>

        {{-- Table --}}
        <div class="tm-card">
            <table id="zero-config" class="tm-table" style="width:100%">
                <thead>
                    <tr>
                        <th>Activity Name</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th class="no-content">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $item)
                        @php
                            $st = $item->status;
                            $stClass = $st=='COMPLETED' ? 'tm-badge-ok' : ($st=='ONGOING' ? 'tm-badge-info' : ($st=='UPCOMING' ? 'tm-badge-warn' : 'tm-badge'));
                        @endphp
                        <tr data-id="{{ $item->id }}">
                            <td class="tm-col-title"><a href="{{ url("activities/view/".$item->id) }}" style="color:#333;text-decoration:none">{{ $item->activity_title }}</a></td>
                            <td><span class="tm-badge {{ $stClass }}">{{ $item->status }}</span></td>
                            <td>{{ date("M d, Y", strtotime($item->activity_date)) }}</td>
                            <td>{{ date("h:iA", strtotime($item->activity_date)) }}</td>
                            <td class="tm-wrap-cell">{{ $item->location }}</td>
                            <td class="tm-actions-cell">
                                <ul class="tm-actions">
                                    @if(Auth::user()->hasPermission('Edit Activity'))
                                        <li><a href="{{ url("activities/edit/".$item->id) }}" class="bs-tooltip text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 p-1 br-8 mb-1 text-success"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></a></li>
                                    @endif
                                    <li><a href="{{ url("activities/view/".$item->id) }}" class="bs-tooltip text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye p-1 br-8 mb-1 text-primary"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a></li>
                                    @if(Auth::user()->hasPermission('Archive Activity'))
                                        <li><a href="javascript:void(0);" onclick='return confirm_archive("{{ url("activities/move_to_archive/".$item->id) }}")' class="bs-tooltip text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Archive"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-archive p-1 br-8 mb-1 text-warning"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg></a></li>
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
    @if(Auth::user()->hasPermission('Edit Activity'))
        <a data-act="edit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg> Edit</a>
    @endif
    <a data-act="view"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg> View</a>
    @if(Auth::user()->hasPermission('Archive Activity'))
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
        searchPlaceholder: 'Search activities...',
        order: [[2, 'desc']],
        columnDefs: [{ orderable: false, targets: [5] }]
    });

    // Row click -> floating action menu
    (function () {
        var menu = document.getElementById('tm-row-menu');
        if (!menu) return;
        var curId = null;
        var editBase    = "{{ url('activities/edit') }}";
        var viewBase    = "{{ url('activities/view') }}";
        var archiveBase = "{{ url('activities/move_to_archive') }}";

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
