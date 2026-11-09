import fs from 'node:fs';
import { createHash } from 'node:crypto';
import { execFileSync } from 'node:child_process';
import assert from 'node:assert/strict';

const output = 'docs/qa/p1-physical-response';
const hash = file => createHash('sha256').update(fs.readFileSync(file)).digest('hex');
const parent = 'docs/qa/visual-baselines/manifest.json', lineage = JSON.parse(fs.readFileSync(parent));
// The negative-control tool replaces the transient reporter file with its one-case clean rerun.
assert.match(fs.readFileSync(`${output}/visual-final.log`, 'utf8'), /23 passed/);
const proof = JSON.parse(fs.readFileSync(`${output}/probe.json`));
assert.equal(proof.rejectedExit, 1); assert.equal(proof.revertedExit, 0);
assert.equal(proof.baselineSha256Before, proof.baselineSha256After);
assert.equal(lineage.snapshots.length, 40);
for (const s of lineage.snapshots) assert.equal(hash(s.file), s.sha256, `Reviewed baseline changed: ${s.file}`);
const before = JSON.parse(fs.readFileSync(`${output}/before.json`)), after = JSON.parse(fs.readFileSync(`${output}/after.json`));
assert.equal(before.samples.length, 6); assert.equal(after.samples.length, 6); assert.equal(after.frames.length, 8);
assert.equal(after.buildSha256, hash('public/build/manifest.json'));
assert.equal(after.errors.length, 0);
for (const width of [1440, 390]) {
    const frames = after.frames.filter(f => f.width === width);
    assert(frames.every(f => f.power === 'off'));
    assert(Math.abs(frames[1].lamp.y - frames[1].socket.y + 4) < .5);
    assert(Math.abs(frames[2].lamp.y - frames[2].socket.y) < .5);
    assert(frames[2].plate.includes('1.035') && frames[2].plate.includes('0.985'));
}
const realtime = JSON.parse(fs.readFileSync(`${output}/realtime.json`));
assert.equal(realtime.records.length, 2);
for (const r of realtime.records) { assert.equal(hash(r.video), r.videoSha256); assert.equal(r.errors.length, 0); }
const files = fs.readdirSync(output).filter(f => f !== 'manifest.json').map(f => `${output}/${f}`);
const inputs = ['resources/js/pages/archive.js', 'resources/js/components/vending-state.mjs', 'resources/js/components/vending-input.js', 'resources/css/vending-machine.css', 'tests/Browser/vending-seating.spec.js', 'public/build/manifest.json', 'playwright.visual.config.js', 'tools/visual-qa.php', 'tools/.runtime/visual-photo.sqlite', 'tools/.runtime/visual-empty.sqlite'];
fs.writeFileSync(`${output}/manifest.json`, JSON.stringify({ date: '2026-11-09', baselineHead: execFileSync('git', ['rev-parse', 'HEAD'], { encoding: 'utf8' }).trim(), workingTreeAtStart: 'clean',
    review: 'Codex opened contact sheets and full-size desktop/compact frames; decoded and inspected raw-video frames. Scope and limits in verification.md.',
    parent: { file: parent, sha256: hash(parent) }, baselineUpdates: 'none', visualCases: 23, snapshots: lineage.snapshots.map(s => ({ file: s.file, sha256: hash(s.file) })),
    environment: { browser: after.browser, node: process.version, platform: process.platform, timezone: 'Asia/Jakarta', deviceScaleFactor: 1, fixture: 'photo-v1 / empty-v1 on fixed dedicated SQLite/media targets' },
    inputs: inputs.map(file => ({ file, sha256: hash(file) })), evidence: files.map(file => ({ file, sha256: hash(file) })) }, null, 2) + '\n');
console.log('Verified 40 unchanged reviewed PNG hashes, 23 passing visual cases, seating poses, six before/after samples and both raw-video hashes.');
