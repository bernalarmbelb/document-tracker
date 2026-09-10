@extends("template",['menu' => $menu])

@section("additional_head")

<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/assets/css/light/scrollspyNav.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/assets/css/dark/scrollspyNav.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/plugins/css/dark/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />

<link href="{{ asset("assets/src/assets/css/light/components/modal.css") }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="{{ asset("assets/css/app.css") }}">

{{-- Page-scoped styles for the Grid/List toggle, the list-view table, and the
     pagination footer — kept local to this page (not theme.css) since none of
     it is reused elsewhere yet. Built entirely from the existing --tm-* tokens. --}}
<style>
    .tm-toolbar-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .tm-viewswitch { display: inline-flex; align-items: center; border: 1px solid rgba(51,51,51,0.2); border-radius: 8px; overflow: hidden; background: var(--tm-card); }
    .tm-viewswitch a {
        display: inline-flex; align-items: center; gap: 7px; font-family: var(--tm-font); font-size: 12px; font-weight: 700;
        letter-spacing: 0.03em; text-transform: uppercase; color: var(--tm-muted); padding: 9px 14px; text-decoration: none;
        transition: background-color .2s ease, color .2s ease;
    }
    .tm-viewswitch a svg { width: 15px; height: 15px; vertical-align: -3px; }
    .tm-viewswitch a + a { border-left: 1px solid rgba(51,51,51,0.2); }
    .tm-viewswitch a.active { background: var(--tm-primary); color: #fff; }
    .tm-viewswitch a:not(.active):hover { background: rgba(66,122,181,0.08); color: var(--tm-primary); }

    .tm-search-card { cursor: pointer; transition: border-color .15s ease, box-shadow .15s ease; }
    .tm-search-card:hover { border-color: rgba(66,122,181,.35); box-shadow: 0 4px 14px rgba(16,24,40,.08); }
    .tm-search-card:focus-visible { outline: 2px solid var(--tm-primary); outline-offset: 2px; }

    /* theme.css's .tm-search-masonry uses CSS multi-column, which fills the
       whole left column top-to-bottom before starting the next one — with a
       paginated (smaller) result set that made reading left-to-right jump
       several items ahead each time. This is the no-JS fallback: a plain
       grid (correct row-major reading order, but no masonry packing — every
       row's height is its tallest card). A small script below upgrades this
       into real packed masonry once card heights are known, by placing each
       card into whichever column is currently shortest — which keeps cards
       close to reading order (since it's still assigning them in sequence)
       while eliminating the leftover whitespace under shorter cards. Scoped
       to this page only, since .tm-search-masonry isn't used anywhere else. */
    .tm-search-masonry { column-count: unset; display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
    .tm-search-masonry-item { display: block; width: auto; margin-bottom: 0; break-inside: auto; }
    @media (max-width: 1399px) { .tm-search-masonry { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 991px)  { .tm-search-masonry { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px)  { .tm-search-masonry { grid-template-columns: 1fr; } }

    .tm-search-table-wrap { overflow-x: auto; border: 1px solid var(--tm-line); border-radius: 12px; background: var(--tm-card); }
    .tm-search-table-wrap .tm-table tbody tr { cursor: pointer; }
    .tm-search-row-sub { font-size: 11.5px; color: var(--tm-muted); margin-top: 2px; }

    /* Smaller action icons, matching the mockup — overrides theme.css's 26px
       .tm-actions svg default just on this page's list view, not sitewide. */
    .tm-search-table-wrap .tm-actions a { width: 30px; height: 30px; border-radius: 7px; justify-content: center; color: var(--tm-muted); }
    .tm-search-table-wrap .tm-actions a:hover { background: rgba(66,122,181,.10); color: var(--tm-primary); }
    .tm-search-table-wrap .tm-actions svg { width: 16px; height: 16px; }

    /* Sortable column headers */
    .tm-th-sort { display: inline-flex; align-items: center; gap: 5px; color: inherit; text-decoration: none; }
    .tm-th-sort:hover { color: var(--tm-primary); }
    .tm-th-sort svg { width: 11px; height: 11px; opacity: .35; }
    .tm-th-sort.active svg { opacity: 1; color: var(--tm-primary); }

    .tm-search-listfoot { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px 20px; margin-top: 16px; }
    .tm-search-listfoot-left { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
    .tm-search-info { font-size: 12px; color: var(--tm-muted); }
    .tm-search-perpage-form { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--tm-muted); }

    .tm-search-pagination { display: inline-flex; align-items: center; }
    .tm-search-pagination a, .tm-search-pagination span.tm-page-current {
        min-width: 24px; padding: 4px 5px; margin: 0 1px; border-radius: 4px; text-align: center;
        font-family: var(--tm-font); font-size: 11px; font-weight: 500; line-height: 1; text-decoration: none; display: inline-block;
    }
    .tm-search-pagination a { color: #333; }
    .tm-search-pagination a:hover { background: rgba(66,122,181,.10); color: var(--tm-primary); }
    .tm-search-pagination span.tm-page-current { background: var(--tm-primary); color: #fff; }
    .tm-search-pagination span.tm-page-disabled { color: rgba(51,51,51,.35); }
    .tm-search-pagination span.tm-page-ellipsis { padding: 0 4px; color: var(--tm-muted); font-size: 11px; }

    @media (max-width: 640px) { .tm-search-listfoot { flex-direction: column; align-items: flex-start; } }
</style>
@endsection

@section("content")

@php
    $baseQuery = ['view' => $view, 'per_page' => $per_page, 'sort' => $sort_col, 'dir' => $sort_dir];
    $pageUrl = fn ($p) => url('/global_search') . '?' . http_build_query(array_merge($baseQuery, ['page' => $p]));
    $sortUrl = fn ($col) => url('/global_search') . '?' . http_build_query(array_merge($baseQuery, [
        'sort' => $col,
        'dir' => ($sort_col === $col && $sort_dir === 'asc') ? 'desc' : 'asc',
        'page' => 1,
    ]));

    // "Sort by" dropdown — works in both Grid and List (List also has the
    // clickable column headers below; both drive the same session state).
    $sortPresets = [
        'number_desc' => 'Newest No. First',
        'number_asc'  => 'Oldest No. First',
        'date_desc'   => 'Newest Date First',
        'date_asc'    => 'Oldest Date First',
        'title_asc'   => 'Title A–Z',
        'title_desc'  => 'Title Z–A',
        'author_asc'  => 'Author A–Z',
        'author_desc' => 'Author Z–A',
    ];
    $currentSortKey = $sort_col . '_' . $sort_dir;
    $sortPresetUrl = fn ($key) => url('/global_search') . '?' . http_build_query(array_merge($baseQuery, [
        'sort' => explode('_', $key)[0],
        'dir' => explode('_', $key)[1],
        'page' => 1,
    ]));
@endphp

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
                    <div class="tm-toolbar-right">
                        <div class="tm-dd" data-tm-dropdown>
                            <button type="button" class="tm-dd-toggle">
                                <span class="tm-dd-label">{{ $sortPresets[$currentSortKey] ?? 'Sort' }}</span>
                                <svg class="tm-dd-caret" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </button>
                            <div class="tm-dd-menu">
                                @foreach($sortPresets as $key => $label)
                                    <a class="tm-dd-option {{ $currentSortKey === $key ? 'active' : '' }}" href="{{ $sortPresetUrl($key) }}">{{ $label }}</a>
                                @endforeach
                            </div>
                        </div>
                        <div class="tm-viewswitch" role="group" aria-label="Result view">
                            <a href="{{ url('/global_search') }}?{{ http_build_query(array_merge($baseQuery, ['view' => 'grid', 'page' => 1])) }}" class="{{ $view === 'grid' ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                                Grid
                            </a>
                            <a href="{{ url('/global_search') }}?{{ http_build_query(array_merge($baseQuery, ['view' => 'list', 'page' => 1])) }}" class="{{ $view === 'list' ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
                                List
                            </a>
                        </div>
                        <button type="button" class="tm-btn tm-btn-outline tm-btn-sm" data-bs-toggle="modal" data-bs-target="#global-search">
                            Modify Search
                        </button>
                    </div>
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

        @if($result_count > 0)

            @if($view === 'list')

                {{-- ============ LIST VIEW ============ --}}
                @php
                    $sortHeaders = [
                        'type' => ['label' => 'Type', 'class' => ''],
                        'title' => ['label' => 'Title', 'class' => 'tm-col-title'],
                        'number' => ['label' => 'No.', 'class' => ''],
                        'author' => ['label' => 'Author', 'class' => ''],
                        'date' => ['label' => 'Date', 'class' => ''],
                    ];
                @endphp
                <div class="tm-search-table-wrap">
                    <table class="tm-table">
                        <thead>
                            <tr>
                                @foreach($sortHeaders as $col => $h)
                                    <th class="{{ $h['class'] }}">
                                        <a href="{{ $sortUrl($col) }}" class="tm-th-sort {{ $sort_col === $col ? 'active' : '' }}">
                                            {{ $h['label'] }}
                                            @if($sort_col === $col && $sort_dir === 'desc')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            @else
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
                                            @endif
                                        </a>
                                    </th>
                                @endforeach
                                <th style="width:90px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $row)
                                @php $item = $row->item; @endphp
                                @switch($row->type)
                                    @case('resolution')
                                        @php
                                            $viewUrl = url("resolutions/view/".$item->id);
                                            $pdfUrl = url("resolutions/generate_pdf/".$item->id);
                                        @endphp
                                        <tr onclick="location.href='{{ $viewUrl }}'">
                                            <td><span class="tm-badge tm-badge-info">Resolution</span></td>
                                            <td class="tm-col-title">
                                                {{ ucwords(strtolower($item->title), " \t\r\n\f\v(-\"'") }}
                                                <div class="tm-search-row-sub">Attested by {{ $item->attested_by }}</div>
                                            </td>
                                            <td>{{ $item->series_number }}</td>
                                            <td>{{ $item->author_name }}</td>
                                            <td>{{ date("M d, Y", strtotime($item->date_created)) }}<br>{{ date("h:iA", strtotime($item->date_created)) }}</td>
                                            <td>
                                                <ul class="tm-actions">
                                                    <li><a href="{{ $viewUrl }}" onclick="event.stopPropagation()" title="View"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg></a></li>
                                                    <li><a href="{{ $pdfUrl }}" target="_blank" onclick="event.stopPropagation()" title="PDF"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg></a></li>
                                                </ul>
                                            </td>
                                        </tr>
                                        @break

                                    @case('ordinance')
                                        @php
                                            $viewUrl = url("ordinances/view/".$item->id);
                                            $pdfUrl = url("ordinances/generate_pdf/".$item->id);
                                        @endphp
                                        <tr onclick="location.href='{{ $viewUrl }}'">
                                            <td><span class="tm-badge tm-badge-sec">Ordinance</span></td>
                                            <td class="tm-col-title">
                                                {{ $item->short_title }}
                                                <div class="tm-search-row-sub">Subject: {{ $item->subject_matter }}</div>
                                            </td>
                                            <td>{{ $item->ordinance_number }}</td>
                                            <td>{{ $item->author_name }}</td>
                                            <td>{{ date("M d, Y", strtotime($item->date_created)) }}<br>{{ date("h:iA", strtotime($item->date_created)) }}</td>
                                            <td>
                                                <ul class="tm-actions">
                                                    <li><a href="{{ $viewUrl }}" onclick="event.stopPropagation()" title="View"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg></a></li>
                                                    <li><a href="{{ $pdfUrl }}" target="_blank" onclick="event.stopPropagation()" title="PDF"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg></a></li>
                                                </ul>
                                            </td>
                                        </tr>
                                        @break

                                    @case('minutes')
                                        @php $viewUrl = url("minutes/view/".$item->id); @endphp
                                        <tr onclick="location.href='{{ $viewUrl }}'">
                                            <td><span class="tm-badge tm-badge-acc">Minutes</span></td>
                                            <td class="tm-col-title">
                                                {{ $item->presiding_officer }} — {{ $item->barangay_name }}
                                                <div class="tm-search-row-sub">Venue: {{ $item->venue }}</div>
                                            </td>
                                            <td>{{ $item->series_number }}</td>
                                            <td>—</td>
                                            <td>{{ date("M d, Y", strtotime($item->date_created)) }}<br>{{ date("h:iA", strtotime($item->date_created)) }}</td>
                                            <td>
                                                <ul class="tm-actions">
                                                    <li><a href="{{ $viewUrl }}" onclick="event.stopPropagation()" title="View"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg></a></li>
                                                </ul>
                                            </td>
                                        </tr>
                                        @break

                                    @case('communication')
                                        @php $viewUrl = url("communications/view/".$item->id); @endphp
                                        <tr onclick="location.href='{{ $viewUrl }}'">
                                            <td><span class="tm-badge tm-badge-ok">Communication</span></td>
                                            <td class="tm-col-title">
                                                {{ $item->particulars }}
                                                <div class="tm-search-row-sub">
                                                    @if($item->communication_type=='INCOMING')
                                                        Source: {{ $item->source }}
                                                    @else
                                                        Addressee: {{ $item->addressee }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td>—</td>
                                            <td>
                                                @if($item->communication_type=='INCOMING')
                                                    {{ $item->received_by }}
                                                @else
                                                    {{ $item->released_by }}
                                                @endif
                                            </td>
                                            <td>{{ date("M d, Y", strtotime($item->created_at)) }}<br>{{ date("h:iA", strtotime($item->created_at)) }}</td>
                                            <td>
                                                <ul class="tm-actions">
                                                    <li><a href="{{ $viewUrl }}" onclick="event.stopPropagation()" title="View"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg></a></li>
                                                </ul>
                                            </td>
                                        </tr>
                                        @break
                                @endswitch
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @else

                {{-- ============ GRID VIEW ============ --}}
                <div class="tm-search-masonry">
                    @foreach($items as $row)
                        @php $item = $row->item; @endphp
                        @switch($row->type)
                            @case('resolution')
                                @php
                                    $viewUrl = url("resolutions/view/".$item->id);
                                    $pdfUrl = url("resolutions/generate_pdf/".$item->id);
                                @endphp
                                <div class="tm-search-masonry-item">
                                    <div class="card style-4 tm-search-card" onclick="location.href='{{ $viewUrl }}'" tabindex="0" role="link" aria-label="View {{ $item->title }}">
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
                                                    <a href="{{ $viewUrl }}" onclick="event.stopPropagation()" class="btn btn-block btn-light-primary _effect--ripple waves-effect waves-light">View</a>
                                                </div>
                                                <div class="d-grid gap-2 col-6 mx-auto">
                                                    <a href="{{ $pdfUrl }}" target="_blank" onclick="event.stopPropagation()" class="btn btn-block btn-light-info _effect--ripple waves-effect waves-light">PDF</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @break

                            @case('ordinance')
                                @php
                                    $viewUrl = url("ordinances/view/".$item->id);
                                    $pdfUrl = url("ordinances/generate_pdf/".$item->id);
                                @endphp
                                <div class="tm-search-masonry-item">
                                    <div class="card style-4 tm-search-card" onclick="location.href='{{ $viewUrl }}'" tabindex="0" role="link" aria-label="View {{ $item->short_title }}">
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
                                                    <a href="{{ $viewUrl }}" onclick="event.stopPropagation()" class="btn btn-block btn-light-primary _effect--ripple waves-effect waves-light">View</a>
                                                </div>
                                                <div class="d-grid gap-2 col-6 mx-auto">
                                                    <a href="{{ $pdfUrl }}" target="_blank" onclick="event.stopPropagation()" class="btn btn-block btn-light-info _effect--ripple waves-effect waves-light">PDF</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @break

                            @case('minutes')
                                @php $viewUrl = url("minutes/view/".$item->id); @endphp
                                <div class="tm-search-masonry-item">
                                    <div class="card style-4 tm-search-card" onclick="location.href='{{ $viewUrl }}'" tabindex="0" role="link" aria-label="View minutes {{ $item->series_number }}">
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
                                                    <a href="{{ $viewUrl }}" onclick="event.stopPropagation()" class="btn btn-block btn-light-primary _effect--ripple waves-effect waves-light">View</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @break

                            @case('communication')
                                @php $viewUrl = url("communications/view/".$item->id); @endphp
                                <div class="tm-search-masonry-item">
                                    <div class="card style-4 tm-search-card" onclick="location.href='{{ $viewUrl }}'" tabindex="0" role="link" aria-label="View {{ $item->particulars }}">
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
                                                    <a href="{{ $viewUrl }}" onclick="event.stopPropagation()" class="btn btn-block btn-light-primary _effect--ripple waves-effect waves-light">View</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @break
                        @endswitch
                    @endforeach
                </div>

            @endif

            {{-- ============ PAGINATION FOOTER (shared by both views) ============ --}}
            <div class="tm-search-listfoot">
                <div class="tm-search-listfoot-left">
                    <span class="tm-search-info">
                        Showing {{ (($page - 1) * $per_page) + 1 }}–{{ min($page * $per_page, $result_count) }} of {{ $result_count }}
                    </span>
                    <form method="get" action="{{ url('/global_search') }}" class="tm-search-perpage-form">
                        <input type="hidden" name="view" value="{{ $view }}">
                        <input type="hidden" name="sort" value="{{ $sort_col }}">
                        <input type="hidden" name="dir" value="{{ $sort_dir }}">
                        <input type="hidden" name="page" value="1">
                        Rows per page
                        <input type="number" name="per_page" class="tm-perpage-input" min="4" max="200" value="{{ $per_page }}" onchange="this.form.submit()">
                    </form>
                </div>
                <div class="tm-search-pagination">
                    @if($page <= 1)
                        <span class="tm-page-disabled">‹</span>
                    @else
                        <a href="{{ $pageUrl($page - 1) }}">‹</a>
                    @endif

                    @foreach($page_list as $p)
                        @if($p === '…')
                            <span class="tm-page-ellipsis">…</span>
                        @elseif($p === $page)
                            <span class="tm-page-current">{{ $p }}</span>
                        @else
                            <a href="{{ $pageUrl($p) }}">{{ $p }}</a>
                        @endif
                    @endforeach

                    @if($page >= $last_page)
                        <span class="tm-page-disabled">›</span>
                    @else
                        <a href="{{ $pageUrl($page + 1) }}">›</a>
                    @endif
                </div>
            </div>

        @endif

    </div>

</div>
@endsection

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>
<script src="{{ asset("js/app.js") }}"></script>
<script>

    // Packs the Grid view's cards into real masonry (no per-row dead space
    // under shorter cards) by placing each one into whichever column is
    // currently shortest — done in DOM order, so it stays close to reading
    // order instead of the "whole column, then the next" jump that CSS
    // multi-column (column-count) produces. No-op on List view (no
    // .tm-search-masonry there) and fails safely: if anything here throws,
    // the plain CSS grid already in place keeps the correct reading order,
    // just without the packing.
    (function () {
        function layoutMasonry() {
            var container = document.querySelector('.tm-search-masonry');
            if (!container) return;

            var items = Array.prototype.slice.call(container.children).filter(function (el) {
                return el.classList.contains('tm-search-masonry-item');
            });
            if (!items.length) return;

            var width = container.clientWidth;
            var cols = width >= 1400 ? 4 : width >= 992 ? 3 : width >= 576 ? 2 : 1;
            var gap = 24;

            if (cols <= 1) {
                container.style.position = '';
                container.style.height = '';
                items.forEach(function (item) {
                    item.style.position = '';
                    item.style.left = '';
                    item.style.top = '';
                    item.style.width = '';
                });
                return;
            }

            var colWidth = (width - gap * (cols - 1)) / cols;
            var colHeights = new Array(cols).fill(0);

            container.style.position = 'relative';

            items.forEach(function (item) {
                item.style.position = 'absolute';
                item.style.width = colWidth + 'px';

                var minCol = 0;
                for (var c = 1; c < cols; c++) {
                    if (colHeights[c] < colHeights[minCol]) minCol = c;
                }

                var x = minCol * (colWidth + gap);
                var y = colHeights[minCol];
                item.style.left = x + 'px';
                item.style.top = y + 'px';

                colHeights[minCol] = y + item.offsetHeight + gap;
            });

            container.style.height = (Math.max.apply(null, colHeights) - gap) + 'px';
        }

        var resizeTimer;
        function scheduleLayout() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                try { layoutMasonry(); } catch (e) {}
            }, 120);
        }

        function run() {
            try { layoutMasonry(); } catch (e) {}
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', run);
        } else {
            run();
        }
        window.addEventListener('load', run);
        window.addEventListener('resize', scheduleLayout);
    })();

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
