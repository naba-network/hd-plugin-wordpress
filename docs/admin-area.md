# Admin area — API token, client portal link & status panel

## Overview

The plugin admin area (**Gamecenter** menu, icon `admin/nova-stats-brand-logo-monochrom.svg`, inlined
as a `data:image/svg+xml` icon_url so WordPress sizes it like a dashicon) is intentionally minimal.
All league/season configuration, the HockeyData credentials and the Gamecenter page URL
(`clientConfig.gamecenterHostUrl`, GameCenter → Configuration) live in the **Nova Stats client
portal**; the WordPress site stores only the account **API token**. The Gamecenter Vue app reads
that token and fetches everything else (leagues, seasons, per-team HockeyData credentials, feature
flags, the client config) from the Nova Stats backend at runtime — see `naba-hdwp-widgets`'s
`docs/gamecenter-client-config.md`.

## Pages

The "Setup" submenu is registered with its own slug (`nova_stats_options_setup`), distinct from the
top-level menu's own slug (`nova_stats_options`). WordPress hides a submenu entirely when it has
exactly one entry whose slug matches the parent's — which is what happens in production, where
`WP_DEBUG` is off and "Setup" is the only submenu page. Giving it a separate slug keeps the
submenu (and therefore "Setup") visible regardless of `WP_DEBUG`.

Adding a submenu with a different slug makes WordPress auto-insert a duplicate entry for the parent
slug (labelled with the menu title, "nova·stats", same page as "Setup"). `nova_stats_admin_menu()`
removes it via `remove_submenu_page()`, so the submenu shows only "Setup" (+ "Debug"), and the
top-level menu item links to `nova_stats_options_setup`.

### Configuration (`Gamecenter` → `Configuration`)

Two blocks, rendered by `templates/admin/admin.php`:

1. **Client portal link** — button opening the locale-aware portal URL
   (`https://nova-stats.com/{de|en}/client-portal`). Locale is derived from `get_locale()`.
2. **API Token form** — above the field, a connection status (round avatar icon + one line of
   text) built from `StatusService::checkConnection()` (same 60-second cached check the Debug page
   uses, keyed by token hash, so a newly saved token is re-checked right away):
   - **valid** — MDI `check-decagram`, green: "API token valid – connection established."
   - **invalid** — MDI `lock`, red: "API token invalid – not connected." + the backend message.
   - **unreachable** — MDI `lock`, amber: "Connection could not be checked – nova·stats is
     unreachable." + the HTTP error.
   - **missing** — MDI `lock`, slate: "No API token saved yet – not connected."

   Icons are inlined SVG paths (no icon font/dependency); colors are the portal's
   `success`/`error`/`warning`/`secondary`, in `admin/admin.css` (`.nova-stats-connection--*`).
   Below that, a single password field (with a Show/Hide toggle) bound to the WordPress
   Settings API option `nova_stats_db_setting__api_key` (group `nova_stats_db_settings_group`,
   sanitized with `sanitize_text_field`). Saving posts to `options.php` — WordPress core handles
   the nonce.

A previous version of this page also had a "Gamecenter Link" form (a manually-entered URL the
schedule slider/team page shortcodes linked back to). That field was removed: the URL now comes from
the Nova Stats backend's `clientConfig.gamecenterHostUrl` instead, set once in the client portal.

Styled with Bootstrap 5.3.8 (`admin/vendor/bootstrap.min.css`, vendored locally) instead of a
custom stylesheet — see [Styling](#styling) below.

### Debug (`Gamecenter` → `Debug`) — only when `WP_DEBUG` is enabled

Rendered by `templates/admin/debug.php` as a single Bootstrap **Status** `card` (half width from
the `lg` breakpoint, 992px, full width below) — see below.

## Status panel

Built by `src/Service/StatusService.php`, rendered on the Debug page.

### Live connection check

`checkConnection()` calls
`GET https://nova-stats.com/api/v1/validate-api-token?token={token}` server-side and reports:

- **Connected** — token valid; shows the number of configured leagues.
  Success is signalled by the backend when the response `message` is an empty string.
- **Rejected** — backend returned a `message` (e.g. invalid/inactive/expired token, or a referrer
  mismatch).
- **Unreachable** — HTTP/transport error.
- **No token** — nothing configured yet.

The result is cached in a 60-second transient keyed by a hash of the token; the **Re-check** link
(nonce-guarded GET) forces a refresh.

> **Referrer requirement.** The request sends `Referer: <site URL>`. In production the backend
> rejects the call unless the request origin/referer contains the token's configured referrer
> URL. This makes the check double as a verification that the portal-side referrer is set
> correctly for **this** domain — the most common real-world misconfiguration. The site URL to
> enter in the portal is shown in the diagnostics as **Site referrer**.

### Local diagnostics

`getDiagnostics()` reports: plugin version + whether a newer GitHub release is available (read
from the `update_plugins` site transient populated by the update checker); PHP version (flagged
if `< 8.3`) and WordPress version; and the site referrer value.

## Styling

Both admin pages (Configuration, Debug) use Bootstrap 5.3.8, vendored locally at
`admin/vendor/bootstrap.min.css`, plus a small theme layer `admin/admin.css`. Both are enqueued in
`AdminController::nova_stats_admin_enqueue_scripts()` whenever any `nova_stats_options*` page loads
(the theme layer after Bootstrap). The markup uses only Bootstrap component/utility classes (`card`,
`btn`, `form-control`/`input-group`, `badge text-bg-*`, `table`, grid/`row`/`col`).

`admin/admin.css` aligns the pages with the client portal by overriding Bootstrap's CSS variables,
scoped to the `.nova-stats-admin` wrapper each template renders:

- **Page background** `#f4f4f4` (portal `$color-background`).
- **Cards** white, `#e1e2e3` border, 12px radius, same shadow as the portal `card()` mixin.
- **Buttons** black: primary actions use `btn-dark`, secondary ones `btn-outline-dark`, both
  tinted to the portal's `primary` `#212121`.

The values are copied literals — keep them in sync with
`nova-stats-client-portal/src/setup/scss/variables/_project.scss`, `mixins/_card.scss` and
`plugins/vuetify.ts` if the portal palette changes.

UI copy follows `TERMINOLOGY.md` at the repo root (`nova·stats`, `hockeydata`, `Gamecenter`).

## Configuration constants / hooks

Defined in `src/Constant/PluginConstants.php`:

- `NOVA_STATS_API_BASE_URL` (`https://nova-stats.com`) — overridable via the
  `nova_stats_api_base_url` filter.
- `VALIDATE_TOKEN_PATH`, `CLIENT_PORTAL_PATH`, `MIN_PHP_VERSION`.

## Data injected into the frontend

`Settings::getSessionInitialData()` returns `['apiToken' => <token>]`, injected as
`window.initialData.session` by the shortcode templates. The frontend store reads
`session.apiToken` and fetches everything else, including the client config, from the backend.
