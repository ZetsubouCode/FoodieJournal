import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
const browser = await chromium.launch(), samples = [];
for (const width of [390, 1440]) for (const [name, route, selector] of [
    ['nav', '/', '.site-header nav a[href$="explore"]'], ['lamp', '/', '[data-vending-lamp]'], ['reset', '/', '[data-memory-reset]'],
    ['filter', '/explore', null], ['gallery', '/visit/demo-bara-di-ujung-hari', '[data-view-photo]'], ['history', '/place/demo-bara-senja', '.place-history .visit-card__link'],
]) {
    const page = await browser.newPage({ viewport: { width, height: 1000 }, reducedMotion: 'reduce' });
    const response = await page.goto('http://127.0.0.1:8015' + route); assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true'); await page.evaluate(() => document.fonts.ready);
    if (name === 'reset') { await page.locator('[data-vending-lamp]').click(); await page.locator('[data-lamp-socket]').click(); await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'on'); }
    const control = name === 'filter' ? page.getByRole('button', { name: /^Filter/ }) : page.locator(selector).first();
    await control.scrollIntoViewIfNeeded(); await page.keyboard.press('Tab'); await control.focus();
    await expect(control).toBeFocused(); assert.ok(await control.evaluate(n => n.matches(':focus-visible')));
    const metrics = await control.evaluate((n, history) => { const frame = history ? n.closest('.visit-card').querySelector('.visit-card__title') : n, r = frame.getBoundingClientRect(), s = getComputedStyle(frame); return { x: r.x, y: r.y, width: r.width, height: r.height, color: s.outlineColor, thickness: parseFloat(s.outlineWidth), offset: parseFloat(s.outlineOffset), style: s.outlineStyle }; }, name === 'history');
    assert.ok(metrics.thickness >= 2 && metrics.style !== 'none');
    const actual = `tools/.runtime/p4-focus-${width}-${name}.png`, background = `tools/.runtime/p4-focus-${width}-${name}-background.png`;
    await page.screenshot({ path: actual });
    await control.evaluate((n, history) => (history ? n.closest('.visit-card').querySelector('.visit-card__title') : n).style.setProperty('outline-color', 'transparent', 'important'), name === 'history');
    await page.screenshot({ path: background });
    samples.push({ viewportWidth: width, name, route, ...metrics, actual, background }); await page.close();
}
await browser.close(); fs.writeFileSync('docs/qa/p4-integrated/focus-input.json', JSON.stringify(samples));
console.log(execFileSync('python', ['docs/qa/p4-integrated/focus-contrast.py'], { encoding: 'utf8' }));
