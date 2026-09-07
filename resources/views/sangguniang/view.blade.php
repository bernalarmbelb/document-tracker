@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/glightbox/glightbox.min.css") }}">
<style>
    .tm-kv { font-size: 13px; }
    .tm-kv .r { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-bottom: 1px solid var(--tm-line); }
    .tm-kv .r:last-child { border-bottom: none; }
    .tm-kv .k { color: var(--tm-muted); white-space: nowrap; }
    .tm-kv .v { text-align: right; font-weight: 600; }
    .tm-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
    .tm-gallery a { display: block; border-radius: 10px; overflow: hidden; border: 1px solid var(--tm-line); }
    .tm-gallery img { width: 100%; height: 130px; object-fit: cover; display: block; }
    .tm-gallery-empty { color: var(--tm-muted); font-style: italic; padding: 24px; text-align: center; }
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
                    @if(count($all_documents)>0)
                        <div class="tm-gallery">
                            @foreach($all_documents as $item)
                                @php
                                    $extension = strtolower(pathinfo($item->filename, PATHINFO_EXTENSION));
                                    $videoExtensions = ['mp4', 'mov', 'avi', 'wmv', 'mkv'];
                                @endphp
                                @if(in_array($extension, $videoExtensions))
                                    <a href="{{ upload_url('uploads_sangguniang', $item->filename) }}" class="defaultGlightbox glightbox-content">
                                        <img src="{{ url('uploads_sangguniang/video-thumbnail.png') }}" alt="video">
                                    </a>
                                @else
                                    <a href="{{ upload_url('uploads_sangguniang', $item->filename) }}" class="defaultGlightbox glightbox-content">
                                        <img src="{{ upload_url('uploads_sangguniang', $item->filename) }}" alt="image">
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="tm-gallery-empty">No documentation uploaded yet.</div>
                    @endif
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
@endsection

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/glightbox/glightbox.min.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/glightbox/custom-glightbox.min.js") }}"></script>
@endsection
