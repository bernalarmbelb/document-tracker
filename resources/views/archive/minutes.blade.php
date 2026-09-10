@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<style>#zero-config.tm-table td, #zero-config.tm-table th { white-space: nowrap; } #zero-config.tm-table td.tm-wrap-cell { white-space: normal; min-width: 220px; }</style>
@endsection

@section("content")
<div class="layout-px-spacing">
    <div class="tm-page">
        <div class="tm-page-head">
            <div><h1 class="tm-title">Archive</h1><div class="tm-crumb"><a href="{{ url('dashboard') }}">Home</a> / Archive / Minutes</div></div>
        </div>

        @include('archive.partials.tabs', ['active' => 'minutes'])

        <div class="tm-card">
            <table id="zero-config" class="tm-table" style="width:100%">
                <thead>
                    <tr><th>Series No.</th><th>Date Created</th><th>Category</th><th>Short Description</th><th>Presiding Officer</th><th>Barangay</th><th>Venue</th><th class="no-content">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($records as $item)
                        <tr data-id="{{ $item->id }}">
                            <td><a href="{{ url("minutes/view/".$item->id) }}" style="color:var(--tm-primary);font-weight:600;text-decoration:none">{{ $item->series_number }}</a></td>
                            <td>{{ date("M d, Y", strtotime($item->date_created)) }}</td>
                            <td>{{ $item->category }}</td>
                            <td class="tm-col-title">{{ $item->short_description }}</td>
                            <td>{{ $item->presiding_officer }}</td>
                            <td>{{ $item->barangay_name }}</td>
                            <td class="tm-wrap-cell">{{ $item->venue }}</td>
                            <td class="tm-actions-cell">
                                <ul class="tm-actions">
                                    <li><a href="{{ url("minutes/view/".$item->id) }}" class="bs-tooltip text-primary" data-bs-toggle="tooltip" title="View"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye p-1 br-8 mb-1 text-primary"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a></li>
                                    <li><a href="javascript:void(0);" onclick='return confirm_unarchive("{{ url("archive/minutes_unarchive/".$item->id) }}")' class="bs-tooltip text-warning" data-bs-toggle="tooltip" title="Remove from Archive"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder-minus p-1 br-8 mb-1 text-warning"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path><line x1="9" y1="14" x2="15" y2="14"></line></svg></a></li>
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="tm-row-menu" class="tm-row-menu">
    <a data-act="view"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg> View</a>
    <div class="tm-row-menu-divider"></div>
    <a data-act="unarchive"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path><line x1="9" y1="14" x2="15" y2="14"></line></svg> Remove from Archive</a>
</div>
@endsection

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/table/datatable/datatables.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>
<script src="{{ asset("js/app.js") }}"></script>
<script>
    tmInitDataTable('#zero-config', { pageLength: 10, searchPlaceholder: 'Search archive...', order: [[1, 'desc']], columnDefs: [{ orderable: false, targets: [7] }, { type: 'tm-series', targets: [0] }] });
    (function () {
        var menu = document.getElementById('tm-row-menu'); if (!menu) return;
        var curId = null, viewBase = "{{ url('minutes/view') }}", unarchiveBase = "{{ url('archive/minutes_unarchive') }}";
        function hideMenu() { menu.style.display = 'none'; }
        $('#zero-config tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('a, .tm-actions, button, input').length) return;
            curId = $(this).data('id'); if (!curId) return; menu.style.display = 'block';
            var x = Math.min(e.clientX, window.innerWidth - menu.offsetWidth - 12), y = Math.min(e.clientY, window.innerHeight - menu.offsetHeight - 12);
            menu.style.left = Math.max(x, 8) + 'px'; menu.style.top = Math.max(y, 8) + 'px'; e.stopPropagation();
        });
        $(menu).on('click', '[data-act]', function () { var act = this.getAttribute('data-act'); hideMenu(); if (act === 'view') window.location.href = viewBase + '/' + curId; else if (act === 'unarchive') confirm_unarchive(unarchiveBase + '/' + curId); });
        document.addEventListener('click', function (e) { if (!menu.contains(e.target)) hideMenu(); });
        document.addEventListener('scroll', hideMenu, true); window.addEventListener('resize', hideMenu);
    })();
    function confirm_unarchive($url) { Swal.fire({ title: 'Are you sure?', text: "This will remove the record from the archive list.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: 'Yes, remove it!' }).then((r) => { if (r.isConfirmed) { window.location.href = $url; } }); }
</script>
@endsection
