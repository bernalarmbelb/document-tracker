@extends("template",['menu' => $menu])

@section("additional_head")
<link href="{{ asset("assets/src/assets/css/light/components/modal.css") }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/filepond/filepond.min.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<style>
    .tm-kv { font-size: 13px; }
    .tm-kv .r { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px solid var(--tm-line); }
    .tm-kv .r:last-child { border-bottom: none; }
    .tm-kv .k { color: var(--tm-muted); white-space: nowrap; }
    .tm-kv .v { text-align: right; font-weight: 600; max-width: 65%; }
    .tm-doclist { list-style: none; margin: 0; padding: 0; }
    .tm-doclist li { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid var(--tm-line); font-size: 13px; }
    .tm-doclist li:last-child { border-bottom: none; }
    .tm-doclist a { color: var(--tm-primary); text-decoration: none; }
    .tm-doclist .tm-doc-del { margin-left: auto; color: var(--tm-danger); flex-shrink: 0; }
</style>
@endsection

@section("content")

<div class="layout-px-spacing">
    <div class="tm-page">

        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">Communication</h1>
                <div class="tm-crumb"><a href="{{ url('communications/incoming_list') }}">Communications</a> / View</div>
            </div>
            <div class="d-flex align-items-center" style="gap:10px; flex-wrap:wrap">
                @if(Auth::user()->hasPermission('Edit Communication'))
                    <button type="button" class="tm-btn tm-btn-success" data-bs-toggle="modal" data-bs-target="#edit-communication">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg> Edit
                    </button>
                @endif
                @if(Auth::user()->hasPermission('Add Communication'))
                    <button type="button" class="tm-btn tm-btn-outline" data-bs-toggle="modal" data-bs-target="#add-supporting-documents">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg> Add Supporting Document
                    </button>
                @endif
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-6">
                <div class="tm-card">
                    @if($info->communication_type=='INCOMING')
                        <h3><span class="tm-badge tm-badge-info">INCOMING</span></h3>
                        <p style="font-weight:600;font-size:14px;margin-bottom:14px">Source: {{ $info->source }}</p>
                        <div class="tm-kv">
                            <div class="r"><span class="k">Date Received</span><span class="v">{{ date("M d, Y", strtotime($info->date_received)) }}</span></div>
                            <div class="r"><span class="k">Received by</span><span class="v">{{ $info->received_by }}</span></div>
                            <div class="r"><span class="k">Particulars</span><span class="v">{{ $info->particulars }}</span></div>
                            <div class="r"><span class="k">Actions Taken</span><span class="v">{{ $info->actions_taken }}</span></div>
                            <div class="r"><span class="k">Created</span><span class="v">{{ date("M d, Y h:iA", strtotime($info->created_at)) }}</span></div>
                        </div>
                    @else
                        <h3><span class="tm-badge tm-badge-sec">OUTGOING</span></h3>
                        <p style="font-weight:600;font-size:14px;margin-bottom:14px">Addressee: {{ $info->addressee }}</p>
                        <div class="tm-kv">
                            <div class="r"><span class="k">Date Released</span><span class="v">{{ date("M d, Y", strtotime($info->date_released)) }}</span></div>
                            <div class="r"><span class="k">Released by</span><span class="v">{{ $info->released_by }}</span></div>
                            <div class="r"><span class="k">Particulars</span><span class="v">{{ $info->particulars }}</span></div>
                            <div class="r"><span class="k">Created</span><span class="v">{{ date("M d, Y h:iA", strtotime($info->created_at)) }}</span></div>
                        </div>
                    @endif
                </div>

                <div class="tm-card" style="margin-top:16px">
                    <h3>Supporting Documents</h3>
                    <ul class="tm-doclist" id="my_files">
                        @foreach($all_documents as $item)
                            <li data-id="{{ $item->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--tm-muted);flex-shrink:0"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                <a href="{{ url('uploads_communications/'.$item->filename) }}" target="_blank">{{ substr($item->filename,11) }}</a>
                                @if(Auth::user()->hasPermission('Add Communication') || Auth::user()->hasPermission('Edit Communication'))
                                    <a href="javascript:void(0);" class="tm-doc-del" title="Delete File" onclick="delete_supporting_document({{ $item->id }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

@if($info->communication_type=='INCOMING')
    <div class="modal fade" id="edit-communication" tabindex="-1" role="dialog" aria-labelledby="editCommunicationLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCommunicationLabel">Edit Communication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" action="{{ url('/communications/save_changes_incoming/') }}" method="post" autocomplete="off">
                        @csrf
                        <input type="hidden" name="communication_id" value="{{ $info->id }}"/>
                        <div class="col-12"><label class="tm-label">Date Received <span class="text-danger">*</span></label><input type="date" class="tm-input" value="{{ date('Y-m-d', strtotime($info->date_received)) }}" required name="date_received"></div>
                        <div class="col-12"><label class="tm-label">Source <span class="text-danger">*</span></label><input type="text" class="tm-input" value="{{ $info->source }}" required name="source" ></div>
                        <div class="col-12"><label class="tm-label">Particulars <span class="text-danger">*</span></label><textarea class="tm-input" rows="4" required name="particulars">{{ $info->particulars }}</textarea></div>
                        <div class="col-12"><label class="tm-label">Actions Taken <span class="text-danger">*</span></label><textarea class="tm-input" rows="3" required name="actions_taken">{{ $info->actions_taken }}</textarea></div>
                        <div class="col-12"><label class="tm-label">Person who received <span class="text-danger">*</span></label><input type="text" class="tm-input" value="{{ $info->received_by }}" required name="received_by" ></div>
                        <div class="d-grid gap-2 col-12 mx-auto"><button type="submit" name="btnsave" value="1" class="tm-btn tm-btn-primary tm-btn-block">Save</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="modal fade" id="edit-communication" tabindex="-1" role="dialog" aria-labelledby="editCommunicationLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCommunicationLabel">Edit Communication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" action="{{ url('/communications/save_changes_outgoing/') }}" method="post" autocomplete="off">
                        @csrf
                        <input type="hidden" name="communication_id" value="{{ $info->id }}"/>
                        <div class="col-12"><label class="tm-label">Date Released <span class="text-danger">*</span></label><input type="date" class="tm-input" value="{{ date('Y-m-d', strtotime($info->date_released)) }}" required name="date_released"></div>
                        <div class="col-12"><label class="tm-label">Addressee <span class="text-danger">*</span></label><input type="text" class="tm-input" value="{{ $info->addressee }}" required name="addressee" ></div>
                        <div class="col-12"><label class="tm-label">Particulars <span class="text-danger">*</span></label><textarea class="tm-input" rows="4" required name="particulars">{{ $info->particulars }}</textarea></div>
                        <div class="col-12"><label class="tm-label">Person who released <span class="text-danger">*</span></label><input type="text" class="tm-input" value="{{ $info->released_by }}" required name="released_by" ></div>
                        <div class="d-grid gap-2 col-12 mx-auto"><button type="submit" name="btnsave" value="1" class="tm-btn tm-btn-primary tm-btn-block">Save</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="modal fade" id="add-supporting-documents" tabindex="-1" role="dialog" aria-labelledby="addSupportingDocumentLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSupportingDocumentLabel">Add Supporting Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="widget-content widget-content-area blog-create-section mb-3">
                    <div class="m-3">
                        <input type="file" class="filepond" id="filepond-view" name="myfile" multiple>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/filepond.min.js") }}"></script>

<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const commType = "{{ $info->communication_type }}";
    const communicationId = {{ $info->id }};
    const uploadUrl = commType === 'INCOMING'
        ? '{{ url("/communications/upload_supporting_documents_incoming") }}'
        : '{{ url("/communications/upload_supporting_documents_outgoing") }}';
    const filesUrl = commType === 'INCOMING'
        ? '{{ url("communications/get_incoming_files") }}'
        : '{{ url("communications/get_outgoing_files") }}';
    const deleteBaseUrl = commType === 'INCOMING'
        ? '{{ url("communications/delete_uploaded_file_incoming") }}'
        : '{{ url("communications/delete_uploaded_file_outgoing") }}';

    function refreshSupportingDocuments() {
        $.ajax({
            type: 'POST', data: { communication_id: communicationId }, dataType: "json",
            url: filesUrl,
            success: function (data) {
                const items = data['rows'];
                const ul = document.getElementById('my_files');
                ul.innerHTML = '';
                items.forEach(item => {
                    const li = document.createElement('li');
                    li.setAttribute('data-id', item.id);

                    const icon = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                    icon.setAttribute('width', '16'); icon.setAttribute('height', '16'); icon.setAttribute('viewBox', '0 0 24 24');
                    icon.setAttribute('fill', 'none'); icon.setAttribute('stroke', 'currentColor'); icon.setAttribute('stroke-width', '2');
                    icon.setAttribute('stroke-linecap', 'round'); icon.setAttribute('stroke-linejoin', 'round');
                    icon.style.color = 'var(--tm-muted)'; icon.style.flexShrink = '0';
                    icon.innerHTML = '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>';

                    const a = document.createElement('a');
                    a.href = "{{ url('uploads_communications') }}/" + item.filename;
                    a.textContent = item.filename.slice(11);
                    a.target = '_blank';

                    li.appendChild(icon);
                    li.appendChild(a);

                    @if(Auth::user()->hasPermission('Add Communication') || Auth::user()->hasPermission('Edit Communication'))
                        const del = document.createElement('a');
                        del.href = 'javascript:void(0);';
                        del.classList.add('tm-doc-del');
                        del.title = 'Delete File';
                        del.setAttribute('onclick', 'delete_supporting_document(' + item.id + ')');
                        del.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>';
                        li.appendChild(del);
                    @endif

                    ul.appendChild(li);
                });
            },
            error: function (XHR, textStatus, errorThrown) { console.log(errorThrown); console.log(XHR.responseText); }
        });
    }

    function delete_supporting_document(fileId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the uploaded document.",
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = deleteBaseUrl + '/' + fileId;
            }
        });
    }

    let filepondView = null;

    function initFilePondView() {
        const input = document.getElementById('filepond-view');
        if (filepondView) {
            filepondView.destroy();
        }
        filepondView = FilePond.create(input, {
            allowMultiple: true,
            storeAsFile: true,
            server: {
                process: uploadUrl + '?communication_id=' + communicationId,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            },
            onprocessfile: function (error, file) {
                if (!error) {
                    refreshSupportingDocuments();
                }
            }
        });
    }

    document.getElementById('add-supporting-documents').addEventListener('show.bs.modal', function () {
        initFilePondView();
    });
</script>
@endsection
