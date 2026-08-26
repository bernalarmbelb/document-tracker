@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/filepond/filepond.min.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImagePreview.min.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/editors/quill/quill.snow.css") }}">

<style>
    .hidden { display: none; }
    .ql-toolbar.ql-snow, .ql-container.ql-snow { border-color: rgba(51,51,51,.15) !important; }
    .ql-toolbar.ql-snow { border-radius: 8px 8px 0 0; }
    .ql-container.ql-snow { border-radius: 0 0 8px 8px; font-family: var(--tm-font); }
    .att-opt { display:inline-block; margin-left:10px; font-size:12px; color:var(--tm-muted); cursor:pointer; }
    .att-opt input { vertical-align:middle; }
</style>
@endsection

@section("content")

<div class="layout-px-spacing">
    <div class="tm-page">

        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">{{ $edit ? 'Edit' : 'New' }} Minutes</h1>
                <div class="tm-crumb"><a href="{{ url('minutes/list') }}">Minutes</a> / {{ $edit ? 'Edit' : 'Add New' }}</div>
            </div>
        </div>

        <div class="row g-3">
            {{-- Left: form --}}
            <div class="col-lg-6">
                <div class="tm-card">
                    <form class="row g-3" action="{{ $edit ? url('/minutes/save_changes/') : url('/minutes/save_add/') }}" method="post" autocomplete="off">
                        @csrf
                        <input type="hidden" name="minute_id" value="{{ $info->id ?? '' }}"/>
                        <input type="hidden" name="editor_content" id="quill-content">
                        <input type="hidden" name="category" value="{{ $edit ? $info->category : $category }}">

                        <div class="col-md-6">
                            <label class="tm-label">Date &amp; Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="tm-input" value={{ $edit ? date("Y-m-d\TH:i", strtotime($info->date_created)) : date("Y-m-d\TH:i") }} required name="date_created">
                        </div>
                        <div class="col-md-6">
                            <label class="tm-label">Minute Number <span class="text-danger">*</span></label>
                            <input type="text" class="tm-input" style="text-align:center;font-weight:700;color:var(--tm-primary)" value="{{ $edit ? $info->series_number : $new_series_number }}" required name="series_number">
                        </div>
                        <div class="col-12">
                            <label class="tm-label">Presiding Officer <span class="text-danger">*</span></label>
                            <select name="presiding_officer" class="tm-select" required>
                                <option value="">Choose...</option>
                                @foreach($all_signatories as $item)
                                    <option {{ $edit && $item->signatory_name==$info->presiding_officer ? 'selected' : '' }}>{{ $item->signatory_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="tm-label">Agenda <span class="text-danger">*</span></label>
                            <input type="text" class="tm-input" value="{{ $edit ? $info->agenda_1 : '' }}" name="agenda_1">
                            <input type="text" class="tm-input mt-2 field {{ $edit && $info->agenda_2!='' ?  : 'hidden' }}" value="{{ $edit ? $info->agenda_2 : '' }}" placeholder="2nd Agenda" name="agenda_2">
                            <input type="text" class="tm-input mt-2 field {{ $edit && $info->agenda_3!='' ?  : 'hidden' }}" value="{{ $edit ? $info->agenda_3 : '' }}" placeholder="3rd Agenda" name="agenda_3">
                            <input type="text" class="tm-input mt-2 field {{ $edit && $info->agenda_4!='' ?  : 'hidden' }}" value="{{ $edit ? $info->agenda_4 : '' }}" placeholder="4th Agenda" name="agenda_4">
                            <input type="text" class="tm-input mt-2 field {{ $edit && $info->agenda_5!='' ?  : 'hidden' }}" value="{{ $edit ? $info->agenda_5 : '' }}" placeholder="5th Agenda" name="agenda_5">
                            <button type="button" class="tm-btn tm-btn-outline tm-btn-sm mt-2" id="btnAddAgenda">＋ Add More</button>
                        </div>
                        <div class="col-12">
                            <label class="tm-label">Venue <span class="text-danger">*</span></label>
                            <input type="text" class="tm-input" value="{{ $edit ? $info->venue : '' }}" required name="venue">
                        </div>
                        <div class="col-12">
                            <label class="tm-label">Short Description <span class="text-danger">*</span></label>
                            <input type="text" class="tm-input" value="{{ $edit ? $info->short_description : '' }}" required name="short_description" >
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:8px">
                                <label class="tm-label mb-0">Attendance <small class="tm-muted">(members roster)</small></label>
                                <span class="tm-badge tm-badge-info" id="presentCount">Present: 0 of {{ count($all_members) }}</span>
                            </div>
                            @if(count($all_members) === 0)
                                <p class="tm-muted" style="margin:8px 0">No members yet. Add office / LGU staff under <b>System → Members</b> first.</p>
                            @else
                                <div class="d-flex gap-2 my-2">
                                    <button type="button" class="tm-btn tm-btn-outline tm-btn-sm" id="btnAllPresent">Mark all present</button>
                                    <button type="button" class="tm-btn tm-btn-outline tm-btn-sm" id="btnClearAll">Clear all</button>
                                </div>
                                <input type="text" id="rosterSearch" class="tm-input mb-2" placeholder="Search by name or position..." autocomplete="off">
                                <div class="tm-table-wrap">
                                    <table class="tm-table" id="rosterTable" style="width:100%">
                                        <tbody>
                                            @foreach($all_members as $m)
                                                @php $sel = $attendance_map[$m->id] ?? ''; @endphp
                                                <tr>
                                                    <td>
                                                        <div style="font-weight:600">{{ $m->name }}</div>
                                                        <div class="tm-muted" style="font-size:12px">{{ $m->position }}</div>
                                                    </td>
                                                    <td style="text-align:right;white-space:nowrap">
                                                        @foreach(['P'=>'Present','A'=>'Absent','E'=>'Excused','L'=>'Late'] as $code => $lbl)
                                                            <label class="att-opt" title="{{ $lbl }}">
                                                                <input type="radio" name="att[{{ $m->id }}]" value="{{ $code }}" {{ $sel === $code ? 'checked' : '' }}> {{ $code }}
                                                            </label>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr id="rosterNoMatch" style="display:none"><td colspan="2" class="tm-muted" style="padding:8px">No members match your search.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                        <div class="col-12">
                            <label class="tm-label">Guests / Others <small class="tm-muted">(not on the roster — one per line)</small></label>
                            <textarea class="tm-textarea" name="attendance" rows="4" placeholder="e.g. Hon. Roberto A. Mendoza — Presiding Officer">{{ $edit ? $info->attendance : '' }}</textarea>
                        </div>

                        <div class="col-12 d-flex gap-3">
                            <button type="submit" name="btnsaveasdraft" value="1" class="tm-btn tm-btn-outline tm-btn-block">Save</button>
                            <button type="submit" name="btnsave" value="1" class="tm-btn tm-btn-primary tm-btn-block">Save and Return</button>
                        </div>
                    </form>

                    @if($edit)
                        <div class="blog-create-section" style="margin-top:20px;border-top:1px solid var(--tm-line);padding-top:16px">
                            <form action="{{ url('/minutes/upload_supporting_documents/') }}" method="post" autocomplete="off" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="supporting_document_minute_id" value="{{ $info->id ?? '' }}">
                                <label class="tm-label">Supporting Documents <small class="tm-muted">Uploads save automatically.</small></label>
                                <div class="multiple-file-upload">
                                    <input type="file" class="filepond file-upload-multiple" name="files[]" id="filepond" multiple data-allow-reorder="true" data-max-file-size="3MB" data-max-files="5">
                                </div>
                            </form>
                            <ul class="list-group mt-2">
                                @foreach($all_documents as $item)
                                    <li class="list-group-item">
                                        <a href="{{ url('minutes/delete_uploaded_file_view/'.$item->id) }}" onclick="return confirm('Are you sure you want to delete this file?')" title="Delete File" class="me-2 text-danger"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></a>
                                        <a href="{{ url('uploads_minutes/'.$item->filename) }}" target="_blank">{{ substr($item->filename,11) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right: editor --}}
            <div class="col-lg-6">
                <div class="tm-card">
                    <h3>Document Content</h3>
                    <div id="editor-container" style="height: 500px;">
                        <p class="ql-align-center">Republic of the Philippines</p>
                        <p class="ql-align-center">Province of Sorsogon</p>
                        <p class="ql-align-center"><strong>MUNICIPALITY OF PRIETO DIAZ</strong></p>
                        <p class="ql-align-center">LGU TIN:000794065</p>
                        <p class="ql-align-center">Official Email Address: sbprietodiaz@gmail.com</p>
                        <br/><br/><br/>
                        <p><strong>BARANGAY SAN ISIDRO</strong></p>
                        <p>January 01, 2025</p>
                        <p>Barangay San Isidro, Function Hall</p>
                        <p>03:00PM-04:00PM</p>
                        <br/>
                        <p><strong>I. HEADING 1</strong></p>
                        <p>Details here...</p>
                        <br/>
                        <p><strong>II. HEADING 2</strong></p>
                        <p>Details here...</p>
                    </div>
                </div>

                @if($edit)
                    <div class="d-inline-flex align-items-center" style="margin-top:16px;gap:10px">
                        <a href="{{ url('minutes/view/'.$info->id) }}" target="_blank" class="tm-btn tm-btn-dark">View in New Tab</a>
                        <span class="tm-muted">Save changes before viewing.</span>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/editors/quill/quill.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/filepond.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginFileValidateType.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImageExifOrientation.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImagePreview.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImageCrop.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImageResize.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImageTransform.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/filepondPluginFileValidateSize.min.js") }}"></script>
<script src="{{ asset("assets/src/assets/js/apps/blog-create.js") }}"></script>

<script src="{{ asset("assets/src/assets/js/scrollspyNav.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/editors/quill/quill.js") }}"></script>
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

    document.getElementById("btnAddAgenda").addEventListener("click", function () {
        const hiddenField = document.querySelector(".field.hidden");

        if (hiddenField) {
            hiddenField.classList.remove("hidden");
        }

        // Optional: hide button if no more hidden fields
        if (!document.querySelector(".field.hidden")) {
            this.style.display = "none";
        }
    });

    const toolbarOptions = [
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
        ['blockquote', 'code-block'],
        ['link', 'image'],

        [{ 'header': 1 }, { 'header': 2 }],               // custom button values
        [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
        [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
        [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent

        [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown

        [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
        [{ 'font': [] }],
        [{ 'align': [] }],

        ['clean']                                         // remove formatting button
        ];

        var quill = new Quill('#editor-container', {
        modules: {
            toolbar: toolbarOptions
        },
            placeholder: 'Compose an article...',
            theme: 'snow'  // or 'bubble'
        });

        @if($edit)
            quill.root.innerHTML = `{!! $info->editor_content !!}`;
        @endif

        document.querySelector("form").onsubmit = function() {
            document.querySelector("#quill-content").value = quill.root.innerHTML;
        };

        
    // Register FilePond on the input field
    @if($edit)
        FilePond.create(document.getElementById('filepond'), {
            allowMultiple: true,
            storeAsFile: true,
            server: {
                process: '{{ url("/minutes/upload_supporting_documents?minute_id=$info->id") }}', 
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            
            }
        });
    @endif

    (function () {
        const total = {{ count($all_members) }};
        const pill = document.getElementById('presentCount');
        function recount() {
            let p = 0;
            document.querySelectorAll('input[type=radio][name^="att["]:checked').forEach(r => { if (r.value === 'P') p++; });
            if (pill) pill.textContent = 'Present: ' + p + ' of ' + total;
        }
        document.querySelectorAll('input[type=radio][name^="att["]').forEach(r => r.addEventListener('change', recount));
        const allBtn = document.getElementById('btnAllPresent');
        const clrBtn = document.getElementById('btnClearAll');
        if (allBtn) allBtn.addEventListener('click', () => {
            document.querySelectorAll('input[type=radio][name^="att["][value="P"]').forEach(r => r.checked = true);
            recount();
        });
        if (clrBtn) clrBtn.addEventListener('click', () => {
            document.querySelectorAll('input[type=radio][name^="att["]:checked').forEach(r => r.checked = false);
            recount();
        });
        recount();
    })();

    // Live search: filter roster rows by member name or position
    (function () {
        const search = document.getElementById('rosterSearch');
        if (!search) return;
        const noMatch = document.getElementById('rosterNoMatch');
        const dataRows = Array.from(document.querySelectorAll('#rosterTable tbody tr'))
            .filter(r => r.id !== 'rosterNoMatch');

        search.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            let visible = 0;
            dataRows.forEach(r => {
                const show = q === '' || r.children[0].textContent.toLowerCase().includes(q);
                r.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            noMatch.style.display = (dataRows.length && visible === 0) ? '' : 'none';
        });
    })();
</script>
@endsection