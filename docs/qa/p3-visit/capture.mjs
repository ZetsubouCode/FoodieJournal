import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';

const output = 'docs/qa/p3-visit', browser = await chromium.launch();
const report = { browser: browser.version(), states: [], errors: [] };
const gate = () => { let release; const promise = new Promise(resolve => { release = resolve; }); return { promise, release }; };
async function ready(page, sparse = false) {
    const response = await page.goto(sparse ? 'http://127.0.0.1:8014/visit/qa-tanpa-foto' : 'http://127.0.0.1:8015/visit/demo-bara-di-ujung-hari');
    if (!sparse) assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.evaluate(async () => { await document.fonts.ready; await Promise.all([...document.images].filter(n => n.src).map(n => { n.loading = 'eager'; return n.decode(); })); });
}
async function shot(page, width, state, target = page, fullPage = false) {
    await target.screenshot({ path: `${output}/${width}-${state}.jpg`, type: 'jpeg', quality: 80, animations: 'allow', ...(target === page ? { fullPage } : {}) });
    const geometry = await page.evaluate(() => ({ overflow: document.documentElement.scrollWidth > innerWidth,
        stage: document.querySelector('[data-photo-viewer]')?.open ? document.querySelector('[data-viewer-stage]').getBoundingClientRect().toJSON() : null,
        status: document.querySelector('[data-viewer-status]')?.textContent }));
    assert.equal(geometry.overflow, false);
    report.states.push({ width, state, url: page.url(), ...geometry });
}
for (const width of [375, 768, 1440, 1920]) {
    const context = await browser.newContext({ viewport: { width, height: 1000 }, reducedMotion: 'no-preference', locale: 'id-ID', timezoneId: 'Asia/Jakarta' });
    const page = await context.newPage(); page.on('pageerror', e => report.errors.push(e.message));
    await ready(page); await shot(page, width, 'story', page, true);
    const note = page.locator('.tasting-note').first(); await shot(page, width, 'note', note);
    const photo = note.locator('.tasting-note__photo'); await photo.hover(); await shot(page, width, 'photo-hover', note);
    const trigger = page.locator('[data-view-photo]').first(); await trigger.focus(); await shot(page, width, 'gallery-focus', page.locator('.dossier-gallery'));
    const held = gate(); let fail = true;
    await page.route('**/media/**/original', async route => { await held.promise; if (fail) await route.fulfill({ status: 403, body: '' }); else await route.continue(); });
    await page.keyboard.press('Enter'); await expect(page.locator('[data-viewer-stage]')).toHaveAttribute('aria-busy', 'true');
    await shot(page, width, 'viewer-wait', page.locator('[data-photo-viewer]')); held.release();
    await expect(page.locator('[data-viewer-status]')).toContainText('Menampilkan pratinjau');
    await shot(page, width, 'viewer-fallback', page.locator('[data-photo-viewer]'));
    await page.route('**/media/**/card', route => route.fulfill({ status: 403, body: '' }));
    await page.locator('[data-viewer-retry]').click(); await expect(page.locator('[data-viewer-status]')).toContainText('Foto tidak tersedia');
    await shot(page, width, 'viewer-failure', page.locator('[data-photo-viewer]'));
    fail = false; await page.locator('[data-viewer-retry]').click(); await expect(page.locator('[data-viewer-status]')).toHaveText(/Foto 1 dari/);
    await shot(page, width, 'viewer-success', page.locator('[data-photo-viewer]'));
    await page.keyboard.press('Escape'); await expect(trigger).toBeFocused(); await shot(page, width, 'focus-restored', page.locator('.dossier-gallery'));
    await page.unroute('**/media/**/card'); await page.unroute('**/media/**/original');
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await ready(page); await page.evaluate(() => { document.documentElement.style.fontSize = '200%'; });
    await shot(page, width, 'reduced-text200', page, true);
    await ready(page, true); await shot(page, width, 'text-only', page, true);
    await context.close();
}
assert.deepEqual(report.errors, []);
fs.writeFileSync(`${output}/states.json`, JSON.stringify(report, null, 2)); await browser.close();
