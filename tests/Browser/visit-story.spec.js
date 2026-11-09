import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const url = 'http://127.0.0.1:8015/visit/demo-bara-di-ujung-hari';
const sparse = 'http://127.0.0.1:8014/visit/qa-tanpa-foto';
const gate = () => { let release; const promise = new Promise(resolve => { release = resolve; }); return { promise, release }; };
async function ready(page, target = url) {
    const response = await page.goto(target);
    if (target === url) expect(response.headers()['x-foodie-visual-fixture']).toBe('photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.evaluate(() => document.fonts.ready);
}
const viewer = page => page.locator('[data-photo-viewer]');
const stage = page => page.locator('[data-viewer-stage]');
const status = page => page.locator('[data-viewer-status]');

test('original wait, derivative fallback, full failure and keyboard retry keep a stable stage and focus', async ({ page }) => {
    const originals = []; page.on('request', r => { if (r.url().endsWith('/original')) originals.push(r.url()); });
    await ready(page); expect(originals).toEqual([]);
    const held = gate(); let fail = true;
    await page.route('**/media/**/original', async route => { await held.promise; if (fail) await route.fulfill({ status: 403, body: '' }); else await route.continue(); });
    const trigger = page.locator('[data-view-photo]').first(); await trigger.focus(); await page.keyboard.press('Enter');
    await expect(status(page)).toContainText('Memuat original 1');
    await expect(stage(page)).toHaveAttribute('aria-busy', 'true');
    const initial = await stage(page).boundingBox();
    await expect(page.locator('[data-viewer-zoom]')).toBeDisabled();
    const scan = await new AxeBuilder({ page }).include('[data-photo-viewer]').withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
    expect(scan.violations.map(v => v.id)).toEqual([]);
    held.release();
    await expect(status(page)).toContainText('Menampilkan pratinjau');
    await expect(page.locator('[data-viewer-image]')).toBeVisible();
    expect((await stage(page).boundingBox()).height).toBe(initial.height);
    await page.route('**/media/**/card', route => route.fulfill({ status: 403, body: '' }));
    await page.locator('[data-viewer-retry]').click();
    await expect(status(page)).toContainText('Foto tidak tersedia');
    await expect(page.locator('[data-viewer-image]')).toBeHidden();
    await expect(stage(page)).toHaveAttribute('aria-busy', 'false');
    expect((await stage(page).boundingBox()).height).toBe(initial.height);
    fail = false; await page.locator('[data-viewer-retry]').focus(); await page.keyboard.press('Enter');
    await expect(status(page)).toHaveText(/Foto 1 dari/);
    await expect(page.locator('[data-dialog-close]:visible')).toBeFocused();
    await page.locator('[data-viewer-zoom]').click(); await expect(page.locator('[data-viewer-zoom]')).toHaveAttribute('aria-pressed', 'true');
    await page.keyboard.press('Escape'); await expect(trigger).toBeFocused();
    await expect(trigger).not.toHaveAttribute('aria-current');
    await expect(page.locator('[data-viewer-image]')).not.toHaveAttribute('src');
});

test('rapid changes and closing pending originals cannot commit a stale image', async ({ page }) => {
    await ready(page);
    const first = page.locator('[data-view-photo]').first(), second = page.locator('[data-view-photo]').nth(1);
    const firstUrl = await first.getAttribute('data-original'), secondUrl = await second.getAttribute('data-original');
    const held = gate();
    await page.route(firstUrl, async route => { await held.promise; await route.continue().catch(() => {}); });
    await first.click();
    const oldImage = await page.locator('[data-viewer-image]').elementHandle();
    await page.keyboard.press('ArrowRight');
    await expect(status(page)).toHaveText(/Foto 2 dari/);
    await expect(page.locator('[data-viewer-image]')).toHaveAttribute('src', secondUrl);
    await oldImage.evaluate(n => { n.dispatchEvent(new Event('error')); n.dispatchEvent(new Event('load')); });
    held.release();
    await expect(status(page)).toHaveText(/Foto 2 dari/);
    await expect(second).toHaveAttribute('aria-current', 'true');
    await page.keyboard.press('Escape'); await expect(first).toBeFocused();
    await page.unroute(firstUrl);
    const closing = gate();
    await page.route(firstUrl, async route => { await closing.promise; await route.continue().catch(() => {}); });
    await first.click(); await expect(stage(page)).toHaveAttribute('aria-busy', 'true');
    const closedImage = await page.locator('[data-viewer-image]').elementHandle();
    await page.keyboard.press('Escape'); closing.release();
    await closedImage.evaluate(n => { n.dispatchEvent(new Event('load')); n.dispatchEvent(new Event('error')); });
    await expect(page.locator('[data-viewer-image]')).not.toHaveAttribute('src');
    await expect(viewer(page)).not.toBeVisible();
});

test('one and forty photo boundaries, editable arrows, native close and text-only no-JS story', async ({ page, browser }) => {
    await page.route('**/visit/demo-bara-di-ujung-hari', async route => {
        const response = await route.fetch(); const html = await response.text();
        await route.fulfill({ response, body: html.replace(/(<div class="gallery">)([\s\S]*?)(<\/div>)/, (_, start, content, end) => start + content.match(/<button[\s\S]*?<\/button>/)[0] + end) });
    });
    await ready(page); await page.locator('[data-view-photo]').click();
    await expect(page.locator('[data-viewer-prev]')).toBeDisabled(); await expect(page.locator('[data-viewer-next]')).toBeDisabled();
    await page.keyboard.press('Escape');
    await page.unroute('**/visit/demo-bara-di-ujung-hari');
    await page.route('**/visit/demo-bara-di-ujung-hari', async route => {
        const response = await route.fetch(); const html = await response.text();
        await route.fulfill({ response, body: html.replace(/(<div class="gallery">)([\s\S]*?)(<\/div>)/, (_, start, content, end) => start + Array(40).fill(content.match(/<button[\s\S]*?<\/button>/)[0]).join('') + end) });
    });
    await ready(page); await expect(page.locator('[data-view-photo]')).toHaveCount(40);
    await page.locator('[data-view-photo]').last().click(); await expect(status(page)).toHaveText('Foto 40 dari 40');
    await expect(page.locator('[data-viewer-next]')).toBeDisabled();
    await viewer(page).evaluate(n => { const input = document.createElement('input'); input.setAttribute('aria-label', 'QA editable'); n.append(input); input.focus(); });
    await page.keyboard.press('ArrowLeft'); await expect(status(page)).toHaveText('Foto 40 dari 40');
    await page.locator('[data-dialog-close]:visible').click(); await expect(page.locator('[data-view-photo]').last()).toBeFocused();
    const context = await browser.newContext({ javaScriptEnabled: false }); const nojs = await context.newPage();
    await nojs.goto(sparse); await expect(nojs.locator('.dossier-story__prose')).toBeVisible();
    await expect(nojs.locator('.tasting-note--text')).toHaveCount(2); await expect(nojs.locator('[data-photo-viewer], #galeri')).toHaveCount(0);
    await expect(nojs.locator('main')).toContainText('Rp0'); await context.close();
});

test('story, long prose, reduced motion and doubled text reflow across desktop and compact sizes', async ({ page }) => {
    test.setTimeout(90000);
    for (const width of [375, 768, 1440, 1920]) {
        await page.setViewportSize({ width, height: 1000 }); await ready(page);
        await expect(page.locator('.dossier-tasting')).toBeVisible(); await expect(page.locator('.dossier-conclusion')).toBeVisible();
        const prose = await page.locator('.dossier-story__prose').evaluate(n => ({ width: n.clientWidth, max: getComputedStyle(n).maxWidth }));
        expect(prose.width).toBeLessThanOrEqual(parseFloat(prose.max) + 1);
        await page.emulateMedia({ reducedMotion: 'reduce' });
        await page.evaluate(() => { document.documentElement.style.fontSize = '200%'; document.querySelector('h1').textContent += ' Catatan panjang menjelang sore di persimpangan jalan'; });
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        if (width <= 768) {
            const heading = await page.locator('.dossier-criterion h3').first().boundingBox();
            const score = await page.locator('.dossier-criterion strong').first().boundingBox();
            expect(score.y).toBeGreaterThanOrEqual(heading.y + heading.height);
        }
        await page.locator('[data-view-photo]').first().click();
        await expect(page.locator('[data-dialog-close]:visible')).toBeInViewport();
        await page.keyboard.press('Escape');
        await ready(page, sparse);
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
    }
    await page.setViewportSize({ width: 667, height: 375 }); await ready(page);
    await page.locator('[data-view-photo]').first().click(); await expect(page.locator('[data-dialog-close]:visible')).toBeInViewport();
    await page.keyboard.press('Escape');
});
