<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>Pto. Diaz Document Tracker </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset("assets/img/favicon.png") }}"/>
    <link href="{{ asset("assets/css/light/loader.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset("assets/css/dark/loader.css") }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset("assets/loader.js") }}"></script>
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">
    <link href="{{ asset("assets/src/bootstrap/css/bootstrap.min.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset("assets/css/light/plugins.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset("assets/css/dark/plugins.css") }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/css/app.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/css/topnav.css") }}?v={{ @filemtime(public_path('assets/css/topnav.css')) }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/css/theme.css") }}?v={{ @filemtime(public_path('assets/css/theme.css')) }}">
    <link href="{{ asset("assets/src/assets/css/light/elements/tooltip.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset("assets/src/assets/css/dark/elements/tooltip.css") }}" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->

    <!-- BEGIN PAGE LEVEL PLUGINS/CUSTOM STYLES -->
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/assets/css/light/elements/alert.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/assets/css/dark/elements/alert.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/flatpickr/flatpickr.css") }}">
    <!-- END PAGE LEVEL PLUGINS/CUSTOM STYLES -->

	@yield('additional_head')

</head>
<body class="layout-boxed" layout="full-width">

    <!-- BEGIN LOADER -->
    <div id="load_screen"> <div class="loader"> <div class="loader-content">
        <div class="spinner-grow align-self-center"></div>
    </div></div></div>
    <!--  END LOADER -->

    <!--  BEGIN NAVBAR  -->
    <div class="header-container custom-css-navbar">

        <!--  BEGIN HEADER BAR (branding + date/time + user)  -->
        <nav class="navigation-header">
            <a href="{{ url('') }}" class="nav-branding">
                <img src="{{ asset("assets/img/logo.png") }}" alt="logo" class="logo-image">
                <div class="nav-branding-text">
                    <span class="nav-branding-sub">Republic of the Philippines</span>
                    <span class="nav-branding-name">MUNICIPALITY OF PRIETO DIAZ</span>
                </div>
            </a>

            <div class="nav-profile">
                <div class="nav-user">
                    <div class="nav-user-info">
                        <span class="nav-user-name">{{ strtoupper(Auth::user()->username) }}</span>
                        <span class="nav-user-role">{{ ucwords(Auth::user()->account_type) }}</span>
                    </div>
                    <span class="nav-user-divider" aria-hidden="true"></span>
                    <a href="{{ url("/logout") }}" class="nav-logout-btn">Logout</a>
                </div>
            </div>
        </nav>
        <!--  END HEADER BAR  -->

        <!--  BEGIN NAVMENU (horizontal scrolling top nav; formerly the mis-named #sidebar)  -->
        <div class="nav-sticky-wrapper">
            <div style="display:flex; width:100%;">
                <button type="button" class="nav-scroll-btn nav-scroll-left" id="scrollLeft" aria-label="Scroll left">&#8249;</button>

                <nav class="navigation-bar" id="navigationBar">

                    <a href="{{ url('/dashboard') }}" class="{{ $menu=='Dashboard' ? 'active' : '' }}"><svg class="nav-ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>Home</a>

                    @if(Auth::user()->hasPermission('View Resolutions'))
                        <a href="{{ url('/resolutions/list') }}" class="{{ $menu=='Resolutions' ? 'active' : '' }}"><svg class="nav-ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>Resolutions</a>
                    @endif

                    @if(Auth::user()->hasPermission('View Ordinances'))
                        <a href="{{ url('/ordinances/list') }}" class="{{ $menu=='Ordinances' ? 'active' : '' }}"><svg class="nav-ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>Ordinances</a>
                    @endif

                    @if(Auth::user()->hasPermission('View Minutes'))
                        <a href="{{ url('/minutes/list') }}" class="{{ $menu=='Minutes' ? 'active' : '' }}"><svg class="nav-ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>Minutes</a>
                    @endif

                    @if(Auth::user()->hasPermission('View Communications'))
                        <div class="nav-dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle {{ $menu=='Communications' ? 'active' : '' }}" data-bs-toggle="dropdown" aria-expanded="false"><svg class="nav-ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>Communications</a>
                            <ul class="dropdown-menu tm-navmenu">
                                <li><a class="dropdown-item" href="{{ url('/communications/incoming_list') }}">Incoming</a></li>
                                <li><a class="dropdown-item" href="{{ url('/communications/outgoing_list') }}">Outgoing</a></li>
                            </ul>
                        </div>
                    @endif

                    @if(Auth::user()->hasPermission('View Activities'))
                        <a href="{{ url('/activities/all') }}" class="{{ in_array($menu, ['Activities - All','Activities - Upcoming','Activities - Ongoing','Activities - Completed']) ? 'active' : '' }}"><svg class="nav-ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>Sangguniang Activities</a>
                    @endif

                    @if(Auth::user()->hasPermission('Search Document'))
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#global-search" class="{{ $menu=='Search' ? 'active' : '' }}"><svg class="nav-ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>Search</a>
                    @endif

                    @if(Auth::user()->hasPermission('View Logs') || Auth::user()->hasPermission('View Users') || Auth::user()->hasPermission('Signatories') || Auth::user()->hasPermission('Set Access Control') || Auth::user()->hasPermission('Members'))
                        <div class="nav-dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle {{ $menu=='User Management' ? 'active' : '' }}" data-bs-toggle="dropdown" aria-expanded="false"><svg class="nav-ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>User Management</a>
                            <ul class="dropdown-menu tm-navmenu">
                                @if(Auth::user()->hasPermission('View Logs'))
                                    <li><a class="dropdown-item" href="{{ url('system/logs') }}">Logs</a></li>
                                @endif
                                @if(Auth::user()->hasPermission('View Users'))
                                    <li><a class="dropdown-item" href="{{ url('system/user_list') }}">Users &amp; Permissions</a></li>
                                @endif
                                @if(Auth::user()->hasPermission('Set Access Control'))
                                    <li><a class="dropdown-item" href="{{ url('system/access_control') }}">Access Control</a></li>
                                @endif
                                @if(Auth::user()->hasPermission('Signatories'))
                                    <li><a class="dropdown-item" href="{{ url('signatories/list') }}">Signatories</a></li>
                                @endif
                                @if(Auth::user()->hasPermission('Members'))
                                    <li><a class="dropdown-item" href="{{ url('members/list') }}">Members</a></li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    @if(Auth::user()->hasPermission('View Archives'))
                        <a href="{{ url('/archive/resolutions') }}" class="{{ in_array($menu, ['Archive - Resolutions','Archive - Ordinances','Archive - Minutes','Archive - Communications','Archive - Users','Archive - Sangguniang']) ? 'active' : '' }}"><svg class="nav-ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg>Archive</a>
                    @endif

                </nav>

                <button type="button" class="nav-scroll-btn nav-scroll-right" id="scrollRight" aria-label="Scroll right">&#8250;</button>
            </div>
        </div>
        <!--  END NAVMENU  -->

    </div>
    <!--  END NAVBAR  -->

    <!--  BEGIN MAIN CONTAINER  -->
    <div class="main-container mt-5" id="container">

        <div class="overlay"></div>
        <div class="search-overlay"></div>

        <!--  BEGIN CONTENT AREA  -->
        <div id="content" class="main-content">
           
            @yield('content')

            <div class="modal fade" id="global-search" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                  <div class="modal-content tm-search-modal">
                    <div class="modal-header">
                      <div>
                        <h5 class="modal-title" id="tabsModalLabel">Search Documents</h5>
                        <p class="tm-search-modal-sub">Find resolutions, ordinances, minutes and communications in one place.</p>
                      </div>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                        <form class="row g-3" action="{{ url('/global_search') }}" method="post" autocomplete="off" id="global-search-form">
                            @csrf

                            <div class="col-12">
                                <label class="tm-label">1. What are you looking for?</label>
                                <p class="tm-search-hint">Leave every box unticked to search all document types.</p>
                                <div class="tm-type-chips">
                                    <label class="tm-type-chip">
                                        <input type="checkbox" name="resolutions" value="1" id="form-check-resolutions" {{ session('search_resolution')=='1' ? 'checked' : '' }}>
                                        <span>Resolutions</span>
                                    </label>

                                    <label class="tm-type-chip">
                                        <input type="checkbox" name="ordinances" value="1" id="form-check-ordinances" {{ session('search_ordinance')=='1' ? 'checked' : '' }} onchange="toggle_ordinances()">
                                        <span>Ordinances</span>
                                    </label>

                                    <label class="tm-type-chip">
                                        <input type="checkbox" name="minutes" value="1" id="form-check-minutes" {{ session('search_minute')=='1' ? 'checked' : '' }}>
                                        <span>Minutes</span>
                                    </label>

                                    <label class="tm-type-chip">
                                        <input type="checkbox" name="communications" value="1" id="form-check-communications" {{ session('search_communication')=='1' ? 'checked' : '' }}>
                                        <span>Communications</span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-12" id="div_ordinance_type">
                                <label class="tm-label" id="lbl_ordinance_type">Ordinance Type <span class="tm-search-hint-inline">(only applies to Ordinances)</span></label>
                                <div class="tm-type-chips tm-type-chips-sm">
                                    @foreach(Auth::user()->getOrdinanceTypes() as $item)
                                        <label class="tm-type-chip tm-type-chip-sm">
                                            <input type="checkbox" name="ordinance_types[]" value="{{ $item->ordinance_type }}" id="form-check-type-{{ str_replace(' ', '-', $item->ordinance_type) }}" {{ in_array($item->ordinance_type, session('search_ordinance_types') ?? []) ? 'checked' : '' }}>
                                            <span>{{ $item->ordinance_type }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-12">
                                <hr class="tm-search-divider">
                                <label class="tm-label">2. Narrow it down <span class="tm-search-hint-inline">(optional)</span></label>
                            </div>

                            <div class="col-12 mb-2">
                                <label class="tm-label">Title</label>
                                <input type="text" class="tm-input" placeholder="e.g. Annual Budget" value="{{ session('search_title') }}" name="title" >
                            </div>

                            <div class="col-12 mb-2">
                                <label class="tm-label">Keywords</label>
                                <input type="text" class="tm-input" placeholder="e.g. flood control, ATM signage" value="{{ session('search_keyword') }}" name="keyword" >
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="tm-label">Author / Presiding Officer</label>
                                <input type="text" class="tm-input"  placeholder="e.g. Hon. Juan Dela Cruz" value="{{ session('search_author') }}" name="author" >
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="tm-label">Series Number</label>
                                <input type="text" class="tm-input" placeholder="e.g. 139" value="{{ session('search_series_number') }}" name="series_number" >
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="tm-label">Date Created — From</label>
                                <input type="text" class="tm-input tm-datepicker" id="search-start-date" placeholder="Select date..." name="start_date" value="{{ session('search_start_date') ?: '' }}" autocomplete="off">
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="tm-label">Date Created — To</label>
                                <input type="text" class="tm-input tm-datepicker" id="search-end-date" placeholder="Select date..." name="end_date" value="{{ session('search_end_date') ?: '' }}" autocomplete="off">
                            </div>

                            <div class="col-12 mt-2 tm-search-actions">
                                <button type="submit" formaction="{{ url('/global_search/clear') }}" formnovalidate class="tm-btn tm-btn-outline">Clear Filters</button>
                                <button type="submit" name="btnsave" value="1" class="tm-btn tm-btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    Search Documents
                                </button>
                            </div>

                        </form>
                    </div>

                  </div>
                </div>
              </div>

            @auth
            <div class="modal fade" id="idle-timeout-modal" tabindex="-1" aria-labelledby="idle-timeout-modal-label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="idle-timeout-modal-label">Still there?</h5>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">You've been idle for a while. For your security, you'll be logged out in <strong><span id="idle-timeout-countdown">60</span> seconds</strong> unless you stay logged in.</p>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ url('/logout') }}" class="tm-btn">Logout now</a>
                        <button type="button" id="idle-timeout-stay-btn" class="tm-btn tm-btn-primary">Stay logged in</button>
                    </div>
                  </div>
                </div>
            </div>
            @endauth

            <div class="footer-wrapper" style="font-family:'Manrope',sans-serif;color:var(--tm-muted,#7A7777);font-size:12px;border-top:1px solid rgba(51,51,51,.08);margin-top:8px">
                <div class="footer-section f-section-1">
                    <p class="mb-0">© <span class="dynamic-year">{{ date('Y') }}</span> <a target="_blank" href="#" style="color:var(--tm-primary,#427AB5);text-decoration:none;font-weight:600">Municipality of Prieto Diaz</a> — Document Tracker System. All rights reserved.</p>
                </div>
            </div>

            {{-- Quick-Create bar --}}
            @auth
            @php $qc = $quick_counts ?? []; @endphp
            <div class="tm-quickbar" id="tmQuickbar">
                <button type="button" class="tm-quickbar-toggle" id="tmQuickbarToggle" aria-label="Toggle quick create">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h7v8l10-12h-7z"></path></svg>
                    Quick Create
                    <svg class="tm-qb-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
                </button>
                <div class="tm-quickbar-links">
                    @if(Auth::user()->hasPermission('Add Resolution'))
                        <a href="{{ url('resolutions/add') }}" class="tm-qchip">＋ Resolution <span class="tm-qbadge {{ ($qc['res_pending'] ?? 0) > 0 ? 'warn' : '' }}">{{ $qc['res_pending'] ?? 0 }} pending</span></a>
                    @endif
                    @if(Auth::user()->hasPermission('Add Ordinance'))
                        <a href="{{ url('ordinances/add') }}" class="tm-qchip">＋ Ordinance <span class="tm-qbadge {{ ($qc['ord_pending'] ?? 0) > 0 ? 'warn' : '' }}">{{ $qc['ord_pending'] ?? 0 }} pending</span></a>
                    @endif
                    @if(Auth::user()->hasPermission('Add Minute'))
                        <a href="{{ url('minutes/add/1') }}" class="tm-qchip">＋ Minutes <span class="tm-qbadge">{{ $qc['min_month'] ?? 0 }} this month</span></a>
                    @endif
                    @if(Auth::user()->hasPermission('Add Communication'))
                        <a href="{{ url('communications/incoming_list') }}" class="tm-qchip">＋ Communication <span class="tm-qbadge">{{ $qc['com_month'] ?? 0 }} this month</span></a>
                    @endif
                    @if(Auth::user()->hasPermission('Add Activity'))
                        <a href="{{ url('activities/add') }}" class="tm-qchip">＋ Activity <span class="tm-qbadge">{{ $qc['act_upcoming'] ?? 0 }} upcoming</span></a>
                    @endif
                </div>
                <div class="tm-quickbar-clock" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 15 14"></polyline></svg>
                    <span id="live-date" class="tm-qb-date"></span>
                    <span class="tm-qb-sep">·</span>
                    <span id="live-time" class="tm-qb-time"></span>
                </div>
            </div>
            <script>
                (function () {
                    var bar = document.getElementById('tmQuickbar');
                    var btn = document.getElementById('tmQuickbarToggle');
                    if (!bar || !btn) return;
                    var KEY = 'tmQuickbarCollapsed';
                    if (localStorage.getItem(KEY) === '1') bar.classList.add('collapsed');
                    btn.addEventListener('click', function () {
                        bar.classList.toggle('collapsed');
                        localStorage.setItem(KEY, bar.classList.contains('collapsed') ? '1' : '0');
                    });
                })();
            </script>
            @endauth
            
        </div>
        <!--  END CONTENT AREA  -->

    </div>
    <!-- END MAIN CONTAINER -->

    <script src="{{ asset("assets/src/plugins/src/flatpickr/flatpickr.js") }}"></script>
    <script>
        // ── Search modal — customized calendar (replaces the native date picker) ──
        (function () {
            var startInput = document.getElementById('search-start-date');
            var endInput = document.getElementById('search-end-date');
            if (!startInput || !endInput || typeof flatpickr === 'undefined') return;

            var startPicker = flatpickr(startInput, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altInputClass: 'tm-input tm-datepicker',
                altFormat: 'M j, Y',
                allowInput: true,
                monthSelectorType: 'static',
                onChange: function (selectedDates) {
                    endPicker.set('minDate', selectedDates[0] || null);
                },
            });
            var endPicker = flatpickr(endInput, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altInputClass: 'tm-input tm-datepicker',
                altFormat: 'M j, Y',
                allowInput: true,
                monthSelectorType: 'static',
                onChange: function (selectedDates) {
                    startPicker.set('maxDate', selectedDates[0] || null);
                },
            });

            if (startInput.value) endPicker.set('minDate', startInput.value);
            if (endInput.value) startPicker.set('maxDate', endInput.value);
        })();

        // ── Live date/time (top nav header) ──────────────────────────────
        function updateDateTime() {
            const now = new Date();
            const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

            const dayName = days[now.getDay()];
            const monthName = months[now.getMonth()];
            const day = String(now.getDate()).padStart(2, '0');
            const year = now.getFullYear();

            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = String(hours % 12 || 12).padStart(2, '0');

            const dateEl = document.getElementById('live-date');
            const timeEl = document.getElementById('live-time');
            if (dateEl) dateEl.textContent = `${dayName}, ${monthName} ${day}, ${year}`;
            if (timeEl) timeEl.textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Keep the type-chip highlight in sync for browsers without :has() support.
        document.querySelectorAll('.tm-type-chip input[type="checkbox"]').forEach(function (cb) {
            var syncChip = function () { cb.closest('.tm-type-chip').classList.toggle('is-checked', cb.checked); };
            cb.addEventListener('change', syncChip);
            syncChip();
        });

        toggle_ordinances();

        function toggle_ordinances()
        {
            var ordinancesChecked = document.getElementById("form-check-ordinances").checked;
            var ordinanceTypeSection = document.getElementById("div_ordinance_type");

            ordinanceTypeSection.style.display = ordinancesChecked ? "block" : "none";

            // A checkbox hidden by CSS is still submitted with the form — clear any
            // leftover ordinance-type selection so it can't quietly filter out other
            // document types the next time someone searches.
            if (!ordinancesChecked) {
                ordinanceTypeSection.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
                    cb.checked = false;
                    cb.closest('.tm-type-chip').classList.remove('is-checked');
                });
            }
        }
    </script>

    <!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
	<script src="{{ asset("assets/src/plugins/src/global/vendors.min.js") }}"></script>
    <script src="{{ asset("assets/src/bootstrap/js/bootstrap.bundle.min.js") }}"></script>
    @auth
    <script src="{{ asset("assets/src/assets/js/idle-timeout.js") }}?v={{ @filemtime(public_path('assets/src/assets/js/idle-timeout.js')) }}"></script>
    @endauth
    <script src="{{ asset("assets/src/plugins/src/perfect-scrollbar/perfect-scrollbar.min.js") }}"></script>
    <script src="{{ asset("assets/src/plugins/src/mousetrap/mousetrap.min.js") }}"></script>
    <script src="{{ asset("assets/src/plugins/src/waves/waves.min.js") }}"></script>
    <script src="{{ asset("assets/app.js") }}"></script>
	<script src="{{ asset("assets/src/assets/js/custom.js") }}"></script>
    <script src="{{ asset("assets/js/tm-datatable.js") }}?v={{ @filemtime(public_path('assets/js/tm-datatable.js')) }}"></script>

    <!-- Top-nav: horizontal scroll buttons + dropdown positioning -->
    <script>
        (function () {
            const nav = document.getElementById('navigationBar');
            const btnLeft = document.getElementById('scrollLeft');
            const btnRight = document.getElementById('scrollRight');

            if (nav && btnLeft && btnRight) {
                function updateButtons() {
                    btnLeft.style.display = nav.scrollLeft > 0 ? 'block' : 'none';
                    btnRight.style.display = (nav.scrollLeft + nav.clientWidth) < (nav.scrollWidth - 1) ? 'block' : 'none';
                }
                btnLeft.addEventListener('click', () => nav.scrollBy({ left: -200, behavior: 'smooth' }));
                btnRight.addEventListener('click', () => nav.scrollBy({ left: 200, behavior: 'smooth' }));
                nav.addEventListener('scroll', updateButtons);
                window.addEventListener('resize', updateButtons);
                updateButtons();
            }

            // Render the menu dropdowns with a fixed strategy so the overflow-x
            // scroll container does not clip them.
            if (window.bootstrap && nav) {
                nav.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (el) {
                    bootstrap.Dropdown.getOrCreateInstance(el, {
                        popperConfig(defaultConfig) {
                            return Object.assign(defaultConfig, { strategy: 'fixed' });
                        }
                    });
                });
            }
        })();
    </script>
    <!-- END GLOBAL MANDATORY SCRIPTS -->

    <!-- BEGIN PAGE LEVEL PLUGINS/CUSTOM SCRIPTS -->
	@yield('additional_footer')
    <!-- BEGIN PAGE LEVEL PLUGINS/CUSTOM SCRIPTS -->
</body>
</html>