import { chromium } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';

const phase = process.argv[2];
assert.ok(['before', 'after'].includes(phase));
const output = `docs/qa/p3-visit/${phase}.json`;
assert.ok(!fs.existsSync(output), 'Keep genuine before/after evidence; explicitly archive before repeating.');
const browser = await chromium.launch();
const report = { browser: browser.version(), build: createHash('sha256').update(fs.readFileSync('public/build/manifest.json')).digest('hex'), samples: [] };
for (const width of [390, 1440]) {
    for (let run = 0; run < 3; run++) {
        const context = await browser.newContext({ viewport: { width, height: 1000 }, reducedMotion: 'reduce' });
        const page = await context.newPage();
        await page.addInitScript(() => {
            window.vitals = { lcp: 0, cls: 0 };
            new PerformanceObserver(list => { for (const e of list.getEntries()) window.vitals.lcp = e.startTime; }).observe({ type: 'largest-contentful-paint', buffered: true });
            new PerformanceObserver(list => { for (const e of list.getEntries()) if (!e.hadRecentInput) window.vitals.cls += e.value; }).observe({ type: 'layout-shift', buffered: true });
        });
        for (const cache of ['cold', 'warm']) {
            const response = await page.goto('http://127.0.0.1:8015/visit/demo-bara-di-ujung-hari');
            assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
            await page.waitForFunction(() => document.documentElement.dataset.uiReady === 'true');
            await page.evaluate(() => document.fonts.ready);
            await page.locator('.dossier-hero img').evaluate(n => n.decode());
            await page.waitForTimeout(1000);
            const sample = await page.evaluate(() => ({ ...window.vitals, media: performance.getEntriesByType('resource').filter(e => e.name.includes('/media/')).map(e => ({ url: e.name, bytes: e.encodedBodySize })), hero: document.querySelector('.dossier-hero img').currentSrc }));
            assert.ok(sample.media.every(e => !e.url.endsWith('/original')));
            report.samples.push({ width, run, cache, ...sample });
        }
        if (run === 0) {
            await page.locator('img[src]').evaluateAll(nodes => Promise.all(nodes.map(n => { n.loading = 'eager'; return n.decode(); })));
            await page.screenshot({ path: `docs/qa/p3-visit/${phase}-${width}-story.jpg`, fullPage: true, type: 'jpeg', quality: 78 });
        }
        await context.close();
    }
}
fs.writeFileSync(output, JSON.stringify(report, null, 2));
await browser.close();
