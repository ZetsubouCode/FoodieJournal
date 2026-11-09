import { chromium, expect as baseExpect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';

const output = 'docs/qa/p2-discovery', browser = await chromium.launch();
const expect = baseExpect.configure({ timeout: 15000 });
const report = { browser: browser.version(), build: createHash('sha256').update(fs.readFileSync('public/build/manifest.json')).digest('hex'), states: [], errors: [] };
const gate = () => { let open; const wait = new Promise(resolve => { open = resolve; }); return { wait, open }; };
async function ready(page) {
    const response = await page.goto('http://127.0.0.1:8015/explore'); assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.evaluate(async () => { await document.fonts.ready; await Promise.all([...document.images].filter(n => n.src).map(n => { n.loading = 'eager'; return n.decode(); })); });
}
async function shot(page, width, state, target = page) {
    await target.screenshot({ path: `${output}/${width}-${state}.jpg`, type: 'jpeg', quality: 78, animations: 'allow' });
    report.states.push({ width, state, url: page.url(), ...await page.locator('[data-discovery]').evaluate(n => ({ mode: n.dataset.mode, busy: n.querySelector('[data-discovery-results]').getAttribute('aria-busy'), count: n.querySelector('.memory-reel__head > p').textContent.trim(), overflow: document.documentElement.scrollWidth > innerWidth })) });
}
for (const width of [375, 768, 1440, 1920]) {
    const context = await browser.newContext({ viewport: { width, height: 1000 }, reducedMotion: 'no-preference', locale: 'id-ID', timezoneId: 'Asia/Jakarta' });
    const page = await context.newPage(); page.on('pageerror', e => report.errors.push(e.message)); await ready(page);
    const board = page.locator('.curiosity-index'), tile = page.locator('.curiosity-surface--style');
    await shot(page, width, 'initial');
    await tile.hover(); await page.waitForTimeout(230); await shot(page, width, 'hover', board);
    await tile.focus(); await shot(page, width, 'focus', board);
    const box = await tile.boundingBox(); await page.mouse.move(box.x + box.width / 2, box.y + box.height / 2); await page.mouse.down();
    await page.waitForTimeout(150); await shot(page, width, 'press', board); await page.mouse.up();
    // Let this genuine first activation settle before recording a held second request.
    await expect(page.locator('[data-discovery-results]')).toHaveAttribute('aria-busy', 'false');
    await page.goBack(); await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', 'visit');
    const held = gate(); let fail = true;
    await page.route('**/explore?**', async route => { if (route.request().headers()['x-explore-fragment'] !== '1') return route.continue(); await held.wait; if (fail) await route.fulfill({ status: 503, body: '' }); else await route.continue(); });
    await tile.click(); await expect(tile).toHaveAttribute('data-pending', 'true');
    await shot(page, width, 'pending', board); held.open();
    await expect(page.locator('[data-discovery-error]')).toBeVisible();
    await page.locator('[data-discovery-error]').scrollIntoViewIfNeeded(); await shot(page, width, 'error');
    fail = false; await page.locator('[data-discovery-retry]').click(); await expect(tile).toHaveAttribute('data-selected', 'true');
    await shot(page, width, 'selected', board);
    await page.getByRole('button', { name: /^Filter/ }).click();
    const dialog = page.locator('[data-filter-drawer]'); await dialog.locator('[data-filter-group-open=rating]').click();
    await shot(page, width, 'filter', dialog); await page.keyboard.press('Escape');
    await page.locator('#discovery-search').fill('p2-tidak-ada'); await page.locator('#discovery-search').press('Enter');
    await expect(page.locator('.empty-state')).toContainText('Tidak ada catatan yang cocok'); await shot(page, width, 'empty');
    await context.close();
    const loading = await browser.newContext({ viewport: { width, height: 1000 }, reducedMotion: 'reduce', locale: 'id-ID', timezoneId: 'Asia/Jakarta' });
    const photoPage = await loading.newPage(), photos = gate();
    await photoPage.route('**/media/**', async route => { await photos.wait; await route.continue().catch(() => {}); });
    const photoResponse = await photoPage.goto('http://127.0.0.1:8015/explore', { waitUntil: 'domcontentloaded' });
    assert.equal(photoResponse.status(), 200); assert.equal(photoResponse.headers()['x-foodie-visual-fixture'], 'photo-v1');
    await expect(photoPage.locator('html')).toHaveAttribute('data-ui-ready', 'true', { timeout: 15000 }); await photoPage.evaluate(() => document.fonts.ready);
    await expect(photoPage.locator('.curiosity-surface--food img')).toHaveAttribute('data-photo-waiting', 'true');
    await shot(photoPage, width, 'photo-wait'); photos.open(); await loading.close();
}
assert.equal(report.errors.length, 0); assert(report.states.every(s => !s.overflow));
fs.writeFileSync(`${output}/states.json`, JSON.stringify(report, null, 2) + '\n'); await browser.close();
console.log(`Captured ${report.states.length} actual states, including held-request and photo-wait frames, at four widths.`);
