@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/editors/quill/quill.snow.css") }}">
<link href="{{ asset("assets/src/assets/css/light/components/modal.css") }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/filepond/filepond.min.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<style>
    .tm-doc .ql-editor { padding: 0; }
    .tm-doc .ql-editor p { margin: 0; }
    .tm-doc .ql-align-center img { width: 100%; }
    .tm-kv { font-size: 13px; }
    .tm-kv .r { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-bottom: 1px solid var(--tm-line); }
    .tm-kv .r:last-child { border-bottom: none; }
    .tm-kv .k { color: var(--tm-muted); white-space: nowrap; }
    .tm-kv .v { text-align: right; font-weight: 600; }
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
                <h1 class="tm-title">Resolution {{ $info->series_number }}</h1>
                <div class="tm-crumb"><a href="{{ url('resolutions/list') }}">Resolutions</a> / View</div>
            </div>
            <div class="d-flex align-items-center" style="gap:10px;flex-wrap:wrap">
                @if(Auth::user()->hasPermission('Add Resolution'))
                    <button type="button" class="tm-btn tm-btn-outline" data-bs-toggle="modal" data-bs-target="#add-supporting-documents">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg> Add Supporting Document
                    </button>
                @endif
                @if(Auth::user()->hasPermission('Edit Resolution'))
                    <a href="{{ url('resolutions/edit/'.$info->id) }}" class="tm-btn tm-btn-dark">✎ Edit</a>
                @endif
                <a href="{{ url('resolutions/generate_pdf/'.$info->id) }}" target="_blank" class="tm-btn tm-btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    Export / Print
                </a>
            </div>
        </div>

        <div class="row g-3">
            {{-- Document --}}
            <div class="col-xl-8">
                <div class="tm-card tm-doc">
                    <div class="content-section ql-editor">
                        {!! $info->editor_content !!}
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-xl-4">
                <div class="tm-stack">
                    <div class="tm-card">
                        <h3>{{ $info->series_number }}</h3>
                        <p style="font-weight:600;font-size:14px;margin-bottom:14px">{{ $info->title }}</p>
                        <div class="tm-kv">
                            <div class="r"><span class="k">Author</span><span class="v">{{ $info->author_name }}</span></div>
                            <div class="r"><span class="k">Created</span><span class="v">{{ date("M d, Y h:iA", strtotime($info->date_created)) }}</span></div>
                            <div class="r"><span class="k">Approved</span><span class="v">{{ date("M d, Y", strtotime($info->approved_date)) }}</span></div>
                            <div class="r"><span class="k">Attested by</span><span class="v">{{ $info->attested_by }}</span></div>
                            <div class="r"><span class="k">Recorded by</span><span class="v">{{ $info->recorded_by }}</span></div>
                            <div class="r"><span class="k">Approved by</span><span class="v">{{ $info->approved_by }}</span></div>
                            <div class="r"><span class="k">Book Ref No.</span><span class="v">{{ $info->book_ref_no }}</span></div>
                            <div class="r"><span class="k">Page Ref No.</span><span class="v">{{ $info->page_ref_no }}</span></div>
                            <div class="r"><span class="k">Total Pages</span><span class="v">{{ $info->total_pages }}</span></div>
                            <div class="r"><span class="k">Minute Ref ID</span><span class="v">{{ $info->minute_ref_no }}</span></div>
                        </div>
                    </div>

                    <div class="tm-card">
                        <h3>Supporting Documents</h3>
                        <ul class="tm-doclist" id="my_files">
                            @foreach($all_documents as $item)
                                <li data-id="{{ $item->id }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--tm-muted);flex-shrink:0"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                    <a href="{{ url('uploads_resolutions/'.$item->filename) }}" target="_blank">{{ substr($item->filename,11) }}</a>
                                    @if(Auth::user()->hasPermission('Edit Resolution'))
                                        <a href="javascript:void(0);" class="tm-doc-del" title="Delete File" onclick="delete_supporting_document({{ $item->id }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="tm-card" style="text-align:center">
                        <h3 style="justify-content:center">QR Code</h3>
                        <div class="custom-qr-code" style="display:inline-block">
                            {!! $qr_code !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@if(Auth::user()->hasPermission('Add Resolution'))
    <div class="modal fade" id="add-supporting-documents" tabindex="-1" role="dialog" aria-labelledby="addSupportingDocumentLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSupportingDocumentLabel">Add Supporting Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="file" class="filepond" id="filepond-view" name="filepond" multiple>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@if(Auth::user()->hasPermission('Add Resolution'))
@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/filepond.min.js") }}"></script>

<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const resolutionId = {{ $info->id }};
    const uploadUrl = '{{ url("/resolutions/upload_supporting_documents") }}';
    const filesUrl = '{{ url("resolutions/get_uploaded_files") }}';
    const deleteBaseUrl = '{{ url("resolutions/delete_uploaded_file_view") }}';

    function refreshSupportingDocuments() {
        $.ajax({
            type: 'POST', data: { resolution_id: resolutionId }, dataType: "json",
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
                    a.href = "{{ url('uploads_resolutions') }}/" + item.filename;
                    a.textContent = item.filename.slice(11);
                    a.target = '_blank';

                    li.appendChild(icon);
                    li.appendChild(a);

                    @if(Auth::user()->hasPermission('Edit Resolution'))
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
            credits: false,
            labelIdle: `
                <div class="tm-upload-drop">
                    <div class="tm-upload-drop-ico">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="12" y1="11" x2="12" y2="17"></line>
                            <line x1="9" y1="14" x2="15" y2="14"></line>
                        </svg>
                    </div>
                    <p class="tm-upload-drop-desc">Add PDF, Word, Excel, or image files for this record.<br>Files appear in the list below once uploaded.</p>
                    <span class="filepond--label-action tm-upload-drop-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Add files
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </span>
                </div>
            `,
            server: {
                process: uploadUrl + '?resolution_id=' + resolutionId,
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
@endif
