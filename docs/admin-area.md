# Admin area — API token, client portal link & status panel

## Overview

The plugin admin area (**Gamecenter** menu, icon `admin/nova-stats-brand-logo-monochrom.svg`, inlined
as a `data:image/svg+xml` icon_url so WordPress sizes it like a dashicon) is intentionally minimal.
All league/season
configuration and the HockeyData credentials live in the **Nova Stats client portal**; the
WordPress site stores only the account **API token**. The Gamecenter Vue app reads that token
and fetches everything else (leagues, seasons, per-team HockeyData credentials, feature flags)
from the Nova Stats backend at runtime.

## Pages

### Configuration (`Gamecenter` → `Configuration`)

Three blocks, rendered by `templates/admin/admin.php`:

1. **Client portal link** — button opening the locale-aware portal URL
   (`https://nova-stats.com/{de|en}/client-portal`). Locale is derived from `get_locale()`.
2. **API Token form** — a single password field (with a Show/Hide toggle) bound to the WordPress
   Settings API option `nova_stats_db_setting__api_key` (group `nova_stats_db_settings_group`,
   sanitized with `sanitize_text_field`). Saving posts to `options.php` — WordPress core handles
   the nonce.
3. **Gamecenter Link form** — a single URL field bound to the Settings API option
   `nova_stats_db_setting__gamecenter_base_url` (same group, sanitized with `sanitize_url`): the page
   on this site where `[Gamecenter]` is placed, so the schedule slider and team page
   shortcodes can link back to it. See
   [Reusable initial data — Gamecenter link](reusable-initial-data.md).

Styled with Bootstrap 5.3.8 (`admin/vendor/bootstrap.min.css`, vendored locally) instead of a
custom stylesheet — see [Styling](#styling) below.

### Debug (`Gamecenter` → `Debug`) — only when `WP_DEBUG` is enabled

Rendered by `templates/admin/debug.php` as two Bootstrap `card`s in a `row` / `col-lg-6` grid,
stacking to full width below the `lg` breakpoint (992px):

1. **Status card** — see below.
2. **Raw Response card** — dumps the raw request URL, HTTP status and JSON body of the
   token-validation call for deep troubleshooting.

## Status panel

Built by `src/Service/StatusService.php`, rendered on the Debug page.

### Live connection check

`checkConnection()` calls
`GET https://nova-stats.com/api/v1/validate-api-token?token={token}` server-side and reports:

- **Connected** — token valid; shows the number of configured leagues and active feature keys.
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
if `< 8.3`) and WordPress version; the CDN host/version the `embed` build is currently loaded from
(`PluginConstants::EMBED_CDN_HOST`/`EMBED_CDN_VERSION`, see
[CDN embed assets](cdn-embed-assets.md)); the site referrer value; and the three shortcodes.

## Styling

Both admin pages (Configuration, Debug) share a single stylesheet: Bootstrap
5.3.8, vendored locally at `admin/vendor/bootstrap.min.css` and enqueued unconditionally in
`AdminController::nova_stats_admin_enqueue_scripts()` whenever any `nova_stats_options*` page loads.
There is no plugin-specific admin CSS file — the markup uses only Bootstrap component/utility
classes (`card`, `btn`, `form-control`/`input-group`, `badge text-bg-*`, `table`, grid/`row`/`col`),
so there is nothing custom to keep in sync when Bootstrap is upgraded.

## Configuration constants / hooks

Defined in `src/Constant/PluginConstants.php`:

- `NOVA_STATS_API_BASE_URL` (`https://nova-stats.com`) — overridable via the
  `nova_stats_api_base_url` filter.
- `VALIDATE_TOKEN_PATH`, `CLIENT_PORTAL_PATH`, `MIN_PHP_VERSION`.

## Data injected into the frontend

`Settings::getSessionInitialData()` returns `['apiToken' => <token>]`, injected as
`window.initialData.session` by the shortcode templates. The frontend store reads
`session.apiToken`. Every shortcode template also injects
`Settings::getGamecenterBaseUrl()` as `window.initialData.gamecenterBaseUrl` (see
[Reusable initial data — Gamecenter link](reusable-initial-data.md)).
