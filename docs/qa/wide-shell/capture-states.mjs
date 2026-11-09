import { chromium } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';

const output = 'docs/qa/wide-shell';
const baseline = JSON.parse(fs.readFileSync(`${output}/after.json`));
const browser = await chromium.launch();
const context = await browser.newContext({ reducedMotion: 'reduce' });
const page = await context.newPage();
const states = { text200: [], photoFreeVisit: '/visit/demo-teh-tanpa-kamera' };
async function load(path) {
    await page.goto('http://127.0.0.1:8012' + path);
    if (path !== baseline.routes.error) await page.waitForSelector('html[data-ui-ready=true]');
    await page.evaluate(() => document.fonts.ready);
}
const shot = name => page.screenshot({ path: `${output}/${name}.jpg`, type: 'jpeg', quality: 70 });
for (const width of [375,1440]) {
    await page.setViewportSize({ width, height: 1000 });
    for (const [name,path] of Object.entries(baseline.routes)) {
        await load(path); await page.evaluate(() => document.documentElement.style.fontSize='200%');
        const scrollWidth = await page.evaluate(() => document.documentElement.scrollWidth);
        assert(scrollWidth <= width);
        states.text200.push({ name, width, scrollWidth });
        await shot(`after-${name}-text200-${width}`);
    }
}
await page.setViewportSize({ width:1920,height:1080 }); await page.emulateMedia({ reducedMotion:'no-preference' }); await load('/');
await page.locator('[data-vending-lamp]').click(); await page.locator('[data-lamp-socket]').click();
await page.waitForFunction(() => document.querySelector('[data-machine]').dataset.power === 'on');
await page.locator('[data-vending-print]').click(); await page.waitForFunction(() => document.querySelector('[data-machine]').dataset.dispenser === 'ready');
await page.locator('[data-discovery-scene]').screenshot({ path: `${output}/after-machine-ready-1920.jpg`, type:'jpeg',quality:75 });
states.machine = await page.locator('[data-machine]').evaluate(n => ({ power:n.dataset.power, dispenser:n.dataset.dispenser, width:n.getBoundingClientRect().width, sourceWidth:n.querySelector('.memory-machine__shell').naturalWidth }));
assert(states.machine.width <= states.machine.sourceWidth);
await page.keyboard.press('Tab'); await page.locator('.site-footer__nav a').first().focus();
await page.locator('.site-footer').screenshot({ path: `${output}/after-footer-focus-1920.jpg`, type:'jpeg',quality:75 });
await load(states.photoFreeVisit); assert.equal(await page.locator('.dossier-hero--text').count(),1); await shot('after-visit-no-photo');
await load(baseline.routes.place); await page.locator('.place-dossier__photo').evaluate(n => n.remove());
states.placePhotoRemoval = 'DOM-only layout stress, not a new database fixture or a production fallback implementation';
assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)); await shot('after-place-photo-removed');
await page.goto('http://127.0.0.1:8011/admin/login'); await page.evaluate(() => document.fonts.ready); await shot('after-admin-login-1920');
states.adminWidth = (await page.locator('main').boundingBox()).width;
assert.equal(states.adminWidth,1280);
fs.writeFileSync(`${output}/states.json`,JSON.stringify(states,null,2)+'\n');
await browser.close(); console.log(JSON.stringify(states));
