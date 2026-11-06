import { chromium } from '@playwright/test';
import fs from 'node:fs';

const baseURL = process.env.FOODIE_QA_URL || 'http://127.0.0.1:8000';
const output = 'docs/qa/memory-archive-v2';
fs.mkdirSync(output, { recursive: true });
const browser = await chromium.launch();
let context = await browser.newContext({ viewport: { width: 1440, height: 1000 },
    recordVideo: { dir: 'tools/.runtime/archive-v2-video', size: { width: 1440, height: 1000 } } });
let page = await context.newPage();
const errors = [], expectedFailures = [], frames = [], alignment = [];
let simulateFailure = false;
function listen() {
    page.on('pageerror', error => errors.push(error.message));
    page.on('console', message => {
        if (message.type() !== 'error') return;
        if (simulateFailure && message.text().includes('503')) expectedFailures.push(message.text());
        else errors.push(message.text());
    });
}
listen();
const phase = value => page.waitForFunction(state => document.querySelector('[data-discovery-scene]').dataset.phase === state, value, { polling: 'raf' });
async function load(width = 1440, path = '/explore') {
    await page.setViewportSize({ width, height: width < 500 ? 844 : 1000 });
    await page.goto(baseURL + path); await page.waitForSelector('html[data-ui-ready=true]');
    await page.evaluate(() => document.fonts.ready);
    for (const img of await page.locator('[data-slot-photo], [data-memory-photo]').all()) await img.evaluate(node => node.decode().catch(() => {}));
}
async function shot(name, fullPage = false) {
    frames.push({ name, ...await page.evaluate(() => {
        const envelope = document.querySelector('[data-memory-open]');
        return { phase: document.querySelector('[data-discovery-scene]').dataset.phase,
            screen: document.querySelector('[data-memory-showcase]').dataset.screenKind,
            url: location.href, envelope: envelope.getBoundingClientRect().toJSON(),
            tray: envelope.closest('[data-memory-tray]').getBoundingClientRect().toJSON(),
            status: document.querySelector('.memory-machine__status').getBoundingClientRect().toJSON() };
    }) });
    await page.screenshot({ path: output + '/' + name + '.png', fullPage });
    if (/partial|dispensed|opening|emerging/.test(name)) await page.locator('[data-memory-tray]').screenshot({ path: output + '/tray-' + name + '.png' });
}
await load(); await shot('01-idle-empty-tray');
const slot = page.locator('[data-memory-slot=noodles], [data-memory-slot^=food_type]').first();
await slot.focus(); await shot('02-focus'); await slot.hover(); await shot('03-hover');
await page.locator('[data-discovery-scene]').evaluate(node => {
    node.style.setProperty('--dispenser-enter', '10000ms');
    node.style.setProperty('--dispenser-detail', '1000ms');
});
await page.route('**/explore?**', async route => {
    if (route.request().headers()['x-explore-fragment'] === '1') await new Promise(resolve => setTimeout(resolve, 450));
    await route.continue();
});
await slot.click(); await shot('04-selection-processing');
await phase('dispensing');
await page.locator('[data-memory-open]').evaluate(node => {
    const animation = node.getAnimations().find(animation => animation.transitionProperty === 'transform');
    animation.pause(); animation.currentTime = Number(animation.effect.getTiming().duration) * .12;
});
await shot('05-partial-dispense');
await page.locator('[data-memory-open]').evaluate(node => {
    const animation = node.getAnimations().find(animation => animation.transitionProperty === 'transform');
    animation.currentTime = Number(animation.effect.getTiming().duration) * .2;
});
await shot('06-partial-dispense-later');
await page.locator('[data-memory-open]').evaluate(node => node.getAnimations().forEach(animation => animation.finish()));
await phase('ready'); await shot('07-dispensed-collection-overview');
await page.locator('[data-discovery-scene]').evaluate(node => node.style.setProperty('--dispenser-enter', '1800ms'));
await page.locator('[data-memory-open]').click(); await phase('revealing');
await page.waitForTimeout(120); await shot('08-opening');
await phase('extracting'); await page.waitForTimeout(160); await shot('09-photo-emerging');
await phase('revealed'); await shot('10-revealed-full-screen-link');
await page.locator('[data-memory-another]').click(); await phase('ready'); await shot('11-another-memory');
await page.locator('[data-memory-open]').click(); await phase('revealed');
await page.locator('[data-memory-reset]').click(); await phase('idle'); await shot('12-reset-empty-tray');
const video = page.video();
await context.close(); await video.saveAs(output + '/interaction-review-v2.webm');
// Static review is outside the recorded motion context.
context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
page = await context.newPage(); listen();
for (const width of [1440, 390]) {
    await load(width);
    const explore = await page.locator('.site-header').boundingBox();
    const exploreMain = await page.locator('#main').boundingBox();
    await shot('explore-' + width);
    await page.goto(baseURL + '/'); await page.waitForSelector('html[data-ui-ready=true]');
    const home = await page.locator('.site-header').boundingBox();
    const homeMain = await page.locator('#main').boundingBox();
    alignment.push({ width, home, explore, homeMain, exploreMain });
    await page.screenshot({ path: output + '/home-' + width + '.png' });
}
for (const width of [1920, 1280, 1024, 768, 390, 320]) {
    await load(width);
    await page.locator('[data-discovery-scene]').screenshot({ path: output + '/scene-' + width + '.png' });
}
await load();
await page.getByRole('button', { name: /^Filter/ }).click();
await page.locator('[data-filter-group-open=food]').click();
await page.screenshot({ path: output + '/drawer-desktop-categories.png' });
await page.locator('[data-filter-group-open=style]').click();
const choices = page.locator('[data-filter-group=style] input[type=checkbox]');
for (let i = 0; i < Math.min(2, await choices.count()); i++) await choices.nth(i).check();
await page.screenshot({ path: output + '/drawer-desktop-pending.png' });
await page.keyboard.press('Escape');
await load(390);
await page.getByRole('button', { name: /^Filter/ }).click();
await page.locator('[data-filter-group-open=style]').click();
await page.screenshot({ path: output + '/drawer-mobile.png' });
await page.keyboard.press('Escape');
for (const [label, mode] of [['Hidangan', 'food'], ['Tempat', 'place']]) {
    await page.getByRole('link', { name: label, exact: true }).click();
    await page.waitForFunction(value => document.querySelector('[data-discovery]').dataset.mode === value, mode);
    await page.locator('[data-memory-another]').click(); await phase('ready');
    await page.locator('[data-memory-open]').click(); await phase('revealed');
    await page.locator('[data-discovery-scene]').screenshot({ path: output + '/mode-' + mode + '-mobile.png' });
}
await load(1122, '/explore?recommendation=favorit-pribadi');
await page.locator('[data-memory-another]').click(); await phase('ready');
await page.locator('[data-memory-open]').click(); await phase('revealed');
for (const img of await page.locator('.memory-reel img').all()) { await img.scrollIntoViewIfNeeded(); await img.evaluate(node => node.decode().catch(() => {})); }
await page.evaluate(() => scrollTo(0, 0)); await shot('full-page-reference', true);
await load(390, '/explore?q=no-memory-matches-this'); await shot('empty-state');
await load(390); simulateFailure = true;
await page.route('**/explore/memory**', route => route.fulfill({ status: 503, body: 'Visual QA unavailable' }));
await page.locator('[data-memory-another]').click(); await phase('error'); await shot('error-state');
await page.unroute('**/explore/memory**'); simulateFailure = false;
await load(390); await page.emulateMedia({ reducedMotion: 'reduce' });
await page.locator('[data-memory-another]').click(); await phase('ready');
await page.locator('[data-memory-open]').click(); await phase('revealed');
await page.locator('[data-discovery-scene]').screenshot({ path: output + '/reduced-motion-mobile.png' });
fs.writeFileSync(output + '/frames.json', JSON.stringify({ baseURL, note: 'Read-only existing published content. Motion review deliberately slowed; static captures and automated tests use production CSS. Frame geometry is sampled before capture.', frames, alignment, errors, expectedFailures }, null, 2));
await context.close(); await browser.close();
console.log(JSON.stringify({ output, frames: frames.length, errors, expectedFailures }));
