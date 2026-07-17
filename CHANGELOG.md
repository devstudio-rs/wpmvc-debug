# Changelog

The package version lives in `wpmvc\debug\Debug::VERSION` (also used for
asset cache busting). While in `0.x`, versioning is: **minor** (`0.x.0`) for
new features and changes, **patch** (`0.0.x`) for bug fixes and internal
refactors. `1.0.0` is reserved for when the feature set is considered
complete.

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
