import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const empty = 'http://127.0.0.1:8013';
const sparse = 'http://127.0.0.1:8014';
async function ready(page, url) {
    await page.goto(url);
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.evaluate(() => document.fonts.ready);
}

test('private-only archive and filtered zero have different honest states, including async reset', async ({ page }) => {
    await ready(page, `${empty}/explore?q=tidak-ada`);
    await expect(page.locator('.empty-state')).toContainText('Arsip belum dipublikasikan');
    await expect(page.locator('.empty-state a')).toHaveCount(0);
    await expect(page.locator('body')).not.toContainText('RAHASIA-READABILITY');
    await expect(page.locator('body')).not.toContainText('DRAFT-READABILITY');
    await ready(page, `${sparse}/explore?view=list&q=tidak-ada`);
    await expect(page.locator('.empty-state')).toContainText('Tidak ada catatan yang cocok');
    await expect(page.locator('#discovery-search')).toHaveValue('tidak-ada');
    await expect(page.getByRole('link', { name: 'Hapus filter Cari: tidak-ada', exact: true })).toBeVisible();
    await page.locator('.empty-state a').click();
    await expect(page.locator('.visit-card')).toHaveCount(1);
    await expect(page).toHaveURL(/tab=visit&view=list$/);
    await page.locator('#discovery-search').fill('tidak-ada');
    await page.locator('#discovery-search').press('Enter');
    await expect(page.locator('.empty-state')).toContainText('Tidak ada catatan yang cocok');
    await expect(page.locator('[data-discovery-results]')).toHaveAttribute('aria-busy', 'false');
    await ready(page, `${sparse}/explore?page=9`);
    await expect(page.locator('.empty-state')).toContainText('Halaman ini sudah kosong');
    await page.locator('.empty-state a').click();
    await expect(page.locator('.visit-card')).toHaveCount(1);
});

test('partial records keep absent recommendations, images, scores, Maps and prices honest', async ({ page, request }) => {
    await ready(page, `${sparse}/`);
    await expect(page.locator('.home-empty')).toContainText(['Belum ada rekomendasi pribadi yang dipublikasikan']);
    await ready(page, `${sparse}/explore`);
    const card = page.locator('.visit-card');
    await expect(card.locator('.photo-fallback')).toBeVisible();
    await expect(card.locator('.visit-card__rating')).toContainText('Belum Dinilai');
    await expect(card.locator('img, .visit-card__recommendation')).toHaveCount(0);
    await expect(page.locator('.curiosity-index__directory')).toContainText('Belum ada rekomendasi yang dipublikasikan');
    await ready(page, `${sparse}/explore?tab=food`);
    await expect(page.locator('[data-discovery-results]')).toContainText('Harga belum dicatat');
    await expect(page.locator('[data-discovery-results]')).toContainText('Rp0');
    await ready(page, `${sparse}/visit/qa-tanpa-foto`);
    await expect(page.locator('main')).toContainText('Penilaian belum dicatat');
    await expect(page.locator('main')).toContainText('Harga belum dicatat');
    await expect(page.locator('main')).toContainText('Rp0');
    await expect(page.locator('main a[href*="maps"]')).toHaveCount(0);
    await page.getByRole('link', { name: 'Lihat tempat', exact: true }).click();
    await expect(page.locator('main')).toContainText('Belum Dinilai');
    await expect(page.locator('main img, main a[href*="maps"]')).toHaveCount(0);
    expect((await request.get(`${sparse}/visit/qa-hidden`)).status()).toBe(404);
    expect((await request.get(`${sparse}/visit/qa-draft`)).status()).toBe(404);
});

test('readable metadata, long chips and touch actions reflow at normal and doubled root text', async ({ page }) => {
    test.setTimeout(120000);
    const query = Array.from({ length: 10 }, (_, i) => `tag[]=${encodeURIComponent(`label-panjang-untuk-catatan-makan-menjelang-sore-${i + 1}`)}`).join('&');
    for (const width of [320, 390, 768, 1440]) {
        for (const scale of [1, 2]) {
            await page.setViewportSize({ width, height: 1000 });
            await ready(page, `${sparse}/explore?${query}`);
            await page.evaluate(scale => document.documentElement.style.fontSize = `${scale * 100}%`, scale);
            const metrics = await page.evaluate(() => {
                const sizes = selector => [...document.querySelectorAll(selector)].map(n => {
                    const r = n.getBoundingClientRect();
                    return { width: r.width, height: r.height, font: parseFloat(getComputedStyle(n).fontSize) };
                });
                const text = document.querySelector('#curiosity-title em').firstChild;
                const range = document.createRange();
                range.setStart(text, 5); range.setEnd(text, text.length);
                const captionsFit = [...document.querySelectorAll('.curiosity-surface')].every(n => {
                    const box = n.getBoundingClientRect();
                    return [...n.querySelectorAll('.curiosity-surface__copy, .photo-fallback')].every(child => {
                        const r = child.getBoundingClientRect();
                        return r.top >= box.top - 1 && r.bottom <= box.bottom + 1 && r.left >= box.left && r.right <= box.right;
                    });
                });
                return { captionsFit, overflow: document.documentElement.scrollWidth > innerWidth, wordLines: range.getClientRects().length,
                    meta: sizes('.visit-card__meta, .visit-card__place, .discovery-chips .chip'),
                    controls: sizes('.discovery-toolbar .btn, .discovery-search-field button, .discovery-toolbar select, .discovery-view a, .discovery-tabs a, .discovery-chips a, .visit-card__tag, .visit-card__more') };
            });
            expect(metrics.overflow, `${width}/${scale}`).toBe(false);
            expect(metrics.captionsFit, `captions at ${width}/${scale}`).toBe(true);
            expect(metrics.wordLines, `penasaran at ${width}/${scale}`).toBe(1);
            expect(metrics.meta.length).toBeGreaterThan(10);
            expect(metrics.meta.every(n => n.font >= 14 * scale)).toBe(true);
            expect(metrics.controls.every(n => n.width >= 44 && n.height >= 44)).toBe(true);
        }
    }
});

test('photographic captions remain inside their tiles with enlarged text', async ({ page }) => {
    for (const width of [320, 390, 768, 1440]) {
        await page.setViewportSize({ width, height: 1000 });
        await ready(page, 'http://127.0.0.1:8012/explore');
        await page.evaluate(() => document.documentElement.style.fontSize = '200%');
        expect(await page.locator('.curiosity-surface').evaluateAll(nodes => nodes.every(n => {
            const box = n.getBoundingClientRect(), copy = n.querySelector('.curiosity-surface__copy').getBoundingClientRect();
            return copy.top >= box.top && copy.bottom <= box.bottom && copy.left >= box.left && copy.right <= box.right;
        }))).toBe(true);
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
    }
});

test('keyboard focus and overflow controls remain visible and disabled states remain safe without JS', async ({ page, browser }) => {
    await ready(page, `${sparse}/explore`);
    const button = page.locator('[data-card-overflow-button]');
    await button.focus();
    await page.keyboard.press('Enter');
    await expect(button).toHaveAttribute('aria-expanded', 'true');
    await page.keyboard.press('Escape');
    await expect(button).toBeFocused();
    expect(await button.evaluate(n => getComputedStyle(n).outlineStyle)).not.toBe('none');
    const axe = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
    expect(axe.violations.map(v => v.id)).toEqual([]);
    const context = await browser.newContext({ javaScriptEnabled: false });
    const noJs = await context.newPage();
    await noJs.goto(`${sparse}/explore?q=tidak-ada`);
    await expect(noJs.locator('[data-dialog-open="explore-filters"]')).toBeDisabled();
    await noJs.locator('.empty-state a').click();
    await expect(noJs.locator('.visit-card')).toHaveCount(1);
    await context.close();
});

test('denied derivatives show the existing fallback without fetching originals', async ({ page }) => {
    const originals = [];
    page.on('request', request => { if (/\/media\/[^/]+\/original/.test(request.url())) originals.push(request.url()); });
    await page.route('**/media/**', route => route.fulfill({ status: 403, body: '' }));
    await ready(page, 'http://127.0.0.1:8012/explore');
    const card = page.locator('.visit-card').filter({ has: page.locator('[data-visit-card-image-fallback]') }).first();
    await card.scrollIntoViewIfNeeded();
    await expect(card.locator('[data-visit-card-image-fallback]')).toBeVisible();
    await expect(card.locator('img')).toBeHidden();
    expect(originals).toEqual([]);
});
