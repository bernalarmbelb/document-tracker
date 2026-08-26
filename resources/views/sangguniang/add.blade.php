@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/filepond/filepond.min.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImagePreview.min.css") }}">
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/tagify/tagify.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
@endsection

@section("content")

<div class="layout-px-spacing">
    <div class="tm-page">

        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">{{ $edit ? 'Edit' : 'New' }} Activity</h1>
                <div class="tm-crumb"><a href="{{ url('activities/all') }}">Activities</a> / {{ $edit ? 'Edit' : 'Add New' }}</div>
            </div>
        </div>

        <form action="{{ $edit ? url('/activities/save_changes/') : url('/activities/save_add/') }}" method="post" autocomplete="off">
            @csrf
            <input type="hidden" name="activity_id" value="{{ $info->id ?? '' }}"/>
            <div class="row g-3">
                {{-- Left --}}
                <div class="col-lg-6">
                    <div class="tm-card">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="tm-label">Activity Date &amp; Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="tm-input" value={{ $edit ? date("Y-m-d\TH:i", strtotime($info->activity_date)) : date("Y-m-d\TH:i") }} required name="activity_date">
                            </div>
                            <div class="col-md-6">
                                <label class="tm-label">Duration <span class="text-danger">*</span></label>
                                <input type="text" class="tm-input" style="text-align:center;font-weight:700;color:var(--tm-primary)" value="{{ $edit ? $info->duration : '' }}" required name="duration">
                            </div>
                            <div class="col-12">
                                <label class="tm-label">Activity Title <span class="text-danger">*</span></label>
                                <input type="text" class="tm-input" value="{{ $edit ? $info->activity_title : '' }}" required name="activity_title" >
                            </div>
                            <div class="col-12">
                                <label class="tm-label">Location <span class="text-danger">*</span></label>
                                <input type="text" class="tm-input" value="{{ $edit ? $info->location : '' }}" required name="location" >
                            </div>
                            <div class="col-12">
                                <label class="tm-label">Description <span class="text-danger">*</span></label>
                                <textarea class="tm-textarea" rows="3" required name="description">{{ $edit ? $info->description : '' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="tm-label">Event Organizers <span class="text-danger">*</span></label>
                                <input type="text" class="tm-input" value="{{ $edit ? $info->event_organizers : '' }}" required name="event_organizers" >
                            </div>
                            <div class="col-md-6">
                                <label class="tm-label">Sponsors <span class="text-danger">*</span></label>
                                <input type="text" class="tm-input" value="{{ $edit ? $info->sponsors : '' }}" required name="sponsors">
                            </div>
                            <div class="col-md-6">
                                <label class="tm-label">Guests/Participants <span class="text-danger">*</span></label>
                                <input type="text" class="tm-input" value="{{ $edit ? $info->guests_participants : '' }}" required name="guests_participants" >
                            </div>
                            <div class="col-md-6">
                                <label class="tm-label">Expected Attendees <span class="text-danger">*</span></label>
                                <input type="number" class="tm-input" value="{{ $edit ? $info->expected_attendees : '' }}" required name="expected_attendees">
                            </div>
                            <div class="col-md-6">
                                <label class="tm-label">Actual Attendees <span class="text-danger">*</span></label>
                                <input type="number" class="tm-input" value="{{ $edit ? $info->actual_attendees : '' }}" required name="actual_attendees" >
                            </div>
                            <div class="col-md-6">
                                <label class="tm-label">Budget/Funding Source <span class="text-danger">*</span></label>
                                <input type="text" class="tm-input" value="{{ $edit ? $info->budget : '' }}" required name="budget">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right --}}
                <div class="col-lg-6">
                    <div class="tm-card">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="tm-label">Objectives / Purpose <span class="text-danger">*</span></label>
                                <textarea class="tm-textarea" rows="5" required name="objective">{{ $edit ? $info->objective : '' }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="tm-label">Remarks / Notes <span class="text-danger">*</span></label>
                                <textarea class="tm-textarea" rows="5" required name="remarks">{{ $edit ? $info->remarks : '' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="tm-label">Resolution / Approval Ref <span class="text-danger">*</span></label>
                                <input type="text" class="tm-input" value="{{ $edit ? $info->resolution : '' }}" required name="resolution" >
                            </div>
                            <div class="col-md-6">
                                <label class="tm-label">Prepared By / Documented By <span class="text-danger">*</span></label>
                                <input type="text" class="tm-input" value="{{ $edit ? $info->prepared_by : '' }}" required name="prepared_by">
                            </div>
                            <div class="col-6">
                                <label class="tm-label">Activity Type <span class="text-danger">*</span></label>
                                <input type="text" list="activity_types" class="tm-input" value="{{ $edit ? $info->type_of_activity : '' }}" required name="type_of_activity">
                                <datalist id="activity_types">
                                    <option value="Regular Session"><option value="Special Session"><option value="Committee Hearing">
                                    <option value="Committee Meeting"><option value="Public Hearing"><option value="Barangay Ordinance/Resolution Review">
                                    <option value="Budget Deliberation"><option value="Performance Review"><option value="Approval of MOA/Contract">
                                    <option value="Flag Ceremony"><option value="Representation in LGU Events"><option value="Awarding/Recognition Ceremony">
                                    <option value="Site Inspection"><option value="Barangay Visit"><option value="Community Outreach">
                                    <option value="Capacity-Building/Training"><option value="Others">
                                </datalist>
                            </div>
                            <div class="col-6">
                                <label class="tm-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="tm-select" required>
                                    <option {{ $edit && $info->status=='UPCOMING' ? 'selected' : '' }}>UPCOMING</option>
                                    <option {{ $edit && $info->status=='ONGOING' ? 'selected' : '' }}>ONGOING</option>
                                    <option {{ $edit && $info->status=='COMPLETED' ? 'selected' : '' }}>COMPLETED</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex gap-3">
                                <button type="submit" name="btnsaveasdraft" value="1" class="tm-btn tm-btn-outline tm-btn-block">Save</button>
                                <button type="submit" name="btnsave" value="1" class="tm-btn tm-btn-primary tm-btn-block">Save and Return</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @if($edit)
            <div class="row g-3" style="margin-top:0">
                <div class="col-lg-6">
                    <div class="tm-card blog-create-section">
                        <input type="hidden" name="supporting_document_activity_id" value="{{ $info->id ?? '' }}">
                        <label class="tm-label">Supporting Documents <small class="tm-muted">Uploads save automatically.</small></label>
                        <div class="multiple-file-upload">
                            <input type="file" class="filepond file-upload-multiple" id="filepond" multiple data-allow-reorder="true" data-max-file-size="3MB" data-max-files="5">
                        </div>
                        <ul class="list-group mt-2">
                            @foreach($all_documents as $item)
                                <li class="list-group-item">
                                    <a href="{{ url('activities/delete_uploaded_file_view/'.$item->id) }}" onclick="return confirm('Are you sure you want to delete this file?')" title="Delete File" class="me-2 text-danger"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></a>
                                    <a href="{{ url('uploads_sangguniang/'.$item->filename) }}" target="_blank">{{ substr($item->filename,11) }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@section("additional_footer")

<script src="{{ asset("assets/src/plugins/src/filepond/filepond.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginFileValidateType.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImageExifOrientation.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImagePreview.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImageCrop.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImageResize.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/FilePondPluginImageTransform.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/filepond/filepondPluginFileValidateSize.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/tagify/tagify.min.js") }}"></script>
<script src="{{ asset("assets/src/assets/js/apps/blog-create.js") }}"></script>

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

          
        
    // Register FilePond on the input field
    @if($edit)
        FilePond.create(document.getElementById('filepond'), {
            allowMultiple: true,
            storeAsFile: true,
            server: {
                process: '{{ url("/activities/upload_supporting_documents?activity_id=$info->id") }}', 
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            
            }
        });

       
    @endif

    

</script>
@endsection