# Minutes Session Attendance & Reports — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add structured, per-member session attendance (Present/Absent/Excused/Late) to the Minutes module, plus a per-session attendance sheet in the PDF and a cross-session attendance report.

**Architecture:** Two new MySQL tables (`members`, `minutes_attendance`) added via Laravel migrations. A new admin-gated **Members** CRUD mirrors the existing Signatories page. The Minutes add/edit form's free-text attendance textarea is replaced by a roster checklist (from `members`) with a P/A/E/L control per person plus a guests textarea (kept in the existing `minutes.attendance` column). Report counting logic is a pure PHP class (unit-tested, no DB) consumed by both the report page and its CSV export.

**Tech Stack:** PHP 8 / Laravel 10-11, Blade views, Eloquent, mPDF (already used), MySQL (via Laravel Herd), Bootstrap + custom `tm-` theme classes, jQuery/SweetAlert2 (already loaded).

**Spec:** `docs/superpowers/specs/2026-08-27-minutes-session-attendance-design.md`

## Global Constraints

- **No test-DB harness exists.** The domain schema is shipped as SQL dumps in `database/` (not migrations), so an in-memory sqlite `RefreshDatabase` cannot build it. Do **not** add HTTP/feature tests that boot the DB. The only automated test in this plan is a **pure PHPUnit unit test** (no framework boot, no DB) for the report-counting class. All other verification is **manual in the running Herd app** + direct DB inspection, with exact expected results given per task.
- **Follow existing patterns verbatim.** Controllers are admin-gated in `__construct` by `Auth::user()->account_type` being `"ADMINISTRATOR"` or `"SUPER ADMIN"` (see `SignatoriesController`). Flash messages use the `$this->messages[]` + `session()->flash('messages', ...)` pattern. Views extend `template` and use `tm-` classes. Keep this style; do not refactor unrelated code.
- **Status codes are exactly** `P`, `A`, `E`, `L` (Present, Absent, Excused, Late), stored one char in `minutes_attendance.status`.
- **Present rate** = `round(Present / sessionCount * 100)` (integer percent), `0` when `sessionCount == 0`. Excused/Absent/Late never fold into Present.
- **Guests** stay in the existing `minutes.attendance` text column; they are NOT roster members and never appear in report counts.
- **Members list starts empty.** No seeding from old free-text attendance.
- **App URL:** the Herd site root (e.g. `https://document-tracker.test`). Log in as an ADMINISTRATOR / SUPER ADMIN account for every manual check.
- **Run commands from repo root** `C:/Users/Administrator/Herd/document-tracker` in PowerShell.
- **Branch:** work on `redesign/treasury-theme` (current branch). Commit after each task.

---

## File Structure

**New files**
- `database/migrations/2026_08_27_000000_create_members_table.php` — `members` table.
- `database/migrations/2026_08_27_000001_create_minutes_attendance_table.php` — `minutes_attendance` table.
- `app/Models/Member.php` — Member model.
- `app/Models/MinutesAttendance.php` — attendance row model.
- `app/Http/Controllers/MembersController.php` — Members CRUD (mirrors SignatoriesController).
- `resources/views/system/members.blade.php` — Members management screen.
- `app/Support/AttendanceReport.php` — pure aggregation/counting logic.
- `tests/Unit/AttendanceReportTest.php` — unit test for the above.
- `resources/views/minutes/_attendance_sheet.blade.php` — attendance sheet partial for the PDF.
- `resources/views/minutes/report.blade.php` — cross-session report page.

**Modified files**
- `app/Models/Minutes.php` — add `attendees()` relationship.
- `app/Http/Controllers/MinutesController.php` — `add`, `edit`, `save_minute` (+ new `save_attendance`), `generate_pdf`, new `attendance_report` (+ `export_report_csv`).
- `resources/views/minutes/add.blade.php` — attendance panel replaces the textarea.
- `resources/views/minutes/list.blade.php` — "Attendance Report" button.
- `routes/web.php` — members routes, report route, `use` import.
- `resources/views/template.blade.php` — Members nav link in the System dropdown.
- `resources/views/system/access_control.blade.php` — "Members" permission checkbox.

---

## Task 1: New tables and models

**Files:**
- Create: `database/migrations/2026_08_27_000000_create_members_table.php`
- Create: `database/migrations/2026_08_27_000001_create_minutes_attendance_table.php`
- Create: `app/Models/Member.php`
- Create: `app/Models/MinutesAttendance.php`
- Modify: `app/Models/Minutes.php`

**Interfaces:**
- Produces:
  - Table `members(id, name VARCHAR(255), position VARCHAR(255) NULL, is_deleted TINYINT default 0, timestamps)`.
  - Table `minutes_attendance(id, minute_id BIGINT index, member_id BIGINT index, status VARCHAR(1), timestamps)`.
  - `App\Models\Member` (fillable: `name`, `position`, `is_deleted`).
  - `App\Models\MinutesAttendance` (fillable: `minute_id`, `member_id`, `status`).
  - `App\Models\Minutes::attendees()` → `hasMany(MinutesAttendance::class, 'minute_id')`.

- [ ] **Step 1: Write the `members` migration**

Create `database/migrations/2026_08_27_000000_create_members_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position')->nullable();
            $table->tinyInteger('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
```

- [ ] **Step 2: Write the `minutes_attendance` migration**

Create `database/migrations/2026_08_27_000001_create_minutes_attendance_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('minutes_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('minute_id')->index();
            $table->unsignedBigInteger('member_id')->index();
            $table->string('status', 1); // P, A, E, L
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minutes_attendance');
    }
};
```

- [ ] **Step 3: Create the models**

Create `app/Models/Member.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $table = 'members';

    protected $fillable = [
        'name',
        'position',
        'is_deleted',
    ];
}
```

Create `app/Models/MinutesAttendance.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinutesAttendance extends Model
{
    use HasFactory;
    protected $table = 'minutes_attendance';

    protected $fillable = [
        'minute_id',
        'member_id',
        'status',
    ];
}
```

- [ ] **Step 4: Add the relationship to `Minutes`**

In `app/Models/Minutes.php`, add this method inside the class (after the `$fillable` array):

```php
    public function attendees()
    {
        return $this->hasMany(MinutesAttendance::class, 'minute_id');
    }
```

- [ ] **Step 5: Run the migrations**

Run: `php artisan migrate`
Expected: two migrations run OK: `2026_08_27_000000_create_members_table` and `2026_08_27_000001_create_minutes_attendance_table`, both `DONE`.

- [ ] **Step 6: Verify tables + models via tinker**

Run: `php artisan tinker`
Then paste:

```php
App\Models\Member::create(['name'=>'Test Member','position'=>'Clerk']);
App\Models\Member::count(); // expect 1
App\Models\MinutesAttendance::create(['minute_id'=>1,'member_id'=>1,'status'=>'P']);
App\Models\MinutesAttendance::first()->status; // expect "P"
App\Models\Member::where('name','Test Member')->delete();
App\Models\MinutesAttendance::truncate();
exit
```
Expected: `Member::count()` returns `1`, `status` is `"P"`, no errors. (The two test rows are cleaned up by the last two lines.)

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_08_27_000000_create_members_table.php database/migrations/2026_08_27_000001_create_minutes_attendance_table.php app/Models/Member.php app/Models/MinutesAttendance.php app/Models/Minutes.php
git commit -m "feat(minutes): add members and minutes_attendance tables + models"
```

---

## Task 2: Members management page

**Files:**
- Create: `app/Http/Controllers/MembersController.php`
- Create: `resources/views/system/members.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/template.blade.php`
- Modify: `resources/views/system/access_control.blade.php`

**Interfaces:**
- Consumes: `App\Models\Member` (Task 1).
- Produces: routes `members.list`, `members.save_add`, `members.save_changes`, `members.toggle_visibility`; a working admin page at `/members/list`.

- [ ] **Step 1: Create the controller**

Create `app/Http/Controllers/MembersController.php` (mirrors `SignatoriesController`, but keyed by `id`):

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Member;

class MembersController extends Controller
{
    public $edit = FALSE;
    public $messages = array();

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::user()->account_type != "ADMINISTRATOR" && Auth::user()->account_type != "SUPER ADMIN")
                return redirect('/');
            else return $next($request);
        });
    }

    public function list()
    {
        $data = array(
            'menu' => 'Members',
            'records' => Member::orderBy('name')->get(),
        );
        return view("system.members", $data);
    }

    public function toggle_visibility($recordid)
    {
        $info = Member::where('id', $recordid)->first();
        Member::where('id', $recordid)->update([
            'is_deleted' => ($info->is_deleted == 1 ? 0 : 1),
        ]);

        $this->messages[] = array('type' => 'success', 'text' => 'Successfully toggled visibility.');
        session()->flash('messages', $this->messages);
        return redirect()->route('members.list');
    }

    public function save_add(Request $request)
    {
        $this->edit = FALSE;
        return $this->_save_changes($request->all());
    }

    public function save_changes(Request $request)
    {
        $this->edit = TRUE;
        return $this->_save_changes($request->all());
    }

    public function _save_changes($data)
    {
        $the_id = $this->edit ? $data["member_id"] : 0;

        if (!$this->save_member($the_id, $data)) {
            $this->messages[] = array('type' => 'danger', 'text' => 'Error ' . ($this->edit ? 'updating' : 'adding') . ' member.');
            session()->flash('messages', $this->messages);
            return redirect()->route('members.list');
        }

        $this->messages[] = array('type' => 'success', 'text' => 'Member ' . ($this->edit ? 'updated' : 'added') . ' successfully.');
        session()->flash('messages', $this->messages);
        return redirect()->route('members.list');
    }

    public function save_member(&$the_id, $data)
    {
        if ($this->edit) {
            $row = Member::where('id', $the_id)->update([
                'name' => $data['name'],
                'position' => $data['position'],
            ]);
            log_activity('Update Member', json_encode(['id' => $the_id, 'name' => $data['name']]));
            return $row;
        } else {
            $row = Member::create([
                'name' => $data['name'],
                'position' => $data['position'],
            ]);
            $the_id = $row->id;
            log_activity('Add Member', json_encode(['name' => $data['name']]));
            return $row;
        }
    }
}
```

- [ ] **Step 2: Create the view**

Create `resources/views/system/members.blade.php`:

```blade
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
                    <div class="tm-table-wrap">
                        <table class="tm-table" style="width:100%">
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
</script>
@endsection
```

- [ ] **Step 3: Add routes**

In `routes/web.php`, add the import near the other controller imports (top of file, next to `use App\Http\Controllers\SignatoriesController;`):

```php
use App\Http\Controllers\MembersController;
```

Then, inside the same authenticated route group where the `//SIGNATORIES` routes live (around line 142-147), add:

```php
    //MEMBERS
    Route::get('/members/list', [MembersController::class, 'list'])->name('members.list');
    Route::get('/members/toggle_visibility/{id}', [MembersController::class, 'toggle_visibility'])->name('members.toggle_visibility');
    Route::post('/members/save_add', [MembersController::class, 'save_add'])->name('members.save_add');
    Route::post('/members/save_changes', [MembersController::class, 'save_changes'])->name('members.save_changes');
```

- [ ] **Step 4: Add the "Members" permission checkbox**

In `resources/views/system/access_control.blade.php`, immediately after the `Signatories` permission `<tr>...</tr>` block (the one ending at line ~473, containing `value="Signatories"`), add:

```blade
                                            <tr>
                                                <td>
                                                    Members
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Members" {{ in_array("Members", $user_permissions) ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                            </tr>
```

- [ ] **Step 5: Add the Members nav link**

In `resources/views/template.blade.php`, the System dropdown wrapper `@if` is around line 106:

```blade
@if(Auth::user()->hasPermission('View Logs') || Auth::user()->hasPermission('View Users') || Auth::user()->hasPermission('Signatories') || Auth::user()->hasPermission('Set Access Control'))
```

Add `|| Auth::user()->hasPermission('Members')` to that condition so the dropdown shows for a Members-only grant:

```blade
@if(Auth::user()->hasPermission('View Logs') || Auth::user()->hasPermission('View Users') || Auth::user()->hasPermission('Signatories') || Auth::user()->hasPermission('Set Access Control') || Auth::user()->hasPermission('Members'))
```

Then immediately after the Signatories dropdown item (line ~120, `<li><a class="dropdown-item" href="{{ url('signatories/list') }}">Signatories</a></li>`), add:

```blade
                                    @if(Auth::user()->hasPermission('Members'))
                                        <li><a class="dropdown-item" href="{{ url('members/list') }}">Members</a></li>
                                    @endif
```

- [ ] **Step 6: Grant the permission to your admin account type**

Run: `php artisan tinker`
Then (replace `SUPER ADMIN` if your logged-in account uses a different `account_type`):

```php
App\Models\UserPermissions::firstOrCreate(['account_type'=>'SUPER ADMIN','privilege'=>'Members']);
exit
```
Expected: a row is created (or already exists) in `account_type_permissions`.

- [ ] **Step 7: Manual verification in the app**

1. Reload the app; open the **System** dropdown in the sidebar → click **Members**. Expect the Members page at `/members/list`.
2. Add a member: name `Juan D. Santos`, position `Legislative Staff` → success alert; row appears as **Active**.
3. Click **Edit** on that row → form fills, title shows "Edit Member"; change position to `Records Officer` → Save → row updates.
4. Click **Hide** → confirm → status becomes **Hidden**; link now reads **Unhide**. Click **Unhide** → back to **Active**.
5. Log in as (or simulate) a non-admin account type and visit `/members/list` → you are redirected to `/` (blocked).

Expected: all five behave as described.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/MembersController.php resources/views/system/members.blade.php routes/web.php resources/views/template.blade.php resources/views/system/access_control.blade.php
git commit -m "feat(members): admin CRUD page for attendance roster"
```

---

## Task 3: Report counting logic (pure, unit-tested)

**Files:**
- Create: `app/Support/AttendanceReport.php`
- Create: `tests/Unit/AttendanceReportTest.php`

**Interfaces:**
- Produces: `App\Support\AttendanceReport::summarize(array $rows, array $members, int $sessionCount): array` where:
  - `$rows`: list of `['member_id' => int, 'status' => 'P'|'A'|'E'|'L']`.
  - `$members`: list of `['id' => int, 'name' => string, 'position' => string]`.
  - `$sessionCount`: total sessions in range (denominator for present rate).
  - Returns:
    ```
    [
      'members' => [ ['id','name','position','P','A','E','L','present_rate'], ... ],  // same order as $members
      'totals'  => ['sessions'=>int, 'P'=>int, 'A'=>int, 'E'=>int, 'L'=>int],
    ]
    ```
  Consumed by Task 6.

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/AttendanceReportTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Support\AttendanceReport;
use PHPUnit\Framework\TestCase;

class AttendanceReportTest extends TestCase
{
    private array $members = [
        ['id' => 1, 'name' => 'Alice', 'position' => 'Clerk'],
        ['id' => 2, 'name' => 'Bob',   'position' => 'Aide'],
    ];

    public function test_empty_rows_produce_zero_counts(): void
    {
        $out = AttendanceReport::summarize([], $this->members, 0);

        $this->assertCount(2, $out['members']);
        $this->assertSame(0, $out['members'][0]['P']);
        $this->assertSame(0, $out['members'][0]['present_rate']);
        $this->assertSame(['sessions' => 0, 'P' => 0, 'A' => 0, 'E' => 0, 'L' => 0], $out['totals']);
    }

    public function test_counts_each_status_separately(): void
    {
        $rows = [
            ['member_id' => 1, 'status' => 'P'],
            ['member_id' => 1, 'status' => 'P'],
            ['member_id' => 2, 'status' => 'P'],
            ['member_id' => 2, 'status' => 'A'],
            ['member_id' => 2, 'status' => 'E'],
            ['member_id' => 1, 'status' => 'L'],
        ];

        $out = AttendanceReport::summarize($rows, $this->members, 2);

        $alice = $out['members'][0];
        $bob   = $out['members'][1];

        $this->assertSame(['P' => 2, 'A' => 0, 'E' => 0, 'L' => 1], [
            'P' => $alice['P'], 'A' => $alice['A'], 'E' => $alice['E'], 'L' => $alice['L'],
        ]);
        $this->assertSame(['P' => 1, 'A' => 1, 'E' => 1, 'L' => 0], [
            'P' => $bob['P'], 'A' => $bob['A'], 'E' => $bob['E'], 'L' => $bob['L'],
        ]);

        // present_rate = round(P / sessionCount * 100)
        $this->assertSame(100, $alice['present_rate']); // 2/2
        $this->assertSame(50, $bob['present_rate']);     // 1/2

        $this->assertSame(['sessions' => 2, 'P' => 3, 'A' => 1, 'E' => 1, 'L' => 1], $out['totals']);
    }

    public function test_present_rate_is_zero_when_no_sessions(): void
    {
        $rows = [['member_id' => 1, 'status' => 'P']];
        $out = AttendanceReport::summarize($rows, $this->members, 0);
        $this->assertSame(0, $out['members'][0]['present_rate']);
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=AttendanceReportTest`
Expected: FAIL — class `App\Support\AttendanceReport` not found.

- [ ] **Step 3: Write the implementation**

Create `app/Support/AttendanceReport.php`:

```php
<?php

namespace App\Support;

class AttendanceReport
{
    /**
     * @param array $rows    list of ['member_id'=>int, 'status'=>'P'|'A'|'E'|'L']
     * @param array $members list of ['id'=>int, 'name'=>string, 'position'=>string]
     * @param int   $sessionCount total sessions in range (present-rate denominator)
     * @return array{members: array, totals: array}
     */
    public static function summarize(array $rows, array $members, int $sessionCount): array
    {
        $counts = []; // member_id => ['P'=>..,'A'=>..,'E'=>..,'L'=>..]
        foreach ($members as $m) {
            $counts[$m['id']] = ['P' => 0, 'A' => 0, 'E' => 0, 'L' => 0];
        }

        $totals = ['sessions' => $sessionCount, 'P' => 0, 'A' => 0, 'E' => 0, 'L' => 0];

        foreach ($rows as $row) {
            $id = $row['member_id'];
            $status = $row['status'];
            if (!isset($counts[$id]) || !isset($counts[$id][$status])) {
                continue; // unknown member or status — ignore
            }
            $counts[$id][$status]++;
            $totals[$status]++;
        }

        $out = [];
        foreach ($members as $m) {
            $c = $counts[$m['id']];
            $out[] = [
                'id' => $m['id'],
                'name' => $m['name'],
                'position' => $m['position'],
                'P' => $c['P'],
                'A' => $c['A'],
                'E' => $c['E'],
                'L' => $c['L'],
                'present_rate' => $sessionCount > 0 ? (int) round($c['P'] / $sessionCount * 100) : 0,
            ];
        }

        return ['members' => $out, 'totals' => $totals];
    }
}
```

- [ ] **Step 4: Regenerate the autoloader (new namespace dir) and run the test**

Run: `composer dump-autoload`
Then run: `php artisan test --filter=AttendanceReportTest`
Expected: PASS (3 tests, all green).

- [ ] **Step 5: Commit**

```bash
git add app/Support/AttendanceReport.php tests/Unit/AttendanceReportTest.php
git commit -m "feat(minutes): pure attendance-report counting logic + unit tests"
```

---

## Task 4: Record attendance in the Minutes form

**Files:**
- Modify: `resources/views/minutes/add.blade.php`
- Modify: `app/Http/Controllers/MinutesController.php`

**Interfaces:**
- Consumes: `App\Models\Member`, `App\Models\MinutesAttendance` (Task 1).
- Produces: the add/edit form posts `att[<member_id>] = P|A|E|L` and `attendance` (guests text); `MinutesController@save_attendance($minute_id, $data)` persists the rows. `add()`/`edit()` pass `$all_members` (active) and `$attendance_map` (`[member_id => status]`).

- [ ] **Step 1: Import the new models in the controller**

In `app/Http/Controllers/MinutesController.php`, add near the other model imports (after `use App\Models\Signatories;`):

```php
use App\Models\Member;
use App\Models\MinutesAttendance;
```

- [ ] **Step 2: Pass members + saved attendance to the form**

In `MinutesController@add`, add two keys to the `$data` array:

```php
            'all_members' => Member::where('is_deleted', 0)->orderBy('name')->get(),
            'attendance_map' => [],
```

In `MinutesController@edit`, build the saved map and pass both keys. Add before the `$data` array:

```php
        $attendance_map = MinutesAttendance::where('minute_id', $transid)
            ->pluck('status', 'member_id')->toArray();
```

Then add to the `$data` array in `edit`:

```php
            'all_members' => Member::where('is_deleted', 0)->orderBy('name')->get(),
            'attendance_map' => $attendance_map,
```

- [ ] **Step 3: Replace the attendance textarea with the roster panel**

In `resources/views/minutes/add.blade.php`, replace the entire block (currently lines ~74-77):

```blade
                        <div class="col-12">
                            <label class="tm-label">Attendance List <span class="text-danger">*</span> <small class="tm-muted">(one name per line)</small></label>
                            <textarea class="tm-textarea" required name="attendance" rows="10">{{ $edit ? $info->attendance : '' }}</textarea>
                        </div>
```

with:

```blade
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
                                <div class="tm-table-wrap">
                                    <table class="tm-table" style="width:100%">
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
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                        <div class="col-12">
                            <label class="tm-label">Guests / Others <small class="tm-muted">(not on the roster — one per line)</small></label>
                            <textarea class="tm-textarea" name="attendance" rows="4" placeholder="e.g. Hon. Roberto A. Mendoza — Presiding Officer">{{ $edit ? $info->attendance : '' }}</textarea>
                        </div>
```

- [ ] **Step 4: Add the present-count + shortcut script**

In `resources/views/minutes/add.blade.php`, inside the existing `@section("additional_footer")` `<script>` block, add this near the end (before the closing `</script>`):

```javascript
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
```

Also add this small style inside the existing `<style>` block in `@section("additional_head")`:

```css
    .att-opt { display:inline-block; margin-left:10px; font-size:12px; color:var(--tm-muted); cursor:pointer; }
    .att-opt input { vertical-align:middle; }
```

- [ ] **Step 5: Persist attendance on save**

In `app/Http/Controllers/MinutesController.php`, add a new method (place it right after `save_minute`):

```php
    public function save_attendance($minute_id, $data)
    {
        MinutesAttendance::where('minute_id', $minute_id)->delete();

        $att = $data['att'] ?? [];
        foreach ($att as $member_id => $status) {
            if (!in_array($status, ['P', 'A', 'E', 'L'], true)) continue;
            MinutesAttendance::create([
                'minute_id' => (int) $minute_id,
                'member_id' => (int) $member_id,
                'status' => $status,
            ]);
        }
    }
```

Then call it from `_save_changes`, right after the `save_minute` call succeeds. Locate this block in `_save_changes`:

```php
        if ( ! $this->save_minute($the_id, $data))
        {
                // ... error branch ...
        }
```

Immediately **after** that `if (...) { ... }` error block (i.e. once the minute is known-saved and `$the_id` is set), add:

```php
        $this->save_attendance($the_id, $data);
```

(The guests text is already saved: the `attendance` textarea still posts `attendance`, which `save_minute` already writes. No change needed there. Note the field is no longer `required`, which is intentional.)

- [ ] **Step 6: Manual verification**

Precondition: Task 2 done and at least 3 members exist (add via System → Members if needed).

1. Go to **Minutes → Add** (any session type). The attendance section shows the member roster with P/A/E/L radios and a live **Present: X of N** pill; a **Guests / Others** textarea sits below.
2. Set mixed statuses (e.g. 2 Present, 1 Late, 1 Absent). The pill counts only Present. Click **Mark all present** → all become P and the pill = N. Click **Clear all** → pill = 0. Re-set your mixed statuses.
3. Type a guest line in Guests / Others. Fill the other required fields; **Save**.
4. Verify DB:
   Run `php artisan tinker` then:
   ```php
   $m = App\Models\Minutes::latest('id')->first();
   App\Models\MinutesAttendance::where('minute_id',$m->id)->get(['member_id','status']);
   $m->attendance; // the guest text
   exit
   ```
   Expected: one attendance row per member you set (unset members have no row), statuses match; `$m->attendance` holds the guest line.
5. Open the same minute via **Edit** → the radios are pre-selected from saved data; the guest text is in the textarea. Change one member Absent→Excused, **Save**, re-check DB: that row's status changed, no duplicate rows for the minute.

Expected: all steps behave as described; editing re-saves without duplicating rows.

- [ ] **Step 7: Commit**

```bash
git add resources/views/minutes/add.blade.php app/Http/Controllers/MinutesController.php
git commit -m "feat(minutes): roster attendance panel (P/A/E/L) + guests, persisted per session"
```

---

## Task 5: Attendance sheet in the PDF

**Files:**
- Create: `resources/views/minutes/_attendance_sheet.blade.php`
- Modify: `app/Http/Controllers/MinutesController.php` (`generate_pdf`)

**Interfaces:**
- Consumes: `minutes_attendance` joined to `members` (Task 1), `minutes.attendance` guest text.
- Produces: an attendance-sheet page appended to the Minutes PDF between the editor content and the image attachments.

- [ ] **Step 1: Create the sheet partial**

Create `resources/views/minutes/_attendance_sheet.blade.php`:

```blade
<div style="font-family:'nunito',sans-serif;">
    <div style="text-align:center;line-height:1.5">
        <div>Republic of the Philippines</div>
        <div>Province of Sorsogon</div>
        <div style="font-weight:bold">MUNICIPALITY OF PRIETO DIAZ</div>
        <div>Sangguniang Bayan</div>
    </div>
    <h3 style="text-align:center;text-transform:uppercase;letter-spacing:1px;margin:16px 0 2px">Attendance Sheet</h3>
    <div style="text-align:center;margin-bottom:14px">{{ $info->category }} No. {{ $info->series_number }}</div>

    <table style="width:100%;font-size:12px;margin-bottom:14px">
        <tr>
            <td><b>Date &amp; Time:</b> {{ date('F d, Y · h:i A', strtotime($info->date_created)) }}</td>
            <td><b>Venue:</b> {{ $info->venue }}</td>
        </tr>
        <tr>
            <td><b>Presiding Officer:</b> {{ $info->presiding_officer }}</td>
            <td><b>Session Type:</b> {{ $info->category }}</td>
        </tr>
    </table>

    <table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:12px">
        <thead>
            <tr style="background:#f0f2f5">
                <th style="width:26px">#</th><th style="text-align:left">Name</th>
                <th style="text-align:left">Position</th><th style="width:80px">Status</th><th style="width:140px">Signature</th>
            </tr>
        </thead>
        <tbody>
            @php $statusLabel = ['P'=>'Present','A'=>'Absent','E'=>'Excused','L'=>'Late']; $i=1; @endphp
            @forelse($attendees as $a)
                <tr>
                    <td style="text-align:center">{{ $i++ }}</td>
                    <td>{{ $a->name }}</td>
                    <td>{{ $a->position }}</td>
                    <td style="text-align:center">{{ $statusLabel[$a->status] ?? $a->status }}</td>
                    <td></td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center">No roster attendance recorded.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table style="width:100%;font-size:12px;margin-top:12px">
        <tr>
            <td><b>Present:</b> {{ $tally['P'] }}</td>
            <td><b>Late:</b> {{ $tally['L'] }}</td>
            <td><b>Excused:</b> {{ $tally['E'] }}</td>
            <td><b>Absent:</b> {{ $tally['A'] }}</td>
            <td><b>Total roster:</b> {{ $tally['P'] + $tally['A'] + $tally['E'] + $tally['L'] }}</td>
        </tr>
    </table>

    @if(trim($info->attendance) !== '')
        <div style="font-size:12px;margin-top:12px">
            <b>Guests / Others present:</b><br>
            {!! nl2br(e($info->attendance)) !!}
        </div>
    @endif
</div>
```

- [ ] **Step 2: Render + append the sheet in `generate_pdf`**

In `app/Http/Controllers/MinutesController.php@generate_pdf`, locate the line that writes the editor HTML:

```php
        $mpdf->WriteHTML($html);
```

Immediately after it, add:

```php
        // Attendance sheet page (roster + guests)
        if ($minute_info) {
            $attendees = MinutesAttendance::where('minute_id', $transid)
                ->join('members', 'members.id', '=', 'minutes_attendance.member_id')
                ->orderBy('members.name')
                ->get(['members.name', 'members.position', 'minutes_attendance.status']);

            $tally = ['P' => 0, 'A' => 0, 'E' => 0, 'L' => 0];
            foreach ($attendees as $a) {
                if (isset($tally[$a->status])) $tally[$a->status]++;
            }

            $sheetHtml = view('minutes._attendance_sheet', [
                'info' => $minute_info,
                'attendees' => $attendees,
                'tally' => $tally,
            ])->render();

            $mpdf->AddPage();
            $mpdf->WriteHTML($sheetHtml);
        }
```

(The existing attachment loop runs after this, so ordering is: editor content → attendance sheet → attachments.)

- [ ] **Step 3: Manual verification**

Use the minute you created in Task 4 (it has attendance rows + a guest line).

1. Open **Minutes → View** for that minute (or the "View in New Tab" button), which runs `generate_pdf`.
2. In the generated PDF, after the main content there is an **Attendance Sheet** page: letterhead, session meta, a numbered table of members with their status, a tally line (Present/Late/Excused/Absent/Total), and the **Guests / Others** line.
3. Any image attachments still appear *after* the attendance sheet.
4. Open an **old** minute (one created before this feature, with only free-text attendance): the sheet shows "No roster attendance recorded" and lists the old text under Guests / Others. No errors.

Expected: sheet renders with correct counts and ordering; old minutes still generate a PDF.

- [ ] **Step 4: Commit**

```bash
git add resources/views/minutes/_attendance_sheet.blade.php app/Http/Controllers/MinutesController.php
git commit -m "feat(minutes): append per-session attendance sheet to the PDF"
```

---

## Task 6: Cross-session attendance report

**Files:**
- Modify: `app/Http/Controllers/MinutesController.php` (add `attendance_report`, `export_report_csv`)
- Create: `resources/views/minutes/report.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/minutes/list.blade.php`

**Interfaces:**
- Consumes: `App\Support\AttendanceReport::summarize()` (Task 3); `Minutes`, `MinutesAttendance`, `Member` (Task 1).
- Produces: route `minutes.attendance_report` (GET, accepts `from`, `to`, `type`, `export` query params); report page + CSV download.

- [ ] **Step 1: Import the report helper**

In `app/Http/Controllers/MinutesController.php`, add near the other `use` imports:

```php
use App\Support\AttendanceReport;
```

- [ ] **Step 2: Add the report + CSV methods**

Add these two methods to `MinutesController` (e.g. after `view_minute`):

```php
    public function attendance_report(Request $request)
    {
        log_activity('View Attendance Report');

        $from = $request->input('from', date('Y-01-01'));
        $to   = $request->input('to', date('Y-m-d'));
        $type = $request->input('type', '');

        $mq = Minutes::where('is_deleted', 0)
            ->whereDate('date_created', '>=', $from)
            ->whereDate('date_created', '<=', $to);
        if ($type !== '') $mq->where('category', $type);

        $minuteIds = $mq->pluck('id')->all();
        $sessionCount = count($minuteIds);

        $rows = [];
        if ($sessionCount > 0) {
            $rows = MinutesAttendance::whereIn('minute_id', $minuteIds)
                ->get(['member_id', 'status'])
                ->map(fn($r) => ['member_id' => $r->member_id, 'status' => $r->status])
                ->all();
        }

        $members = Member::where('is_deleted', 0)->orderBy('name')
            ->get(['id', 'name', 'position'])
            ->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'position' => $m->position])
            ->all();

        $summary = AttendanceReport::summarize($rows, $members, $sessionCount);

        if ($request->input('export') === 'csv') {
            return $this->export_report_csv($summary, $from, $to, $type);
        }

        $sessionTypes = ['Regular Session', 'Committee Hearing', 'Special Session'];

        return view('minutes.report', [
            'menu' => 'Minutes',
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
            'type' => $type,
            'sessionTypes' => $sessionTypes,
        ]);
    }

    public function export_report_csv($summary, $from, $to, $type)
    {
        $filename = 'attendance_report_' . $from . '_to_' . $to . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($summary, $from, $to, $type) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Attendance Report']);
            fputcsv($out, ['Range', $from . ' to ' . $to]);
            fputcsv($out, ['Session type', $type === '' ? 'All types' : $type]);
            fputcsv($out, ['Sessions held', $summary['totals']['sessions']]);
            fputcsv($out, []);
            fputcsv($out, ['Member', 'Position', 'Present', 'Late', 'Excused', 'Absent', 'Present rate %']);
            foreach ($summary['members'] as $m) {
                fputcsv($out, [$m['name'], $m['position'], $m['P'], $m['L'], $m['E'], $m['A'], $m['present_rate']]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
```

- [ ] **Step 3: Create the report view**

Create `resources/views/minutes/report.blade.php`:

```blade
@extends("template",['menu' => $menu])

@section("content")
<div class="layout-px-spacing">
    <div class="tm-page">
        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">Attendance Report</h1>
                <div class="tm-crumb"><a href="{{ url('minutes/list') }}">Minutes</a> / Attendance Report</div>
            </div>
        </div>

        <div class="tm-card">
            <form method="get" action="{{ url('minutes/attendance_report') }}" class="row g-3 align-items-end">
                <div class="col-auto">
                    <label class="tm-label">From</label>
                    <input type="date" class="tm-input" name="from" value="{{ $from }}">
                </div>
                <div class="col-auto">
                    <label class="tm-label">To</label>
                    <input type="date" class="tm-input" name="to" value="{{ $to }}">
                </div>
                <div class="col-auto">
                    <label class="tm-label">Session type</label>
                    <select name="type" class="tm-select">
                        <option value="">All types</option>
                        @foreach($sessionTypes as $st)
                            <option {{ $type === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="tm-btn tm-btn-primary">Apply</button>
                </div>
                <div class="col-auto">
                    <a class="tm-btn tm-btn-outline"
                       href="{{ url('minutes/attendance_report') }}?from={{ $from }}&to={{ $to }}&type={{ urlencode($type) }}&export=csv">Export CSV</a>
                </div>
            </form>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-6 col-md"><div class="tm-card text-center"><div style="font-size:24px;font-weight:800">{{ $summary['totals']['sessions'] }}</div><div class="tm-muted" style="font-size:12px">Sessions held</div></div></div>
            <div class="col-6 col-md"><div class="tm-card text-center"><div style="font-size:24px;font-weight:800">{{ $summary['totals']['P'] }}</div><div class="tm-muted" style="font-size:12px">Total Present</div></div></div>
            <div class="col-6 col-md"><div class="tm-card text-center"><div style="font-size:24px;font-weight:800">{{ $summary['totals']['L'] }}</div><div class="tm-muted" style="font-size:12px">Total Late</div></div></div>
            <div class="col-6 col-md"><div class="tm-card text-center"><div style="font-size:24px;font-weight:800">{{ $summary['totals']['E'] }}</div><div class="tm-muted" style="font-size:12px">Total Excused</div></div></div>
            <div class="col-6 col-md"><div class="tm-card text-center"><div style="font-size:24px;font-weight:800">{{ $summary['totals']['A'] }}</div><div class="tm-muted" style="font-size:12px">Total Absent</div></div></div>
        </div>

        <div class="tm-card mt-3">
            <div class="tm-table-wrap">
                <table class="tm-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Member</th><th>Position</th>
                            <th style="text-align:center">Present</th><th style="text-align:center">Late</th>
                            <th style="text-align:center">Excused</th><th style="text-align:center">Absent</th>
                            <th style="text-align:center">Present rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary['members'] as $m)
                            <tr>
                                <td style="font-weight:600">{{ $m['name'] }}</td>
                                <td class="tm-muted">{{ $m['position'] }}</td>
                                <td style="text-align:center">{{ $m['P'] }}</td>
                                <td style="text-align:center">{{ $m['L'] }}</td>
                                <td style="text-align:center">{{ $m['E'] }}</td>
                                <td style="text-align:center">{{ $m['A'] }}</td>
                                <td style="text-align:center;font-weight:700;{{ $m['present_rate'] < 75 ? 'color:#C0392B' : '' }}">{{ $m['present_rate'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="tm-table-empty">No members yet. Add members under System → Members.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 4: Add the route**

In `routes/web.php`, inside the `//MINUTES` route group (near the other `minutes/*` GET routes), add:

```php
    Route::get('/minutes/attendance_report', [MinutesController::class, 'attendance_report'])->name('minutes.attendance_report');
```

- [ ] **Step 5: Add the entry-point button on the Minutes list**

In `resources/views/minutes/list.blade.php`, the toolbar `<div>` sits inside `tm-page-head` (lines ~24-35) and ends with the Grid View link (line 34):

```blade
                <a href="{{ url('minutes/list_grid') }}" class="tm-btn tm-btn-outline">Grid View</a>
```

Add the report link immediately after that Grid View link, before the closing `</div>`:

```blade
                <a href="{{ url('minutes/attendance_report') }}" class="tm-btn tm-btn-outline">Attendance Report</a>
```

- [ ] **Step 6: Manual verification**

Precondition: at least 2 minutes exist in the current year with roster attendance recorded (create a second one via Task 4 flow if needed, with different statuses).

1. From **Minutes list**, click **Attendance Report** → the report page loads with the default range (Jan 1 → today).
2. KPI cards show Sessions held and the four separate status totals. The table lists each member with separate Present/Late/Excused/Absent columns and a Present rate; rates under 75% render red.
3. Cross-check one member's numbers by hand against the minutes you created — counts match, and Present rate = round(Present ÷ sessions × 100).
4. Change **Session type** to a specific type and **Apply** → sessions and counts narrow to that type. Set a **From/To** range that excludes one session → counts drop accordingly.
5. Click **Export CSV** → a `attendance_report_*.csv` downloads; open it and confirm the header rows and one line per member with the same figures.

Expected: counts are correct and each status is counted separately; filters and CSV work.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/MinutesController.php resources/views/minutes/report.blade.php routes/web.php resources/views/minutes/list.blade.php
git commit -m "feat(minutes): cross-session attendance report with filters + CSV export"
```

---

## Self-Review

**Spec coverage:**
- Structured attendance from a managed roster (P/A/E/L) → Tasks 1, 4. ✔
- Guests/others free text kept → Task 4 (reuses `minutes.attendance`). ✔
- Members list separate from Signatories, admin-managed, starts empty → Tasks 1, 2. ✔
- Per-session attendance sheet appended to PDF → Task 5. ✔
- Cross-session report, each status counted separately, date + type filters, export → Tasks 3, 6. ✔
- Informational present count (no quorum enforcement) → Task 4 (pill). ✔
- Same mechanism for all three session types → Task 4 (roster shown regardless of `category`). ✔
- Backward compatibility (old minutes keep text as guests) → Task 5 verification step 4 + Task 4 (guests textarea prefilled from `attendance`). ✔
- Permission + nav wiring → Task 2. ✔

**Placeholder scan:** No TBD/TODO. Every code step shows exact content, including the Minutes-list button placement (Task 6 Step 5 quotes the exact anchor line to insert after).

**Type consistency:** `AttendanceReport::summarize($rows, $members, $sessionCount)` signature and its return shape (`members[]` with keys `id,name,position,P,A,E,L,present_rate`; `totals` with `sessions,P,A,E,L`) are defined in Task 3 and consumed identically in Task 6. Form field `att[<member_id>]` and column `status` (`P/A/E/L`) are consistent across Tasks 4, 5, 6. `member_id`/`minute_id` column names match the migrations in Task 1.
