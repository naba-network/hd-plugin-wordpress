# Hockey Data Changelog

## Unreleased

* [BREAKING] Removed the "Gamecenter Link" admin setting (`Settings::getGamecenterBaseUrl()`,
  `FIELD_GAMECENTER_BASE_URL`, its admin card in the Configuration page). The schedule
  slider/team page/Gamecenter widgets now get their "link back to the main Gamecenter" URL directly
  from the Nova Stats backend (`clientConfig.gamecenterHostUrl`) instead of this manually-entered
  field. Set it once in the Nova Stats client portal's GameCenter → Configuration page instead; the
  old option is deleted on uninstall.
* [ENHANCEMENT] The Gamecenter admin menu now uses the Nova Stats brand logo
  (`admin/nova-stats-brand-logo-monochrom.svg`) as its icon instead of the default dashicon,
  inlined as a base64 `data:image/svg+xml` icon_url (`AdminController::getMenuIconDataUri()`) so
  WordPress applies its own `.svg` menu-icon sizing instead of rendering it at native size.
* [BREAKING] Removed the admin "Documentation" page (`nova_stats_options_documentation` submenu,
  `AdminController::nova_stats_admin_settings_documentation()`, `templates/admin/documentation.php`)
  — no longer needed.
* [BREAKING] Renamed the plugin from "NovaStats HockeyData" to **Gamecenter** and replaced the
  internal `NabaHdwp`/`NABA_HDWP` naming with `NovaStats\Gamecenter`/`NOVA_STATS`:
  - PHP namespace `NabaHdwp` → `NovaStats\Gamecenter`.
  - Global constants `NABA_HDWP_VERSION`/`PLUGIN_NAME`/`PLUGIN_URL`/`PLUGIN_PATH`/`GITHUB_TOKEN` →
    `NOVA_STATS_*`. The local/staging override wp-config defines (embed CDN host, API base URL,
    HockeyData URL, update source) → `NOVA_STATS_LOCAL_*` to avoid colliding with
    `PluginConstants::NOVA_STATS_API_BASE_URL`.
  - Database option names `naba_hdwp_db_setting__api_key` / `naba_hdwp_db_setting__gamecenter_base_url`
    (and their settings groups) → `nova_stats_db_setting__*`. `uninstall.php` now only deletes these
    two (the previously-listed `naba_hdwp_db_setting__hd_api_key` / `__hd_referrer` /
    `__league_settings` keys were already dead/unused and have been dropped).
  - Admin page slugs `naba_hdwp_options*`, the `naba_hdwp_recheck` query arg, and the
    `naba_hdwp_api_base_url` filter → `nova_stats_*`. Admin menu label "Naba HDWP" → "Gamecenter".
  - Shortcode tags `[Naba-Hdwp-Gamecenter]` / `[Naba-Hdwp-Schedule-Slider]` / `[Naba-Hdwp-Team-Page]`
    → `[Gamecenter]` / `[Gamecenter-Schedule-Slider]` / `[Gamecenter-Team-Page]`.
  - No backwards compatibility: existing installs lose their saved API token/Gamecenter link
    setting and must re-enter them, and any site content using the old shortcode tags must be
    updated manually. The GitHub repository, plugin directory/slug (`hd-plugin-wordpress`), and the
    update-checker source URL are unchanged.

* [BUGFIX] The Debug page's "Frontend assets" diagnostic (`StatusService::getDiagnostics()['embed']`)
  reported the hardcoded production CDN host even when the local/staging
  `NABA_HDWP_EMBED_CDN_HOST` override was active, instead of the host `VueService` actually loads
  from. `StatusService` now has its own `getEmbedCdnHost()` (mirroring `VueService`'s) that resolves
  the same override, matching how `getApiBaseUrl()` already resolves `NABA_HDWP_API_BASE_URL`.
* [BREAKING] All three shortcodes now load the Gamecenter widget's `embed` build from the Cloudflare
  CDN (`https://cdn.statistics.stream`, pinned to `latest`) instead of a `frontend/` directory
  bundled into the plugin at build time. `VueService::enqueueAssets()` (manifest-based, per
  `app`/`compact` build type) was replaced by `enqueueEmbedAssets()`; the shortcode templates now
  render the `<nova-stats-gamecenter>`/`<nova-stats-schedule-slider>`/`<nova-stats-team-page>`
  custom elements instead of the old `#app`/`#appWpc` SPA mount markup. Removed the
  `@naba-network/hd-vue-gamecenter` npm dependency, the `frontend:*`/`link:local` scripts, and the
  vendored `frontend/` directory. New optional `NABA_HDWP_EMBED_CDN_HOST` wp-config define for
  local/staging testing. See [docs/cdn-embed-assets.md](docs/cdn-embed-assets.md).
* [BUGFIX] `PluginConstants::NOVA_STATS_API_BASE_URL` now points at `https://nova-stats.com` instead
  of the retired `https://datahub.h-sc.at`. See `docs/admin-area.md`.
* [FEATURE] Added a "Gamecenter Link" field to the Configuration page (`templates/admin/admin.php`,
  new `Settings::FIELD_GAMECENTER_BASE_URL` / `getGamecenterBaseUrl()`), so the page URL where
  `[Naba-Hdwp-Gamecenter]` is placed only needs to be set once. Registered under its own settings
  group (`Settings::DB_GROUP_NAME_GAMECENTER`), not the API token's group — the field is saved from
  a separate `<form>`, and sharing a group across two independent forms would make saving either one
  reset the other to empty (WordPress's `options.php` resets every option in the *submitted* group
  that isn't present in that form's `$_POST`). All three shortcode templates now also inject it as
  `window.initialData.gamecenterBaseUrl` (via `PluginConstants::INITIAL_DATA_KEY_GAMECENTER_BASE_URL`);
  the Gamecenter widget's schedule slider and team page shortcodes use it to link back to the main
  Gamecenter. See [docs/reusable-initial-data.md](docs/reusable-initial-data.md).
* [ENHANCEMENT] Migrated the Configuration and Debug admin pages
  (`templates/admin/admin.php`, `templates/admin/debug.php`) from custom CSS to Bootstrap 5.3.8
  (already bundled locally at `admin/vendor/bootstrap.min.css` and already used by the
  Documentation page), for visual consistency across all three admin pages and to drop the
  custom purple headers / green buttons in favor of Bootstrap's default theme. Deleted
  `admin/css/admin-base-styles.css` and `admin/css/admin-settings.css` and their
  `wp_enqueue_style()` calls in `AdminController.php`; Bootstrap CSS is now enqueued on all
  `naba_hdwp_options*` admin pages. The Debug page's two-card row now uses Bootstrap's grid
  (`row`/`col-lg-6`) instead of the removed `.naba-hdwp-row`/`.naba-hdwp-col-6` classes. All
  `.card` elements use `p-0` to remove Bootstrap's default card padding (the card-header/
  card-body already provide their own).
* [ENHANCEMENT] Moved the **Status** panel (connection check + diagnostics) from the Configuration
  page to the `WP_DEBUG`-only Debug page, next to the raw token-validation response. Both are now
  rendered as cards in a responsive row (`templates/admin/debug.php`, Bootstrap's `row` /
  `col-lg-6` / `col-12` grid classes): side by side (`col-lg-6` each) from the `lg` breakpoint up,
  stacking to full width (`col-12`) below it (<992px).
* [BUGFIX] The Debug page's root wrapper (`templates/admin/debug.php`) lost WordPress core's
  `wrap` class during the Bootstrap migration, regressing standard admin-page spacing/notice
  placement; it's now `class="wrap container-fluid px-0 py-3"`. Also removed a dead
  `$forceRefresh` computation in `AdminController::naba_hdwp_admin_settings_page()` left over from
  when the Status/connection check lived on the Configuration page — it was unused there (the
  real one lives in `naba_hdwp_admin_settings_overview()`) and could needlessly trigger
  `check_admin_referer()`'s die-on-failed-nonce behavior for a stale query string.
* [BUGFIX] The Gamecenter shortcode now mounts `<v-app><router-view /></v-app>` instead of `<l-gamecenter />` (`templates/shortcodes/gamecenter.php`), and the ScheduleSlider/TeamPage shortcodes now wrap their component in `<v-app>`. `<v-app>` provides the Vuetify overlay container that dropdowns/dialogs need to open, and mounting `<router-view />` (rather than the layout component directly) avoids a duplicated layout shell, since `l-gamecenter` is itself the `/gc` route component in the widget.
* [BUGFIX] The admin connection check reported "no leagues" for a valid token even when leagues were configured. The backend wraps API payloads in a `{ data, notifications }` envelope (`ApiResponseWrapperSubscriber`), but `StatusService::requestValidation()` read `leagues`/`message`/`features` at the top level. It now unwraps `data` (falling back to the top level if the envelope is absent). Added a regression test for the enveloped response.
* [FEATURE] Local end-to-end testing support via opt-in wp-config defines (all absent in production, so production behaviour is unchanged; see `docs/local-development.md` + `docs/local-update-testing.md`):
  * `NABA_HDWP_API_BASE_URL` — `StatusService::getApiBaseUrl()` now uses it as the server-side base-URL default (the `naba_hdwp_api_base_url` filter still wins), and `VueService::enqueueRuntimeOverrides()` injects it into `window.initialData.novaStatsApiUrl` so the embedded widget's traffic follows the same backend.
  * `NABA_HDWP_HOCKEYDATA_URL` — injected as `window.initialData.hockeyDataApiUrl`, repointing the widget's HockeyData calls at the backend's dev-only `hockeyDataByPass` proxy for real data from localhost.
  * `NABA_HDWP_UPDATE_SOURCE` — `plugin.php::initUpdater()` points the update checker at a self-hosted metadata JSON (generic, non-VCS checker; release-asset/token setup skipped) so the WordPress update flow can be exercised without cutting a GitHub release.
  * New `link:local` npm script copies the sibling `naba-hdwp-widgets/dist/*` into `frontend/`, so a locally-built widget (with the runtime-override code) can be used before it is published to the npm registry.
  * New constants in `PluginConstants` for the define names and the `window.initialData` override keys.
* [BREAKING] The admin area no longer stores league/season configuration or HockeyData credentials — these now live exclusively in the Nova Stats client portal. WordPress now stores only the account **API token**. `Settings` dropped the `FIELD_HD_API_KEY`, `FIELD_HD_REFERRER` and `FIELD_LEAGUE_SETTINGS` options (and their getters) and the drag-and-drop league editor (`admin/js/admin-league-configurator.js` + bundled `admin/vendor/Sortable.min.js`) was removed. Migration: existing installs keep their stored token; the now-unused `hd_api_key`/`hd_referrer`/`league_settings` option rows become inert and are removed on uninstall (`uninstall.php` already deletes them).
* [BUGFIX] `Settings::getSessionInitialData()` now injects the token under the key `apiToken` (was `apiKey`). The Gamecenter frontend reads `session.apiToken`, so the token was previously never wired through to the app.
* [FEATURE] Added an admin **Status** panel on the Configuration page (`templates/admin/admin.php`, new `src/Service/StatusService.php`): a live token-validation check against the Nova Stats backend (`/api/v1/validate-api-token`, sending `Referer` = site URL so the portal-side referrer is verified for this domain), plus local diagnostics (plugin version + update availability, PHP/WordPress versions, frontend-build presence, site referrer, shortcode list) and a nonce-guarded Re-check action. Added a "Open client portal" link (locale-aware URL).
* [FEATURE] The `WP_DEBUG`-only Debug page now dumps the raw token-validation request/response instead of a static demo-leagues sample (`templates/admin/debug.php`).
* [ENHANCEMENT] New hardcoded, filterable constants in `PluginConstants` (`NOVA_STATS_API_BASE_URL`, `VALIDATE_TOKEN_PATH`, `CLIENT_PORTAL_PATH`, `MIN_PHP_VERSION`); backend base URL overridable via the `naba_hdwp_api_base_url` filter. Documented the admin area under `docs/`.

* [BUGFIX] Fixed the auto-updater in `plugin.php`: it now installs updates from the built `.zip` GitHub release asset (`enableReleaseAssets()`) instead of the `main` branch zipball, which lacks the gitignored `vendor/` and `frontend/` directories and would have installed a broken plugin. Added optional support for a `NABA_HDWP_GITHUB_TOKEN` constant (for private-repo update checks) and documented the setup in `README.md`.
* [BUGFIX] Fixed version/metadata drift: `README.md` stable tag bumped from 0.0.1 to 0.0.4, `generate-test.php` now reads the version dynamically from the `plugin.php` header, and `package.json` name/repository/bugs/homepage URLs corrected from `hp-plugin-wordpress` to `hd-plugin-wordpress`.
* [ENHANCEMENT] `update-version.js` now also updates the `Stable tag` header in `README.md` so version drift cannot recur.
* [ENHANCEMENT] Bundled Bootstrap 5.3.8 CSS and SortableJS 1.15.0 locally under `admin/vendor/` and enqueue them from the plugin instead of the jsdelivr CDN (`AdminController.php`). Anchored the `.gitignore` `vendor/` rule to `/vendor/` so `admin/vendor/` is committed and shipped.
* [BUGFIX] `templates/admin/admin.php` now uses `esc_attr()` instead of `esc_html()` for HTML attribute values (`action=`, `name=`, `value=`).
* [FEATURE] Documented the `[Naba-Hdwp-Team-Page]` shortcode on the admin documentation page (`templates/admin/documentation.php`).
* [ENHANCEMENT] The Debug admin submenu (`naba_hdwp_options_overview`) is now only registered when `WP_DEBUG` is enabled.
* [ENHANCEMENT] Removed the committed generated file `test.html` and added it to `.gitignore` (it is the output of the `generate-test.php` dev tool).

* [ENHANCEMENT] `VueService.php` no longer emits `<link rel="modulepreload">` for the entry's dynamic imports; only static imports are preloaded. Lazily code-split route chunks are now downloaded on demand instead of up front, reducing the initial page weight for visitors.

### v0.0.4 (2026-04-05)

* [BUGFIX] Updated `build.sh` to prevent dotted files (like `.DS_Store` or `.DS_Script`) from being packed into the build zip.

* [BUGFIX] Fixed PHPStan error by changing `getVersionedDocument()` return type from `?string` to `string` in `VueService.php`.
* [FEATURE] Introduced PHPUnit, Mockery, and BrainMonkey for robust unit testing of WordPress interactions without requiring a full WordPress installation. Added a comprehensive test suite for `VueService.php` to ensure asset enqueuing logic behaves correctly.
* [FEATURE] Added `test:unit` script to `package.json` and a `test` script in `composer.json` to streamline the execution of tests (`npm run test:unit` or `composer test`).

* [FEATURE] Refactored `VueService.php` to support module chunk preloading via `<link rel="modulepreload">` for faster frontend rendering.
* [BUGFIX] Fixed manifest caching bug in `VueService.php` that occurred when multiple build types (`app` and `compact`) were enqueued on the same page.
* [FEATURE] Replaced anonymous functions in `VueService.php` hooks with object-oriented methods and implemented a `hooksRegistered` flag to prevent duplicate hook execution.
* [BUGFIX] Added proper JSON parsing error handling to `VueService.php`'s `loadManifest()` method.
* [FEATURE] Centralized the plugin version lookup by adding `PluginConstants::VERSION` and refactored `AdminController.php` to rely on the interface instead of manual definitions.
* [BUGFIX] Fixed an issue where the main script and styles were not output in the DOM when shortcodes were executed. Moved `manifest.json` hook from `wp_head` to `wp_footer` to guarantee output even if assets are enqueued late.
* [BUGFIX] Corrected path construction in `VueService.php` using `rtrim()` to prevent double slashes that could cause `file_exists()` checks to fail in some server environments.

### v0.0.3 (2026-04-04)

* [FEATURE] Created `generate-test.php` script to mock WordPress functions and output a local `test.html` file to test the script and style enqueue logic without needing a WordPress instance.
* [BUGFIX] Updated `PluginConstants.php` and `VueService.php` to use the correct `frontend/app` and `frontend/compact` paths instead of `wp` and `wpc` based on the new frontend build manifest location.

### v0.0.2 (2026-04-04)

* [BUGFIX] Adapted `VueService.php` asset enqueuing logic to match the new frontend build manifest structure, which removed `index.html` as the entry point and simplified chunk management.

* [BUGFIX] Updated `build.sh` to explicitly read `.distinclude` item by item and copy contents using `rsync -aR` so that all directory contents are included properly in the release zip. Added `vendor/`, `CHANGELOG.md`, and `README.md` to `.distinclude`.

* [FEATURE] Introduced `NABA_HDWP_VERSION` constant in `plugin.php` and updated `src/Controller/AdminController.php` to use it instead of reading `package.json`.
* [BUGFIX] Updated `build.sh` to extract the correct version from `plugin.php` instead of the non-existent `naba-hdwp-widgets.php`.
* [FEATURE] Updated `update-version.js` to automatically update the new `NABA_HDWP_VERSION` constant.

* [FEATURE] Replaced `.distignore` with `.distinclude` in `build.sh` to explicitly define which files are included in the package rather than listing exclusions.

* [FEATURE] Improved `release:commit` script in `package.json` to only commit tracked files and include a cleaner commit message format ("Release v0.0.1"). Usage: `npm run release:commit`.

* [FEATURE] Added `release:tag` script in `package.json` to automatically create and push a git tag for the current version. Usage: `npm run release:tag`.

* [BUGFIX] Added missing `release` script to `package.json` to enable `npm run release` command.

* [FEATURE] Refactored `AdminController.php` to dynamically read the plugin version from `package.json` instead of using a hardcoded string.

* [FEATURE] Updated `update-version.js` to automatically calculate semver version bumps. It now accepts `major`, `minor`, `patch`, or no argument (defaults to `patch`). Usage: `npm run release [major|minor|patch|<version>]`.
* [FEATURE] Added `update-version.js` script to automate updating the plugin version across `package.json`, `plugin.php`, and `CHANGELOG.md`. Usage: `npm run release <version>`.

* [BUGFIX] Updated build script to create frontend directory before copying files.
* [BUGFIX] Removed hardcoded `NODE_AUTH_TOKEN` from `.npmrc` to allow local `npm ci` after `npm login`.
* [BUGFIX] Added `packages: read` permission to the GitHub Actions workflow to allow reading packages from GitHub Packages.

* [FEATURE] Switched `@naba-network/hd-vue-gamecenter` dependency to use GitHub Packages NPM registry instead of Git repository URL.
* [FEATURE] Updated GitHub Actions build pipeline to authenticate with GitHub Packages using `GITHUB_TOKEN`.
* [Feature] Added GitHub Actions build pipeline to automatically build, package, and release `.zip` assets on new tags.
* [Feature] Integrated `plugin-update-checker` to enable automatic updates from GitHub releases directly within the WordPress admin dashboard.

### v4.0.7 (2025-12-19)

- [FEATURE] Improved buttons on schedule item.
- [FEATURE] Improved team page and player cards.

### v4.0.6

- [FEATURE] Improved team page and player cards. 

### v4.0.5

- Tag for the current state.
