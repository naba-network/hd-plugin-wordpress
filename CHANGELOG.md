# Hockey Data Changelog

## Unreleased

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
