# CDN embed assets

The three shortcodes (`[Naba-Hdwp-Gamecenter]`, `[Naba-Hdwp-Schedule-Slider]`, `[Naba-Hdwp-Team-Page]`)
now load the Gamecenter widget from the same Cloudflare Worker + R2 CDN
(`https://cdn.statistics.stream`) the client portal's manual embed snippet already uses, instead of a
`frontend/` directory bundled into the plugin's `.zip` at build time. See
`naba-hdwp-widgets`'s `docs/embed-snippet.md` for the underlying contract (custom elements,
`window.initialData`) and `docs/cloudflare-cdn-deploy.md` for the CDN itself.

## Why

Previously the plugin vendored the widget's `app`/`compact` builds via the
`@naba-network/hd-vue-gamecenter` npm dependency, copied into `frontend/` at build time and resolved
through a `manifest.json` (hashed filenames). The `embed` build ships the same three widgets as
custom elements with stable, unhashed filenames (`gamecenter.js`/`gamecenter.css`), so it can be
loaded directly from a fixed URL with no bundling or manifest step — the plugin no longer needs a
frontend build pipeline at all.

## How it works

- **`PluginConstants::EMBED_CDN_HOST`** (`https://cdn.statistics.stream`) and
  **`EMBED_CDN_VERSION`** (`latest`) build the asset URL:
  `{host}/embed/gamecenter/{version}/gamecenter.{js,css}`.
- **`EMBED_CDN_VERSION` is pinned to `latest`, not a specific release.** Accepted trade-off: a bad
  `latest` deploy could affect every WordPress site immediately, with no plugin release to point at
  or roll back to — but it avoids having to bump and test a pinned CDN version on every widget
  release. Revisit if this ever causes a real incident.
- **`VueService::enqueueEmbedAssets()`** replaces the old manifest-based `enqueueAssets()`. It calls
  `wp_enqueue_script`/`wp_enqueue_style` with one shared handle
  (`nova-stats--embed-scripts`/`nova-stats--embed-styles`) for all three shortcodes. WordPress's own
  enqueue registry dedupes by handle, so the CDN `<script>`/`<link>` tags are printed exactly once
  per page no matter how many shortcodes (or shortcode instances) are placed on it — no custom
  "already loaded" flag needed. It's only ever called from inside a shortcode's own render callback,
  so pages without a shortcode never load it either.
- Each shortcode template (`templates/shortcodes/*.php`) renders the matching custom element
  (`<nova-stats-gamecenter>`, `<nova-stats-schedule-slider>`, `<nova-stats-team-page division-id="…"
  team-id="…" player-image-path="…">`) instead of the old `#app`/`#appWpc` SPA mount markup. The
  `window.initialData` bootstrap script (session token + `gamecenterBaseUrl`, see
  [Reusable initial data](reusable-initial-data.md)) is unchanged — it's the same contract the
  `embed` build already expects.

## Local/staging testing

The optional `NABA_HDWP_EMBED_CDN_HOST` wp-config define repoints the script/style URLs at a
locally-served `embed` build instead of the production CDN — see
[Local development & end-to-end testing](local-development.md). Production has no define, so
production behaviour is unchanged.

## What was removed

- `VueService`'s manifest loading, `app`/`compact` build-path resolution, and the preload-link
  (`printPreloadLinks`/`printManifestLinks`) machinery — the `embed` build has no manifest and no
  code-split chunks to preload.
- The `@naba-network/hd-vue-gamecenter` npm dependency, the `frontend:*`/`link:local` npm scripts,
  and the vendored `frontend/` directory (dropped from `.distinclude`/`.gitignore`).
- The admin Debug page's "Frontend build" check (local `manifest.json` presence) — replaced with a
  "Frontend assets" row showing the CDN host/version currently in use
  (`StatusService::getDiagnostics()['embed']`).
