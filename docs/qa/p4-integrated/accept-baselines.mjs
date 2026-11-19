import fs from 'node:fs';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';

assert.ok(process.argv.includes('--reviewed'), 'Open all candidate sheets and the named before/after pairs first.');
const output = 'docs/qa/p4-integrated', directory = 'tests/Browser/__screenshots__/public-visual-win32-chromium';
assert.ok(!fs.existsSync(`${output}/baseline-review.json`), 'Do not silently replace a completed review.');
const hash = file => createHash('sha256').update(fs.readFileSync(file)).digest('hex');
const start = JSON.parse(fs.readFileSync(`${output}/starting-state.json`));
const names = ['home-390-hero', 'home-1440-hero', 'home-1920-hero', 'explore-390-hero', 'cabinet-on-1440', 'cabinet-printed-1440'];
const accepted = [];
fs.mkdirSync(`${output}/previous-baselines`, { recursive: true });
for (const name of names) {
    const file = `${directory}/${name}.png`, before = hash(file), candidate = `${output}/candidates/${name}.png`;
    assert.equal(before, start.files.find(f => f.file === file).sha256);
    const a = fs.readFileSync(file), b = fs.readFileSync(candidate); assert.ok(a.subarray(16, 24).equals(b.subarray(16, 24)));
    fs.copyFileSync(file, `${output}/previous-baselines/${name}.png`);
    fs.copyFileSync(candidate, file); accepted.push({ file, before, after: hash(file) });
}
const preserved = start.files.filter(f => f.file.startsWith(directory + '/') && !names.some(n => f.file.endsWith(`/${n}.png`)));
for (const file of preserved) assert.equal(hash(file.file), file.sha256);
fs.writeFileSync(`${output}/baseline-review.json`, JSON.stringify({ reason: 'Reviewed all 40 separately generated candidates and six named before/after pairs. Brighter engraved plaque text and local contrast shadows only; no geometry/source/media/identity change. No mask, threshold, font, browser or fixture change.', accepted, preserved }, null, 2));
console.log(`Accepted ${accepted.length} named contrast crops; preserved ${preserved.length} starting PNG hashes.`);
