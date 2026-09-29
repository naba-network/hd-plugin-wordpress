import fs from 'fs';
import path from 'path';
import { execFileSync } from 'child_process';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const packageJsonPath = path.join(__dirname, 'package.json');
let currentVersion = '0.0.0';
try {
    const packageJson = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));
    currentVersion = packageJson.version || '0.0.0';
} catch (e) {
    console.error('Error: Could not read package.json to get current version.');
    process.exit(1);
}

const usage = 'Usage: npm run release [major | minor | patch | <version>] [-- --no-git-tag-version]';
const args = process.argv.slice(2);
const flags = args.filter((arg) => arg.startsWith('-'));
const positionals = args.filter((arg) => !arg.startsWith('-'));

const unknownFlags = flags.filter((flag) => flag !== '--no-git-tag-version');
if (unknownFlags.length > 0 || positionals.length > 1) {
    console.error(`Error: Unexpected argument(s): ${[...unknownFlags, ...positionals.slice(1)].join(' ')}`);
    console.error(usage);
    process.exit(1);
}

// Like `npm version`: npm consumes `npm run release minor --no-git-tag-version` itself and exposes it as config env
const gitTagVersion = !flags.includes('--no-git-tag-version')
    && !['', 'false'].includes(process.env.npm_config_git_tag_version ?? 'true');

let newVersion = positionals[0] || 'patch';

const semverParts = currentVersion.split('.').map(Number);
let [major, minor, patch] = semverParts;

if (newVersion === 'major') {
    major += 1;
    minor = 0;
    patch = 0;
    newVersion = `${major}.${minor}.${patch}`;
} else if (newVersion === 'minor') {
    minor += 1;
    patch = 0;
    newVersion = `${major}.${minor}.${patch}`;
} else if (newVersion === 'patch') {
    patch += 1;
    newVersion = `${major}.${minor}.${patch}`;
}

// Ensure version format (e.g., 1.0.0)
if (!/^\d+\.\d+\.\d+(-[0-9A-Za-z-]+(\.[0-9A-Za-z-]+)*)?$/.test(newVersion)) {
    console.error('Error: Invalid version format. Please use semver (e.g., 1.0.0, 1.0.0-beta.1) or "major", "minor", "patch".');
    console.error(usage);
    process.exit(1);
}

if (newVersion === currentVersion) {
    console.error(`Error: Version not changed, already at ${currentVersion}.`);
    process.exit(1);
}

const git = (...gitArgs) => execFileSync('git', gitArgs, { cwd: __dirname, encoding: 'utf8' });

if (gitTagVersion) {
    if (git('status', '--porcelain').trim() !== '') {
        console.error('Error: Git working directory not clean. Commit or stash your changes first (or pass --no-git-tag-version).');
        process.exit(1);
    }
    try {
        git('rev-parse', '--quiet', '--verify', `refs/tags/v${newVersion}`);
        console.error(`Error: Git tag v${newVersion} already exists.`);
        process.exit(1);
    } catch {
        // tag does not exist yet
    }
}

let failed = false;
const today = new Date().toISOString().split('T')[0];

console.log(`Updating version to ${newVersion}...`);

// 1. Update package.json
try {
    let packageJson = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));
    packageJson.version = newVersion;
    fs.writeFileSync(packageJsonPath, JSON.stringify(packageJson, null, 2) + '\n');
    console.log('✅ Updated package.json');
} catch (e) {
    failed = true;
    console.error('❌ Failed to update package.json:', e.message);
}

// 2. Update plugin.php
const pluginPhpPath = path.join(__dirname, 'plugin.php');
try {
    let pluginPhp = fs.readFileSync(pluginPhpPath, 'utf8');
    pluginPhp = pluginPhp.replace(/\*\s+Version:\s+.*$/m, `* Version:     ${newVersion}`);
    pluginPhp = pluginPhp.replace(/define\('NOVA_STATS_VERSION',\s+'[^']+'\);/g, `define('NOVA_STATS_VERSION', '${newVersion}');`);
    fs.writeFileSync(pluginPhpPath, pluginPhp);
    console.log('✅ Updated plugin.php');
} catch (e) {
    failed = true;
    console.error('❌ Failed to update plugin.php:', e.message);
}

// 3. Update README.md (WordPress "Stable tag" header)
const readmePath = path.join(__dirname, 'README.md');
try {
    let readme = fs.readFileSync(readmePath, 'utf8');
    if (!/\*\*Stable tag:\*\*/.test(readme)) {
        failed = true;
        console.error('❌ Failed to update README.md: "**Stable tag:**" line not found.');
    } else {
        readme = readme.replace(/\*\*Stable tag:\*\*\s*\S+/, `**Stable tag:** ${newVersion}`);
        fs.writeFileSync(readmePath, readme);
        console.log('✅ Updated README.md');
    }
} catch (e) {
    failed = true;
    console.error('❌ Failed to update README.md:', e.message);
}

// 4. Update CHANGELOG.md
const changelogPath = path.join(__dirname, 'CHANGELOG.md');
try {
    let changelog = fs.readFileSync(changelogPath, 'utf8');
    
    // Check if Unreleased section exists
    if (!changelog.includes('## Unreleased')) {
        failed = true;
        console.error('❌ Failed to update CHANGELOG.md: "## Unreleased" section not found.');
    } else {
        const releaseHeader = `## Unreleased\n\n### v${newVersion} (${today})`;
        changelog = changelog.replace('## Unreleased', releaseHeader);
        fs.writeFileSync(changelogPath, changelog);
        console.log('✅ Updated CHANGELOG.md');
    }
} catch (e) {
    failed = true;
    console.error('❌ Failed to update CHANGELOG.md:', e.message);
}

if (failed) {
    console.error('❌ Version update incomplete, skipping git commit and tag.');
    process.exit(1);
}

console.log('🎉 Version update complete!');

// 5. Commit and tag (like `npm version`), unless --no-git-tag-version
if (gitTagVersion) {
    try {
        git('add', packageJsonPath, pluginPhpPath, readmePath, changelogPath);
        git('commit', '-m', `Release v${newVersion}`);
        git('tag', `v${newVersion}`);
        console.log(`✅ Committed and tagged v${newVersion}`);
        console.log(`➡️  Publish with: git push && git push origin tag v${newVersion}`);
    } catch (e) {
        console.error('❌ Failed to commit/tag:', e.message);
        process.exit(1);
    }
}
