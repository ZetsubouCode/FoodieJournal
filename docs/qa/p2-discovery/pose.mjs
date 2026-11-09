import { chromium, expect } from '@playwright/test';
import assert from 'node:assert/strict';
import fs from 'node:fs';

const output = 'docs/qa/p2-discovery', browser = await chromium.launch(), records = [];
for (const width of [1440, 375]) {
    const context = await browser.newContext({ viewport: { width, height: 1000 }, reducedMotion: 'no-preference' });
    const page = await context.newPage();
    await page.addInitScript(() => {
        window.posed = []; const animate = Element.prototype.animate;
        Element.prototype.animate = function (frames, options) {
            const a = animate.call(this, frames, options);
            if (this.matches('.results > article')) { a.pause(); window.posed.push(a); }
            return a;
        };
    });
    const response = await page.goto('http://127.0.0.1:8015/explore'); assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true'); await page.evaluate(() => document.fonts.ready);
    await page.locator('.curiosity-surface--style').click(); await expect(page.locator('.curiosity-surface--style')).toHaveAttribute('data-selected', 'true');
    const effects = await page.evaluate(() => window.posed.map(a => ({ duration: a.effect.getTiming().duration, top: a.effect.target.getBoundingClientRect().top })));
    assert(effects.length > 0 && effects.length <= 4); assert(effects.every(e => e.duration === 220 && e.top < 1000));
    await page.locator('[data-discovery-results] img').evaluateAll(nodes => Promise.all(nodes.map(n => { n.loading = 'eager'; return n.decode(); })));
    for (const ms of [0, 110, 220]) {
        await page.evaluate(ms => window.posed.forEach(a => { a.currentTime = ms; }), ms);
        await page.screenshot({ path: `${output}/${width}-result-${ms}.jpg`, type: 'jpeg', quality: 82, animations: 'allow' });
        records.push({ width, ms, effects, states: await page.evaluate(() => window.posed.map(a => ({ opacity: getComputedStyle(a.effect.target).opacity, transform: getComputedStyle(a.effect.target).transform }))) });
    }
    await context.close();
}
fs.writeFileSync(`${output}/poses.json`, JSON.stringify({ method: 'Only local visible-card effects paused/seeked for start/110/220ms frames; not timing/performance evidence.', records }, null, 2) + '\n');
await browser.close(); console.log('Captured visible bounded result-group start/mid/end at desktop and compact.');
