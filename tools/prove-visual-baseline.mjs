import { spawnSync } from 'node:child_process';
import { createRequire } from 'node:module';
import { createHash } from 'node:crypto';
import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';

const require = createRequire(import.meta.url);
const cli = require.resolve('@playwright/test/cli');
const baseline = 'tests/Browser/__screenshots__/public-visual-win32-chromium/home-1440-hero.png';
const digest = () => createHash('sha256').update(fs.readFileSync(baseline)).digest('hex');
const before = digest();
const output = 'docs/qa/visual-baselines';
const args = [cli, 'test', '--config', 'playwright.visual.config.js', '--grep', 'Home at 1440', '--update-snapshots=none'];
const failed = spawnSync(process.execPath, args, { env: { ...process.env, FOODIE_VISUAL_SHIFT_PROBE: '1' }, encoding: 'utf8' });
fs.writeFileSync(`${output}/probe-rejected.log`, failed.stdout + failed.stderr);
assert.equal(failed.status, 1, 'The intentional eight-pixel shift must fail.');
const report = JSON.parse(fs.readFileSync('tools/.runtime/visual-results.json'));
assert.equal(report.stats.unexpected, 1);
const serialized = JSON.stringify(report);
assert(serialized.includes('toHaveScreenshot') && serialized.includes('home-1440-hero.png'));
const result = report.suites.flatMap(s => s.suites ?? []).flatMap(s => s.specs ?? []).flatMap(s => s.tests).flatMap(t => t.results);
// Top-level file suites can hold specs directly.
const allResults = [...result, ...report.suites.flatMap(s => s.specs ?? []).flatMap(s => s.tests).flatMap(t => t.results)];
const attachments = allResults.flatMap(r => r.attachments ?? []);
const error = allResults.find(r => r.status === 'failed')?.error?.message ?? '';
const differentPixels = Number(error.match(/(\d+) pixels/)?.[1]);
assert(differentPixels > 30, 'Expected a measured pixel difference above the allowed budget.');
for (const suffix of ['actual', 'diff']) {
    const file = attachments.find(a => a.path && a.path.endsWith(`home-1440-hero-${suffix}.png`));
    assert(file, `Expected the ${suffix} pixel artifact.`);
    fs.copyFileSync(file.path, path.join(output, `probe-${suffix}.png`));
}
assert.equal(digest(), before, 'The probe must not update the baseline.');
const cleanEnv = { ...process.env }; delete cleanEnv.FOODIE_VISUAL_SHIFT_PROBE;
const clean = spawnSync(process.execPath, args, { env: cleanEnv, encoding: 'utf8' });
fs.writeFileSync(`${output}/probe-reverted.log`, clean.stdout + clean.stderr);
assert.equal(clean.status, 0, 'The same test must pass after removing the DOM-only shift.');
assert.equal(digest(), before);
fs.writeFileSync(`${output}/probe.json`, JSON.stringify({ shift: 'DOM-only site-header translateY(8px), Home 1440 hero',
    differentPixels, allowedPixels: 30, rejectedExit: failed.status, revertedExit: clean.status, baselineSha256Before: before, baselineSha256After: digest(),
    baselineUpdates: 'none', productionSourceMutation: false }, null, 2) + '\n');
console.log('Pixel matcher rejected the 8px shift; clean rerun passed; baseline hash unchanged.');
