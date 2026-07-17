# wpmvc-debug

Debug toolbar for the WPMVC framework.

## Assets

`assets/` contains compiled files only. The sources (SCSS/JS) and the webpack build
live in the `wpmvc-dev` repository under `assets/wpmvc-debug/` — build from there
with `npm run build` (or `npm run dev` for watch mode).

The CSS is Bootstrap 5.3 compiled with a `wpmvc-` prefix on every class and CSS
variable, so the toolbar cannot clash with a site that already loads Bootstrap:

- Markup must use prefixed classes: `wpmvc-btn`, `wpmvc-card`, `wpmvc-nav-tabs`, ...
- Light/dark theme is switched via the `data-bs-theme="light|dark"` attribute on the
  toolbar root element (`.wpmvc-debug`) — the attribute name comes from Bootstrap
  and is not affected by the prefix.
- Bootstrap's JS is not bundled (it toggles unprefixed class names); all toolbar
  behavior is custom JS in `assets/js/main.js`.
