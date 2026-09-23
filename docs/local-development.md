# Local development & end-to-end testing

Run the plugin against the **local DDEV backend** with a **live-mounted source
tree**, so you no longer need to cut a GitHub release or point at production to
test a change.

## How it works

Everything is driven by opt-in WordPress defines. **None are set in production**,
so production behaviour is unchanged.

| Define                             | Effect                                                                                                   |
| ----------------------------------- | -------------------------------------------------------------------------------------------------------- |
| `NOVA_STATS_LOCAL_API_BASE_URL`     | Repoints **both** the embedded widget (injected into `window.initialData.novaStatsApiUrl`) and the admin health-check at this backend. |
| `NOVA_STATS_LOCAL_HOCKEYDATA_URL`   | Repoints the widget's HockeyData calls (injected as `window.initialData.hockeyDataApiUrl`) — use the backend's dev-only `hockeyDataByPass` proxy for real data from localhost. |
| `NOVA_STATS_LOCAL_EMBED_CDN_HOST`   | Repoints the `<nova-stats-*>` `embed` build script/style tags at a locally-served build instead of the production CDN (`https://cdn.statistics.stream`) — see below. |
| `NOVA_STATS_LOCAL_UPDATE_SOURCE`    | Points the update checker at a local metadata JSON — see [local-update-testing.md](local-update-testing.md). |

The widget reads these overrides in `naba-hdwp-widgets/src/setup/js/config.ts`
(`docs/runtime-api-override.md` there); the plugin injects them once per page from
`VueService::enqueueRuntimeOverrides()` and uses `NOVA_STATS_LOCAL_API_BASE_URL` as the
server-side base URL default in `StatusService::getApiBaseUrl()` (the existing
`nova_stats_api_base_url` filter still overrides both).

## One-time setup (the WordPress DDEV project, `env-wordpress`)

The WordPress instance is its own DDEV project (`wordpress` → `https://wordpress.ddev.site`).

1. **Live-mount the plugin source.** A symlink does not work — DDEV only mounts its
   own project directory, and this repo is a sibling of `env-wordpress`. Bind-mount
   it instead via `env-wordpress/.ddev/docker-compose.plugin.yaml`:

   ```yaml
   services:
     web:
       volumes:
         - "../../hd-plugin-wordpress:/var/www/html/wp-content/plugins/novastats-hockeydata"
   ```

   This shadows the static copy under `wp-content/plugins/novastats-hockeydata`, so
   edits in this repo are live in the container with no copy step. Run `ddev restart`
   after adding it. (Confirm the relative path resolves to this repo from the compose
   file location.)

2. **Set the defines** in a must-use plugin
   `env-wordpress/wp-content/mu-plugins/local-nova-stats.php` (preferred — it survives
   DDEV regenerating `wp-config.php`):

   ```php
   <?php
   // Local-only overrides. Never ship to production.
   define('NOVA_STATS_LOCAL_API_BASE_URL', 'https://opendxp-nova-stats.ddev.site');
   define('NOVA_STATS_LOCAL_HOCKEYDATA_URL', 'https://opendxp-nova-stats.ddev.site/api/hockeyDataByPass/data/ebel');
   define('NOVA_STATS_LOCAL_EMBED_CDN_HOST', 'https://opendxp-nova-stats.ddev.site');
   // define('NOVA_STATS_LOCAL_UPDATE_SOURCE', 'https://wordpress.ddev.site/local-update/info.json');
   ```

## The easy path — one script

After the one-time setup above, whenever you change the widget, just run the helper in
the repo root:

```
./update-wordpress-demo.sh
```

It rebuilds the widget's `embed` mode (with the runtime-override code) and deploys it
into `nova-stats-opendxp`'s `GamecenterEmbedController` local-testing directory (see
that repo's `docs/gamecenter-embed-snippet.md#local-testing`), verifies the override
build actually landed, and clears the cached backend connection check. Then hard-reload
the demo page. The plugin itself has no build step anymore — `VueService` always loads
the `embed` build from `NOVA_STATS_LOCAL_EMBED_CDN_HOST` (or the production CDN when that
define is absent), so editing plugin PHP/templates is live immediately, same as before.

## The loop (what the script automates, step by step)

1. **Backend:** `cd nova-stats-opendxp && ddev start`. Ensure `APP_ENV=dev` so the
   backend skips the token referrer check (a locally-created token then validates
   from any origin).
2. **Widget:** `cd naba-hdwp-widgets && npm run build:embed` — builds `dist/embed`
   (with the runtime-override code).
3. **Backend embed dir:** the script copies `dist/embed` into
   `nova-stats-opendxp/project/public/var/nova-stats-gamecenter-embed/local` and
   symlinks `latest` to it, so `GamecenterEmbedController` serves it at
   `https://opendxp-nova-stats.ddev.site/embed/gamecenter/latest/gamecenter.js` —
   exactly the path/version the plugin's `VueService::enqueueEmbedAssets()` requests.
4. **WordPress:** `cd env-wordpress && ddev start` (mount + defines active, including
   `NOVA_STATS_LOCAL_EMBED_CDN_HOST`). Activate the plugin in wp-admin if needed.
5. **Token:** create a `GameCenterApiToken` in the backend (register via the client
   portal + `POST /api/v1/api-keys`, or `ddev import-project` for real data). Paste it
   into the plugin's admin Configuration page.
6. **Render:** add the `[Gamecenter]` shortcode to a page and open it.

## Verify

- DevTools → Network: token validation hits `opendxp-nova-stats.ddev.site/api/v1/validate-api-token`
  and stats hit the `hockeyDataByPass` proxy — **not** `nova-stats.com` / `api.hockeydata.net`.
- Remove the defines and reload: calls fall back to `nova-stats.com` (proves
  production behaviour is untouched).
- Admin Status panel shows the connection as green against the local backend. If the
  WP container cannot reach `opendxp-nova-stats.ddev.site` server-side, add both DDEV
  projects to a shared external Docker network (or use `host.docker.internal`); the
  browser/widget path works regardless.
- Edit a template/PHP file in this repo, reload — the change appears with no rebuild.

## After the widget change is published

Once `naba-hdwp-widgets` publishes a version containing the runtime-override code,
`nova-stats-opendxp`'s `docs/gamecenter-embed-snippet.md` "Production release" steps
apply instead of the local `local`/`latest` symlink used here.
