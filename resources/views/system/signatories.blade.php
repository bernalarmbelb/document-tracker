@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/assets/css/light/components/modal.css") }}" rel="stylesheet" type="text/css" />
@endsection

@section("content")

<div class="layout-px-spacing">
    <div class="tm-page">

        {{-- Page header + toolbar --}}
        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">Signatories</h1>
                <div class="tm-crumb"><a href="{{ url('signatories/list') }}">Signatories</a> / List</div>
            </div>
            <div class="d-flex align-items-center" style="gap:10px; flex-wrap:wrap">
                <button type="button" class="tm-btn tm-btn-secondary" data-bs-toggle="modal" data-bs-target="#add-signatory">＋ Add Signatory</button>
            </div>
        </div>

        {{-- Table --}}
        <div class="tm-card">
            <table id="zero-config" class="tm-table" style="width:100%">
                <thead>
                    <tr>
                        <th>Signatory</th>
                        <th>Position</th>
                        <th>E-Signature</th>
                        <th>Visible?</th>
                        <th class="no-content">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $item)
                        <tr data-name="{{ $item->signatory_name }}" data-position="{{ $item->position }}">
                            <td style="font-weight:600">{{ smart_title($item->signatory_name) }}</td>
                            <td>{{ $item->position }}</td>
                            <td>
                                @if($item->esignature<>"")
                                    <a href="javascript:void(0);" onclick='view_esignature(@json(upload_url("uploads_signatures", $item->esignature)))' style="color:var(--tm-primary);text-decoration:none">View</a>
                                @else
                                    <span class="tm-muted">None</span>
                                @endif
                            </td>
                            <td><span class="tm-badge {{ $item->is_deleted=='0' ? 'tm-badge-info' : 'tm-badge-bad' }}">{{ $item->is_deleted=='0' ? 'YES' : 'NO' }}</span></td>
                            <td class="tm-actions-cell">
                                <ul class="tm-actions">
                                    <li><a href="javascript:void(0);" onclick='view_details(@json($item->signatory_name),@json($item->position))' data-bs-toggle="modal" data-bs-target="#edit-signatory" class="bs-tooltip text-success" data-bs-placement="top" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 p-1 br-8 mb-1 text-success"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></a></li>
                                    <li><a href="javascript:void(0);" onclick="document.getElementById('upload_signatory_id').value='{{ $item->signatory_name}}'" class="bs-tooltip text-info" data-bs-toggle="modal" data-bs-target="#add-e-signature" data-bs-placement="top" title="Upload E-Signature"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-share p-1 br-8 mb-1 text-info"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg></a></li>
                                    <li><a href="javascript:void(0);" onclick='return confirm_toggle("{{ url("signatories/toggle_visibility/".urlencode($item->signatory_name)) }}")' class="bs-tooltip text-warning" data-bs-placement="top" title="Toggle Visibility"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye p-1 br-8 mb-1 text-primary"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a></li>
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- Row click context menu --}}
<div id="tm-row-menu" class="tm-row-menu">
    <a data-act="edit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg> Edit</a>
    <a data-act="esign"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg> Upload E-Signature</a>
    <div class="tm-row-menu-divider"></div>
    <a data-act="toggle"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg> Toggle Visibility</a>
</div>

<div class="modal fade" id="add-signatory" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tabsModalLabel">Add Signatory</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="simple-pill">
                    <form class="row g-3" action="{{ url('/signatories/save_add_signatory/') }}" method="post" autocomplete="off">
                        @csrf
                        <div class="col-12"><label class="tm-label">Signatory <span class="text-danger">*</span></label><input type="text" class="tm-input" required name="signatory_name" ></div>
                        <div class="col-12"><label class="tm-label">Position <span class="text-danger">*</span></label>
                            <input type="text" list="legislative-positions" class="tm-input" required name="position" value="Sangguniang Bayan Member">
                            <datalist id="legislative-positions">
                                <option value="Municipal Mayor"><option value="Municipal Vice Mayor"><option value="Municipal Councilor">
                                <option value="Sangguniang Bayan Secretary"><option value="Sangguniang Bayan Member">
                                <option value="SK Federation President"><option value="Barangay President">
                            </datalist>
                        </div>
                        <div class="d-grid gap-2 col-12 mt-4 mx-auto"><button type="submit" name="btnsave" value="1" class="tm-btn tm-btn-primary tm-btn-block">Save</button></div>
                    </form>
          </div>
          </div>
      </div>
    </div>
</div>

<div class="modal fade" id="edit-signatory" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tabsModalLabel">Edit Signatory</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="simple-pill">
                    <form class="row g-3" action="{{ url('/signatories/save_changes_signatory/') }}" method="post" autocomplete="off">
                        @csrf
                        <input type="hidden" name="signatory_id" id="signatory_id" >
                        <div class="col-12"><label class="tm-label">Signatory <span class="text-danger">*</span></label><input type="text" class="tm-input" required name="signatory_name" id="signatory_name" ></div>
                        <div class="col-12"><label class="tm-label">Position <span class="text-danger">*</span></label><input type="text" list="legislative-positions" class="tm-input" required name="position" id="position"></div>
                        <div class="d-grid gap-2 col-12 mt-4 mx-auto"><button type="submit" name="btnsave" value="1" class="tm-btn tm-btn-primary tm-btn-block">Save Changes</button></div>
                    </form>
          </div>
          </div>
      </div>
    </div>
  </div>

    <div class="modal fade" id="add-e-signature" tabindex="-1" role="dialog" aria-labelledby="esigUploadLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="esigUploadLabel">Upload E-Signature</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="tm-esig-target" id="esig-upload-target"></p>
                    <form action="{{ url('/signatories/upload_esignature/') }}" method="post" autocomplete="off" enctype="multipart/form-data" id="esig-upload-form">
                        @csrf
                        <input type="hidden" id="upload_signatory_id" name="signatory_id" value="">
                        <label class="tm-dropzone" id="esig-dropzone">
                            <input type="file" name="myfile" accept="image/png,image/jpeg,image/webp" class="tm-dropzone-input" onchange="esigOnPick(this)">
                            <span class="tm-dropzone-ico" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            </span>
                            <span class="tm-dropzone-title" id="esig-dropzone-title">Click to choose an image</span>
                            <span class="tm-dropzone-hint">PNG, JPG or WEBP — a transparent PNG works best</span>
                        </label>
                        <div class="tm-esig-uploading" id="esig-uploading">
                            <span class="tm-spinner" aria-hidden="true"></span> Uploading…
                        </div>
                    </form>
                    <ul id="my_files" class="list-group mt-2"></ul>
                </div>
            </div>
        </div>
    </div>

    {{-- E-Signature preview (in-modal, download/right-click deterred) --}}
    <div class="modal fade" id="view-esignature" tabindex="-1" role="dialog" aria-labelledby="esigModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="esigModalLabel">E-Signature</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body" style="text-align:center;background:#f7f8fa">
                    <div id="esig-wrap" style="display:inline-block;user-select:none;">
                        <img id="esig-img" src="" alt="E-Signature" draggable="false"
                             style="max-width:100%;max-height:60vh;pointer-events:none;-webkit-user-drag:none;user-select:none;">
                    </div>
                    <p class="tm-muted" style="font-size:11px;margin:10px 0 0">Preview only.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/table/datatable/datatables.js") }}"></script>
<script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>
<script src="{{ asset("js/app.js") }}"></script>

<script>
    window.onload = function() {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        const messages = @json(session('messages'));
        const showMessages = async () => {
            for (const message of messages) {
                await Swal.fire({ title: message['type'].toUpperCase(), text: message['text'], icon: message['type'], confirmButtonText: 'OK' });
            }
        };
        showMessages();
    };

    tmInitDataTable('#zero-config', {
        pageLength: 10,
        searchPlaceholder: 'Search signatories...',
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: [4] }]
    });

    // Row click -> floating action menu
    (function () {
        var menu = document.getElementById('tm-row-menu');
        if (!menu) return;
        var curName = null, curPos = '';
        var toggleBase = "{{ url('signatories/toggle_visibility') }}";

        function hideMenu() { menu.style.display = 'none'; }

        $('#zero-config tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('a, .tm-actions, button, input').length) return;
            curName = $(this).data('name');
            curPos = $(this).data('position');
            if (!curName) return;
            menu.style.display = 'block';
            var mw = menu.offsetWidth, mh = menu.offsetHeight;
            var x = Math.min(e.clientX, window.innerWidth - mw - 12);
            var y = Math.min(e.clientY, window.innerHeight - mh - 12);
            menu.style.left = Math.max(x, 8) + 'px';
            menu.style.top  = Math.max(y, 8) + 'px';
            e.stopPropagation();
        });

        $(menu).on('click', '[data-act]', function () {
            var act = this.getAttribute('data-act');
            hideMenu();
            if (act === 'edit') { view_details(curName, curPos); bootstrap.Modal.getOrCreateInstance(document.getElementById('edit-signatory')).show(); }
            else if (act === 'esign') { document.getElementById('upload_signatory_id').value = curName; bootstrap.Modal.getOrCreateInstance(document.getElementById('add-e-signature')).show(); }
            else if (act === 'toggle') confirm_toggle(toggleBase + '/' + encodeURIComponent(curName));
        });

        document.addEventListener('click', function (e) { if (!menu.contains(e.target)) hideMenu(); });
        document.addEventListener('scroll', hideMenu, true);
        window.addEventListener('resize', hideMenu);
    })();

    function confirm_toggle($url) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will toggle the visibility of the selected signatory.",
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, hide/unhide it!'
        }).then((result) => { if (result.isConfirmed) { window.location.href = $url; } });
    }

    function view_details($signatory_name, $position) {
        document.getElementById("signatory_name").value = $signatory_name;
        document.getElementById("signatory_id").value = $signatory_name;
        document.getElementById("position").value = $position;
    }

    // E-signature upload: reflect the selected file, show a spinner, then submit
    function esigOnPick(input) {
        if (!input.files || !input.files.length) return;
        var t = document.getElementById('esig-dropzone-title');
        if (t) t.textContent = input.files[0].name;
        var dz = document.getElementById('esig-dropzone');
        if (dz) dz.classList.add('is-picked');
        var up = document.getElementById('esig-uploading');
        if (up) up.classList.add('show');
        input.form.submit();
    }
    // When the upload modal opens, show whose signature we're uploading & reset state
    (function () {
        var m = document.getElementById('add-e-signature');
        if (!m) return;
        m.addEventListener('show.bs.modal', function () {
            var name = document.getElementById('upload_signatory_id').value || '';
            var tgt = document.getElementById('esig-upload-target');
            if (tgt) tgt.textContent = name ? name : '';
            var t = document.getElementById('esig-dropzone-title');
            if (t) t.textContent = 'Click to choose an image';
            var dz = document.getElementById('esig-dropzone');
            if (dz) dz.classList.remove('is-picked');
            var up = document.getElementById('esig-uploading');
            if (up) up.classList.remove('show');
            var f = document.getElementById('esig-upload-form');
            if (f) { var fi = f.querySelector('input[type=file]'); if (fi) fi.value = ''; }
        });
    })();

    function view_esignature(url) {
        document.getElementById('esig-img').src = url;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('view-esignature')).show();
    }
    // Extra deterrent: block right-click / drag anywhere inside the preview
    document.addEventListener('DOMContentLoaded', function () {
        var wrap = document.getElementById('esig-wrap');
        if (wrap) wrap.addEventListener('contextmenu', function (e) { e.preventDefault(); });
    });
</script>
@endsection
