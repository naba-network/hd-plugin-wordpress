# Gamecenter — Feature Docs

One Markdown file per feature.

- [Admin area — API token, client portal link & status panel](admin-area.md)
- [Local development & end-to-end testing](local-development.md) — run the plugin against the local DDEV backend with a live-mounted source tree, no release needed.
- [Local update-flow testing](local-update-testing.md) — exercise the WordPress auto-update UI from a local metadata JSON instead of GitHub releases.
- [Reusable initial data — Gamecenter link](reusable-initial-data.md) — the "Gamecenter Link" setting, injected into every shortcode alongside the API token, that the schedule slider/team page shortcodes use to link back to the main Gamecenter.
- [CDN embed assets](cdn-embed-assets.md) — the three shortcodes load the Gamecenter widget's `embed` build from the Cloudflare CDN instead of a bundled `frontend/` directory.
