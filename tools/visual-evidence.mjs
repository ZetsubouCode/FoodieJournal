import fs from 'node:fs';
import { createHash } from 'node:crypto';
import { execFileSync } from 'node:child_process';
import os from 'node:os';
import assert from 'node:assert/strict';

assert(process.argv.includes('--reviewed'), 'Review the PNGs/contact sheets first, then pass --reviewed to record that review.');
const hash = path => createHash('sha256').update(fs.readFileSync(path)).digest('hex');
const report = JSON.parse(fs.readFileSync('tools/.runtime/visual-results.json'));
assert.equal(report.stats.unexpected, 0, 'Only record a fully passing baseline verification run.');
assert.equal(report.stats.expected, 23);
const snapshots = [];
function collect(suites) {
    for (const suite of suites) {
        for (const spec of suite.specs ?? []) for (const test of spec.tests) for (const result of test.results) {
            for (const attachment of result.attachments ?? []) {
                if (attachment.name !== 'visual-case') continue;
                const item = JSON.parse(Buffer.from(attachment.body, 'base64').toString('utf8'));
                assert(/^[a-z0-9-]+$/.test(item.name));
                assert(/^http:\/\/127\.0\.0\.1:801[56]\//.test(item.route));
                const path = `tests/Browser/__screenshots__/public-visual-win32-chromium/${item.name}.png`;
                const png = fs.readFileSync(path);
                const state = item.state ?? (item.name.includes('footer') ? 'footer' : item.name.includes('hero') ? 'arrival; Home is power OFF' : item.name);
                snapshots.push({ ...item, state, fixture: item.route.includes(':8016/') ? 'empty-v1' : 'photo-v1', file: path, width: png.readUInt32BE(16), height: png.readUInt32BE(20), sha256: hash(path) });
            }
        }
        collect(suite.suites ?? []);
    }
}
collect(report.suites);
assert.equal(snapshots.length, 40);
assert.equal(new Set(snapshots.map(s => s.name)).size, 40);
const inputs = ['public/build/manifest.json', 'tools/visual-qa.php', 'playwright.visual.config.js', 'tests/Browser/public-visual.spec.js',
    'database/seeders/DemoJournalSeeder.php', 'database/seeders/DemoTaxonomySeeder.php', 'database/seeders/data/demo-journal.php',
    'tools/.runtime/visual-photo.sqlite', 'tools/.runtime/visual-empty.sqlite', 'tools/visual-evidence.mjs',
    ...fs.readdirSync('public/assets/fonts').filter(f => f.endsWith('.woff2')).map(f => `public/assets/fonts/${f}`),
    ...fs.readdirSync('database/seeders/assets').filter(f => f.endsWith('.jpg')).map(f => `database/seeders/assets/${f}`)];
const manifest = { reviewed: new Date().toISOString().slice(0, 10), reviewer: 'Codex visual review against completed P0-1/P0-2/P0-3 contracts',
    baselineHead: execFileSync('git', ['rev-parse', 'HEAD'], { encoding: 'utf8' }).trim(),
    workingTree: 'Existing uncommitted P0-1/P0-2/P0-3 retained; P0-4 changes testing/tools/docs only.',
    environment: { platform: process.platform, release: os.release(), node: process.version, browser: '156.0.8078.4',
        playwright: JSON.parse(fs.readFileSync('node_modules/@playwright/test/package.json')).version,
        locale: 'id-ID', timezone: 'Asia/Jakarta', deviceScaleFactor: 1, theme: 'dark', fixedServerDate: '2026-11-09T12:00:00Z' },
    comparison: { threshold: .15, maxDiffPixels: 30, navigationMidpointMaxDiffPixels: 0, animations: 'allow', masks: 'none', updateDefault: 'none' },
    reviewedGroups: ['All five arrival hero/footer pairs at 390/1440/1920', 'Actual paused 110ms public/error nav progress',
        'Full on/printed cabinet and exact visit identity', 'Staged mobile/desktop filter', 'Unpublished and filtered empty', 'Browse failure/retry', 'On-demand viewer/focus'],
    inputs: inputs.map(file => ({ file, sha256: hash(file) })), snapshots };
fs.writeFileSync('docs/qa/visual-baselines/manifest.json', JSON.stringify(manifest, null, 2) + '\n');
console.log('Recorded 40 reviewed baselines with route/state, geometry, fixture/build hashes and environment.');
