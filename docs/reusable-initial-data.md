# Reusable initial data — Gamecenter link

The account **API token** was already "set once": the Configuration page stores it a single time
(`Settings::FIELD_API_KEY`), and every shortcode's inline bootstrap script re-emits it into
`window.initialData.session.apiToken`, no matter how many shortcodes are placed on the site.

A second value now follows the same pattern: **Gamecenter Link**
(`Settings::FIELD_GAMECENTER_BASE_URL` / `getGamecenterBaseUrl()`) — the page on this site where
`[Gamecenter]` is placed. The schedule slider (`[Gamecenter-Schedule-Slider]`) and team page
(`[Gamecenter-Team-Page]`) shortcodes use it to render a link back to the main Gamecenter (see
`naba-hdwp-widgets`' `docs/reusable-initial-data.md` for how the frontend consumes it).

## Why a WordPress setting, not something derived automatically

WordPress has no reliable way to know which page a shortcode ends up rendered on ahead of time (a
shortcode can be placed in a template, a widget, a block, or programmatically), so this can't be
auto-detected — the site owner has to say where they put the Gamecenter shortcode, once.

## Where it's set and injected

- **Set:** Configuration page (`Gamecenter` → `Configuration`), "Gamecenter Link" card
  (`templates/admin/admin.php`), a plain URL field in its own `<form>`, bound to its own Settings API
  group (`Settings::DB_GROUP_NAME_GAMECENTER`), sanitized with `sanitize_url`. Empty by default — the
  frontend simply doesn't render the link until this is filled in.
- **Injected:** every shortcode's `Shortcode\*` class now also builds
  `PluginConstants::INITIAL_DATA_KEY_GAMECENTER_BASE_URL => Settings::getGamecenterBaseUrl()`
  (alongside the existing `sessionData`), and every shortcode template's inline bootstrap `<script>`
  (`templates/shortcodes/*.php`) sets `window.initialData` at that same key from it — the same
  script, same mechanism that already sets `window.initialData.session`. It's injected
  unconditionally, including on the `[Gamecenter]` shortcode itself, even though that
  widget has no use for it — keeping all three shortcode templates identical in shape was preferred
  over a special case for one of them.

### Why its own settings group, not `Settings::DB_GROUP_NAME`

The Gamecenter Link field is saved from a **separate `<form>`** on the Configuration page, next to —
not inside — the API Token form. WordPress's `options.php` handler resets *every* option registered
in the **submitted** group to empty if that option isn't present in that particular form's `$_POST`.
Registering both fields under the same group would mean saving the API Token form silently wipes the
Gamecenter Link (and vice versa), since each form only posts its own field. Each field's group must
therefore match one `<form>` exactly.

## Not part of `getSessionInitialData()`

`gamecenterBaseUrl` is a page-level `window.initialData` key, not nested under `session` like
`apiToken` — matching how the CDN embed build's own runtime API overrides
(`novaStatsApiUrl`/`hockeyDataApiUrl`) are structured on the frontend side. `Settings::getGamecenterBaseUrl()`
is therefore a separate method from `getSessionInitialData()`, not folded into it.
