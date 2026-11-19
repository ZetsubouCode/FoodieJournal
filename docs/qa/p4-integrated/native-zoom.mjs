import { chromium, firefox, webkit, expect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';

const output = 'docs/qa/p4-integrated', availability = [];
for (const [engine, type] of Object.entries({ firefox, webkit })) {
    try { const browser = await type.launch(); availability.push({ engine, available: true, version: browser.version() }); await browser.close(); }
    catch (error) { availability.push({ engine, available: false, reason: error.message.split('\n').find(s => /Executable|exist/.test(s)) || error.message.split('\n')[0] }); }
}
fs.writeFileSync(`${output}/engines.json`, JSON.stringify(availability, null, 2));
const browser = await chromium.launch({ headless: false, args: ['--window-size=1440,1000'] });
const context = await browser.newContext({ viewport: null, reducedMotion: 'reduce' }), page = await context.newPage();
const origin = 'http://127.0.0.1:8015';
await page.goto(origin + '/explore'); await page.evaluate(() => document.fonts.ready);
const measure = () => page.evaluate(() => ({ width: innerWidth, height: innerHeight, dpr: devicePixelRatio, root: getComputedStyle(document.documentElement).fontSize,
    scrollWidth: document.documentElement.scrollWidth, zoom: getComputedStyle(document.documentElement).zoom, visualScale: visualViewport.scale }));
const initial = await measure();
console.log('Waiting for native browser zoom to reach 200%; initial: ' + JSON.stringify(initial));
try {
await page.waitForFunction(dpr => Math.abs(devicePixelRatio / dpr - 2) < .02, initial.dpr, { timeout: 180000 });
const states = [];
for (const [name, route] of Object.entries({ home: '/', explore: '/explore', visit: '/visit/demo-bara-di-ujung-hari', place: '/place/demo-bara-senja', error: '/p4-missing' })) {
    await page.goto(origin + route);
    if (name !== 'error') await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.evaluate(() => document.fonts.ready);
    const metrics = await measure();
    assert.ok(Math.abs(metrics.dpr / initial.dpr - 2) < .02); assert.ok(metrics.scrollWidth <= metrics.width);
    await page.keyboard.press('Tab'); await expect(page.getByRole('link', { name: 'Lewati ke isi', exact: true })).toBeFocused();
    await page.keyboard.press('Enter'); await expect(page.locator('main')).toBeFocused();
    await page.screenshot({ path: `${output}/native-zoom-${name}.jpg`, type: 'jpeg', quality: 80 });
    states.push({ name, ...metrics });
}
fs.writeFileSync(`${output}/native-zoom.json`, JSON.stringify({ browser: browser.version(), method: 'Headed Chromium native Ctrl+plus via Windows input; no CSS zoom/root-size/device metrics override', initial, states }, null, 2));
console.log('Native zoom observations saved; waiting for Ctrl+0 reset.');
await page.waitForFunction(dpr => Math.abs(devicePixelRatio - dpr) < .02, initial.dpr, { timeout: 180000 });
} finally { await browser.close(); }
