@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/filepond/filepond.min.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImagePreview.min.css") }}">
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/tagify/tagify.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/editors/summernote/summernote-lite.min.css") }}">
{{-- quill.snow.css kept so legacy (Quill-authored) alignment/indent classes still render inside the editor --}}
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/editors/quill/quill.snow.css") }}">

<style>
    .note-editor.note-frame { border-radius: 8px; border-color: rgba(51,51,51,.15); }
    .note-editor .note-editing-area .note-editable { font-family: var(--tm-font); }
</style>
@endsection

@section("content")

<div class="layout-px-spacing">
    <div class="tm-page">

        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">{{ $edit ? 'Edit' : 'New' }} Resolution</h1>
                <div class="tm-crumb"><a href="{{ url('resolutions/list') }}">Resolutions</a> / {{ $edit ? 'Edit' : 'Add New' }}</div>
            </div>
        </div>

        <div class="row g-3">
            {{-- Left: form --}}
            <div class="col-lg-6">
                <div class="tm-card">
                    <form class="row g-3" id="mainForm" action="{{ $edit ? url('/resolutions/save_changes/') : url('/resolutions/save_add/') }}" method="post" autocomplete="off">
                        @csrf
                        <input type="hidden" name="resolution_id" value="{{ $info->id ?? '' }}"/>
                        <input type="hidden" name="editor_content" id="quill-content">

                        <div class="col-md-6">
                            <label class="tm-label">Date &amp; Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="tm-input" value={{ $edit ? date("Y-m-d\TH:i", strtotime($info->date_created)) : date("Y-m-d\TH:i") }} required name="date_created">
                        </div>
                        <div class="col-md-6">
                            <label class="tm-label">Series Number <span class="text-danger">*</span></label>
                            <input type="text" class="tm-input" style="text-align:center;font-weight:700;color:var(--tm-primary)" value="{{ $edit ? $info->series_number : $new_resolution_number }}" required name="series_number">
                        </div>
                        <div class="col-12">
                            <label class="tm-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="tm-input" value="{{ $edit ? $info->title : '' }}" required name="title" >
                        </div>
                        <div class="col-6">
                            <label class="tm-label">Author <span class="text-danger">*</span></label>
                            <select name="author_name" class="tm-select" required>
                                <option value="">Choose...</option>
                                @foreach($all_signatories as $item)
                                    <option {{ $edit && $item->signatory_name==$info->author_name ? 'selected' : '' }}>{{ $item->signatory_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="tm-label">Status/Classification <span class="text-danger">*</span></label>
                            <select name="resolution_status" class="tm-select" required onchange="toggleApprovedDate(this.value)">
                                <option value="">Choose...</option>
                                @foreach($all_statuses as $item)
                                    <option {{ $edit && $item->status_code==$info->resolution_status ? 'selected' : '' }} value='{{ $item->status_code }}'>{{ $item->status_resolution }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="tm-label">Keywords/Tags <span class="text-danger">*</span></label>
                            <input type="text" class="tm-input" value="{{ $edit ? $info->keywords_tags : '' }}" required name="keywords_tags" >
                        </div>
                        <div class="col-md-6">
                            <label class="tm-label">Sponsor <span class="text-danger">*</span></label>
                            <input type="text" class="tm-input" value="{{ $edit ? $info->sponsor : '' }}" required name="sponsor">
                        </div>
                        <div class="col-md-6">
                            <label class="tm-label">Attested By <span class="text-danger">*</span></label>
                            <select name="attested_by" class="tm-select" required>
                                <option value="">Choose...</option>
                                @foreach($all_signatories as $item)
                                    <option {{ $edit && $item->signatory_name==$info->attested_by ? 'selected' : '' }}>{{ $item->signatory_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="tm-label">Recorded By <span class="text-danger">*</span></label>
                            <select name="recorded_by" class="tm-select" required>
                                <option value="">Choose...</option>
                                @foreach($all_signatories as $item)
                                    <option {{ $edit && $item->signatory_name==$info->recorded_by ? 'selected' : '' }}>{{ $item->signatory_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" id="div_approved_by">
                            <label class="tm-label">Approved By <span class="text-danger">*</span></label>
                            <select name="approved_by" class="tm-select" required id="approved_by">
                                <option value="">Choose...</option>
                                @foreach($all_signatories as $item)
                                    <option {{ $edit && $item->signatory_name==$info->approved_by ? 'selected' : '' }}>{{ $item->signatory_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" id="div_approved_date">
                            <label class="tm-label">Approved Date <span class="text-danger">*</span></label>
                            <input type="date" class="tm-input" value={{ $edit ? date("Y-m-d", strtotime($info->approved_date)) : date("Y-m-d") }} required name="approved_date" id="approved_date">
                        </div>
                        <div class="col-md-6">
                            <label class="tm-label">Reference Book No. <span class="text-danger">*</span></label>
                            <input type="text" class="tm-input" value="{{ $edit ? $info->book_ref_no : '' }}" name="book_ref_no" required>
                        </div>
                        <div class="col-md-6">
                            <label class="tm-label">Reference Page No. <span class="text-danger">*</span></label>
                            <input type="text" class="tm-input" value="{{ $edit ? $info->page_ref_no : '' }}" name="page_ref_no" required>
                        </div>
                        <div class="col-md-6">
                            <label class="tm-label">Total Pages <span class="text-danger">*</span></label>
                            <input type="text" class="tm-input" value="{{ $edit ? $info->total_pages : '' }}" name="total_pages" required>
                        </div>
                        <div class="col-md-6">
                            <label class="tm-label">Minute Reference ID <span class="text-danger">*</span></label>
                            <input type="text" class="tm-input" value="{{ $edit ? $info->minute_ref_no : '' }}" name="minute_ref_no" required>
                        </div>

                        <div class="col-12 d-flex gap-3">
                            @if(!$edit)
                                <button type="button" id="btn-add-docs" class="tm-btn tm-btn-outline tm-btn-block" onclick="addSupportingDocsInline()">Add Supporting Documents</button>
                            @endif
                            <button type="submit" name="btnsaveasdraft" value="1" class="tm-btn tm-btn-outline tm-btn-block">Save</button>
                            <button type="submit" name="btnsave" value="1" class="tm-btn tm-btn-primary tm-btn-block">Save and Return</button>
                        </div>
                    </form>

                    <div class="blog-create-section" id="supporting-documents-panel" style="margin-top:20px;border-top:1px solid var(--tm-line);padding-top:16px;{{ $edit ? '' : 'display:none' }}">
                        <form action="{{ url('/resolutions/upload_supporting_documents/') }}" method="post" autocomplete="off" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="supporting_document_resolution_id" value="{{ $info->id ?? '' }}">
                            <label class="tm-label">Supporting Documents <small class="tm-muted">Uploads save automatically.</small></label>
                            <div class="multiple-file-upload tm-upload-inline">
                                <input type="file" class="filepond file-upload-multiple" name="filepond" id="filepond" multiple data-allow-reorder="true" data-max-file-size="100MB" data-max-files="5">
                            </div>
                        </form>
                        <ul class="tm-doclist mt-2" id="supporting-documents-list">
                            @foreach($all_documents ?? [] as $item)
                                <li>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--tm-muted);flex-shrink:0"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                    <a href="{{ url('uploads_resolutions/'.$item->filename) }}" target="_blank">{{ substr($item->filename,11) }}</a>
                                    <a href="{{ url('resolutions/delete_uploaded_file_view/'.$item->id) }}" onclick="return confirm('Are you sure you want to delete this file?')" title="Delete File" class="tm-doc-del">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Right: editor --}}
            <div class="col-lg-6">
                <div class="tm-card">
                    <h3>Document Content</h3>
                    <div id="editor-container">
                        @if($edit)
                            {!! $info->editor_content !!}
                        @else
                        <p style="text-align:center;"><img src="https://cdn.jsdelivr.net/gh/passam22/doc_tracker_assets/letter_head_resolution.PNG" alt="Left Logo" style="width:100%;height:auto;"></p>
                        <p><br></p>
                        <p><strong>BARANGAY SAN ISIDRO</strong></p>
                        <p>January 01, 2025</p>
                        <p>Barangay San Isidro, Function Hall</p>
                        <p>03:00PM-04:00PM</p>
                        <p><br></p>
                        <p><strong>I. HEADING 1</strong></p>
                        <p>Details here...</p>
                        <p><br></p>
                        <p><strong>II. HEADING 2</strong></p>
                        <p>Details here...</p>
                        @endif
                    </div>
                    <p class="tm-muted" style="margin-top:12px;font-size:12px">
                        Short Codes: <code>[recorded_by] [attested_by] [approved_by]</code>
                    </p>
                </div>

                @if($edit)
                    <div class="d-inline-flex align-items-center" style="margin-top:16px;gap:10px">
                        <a href="{{ url('resolutions/view/'.$info->id) }}" target="_blank" class="tm-btn tm-btn-dark">View in New Tab</a>
                        <span class="tm-muted">Save changes before viewing.</span>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/editors/summernote/summernote-lite.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/filepond.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginFileValidateType.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImageExifOrientation.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImagePreview.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImageCrop.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImageResize.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImageTransform.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/filepondPluginFileValidateSize.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/tagify/tagify.min.js") }}"></script>

<script src="{{ asset("assets/src/assets/js/scrollspyNav.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>
<script src="{{ asset("js/app.js") }}"></script>
<script>
    window.onload = function() {    
        $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
        });

        const messages = @json(session('messages') ?? []);

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

        $('#editor-container').summernote({
            height: 500,
            fontNames: ['Manrope', 'Arial', 'Times New Roman', 'Georgia', 'Courier New', 'Verdana', 'Tahoma'],
            fontNamesIgnoreCheck: ['Manrope'],
            fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '24', '36'],
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });

        document.querySelector("#mainForm").onsubmit = function() {
            document.querySelector("#quill-content").value = $('#editor-container').summernote('code');
        };

    // Register FilePond plugins here (blog-create.js used to do this, but it was a
    // demo script referencing elements that don't exist on this page).
    if (window.FilePond) {
        FilePond.registerPlugin(
            FilePondPluginImagePreview,
            FilePondPluginImageExifOrientation,
            FilePondPluginFileValidateSize
        );
    }

    // Register FilePond on the input field
    function initFilePond(resId) {
        FilePond.create(document.getElementById('filepond'), {
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
                    <p class="tm-upload-drop-desc">Add PDF, Word, Excel, or image files for this record.</p>
                    <span class="filepond--label-action tm-upload-drop-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Add files
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </span>
                </div>
            `,
            server: {
                process: '{{ url("/resolutions/upload_supporting_documents") }}?resolution_id=' + resId,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            },
            onprocessfile: function (error, file) {
                if (!error) refreshSupportingDocuments(resId);
            }
        });
    }

    // Re-fetch the supporting-document list so newly uploaded files show up
    // without requiring a page reload.
    function refreshSupportingDocuments(resId) {
        $.ajax({
            type: 'POST',
            data: { resolution_id: resId },
            dataType: 'json',
            url: '{{ url("resolutions/get_uploaded_files") }}',
            success: function (data) {
                const ul = document.getElementById('supporting-documents-list');
                ul.innerHTML = '';
                (data['rows'] || []).forEach(item => {
                    const li = document.createElement('li');

                    const icon = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                    icon.setAttribute('width', '16'); icon.setAttribute('height', '16'); icon.setAttribute('viewBox', '0 0 24 24');
                    icon.setAttribute('fill', 'none'); icon.setAttribute('stroke', 'currentColor'); icon.setAttribute('stroke-width', '2');
                    icon.setAttribute('stroke-linecap', 'round'); icon.setAttribute('stroke-linejoin', 'round');
                    icon.style.color = 'var(--tm-muted)'; icon.style.flexShrink = '0';
                    icon.innerHTML = '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>';

                    const view = document.createElement('a');
                    view.href = "{{ url('uploads_resolutions') }}/" + item.filename;
                    view.target = '_blank';
                    view.textContent = item.filename.substring(11);

                    const del = document.createElement('a');
                    del.href = "{{ url('resolutions/delete_uploaded_file_view') }}/" + item.id;
                    del.title = 'Delete File';
                    del.classList.add('tm-doc-del');
                    del.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>';
                    del.onclick = function (event) {
                        if (!confirm('Are you sure you want to delete this file?')) event.preventDefault();
                    };

                    li.appendChild(icon);
                    li.appendChild(view);
                    li.appendChild(del);
                    ul.appendChild(li);
                });
            }
        });
    }

    @if($edit)
        initFilePond({{ $info->id }});
        toggleApprovedDate('{{ $info->resolution_status }}');
    @endif

    function addSupportingDocsInline() {
        const form = document.getElementById('mainForm');
        if (!form.reportValidity()) return;

        document.querySelector("#quill-content").value = $('#editor-container').summernote('code');

        const btn = document.getElementById('btn-add-docs');
        btn.disabled = true;
        btn.textContent = 'Saving...';

        const formData = new FormData(form);
        formData.append('ajax', '1');

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
        })
        .then(async (r) => {
            const body = await r.json();
            if (!r.ok) throw new Error(body.error || 'Something went wrong.');
            return body;
        })
        .then((body) => {
            document.querySelector('input[name="resolution_id"]').value = body.id;
            form.action = '{{ url('/resolutions/save_changes/') }}';
            btn.style.display = 'none';

            const panel = document.getElementById('supporting-documents-panel');
            panel.style.display = 'block';
            document.querySelector('#supporting-documents-panel input[name="supporting_document_resolution_id"]').value = body.id;

            initFilePond(body.id);
        })
        .catch((err) => {
            btn.disabled = false;
            btn.textContent = 'Add Supporting Documents';
            Swal.fire('Error', err.message, 'error');
        });
    }

    function toggleApprovedDate(myvalue) 
    {
        const approvedDateInput = document.getElementById("approved_date");
        const approvedDateWrapper = document.getElementById("div_approved_date");
        const approvedByInput = document.getElementById("approved_by");
        const approvedByWrapper = document.getElementById("div_approved_by");
        if (myvalue === "APPROVED") {
            approvedDateWrapper.style.display = "block";
            approvedDateInput.required = true;

            approvedByWrapper.style.display = "block";
            approvedByInput.required = true;
        } else {
            approvedDateWrapper.style.display = "none";
            approvedDateInput.required = false;
            approvedDateInput.value = ""; 

            approvedByWrapper.style.display = "none";
            approvedByInput.required = false;
            approvedByInput.value = ""; 
        }
  }

</script>
@endsection