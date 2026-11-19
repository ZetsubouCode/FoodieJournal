import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
const output = 'docs/qa/p4-integrated', browser = await chromium.launch(), samples = [];
const start = JSON.parse(fs.readFileSync(`${output}/starting-state.json`));
assert.equal(createHash('sha256').update(fs.readFileSync('resources/css/home.css')).digest('hex'), start.files.find(f => f.file === 'resources/css/home.css').sha256);
for (const width of [390, 1440]) {
    const page = await browser.newPage({ viewport: { width, height: 1000 }, reducedMotion: 'reduce' });
    let release; const wait = new Promise(resolve => release = resolve);
    await page.route('**/assets/fonts/**', async route => { await wait; await route.continue(); });
    const response = await page.goto('http://127.0.0.1:8015/', { waitUntil: 'domcontentloaded' }); assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    const geometry = () => page.evaluate(() => {
        const rect = selector => { const r = document.querySelector(selector).getBoundingClientRect(); return { y: r.y, width: r.width, height: r.height }; };
        return { title: rect('h1'), scene: rect('.dispenser-scene__machine'), brand: rect('.brand'), fonts: [...document.fonts].map(f => ({ family: f.family, style: f.style, status: f.status })) };
    });
    const before = await geometry(); release(); await page.evaluate(() => document.fonts.ready);
    await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
    const after = await geometry(); samples.push({ width, before, after, sceneDelta: after.scene.y - before.scene.y }); await page.close();
}
await browser.close(); fs.writeFileSync(`${output}/font-diagnostic.json`, JSON.stringify({ method: 'Hold real font requests then release; geometry diagnostic, not a performance sample. Home geometry CSS is byte-identical to P4 starting HEAD. Font declarations are unchanged; current trace records remaining font-swap shifts.', samples }, null, 2));
console.log(JSON.stringify(samples.map(s => ({ width: s.width, beforeTitleHeight: s.before.title.height, afterTitleHeight: s.after.title.height, sceneDelta: s.sceneDelta }))));
