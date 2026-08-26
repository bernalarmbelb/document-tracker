@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
@endsection

@section("content")
<div class="layout-px-spacing">
    <div class="tm-page">
        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">Members</h1>
                <div class="tm-crumb">User Management / Members <span class="tm-muted">— attendance roster</span></div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-5">
                <div class="tm-card">
                    <h3 id="frmtitle">Add Member</h3>
                    <form class="row g-3" method="post" id="frmmember"
                          action="{{ url('/members/save_add') }}">
                        @csrf
                        <input type="hidden" name="member_id" id="member_id" value="">
                        <div class="col-12">
                            <label class="tm-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="tm-input" name="name" id="name" required>
                        </div>
                        <div class="col-12">
                            <label class="tm-label">Position</label>
                            <input type="text" class="tm-input" name="position" id="position" placeholder="e.g. Legislative Staff">
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="tm-btn tm-btn-primary tm-btn-block" id="btnsubmit">Add Member</button>
                            <button type="button" class="tm-btn tm-btn-outline" id="btncancel" style="display:none">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="tm-card">
                    <div style="margin-bottom:12px">
                        <input type="text" id="memberSearch" class="tm-input" placeholder="Search by name or position..." autocomplete="off">
                    </div>
                    <div class="tm-table-wrap">
                        <table class="tm-table" id="memberTable" style="width:100%">
                            <thead>
                                <tr><th>Name</th><th>Position</th><th style="text-align:center">Status</th><th style="text-align:center">Actions</th></tr>
                            </thead>
                            <tbody>
                                @forelse($records as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->position }}</td>
                                        <td class="text-center">
                                            @if($item->is_deleted)
                                                <span class="tm-badge">Hidden</span>
                                            @else
                                                <span class="tm-badge tm-badge-info">Active</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="#" class="me-2 tm-edit"
                                               data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-position="{{ $item->position }}">Edit</a>
                                            <a href="{{ url('members/toggle_visibility/'.$item->id) }}"
                                               onclick="return confirm('Toggle visibility for this member?')">
                                               {{ $item->is_deleted ? 'Unhide' : 'Hide' }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="tm-table-empty">No members yet. Add office / LGU staff on the left.</td></tr>
                                @endforelse
                                <tr id="memberNoMatch" style="display:none"><td colspan="4" class="tm-table-empty">No members match your search.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>
<script src="{{ asset("js/app.js") }}"></script>
<script>
    window.onload = function () {
        const messages = @json(session('messages') ?? []);
        (async () => {
            for (const m of messages) {
                await Swal.fire({ title: m['type'].toUpperCase(), text: m['text'], icon: m['type'], confirmButtonText: 'OK' });
            }
        })();
    };

    document.querySelectorAll('.tm-edit').forEach(a => a.addEventListener('click', function (e) {
        e.preventDefault();
        document.getElementById('member_id').value = this.dataset.id;
        document.getElementById('name').value = this.dataset.name;
        document.getElementById('position').value = this.dataset.position;
        document.getElementById('frmmember').setAttribute('action', '{{ url("/members/save_changes") }}');
        document.getElementById('frmtitle').textContent = 'Edit Member';
        document.getElementById('btnsubmit').textContent = 'Save Changes';
        document.getElementById('btncancel').style.display = 'inline-block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }));

    document.getElementById('btncancel').addEventListener('click', function () {
        document.getElementById('frmmember').reset();
        document.getElementById('member_id').value = '';
        document.getElementById('frmmember').setAttribute('action', '{{ url("/members/save_add") }}');
        document.getElementById('frmtitle').textContent = 'Add Member';
        document.getElementById('btnsubmit').textContent = 'Add Member';
        this.style.display = 'none';
    });

    // Live search: filter member rows by name or position
    (function () {
        const search = document.getElementById('memberSearch');
        if (!search) return;
        const noMatch = document.getElementById('memberNoMatch');
        const dataRows = Array.from(document.querySelectorAll('#memberTable tbody tr'))
            .filter(r => r.id !== 'memberNoMatch' && !r.querySelector('.tm-table-empty'));

        search.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            let visible = 0;
            dataRows.forEach(r => {
                const text = (r.children[0].textContent + ' ' + r.children[1].textContent).toLowerCase();
                const show = q === '' || text.includes(q);
                r.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            noMatch.style.display = (dataRows.length && visible === 0) ? '' : 'none';
        });
    })();
</script>
@endsection
