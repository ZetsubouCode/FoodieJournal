import { chromium } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';

const browser = await chromium.launch();
const page = await browser.newPage({ reducedMotion: 'reduce' });
const metrics = [];
for (const width of [390, 1440, 2560]) {
    await page.setViewportSize({ width, height: 1000 });
    for (const [name, route, selector] of [
        ['home-recent', '/', '[aria-labelledby=recent-title]'],
        ['home-picks', '/', '[aria-labelledby=picks-title]'],
        ['explore-results', '/explore', '.memory-reel'],
        ['visit-story', '/visit/demo-bara-di-ujung-hari', '.dossier-story'],
        ['visit-gallery', '/visit/demo-bara-di-ujung-hari', '.dossier-gallery'],
    ]) {
        const response = await page.goto('http://127.0.0.1:8015' + route);
        assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
        await page.waitForSelector('html[data-ui-ready=true]');
        await page.evaluate(() => document.fonts.ready);
        const section = page.locator(selector);
        await section.locator('img[src]').evaluateAll(nodes => Promise.all(nodes.map(n => { n.loading = 'eager'; return n.decode(); })));
        await section.evaluate(n => n.scrollIntoView({ block: 'start' }));
        await page.screenshot({ path: `docs/qa/p0-hardening/section-${name}-${width}.jpg`, quality: 65 });
        metrics.push({ name, width, ...await section.evaluate(n => ({ sectionWidth: n.getBoundingClientRect().width, images: [...n.querySelectorAll('img')].map(i => ({ width: i.width, height: i.height, naturalWidth: i.naturalWidth, source: i.currentSrc })), proseWidth: n.querySelector('.dossier-story__prose')?.getBoundingClientRect().width })) });
        assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
    }
}
fs.writeFileSync('docs/qa/p0-hardening/sections.json', JSON.stringify(metrics, null, 2) + '\n');
await browser.close();
console.log('Captured 15 lower-page section views, derivative dimensions and reading measure.');
