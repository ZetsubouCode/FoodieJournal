import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';

const phase = process.argv[2], output = 'docs/qa/p3-place';
assert.ok(['before', 'after'].includes(phase));
assert.ok(!fs.existsSync(`${output}/${phase}.json`), 'Preserve genuine before/after evidence; explicitly archive repeated runs.');
const browser = await chromium.launch();
const report = { browser: browser.version(), build: createHash('sha256').update(fs.readFileSync('public/build/manifest.json')).digest('hex'), samples: [] };
for (const width of [390, 1440]) for (let run = 0; run < 3; run++) {
    const context = await browser.newContext({ viewport: { width, height: 1000 }, reducedMotion: 'reduce', locale: 'id-ID', timezoneId: 'Asia/Jakarta' });
    const page = await context.newPage();
    await page.addInitScript(() => {
        window.vitals = { lcp: 0, cls: 0 };
        new PerformanceObserver(list => { for (const entry of list.getEntries()) window.vitals.lcp = entry.startTime; }).observe({ type: 'largest-contentful-paint', buffered: true });
        new PerformanceObserver(list => { for (const entry of list.getEntries()) if (!entry.hadRecentInput) window.vitals.cls += entry.value; }).observe({ type: 'layout-shift', buffered: true });
    });
    for (const cache of ['cold', 'warm']) {
        const response = await page.goto('http://127.0.0.1:8015/place/demo-bara-senja');
        assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
        await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
        await page.evaluate(() => document.fonts.ready);
        await page.locator('.place-dossier__photo img').evaluate(n => n.decode());
        await page.waitForTimeout(1000);
        const sample = await page.evaluate(() => ({ ...window.vitals, hero: document.querySelector('.place-dossier__photo img').currentSrc,
            media: performance.getEntriesByType('resource').filter(e => e.name.includes('/media/')).map(e => ({ url: e.name, bytes: e.encodedBodySize })) }));
        assert.ok(sample.media.every(m => !m.url.endsWith('/original')));
        report.samples.push({ width, run, cache, ...sample });
    }
    if (run === 0) {
        await page.locator('img[src]').evaluateAll(nodes => Promise.all(nodes.map(n => { n.loading = 'eager'; return n.decode(); })));
        await page.screenshot({ path: `${output}/${phase}-${width}-place.jpg`, type: 'jpeg', quality: 80, fullPage: true });
    }
    await context.close();
}
fs.writeFileSync(`${output}/${phase}.json`, JSON.stringify(report, null, 2)); await browser.close();
