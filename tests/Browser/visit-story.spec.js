import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const url = 'http://127.0.0.1:8018/visit/demo-semangkuk-hangat-blok-m';
const sparse = 'http://127.0.0.1:8014/visit/qa-tanpa-foto';
const gate = () => { let release; const promise = new Promise(resolve => { release = resolve; }); return { promise, release }; };
async function ready(page, target = url) {
    const response = await page.goto(target);
    if (target === url) expect(response.headers()['x-foodie-visual-fixture']).toBe('visit-v1');
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
    await page.route(/\/media\/[^/]+\/card\?viewer=/, route => route.fulfill({ status: 403, body: '' }));
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

test('dish photo opens the matching asset and fullscreen preserves focus on close', async ({ page }) => {
    await ready(page);
    const dish = page.locator('[data-item-photo]').last();
    const galleryPhoto = page.locator('[data-view-photo]').nth(1);
    expect(await dish.getAttribute('data-item-photo')).toBe(await galleryPhoto.getAttribute('data-view-photo'));
    await dish.focus();
    await page.keyboard.press('Enter');
    await expect(status(page)).toHaveText('Foto 2 dari 2');
    await expect(page.locator('[data-viewer-image]')).toHaveAttribute('src', await galleryPhoto.getAttribute('data-original'));
    const fullscreen = page.locator('[data-viewer-fullscreen]');
    await fullscreen.click();
    await expect(fullscreen).toHaveAttribute('aria-pressed', 'true');
    await expect(viewer(page)).toHaveAttribute('data-viewer-expanded', '');
    await expect.poll(() => page.evaluate(() => document.fullscreenElement === document.documentElement)).toBe(true);
    await expect(page.locator('[data-dialog-close]:visible')).toBeInViewport();
    await page.locator('[data-dialog-close]:visible').click();
    await expect(dish).toBeFocused();
    await expect.poll(() => page.evaluate(() => document.fullscreenElement === null)).toBe(true);
    await expect(fullscreen).toHaveAttribute('aria-pressed', 'false');
    await page.evaluate(() => {
        document.documentElement.requestFullscreen = () => Promise.reject(new DOMException('QA fallback'));
    });
    await dish.click();
    await fullscreen.click();
    await expect(fullscreen).toHaveAttribute('aria-pressed', 'true');
    expect(await page.evaluate(() => document.fullscreenElement)).toBeNull();
    await page.keyboard.press('Escape');
    await expect(dish).toBeFocused();
    await expect(fullscreen).toHaveAttribute('aria-pressed', 'false');
});

test('one and forty photo boundaries, editable arrows, native close and text-only no-JS story', async ({ page, browser }) => {
    const setPhotoCount = count => page.route(url, async route => {
        const response = await route.fetch();
        const html = await response.text();
        const body = html.replace(/(<div class="gallery">)([\s\S]*?)(<\/div>)/, (_, start, content, end) => {
            const photo = content.match(/<a[\s\S]*?<\/a>/)[0];
            return start + photo.repeat(count) + end;
        });
        await route.fulfill({ response, body });
    });
    await setPhotoCount(1);
    await ready(page); await page.locator('[data-view-photo]').click();
    await expect(page.locator('[data-viewer-prev]')).toBeDisabled(); await expect(page.locator('[data-viewer-next]')).toBeDisabled();
    await page.keyboard.press('Escape');
    await page.unroute(url);
    await setPhotoCount(40);
    await ready(page); await expect(page.locator('[data-view-photo]')).toHaveCount(40);
    await page.locator('[data-view-photo]').last().click(); await expect(status(page)).toHaveText('Foto 40 dari 40');
    await expect(page.locator('[data-viewer-next]')).toBeDisabled();
    await viewer(page).evaluate(n => { const input = document.createElement('input'); input.setAttribute('aria-label', 'QA editable'); n.append(input); input.focus(); });
    await page.keyboard.press('ArrowLeft'); await expect(status(page)).toHaveText('Foto 40 dari 40');
    await page.locator('[data-dialog-close]:visible').click(); await expect(page.locator('[data-view-photo]').last()).toBeFocused();
    const context = await browser.newContext({ javaScriptEnabled: false }); const nojs = await context.newPage();
    await nojs.goto(sparse); await expect(nojs.locator('.dossier-story__prose')).toBeVisible();
    await expect(nojs.locator('.tasting-note--text')).toHaveCount(2); await expect(nojs.locator('[data-photo-viewer], #galeri')).toHaveCount(0);
    await expect(nojs.locator('main')).toContainText('Rp0');
    await nojs.goto(url);
    const derivative = await nojs.locator('[data-item-photo]').first().getAttribute('href');
    await nojs.locator('[data-item-photo]').first().click();
    await expect(nojs).toHaveURL(derivative);
    await context.close();
});

test('story, long prose, reduced motion and doubled text reflow across desktop and compact sizes', async ({ page }) => {
    test.setTimeout(90000);
    for (const width of [320, 768, 1440, 2560]) {
        await page.setViewportSize({ width, height: 1000 }); await ready(page);
        await expect(page.locator('.dossier-tasting')).toBeVisible(); await expect(page.locator('.dossier-conclusion')).toBeVisible();
        const prose = await page.locator('.dossier-story__prose').evaluate(n => ({ width: n.clientWidth, max: getComputedStyle(n).maxWidth }));
        expect(prose.width).toBeLessThanOrEqual(parseFloat(prose.max) + 1);
        const sides = page.locator('.dossier-observations__side');
        await expect(sides.locator('h3')).toHaveText(['Hal menyenangkan', 'Kurang menyenangkan']);
        if (width >= 1440) {
            expect((await sides.last().boundingBox()).x).toBeGreaterThan((await sides.first().boundingBox()).x);
        }
        await page.emulateMedia({ reducedMotion: 'reduce' });
        await page.evaluate(() => { document.documentElement.style.fontSize = '200%'; document.querySelector('h1').textContent += ' Catatan panjang menjelang sore di persimpangan jalan'; });
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        if (width <= 768) {
            const pros = await sides.first().boundingBox();
            const cons = await sides.last().boundingBox();
            expect(cons.y).toBeGreaterThanOrEqual(pros.y + pros.height);
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

test('V4 geometry, paper reviews, opinion stamp and desktop chapter rail', async ({ browser }, testInfo) => {
    for (const width of [1440, 390]) {
        const mobile = width === 390;
        const page = await browser.newPage({
            viewport: { width, height: mobile ? 844 : 1000 },
            isMobile: mobile, hasTouch: mobile, reducedMotion: 'reduce',
        });
        await ready(page);
        const opinion = page.locator('#opinion');
        const quote = opinion.locator('blockquote');
        const eyebrow = opinion.locator('.dossier-eyebrow');
        const stamp = opinion.locator('.dossier-stamp');
        await expect(eyebrow).toHaveText('05 / PENDAPAT');
        await expect(stamp).toHaveText('Layak dicoba');
        expect(await stamp.evaluate(node => node.tagName)).toBe('SPAN');
        const labelBox = await eyebrow.boundingBox(), quoteBox = await quote.boundingBox(), stampBox = await stamp.boundingBox();
        expect(stampBox.y).toBeGreaterThan(quoteBox.y + quoteBox.height);
        if (mobile) {
            expect(labelBox.y + labelBox.height).toBeLessThan(quoteBox.y);
        } else {
            expect(Math.abs(labelBox.y - quoteBox.y)).toBeLessThan(12);
            expect(stampBox.x).toBeGreaterThan(quoteBox.x + quoteBox.width / 2);
        }
        const shell = await page.locator('main').boundingBox();
        for (const selector of ['.site-header__inner', '.dossier-hero', '.dossier-facts', '.dossier-section', '.site-footer__inner']) {
            for (const region of await page.locator(selector).all()) {
                const box = await region.boundingBox();
                expect(Math.abs(box.x - shell.x)).toBeLessThan(1);
                expect(Math.abs(box.width - shell.width)).toBeLessThan(1);
            }
        }
        expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
        await expect(page.locator('.tasting-note__spread')).toContainText('suapan terakhir.');
        await expect(page.locator('.tasting-note__spread .prose')).toHaveCSS('column-count', mobile ? '1' : '2');
        await expect(page.locator('.tasting-note--text')).toHaveCount(1);
        await expect(page.locator('.rating__star--half')).toHaveCount(2);

        const rail = page.locator('[data-chapter-rail]');
        if (mobile) {
            await expect(rail).toBeHidden();
        } else {
            await page.evaluate(() => scrollTo(0, document.querySelector('#tasting').offsetTop - 300));
            await expect(rail.locator('a[href="#tasting"]')).toHaveAttribute('aria-current', 'location');
            await expect(rail).toHaveCSS('opacity', '1');
            await expect(rail).toHaveCSS('pointer-events', 'none', { timeout: 3500 });
            await rail.locator('a[href="#opinion"]').focus();
            await expect(rail).toHaveCSS('opacity', '1');
            await page.keyboard.press('Enter');
            await expect(page).toHaveURL(/#opinion$/);
        }
        if (process.env.FOODIE_VISIT_EVIDENCE) {
            // Capture the resting page after the keyboard and rail checks.
            await rail.evaluate(node => { node.hidden = true; });
            await page.evaluate(async () => {
                document.activeElement.blur();
                const photos = [...document.querySelectorAll('img.media-image')];
                photos.forEach(photo => { photo.loading = 'eager'; });
                await Promise.all(photos.map(photo => photo.decode()));
                scrollTo(0, 0);
            });
            const capture = name => ({ path: testInfo.outputPath(`${name}-${width}.jpg`), quality: 88 });
            await page.screenshot(capture('hero'));
            await page.screenshot({ ...capture('visit'), fullPage: true });
            await opinion.screenshot(capture('pendapat'));
            if (mobile) {
                await page.locator('[data-item-photo]').last().tap();
                await expect(status(page)).toHaveText('Foto 2 dari 2');
                await viewer(page).screenshot(capture('viewer'));
            }
        }
        await page.close();
    }
});

test('V1 Visit chrome links and motion switch stay usable with keyboard and reduced motion', async ({ page, browser }) => {
    await page.setViewportSize({ width: 1843, height: 1000 });
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await ready(page);
    const context = page.getByRole('navigation', { name: 'Navigasi catatan' });
    const motion = context.getByRole('switch', { name: 'Gerak', exact: true });
    const primary = page.getByRole('navigation', { name: 'Navigasi utama' });
    const explore = primary.getByRole('link', { name: 'Jelajah' });
    const current = context.getByRole('link', { name: 'Catatan ini' });
    const place = await page.locator('.dossier-hero__place a').getAttribute('href');
    const footer = page.getByRole('navigation', { name: 'Navigasi kaki halaman' });
    const transition = () => explore.evaluate(node => getComputedStyle(node, '::after').transitionDuration);

    await expect(current).toHaveAttribute('aria-current', 'page');
    await expect(explore).not.toHaveAttribute('aria-current');
    await expect(motion).not.toBeChecked();
    await expect(motion).toHaveText('Gerak nonaktif');
    expect(await transition()).toBe('0s');
    await motion.focus();
    await page.keyboard.press('Space');
    await expect(motion).toBeChecked();
    await expect(motion).toHaveText('Gerak aktif');
    expect(await transition()).toBe('0.14s');
    await expect(motion).toHaveCSS('outline-style', 'solid');
    await page.emulateMedia({ reducedMotion: 'no-preference' });
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await expect(motion).not.toBeChecked();
    expect(await transition()).toBe('0s');

    await current.focus();
    await page.keyboard.press('Enter');
    await expect(page.locator('main')).toBeFocused();
    await expect(page).toHaveURL(/#main$/);
    await footer.getByRole('link', { name: 'Lihat hidangan' }).click();
    await expect(page.locator('#tasting h2')).toBeInViewport();
    await expect(page).toHaveURL(/#tasting$/);
    await footer.getByRole('link', { name: 'Kembali ke atas' }).click();
    await expect(page).toHaveURL(/#top$/);
    expect(await page.evaluate(() => scrollY)).toBe(0);
    await expect(context.getByRole('link', { name: 'Tentang tempat' })).toHaveAttribute('href', place);
    await footer.getByRole('link', { name: 'Tentang tempat' }).click();
    await expect(page).toHaveURL(place);
    await expect(context).toHaveCount(0);

    const touch = await browser.newContext({ isMobile: true, hasTouch: true, viewport: { width: 390, height: 844 }, javaScriptEnabled: false });
    const mobile = await touch.newPage();
    await mobile.goto(url);
    await expect(mobile.getByRole('switch')).toHaveCount(0);
    await mobile.getByRole('navigation', { name: 'Navigasi catatan' }).getByRole('link', { name: 'Tentang tempat' }).tap();
    await expect(mobile).toHaveURL(place);
    await touch.close();
});
