# Local development & end-to-end testing

Run the plugin against the **local DDEV backend** with a **live-mounted source
tree**, so you no longer need to cut a GitHub release or point at production to
test a change.

## How it works

Everything is driven by opt-in WordPress defines. **None are set in production**,
so production behaviour is unchanged.

| Define                     | Effect                                                                                                   |
| -------------------------- | -------------------------------------------------------------------------------------------------------- |
| `NABA_HDWP_API_BASE_URL`   | Repoints **both** the embedded widget (injected into `window.initialData.novaStatsApiUrl`) and the admin health-check at this backend. |
| `NABA_HDWP_HOCKEYDATA_URL` | Repoints the widget's HockeyData calls (injected as `window.initialData.hockeyDataApiUrl`) — use the backend's dev-only `hockeyDataByPass` proxy for real data from localhost. |
| `NABA_HDWP_UPDATE_SOURCE`  | Points the update checker at a local metadata JSON — see [local-update-testing.md](local-update-testing.md). |

The widget reads these overrides in `naba-hdwp-widgets/src/setup/js/config.ts`
(`docs/runtime-api-override.md` there); the plugin injects them once per page from
`VueService::enqueueRuntimeOverrides()` and uses `NABA_HDWP_API_BASE_URL` as the
server-side base URL default in `StatusService::getApiBaseUrl()` (the existing
`naba_hdwp_api_base_url` filter still overrides both).

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
   define('NABA_HDWP_API_BASE_URL', 'https://opendxp-nova-stats.ddev.site');
   define('NABA_HDWP_HOCKEYDATA_URL', 'https://opendxp-nova-stats.ddev.site/api/hockeyDataByPass/data/ebel');
   // define('NABA_HDWP_UPDATE_SOURCE', 'https://wordpress.ddev.site/local-update/info.json');
   ```

## The easy path — one script

After the one-time setup above, whenever you change the plugin or the widget, just run
the helper in the repo root:

```
./update-wordpress-demo.sh
```

It rebuilds the widget (with the runtime-override code), deploys it into the plugin's
`frontend/`, verifies the override build actually landed, and clears the cached backend
connection check. Then hard-reload the demo page. **Use this instead of the plugin's
`npm run build`** — that command pulls the *published* widget package (no override code,
production URLs) and is what breaks the demo.

## The loop (what the script automates, step by step)

1. **Backend:** `cd nova-stats-opendxp && ddev start`. Ensure `APP_ENV=dev` so the
   backend skips the token referrer check (a locally-created token then validates
   from any origin).
2. **Widget:** `cd naba-hdwp-widgets && npm run build:all` — builds `dist/app` +
   `dist/compact` (with the runtime-override code).
3. **Plugin:** `cd hd-plugin-wordpress && composer install && npm run link:local` —
   `composer install` fills `vendor/`; `link:local` copies the sibling widget's
   freshly built `dist/*` into `frontend/` (mirrors the CI `npm run build`, but from
   the local build instead of the private npm registry).
4. **WordPress:** `cd env-wordpress && ddev start` (mount + defines active). Activate
   the plugin in wp-admin if needed.
5. **Token:** create a `GameCenterApiToken` in the backend (register via the client
   portal + `POST /api/v1/api-keys`, or `ddev import-project` for real data). Paste it
   into the plugin's admin Configuration page.
6. **Render:** add the `[Naba-Hdwp-Gamecenter]` shortcode to a page and open it.

## Verify

- DevTools → Network: token validation hits `opendxp-nova-stats.ddev.site/api/v1/validate-api-token`
  and stats hit the `hockeyDataByPass` proxy — **not** `datahub.h-sc.at` / `api.hockeydata.net`.
- Remove the defines and reload: calls fall back to `datahub.h-sc.at` (proves
  production behaviour is untouched).
- Admin Status panel shows the connection as green against the local backend. If the
  WP container cannot reach `opendxp-nova-stats.ddev.site` server-side, add both DDEV
  projects to a shared external Docker network (or use `host.docker.internal`); the
  browser/widget path works regardless.
- Edit a template/PHP file in this repo, reload — the change appears with no rebuild.

## After the widget change is published

Once `naba-hdwp-widgets` publishes a version containing the runtime-override code to
GitHub Packages, step 2–3 can use the normal `npm run frontend:update && npm run build`
instead of the local `link:local` build.
