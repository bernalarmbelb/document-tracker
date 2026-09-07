@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/assets/css/light/components/modal.css") }}" rel="stylesheet" type="text/css" />

<style>
    #zero-config.tm-table td, #zero-config.tm-table th { white-space: nowrap; }
    #zero-config.tm-table td.tm-wrap-cell { white-space: normal; min-width: 240px; }
</style>
@endsection

@section("content")

<div class="layout-px-spacing">
    <div class="tm-page">

        {{-- Page header + toolbar --}}
        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">Resolutions</h1>
                <div class="tm-crumb"><a href="{{ url('resolutions/list') }}">Resolutions</a> / List</div>
            </div>
            <div class="d-flex align-items-center" style="gap:10px; flex-wrap:wrap">
                <form method="post" action="{{ url('/resolutions/list_filter/') }}" id="frmviewrecords" style="margin:0">
                    @csrf
                    @php
                        $currentLabel = 'All Status';
                        foreach($all_statuses as $s){ if($s->status_code == $selected_status){ $currentLabel = $s->status_resolution; break; } }
                    @endphp
                    <div class="tm-dd" data-tm-dropdown data-tm-submit>
                        <button type="button" class="tm-dd-toggle">
                            <span class="tm-dd-label">{{ $currentLabel }}</span>
                            <svg class="tm-dd-caret" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        <div class="tm-dd-menu">
                            <a class="tm-dd-option {{ $selected_status=='ALL' ? 'active' : '' }}" data-value="ALL">All Status</a>
                            @foreach($all_statuses as $item)
                                <a class="tm-dd-option {{ $item->status_code==$selected_status ? 'active' : '' }}" data-value="{{ $item->status_code }}">{{ $item->status_resolution }}</a>
                            @endforeach
                        </div>
                        <input type="hidden" name="resolution_status" value="{{ $selected_status }}">
                    </div>
                </form>
                @if(Auth::user()->hasPermission('Add Resolution'))
                    <a href="{{ url("/resolutions/add") }}" class="tm-btn tm-btn-secondary">＋ Add Resolution</a>
                @endif
                <a href="{{ url('resolutions/print_list') }}" class="tm-btn tm-btn-outline" title="Export list">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg> Export
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="tm-card">
            <table id="zero-config" class="tm-table" style="width:100%">
                <thead>
                    <tr>
                        <th>Series No.</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Keywords/Tags</th>
                        <th>Sponsor</th>
                        <th>Date Created</th>
                        <th>Attested By</th>
                        <th>Recorded By</th>
                        <th>Approved By</th>
                        <th>Approved Date</th>
                        <th>Book Ref No.</th>
                        <th>Page Ref No.</th>
                        <th>Total Pages</th>
                        <th>Minute Ref No.</th>
                        <th>Status</th>
                        <th class="no-content">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $item)
                        @php
                            $st = $item->resolution_status;
                            $stClass = $st=='APPROVED' ? 'tm-badge-ok' : ($st=='DISAPPROVED' ? 'tm-badge-bad' : ($st=='IN ABEYANCE' ? 'tm-badge-warn' : ($st=='UNDER STUDY' ? 'tm-badge-sec' : ($st=='NO TEXT PROVIDED' ? 'tm-badge-info' : 'tm-badge'))));
                        @endphp
                        <tr data-id="{{ $item->id }}" data-title="{{ $item->title }}">
                            <td><a href="{{ url("resolutions/view/".$item->id) }}" style="color:var(--tm-primary);font-weight:600;text-decoration:none">{{ $item->series_number }}</a></td>
                            <td class="tm-col-title">{{ smart_title($item->title) }}</td>
                            <td>{{ $item->author_name }}</td>
                            <td class="tm-wrap-cell">{{ $item->keywords_tags }}</td>
                            <td>{{ $item->sponsor }}</td>
                            <td>{{ date("M d, Y", strtotime($item->date_created)) }}</td>
                            <td>{{ $item->attested_by }}</td>
                            <td>{{ $item->recorded_by }}</td>
                            <td>{{ $item->approved_by }}</td>
                            <td>{{ date("M d, Y", strtotime($item->approved_date)) }}</td>
                            <td>{{ $item->book_ref_no }}</td>
                            <td>{{ $item->page_ref_no }}</td>
                            <td>{{ $item->total_pages }}</td>
                            <td>{{ $item->minute_ref_no }}</td>
                            <td><span class="tm-badge {{ $stClass }}">{{ $item->resolution_status }}</span></td>
                            <td class="tm-actions-cell">
                                <ul class="tm-actions">
                                    @if(Auth::user()->hasPermission('Edit Resolution'))
                                        <li><a href="{{ url("resolutions/edit/".$item->id) }}" class="bs-tooltip text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 p-1 br-8 mb-1 text-success"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></a></li>
                                    @endif
                                    <li><a href="{{ url("resolutions/view/".$item->id) }}" class="bs-tooltip text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye p-1 br-8 mb-1 text-primary"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a></li>
                                    @if(Auth::user()->hasPermission('Add Resolution'))
                                        <li><a href="javascript:void(0);" onclick='view_files("{{ $item->id }}", @json($item->title))' class="bs-tooltip text-info" data-bs-toggle="modal" data-bs-target="#add-supporting-documents" title="Add Supporting Document"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-share p-1 br-8 mb-1 text-info"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg></a></li>
                                    @endif
                                    @if(Auth::user()->hasPermission('Archive Resolution'))
                                        <li><a href="javascript:void(0);" onclick='return confirm_archive("{{ url("resolutions/move_to_archive/".$item->id) }}")' class="bs-tooltip text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Archive"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-archive p-1 br-8 mb-1 text-warning"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg></a></li>
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
    @if(Auth::user()->hasPermission('Edit Resolution'))
        <a data-act="edit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg> Edit</a>
    @endif
    <a data-act="view"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg> View</a>
    @if(Auth::user()->hasPermission('Add Resolution'))
        <a data-act="adddoc"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg> Add Supporting Document</a>
    @endif
    @if(Auth::user()->hasPermission('Archive Resolution'))
        <div class="tm-row-menu-divider"></div>
        <a data-act="archive"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg> Archive</a>
    @endif
</div>

<div class="modal fade" id="add-supporting-documents" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tabsModalLabel">Add Supporting Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6 class='text-primary mb-2' id='h6_add_supporting_document'></h6>

                <div class="widget-content widget-content-area blog-create-section mb-3">
                    <div class="m-3">
                        <form  action="{{ url('/resolutions/upload_supporting_documents_single/') }}" method="post" autocomplete="off" enctype="multipart/form-data">  
                            @csrf
                            <input type="hidden" id="supporting_document_resolution_id" name="resolution_id" value="">                                                      
                            <input type="file" name="myfile"
                                onchange="this.form.submit()"                                   
                                name="files[]"                                                                   
                                >
                                                 
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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const messages = @json(session('messages'));

        const showMessages = async () => {
            for (const message of messages) {
                await Swal.fire({
                    title: message['type'].toUpperCase(),
                    text: message['text'],
                    icon: message['type'],
                    confirmButtonText: 'OK'
                });
            }
        };

        showMessages();
    };
    

    tmInitDataTable('#zero-config', {
        pageLength: 10,
        searchPlaceholder: 'Search resolutions...',
        order: [[5, 'desc']],
        columnDefs: [{ orderable: false, targets: [15] }]
    });

    // Row click -> floating action menu
    (function () {
        var menu = document.getElementById('tm-row-menu');
        if (!menu) return;
        var curId = null, curTitle = '';
        var editBase    = "{{ url('resolutions/edit') }}";
        var viewBase    = "{{ url('resolutions/view') }}";
        var archiveBase = "{{ url('resolutions/move_to_archive') }}";

        function hideMenu() { menu.style.display = 'none'; }

        $('#zero-config tbody').on('click', 'tr', function (e) {
            // ignore clicks on links / action icons
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
            if (act === 'edit')        window.location.href = editBase + '/' + curId;
            else if (act === 'view')   window.location.href = viewBase + '/' + curId;
            else if (act === 'adddoc') { view_files(curId, curTitle); bootstrap.Modal.getOrCreateInstance(document.getElementById('add-supporting-documents')).show(); }
            else if (act === 'archive') confirm_archive(archiveBase + '/' + curId);
        });

        document.addEventListener('click', function (e) { if (!menu.contains(e.target)) hideMenu(); });
        document.addEventListener('scroll', hideMenu, true);
        window.addEventListener('resize', hideMenu);
    })();
    
    function confirm_archive($url)
    {
        Swal.fire({
        title: 'Are you sure?',
        text: "This action will move the record to the archive list.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, archive it!'
        }).then((result) => {
            if (result.isConfirmed) {
               
                window.location.href = $url ; 
            }
        });    
    }    

    function view_files(param, paramparticulars)
    {               
        document.getElementById('supporting_document_resolution_id').value = param;
        document.getElementById('h6_add_supporting_document').innerHTML =  paramparticulars;                          
        $.ajax({
                type: 'POST',
                data: {resolution_id: param},
                dataType: "json",
                url:'{{ url("resolutions/get_uploaded_files") }}',
                success: function (data){		
                    items  = data['rows'];
                    const ul = document.getElementById('my_files');
                    ul.innerHTML = '';

                    items.forEach(item => {
                        const li = document.createElement('li');
                        li.classList.add('list-group-item');
                        li.classList.add('ps-1');

                        const a = document.createElement('a');
                        a.href = item.url;
                        a.textContent = item.filename;
                        a.target = '_blank';
                        a.title = "View File";

                        const b = document.createElement('a');
                        b.classList.add('me-2');
                        b.classList.add('text-danger');
                        b.href = "{{ url('resolutions/delete_uploaded_file') }}/" + item.id;
                        b.innerHTML  = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 p-1 br-8 mb-1 delete-note"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>';                       
                        b.title = "Delete File";
                        b.onclick = function (event) {
                            if (!confirm("Are you sure you want to delete this file?")) {
                                event.preventDefault(); 
                            }
                        };

                        li.appendChild(b);
                        li.appendChild(a);
                        
                        ul.appendChild(li);
                    });                                                                                                           
                        
                },
                error: function(XHR, textStatus, errorThrown) 
                {
                    console.log(errorThrown);
                    console.log(XHR.responseText);
                } 
        }
        );
    }
    
</script>
@endsection