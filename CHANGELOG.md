# Hockey Data Changelog

## Unreleased

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
