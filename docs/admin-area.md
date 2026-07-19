# Admin area — API token, client portal link & status panel

## Overview

The plugin admin area (**Naba HDWP** menu) is intentionally minimal. All league/season
configuration and the HockeyData credentials live in the **Nova Stats client portal**; the
WordPress site stores only the account **API token**. The Gamecenter Vue app reads that token
and fetches everything else (leagues, seasons, per-team HockeyData credentials, feature flags)
from the Nova Stats backend at runtime.

## Pages

### Configuration (`Naba HDWP` → `Configuration`)

Three blocks, rendered by `templates/admin/admin.php`:

1. **Client portal link** — button opening the locale-aware portal URL
   (`https://datahub.h-sc.at/{de|en}/client-portal`). Locale is derived from `get_locale()`.
2. **API Token form** — a single password field (with a Show/Hide toggle) bound to the WordPress
   Settings API option `naba_hdwp_db_setting__api_key` (group `naba_hdwp_db_settings_group`,
   sanitized with `sanitize_text_field`). Saving posts to `options.php` — WordPress core handles
   the nonce.
3. **Status panel** — see below.

### Documentation (`Naba HDWP` → `Documentation`)

Lists the available shortcodes. (Content to be expanded later.)

### Debug (`Naba HDWP` → `Debug`) — only when `WP_DEBUG` is enabled

Dumps the raw request URL, HTTP status and JSON body of the token-validation call for deep
troubleshooting (`templates/admin/debug.php`).

## Status panel

Built by `src/Service/StatusService.php`.

### Live connection check

`checkConnection()` calls
`GET https://datahub.h-sc.at/api/v1/validate-api-token?token={token}` server-side and reports:

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
if `< 8.3`) and WordPress version; whether the frontend build (`frontend/app/manifest.json` and
`frontend/compact/manifest.json`) is present; the site referrer value; and the three shortcodes.

## Configuration constants / hooks

Defined in `src/Constant/PluginConstants.php`:

- `NOVA_STATS_API_BASE_URL` (`https://datahub.h-sc.at`) — overridable via the
  `naba_hdwp_api_base_url` filter.
- `VALIDATE_TOKEN_PATH`, `CLIENT_PORTAL_PATH`, `MIN_PHP_VERSION`.

## Data injected into the frontend

`Settings::getSessionInitialData()` returns `['apiToken' => <token>]`, injected as
`window.initialData.session` by the shortcode templates. The frontend store reads
`session.apiToken`.
