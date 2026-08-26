@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/assets/css/light/components/modal.css") }}" rel="stylesheet" type="text/css" />
<style>
    #zero-config .profile-img { width: 34px; height: 34px; border-radius: 50%; }
    #zero-config .media { display: flex; align-items: center; }
</style>
@endsection

@section("content")

<div class="layout-px-spacing">
    <div class="tm-page">

        {{-- Page header + toolbar --}}
        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">Users</h1>
                <div class="tm-crumb"><a href="{{ url('system/user_list') }}">User Management</a> / Users</div>
            </div>
            <div class="d-flex align-items-center" style="gap:10px; flex-wrap:wrap">
                @if(Auth::user()->hasPermission('Add User'))
                    <button type="button" class="tm-btn tm-btn-secondary" data-bs-toggle="modal" data-bs-target="#add-user">＋ Add User</button>
                @endif
            </div>
        </div>

        {{-- Table --}}
        <div class="tm-card">
            <table id="zero-config" class="tm-table" style="width:100%">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Account Type</th>
                        <th>Status</th>
                        <th>Date Added</th>
                        <th class="no-content">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $item)
                        <tr data-id="{{ $item->id }}">
                            <td>
                                <div class="media">
                                    <img src="{{ asset('assets/src/assets/img/profile-30.png') }}" class="profile-img me-2" alt="avatar">
                                    <div class="media-body">
                                        <span style="font-weight:600">{{ smart_title($item->fullname) }}</span><br/>
                                        <span class="tm-muted" style="font-size:11px">Username: {{ $item->username }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item->email }}</td>
                            <td><span class="tm-badge {{ $item->account_type=='ADMINISTRATOR' ? 'tm-badge-info' : 'tm-badge-warn' }}">{{ $item->account_type }}</span></td>
                            <td><span class="tm-badge {{ $item->status=='ACTIVE' ? 'tm-badge-ok' : 'tm-badge-bad' }}">{{ $item->status }}</span></td>
                            <td>{{ date("M d, Y h:iA", strtotime($item->created_at)) }}</td>
                            <td class="tm-actions-cell">
                                <ul class="tm-actions">
                                    @if(Auth::user()->hasPermission('Edit User'))
                                        <li><a href="javascript:void(0);" onclick="view_details('{{ $item->id }}')" data-bs-toggle="modal" data-bs-target="#edit-user" class="bs-tooltip text-success" data-bs-placement="top" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 p-1 br-8 mb-1 text-success"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></a></li>
                                    @endif
                                    @if(Auth::user()->hasPermission('Reset Password'))
                                        <li><a href="javascript:void(0);" onclick="document.getElementById('reset_password_user_id').value='{{ $item->id }}'" data-bs-toggle="modal" data-bs-target="#reset-password" class="bs-tooltip text-info" data-bs-placement="top" title="Reset Password"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-key p-1 br-8 mb-1 text-info"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg></a></li>
                                    @endif
                                    @if(Auth::user()->hasPermission('Archive User'))
                                        <li><a href="javascript:void(0);" onclick='return confirm_archive("{{ url("system/move_to_archive/".$item->id) }}")' class="bs-tooltip text-warning" data-bs-placement="top" title="Archive"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-archive p-1 br-8 mb-1 text-warning"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg></a></li>
                                    @endif
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
    @if(Auth::user()->hasPermission('Edit User'))
        <a data-act="edit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg> Edit</a>
    @endif
    @if(Auth::user()->hasPermission('Reset Password'))
        <a data-act="reset"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg> Reset Password</a>
    @endif
    @if(Auth::user()->hasPermission('Archive User'))
        <div class="tm-row-menu-divider"></div>
        <a data-act="archive"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg> Archive</a>
    @endif
</div>

<div class="modal fade" id="add-user" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tabsModalLabel">Add User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
                    <form class="row g-3" action="{{ url('/system/save_add_user/') }}" method="post" autocomplete="off" onsubmit="return validatePasswords()">
                        @csrf
                        <div class="col-12"><label class="tm-label">Username <span class="text-danger">*</span></label><input type="text" class="tm-input" required name="username" ></div>
                        <div class="col-12"><label class="tm-label">Full Name <span class="text-danger">*</span></label><input type="text" class="tm-input" required name="fullname" ></div>
                        <div class="col-12"><label class="tm-label">Email <span class="text-danger">*</span></label><input type="text" class="tm-input" required name="email" ></div>
                        <div class="col-12"><label class="tm-label">Password <span class="text-danger">*</span></label><input type="password" class="tm-input" required name="password" id="add_password" ></div>
                        <div class="col-12"><label class="tm-label">Retype Password <span class="text-danger">*</span></label><input type="password" class="tm-input" required id="add_retype_password" ></div>
                        <div class="col-12"><label class="tm-label">Account Type <span class="text-danger">*</span></label><select class="tm-select" required name="account_type"><option></option><option>ADMINISTRATOR</option><option>SECRETARY</option></select></div>
                        <div class="col-12 mb-4"><label class="tm-label">Status <span class="text-danger">*</span></label><select class="tm-select" required name="status"><option></option><option>ACTIVE</option><option>INACTIVE</option></select></div>
                        <div class="d-grid gap-2 col-12 mx-auto"><button type="submit" name="btnsave" value="1" class="tm-btn tm-btn-primary tm-btn-block">Save</button></div>
                    </form>
          </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="edit-user" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tabsModalLabel">Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
                    <form class="row g-3" action="{{ url('/system/save_changes_user/') }}" method="post" autocomplete="off">
                        @csrf
                        <input type="hidden" name="user_id" id="edit_user_id"/>
                        <div class="col-12"><label class="tm-label">Username <span class="text-danger">*</span></label><input type="text" class="tm-input" id="edit_username" required name="username" ></div>
                        <div class="col-12"><label class="tm-label">Full Name <span class="text-danger">*</span></label><input type="text" class="tm-input" id="edit_fullname" required name="fullname" ></div>
                        <div class="col-12"><label class="tm-label">Email <span class="text-danger">*</span></label><input type="text" class="tm-input" id="edit_email" required name="email" ></div>
                        <div class="col-12"><label class="tm-label">Account Type <span class="text-danger">*</span></label><select class="tm-select" id="edit_account_type" required name="account_type"><option></option><option>ADMINISTRATOR</option><option>SECRETARY</option></select></div>
                        <div class="col-12 mb-4"><label class="tm-label">Status <span class="text-danger">*</span></label><select class="tm-select" id="edit_status" required name="status"><option></option><option>ACTIVE</option><option>INACTIVE</option></select></div>
                        <div class="d-grid gap-2 col-12 mx-auto"><button type="submit" name="btnsave" value="1" class="tm-btn tm-btn-primary tm-btn-block">Save</button></div>
                    </form>
          </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="reset-password" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tabsModalLabel">Reset Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
                    <form class="row g-3" action="{{ url('/system/reset_password/') }}" method="post" autocomplete="off" onsubmit="return validatePasswordsReset()">
                        @csrf
                        <input type="hidden" name="user_id" id="reset_password_user_id" />
                        <div class="col-12 mb-4"><label class="tm-label">Password <span class="text-danger">*</span></label><input type="password" class="tm-input" required name="password" id="reset_password" ></div>
                        <div class="col-12 mb-4"><label class="tm-label">Retype Password <span class="text-danger">*</span></label><input type="password" class="tm-input" required id="reset_retype_password" ></div>
                        <div class="d-grid gap-2 col-12 mx-auto"><button type="submit" name="btnsave" value="1" class="tm-btn tm-btn-primary tm-btn-block">Save</button></div>
                    </form>
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
        searchPlaceholder: 'Search users...',
        order: [[4, 'desc']],
        columnDefs: [{ orderable: false, targets: [5] }]
    });

    // Row click -> floating action menu
    (function () {
        var menu = document.getElementById('tm-row-menu');
        if (!menu) return;
        var curId = null;
        var archiveBase = "{{ url('system/move_to_archive') }}";

        function hideMenu() { menu.style.display = 'none'; }

        $('#zero-config tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('a, .tm-actions, button, input').length) return;
            curId = $(this).data('id');
            if (!curId) return;
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
            if (act === 'edit')  { view_details(curId); bootstrap.Modal.getOrCreateInstance(document.getElementById('edit-user')).show(); }
            else if (act === 'reset') { document.getElementById('reset_password_user_id').value = curId; bootstrap.Modal.getOrCreateInstance(document.getElementById('reset-password')).show(); }
            else if (act === 'archive') confirm_archive(archiveBase + '/' + curId);
        });

        document.addEventListener('click', function (e) { if (!menu.contains(e.target)) hideMenu(); });
        document.addEventListener('scroll', hideMenu, true);
        window.addEventListener('resize', hideMenu);
    })();

    function confirm_archive($url) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action will move the record to the archive list.",
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, archive it!'
        }).then((result) => { if (result.isConfirmed) { window.location.href = $url; } });
    }

    function view_details(param) {
        $.ajax({
            type: 'POST', data: { user_id: param }, dataType: "json",
            url: '{{ url("system/get_user_info") }}',
            success: function (data) {
                document.getElementById('edit_user_id').value = data['info']['id'];
                document.getElementById('edit_username').value = data['info']['username'];
                document.getElementById('edit_fullname').value = data['info']['fullname'];
                document.getElementById('edit_email').value = data['info']['email'];
                document.getElementById('edit_account_type').value = data['info']['account_type'];
                document.getElementById('edit_status').value = data['info']['status'];
            },
            error: function (XHR, textStatus, errorThrown) { console.log(errorThrown); console.log(XHR.responseText); }
        });
    }

    function validatePasswords() {
        const password = document.getElementById('add_password').value;
        const retype = document.getElementById('add_retype_password').value;
        if (password !== retype) { Swal.fire('Ooops!', 'Passwords do not match.', 'error'); return false; }
        return true;
    }

    function validatePasswordsReset() {
        const password = document.getElementById('reset_password').value;
        const retype = document.getElementById('reset_retype_password').value;
        if (password !== retype) { Swal.fire('Ooops!', 'Passwords do not match.', 'error'); return false; }
        return true;
    }
</script>
@endsection
