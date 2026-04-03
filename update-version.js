import fs from 'fs';
import path from 'path';
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

let newVersion = process.argv[2] || 'patch';

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
    process.exit(1);
}

const today = new Date().toISOString().split('T')[0];

console.log(`Updating version to ${newVersion}...`);

// 1. Update package.json
try {
    let packageJson = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));
    packageJson.version = newVersion;
    fs.writeFileSync(packageJsonPath, JSON.stringify(packageJson, null, 2) + '\n');
    console.log('✅ Updated package.json');
} catch (e) {
    console.error('❌ Failed to update package.json:', e.message);
}

// 2. Update plugin.php
const pluginPhpPath = path.join(__dirname, 'plugin.php');
try {
    let pluginPhp = fs.readFileSync(pluginPhpPath, 'utf8');
    pluginPhp = pluginPhp.replace(/\*\s+Version:\s+.*$/m, `* Version:     ${newVersion}`);
    pluginPhp = pluginPhp.replace(/define\('NABA_HDWP_VERSION',\s+'[^']+'\);/g, `define('NABA_HDWP_VERSION', '${newVersion}');`);
    fs.writeFileSync(pluginPhpPath, pluginPhp);
    console.log('✅ Updated plugin.php');
} catch (e) {
    console.error('❌ Failed to update plugin.php:', e.message);
}

// 3. Update CHANGELOG.md
const changelogPath = path.join(__dirname, 'CHANGELOG.md');
try {
    let changelog = fs.readFileSync(changelogPath, 'utf8');
    
    // Check if Unreleased section exists
    if (!changelog.includes('## Unreleased')) {
        console.error('❌ Failed to update CHANGELOG.md: "## Unreleased" section not found.');
    } else {
        const releaseHeader = `## Unreleased\n\n### v${newVersion} (${today})`;
        changelog = changelog.replace('## Unreleased', releaseHeader);
        fs.writeFileSync(changelogPath, changelog);
        console.log('✅ Updated CHANGELOG.md');
    }
} catch (e) {
    console.error('❌ Failed to update CHANGELOG.md:', e.message);
}

console.log('🎉 Version update complete!');
