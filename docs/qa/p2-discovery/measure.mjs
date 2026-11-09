import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
import { gzipSync } from 'node:zlib';

const phase = process.argv[2], output = 'docs/qa/p2-discovery';
assert(['before', 'after'].includes(phase));
const browser = await chromium.launch();
const report = { phase, browser: browser.version(), build: createHash('sha256').update(fs.readFileSync('public/build/manifest.json')).digest('hex'), method: 'Unthrottled fixed photographic SQLite fixture; fresh context per sample, cold then same-context warm reload. Native clocks, no seeking or animation rewrite. Normal-time socket-independent Explore navigation trace.', samples: [], errors: [] };
for (const width of [1440, 390]) {
    for (let sample = 1; sample <= 3; sample++) {
        const context = await browser.newContext({ viewport: { width, height: 1000 }, locale: 'id-ID', timezoneId: 'Asia/Jakarta', reducedMotion: 'no-preference' });
        const page = await context.newPage(); page.on('pageerror', e => report.errors.push(e.message));
        await page.addInitScript(() => {
            window.metrics = { lcp: 0, cls: 0 };
            new PerformanceObserver(list => { for (const e of list.getEntries()) window.metrics.lcp = e.startTime; }).observe({ type: 'largest-contentful-paint', buffered: true });
            new PerformanceObserver(list => { for (const e of list.getEntries()) if (!e.hadRecentInput) window.metrics.cls += e.value; }).observe({ type: 'layout-shift', buffered: true });
        });
        const arrivals = [];
        for (const cache of ['cold', 'warm']) {
            const response = await page.goto('http://127.0.0.1:8015/explore');
            assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
            await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
            await page.evaluate(async () => { await document.fonts.ready; const image = document.querySelector('.curiosity-surface img'); if (image) await image.decode(); await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))); });
            arrivals.push({ cache, ...await page.evaluate(() => ({ ...window.metrics, navigation: performance.getEntriesByType('navigation')[0].duration, imageBytes: performance.getEntriesByType('resource').filter(e => e.name.includes('/media/')).reduce((sum, e) => sum + e.transferSize, 0) })) });
        }
        const link = page.locator('.curiosity-surface--style'); await link.scrollIntoViewIfNeeded();
        const url = await link.getAttribute('href');
        const client = await context.newCDPSession(page), events = [];
        client.on('Tracing.dataCollected', data => events.push(...data.value));
        await page.evaluate(() => { window.frames = []; window.sampling = true; function tick(t) { window.frames.push(t); if (window.sampling) requestAnimationFrame(tick); } requestAnimationFrame(tick); });
        await client.send('Tracing.start', { categories: 'devtools.timeline,blink.user_timing', transferMode: 'ReportEvents' });
        await link.click(); await expect(page).toHaveURL(url);
        await expect(page.locator('[data-discovery-results]')).toHaveAttribute('aria-busy', 'false');
        // Observe completion of the bounded entrance, without slowing application work.
        await page.waitForTimeout(250);
        const frames = await page.evaluate(() => { window.sampling = false; return window.frames; });
        const ended = new Promise(resolve => client.once('Tracing.tracingComplete', resolve)); await client.send('Tracing.end'); await ended;
        const gaps = frames.slice(1).map((t, i) => t - frames[i]).sort((a, b) => a - b);
        const trace = Object.fromEntries(['Paint', 'Layout', 'UpdateLayoutTree'].map(name => { const found = events.filter(e => e.name === name && e.ph === 'X'); return [name, { count: found.length, ms: found.reduce((sum, e) => sum + (e.dur ?? 0), 0) / 1000 }]; }));
        const file = `${phase}-${width}-trace-${sample}.json.gz`; fs.writeFileSync(`${output}/${file}`, gzipSync(JSON.stringify({ traceEvents: events })));
        report.samples.push({ width, sample, arrivals, traceFile: file, medianGap: gaps[Math.floor(gaps.length / 2)], p95Gap: gaps[Math.floor(gaps.length * .95)], maxGap: gaps.at(-1), trace });
        await context.close();
    }
}
assert.equal(report.errors.length, 0); assert.equal(report.samples.length, 6);
fs.writeFileSync(`${output}/${phase}.json`, JSON.stringify(report, null, 2) + '\n'); await browser.close();
console.log(`${phase}: three cold/warm arrivals and normal-time navigation traces at each width.`);
