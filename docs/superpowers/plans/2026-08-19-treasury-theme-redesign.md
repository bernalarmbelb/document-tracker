# Treasury-Style Theme Redesign — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restyle the Prieto Diaz Document Tracker to the Treasury Management System's look & feel and rebuild the Dashboard with KPIs, ApexCharts, and a live activity feed — view-layer + CSS only, no data changes.

**Architecture:** A single shared `theme.css` defines treasury design tokens and component styles, layered on top of the existing Cork admin CSS (which stays loaded, so unrestyled views keep working). Views are restyled wave by wave. The only server change is additive read-only queries in the Dashboard controller.

**Tech Stack:** Laravel 10 (Blade), Bootstrap 5, ApexCharts (already bundled), Manrope + Archivo fonts.

**Spec:** `docs/superpowers/specs/2026-08-19-treasury-theme-redesign-design.md`

## Global Constraints

- **View-layer + CSS only.** No migrations, no schema/model/route changes. Only additive, read-only queries permitted, and only in the Dashboard controller.
- **Additive CSS.** Keep Cork CSS loaded; `theme.css` layers on top. Namespace tokens (`--tm-*`) so Cork variables are never clobbered.
- **Colors:** primary `#427AB5`, secondary `#406AAF`, accent `#F7DD7D`, success `#0FA958`, danger `#DC3545`, warning `#E84E46`, dark `#333`, bg `#F0F2F5`, muted `#7A7777`.
- **Fonts:** Manrope (UI/body), Archivo (headings/numbers).
- **Verify each task:** `php artisan view:cache` must succeed (0 Blade errors) + visual check + existing functionality unchanged. Commit per task.
- **Branch:** `redesign/treasury-theme`. Local DB `document_tracker` (root/admin1234). php: `~/.config/herd/bin/php84/php.exe`.

---

## File Structure

- Create: `public/assets/css/theme.css` — design tokens + component classes (buttons, cards, tables, forms, badges, page headers, KPI tiles, activity feed).
- Modify: `resources/views/template.blade.php` — link `theme.css`.
- Modify: `app/Http/Controllers/LoginController.php` (`dashboard()`) — additive read-only queries (monthly trend, recent list, activity feed).
- Modify: `resources/views/dashboard.blade.php` — new two-column layout + ApexCharts.
- Modify (Wave 2): list views under `resources/views/{resolutions,ordinances,minutes,communications,sangguniang,signatories,system,archive}/`.
- Modify (Wave 3): add/edit form views + `template.blade.php` global-search modal.
- Modify (Wave 4): `resources/views/login_form.blade.php`, footer, PDF/print blades.

---

## WAVE 0 — Foundation

### Task 0.1: Create `theme.css` design tokens + component library

**Files:**
- Create: `public/assets/css/theme.css`
- Modify: `resources/views/template.blade.php` (add `<link>` after `topnav.css`)

**Interfaces:**
- Produces CSS classes consumed by all later waves: `.tm-btn` (+ `--primary/--secondary/--accent/--success/--danger/--warning/--dark`), `.tm-card`, `.tm-page-head`, `.tm-title`, `.tm-sub`, `.tm-crumb`, `.tm-table`, `.tm-badge` (+ `--ok/--warn/--bad`), `.tm-input`, `.tm-select`, `.tm-kpi` (+ `.chip`, `.num`, `.lbl`, `.sub`), `.tm-feed` (+ `.item`, `.ic`, `.act`, `.meta`), `.tm-modal`.
- Tokens: `--tm-primary`, `--tm-secondary`, `--tm-accent`, `--tm-success`, `--tm-danger`, `--tm-warning`, `--tm-dark`, `--tm-bg`, `--tm-muted`, `--tm-line`, `--tm-card`.

- [ ] **Step 1: Write `public/assets/css/theme.css`** with the tokens and components. Use the exact palette from Global Constraints. Base the component styles on `treasury/resources/css/app.css` (buttons lines 564-583, tables 1087+, badges 1176+) and the mockup at `scratchpad/dashboard-mockup.html` (KPI tiles, feed, cards). Namespace all classes `.tm-*` and tokens `--tm-*`. Import Manrope + Archivo via `@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Archivo:wght@500;600;700;800&display=swap');`.

- [ ] **Step 2: Link it in the template.** In `resources/views/template.blade.php`, immediately after the `topnav.css` link, add:

```blade
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/css/theme.css") }}">
```

- [ ] **Step 3: Compile views.** Run: `~/.config/herd/bin/php84/php.exe artisan view:cache` — Expected: "Blade templates cached successfully." Then `... artisan view:clear`.

- [ ] **Step 4: Visual check.** Build a throwaway `_theme_probe_tmp.html` in the project root that links nothing but inlines `theme.css` and renders one of each component (button set, card, table, badge, KPI tile, feed item). Render it in the browser pane, screenshot, confirm the treasury look. Delete the probe file.

- [ ] **Step 5: Commit.**

```bash
git add public/assets/css/theme.css resources/views/template.blade.php
git commit -m "feat(ui): add treasury-style theme.css design system"
```

---

## WAVE 1 — Dashboard

### Task 1.1: Add read-only dashboard data (controller)

**Files:**
- Modify: `app/Http/Controllers/LoginController.php` — `dashboard()` (currently ~line 46-78)

**Interfaces:**
- Produces view variables consumed by `dashboard.blade.php`: existing counts (`resolutions_ctr`, `resolutions_approved_ctr`, `ordinances_ctr`, `ordinances_approved_ctr`, etc.), plus new: `minutes_ctr` (int), `communications_ctr` (int), `activities_ctr` (int), `monthly_labels` (array<string> month names), `monthly_counts` (array<int>), `recent_docs` (Collection, ≤8, fields: record_type, series_number, title, author_name, approved_date/date_created, status), `activity_feed` (Collection of {action, username, activity_date}, ≤12).

- [ ] **Step 1: Add the additive queries.** In `dashboard()`, before `$data = array(...)`, add (read-only):

```php
use App\Models\Minutes;
use App\Models\Communications;
use App\Models\SangguniangActivities;
use App\Models\ActivityLogs;
use Illuminate\Support\Facades\DB;

// counts
$minutes_ctr        = Minutes::where(['is_deleted' => 0])->count();
$communications_ctr = Communications::where(['is_deleted' => 0])->count();
$activities_ctr     = SangguniangActivities::where(['is_deleted' => 0])->count();

// documents created per month (current year), resolutions + ordinances
$months = collect(range(1, 12));
$resByMonth = Resolutions::where('is_deleted', 0)
    ->whereYear('date_created', $currentYear)
    ->selectRaw('MONTH(date_created) m, COUNT(*) c')->groupBy('m')->pluck('c', 'm');
$ordByMonth = Ordinances::where('is_deleted', 0)
    ->whereYear('date_created', $currentYear)
    ->selectRaw('MONTH(date_created) m, COUNT(*) c')->groupBy('m')->pluck('c', 'm');
$monthly_labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$monthly_counts = $months->map(fn ($m) => (int) ($resByMonth[$m] ?? 0) + (int) ($ordByMonth[$m] ?? 0))->all();

// recent documents (reuse merged), latest 8
$recent_docs = $merged->sortByDesc(fn ($r) => $r->date_created)->take(8)->values();

// activity feed, latest 12 with username
$activity_feed = ActivityLogs::leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
    ->orderByDesc('activity_logs.activity_date')
    ->limit(12)
    ->get(['activity_logs.action', 'activity_logs.activity_date', 'users.username']);
```

Then add these keys to the `$data` array: `'minutes_ctr'`, `'communications_ctr'`, `'activities_ctr'`, `'monthly_labels'`, `'monthly_counts'`, `'recent_docs'`, `'activity_feed'`.

- [ ] **Step 2: Verify data via tinker.** Run:

```bash
~/.config/herd/bin/php84/php.exe artisan tinker --execute="\$c=app('App\Http\Controllers\LoginController'); echo 'ok';"
```

Then sanity-check counts directly: `... artisan tinker --execute="echo App\Models\Minutes::where('is_deleted',0)->count();"` — Expected: 41.

- [ ] **Step 3: Commit.**

```bash
git add app/Http/Controllers/LoginController.php
git commit -m "feat(dashboard): add read-only counts, monthly trend, recent docs, activity feed"
```

### Task 1.2: Rebuild `dashboard.blade.php` with the new layout

**Files:**
- Modify: `resources/views/dashboard.blade.php` (replace body content; keep `@extends`/`@section` wrapper and `$menu='Dashboard'`)

**Interfaces:**
- Consumes all variables from Task 1.1.

- [ ] **Step 1: Replace the dashboard content** with the two-column layout from `scratchpad/dashboard-mockup.html`, wired to real variables: KPI row (5 `.tm-kpi` tiles), main column (monthly bar `#trend`, records-by-type donut `#bytype`, approval-status bar `#status`, `.tm-table` recent docs from `$recent_docs`), sidebar (`.tm-btn` quick actions linking to `/resolutions/add` etc. gated by `hasPermission`, and `.tm-feed` from `$activity_feed`). Use `@section('additional_footer')` for the ApexCharts init.

- [ ] **Step 2: ApexCharts init** in `@section('additional_footer')` — include `<script src="{{ asset('assets/src/plugins/src/apex/apexcharts.min.js') }}"></script>` then render `#trend` (bar, `@json($monthly_labels)` / `@json($monthly_counts)`), `#bytype` (donut, the 5 counts), `#status` (horizontal stacked bar, approved vs other). Colors from tokens.

- [ ] **Step 3: Compile + functional check.** `... artisan view:cache` (expect success), then `... view:clear`. Log in to the live local app and confirm the dashboard renders, numbers match, charts draw, quick-action links resolve, feed shows recent activity.

- [ ] **Step 4: Visual check.** Screenshot the logged-in dashboard; compare against the mockup.

- [ ] **Step 5: Commit.**

```bash
git add resources/views/dashboard.blade.php
git commit -m "feat(dashboard): treasury-style layout with charts and activity feed"
```

---

## WAVE 2 — List / index pages

Apply the same recipe to each list view: wrap the page in a `.tm-page-head` (title + breadcrumb), convert primary action buttons to `.tm-btn`, convert the data table to `.tm-table` with `.tm-badge` status pills, and style filter controls with `.tm-input`/`.tm-select`. **Per view:** edit markup → `view:cache` (expect success) → log in and confirm the list loads, pagination/search/filter still work, row actions (view/edit/delete/export) still work → commit.

- [ ] **Task 2.1:** `resources/views/resolutions/list.blade.php`
- [ ] **Task 2.2:** `resources/views/ordinances/list.blade.php`
- [ ] **Task 2.3:** `resources/views/minutes/list.blade.php` (and `list_grid.blade.php`)
- [ ] **Task 2.4:** `resources/views/communications/` incoming + outgoing lists
- [ ] **Task 2.5:** `resources/views/sangguniang*` activities lists
- [ ] **Task 2.6:** `resources/views/system/signatories.blade.php`
- [ ] **Task 2.7:** `resources/views/system/user_list.blade.php` + `access_control.blade.php`
- [ ] **Task 2.8:** `resources/views/system/logs.blade.php`
- [ ] **Task 2.9:** `resources/views/archive/*` list views

### Per-view task shape (applies to every Wave 2/3 task)

- [ ] Step 1: Restyle the view's markup using `theme.css` classes (do not change form field `name`s, action URLs, `@if(hasPermission)` gates, or JS hooks/ids).
- [ ] Step 2: `~/.config/herd/bin/php84/php.exe artisan view:cache` — Expected: success. Then `view:clear`.
- [ ] Step 3: Log in locally; confirm the page loads and every action (links, search, filters, submit, modals, export) behaves exactly as before.
- [ ] Step 4: Screenshot; compare to the design language.
- [ ] Step 5: Commit `git commit -m "style(<module>): treasury restyle of <view>"`.

---

## WAVE 3 — Forms & modals

Same per-view task shape. Style inputs/selects/textareas with `.tm-input`/`.tm-select`, buttons with `.tm-btn`, and lay out form sections in `.tm-card`. **Critical:** never rename `name` attributes, change action URLs/methods, or remove CSRF — these bind to controllers.

- [ ] **Task 3.1:** `resolutions/add.blade.php` (+ view.blade.php)
- [ ] **Task 3.2:** `ordinances/add.blade.php` (+ view)
- [ ] **Task 3.3:** `minutes/add.blade.php` (+ view)
- [ ] **Task 3.4:** communications add/view forms
- [ ] **Task 3.5:** sangguniang activities add/view forms
- [ ] **Task 3.6:** signatories + users add/edit modals
- [ ] **Task 3.7:** the global-search modal in `template.blade.php`

---

## WAVE 4 — Polish

- [ ] **Task 4.1:** `resources/views/login_form.blade.php` — treasury-styled login card.
- [ ] **Task 4.2:** Footer + any leftover Cork chrome; responsive pass (mobile/tablet) across restyled pages.
- [ ] **Task 4.3:** Review PDF/print blades (mPDF templates) — confirm the restyle didn't affect generated documents; adjust only if visibly off.
- [ ] **Task 4.4:** Final full-app walkthrough; confirm no behavior regressions; update the spec's status to "Implemented".

---

## Self-Review

- **Spec coverage:** tokens/components (0.1) ✓; dashboard data (1.1) + layout (1.2) ✓; list pages (Wave 2) ✓; forms/modals (Wave 3) ✓; login/polish/PDF (Wave 4) ✓; data-safety enforced by Global Constraints + per-task functional checks ✓.
- **Placeholder scan:** foundational tasks (0.1, 1.1, 1.2) carry concrete code; Wave 2/3 per-view tasks share one explicit task shape (restyle → compile → functional check → screenshot → commit) rather than repeating boilerplate.
- **Type consistency:** view variables produced in 1.1 match those consumed in 1.2; `.tm-*` class names are defined once in 0.1 and reused thereafter.
