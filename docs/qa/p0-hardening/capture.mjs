import { chromium } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';

const phase = process.argv[2];
assert(['before', 'after'].includes(phase));
const output = 'docs/qa/p0-hardening';
const browser = await chromium.launch();
const context = await browser.newContext({ reducedMotion: 'reduce', locale: 'id-ID', timezoneId: 'Asia/Jakarta' });
const page = await context.newPage();
const evidence = { phase, browser: browser.version(), motion: 'reduce', measurements: [], errors: [] };
page.on('pageerror', error => evidence.errors.push(error.message));
const routes = { home: '/', explore: '/explore', visit: '/visit/demo-bara-di-ujung-hari', place: '/place/demo-bara-senja', error: '/visual-missing-page' };
async function ready(url) {
    const response = await page.goto(url);
    if (url.includes(':8015')) assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
    if (response.status() === 200) await page.waitForSelector('html[data-ui-ready=true]');
    await page.evaluate(async () => {
        await document.fonts.ready;
        await Promise.all([...document.images].filter(n => n.src).map(n => { n.loading = 'eager'; return n.decode().catch(() => {}); }));
    });
}
async function measure(name, width) {
    evidence.measurements.push({ name, width, url: page.url(), ...await page.evaluate(() => {
        const rect = selector => { const n = document.querySelector(selector); if (!n) return null; const r = n.getBoundingClientRect(); return { x: r.x, y: r.y, width: r.width, height: r.height }; };
        return { viewport: innerWidth, scrollWidth: document.documentElement.scrollWidth, header: rect('.site-header'), main: rect('main'), footer: rect('.site-footer'), inner: rect('.site-footer__inner'),
            bodyFont: getComputedStyle(document.body).fontFamily, headingFont: getComputedStyle(document.querySelector('h1')).fontFamily, background: getComputedStyle(document.body).backgroundImage,
            placeColumns: document.querySelector('.place-dossier') ? getComputedStyle(document.querySelector('.place-dossier')).gridTemplateColumns : null,
            prose: rect('.dossier-story__prose'), cards: [...document.querySelectorAll('.results > article')].slice(0, 4).map(n => n.getBoundingClientRect().width),
            images: [...document.querySelectorAll('img[sizes]')].map(n => ({ slot: n.width, height: n.height, naturalWidth: n.naturalWidth, currentSrc: n.currentSrc, sizes: n.sizes })) };
    }) });
}
for (const width of phase === 'before' ? [390, 1440, 1920, 2560] : [320, 375, 390, 768, 959, 960, 1023, 1024, 1440, 1920, 2560, 3440]) {
    await page.setViewportSize({ width, height: 1000 });
    for (const [name, route] of Object.entries(routes)) {
        await ready('http://127.0.0.1:8015' + route); await measure(name, width);
        if ([390, 1440, 1920, 2560].includes(width)) {
            await page.screenshot({ path: `${output}/${phase}-${name}-${width}.jpg`, quality: 65 });
            await page.locator('.site-footer').screenshot({ path: `${output}/${phase}-${name}-footer-${width}.jpg`, quality: 65 });
        }
    }
}
await ready('http://127.0.0.1:8014/visit/qa-tanpa-foto');
const sparsePlace = new URL(await page.locator('.dossier-hero__place a').getAttribute('href')).pathname;
for (const width of [390, 1440, 2560]) {
    await page.setViewportSize({ width, height: 1000 });
    await ready('http://127.0.0.1:8014' + sparsePlace); await measure('photo-free-place', width);
    await page.screenshot({ path: `${output}/${phase}-photo-free-place-${width}.jpg`, quality: 75 });
}
const credentials = JSON.parse(fs.readFileSync('tools/.runtime/browser-credentials.json'));
await page.setViewportSize({ width: 1440, height: 1000 });
await ready('http://127.0.0.1:8011/admin/login'); await measure('admin-login', 1440);
await page.screenshot({ path: `${output}/${phase}-admin-login-1440.jpg`, quality: 75 });
await page.getByRole('textbox', { name: 'Email', exact: true }).fill(credentials.email);
await page.getByRole('textbox', { name: 'Kata sandi', exact: true }).fill(credentials.password);
await page.getByRole('button', { name: 'Masuk', exact: true }).click(); await page.waitForURL('**/admin');
for (const [name, route] of Object.entries({ 'admin-table': '/admin/visit', 'admin-form': '/admin/visit/create' })) {
    for (const width of [390, 1440]) {
        await page.setViewportSize({ width, height: 1000 }); await ready('http://127.0.0.1:8011' + route); await measure(name, width);
        await page.screenshot({ path: `${output}/${phase}-${name}-${width}.jpg`, quality: 75 });
    }
}
fs.writeFileSync(`${output}/${phase}.json`, JSON.stringify(evidence, null, 2) + '\n');
assert.equal(evidence.errors.length, 0);
assert(evidence.measurements.every(m => m.scrollWidth <= m.viewport));
await browser.close();
console.log(`${phase}: ${evidence.measurements.length} geometry records; screenshots and computed fonts recorded.`);
