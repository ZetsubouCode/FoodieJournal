import { chromium } from '@playwright/test';
import fs from 'node:fs';

// Dedicated, labeled photographic demo environment; never writes journal data.
const base = 'http://127.0.0.1:8012';
const output = process.env.FOODIE_QA_OUTPUT || 'docs/qa/cinematic';
fs.mkdirSync(output, { recursive: true });
const browser = await chromium.launch();
const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
const page = await context.newPage();
const evidence = { fixture: 'Dedicated cinematic.sqlite, bundled and labeled demo photography. No owner data.', browser: browser.version(), errors: [], checks: [], frames: [] };
page.on('pageerror', error => evidence.errors.push(error.message));
async function load(path, width = 1440, height = 1000) {
    await page.setViewportSize({ width, height });
    const response = await page.goto(base + path);
    if (response.status() >= 400 && path !== '/missing-cinematic-page') throw new Error(path + ': ' + response.status());
    if (path !== '/missing-cinematic-page') await page.waitForSelector('html[data-ui-ready=true]');
    await page.evaluate(() => document.fonts.ready);
    // Exercise native lazy loading before durable photographic screenshots.
    for (const img of await page.locator('img[loading=lazy]').all()) {
        if (await img.isVisible()) {
            await img.scrollIntoViewIfNeeded();
            await img.evaluate(node => node.decode().catch(() => {}));
        }
    }
    await page.evaluate(() => scrollTo(0, 0));
    evidence.checks.push({ path, width, height, ...await page.evaluate(() => ({
        overflow: document.documentElement.scrollWidth > innerWidth,
        brokenImages: [...document.images].filter(img => img.offsetParent && img.complete && !img.naturalWidth).map(img => img.getAttribute('src')),
        header: document.querySelector('.site-header').getBoundingClientRect().toJSON(),
        main: document.querySelector('main').getBoundingClientRect().toJSON(),
    })) });
}
async function shot(name, selector = null) {
    const target = selector ? page.locator(selector) : page;
    if (selector) await target.scrollIntoViewIfNeeded();
    await target.screenshot({ path: output + '/' + name + '.png', ...(!selector ? { fullPage: true } : {}) });
    evidence.frames.push(name);
}
// Detailed mechanical frames are captured by tools/capture-vending-machine.mjs.
await load('/');
await shot('home-hero', '[data-discovery-scene]');
await shot('home-recent', '.home-section[aria-labelledby=recent-title]');
await shot('home-picks', '.home-section[aria-labelledby=picks-title]');
await page.locator('[data-vending-lamp]').click();
await page.locator('[data-lamp-socket]').click();
await page.waitForFunction(() => document.querySelector('[data-machine]').dataset.power === 'on');
await shot('machine-powered', '[data-machine]');
await page.locator('[data-vending-print]').click();
await page.waitForFunction(() => document.querySelector('[data-machine]').dataset.dispenser === 'ready');
await shot('machine-photo-ready', '[data-machine]');
await load('/explore'); await shot('explore-hero', '.curiosity-index'); await shot('explore-results', '[data-discovery]');
await load('/visit/demo-bara-di-ujung-hari'); await shot('visit-full');
for (const [name, selector] of [['hero','.dossier-hero'],['story','.dossier-story'],['food','.dossier-tasting'],['ratings','.dossier-assessment'],['gallery','.dossier-gallery'],['conclusion','.dossier-conclusion']]) await shot('visit-'+name, selector);
await page.locator('[data-view-photo]').first().click(); await page.waitForSelector('[data-viewer-image]:not([hidden])'); await shot('visit-viewer'); await page.keyboard.press('Escape');
await load('/place/demo-bara-senja'); await shot('place-desktop');
await load('/missing-cinematic-page'); await shot('error-desktop');
for (const width of [320, 375, 768, 1024, 1920]) {
    for (const [name,path] of [['home','/'],['explore','/explore'],['visit','/visit/demo-bara-di-ujung-hari']]) {
        await load(path, width, width < 500 ? 844 : 1000);
        if ([375,768].includes(width)) await shot(name+'-'+width);
    }
}
for (const path of ['/', '/explore', '/visit/demo-bara-di-ujung-hari', '/place/demo-bara-senja', '/missing-cinematic-page']) {
    await load(path, 812, 375);
    await page.evaluate(() => { document.documentElement.style.fontSize='200%'; });
    evidence.checks.push({ path, width:812, height:375, textScale:'200%', overflow: await page.evaluate(()=>document.documentElement.scrollWidth > innerWidth) });
}
fs.writeFileSync(output+'/evidence.json', JSON.stringify(evidence,null,2));
console.log(JSON.stringify({ screenshots:evidence.frames.length, checks:evidence.checks.length, errors:evidence.errors, overflow:evidence.checks.filter(x=>x.overflow), broken:evidence.checks.filter(x=>x.brokenImages?.length) }));
await browser.close();
