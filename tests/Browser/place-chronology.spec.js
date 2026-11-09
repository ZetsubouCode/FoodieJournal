import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const qa = 'http://127.0.0.1:8017';
const photo = 'http://127.0.0.1:8015';
async function ready(page, path = '/place/qa-stable-cover', origin = qa) {
    const response = await page.goto(origin + path);
    expect(response.headers()[origin === qa ? 'x-foodie-hardening-fixture' : 'x-foodie-visual-fixture']).toBe(origin === qa ? 'v1' : 'photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.evaluate(() => document.fonts.ready);
    return response;
}
const periods = page => page.locator('.place-history__period');
const noOverflow = async page => expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);

test('current-page month groups keep the same older hero, 18/2 pagination and published independent story links', async ({ page }) => {
    const originals = []; page.on('request', r => { if (r.url().endsWith('/original')) originals.push(r.url()); });
    await ready(page);
    const source = await page.locator('.place-dossier__photo img').getAttribute('src');
    await expect(periods(page)).toHaveText(['November 2026']);
    expect(await periods(page).first().evaluate(n => {
        const word = document.createRange(); word.setStart(n.firstChild, 0); word.setEnd(n.firstChild, n.textContent.indexOf(' '));
        return word.getClientRects().length;
    })).toBe(1);
    await expect(page.locator('.place-history .visit-card')).toHaveCount(18);
    await expect(page.locator('body')).not.toContainText(/P0H-(PRIVATE|DRAFT)/);
    await page.getByRole('link', { name: 'Berikutnya', exact: true }).click();
    await expect(page).toHaveURL(`${qa}/place/qa-stable-cover?page=2`);
    await expect(page.locator('.place-dossier__photo img')).toHaveAttribute('src', source);
    await expect(periods(page)).toHaveText(['November 2026', 'Januari 2026']);
    await expect(page.locator('.place-history .visit-card')).toHaveCount(2);
    const older = page.locator('.place-history__group').last();
    await expect(older).toContainText('Foto lama QA di halaman kedua');
    const link = older.locator('.visit-card__link'); const href = await link.getAttribute('href');
    await link.focus(); await page.keyboard.press('Enter'); await expect(page).toHaveURL(href);
    await expect(page.locator('.visit-dossier h1')).toHaveText('Foto lama QA di halaman kedua');
    await page.getByRole('link', { name: 'Lihat tempat', exact: true }).focus(); await page.keyboard.press('Enter');
    await expect(page).toHaveURL(`${qa}/place/qa-stable-cover`);
    await page.goBack(); await expect(page).toHaveURL(href);
    await page.goBack(); await expect(page).toHaveURL(`${qa}/place/qa-stable-cover?page=2`);
    await expect(periods(page)).toHaveText(['November 2026', 'Januari 2026']);
    expect(originals).toEqual([]);
    await ready(page, '/place/qa-stable-cover?page=99');
    await expect(page.locator('.place-history__group')).toHaveCount(0);
    await expect(page.locator('.empty-state')).toContainText('Halaman ini sudah kosong');
    await expect(page.locator('.place-dossier__photo img')).toHaveAttribute('src', source);
    await page.getByRole('link', { name: 'Ke halaman pertama', exact: true }).click();
    await expect(page.locator('.place-history .visit-card')).toHaveCount(18);
});

test('long photo-free facts, all-null history and enlarged text reflow without hidden chronology', async ({ page }) => {
    test.setTimeout(120000);
    for (const width of [320, 375, 768, 959, 960, 1440, 1920, 2560]) {
        await page.setViewportSize({ width, height: 1000 });
        for (const scale of [100, 200]) {
            await ready(page, '/place/qa-text-place');
            await page.evaluate(scale => { document.documentElement.style.fontSize = `${scale}%`; }, scale);
            await expect(page.locator('.place-dossier')).toContainText('0 kunjungan dinilai');
            await expect(page.locator('.place-dossier')).toContainText('Belum Dinilai');
            await expect(page.locator('.place-dossier img, .place-dossier__photo, .place-dossier a[href*="maps"]')).toHaveCount(0);
            await expect(page.locator('.place-history__entries')).toHaveCount(1);
            await expect(page.locator('.place-history .visit-card__rating')).toContainText('Belum Dinilai');
            await expect(page.locator('.place-history__period')).toBeVisible();
            await noOverflow(page);
            const card = await page.locator('.place-history .visit-card').boundingBox();
            expect(card.width).toBeLessThanOrEqual(width);
        }
    }
    await page.setViewportSize({ width: 667, height: 375 }); await ready(page, '/place/qa-text-place'); await noOverflow(page);
    const results = await new AxeBuilder({ page }).include('.place-history').withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
    expect(results.violations.map(v => v.id)).toEqual([]);
});

test('reduced motion at load/runtime and failed derivatives preserve readable history and footer', async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await ready(page, '/place/demo-bara-senja', photo);
    const links = await page.locator('.place-history .visit-card__link').evaluateAll(nodes => nodes.map(n => n.href));
    await expect(page.locator('.place-history__entries[hidden]')).toHaveCount(0);
    const card = page.locator('.place-history .visit-card').first(); await card.locator('.visit-card__link').focus();
    expect(await card.evaluate(n => getComputedStyle(n).opacity)).toBe('1');
    await page.emulateMedia({ reducedMotion: 'no-preference' }); await card.hover();
    await page.emulateMedia({ reducedMotion: 'reduce' });
    expect(await card.evaluate(n => getComputedStyle(n).opacity)).toBe('1');
    await page.route('**/media/**', route => route.abort());
    await ready(page, '/place/demo-bara-senja', photo);
    await expect(page.locator('.place-dossier__photo .photo-fallback')).toBeVisible();
    await expect(page.locator('.place-history .visit-card').first().locator('[data-visit-card-image-fallback]')).toBeVisible();
    expect(await page.locator('.place-history .visit-card__link').evaluateAll(nodes => nodes.map(n => n.href))).toEqual(links);
    await expect(page.locator('.site-footer')).toContainText('Ceritanya tinggal.'); await noOverflow(page);
});

test('native no-JS pagination and emulated touch keep Place to Visit journeys independent of enhancements', async ({ browser }) => {
    const context = await browser.newContext({ javaScriptEnabled: false, viewport: { width: 375, height: 900 } });
    const page = await context.newPage(); await page.goto(`${qa}/place/qa-stable-cover`);
    await expect(periods(page)).toHaveText(['November 2026']);
    await page.getByRole('link', { name: 'Berikutnya', exact: true }).click();
    await expect(periods(page)).toHaveText(['November 2026', 'Januari 2026']);
    await page.locator('.place-history .visit-card__link').last().click();
    await expect(page.locator('.visit-dossier h1')).toHaveText('Foto lama QA di halaman kedua'); await context.close();
    const touch = await browser.newContext({ hasTouch: true, viewport: { width: 375, height: 900 }, reducedMotion: 'reduce' });
    const compact = await touch.newPage(); await ready(compact, '/place/demo-bara-senja', photo);
    await compact.locator('.place-history .visit-card__link').first().tap();
    await expect(compact.locator('.visit-dossier')).toBeVisible();
    await compact.getByRole('link', { name: 'Lihat tempat', exact: true }).tap();
    await compact.waitForLoadState('load'); await expect(compact.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await compact.evaluate(() => document.fonts.ready); await expect(periods(compact).first()).toBeVisible();
    await noOverflow(compact); await touch.close();
});

test('loading real fonts does not move the month groups through a changing caption wrap', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 1000 });
    let release; const held = new Promise(resolve => { release = resolve; });
    await page.route('**/assets/fonts/**', async route => { await held; await route.continue(); });
    await page.goto(`${photo}/place/demo-bara-senja`, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
    const before = await page.locator('.place-history__group').first().boundingBox();
    release(); await page.evaluate(() => document.fonts.ready);
    const after = await page.locator('.place-history__group').first().boundingBox();
    expect(Math.abs(after.y - before.y)).toBeLessThan(1);
});
