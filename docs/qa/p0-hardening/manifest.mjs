import fs from 'node:fs';
import { createHash } from 'node:crypto';
import { execFileSync } from 'node:child_process';
import assert from 'node:assert/strict';

const hash = file => createHash('sha256').update(fs.readFileSync(file)).digest('hex');
const parent = 'docs/qa/visual-baselines/manifest.json';
const lineage = JSON.parse(fs.readFileSync(parent));
const report = JSON.parse(fs.readFileSync('tools/.runtime/visual-results.json'));
assert.equal(report.stats.unexpected, 0);
assert.equal(report.stats.expected, 23);
assert.equal(lineage.snapshots.length, 40);
for (const snapshot of lineage.snapshots) assert.equal(hash(snapshot.file), snapshot.sha256, `Historical PNG changed: ${snapshot.file}`);
const inputs = ['public/build/manifest.json', 'public/assets/css/error.css', 'playwright.visual.config.js', 'tests/Browser/public-visual.spec.js',
    'tools/visual-qa.php', 'tools/p0-hardening-qa.php', 'tests/Feature/PlaceHeroTest.php', 'tests/Browser/p0-hardening.spec.js',
    'tools/.runtime/visual-photo.sqlite', 'tools/.runtime/visual-empty.sqlite', 'tools/.runtime/p0-hardening.sqlite'];
fs.writeFileSync('docs/qa/p0-hardening/manifest.json', JSON.stringify({
    reviewed: '2026-11-09', baselineHead: execFileSync('git', ['rev-parse', 'HEAD'], { encoding: 'utf8' }).trim(),
    workingTreeAtStart: 'clean', reviewer: 'Codex: opened matched contact sheets and selected full-size captures',
    parentManifest: { file: parent, sha256: hash(parent) },
    visualBaselineChanges: 'none; all 40 PNGs retain original SHA-256; no tolerance or mask changes',
    comparison: lineage.comparison, environment: { ...lineage.environment, browser: JSON.parse(fs.readFileSync('docs/qa/p0-hardening/after.json')).browser,
        node: process.version, playwright: JSON.parse(fs.readFileSync('node_modules/@playwright/test/package.json')).version },
    verifiedCases: report.stats.expected, snapshots: lineage.snapshots.map(s => ({ file: s.file, sha256: hash(s.file), width: s.width, height: s.height })),
    inputs: inputs.map(file => ({ file, sha256: hash(file) })),
}, null, 2) + '\n');
console.log('Verified 40 unchanged baseline hashes and 23 passing cases; recorded current build/fixture lineage separately.');
