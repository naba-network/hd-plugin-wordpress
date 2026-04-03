# Hockey Data Changelog

## Unreleased

* [FEATURE] Updated `update-version.js` to automatically calculate semver version bumps. It now accepts `major`, `minor`, `patch`, or no argument (defaults to `patch`). Usage: `npm run release [major|minor|patch|<version>]`.
* [FEATURE] Added `update-version.js` script to automate updating the plugin version across `package.json`, `plugin.php`, `CHANGELOG.md`, and `AdminController.php`. Usage: `npm run release <version>`.

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
