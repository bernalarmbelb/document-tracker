@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/glightbox/glightbox.min.css") }}">
<link href="{{ asset("assets/src/assets/css/light/components/modal.css") }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/filepond/filepond.min.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<style>
    .tm-kv { font-size: 13px; }
    .tm-kv .r { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-bottom: 1px solid var(--tm-line); }
    .tm-kv .r:last-child { border-bottom: none; }
    .tm-kv .k { color: var(--tm-muted); white-space: nowrap; }
    .tm-kv .v { text-align: right; font-weight: 600; }
    .tm-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
    .tm-gallery-item { position: relative; border-radius: 10px; overflow: hidden; border: 1px solid var(--tm-line); }
    .tm-gallery-item a { display: block; }
    .tm-gallery img { width: 100%; height: 130px; object-fit: cover; display: block; }
    .tm-gallery-empty { color: var(--tm-muted); font-style: italic; padding: 24px; text-align: center; }
    .tm-gallery-del {
        position: absolute; top: 6px; right: 6px; z-index: 2;
        width: 26px; height: 26px; border: none; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: rgba(20, 20, 20, 0.55); color: #fff; cursor: pointer;
        transition: background-color .15s ease;
    }
    .tm-gallery-del:hover { background: var(--tm-danger); }
    .tm-gallery-del svg { width: 14px; height: 14px; }
</style>
@endsection

@section("content")

<div class="layout-px-spacing">
    <div class="tm-page">

        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">{{ $info->activity_title }}</h1>
                <div class="tm-crumb"><a href="{{ url('activities/all') }}">Activities</a> / View</div>
            </div>
            <div class="d-flex align-items-center" style="gap:10px;flex-wrap:wrap">
                @if(Auth::user()->hasPermission('Add Activity'))
                    <button type="button" class="tm-btn tm-btn-outline" data-bs-toggle="modal" data-bs-target="#add-supporting-documents">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg> Add Supporting Document
                    </button>
                @endif
                @if(Auth::user()->hasPermission('Edit Activity'))
                    <a href="{{ url('activities/edit/'.$info->id) }}" class="tm-btn tm-btn-dark">✎ Edit</a>
                @endif
            </div>
        </div>

        <div class="row g-3">
            {{-- Gallery --}}
            <div class="col-xl-8">
                <div class="tm-card">
                    <h3>Documentation</h3>
                    <div class="tm-gallery" id="my_files" style="{{ count($all_documents)>0 ? '' : 'display:none' }}">
                        @foreach($all_documents as $item)
                            @php
                                $extension = strtolower(pathinfo($item->filename, PATHINFO_EXTENSION));
                                $videoExtensions = ['mp4', 'mov', 'avi', 'wmv', 'mkv'];
                            @endphp
                            <div class="tm-gallery-item" data-id="{{ $item->id }}">
                                @if(in_array($extension, $videoExtensions))
                                    <a href="{{ url('uploads_sangguniang/'.$item->filename) }}" class="defaultGlightbox glightbox-content">
                                        <img src="{{ url('uploads_sangguniang/video-thumbnail.png') }}" alt="video">
                                    </a>
                                @else
                                    <a href="{{ url('uploads_sangguniang/'.$item->filename) }}" class="defaultGlightbox glightbox-content">
                                        <img src="{{ url('uploads_sangguniang/'.$item->filename) }}" alt="image">
                                    </a>
                                @endif
                                @if(Auth::user()->hasPermission('Edit Activity'))
                                    <button type="button" class="tm-gallery-del" title="Delete File" onclick="delete_supporting_document({{ $item->id }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="tm-gallery-empty" id="my_files_empty" style="{{ count($all_documents)>0 ? 'display:none' : '' }}">No documentation uploaded yet.</div>
                </div>
            </div>

            {{-- Details --}}
            <div class="col-xl-4">
                <div class="tm-card">
                    @php
                        $st = $info->status;
                        $stClass = $st=='COMPLETED' ? 'tm-badge-ok' : ($st=='ONGOING' ? 'tm-badge-info' : ($st=='UPCOMING' ? 'tm-badge-warn' : 'tm-badge'));
                    @endphp
                    <h3>{{ $info->type_of_activity }}</h3>
                    <p style="font-weight:600;font-size:14px;margin-bottom:14px">{{ $info->activity_title }}</p>
                    <div class="tm-kv">
                        <div class="r"><span class="k">Activity Date</span><span class="v">{{ date("M d, Y h:iA", strtotime($info->activity_date)) }}</span></div>
                        <div class="r"><span class="k">Duration</span><span class="v">{{ $info->duration }}</span></div>
                        <div class="r"><span class="k">Location</span><span class="v">{{ $info->location }}</span></div>
                        <div class="r"><span class="k">Organizers</span><span class="v">{{ $info->event_organizers }}</span></div>
                        <div class="r"><span class="k">Sponsors</span><span class="v">{{ $info->sponsors }}</span></div>
                        <div class="r"><span class="k">Participants</span><span class="v">{{ $info->guests_participants }}</span></div>
                        <div class="r"><span class="k">Expected Attendees</span><span class="v">{{ $info->expected_attendees }}</span></div>
                        <div class="r"><span class="k">Actual Attendees</span><span class="v">{{ $info->actual_attendees }}</span></div>
                        <div class="r"><span class="k">Budget/Funding</span><span class="v">{{ $info->budget }}</span></div>
                        <div class="r"><span class="k">Status</span><span class="v"><span class="tm-badge {{ $stClass }}">{{ $info->status }}</span></span></div>
                        <div class="r"><span class="k">Resolution Ref</span><span class="v">{{ $info->resolution }}</span></div>
                        <div class="r"><span class="k">Prepared By</span><span class="v">{{ $info->prepared_by }}</span></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@if(Auth::user()->hasPermission('Add Activity'))
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

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/glightbox/glightbox.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/glightbox/custom-glightbox.min.js") }}"></script>

@if(Auth::user()->hasPermission('Add Activity'))
<script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/filepond.min.js") }}"></script>
<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const activityId = {{ $info->id }};
    const uploadUrl = '{{ url("/activities/upload_supporting_documents") }}';
    const filesUrl = '{{ url("activities/get_uploaded_files") }}';
    const deleteBaseUrl = '{{ url("activities/delete_uploaded_file_view") }}';
    const videoExtensions = ['mp4', 'mov', 'avi', 'wmv', 'mkv'];

    function refreshSupportingDocuments() {
        $.ajax({
            type: 'POST', data: { activity_id: activityId }, dataType: "json",
            url: filesUrl,
            success: function (data) {
                const items = data['rows'];
                const gallery = document.getElementById('my_files');
                const empty = document.getElementById('my_files_empty');
                gallery.innerHTML = '';
                empty.style.display = items.length ? 'none' : '';
                gallery.style.display = items.length ? '' : 'none';

                items.forEach(item => {
                    const ext = item.filename.split('.').pop().toLowerCase();
                    const isVideo = videoExtensions.includes(ext);

                    const tile = document.createElement('div');
                    tile.classList.add('tm-gallery-item');
                    tile.setAttribute('data-id', item.id);

                    const a = document.createElement('a');
                    a.href = "{{ url('uploads_sangguniang') }}/" + item.filename;
                    a.classList.add('defaultGlightbox', 'glightbox-content');

                    const img = document.createElement('img');
                    img.src = isVideo ? "{{ url('uploads_sangguniang/video-thumbnail.png') }}" : a.href;
                    img.alt = isVideo ? 'video' : 'image';

                    a.appendChild(img);
                    tile.appendChild(a);

                    @if(Auth::user()->hasPermission('Edit Activity'))
                        const del = document.createElement('button');
                        del.type = 'button';
                        del.classList.add('tm-gallery-del');
                        del.title = 'Delete File';
                        del.setAttribute('onclick', 'delete_supporting_document(' + item.id + ')');
                        del.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>';
                        tile.appendChild(del);
                    @endif

                    gallery.appendChild(tile);
                });

                if (typeof defaultGlightbox !== 'undefined') defaultGlightbox.reload();
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
                    <p class="tm-upload-drop-desc">Add photos, videos, or documents for this activity.<br>Files appear in Documentation once uploaded.</p>
                    <span class="filepond--label-action tm-upload-drop-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Add files
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </span>
                </div>
            `,
            server: {
                process: uploadUrl + '?activity_id=' + activityId,
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
@endif
@endsection
