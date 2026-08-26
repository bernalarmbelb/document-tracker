# Minutes — Session Attendance & Reports

**Date:** 2026-08-27
**Branch:** `redesign/treasury-theme` (feature work branches from here)
**Module:** Minutes
**Status:** Design approved in brainstorming; pending spec review → implementation plan

---

## 1. Problem

The client needs to record **who attended each session** and generate reports from
it. Today the Minutes module stores attendance as a single free-text `attendance`
column (a plain textarea, "one name per line"). There is no structured record, so:

- No per-person status (present / absent / etc.).
- No consistent list of who *could* attend.
- No way to report attendance across sessions (e.g. "who attends regularly").

This applies to all three session types: **Regular Session**, **Committee Hearing**,
and **Special Session** — which share one `category` column and one add/edit form.

## 2. Goals

1. Record structured attendance per session from a managed roster, with status
   **Present / Absent / Excused / Late** per person.
2. Keep a free-text area for **guests / others** not on the roster.
3. Produce a **per-session attendance sheet** (printable / PDF, appended to the
   Minutes PDF).
4. Produce a **cross-session attendance report** with each status counted
   **separately** per member, filterable by date range and session type.

### Non-goals (YAGNI)

- No quorum enforcement. Quorum rule is undecided, so the UI shows an
  **informational present count** only. No blocking, no pass/fail state. Turning
  it into a real quorum check later is a display-only change; nothing in the data
  model depends on it.
- No import/seeding of the Members list from old free-text attendance (names are
  inconsistent). Members list starts **empty**; admin populates it.
- No per-committee roster subsets. All three session types use the identical
  attendance mechanism (confirmed with client).
- No editing of historical minutes' attendance beyond what the existing edit form
  already allows.

## 3. Key decisions (from brainstorming)

| Decision | Choice |
|---|---|
| Attendee source | New **Members** list (office / LGU staff), **separate from Signatories** |
| Guests | Free-text area at the bottom of the attendance panel; not counted in reports |
| Status values | Present / Absent / Excused / Late |
| Report type | **Both** per-session sheet **and** cross-session report |
| Report counting | Each status counted **separately** per member; headline metric is Present rate (Present ÷ sessions) |
| Session types | Same mechanism for all three |
| Quorum | Informational count only (rule TBD) |
| Members seed | Start empty |

**Why Members ≠ Signatories:** Signatories are the officials who *sign* documents
(used for the Presiding Officer dropdown and e-signatures). Members are who
*attends* sessions. These are different lists and must not be conflated.

## 4. Data model

### 4.1 New table `members`

Mirrors the shape and management pattern of `signatories`.

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | string | Full name |
| `position` | string | e.g. "Legislative Staff" |
| `is_deleted` | tinyint default 0 | "Hide" toggle; preserves history |
| `created_at` / `updated_at` | timestamps | |

New migration `..._create_members_table`. New model `App\Models\Member` (table
`members`, fillable `name`, `position`, `is_deleted`).

### 4.2 New table `minutes_attendance`

One row per member per session that has attendance recorded.

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `minute_id` | bigint | FK → `minutes.id` (index; not enforced FK, matching existing project style) |
| `member_id` | bigint | FK → `members.id` |
| `status` | string(1) | `P` / `A` / `E` / `L` |
| `created_at` / `updated_at` | timestamps | |

New migration `..._create_minutes_attendance_table`. New model
`App\Models\MinutesAttendance` (fillable `minute_id`, `member_id`, `status`).

Store the member's name/position via the `member_id` join at render time. (No
name snapshot column in v1; if a member is renamed, historical sheets reflect the
current name. Acceptable for this system — revisit only if the client reports it.)

### 4.3 Repurpose existing `minutes.attendance` column

The existing free-text `attendance` column becomes the **guests / others** field.
No schema change, no data migration — existing minutes keep their typed text,
which now renders under "Guests / Others". `Minutes` model gains an `attendees()`
`hasMany` relationship to `MinutesAttendance`.

## 5. Components & flow

### 5.1 Members management (new admin page)

Mirror `SignatoriesController` + `resources/views/system/signatories.blade.php`
exactly.

- `MembersController` with `list`, `save_add`, `save_changes`, `toggle_visibility`
  (hide/unhide). Admin-gated in `__construct` like the other system controllers.
- View `resources/views/system/members.blade.php`: add-member form (name +
  position) and a table with edit / hide actions.
- Routes under `/members/*` in the same `routes/web.php` group as signatories.
- Nav: add a **Members** link in the System dropdown in `template.blade.php`,
  gated by a new `Members` permission (consistent with the existing
  `hasPermission('Signatories')` pattern). The `Members` permission must be added
  to the access-control permission set.

### 5.2 Recording attendance (Minutes add/edit form)

Modify `resources/views/minutes/add.blade.php`: replace the single `attendance`
textarea with an **attendance panel**:

- Roster list built from active `members` (`is_deleted = 0`). Each row: name +
  position + a **P / A / E / L** segmented control.
- "Mark all present" / "Clear all" shortcuts.
- Informational **"Present: X of N"** count (no enforcement).
- **Guests / others** textarea below (maps to `minutes.attendance`).
- On edit, pre-select each member's saved status from `minutes_attendance`.

Form submission (existing `save_add` / `save_changes` → `save_minute`):

- The form posts a `status[member_id] = P|A|E|L` map plus the guests textarea.
- `MinutesController@save_minute` continues to write the `minutes` row (guests →
  `attendance` column), then **upserts** `minutes_attendance`: delete existing rows
  for the minute and insert the submitted set (simple, correct, matches the
  project's straightforward style). Members left unset are not written (treated as
  "no record" rather than a status).

Controller `add()` / `edit()` pass the active members list and (on edit) the saved
attendance map to the view.

### 5.3 Per-session attendance sheet (PDF)

Modify `MinutesController@generate_pdf`: after the editor content, append an
**Attendance Sheet** page rendered from a Blade partial:

- Municipal letterhead (reuse the existing header style).
- Session meta: series number, date/time, venue, presiding officer, session type.
- Numbered table: # / Name / Position / Status pill / Signature column.
- Tally strip: counts of Present / Late / Excused / Absent / roster total.
- Guests / others listed below.

The sheet is inserted before the image-attachment pages so ordering is:
editor content → attendance sheet → attachments. No new route required (it rides
the existing `generate_pdf` / `view_minute` flow). Optionally a standalone
"attendance sheet only" route can be added if the client wants it separately —
deferred unless requested.

### 5.4 Cross-session report (new page)

- `MinutesController@attendance_report` (view) reads a date range + optional
  session-type filter, aggregates `minutes_attendance` joined to `members` and
  `minutes`, and renders per-member counts.
- View `resources/views/minutes/report.blade.php`:
  - Filters: from date, to date, session type (All / Regular / Committee / Special).
  - KPI totals: Sessions held, Total Present, Total Late, Total Excused,
    Total Absent (**each counted separately**).
  - Table: Member / Position / Present / Late / Excused / Absent / Present rate
    (bar = Present ÷ sessions in range).
  - Export buttons (PDF via existing mPDF; Excel/CSV). **CSV/PDF export scope:**
    PDF export reuses mPDF; a CSV download covers the "Excel" need in v1 (native
    xlsx deferred unless requested).
- Routes: `GET /minutes/attendance_report` (+ a POST or query-string variant for
  filters), in the Minutes route group.
- Entry point: an **"Attendance Report"** button on the Minutes list page
  (`minutes/list`), gated by `View Minutes`.

## 6. Files touched

**New**
- `database/migrations/..._create_members_table.php`
- `database/migrations/..._create_minutes_attendance_table.php`
- `app/Models/Member.php`
- `app/Models/MinutesAttendance.php`
- `app/Http/Controllers/MembersController.php`
- `resources/views/system/members.blade.php`
- `resources/views/minutes/report.blade.php`
- `resources/views/minutes/_attendance_sheet.blade.php` (PDF partial)

**Edit**
- `app/Models/Minutes.php` — add `attendees()` relationship.
- `app/Http/Controllers/MinutesController.php` — `add`, `edit`, `save_minute`,
  `generate_pdf`; add `attendance_report`.
- `resources/views/minutes/add.blade.php` — attendance panel replaces textarea.
- `resources/views/minutes/list.blade.php` — "Attendance Report" button.
- `routes/web.php` — members routes, report route.
- `resources/views/template.blade.php` — Members nav link.
- Access-control permission set — add `Members` permission.

## 7. Backward compatibility

- Existing minutes: unchanged. Their `attendance` text renders as "Guests /
  Others". No data migration.
- No structured attendance for old minutes until someone edits them — acceptable;
  the report simply has no rows for sessions predating the feature.
- All other modules (Resolutions, Ordinances, Communications, Activities,
  Signatories, Archive) are untouched.

## 8. Testing

- **Members CRUD:** add, edit, hide/unhide; hidden members drop off future
  checklists but remain in historical attendance.
- **Record attendance:** new minute with mixed statuses + guests; verify
  `minutes_attendance` rows and `attendance` (guests) text saved; edit and change
  statuses; verify upsert (no duplicates, removed members cleared).
- **Present count:** informational count updates as statuses change.
- **PDF sheet:** statuses, tally, and guests render; ordering is content → sheet →
  attachments.
- **Report:** counts per status are correct and separate; date-range and
  session-type filters work; Present rate math; CSV/PDF export.
- **Backward compat:** an old minute (text-only attendance) still opens, edits, and
  generates a PDF; its text shows under Guests / Others.
- **Permissions:** non-admin cannot reach Members management; Minutes report
  respects `View Minutes`.

## 9. Open items (non-blocking)

- **Quorum rule** — informational for now; wire a real rule when the client
  specifies it (majority of members? fixed number? does Late count as present?).
- **Native xlsx export** — CSV in v1; upgrade if the client wants true Excel.
- **Member name snapshotting** — not stored in v1; add a snapshot column only if
  renames corrupting historical sheets becomes a real complaint.
