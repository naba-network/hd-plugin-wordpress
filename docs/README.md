# Gamecenter — Feature Docs

One Markdown file per feature.

- [Admin area — API token, client portal link & status panel](admin-area.md)
- [Local development & end-to-end testing](local-development.md) — run the plugin against the local DDEV backend with a live-mounted source tree, no release needed.
- [Local update-flow testing](local-update-testing.md) — exercise the WordPress auto-update UI from a local metadata JSON instead of GitHub releases.
- [Build (release zip)](build.md) — what `build.sh` packs: only runtime files from `.distinclude`, no dev/internal files.
- [Release script](release.md) — `npm run release [major|minor|patch|<version>]` bumps the version, commits and tags like `npm version`.
- [CDN embed assets](cdn-embed-assets.md) — the three shortcodes load the Gamecenter widget's `embed` build from the Cloudflare CDN instead of a bundled `frontend/` directory.
