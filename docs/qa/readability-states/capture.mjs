import { chromium } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';

const output = 'docs/qa/readability-states';
const browser = await chromium.launch();
const evidence = { buildManifestSha256: createHash('sha256').update(fs.readFileSync('public/build/manifest.json')).digest('hex'), browser: browser.version(), captured: new Date().toISOString(), pages: [], errors: [] };
const page = await browser.newPage({ reducedMotion: 'reduce' });
page.on('pageerror', e => evidence.errors.push(e.message));
const tags = Array.from({ length: 10 }, (_, i) => `tag[]=label-panjang-untuk-catatan-makan-menjelang-sore-${i + 1}`).join('&');
await page.goto('http://127.0.0.1:8014/visit/qa-tanpa-foto');
const place = await page.getByRole('link', { name: 'Lihat tempat', exact: true }).getAttribute('href');
const states = [
    ['empty-explore', 'http://127.0.0.1:8013/explore', 1],
    ['filtered-explore', 'http://127.0.0.1:8014/explore?q=tidak-ada&view=list', 1],
    ['sparse-explore', `http://127.0.0.1:8014/explore?${tags}`, 1],
    ['text200-explore', `http://127.0.0.1:8014/explore?${tags}`, 2],
    ['sparse-visit', 'http://127.0.0.1:8014/visit/qa-tanpa-foto', 1],
    ['sparse-place', place, 1],
    ['photographic-text200', 'http://127.0.0.1:8012/explore', 2],
];
for (const width of [320, 390, 768, 1440]) {
    await page.setViewportSize({ width, height: 1000 });
    for (const [name, url, scale] of states) {
        const response = await page.goto(url);
        await page.locator('html[data-ui-ready=true]').waitFor();
        await page.evaluate(() => document.fonts.ready);
        await page.evaluate(scale => document.documentElement.style.fontSize = `${scale * 100}%`, scale);
        await page.locator('img').evaluateAll(nodes => Promise.all(nodes.map(n => { n.loading = 'eager'; return n.decode().catch(() => {}); })));
        const measurements = await page.evaluate(() => {
            const metrics = selector => [...document.querySelectorAll(selector)].map(n => {
                const r = n.getBoundingClientRect(), s = getComputedStyle(n);
                return { text: n.textContent.trim(), width: r.width, height: r.height, font: s.fontSize, color: s.color, background: s.backgroundColor };
            });
            return { width: innerWidth, scrollWidth: document.documentElement.scrollWidth,
                controls: metrics('.discovery-tabs a, .discovery-toolbar .btn, .discovery-toolbar select, .discovery-view a, .discovery-search-field button, .discovery-chips a, .visit-card__tag, .visit-card__more, .empty-state .btn'),
                text: metrics('.visit-card__meta, .visit-card__place, .visit-card__rating, .empty-state p, .discovery-search-field input'),
            };
        });
        assert.equal(response.status(), 200);
        assert(measurements.scrollWidth <= width, `${name} ${width}`);
        assert(measurements.controls.every(n => n.width >= 44 && n.height >= 44));
        fs.writeFileSync(`${output}/${name}-${width}.jpg`, await page.screenshot({ type: 'jpeg', quality: 72, fullPage: true }));
        evidence.pages.push({ name, url, scale, ...measurements });
    }
}
await page.setViewportSize({ width: 390, height: 1000 });
for (const [name, url, selector] of [
    ['empty-home', 'http://127.0.0.1:8013/', '.homepage'],
    ['no-picks-home', 'http://127.0.0.1:8014/', '[aria-labelledby="picks-title"]'],
    ['unknown-and-zero-food', 'http://127.0.0.1:8014/explore?tab=food', '[data-discovery-results]'],
]) {
    await page.goto(url);
    await page.locator('html[data-ui-ready=true]').waitFor();
    await page.locator(selector).screenshot({ path: `${output}/${name}-390.jpg`, type: 'jpeg', quality: 75 });
}
await page.goto('http://127.0.0.1:8014/explore');
const overflow = page.locator('[data-card-overflow-button]');
await overflow.focus();
await page.keyboard.press('Enter');
await page.keyboard.press('Escape');
assert(await overflow.evaluate(n => n === document.activeElement && getComputedStyle(n).outlineStyle !== 'none'));
await page.locator('.visit-card').screenshot({ path: `${output}/keyboard-focus-390.jpg`, type: 'jpeg', quality: 80 });
await page.setViewportSize({ width: 1440, height: 1000 });
await page.goto('http://127.0.0.1:8012/explore');
await page.locator('html[data-ui-ready=true]').waitFor();
await page.locator('.curiosity-index img').evaluateAll(nodes => Promise.all(nodes.map(n => { n.loading = 'eager'; return n.decode(); })));
await page.locator('.curiosity-index').screenshot({ path: `${output}/photographic-board-1440.jpg`, type: 'jpeg', quality: 80 });
const requests = [];
page.on('request', r => { if (r.url().includes('/media/')) requests.push(r.url()); });
await page.route('**/media/**', route => route.fulfill({ status: 403, body: '' }));
await page.reload();
const denied = page.locator('.visit-card').filter({ has: page.locator('[data-visit-card-image-fallback]') }).first();
await denied.scrollIntoViewIfNeeded();
await denied.locator('[data-visit-card-image-fallback]:not([hidden])').waitFor();
await denied.screenshot({ path: `${output}/denied-derivative-1440.jpg`, type: 'jpeg', quality: 80 });
assert(!requests.some(url => url.endsWith('/original')));
evidence.deniedRequests = requests;
assert.equal(evidence.errors.length, 0);
fs.writeFileSync(`${output}/measurements.json`, JSON.stringify(evidence, null, 2) + '\n');
await browser.close();
console.log(`Captured ${evidence.pages.length} measured layouts and six supplemental states; no overflow or page errors.`);
