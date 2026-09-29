# Build (release zip)

`./build.sh` packages the plugin as `novastats-hockeydata-<version>.zip`. CI
(`.github/workflows/release.yml`) runs it on every `v*` tag and uploads the zip as
`novastats-hockeydata.zip`. The CI job is PHP-only — the plugin has no JS build.

## What ships

The zip is a whitelist: only paths listed in `.distinclude` are copied.

| Path | Why |
|---|---|
| `plugin.php`, `index.php`, `uninstall.php` | Plugin entry, directory guard, uninstall hook |
| `src/`, `templates/` | PHP code and templates |
| `admin/` | Admin CSS, logo, bundled Bootstrap (`admin/vendor/`) |
| `vendor/` | Composer autoloader + `plugin-update-checker`, installed with `--no-dev` |
| `LICENSE` | License |

Dotfiles (`.DS_Store`, …) are excluded by the `zip -x` patterns.

Everything else stays out, notably the developer `README.md`, `CHANGELOG.md`, `docs/`,
`tests/`, Composer/npm/PHPStan/PHPUnit/cs-fixer configs, `build.sh` and `update-version.js`.
The update checker gets version details from GitHub, not from files in the zip.

Third-party packages in `vendor/` are shipped unmodified (including their own
README/license files).

## Adding a runtime file

A new top-level file or folder the plugin needs at runtime **must** be added to
`.distinclude`, otherwise it is missing from the release. Files inside already listed
folders (`src/`, `templates/`, `admin/`) are included automatically.
