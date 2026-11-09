import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';

const browser = await chromium.launch(), report = [];
for (const capped of [true, false]) {
    const context = await browser.newContext({ viewport: { width: 1440, height: 1000 }, reducedMotion: 'reduce' });
    const page = await context.newPage(); let release;
    const held = new Promise(resolve => { release = resolve; });
    await page.route('**/assets/fonts/**', async route => { await held; await route.continue(); });
    await page.addInitScript(() => {
        window.shifts = [];
        new PerformanceObserver(list => { for (const e of list.getEntries()) if (!e.hadRecentInput) window.shifts.push({ value: e.value, sources: e.sources.map(s => ({ node: s.node?.className, previous: s.previousRect.toJSON(), current: s.currentRect.toJSON() })) }); }).observe({ type: 'layout-shift', buffered: true });
    });
    const response = await page.goto('http://127.0.0.1:8015/place/demo-bara-senja', { waitUntil: 'domcontentloaded' });
    assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.locator('.place-history > .muted').evaluate((node, capped) => { node.style.maxWidth = capped ? 'var(--reading-max)' : 'none'; }, capped);
    const bounds = () => page.evaluate(() => ({ caption: document.querySelector('.place-history > .muted').getBoundingClientRect().toJSON(), group: document.querySelector('.place-history__group').getBoundingClientRect().toJSON(), fonts: document.fonts.status }));
    await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
    const before = await bounds(); await page.evaluate(() => { window.shifts = []; });
    release(); await page.evaluate(() => document.fonts.ready);
    await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
    report.push({ capped, before, after: await bounds(), shifts: await page.evaluate(() => window.shifts) }); await context.close();
}
fs.writeFileSync('docs/qa/p3-place/font-shift-investigation.json', JSON.stringify(report, null, 2)); await browser.close();
