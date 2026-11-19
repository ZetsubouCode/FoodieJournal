import fs from 'node:fs';
import { execFileSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import assert from 'node:assert/strict';
import os from 'node:os';

const output = 'docs/qa/p4-integrated';
const hash = file => createHash('sha256').update(fs.readFileSync(file)).digest('hex');
const tracked = execFileSync('git', ['ls-files', '-z'], { encoding: 'utf8' }).split('\0').filter(Boolean);
if (process.argv.includes('--start')) {
    assert.ok(!fs.existsSync(`${output}/starting-state.json`));
    const files = tracked.filter(f => /^(app|resources|config|routes|database|tests|docs\/qa)\//.test(f));
    fs.writeFileSync(`${output}/starting-state.json`, JSON.stringify({ head: execFileSync('git', ['rev-parse', 'HEAD'], { encoding: 'utf8' }).trim(),
        status: execFileSync('git', ['status', '--porcelain=v1'], { encoding: 'utf8' }), files: files.map(file => ({ file, sha256: hash(file) })) }, null, 2));
} else {
    const start = JSON.parse(fs.readFileSync(`${output}/starting-state.json`));
    const read = name => JSON.parse(fs.readFileSync(`${output}/${name}.json`));
    const build = hash('public/build/manifest.json');
    for (const name of ['navigation', 'interactions', 'place']) { assert.equal(read(name).build, build); assert.equal(read(name).samples.length, 48); }
    for (const name of ['contrast', 'contrast-states']) {
        assert.deepEqual(read(name).needsReview, []);
        assert.ok(read(name).layouts.every(l => l.build === build));
    }
    assert.equal(read('browser-release').stats.unexpected, 0); assert.equal(read('browser-release').stats.expected, 86);
    for (const name of ['browser-focused', 'browser-keyboard']) { assert.equal(read(name).stats.unexpected, 0); assert.equal(read(name).stats.expected, 8); }
    assert.equal(read('focus-contrast').samples.length, 12); assert.ok(read('focus-contrast').samples.every(s => s.minimum >= 3));
    assert.equal(read('visual-release').stats.unexpected, 0); assert.equal(read('visual-release').stats.expected, 23);
    assert.equal(read('probe').rejectedExit, 1); assert.equal(read('probe').revertedExit, 0);
    assert.equal(read('probe').baselineSha256Before, read('probe').baselineSha256After);
    assert.ok(read('realtime').every(r => r.build === build));
    const changes = start.files.filter(f => hash(f.file) !== f.sha256);
    const baselineReview = read('baseline-review');
    for (const entry of baselineReview.accepted) {
        assert.equal(hash(entry.file), entry.after);
        assert.equal(hash(`${output}/previous-baselines/${entry.file.split('/').at(-1)}`), entry.before);
    }
    const changedBaselineNames = baselineReview.accepted.map(f => f.file);
    assert.deepEqual(changes.map(f => f.file).sort(), [...changedBaselineNames, 'resources/css/discovery.css', 'resources/css/public-chrome.css', 'resources/css/vending-machine.css', 'resources/css/visit-card.css', 'resources/views/components/discovery/dispenser.blade.php',
        'resources/views/errors/layout.blade.php', 'resources/views/components/domain/visit-card.blade.php', 'resources/views/public/explore-fragment.blade.php',
        'tests/Browser/p0-hardening.spec.js', 'tests/Browser/performance.spec.js'].sort());
    const inputs = ['public/build/manifest.json', 'public/assets/css/error.css', 'playwright.config.js', 'playwright.visual.config.js', '.ui-craft/brief.md', '.ui-craft/tokens.md',
        'tools/visual-qa.php', 'tools/p0-hardening-qa.php', 'tools/readability-qa.php', 'tools/browser-environment.php', 'tools/.runtime/visual-photo.sqlite', 'tools/.runtime/visual-empty.sqlite', 'tools/.runtime/p0-hardening.sqlite',
        'docs/roadmap/frontend-experience.md', 'docs/implementation-status.md', 'docs/page-experience.md', 'docs/motion-interaction.md', 'docs/art-direction.md', 'README.md', 'tests/Browser/integrated-gates.spec.js'];
    const rootContracts = ['product-blueprint.md', 'sitemap.md', 'erd-database-specification.md', 'user-flow-wireframe.md', 'design-system-specification.md'];
    for (const file of rootContracts) {
        const original = execFileSync('git', ['show', `${start.head}:${file}`]);
        assert.equal(hash(file), createHash('sha256').update(original).digest('hex'), `Preserve root authority: ${file}`);
    }
    const collect = dir => fs.readdirSync(dir, { withFileTypes: true }).flatMap(f => f.isDirectory() ? collect(`${dir}/${f.name}`) : [`${dir}/${f.name}`]);
    const artifacts = collect(output).filter(f => f !== `${output}/manifest.json`);
    fs.writeFileSync(`${output}/manifest.json`, JSON.stringify({ head: start.head, environment: { platform: process.platform, release: os.release(), node: process.version,
        playwright: JSON.parse(fs.readFileSync('node_modules/@playwright/test/package.json')).version, browser: read('navigation').browser, locale: 'id-ID', timezone: 'Asia/Jakarta', deviceScaleFactor: 1 },
        changedStartingFiles: changes.map(f => ({ ...f, afterSha256: hash(f.file) })),
        preservedCount: start.files.length - changes.length, files: [...new Set([...inputs, ...rootContracts, ...changes.map(f => f.file), ...artifacts])].map(file => ({ file, bytes: fs.statSync(file).size, sha256: hash(file) })) }, null, 2));
    console.log(JSON.stringify({ changedStartingFiles: changes.map(f => f.file), preservedCount: start.files.length - changes.length }));
}
