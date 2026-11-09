import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';

const browser = await chromium.launch(), report = { browser: browser.version(), method: 'Paired same-build cold client presentation diagnostic, not a replacement for genuine before/after samples. Both variants use identical server response/query work and request interception; old variant restores the original ungrouped history HTML.', samples: [] };
for (const width of [390, 1440]) for (let run = 0; run < 3; run++) {
    for (const grouped of (run % 2 ? [true, false] : [false, true])) {
        const context = await browser.newContext({ viewport: { width, height: 1000 }, reducedMotion: 'reduce', locale: 'id-ID', timezoneId: 'Asia/Jakarta' });
        const page = await context.newPage();
        await page.route('**/place/demo-bara-senja', async route => {
            const response = await route.fetch(); assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
            let html = await response.text();
            if (!grouped) html = html.replace(/<section class="section place-history"[\s\S]*?<\/section>/, section => {
                const cards = section.match(/<article class="visit-card"[\s\S]*?<\/article>/g); assert.equal(cards.length, 2);
                return `<section class="section"><h2>Riwayat kunjungan</h2><p class="muted">Harga, hidangan, dan opini merupakan catatan pada tanggal masing-masing kunjungan.</p><div class="results results--visit results--list">${cards.join('')}</div></section>`;
            });
            await route.fulfill({ response, body: html });
        });
        await page.addInitScript(() => {
            window.metrics = { lcp: 0, element: null, cls: 0 };
            new PerformanceObserver(list => { for (const e of list.getEntries()) { window.metrics.lcp = e.startTime; window.metrics.element = e.element?.className; } }).observe({ type: 'largest-contentful-paint', buffered: true });
            new PerformanceObserver(list => { for (const e of list.getEntries()) if (!e.hadRecentInput) window.metrics.cls += e.value; }).observe({ type: 'layout-shift', buffered: true });
        });
        await page.goto('http://127.0.0.1:8015/place/demo-bara-senja');
        await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true'); await page.evaluate(() => document.fonts.ready);
        await page.locator('.place-dossier__photo img').evaluate(n => n.decode()); await page.waitForTimeout(1000);
        const sample = await page.evaluate(() => ({ ...window.metrics, ttfb: performance.getEntriesByType('navigation')[0].responseStart,
            hero: document.querySelector('.place-dossier__photo img').currentSrc,
            media: performance.getEntriesByType('resource').filter(e => e.name.includes('/media/')).map(e => ({ url: e.name, bytes: e.encodedBodySize, responseEnd: e.responseEnd })) }));
        assert.ok(sample.media.every(m => !m.url.endsWith('/original'))); report.samples.push({ width, run, grouped, ...sample }); await context.close();
    }
}
fs.writeFileSync('docs/qa/p3-place/presentation-diagnostic.json', JSON.stringify(report, null, 2)); await browser.close();
