import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';

const output = 'docs/qa/p3-place', snapshots = 'tests/Browser/__screenshots__/public-visual-win32-chromium/';
const hash = bytes => createHash('sha256').update(bytes).digest('hex');
const start = JSON.parse(fs.readFileSync(`${output}/starting-state.json`));
const before = JSON.parse(fs.readFileSync(`${output}/before.json`)), after = JSON.parse(fs.readFileSync(`${output}/after.json`));
const states = JSON.parse(fs.readFileSync(`${output}/states.json`));
assert.equal(after.build, hash(fs.readFileSync('public/build/manifest.json'))); assert.equal(states.build, after.build);
const reviewed = ['place-390-hero.png', 'place-1440-hero.png', 'place-1920-hero.png', 'place-390-footer.png', 'place-1920-footer.png'];
const baselines = start.files.filter(f => f.file.startsWith(snapshots)).map(entry => {
    const current = hash(fs.readFileSync(entry.file));
    if (reviewed.includes(path.basename(entry.file))) assert.equal(hash(fs.readFileSync(`${output}/${path.basename(entry.file).replace('.png', '-before.png')}`)), entry.sha256);
    return { file: entry.file, before: entry.sha256, after: current, changed: current !== entry.sha256 };
});
assert.equal(baselines.length, 40);
assert.deepEqual(baselines.filter(b => b.changed).map(b => path.basename(b.file)).sort(), [...reviewed].sort());
const preserved = start.files.filter(f => !f.file.startsWith(snapshots));
for (const entry of preserved) assert.equal(hash(fs.readFileSync(entry.file)), entry.sha256, `Preserve completed P3-1/controller/shared primitive: ${entry.file}`);
const median = values => [...values].sort((a, b) => a - b)[1];
const performance = [];
for (const width of [390, 1440]) for (const cache of ['cold', 'warm']) {
    const select = data => data.samples.filter(s => s.width === width && s.cache === cache);
    const old = select(before), current = select(after); assert.equal(old.length, 3); assert.equal(current.length, 3);
    const media = sample => sample.media.filter(m => m.bytes > 0).map(m => `${m.url}:${m.bytes}`).sort();
    for (const sample of current) { assert.deepEqual(media(sample), media(old[0])); assert.equal(sample.hero, old[0].hero); assert.ok(sample.media.every(m => !m.url.endsWith('/original'))); }
    const oldLcp = median(old.map(s => s.lcp)), newLcp = median(current.map(s => s.lcp));
    performance.push({ width, cache, beforeLcpMedian: oldLcp, afterLcpMedian: newLcp, lcpChangePercent: (newLcp / oldLcp - 1) * 100,
        beforeRange: [Math.min(...old.map(s => s.lcp)), Math.max(...old.map(s => s.lcp))], afterRange: [Math.min(...current.map(s => s.lcp)), Math.max(...current.map(s => s.lcp))],
        beforeClsMax: Math.max(...old.map(s => s.cls)), afterClsMax: Math.max(...current.map(s => s.cls)),
        arrivalMediaBytes: current[0].media.reduce((sum, m) => sum + m.bytes, 0), identicalHeroAndPositiveByteMedia: true,
        zeroByteTimingEntriesBefore: old.map(s => s.media.filter(m => m.bytes === 0).map(m => m.url)),
        zeroByteTimingEntriesAfter: current.map(s => s.media.filter(m => m.bytes === 0).map(m => m.url)) });
}
fs.writeFileSync(`${output}/performance.json`, JSON.stringify({ samples: performance, limits: 'Small local lab sample; cold LCP increase and residual font/card CLS retained. Font and same-build diagnostic do not replace genuine before/after. See verification.md.' }, null, 2));
assert.equal(states.states.length, 44); assert.deepEqual(states.errors, []);
assert.match(fs.readFileSync(`${output}/browser-final.log`, 'utf8'), /9 passed/);
assert.match(fs.readFileSync(`${output}/visual-before.log`, 'utf8'), /23 passed/);
assert.match(fs.readFileSync(`${output}/visual-final.log`, 'utf8'), /23 passed/);
const probe = JSON.parse(fs.readFileSync(`${output}/probe.json`)); assert.equal(probe.rejectedExit, 1); assert.equal(probe.revertedExit, 0); assert.equal(probe.baselineSha256Before, probe.baselineSha256After);
const inputs = ['resources/views/public/place.blade.php', 'resources/css/cinematic.css', 'tests/Feature/PlaceChronologyTest.php', 'tests/Browser/place-chronology.spec.js', '.ui-craft/brief.md', '.ui-craft/tokens.md', 'docs/page-experience.md', 'docs/motion-interaction.md', 'docs/implementation-status.md', 'docs/roadmap/frontend-experience.md'];
const files = [...inputs, ...fs.readdirSync(output).filter(n => n !== 'manifest.json').map(n => `${output}/${n}`)].filter(f => fs.statSync(f).isFile()).map(file => ({ file, bytes: fs.statSync(file).size, sha256: hash(fs.readFileSync(file)) }));
fs.writeFileSync(`${output}/manifest.json`, JSON.stringify({ head: start.head, build: after.build, browser: after.browser, baselines, preserved, files,
    review: 'Opened full-size desktop/compact chronology, page-2/text/failure states, six Place crop pairs and decoded video samples. Five named Place PNG changes; 35 starting hashes and all completed P3-1 source/tests/evidence plus controller/card/pagination preserved. Performance limits remain explicit.' }, null, 2));
console.log(`Verified final build/captures, 40 baseline hashes (five named Place changes), ${preserved.length} preserved inputs, 44 states, unchanged hero/positive-byte media sets and deliberate matcher rejection/reversion.`);
