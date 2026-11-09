import { test, expect } from '@playwright/test';

const nav = '.site-header nav[aria-label="Navigasi utama"]';
const scale = link => link.evaluate(n => new DOMMatrixReadOnly(getComputedStyle(n, '::after').transform).a);
const timing = link => link.evaluate(n => Number.parseFloat(getComputedStyle(n, '::after').transitionDuration) * 1000);
async function frame(link, time) {
    return link.evaluate((n, time) => {
        const animation = n.getAnimations({ subtree: true }).find(a => a.transitionProperty === 'transform');
        if (!animation) throw new Error('Expected the navigation underline transition');
        animation.pause(); animation.currentTime = time;
        return new DOMMatrixReadOnly(getComputedStyle(n, '::after').transform).a;
    }, time);
}
const finish = link => link.evaluate(n => n.getAnimations({ subtree: true }).forEach(a => a.finish()));

for (const route of ['/', '/missing-error-page-preview']) {
    test(`public hover progress is interruptible, left anchored and keyboard accessible on ${route}`, async ({ page }) => {
        await page.goto(route); await page.evaluate(() => document.fonts.ready);
        const link = page.locator(nav).getByRole('link', { name: 'Jelajah', exact: true });
        const prefix = route === '/' ? 'public' : 'error';
        const shot = name => page.locator('.site-header').screenshot({ path: `test-results/navigation-${prefix}-${name}.png`, animations: 'allow' });
        const box = await link.boundingBox();
        expect(await scale(link)).toBe(0);
        expect(await link.evaluate(n => getComputedStyle(n, '::after').transformOrigin.split(' ')[0])).toBe('0px');
        expect(await link.evaluate(n => getComputedStyle(n, '::after').backgroundColor)).toBe('rgb(226, 167, 107)');
        await link.hover(); expect(await timing(link)).toBe(220);
        expect(await frame(link, 0)).toBeCloseTo(0);
        await shot('start');
        expect(await frame(link, 110)).toBeCloseTo(.5);
        await shot('mid');
        expect(await frame(link, 220)).toBeCloseTo(1);
        await shot('end');
        await finish(link); await page.mouse.move(0, 0);
        expect(await timing(link)).toBe(140);
        expect(await frame(link, 70)).toBeCloseTo(.5);
        await shot('leave-mid');
        const leaving = await scale(link);
        await link.hover();
        expect(await frame(link, 0)).toBeCloseTo(leaving);
        await shot('reenter-start');
        await finish(link); expect(await scale(link)).toBe(1);
        expect(await link.boundingBox()).toEqual(box);
        await page.mouse.move(0, 0); await finish(link);
        // Traverse native links so :focus-visible is tested in keyboard modality.
        await page.keyboard.press('Tab'); await page.keyboard.press('Tab');
        await page.keyboard.press('Tab'); await page.keyboard.press('Tab');
        await expect(link).toBeFocused();
        expect(await scale(link)).toBe(1); expect(await timing(link)).toBe(0);
        expect(await link.evaluate(n => getComputedStyle(n).outlineStyle)).toBe('solid');
        await page.screenshot({ path: `test-results/navigation-${prefix}-keyboard-focus.png`, animations: 'allow' });
        await page.keyboard.press('Enter'); await expect(page).toHaveURL(/\/explore$/);
        const active = page.locator(`${nav} [aria-current=page]`);
        expect(await scale(active)).toBe(1);
        await active.hover(); await page.mouse.move(0, 0); expect(await scale(active)).toBe(1);
    });
}

test('shared navigation retains active and hover states across public routes and responsive sizes', async ({ page }) => {
    test.setTimeout(90000);
    await page.goto('/');
    const visit = await page.locator('[data-memory-slot]').first().getAttribute('href');
    await page.goto(visit);
    const place = await page.locator('.dossier-hero__place a').getAttribute('href');
    for (const width of [375, 1440, 1920]) {
        await page.setViewportSize({ width, height: 900 });
        for (const [name, route] of [['home', '/'], ['explore', '/explore'], ['visit', visit], ['place', place], ['error', '/missing-error-page-preview']]) {
            await page.goto(route); await page.mouse.move(0, 0);
            const links = page.locator(`${nav} a`);
            // Errors have no active route; detail pages keep Jelajah selected.
            const link = await links.evaluateAll(nodes => nodes.findIndex(n => !n.hasAttribute('aria-current')));
            const target = links.nth(link);
            await target.hover(); await expect.poll(() => scale(target)).toBe(1);
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
            const active = page.locator(`${nav} [aria-current=page]`);
            if (await active.count()) expect(await scale(active)).toBe(1);
            await page.locator('.site-header').screenshot({ path: `test-results/navigation-${name}-${width}.png` });
        }
    }
    await page.setViewportSize({ width: 375, height: 812 }); await page.goto('/explore');
    await page.evaluate(() => document.documentElement.style.fontSize = '200%');
    await page.locator(nav).getByRole('link', { name: 'Beranda', exact: true }).hover();
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
    await page.locator('.site-header').screenshot({ path: 'test-results/navigation-text200-375.png' });
});

test('reduced motion is immediate at load and when the preference changes during hover', async ({ page }) => {
    for (const route of ['/', '/missing-error-page-preview']) {
        await page.emulateMedia({ reducedMotion: 'reduce' }); await page.goto(route);
        const link = page.locator(nav).getByRole('link', { name: 'Jelajah', exact: true });
        await link.hover(); expect(await timing(link)).toBe(0); expect(await scale(link)).toBe(1);
        await page.locator('.site-header').screenshot({ path: `test-results/navigation-reduced-${route === '/' ? 'public' : 'error'}.png` });
        await page.mouse.move(0, 0); expect(await scale(link)).toBe(0);
        await page.emulateMedia({ reducedMotion: 'no-preference' }); await link.hover();
        await frame(link, 80); await page.emulateMedia({ reducedMotion: 'reduce' });
        expect(await scale(link)).toBe(1); expect(await timing(link)).toBe(0);
    }
});

test('touch and JavaScript-free navigation use ordinary links without a hover dependency', async ({ browser, baseURL }) => {
    const context = await browser.newContext({ baseURL, viewport: { width: 375, height: 812 }, isMobile: true, hasTouch: true, javaScriptEnabled: false });
    const page = await context.newPage(); await page.goto('/');
    expect(await page.evaluate(() => matchMedia('(hover: hover)').matches)).toBe(false);
    expect(await scale(page.locator(`${nav} [aria-current=page]`))).toBe(1);
    await page.locator(nav).getByRole('link', { name: 'Jelajah', exact: true }).tap();
    await expect(page).toHaveURL(/\/explore$/);
    expect(await scale(page.locator(`${nav} [aria-current=page]`))).toBe(1);
    await context.close();
});
