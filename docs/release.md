# Release script

`npm run release` (`update-version.js`) bumps the plugin version and — like `npm version` — commits and
tags it in one go.

```bash
npm run release [major | minor | patch | <version>]   # default: patch
npm run release minor --no-git-tag-version            # only update the files
npm run release -- minor --no-git-tag-version         # same, flag passed straight to the script
```

## What it does

1. Validates the arguments: one keyword or semver version, the only allowed flag is
   `--no-git-tag-version`. Unknown arguments, an invalid version or the current version abort with a
   usage message.
2. Unless `--no-git-tag-version`: aborts if the git working tree is not clean or tag `v<version>`
   already exists (checked before any file is touched).
3. Updates the version in `package.json`, `plugin.php` (header + `NOVA_STATS_VERSION`), the README
   `**Stable tag:**` line and turns `## Unreleased` in `CHANGELOG.md` into a `### v<version> (<date>)`
   section. If any file fails, it exits non-zero and skips git.
4. Unless `--no-git-tag-version`: commits only those four files as `Release v<version>` and creates the
   lightweight tag `v<version>`.

Nothing is pushed. Pushing the tag triggers `.github/workflows/release.yml`, which builds and publishes
the release zip:

```bash
git push && git push origin tag v<version>
```

## `--no-git-tag-version`

npm consumes flags written without `--` as its own config (`npm run release minor --no-git-tag-version`
sets `npm_config_git_tag_version`); the script honours both forms, same as `npm version`. Other flags
without `--` are swallowed by npm and never reach the script.

Use it for local-only bumps (e.g. [local update-flow testing](local-update-testing.md)) or the manual
flow with `npm run release:commit` / `npm run release:tag` (tags and pushes).
