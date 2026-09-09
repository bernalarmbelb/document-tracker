@extends("template",['menu' => $menu])

@section("additional_head")

<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/assets/css/light/scrollspyNav.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/assets/css/dark/scrollspyNav.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/plugins/css/dark/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />

<link href="{{ asset("assets/src/assets/css/light/components/modal.css") }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="{{ asset("assets/css/app.css") }}">
@endsection

@section("content")

<div class="layout-px-spacing">

    <div class="middle-content container-xxl p-0">

        <div class="row layout-top-spacing">
            <div class="d-flex justify-content-between">
                <div class="ms-2 mb-4">
                    <h4 class="mb-0 page-title">SEARCH RESULTS</h4>
                    <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Search Results</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="ms-2 mb-4 tm-search-summary">
                <div class="tm-search-summary-row">
                    <div class="tm-search-summary-count">
                        <strong>{{ $result_count }}</strong> {{ \Illuminate\Support\Str::plural('document', $result_count) }} found
                    </div>
                    <button type="button" class="tm-btn tm-btn-outline tm-btn-sm" data-bs-toggle="modal" data-bs-target="#global-search">
                        Modify Search
                    </button>
                </div>
                @if(count($filters_applied))
                    <div class="tm-search-summary-filters">
                        @foreach($filters_applied as $filter)
                            <span class="tm-badge tm-badge-info">{{ $filter }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($result_count === 0)
                <div class="tm-search-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <h5>No documents match your search</h5>
                    <p class="tm-muted">Try fewer keywords, double-check the spelling, or widen the date range. If you're only searching one document type, make sure it's ticked in the search box.</p>
                    <button type="button" class="tm-btn tm-btn-primary tm-btn-sm" data-bs-toggle="modal" data-bs-target="#global-search">Try Another Search</button>
                </div>
            @endif
        </div>

        <div class="tm-search-masonry">

                {{-- RESOLUTIONS --}}
                @foreach($resolutions as $item)
                    <div class="tm-search-masonry-item">
                        <div class="card style-4">
                            <div class="card-body pt-3">
                                <span class="tm-badge tm-badge-info mb-2">Resolution</span>
                                <div class="media mt-0 mb-3">
                                    <div class="">
                                        <div class="avatar avatar-md me-3">
                                            <img alt="avatar" src="{{ asset('assets/src/assets/img/resolution.jpg') }}" class="rounded-circle">
                                        </div>
                                    </div>
                                    <div class="media-body">
                                        <h4 class="media-heading mb-0">{{ ucwords(strtolower($item->title), " \t\r\n\f\v(-\"'") }}</h4>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="fw-semibold mb-0">{{ $item->series_number }}</h5>
                                        <span class="mt-0">Author: {{ $item->author_name }}</span>
                                    </div>

                                    <div class="text-end text-body-secondary">
                                        <small>{{ date("F d, Y", strtotime($item->date_created)) }}<br/>{{ date("h:iA", strtotime($item->date_created)) }}</small>
                                    </div>                                                        
                                </div>
                                
                                <p class="card-text mt-4 mb-0">
                                    Attested by {{ $item->attested_by }}.<br>
                                    Recorded by {{ $item->recorded_by }}.<br>
                                    Approved by {{ $item->approved_by }}.
                                </p>
                            </div>
                            <div class="card-footer pt-0 border-0 text-center">
                                <div class="row">
                                    <div class="d-grid gap-2 col-6 mx-auto">
                                        <a href="{{ url("resolutions/view/".$item->id) }}" class="btn btn-block btn-light-primary _effect--ripple waves-effect waves-light">View</a>
                                    </div>
                                    <div class="d-grid gap-2 col-6 mx-auto">
                                        <a href="{{ url("resolutions/generate_pdf/".$item->id) }}"  target="_blank" class="btn btn-block btn-light-info _effect--ripple waves-effect waves-light">PDF</a>
                                    </div>             
                                </div>           
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- ORDINANCES --}}
                @foreach($ordinances as $item)
                    <div class="tm-search-masonry-item">
                        <div class="card style-4">
                            <div class="card-body pt-3">
                                <span class="tm-badge tm-badge-sec mb-2">Ordinance</span>
                                <div class="media mt-0 mb-3">
                                    <div class="">
                                        <div class="avatar avatar-md me-3">
                                            <img alt="avatar" src="{{ asset('assets/src/assets/img/ordinance.jpg') }}" class="rounded-circle">
                                        </div>
                                    </div>
                                    <div class="media-body">
                                        <h4 class="media-heading mb-0">{{ $item->short_title }}</h4>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="fw-semibold mb-0">{{ $item->ordinance_number }}</h5>
                                        <span class="mt-0">Author: {{ $item->author_name }}</span>
                                    </div>

                                    <div class="text-end text-body-secondary">
                                        <small>{{ date("F d, Y", strtotime($item->date_created)) }}<br/>{{ date("h:iA", strtotime($item->date_created)) }}</small>
                                    </div>                                                        
                                </div>
                                <p class="card-text mt-4 mb-0">Subject Matter: {{ $item->subject_matter }}. Type: {{ $item->ordinance_type }}. Source Book Number {{ $item->source_book_number }}. SP Resolutions: {{ $item->sp_resolutions }}. Publicaton/Posting: {{ date("M d, Y", strtotime($item->publication_postings)) }}</p>
                            </div>
                            <div class="card-footer pt-0 border-0 text-center">
                                <div class="row">
                                    <div class="d-grid gap-2 col-6 mx-auto">
                                        <a href="{{ url("ordinances/view/".$item->id) }}" class="btn btn-block btn-light-primary _effect--ripple waves-effect waves-light">View</a>
                                    </div>
                                    <div class="d-grid gap-2 col-6 mx-auto">
                                        <a href="{{ url("ordinances/generate_pdf/".$item->id) }}"  target="_blank" class="btn btn-block btn-light-info _effect--ripple waves-effect waves-light">PDF</a>
                                    </div>             
                                </div>           
                            </div>
                        </div>
                    </div>
                @endforeach


                {{-- MINUTES --}}
                @foreach($minutes as $item)
                    <div class="tm-search-masonry-item">
                        <div class="card style-4">
                            <div class="card-body pt-3">
                                <span class="tm-badge tm-badge-acc mb-2">Minutes</span>
                                <div class="media mt-0 mb-3">
                                    <div class="">
                                        <div class="avatar avatar-md me-3">
                                            <img alt="avatar" src="{{ asset('assets/src/assets/img/minute.jpg') }}" class="rounded-circle">
                                        </div>
                                    </div>
                                    <div class="media-body">
                                        <h4 class="media-heading mb-0">{{ $item->presiding_officer }}</h4>
                                        <p class="media-text">{{ $item->series_number }}</p>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="fw-semibold mb-0">{{ $item->barangay_name }}</h5>
                                    <span class="mt-0">Venue: {{ $item->venue }}</span>
                                    </div>

                                    <div class="text-end text-body-secondary">
                                        <small>{{ date("F d, Y", strtotime($item->date_created)) }}<br/>{{ date("h:iA", strtotime($item->date_created)) }}</small>
                                    </div>                                                        
                                </div>
                                <p class="card-text mt-4 mb-0">{{ $item->short_description }}</p>
                            </div>
                            <div class="card-footer pt-0 border-0 text-center">
                                <div class="row">
                                    <div class="d-grid gap-2 col-6 mx-auto">
                                        <a href="{{ url("minutes/view/".$item->id) }}" class="btn btn-block btn-light-primary _effect--ripple waves-effect waves-light">View</a>
                                    </div>
                                    <div class="d-grid gap-2 col-6 mx-auto">
                                        <a href="{{ url("minutes/generate_pdf/".$item->id) }}"  target="_blank" class="btn btn-block btn-light-info _effect--ripple waves-effect waves-light">PDF</a>
                                    </div>             
                                </div>           
                            </div>
                        </div>
                    </div>
                @endforeach


                {{-- COMMUNICATIONS --}}
                @foreach($communications as $item)
                    <div class="tm-search-masonry-item">
                        <div class="card style-4">
                            <div class="card-body pt-3">
                                <span class="tm-badge tm-badge-ok mb-2">Communication</span>
                                <div class="media mt-0 mb-3">
                                    <div class="">
                                        <div class="avatar avatar-md me-3">
                                            <img alt="avatar" src="{{ asset('assets/src/assets/img/communication.jpg') }}" class="rounded-circle">
                                        </div>
                                    </div>
                                    <div class="media-body">
                                        <p class="media-text mb-0 {{ $item->communication_type=='INCOMING' ? 'text-info' : 'text-warning' }}">{{ $item->communication_type }}</p>
                                        <h4 class="media-heading ">{{ $item->particulars }}</h4>

                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    @if($item->communication_type=='INCOMING')
                                        <div>
                                            <h5 class="fw-semibold mb-0">Received on {{ date("M d, Y", strtotime($item->date_received)) }}</h5>
                                            <span class="mt-0">Received by {{ $item->received_by }} </span>                                           
                                        </div>
                                    @else
                                        <div>
                                            <h5 class="fw-semibold mb-0">Released on {{ date("M d, Y", strtotime($item->date_released)) }}</h5>
                                            <span class="mt-0">Released by {{ $item->released_by }} </span>                                           
                                        </div>
                                    @endif

                                    <div class="text-end text-body-secondary">
                                        <small>{{ date("F d, Y", strtotime($item->created_at)) }}<br/>{{ date("h:iA", strtotime($item->created_at)) }}</small>
                                    </div>                                                        
                                </div>
                                @if($item->communication_type=='INCOMING')
                                    <p class="card-text mt-4 mb-0">Source: {{ $item->source }}</p>
                                @else
                                    <p class="card-text mt-4 mb-0">Addressee: {{ $item->addressee }}</p>
                                @endif
                            </div>
                            <div class="card-footer pt-0 border-0 text-center">
                                <div class="row">
                                    <div class="d-grid gap-2 col-6 mx-auto">
                                        <a href="{{ url("communications/view/".$item->id) }}" class="btn btn-block btn-light-primary _effect--ripple waves-effect waves-light">View</a>
                                    </div>
                                    
                                </div>           
                            </div>
                        </div>
                    </div>
                @endforeach


            </div>

        </div>

    </div>

</div>
@endsection

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>
<script src="{{ asset("js/app.js") }}"></script>
<script>
   

    function confirm_delete($url)
    {
        Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                'Deleted!',
                'Record has been deleted.',
                'success'
                );
                window.location.href = $url ; 
            }
        });    
    }   
    
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
                Swal.fire(
                'Archived!',
                'Record has been archived.',
                'success'
                );
                window.location.href = $url ; 
            }
        });    
    }    
    
</script>
@endsection