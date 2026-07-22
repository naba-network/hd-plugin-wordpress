# Local update-flow testing

The plugin auto-updates from the built `.zip` attached to a **GitHub release**
(`plugin.php::initUpdater()` → `enableReleaseAssets()`). That normally means the
WordPress "update available" flow can only be exercised by cutting a real release.

The `NABA_HDWP_UPDATE_SOURCE` define lets you point the update checker at a
**self-hosted metadata JSON** instead, so you can bump the version and drive the
WordPress update UI entirely locally.

## How it works

When `NABA_HDWP_UPDATE_SOURCE` is defined and non-empty, `initUpdater()` builds a
generic (non-VCS) update checker against that URL and returns early — the GitHub
release-asset and token setup is skipped. The define is absent in production, so the
GitHub path is used there unchanged.

## Setup

1. **Serve a metadata file + zip** from somewhere the WordPress site can reach — the
   simplest is a folder under the WP docroot, e.g.
   `env-wordpress/local-update/` served at `https://wordpress.ddev.site/local-update/`.

2. **Point the define** (in the local mu-plugin, see
   [local-development.md](local-development.md)):

   ```php
   define('NABA_HDWP_UPDATE_SOURCE', 'https://wordpress.ddev.site/local-update/info.json');
   ```

3. **Build a zip** of a higher version:

   ```bash
   cd hd-plugin-wordpress
   npm run release patch      # bump the version (or edit plugin.php manually)
   npm run build              # or: npm run link:local  (populate frontend/)
   composer install --no-dev --optimize-autoloader
   ./build.sh                 # produces novastats-hockeydata-<version>.zip
   ```

4. **Publish locally**: copy the zip into `env-wordpress/local-update/` and write
   `info.json` describing it. Minimal shape the update checker understands:

   ```json
   {
     "name": "NovaStats HockeyData",
     "slug": "novastats-hockeydata",
     "version": "0.0.5",
     "download_url": "https://wordpress.ddev.site/local-update/novastats-hockeydata-0.0.5.zip",
     "requires": "5.8",
     "requires_php": "8.3",
     "sections": { "changelog": "Local test build." }
   }
   ```

   `version` must be higher than the installed `NABA_HDWP_VERSION` for WordPress to
   offer the update.

## Verify

- wp-admin → Dashboard → Updates (or Plugins) shows the update as available.
- Installing it pulls the local zip and activates the new version — no GitHub, no
  release. Confirm the version in the plugin header afterwards.
- Remove the define to return to the normal GitHub release-asset behaviour.
