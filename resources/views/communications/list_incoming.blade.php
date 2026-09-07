@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/assets/css/light/components/modal.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/assets/css/light/components/tabs.css") }}" rel="stylesheet" type="text/css">
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
                <h1 class="tm-title">Incoming Communications</h1>
                <div class="tm-crumb"><a href="{{ url('communications/incoming_list') }}">Communications</a> / Incoming</div>
            </div>
            <div class="d-flex align-items-center" style="gap:10px; flex-wrap:wrap">
                @if(Auth::user()->hasPermission('Add Communication'))
                    <button type="button" class="tm-btn tm-btn-secondary" data-bs-toggle="modal" data-bs-target="#add-communication">＋ Add Communication</button>
                @endif
                <a href="{{ url('communications/print_incoming') }}" class="tm-btn tm-btn-outline" title="Export list">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg> Export
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="tm-card">
            <table id="zero-config" class="tm-table" style="width:100%">
                <thead>
                    <tr>
                        <th>Date Received</th>
                        <th>Source</th>
                        <th>Particulars</th>
                        <th>Actions Taken</th>
                        <th>Received By</th>
                        <th class="no-content">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $item)
                        <tr data-id="{{ $item->id }}" data-title="{{ $item->particulars }}">
                            <td>{{ date("M d, Y", strtotime($item->date_received)) }}</td>
                            <td>{{ $item->source }}</td>
                            <td class="tm-col-title">{{ $item->particulars }}</td>
                            <td class="tm-wrap-cell">{{ $item->actions_taken }}</td>
                            <td>{{ $item->received_by }}</td>
                            <td class="tm-actions-cell">
                                <ul class="tm-actions">
                                    <li><a href="{{ url("communications/view/".$item->id) }}" class="bs-tooltip text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye p-1 br-8 mb-1 text-primary"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a></li>
                                    @if(Auth::user()->hasPermission('Edit Communication'))
                                        <li><a href="javascript:void(0);" onclick="view_details('{{ $item->id }}')" data-bs-toggle="modal" data-bs-target="#edit-communication" class="bs-tooltip text-success" data-bs-placement="top" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 p-1 br-8 mb-1 text-success"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></a></li>
                                    @endif
                                    @if(Auth::user()->hasPermission('Add Communication'))
                                        <li><a href="javascript:void(0);" onclick='view_files("{{ $item->id }}", @json($item->particulars))' class="bs-tooltip text-info" data-bs-toggle="modal" data-bs-target="#add-supporting-documents" data-bs-placement="top" title="Add Supporting Document"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-share p-1 br-8 mb-1 text-info"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg></a></li>
                                    @endif
                                    @if(Auth::user()->hasPermission('Archive Communication'))
                                        <li><a href="javascript:void(0);" onclick='return confirm_archive("{{ url("communications/move_to_archive_incoming/".$item->id) }}")' class="bs-tooltip text-warning" data-bs-placement="top" title="Archive"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-archive p-1 br-8 mb-1 text-warning"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg></a></li>
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
    <a data-act="view"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg> View</a>
    @if(Auth::user()->hasPermission('Edit Communication'))
        <a data-act="edit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg> Edit</a>
    @endif
    @if(Auth::user()->hasPermission('Add Communication'))
        <a data-act="adddoc"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg> Add Supporting Document</a>
    @endif
    @if(Auth::user()->hasPermission('Archive Communication'))
        <div class="tm-row-menu-divider"></div>
        <a data-act="archive"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg> Archive</a>
    @endif
</div>

<div class="modal fade" id="add-communication" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tabsModalLabel">Add Communication</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="simple-pill">
              <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-incoming-tab" data-bs-toggle="pill" data-bs-target="#pills-incoming" type="button" role="tab" aria-controls="pills-incoming" aria-selected="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-down-right me-2"><line x1="7" y1="7" x2="17" y2="17"></line><polyline points="17 7 17 17 7 17"></polyline></svg>
                         Incoming
                    </button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-outgoing-tab" data-bs-toggle="pill" data-bs-target="#pills-outgoing" type="button" role="tab" aria-controls="pills-outgoing" aria-selected="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-up-right me-2"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                         Outgoing
                    </button>
                  </li>
              </ul>
              <div class="tab-content" id="pills-tabContent">
                  <div class="tab-pane fade show active" id="pills-incoming" role="tabpanel" aria-labelledby="pills-incoming-tab" tabindex="0">
                    <form class="row g-3" action="{{ url('/communications/save_add_incoming/') }}" method="post" autocomplete="off">
                        @csrf
                        <div class="col-12"><label class="tm-label">Date Received <span class="text-danger">*</span></label><input type="date" class="tm-input" value={{ date("Y-m-d") }} required name="date_received"></div>
                        <div class="col-12"><label class="tm-label">Source <span class="text-danger">*</span></label><input type="text" class="tm-input" required name="source" ></div>
                        <div class="col-12"><label class="tm-label">Particulars <span class="text-danger">*</span></label><textarea class="tm-input" rows="4" required name="particulars"></textarea></div>
                        <div class="col-12"><label class="tm-label">Actions Taken <span class="text-danger">*</span></label><textarea class="tm-input" rows="3" required name="actions_taken"></textarea></div>
                        <div class="col-12"><label class="tm-label">Person who received <span class="text-danger">*</span></label><input type="text" class="tm-input" required name="received_by" ></div>
                        <div class="d-grid gap-2 col-12 mx-auto"><button type="submit" name="btnsave" value="1" class="tm-btn tm-btn-primary tm-btn-block">Save</button></div>
                    </form>
                  </div>
                  <div class="tab-pane fade" id="pills-outgoing" role="tabpanel" aria-labelledby="pills-outgoing-tab" tabindex="0">
                    <form class="row g-3" action="{{ url('/communications/save_add_outgoing/') }}" method="post" autocomplete="off">
                        @csrf
                        <div class="col-12"><label class="tm-label">Date Released <span class="text-danger">*</span></label><input type="date" class="tm-input" value={{ date("Y-m-d") }} required name="date_released"></div>
                        <div class="col-12"><label class="tm-label">Addressee <span class="text-danger">*</span></label><input type="text" class="tm-input" required name="addressee" ></div>
                        <div class="col-12"><label class="tm-label">Particulars <span class="text-danger">*</span></label><textarea class="tm-input" rows="4" required name="particulars"></textarea></div>
                        <div class="col-12"><label class="tm-label">Person who released <span class="text-danger">*</span></label><input type="text" class="tm-input" required name="released_by" ></div>
                        <div class="d-grid gap-2 col-12 mx-auto"><button type="submit" name="btnsave" value="1" class="tm-btn tm-btn-primary tm-btn-block">Save</button></div>
                    </form>
                  </div>
              </div>
          </div>
          </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="edit-communication" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tabsModalLabel">Edit Communication</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
                    <form class="row g-3" action="{{ url('/communications/save_changes_incoming/') }}" method="post" autocomplete="off">
                        @csrf
                        <input type="hidden" name="communication_id" id="edit_communication_id"/>
                        <div class="col-12"><label class="tm-label">Date Received <span class="text-danger">*</span></label><input type="date" class="tm-input" value={{ date("Y-m-d") }} required id="edit_date_received" name="date_received"></div>
                        <div class="col-12"><label class="tm-label">Source <span class="text-danger">*</span></label><input type="text" class="tm-input" required id="edit_source" name="source" ></div>
                        <div class="col-12"><label class="tm-label">Particulars <span class="text-danger">*</span></label><textarea class="tm-input" rows="4" required id="edit_particulars" name="particulars"></textarea></div>
                        <div class="col-12"><label class="tm-label">Actions Taken <span class="text-danger">*</span></label><textarea class="tm-input" rows="3" required id="edit_actions_taken" name="actions_taken"></textarea></div>
                        <div class="col-12"><label class="tm-label">Person who received <span class="text-danger">*</span></label><input type="text" class="tm-input" required id="edit_received_by" name="received_by" ></div>
                        <div class="d-grid gap-2 col-12 mx-auto"><button type="submit" name="btnsave" value="1" class="tm-btn tm-btn-primary tm-btn-block">Save</button></div>
                    </form>
          </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="add-supporting-documents" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tabsModalLabel">Add Supporting Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6 class='text-primary mb-2' id='h6_add_supporting_document'></h6>
                    <div class="widget-content widget-content-area blog-create-section mb-3">
                        <div class="m-3">
                            <form action="{{ url('/communications/upload_supporting_documents_incoming/') }}" method="post" autocomplete="off" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="supporting_document_communication_id" name="communication_id" value="">
                                <input type="file" name="myfile" onchange="this.form.submit()">
                            </form>
                        </div>
                    </div>
                    <ul id="my_files" class="list-group mt-2"></ul>
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
        searchPlaceholder: 'Search communications...',
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [5] }]
    });

    // Row click -> floating action menu
    (function () {
        var menu = document.getElementById('tm-row-menu');
        if (!menu) return;
        var curId = null, curTitle = '';
        var archiveBase = "{{ url('communications/move_to_archive_incoming') }}";
        var viewBase = "{{ url('communications/view') }}";

        function hideMenu() { menu.style.display = 'none'; }

        $('#zero-config tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('a, .tm-actions, button, input').length) return;
            curId = $(this).data('id');
            curTitle = $(this).data('title');
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
            if (act === 'view')        { window.location.href = viewBase + '/' + curId; }
            else if (act === 'edit')   { view_details(curId); bootstrap.Modal.getOrCreateInstance(document.getElementById('edit-communication')).show(); }
            else if (act === 'adddoc') { view_files(curId, curTitle); bootstrap.Modal.getOrCreateInstance(document.getElementById('add-supporting-documents')).show(); }
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

    function view_details(param) {
        $.ajax({
            type: 'POST', data: { communication_id: param }, dataType: "json",
            url: '{{ url("communications/get_incoming_info") }}',
            success: function (data) {
                document.getElementById('edit_communication_id').value = data['info']['id'];
                document.getElementById('edit_date_received').value = data['info']['date_received'];
                document.getElementById('edit_source').value = data['info']['source'];
                document.getElementById('edit_particulars').value = data['info']['particulars'];
                document.getElementById('edit_actions_taken').value = data['info']['actions_taken'];
                document.getElementById('edit_received_by').value = data['info']['received_by'];
            },
            error: function (XHR, textStatus, errorThrown) { console.log(errorThrown); console.log(XHR.responseText); }
        });
    }

    function view_files(param, paramparticulars) {
        document.getElementById('supporting_document_communication_id').value = param;
        document.getElementById('h6_add_supporting_document').innerHTML = paramparticulars;
        $.ajax({
            type: 'POST', data: { communication_id: param }, dataType: "json",
            url: '{{ url("communications/get_incoming_files") }}',
            success: function (data) {
                let items = data['rows'];
                const ul = document.getElementById('my_files');
                ul.innerHTML = '';
                items.forEach(item => {
                    const li = document.createElement('li');
                    li.classList.add('list-group-item'); li.classList.add('ps-1');
                    const a = document.createElement('a');
                    a.href = "{{ url('uploads_communications') }}/" + item.filename;
                    a.textContent = item.filename; a.target = '_blank'; a.title = "View File";
                    const b = document.createElement('a');
                    b.classList.add('me-2'); b.classList.add('text-danger');
                    b.href = "{{ url('communications/delete_uploaded_file_incoming') }}/" + item.id;
                    b.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 p-1 br-8 mb-1 delete-note"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>';
                    b.title = "Delete File";
                    b.onclick = function (event) { if (!confirm("Are you sure you want to delete this file?")) { event.preventDefault(); } };
                    li.appendChild(b); li.appendChild(a); ul.appendChild(li);
                });
            },
            error: function (XHR, textStatus, errorThrown) { console.log(errorThrown); console.log(XHR.responseText); }
        });
    }
</script>
@endsection
