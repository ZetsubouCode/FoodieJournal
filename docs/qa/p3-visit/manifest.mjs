import fs from 'node:fs';
import path from 'node:path';
import { createHash } from 'node:crypto';
import { execFileSync } from 'node:child_process';
import assert from 'node:assert/strict';

const output = 'docs/qa/p3-visit';
const hash = bytes => createHash('sha256').update(bytes).digest('hex');
const before = JSON.parse(fs.readFileSync(`${output}/before.json`));
const after = JSON.parse(fs.readFileSync(`${output}/after.json`));
assert.equal(after.build, hash(fs.readFileSync('public/build/manifest.json')), 'Measure the final build.');
const median = values => [...values].sort((a, b) => a - b)[1];
const performance = [];
for (const width of [390, 1440]) for (const cache of ['cold', 'warm']) {
    const select = data => data.samples.filter(s => s.width === width && s.cache === cache);
    const old = select(before), current = select(after);
    assert.equal(old.length, 3); assert.equal(current.length, 3);
    const media = s => s.media.map(m => `${m.url}:${m.bytes}`).sort();
    for (const sample of current) { assert.deepEqual(media(sample), media(old[0])); assert.ok(sample.media.every(m => !m.url.endsWith('/original'))); }
    performance.push({ width, cache, beforeLcpMedian: median(old.map(s => s.lcp)), afterLcpMedian: median(current.map(s => s.lcp)),
        beforeLcpRange: [Math.min(...old.map(s => s.lcp)), Math.max(...old.map(s => s.lcp))],
        afterLcpRange: [Math.min(...current.map(s => s.lcp)), Math.max(...current.map(s => s.lcp))],
        beforeClsMax: Math.max(...old.map(s => s.cls)), afterClsMax: Math.max(...current.map(s => s.cls)),
        arrivalMediaBytes: current[0].media.reduce((sum, m) => sum + m.bytes, 0), identicalArrivalMediaSets: true });
}
fs.writeFileSync(`${output}/performance.json`, JSON.stringify({ samples: performance, limits: 'Small local lab sample; completed arrival media only; startup outlier retained; not field vitals or causal speed gains.' }, null, 2));
const snapshots = 'tests/Browser/__screenshots__/public-visual-win32-chromium';
const reviewed = ['viewer-original-390.png', 'visit-1440-footer.png', 'visit-1920-footer.png'];
const baselines = fs.readdirSync(snapshots).filter(n => n.endsWith('.png')).map(name => {
    const file = `${snapshots}/${name}`;
    const original = hash(execFileSync('git', ['show', `HEAD:${file}`], { maxBuffer: 8 * 1024 * 1024 }));
    const current = hash(fs.readFileSync(file));
    if (reviewed.includes(name)) assert.equal(hash(fs.readFileSync(`${output}/${name.replace('.png', '-before.png')}`)), original);
    return { file, original, current, changed: original !== current };
});
assert.equal(baselines.length, 40);
assert.deepEqual(baselines.filter(b => b.changed).map(b => path.basename(b.file)).sort(), [...reviewed].sort());
assert.match(fs.readFileSync(`${output}/visual-final.log`, 'utf8'), /23 passed/);
assert.match(fs.readFileSync(`${output}/browser-final.log`, 'utf8'), /4 passed/);
const states = JSON.parse(fs.readFileSync(`${output}/states.json`)); assert.equal(states.states.length, 44); assert.deepEqual(states.errors, []);
const probe = JSON.parse(fs.readFileSync(`${output}/probe.json`)); assert.equal(probe.rejectedExit, 1); assert.equal(probe.revertedExit, 0); assert.equal(probe.baselineSha256Before, probe.baselineSha256After);
const inputs = ['resources/css/dossier.css', 'resources/js/components/viewer.js', 'resources/views/public/visit.blade.php', 'resources/views/components/domain/gallery.blade.php', 'tests/Feature/CinematicPublicTest.php', 'tests/Browser/visit-story.spec.js'];
const files = [...inputs, ...fs.readdirSync(output).filter(n => n !== 'manifest.json').map(n => `${output}/${n}`)].filter(f => fs.statSync(f).isFile()).map(file => ({ file, bytes: fs.statSync(file).size, sha256: hash(fs.readFileSync(file)) }));
fs.writeFileSync(`${output}/manifest.json`, JSON.stringify({ startingHead: '3c9f3e64db880dbd7f6cdffa9e714e5a83c7547f', browser: after.browser, build: after.build, baselines, files,
    review: 'Opened full-size desktop/compact story/note/gallery/viewer states, expected/actual/diff crops and decoded normal-time video samples. Three named baseline changes; 37 originals retained. Scope and unknowns in verification.md.' }, null, 2));
console.log('Verified 40 baseline hashes (three named reviews), final build linkage, 44 states, media equality, probe and evidence hashes.');
