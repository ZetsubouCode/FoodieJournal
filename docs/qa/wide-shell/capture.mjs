import { chromium } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';

const phase = process.argv[2];
assert(['before', 'after'].includes(phase), 'Use before or after against isolated cinematic QA on 8012');
const output = 'docs/qa/wide-shell';
const browser = await chromium.launch();
const context = await browser.newContext({ reducedMotion: 'reduce' });
const page = await context.newPage();
const evidence = { phase, date: '2026-11-09', fixture: 'Existing isolated cinematic.sqlite / cinematic-media; fictional demo photography, no owner data.', browser: browser.version(), pages: [], pageErrors: [] };
page.on('pageerror', e => evidence.pageErrors.push(e.message));
async function load(path) {
    const response = await page.goto('http://127.0.0.1:8012' + path);
    if (response.status() === 200) await page.waitForSelector('html[data-ui-ready=true]');
    await page.evaluate(async () => {
        await document.fonts.ready;
        await Promise.all([...document.images].filter(n => n.getBoundingClientRect().top < innerHeight).map(n => n.decode().catch(() => {})));
    });
    return response.status();
}
await load('/');
const visit = new URL(await page.locator('[data-memory-slot]').first().getAttribute('href')).pathname;
await load(visit);
const place = new URL(await page.locator('.dossier-hero__place a').getAttribute('href')).pathname;
const routes = { home: '/', explore: '/explore', visit, place, error: '/missing-error-page-preview' };
evidence.routes = routes;
for (const width of phase === 'before' ? [375, 1440, 1920] : [320, 375, 390, 768, 1024, 1440, 1920]) {
    await page.setViewportSize({ width, height: 1000 });
    for (const [name, path] of Object.entries(routes)) {
        const status = await load(path);
        const measurements = await page.evaluate(() => {
            const rect = selector => { const n = document.querySelector(selector); if (!n) return null; const r = n.getBoundingClientRect(); return { x: r.x, width: r.width }; };
            return { viewport: innerWidth, scrollWidth: document.documentElement.scrollWidth, header: rect('.site-header'), main: rect('main'), footer: rect('.site-footer'), footerInner: rect('.site-footer__inner'), images: [...document.images].filter(n => n.sizes).map(n => ({ sizes: n.sizes, slot: n.getBoundingClientRect().width, currentSrc: n.currentSrc, naturalWidth: n.naturalWidth })) };
        });
        evidence.pages.push({ name, path, width, status, ...measurements });
        await page.screenshot({ path: `${output}/${phase}-${name}-${width}.jpg`, type: 'jpeg', quality: 70 });
        if ([375,1440,1920].includes(width)) await page.locator('.site-footer').screenshot({ path: `${output}/${phase}-${name}-footer-${width}.jpg`, type: 'jpeg', quality: 75 });
    }
}
assert.equal(evidence.pageErrors.length, 0);
assert(evidence.pages.every(p => p.scrollWidth <= p.viewport));
fs.writeFileSync(`${output}/${phase}.json`, JSON.stringify(evidence, null, 2) + '\n');
await browser.close();
console.log(JSON.stringify({ phase, checks: evidence.pages.length, errors: evidence.pageErrors }));
