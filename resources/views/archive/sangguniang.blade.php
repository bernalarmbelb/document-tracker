@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<style>#zero-config.tm-table tbody tr { cursor: default; } #zero-config.tm-table td.tm-wrap-cell { white-space: normal; min-width: 220px; }</style>
@endsection

@section("content")
<div class="layout-px-spacing">
    <div class="tm-page">
        <div class="tm-page-head">
            <div><h1 class="tm-title">Archive</h1><div class="tm-crumb"><a href="{{ url('dashboard') }}">Home</a> / Archive / Sangguniang Activities</div></div>
        </div>

        @include('archive.partials.tabs', ['active' => 'sangguniang'])

        <div class="tm-card">
            <table id="zero-config" class="tm-table" style="width:100%">
                <thead>
                    <tr><th>Activity Name</th><th>Status</th><th>Date</th><th>Time</th><th>Location</th><th class="no-content">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($records as $item)
                        @php $st = $item->status; $stClass = $st=='COMPLETED' ? 'tm-badge-ok' : ($st=='ONGOING' ? 'tm-badge-info' : ($st=='UPCOMING' ? 'tm-badge-warn' : 'tm-badge')); @endphp
                        <tr>
                            <td class="tm-col-title">{{ $item->activity_title }}</td>
                            <td><span class="tm-badge {{ $stClass }}">{{ $item->status }}</span></td>
                            <td>{{ date("M d, Y", strtotime($item->activity_date)) }}</td>
                            <td>{{ date("h:iA", strtotime($item->activity_date)) }}</td>
                            <td class="tm-wrap-cell">{{ $item->location }}</td>
                            <td class="tm-actions-cell">
                                <ul class="tm-actions">
                                    <li><a href="javascript:void(0);" onclick='return confirm_unarchive("{{ url("archive/sangguniang_unarchive/".$item->id) }}")' class="bs-tooltip text-warning" data-bs-toggle="tooltip" title="Remove from Archive"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder-minus p-1 br-8 mb-1 text-warning"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path><line x1="9" y1="14" x2="15" y2="14"></line></svg></a></li>
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/table/datatable/datatables.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>
<script src="{{ asset("js/app.js") }}"></script>
<script>
    tmInitDataTable('#zero-config', { pageLength: 10, searchPlaceholder: 'Search archive...', order: [[2, 'desc']], columnDefs: [{ orderable: false, targets: [5] }] });
    function confirm_unarchive($url) { Swal.fire({ title: 'Are you sure?', text: "This will remove the record from the archive list.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: 'Yes, remove it!' }).then((r) => { if (r.isConfirmed) { window.location.href = $url; } }); }
</script>
@endsection
