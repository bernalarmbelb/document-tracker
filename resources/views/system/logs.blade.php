@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/assets/css/light/components/modal.css") }}" rel="stylesheet" type="text/css" />
<style>
    #tbldetails { width: 100%; border-collapse: collapse; font-size: 12px; }
    #tbldetails th, #tbldetails td { padding: 8px 10px; border-bottom: 1px solid var(--tm-line); text-align: left; }
    #tbldetails th { color: var(--tm-muted); text-transform: uppercase; font-size: 10px; }
</style>
@endsection

@section("content")

<div class="layout-px-spacing">
    <div class="tm-page">

        {{-- Page header + toolbar --}}
        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">System Logs</h1>
                <div class="tm-crumb"><a href="{{ url('system/logs') }}">User Management</a> / Logs</div>
            </div>
            <div class="d-flex align-items-center" style="gap:10px; flex-wrap:wrap">
                <form method="post" action="{{ url('/system/logs_filter/') }}" id="frmviewrecords" style="margin:0; display:flex; gap:8px; align-items:center">
                    @csrf
                    <input type="date" value="{{ date("Y-m-d", strtotime($selected_date_start)) }}" name="logs_selected_date_start" class="tm-input" style="width:auto" required onchange="this.form.submit()" />
                    <span class="tm-muted">to</span>
                    <input type="date" value="{{ date("Y-m-d", strtotime($selected_date_end)) }}" name="logs_selected_date_end" class="tm-input" style="width:auto" required onchange="this.form.submit()" />
                </form>
                <a href="{{ url('system/print_logs_list') }}" class="tm-btn tm-btn-outline" title="Export list">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg> Export
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="tm-card">
            <table id="zero-config" class="tm-table" style="width:100%">
                <thead>
                    <tr>
                        <th>Log Date</th>
                        <th>User</th>
                        <th>Activity</th>
                        <th class="no-content">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $item)
                        <tr data-record="{{ $item->record_id }}">
                            <td data-order="{{ strtotime($item->activity_date) }}" style="white-space:nowrap">
                                <span style="font-weight:600">{{ date("M d, Y, D", strtotime($item->activity_date)) }}</span>
                                <br/><span class="tm-muted" style="font-size:11px">{{ date("h:iA", strtotime($item->activity_date)) }}</span>
                            </td>
                            <td>
                                {{ $item->fullname }}
                                <br/><span class="tm-muted" style="font-size:11px">Username: {{ $item->username }}</span>
                            </td>
                            <td>{{ $item->action }}</td>
                            <td class="tm-actions-cell">
                                <ul class="tm-actions">
                                    <li><a onclick="view_details('{{ $item->record_id }}')" href="javascript:void(0)" class="bs-tooltip text-primary" data-bs-toggle="modal" data-bs-target="#view-details" data-bs-placement="top" title="View Details"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye p-1 br-8 mb-1 text-primary"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a></li>
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

<div class="modal fade" id="view-details" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tabsModalLabel">View Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <table id="tbldetails">
                    <thead><tr><th>Field</th><th>Value</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/table/datatable/datatables.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>
<script src="{{ asset("js/app.js") }}"></script>

<script>
    $(document).ready(function() {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    });

    tmInitDataTable('#zero-config', {
        pageLength: 10,
        searchPlaceholder: 'Search logs...',
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [3] }]
    });

    // Row click -> open details
    (function () {
        $('#zero-config tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('a, .tm-actions, button, input').length) return;
            var rec = $(this).data('record');
            if (!rec) return;
            view_details(rec);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('view-details')).show();
        });
    })();

    function view_details(param) {
        $.ajax({
            type: 'POST', data: { record_id: param }, dataType: "json",
            url: '{{ url("system/get_log_details") }}',
            success: function (data) {
                let jsonString = data['record_info']['description'];
                let tableBody = document.querySelector("#tbldetails tbody");
                tableBody.innerHTML = "";
                if (jsonString != '') {
                    let d = JSON.parse(jsonString);
                    Object.entries(d).forEach(([key, value]) => {
                        if (key == 'editor_content') return;
                        let tr = document.createElement("tr");
                        let tdKey = document.createElement("td"); tdKey.textContent = key;
                        let tdValue = document.createElement("td"); tdValue.textContent = value;
                        tr.appendChild(tdKey); tr.appendChild(tdValue); tableBody.appendChild(tr);
                    });
                }
                let tr = document.createElement("tr");
                let tdKey = document.createElement("td"); tdKey.textContent = 'Log date';
                let tdValue = document.createElement("td"); tdValue.textContent = data['record_info']['activity_date'];
                tr.appendChild(tdKey); tr.appendChild(tdValue); tableBody.appendChild(tr);
                tr = document.createElement("tr");
                tdKey = document.createElement("td"); tdKey.textContent = 'IP Address';
                tdValue = document.createElement("td"); tdValue.textContent = data['record_info']['ip_address'];
                tr.appendChild(tdKey); tr.appendChild(tdValue); tableBody.appendChild(tr);
            },
            error: function (XHR, textStatus, errorThrown) { console.log(errorThrown); console.log(XHR.responseText); }
        });
    }
</script>
@endsection
