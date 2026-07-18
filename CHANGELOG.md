# Changelog

The package version lives in `wpmvc\debug\Debug::VERSION` (also used for
asset cache busting). While in `0.x`, versioning is: **minor** (`0.x.0`) for
new features and changes, **patch** (`0.0.x`) for bug fixes and internal
refactors. `1.0.0` is reserved for when the feature set is considered
complete.

## 0.6.0

- **New tab: Cache.** Three sub-tabs of what WordPress's caching layers
  actually expose: **Object Cache** (backend class, persistent-drop-in
  detection, this request's hits/misses/hit rate, and the per-group contents
  with item counts and approximate sizes — read via reflection, since core's
  `WP_Object_Cache::$cache` is private), **Transients** (every transient in
  the options table with scope, expiry state and size, capped at the 200
  largest), and **Autoloaded Options** (count + combined size, and the 30
  largest). Admin-only actions via `wp_ajax_wpmvc_debug_cache` (nonce +
  capability checked): flush the object cache — shown only when a
  persistent drop-in is active — and delete expired transients.
  Deliberately omitted from the concept design as unmeasurable in a
  per-request tool: per-store hit rates, activity/eviction history charts,
  and page/browser caches (they live outside PHP).

## 0.5.0

- **New tab: Events.** The WordPress actions and filters fired during the
  request, captured via the `all` hook from the moment the component boots
  (`collectors\Hook_Collector`). Fires of the same hook are aggregated into
  one row (fire count + summed execution time, measured per fire with a
  self-registered `PHP_INT_MAX`-priority end callback on the hook), and each
  row expands to timing details plus the registered callbacks (priority,
  callback, source, file:line — resolved via reflection at render time).
  High-frequency noise hooks (translations, escaping, option/transient
  reads) are excluded, unique hooks are hard-capped at 500, and capture is
  skipped in admin/AJAX/cron requests. The list is sortable by the Time
  column (longest first → shortest first → chronological). A **Timeline**
  sub-tab charts every hook as a bar — offset at its first fire within the
  request, width proportional to the total time spent in its callbacks.
- New generic hook: `[data-wpmvc-debug-sort="<key>"]` column-header buttons
  sort the `[data-wpmvc-debug-item]` rows in the same (sub-)pane by their
  numeric `data-wpmvc-debug-sort-<key>` attribute, cycling
  descending → ascending → original order.
- Database: the query list is sortable by the Time column too (same
  control as Events).

## 0.4.2

- Scheduled Jobs: moved the run/delete actions out of the row and into the
  expanded accordion body as labelled buttons (bottom-left).

## 0.4.1

- Refactor: each tab now owns its AJAX handlers via `Tab::register_ajax()`
  (called during `Debug` init). Moved the clear-log handler into `Logs_Tab`
  and the run/delete-job handler into `Scheduled_Jobs_Tab`; `Debug` no
  longer holds tab-specific endpoint logic. Shared `Tab::verify_ajax()`
  guard (admin + nonce) and `Debug::NONCE` constant.

## 0.4.0

- **New tab: Scheduled Jobs (WP-Cron).** Lists every scheduled event from
  `_get_cron_array()` with next run, recurrence, arguments and status, plus
  summary stats. Admin-only per-row actions to run a job immediately or
  delete it, via the `wp_ajax_wpmvc_debug_job` handler (nonce + capability
  checked; args are looked up server-side, never taken from the client).

## 0.3.4

- Floating button: replaced the "WPMVC" text with the "W" logo as an inline
  SVG (rounded-stroke mark, inherits the button's white color).

## 0.3.3

- Overview: zeroed the card-body padding on the Applications and Components
  lists so rows sit flush to the card edges; header/rows keep their own inset.

## 0.3.2

- Overview: summary-card labels now use the emphasis color (black in light,
  white in dark) for better contrast; the icon keeps the primary accent.
- Overview: "Core Components" preview shows 5 components instead of 6.

## 0.3.1

- Overview: moved the "View all …" links into a card footer — always
  bottom-aligned and full width.

## 0.3.0

- **Overview: richer layout.** Icons on the summary cards, plus two new
  cards — "Loaded Applications" and "Core Components" (up to 6) — each with
  a "View all …" link that jumps to the matching tab.
- New generic hook: `[data-wpmvc-debug-goto="<tab>"]` switches the main tab
  from in-content links.

## 0.2.0

- **Logs: clear buttons.** Each Logs sub-tab has an admin-only "Clear"
  button that empties its log via an AJAX handler
  (`wp_ajax_wpmvc_debug_clear_log`), gated by `manage_options` and a nonce.
- Renamed the "WPMVC Logger" sub-tab to "Logger".

## 0.1.0

Initial debugger.

- Floating button + bottom-anchored modal (header + sidebar tabs + content),
  Bootstrap 5.3 compiled with a `wpmvc-` prefix so it can't clash with the
  host site; light/dark theme with a VitePress-style switch (dark default),
  pin-to-persist and open-tab persistence via `localStorage`.
- Header info chips (PHP/WP versions, request time, peak memory).
- Tabs: Overview, Applications, Components, Database, Logs, Environment.
- Reusable UI hooks: accordions, search/level filtering, sub-tabs.
