import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';

const output = 'docs/qa/p3-place', browser = await chromium.launch();
const report = { browser: browser.version(), build: createHash('sha256').update(fs.readFileSync('public/build/manifest.json')).digest('hex'), states: [], errors: [] };
async function ready(page, path, origin = 'http://127.0.0.1:8015', decode = true) {
    const response = await page.goto(origin + path);
    assert.equal(response.headers()[origin.endsWith('8015') ? 'x-foodie-visual-fixture' : 'x-foodie-hardening-fixture'], origin.endsWith('8015') ? 'photo-v1' : 'v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true'); await page.evaluate(() => document.fonts.ready);
    if (decode) await page.locator('img[src]').evaluateAll(nodes => Promise.all(nodes.map(n => { n.loading = 'eager'; return n.decode(); })));
}
async function shot(page, width, state, target = page, fullPage = false) {
    const file = `${output}/${width}-${state}.jpg`;
    await target.screenshot({ path: file, type: 'jpeg', quality: 80, animations: 'allow', ...(target === page ? { fullPage } : {}) });
    const data = await page.evaluate(() => ({ overflow: document.documentElement.scrollWidth > innerWidth,
        periods: [...document.querySelectorAll('.place-history__period')].map(n => n.textContent),
        visitLinks: [...document.querySelectorAll('.place-history .visit-card__link')].map(n => n.href),
        hero: document.querySelector('.place-dossier__photo img')?.getAttribute('src') ?? null }));
    assert.equal(data.overflow, false); report.states.push({ width, state, file, url: page.url(), ...data });
}
for (const width of [375, 768, 1440, 1920]) {
    const context = await browser.newContext({ viewport: { width, height: 1000 }, reducedMotion: 'no-preference', locale: 'id-ID', timezoneId: 'Asia/Jakarta' });
    const page = await context.newPage(); page.on('pageerror', e => report.errors.push(e.message));
    await ready(page, '/place/demo-bara-senja');
    await shot(page, width, 'photo-story', page, true);
    await shot(page, width, 'history', page.locator('.place-history'));
    await shot(page, width, 'footer', page.locator('.site-footer'));
    const link = page.locator('.place-history .visit-card__link').first();
    await link.focus(); await shot(page, width, 'keyboard-focus', page.locator('.place-history__group').first());
    await page.keyboard.press('Enter'); await expect(page.locator('.visit-dossier')).toBeVisible(); await page.waitForLoadState('load');
    await page.evaluate(() => document.fonts.ready); await shot(page, width, 'visit-arrival');
    await page.getByRole('link', { name: 'Lihat tempat', exact: true }).click(); await page.waitForLoadState('load');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true'); await page.evaluate(() => document.fonts.ready);
    await shot(page, width, 'returned-place');
    await ready(page, '/place/qa-stable-cover?page=2', 'http://127.0.0.1:8017');
    await shot(page, width, 'history-page2', page, true);
    await ready(page, '/place/qa-stable-cover?page=99', 'http://127.0.0.1:8017');
    await shot(page, width, 'page-empty', page.locator('.place-history'));
    await ready(page, '/place/qa-text-place', 'http://127.0.0.1:8017');
    await shot(page, width, 'text-only', page, true);
    await page.emulateMedia({ reducedMotion: 'reduce' }); await page.evaluate(() => { document.documentElement.style.fontSize = '200%'; });
    await shot(page, width, 'reduced-text200', page, true);
    await page.route('**/media/**', route => route.abort());
    await ready(page, '/place/demo-bara-senja', 'http://127.0.0.1:8015', false);
    await expect(page.locator('.place-dossier__photo .photo-fallback')).toBeVisible();
    await shot(page, width, 'photo-failure', page, true);
    await context.close();
}
assert.deepEqual(report.errors, []); assert.equal(report.states.length, 44);
fs.writeFileSync(`${output}/states.json`, JSON.stringify(report, null, 2)); await browser.close();
