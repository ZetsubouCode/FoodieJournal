import fs from 'node:fs';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
import { execFileSync } from 'node:child_process';

const output = 'docs/qa/p2-discovery';
const hash = file => createHash('sha256').update(fs.readFileSync(file)).digest('hex');
const parentFile = 'docs/qa/visual-baselines/manifest.json', parent = JSON.parse(fs.readFileSync(parentFile));
const changed = 'tests/Browser/__screenshots__/public-visual-win32-chromium/filtered-empty-390.png';
assert.equal(parent.snapshots.length, 40);
for (const s of parent.snapshots) {
    if (s.file === changed) assert.equal(hash(`${output}/filtered-empty-before.png`), s.sha256);
    else assert.equal(hash(s.file), s.sha256, `Unreviewed baseline change: ${s.file}`);
}
assert.equal(hash(changed), hash(`${output}/filtered-empty-reviewed.png`));
assert.match(fs.readFileSync(`${output}/visual-release.log`, 'utf8'), /23 passed/);
assert.match(fs.readFileSync(`${output}/release-regressions.log`, 'utf8'), /21 passed/);
const proof = JSON.parse(fs.readFileSync(`${output}/probe.json`));
assert.equal(proof.rejectedExit, 1); assert.equal(proof.revertedExit, 0); assert.equal(proof.baselineSha256Before, proof.baselineSha256After);
const build = hash('public/build/manifest.json');
const before = JSON.parse(fs.readFileSync(`${output}/before.json`)), after = JSON.parse(fs.readFileSync(`${output}/after.json`));
assert.equal(before.samples.length, 6); assert.equal(after.samples.length, 6); assert.equal(after.build, build);
const states = JSON.parse(fs.readFileSync(`${output}/states.json`)); assert.equal(states.states.length, 40); assert.equal(states.errors.length, 0); assert.equal(states.build, build);
const poses = JSON.parse(fs.readFileSync(`${output}/poses.json`)); assert.equal(poses.records.length, 6);
const realtime = JSON.parse(fs.readFileSync(`${output}/realtime.json`)); assert.equal(realtime.recordings.length, 2); assert.equal(realtime.build, build);
for (const r of realtime.recordings) { assert.equal(hash(r.file), r.sha256); assert.equal(r.errors.length, 0); }
const p1 = JSON.parse(fs.readFileSync('docs/qa/p1-physical-response/manifest.json'));
const archive = p1.inputs.find(f => f.file === 'resources/js/pages/archive.js'); assert.equal(hash(archive.file), archive.sha256, 'Preserve completed P1 source.');
const inputs = ['resources/js/pages/explore.js', 'resources/css/cinematic.css', 'resources/css/discovery.css', 'app/Services/CuriosityIndex.php', 'resources/views/public/explore.blade.php', 'resources/views/public/explore-fragment.blade.php', 'resources/views/components/discovery/curiosity-index.blade.php', 'tests/Browser/explore-response.spec.js', 'tests/Feature/CinematicPublicTest.php', 'public/build/manifest.json', 'playwright.visual.config.js', 'tests/Browser/public-visual.spec.js', 'tools/.runtime/visual-photo.sqlite', 'tools/.runtime/visual-empty.sqlite'];
fs.writeFileSync(`${output}/manifest.json`, JSON.stringify({ date: '2026-11-09', head: execFileSync('git', ['rev-parse', 'HEAD'], { encoding: 'utf8' }).trim(), start: 'Completed uncommitted P1 source/docs/evidence preserved. No commit/push/deployment.',
    environment: { browser: after.browser, platform: process.platform, node: process.version, timezone: 'Asia/Jakarta', fixture: 'Dedicated fixed-date photo-v1/empty-v1 SQLite/media plus existing isolated functional/readability fixtures' },
    review: 'Opened full-size desktop/compact selected/pending/photographic waits, contact sheets, native-select expected/actual/diff and decoded normal-time videos. Limits and measured regression are in verification.md.',
    parent: { file: parentFile, sha256: hash(parentFile) }, baselineUpdates: { unchanged: 39, explicitlyReviewed: changed, originalSaved: `${output}/filtered-empty-before.png`, reason: 'Stable one-pixel native select text baseline shift, 262 differing pixels; font, box, labels, values and actions unchanged. Full expected/actual/diff inspected; no mask/tolerance change.' },
    snapshots: parent.snapshots.map(s => ({ ...s, sha256: hash(s.file) })), preservedP1: archive,
    inputs: inputs.map(file => ({ file, sha256: hash(file) })), evidence: fs.readdirSync(output).filter(f => f !== 'manifest.json').map(f => ({ file: `${output}/${f}`, sha256: hash(`${output}/${f}`) })) }, null, 2) + '\n');
console.log('Verified 39 unchanged baselines plus one named reviewed crop, 23 pixel cases, 21 interaction regressions, matching final build evidence and preserved P1 source.');
