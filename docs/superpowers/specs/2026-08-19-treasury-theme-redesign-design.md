# Design Spec — Treasury-Style Theme Redesign & Dashboard Overhaul

**Date:** 2026-08-19
**Branch:** `redesign/treasury-theme`
**Status:** Approved design; pending implementation plan

## 1. Goal

Restyle the Prieto Diaz Document Tracker (Laravel 10 + Bootstrap 5, "Cork"
admin template) to the professional, modern look & feel of the sibling
**Treasury Management System** (`treasury/`), and rebuild the Dashboard with
KPIs, charts, and a live activity-history feed.

Non-negotiable constraint: **the redesign is view-layer only. It must not
change, migrate, or risk any data.**

## 2. Scope

- **In scope:** Blade view markup (classes/structure), a shared `theme.css`,
  the already-shipped `topnav.css`, and read-only additive queries in the
  dashboard controller. ~35 views across dashboard, list pages, forms/modals,
  and the login page.
- **Out of scope:** Database schema, migrations, models, routes, business
  logic, permissions, controllers' write paths. No changes to how documents
  are stored, uploaded, or exported.

## 3. Data-safety approach

1. **View + CSS only.** The only controller change is the Dashboard gaining
   read-only `count()` / aggregate / `latest()` queries. No writes anywhere.
2. **No DB changes.** No migrations, no column edits, no seeders.
3. **Isolated branch** `redesign/treasury-theme`, committed per wave — every
   step reversible via git.
4. **Additive CSS.** The existing Cork CSS stays loaded. `theme.css` layers on
   top, so any not-yet-restyled view keeps rendering correctly during the
   phased rollout.
5. **Local-first.** All work verified against the local `document_tracker`
   database (production data copy). Production is untouched until deployed.

## 4. Design tokens (ported from Treasury)

- **Colors:** primary `#427AB5`, secondary `#406AAF`, accent `#F7DD7D`,
  success `#0FA958`, danger `#DC3545`, warning `#E84E46`, dark `#333`,
  background `#F0F2F5`, muted text `#7A7777`.
- **Typography:** Manrope (body/UI), Archivo (headings & numeric emphasis).
  Both free Google fonts. (Treasury's "Obviously" is paid; Archivo is the
  closest free substitute.)
- **Shape language:** 12px card radius, 1px subtle border + soft shadow,
  uppercase buttons, pill-shaped status badges.
- Tokens defined once as CSS custom properties in `theme.css`, namespaced to
  avoid clobbering Cork's own variables.

## 5. Component system (in `theme.css`)

Buttons (primary/secondary/accent/success/danger/warning/dark), cards, tables
(header, rows, hover, empty state), status badges/pills, form inputs & selects,
modals, page headers/breadcrumbs, KPI stat tiles, activity-feed list.

## 6. Dashboard design

Two-column layout under the top nav:

- **KPI row (5 tiles):** Resolutions, Ordinances, Minutes, Communications,
  Sangguniang Activities — each total + approved/sub count.
- **Main column:** "Documents Created per Month" (bar) → "Records by Type"
  (donut) + "Approval Status" (horizontal stacked bar) → "Recent Documents"
  table (merged resolutions + ordinances, latest first).
- **Right sidebar:** "Quick Actions" (New Resolution/Ordinance/Minutes,
  Search) stacked above a scrollable "Activity History" feed.

Charts via the already-bundled **ApexCharts** (no new dependency). Status pie
charts intentionally avoided — data is ~99% "Approved"; volume/trend framing
reads better.

### Dashboard data mapping (all existing tables, read-only)

| Widget | Source | Query |
|---|---|---|
| KPI tiles | resolutions, ordinances, minutes, communications, sangguniang_activities | `count()` with `is_deleted=0, is_archived=0` (+ approved counts) |
| Documents per month | resolutions + ordinances | group by `MONTH(date_created)`, current year |
| Records by type | the 5 counts above | — |
| Approval status | resolution_status / ordinance_status counts | already in controller |
| Recent Documents | existing `$merged` collection | sort by date desc, limit ~8 |
| Activity History | `activity_logs` (`action`, `user_id`, `activity_date`) join `users` | `latest()->limit(~12)` |

## 7. Phased rollout

| Wave | Scope | Risk |
|---|---|---|
| 0 — Foundation | `theme.css` tokens + components; top nav (done) | low |
| 1 — Dashboard | new layout, 3 charts, activity feed; additive controller queries | low |
| 2 — List pages | resolutions, ordinances, minutes, communications, activities, signatories, users, logs, archive | medium |
| 3 — Forms & modals | add/edit views + global search modal | medium |
| 4 — Polish | login page, footer, PDF/print templates, responsive pass | low |

Each wave: implement → verify locally (screenshots) → commit.

## 8. Testing & verification

- Compile all Blade views (`view:cache`) after each wave — zero syntax errors.
- Visually verify each restyled view against the mockup at desktop + mobile widths.
- Confirm all existing functionality still works (links, forms submit, modals,
  permissions gating, PDF export) — no behavior change, only presentation.
- Confirm dashboard numbers match direct DB queries.

## 9. Rollback

Any wave is a discrete commit on `redesign/treasury-theme`. Revert the commit
or the branch to restore the prior look. Because Cork CSS remains loaded and no
data/logic changed, rollback is purely cosmetic and safe.

## 10. Reference

- Mockup: `scratchpad/dashboard-mockup.html` (throwaway preview, real numbers).
- Source design system: `treasury/resources/css/app.css`,
  `treasury/resources/views/components/layout.blade.php`.
