import { test, expect } from '@playwright/test';

const photo = 'http://127.0.0.1:8015';
const empty = 'http://127.0.0.1:8016';
const routes = { Home: '/', Explore: '/explore', Visit: '/visit/demo-bara-di-ujung-hari', Place: '/place/demo-bara-senja', Error: '/visual-missing-page' };
test.beforeAll(async ({ browser }) => expect(browser.version()).toBe('156.0.8078.4'));

async function ready(page, route, origin = photo) {
    const response = await page.goto(origin + route);
    expect(response.headers()['x-foodie-visual-fixture']).toBe(origin === photo ? 'photo-v1' : 'empty-v1');
    expect(response.status()).toBe(route === routes.Error ? 404 : 200);
    if (route !== routes.Error) await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.evaluate(() => document.fonts.ready);
    await expect(page.locator('body')).not.toContainText('VISUAL-PRIVATE');
    await expect(page.locator('body')).not.toContainText('VISUAL-DRAFT');
    await expect(page.locator('body')).not.toContainText('Draft: catatan sore yang belum selesai');
    await expect(page.locator('body')).not.toContainText('Privat: makan malam kecil bersama teman');
    await images(page);
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
}

async function images(page) {
    await page.locator('img[src]').evaluateAll(nodes => Promise.all(nodes.map(n => { n.loading = 'eager'; return n.decode(); })));
}

async function shot(page, info, name, target = page) {
    await page.mouse.move(0, 0);
    await info.attach('visual-case', { contentType: 'application/json', body: JSON.stringify({ name, route: page.url(),
        viewport: page.viewportSize(), browser: '156.0.8078.4', motion: 'reduce unless state specifies normal',
        fonts: await page.evaluate(() => [...document.fonts].filter(f => f.status === 'loaded').map(f => f.family)),
        originals: await page.locator('img[src$="/original"]').count() }) });
    await expect(target).toHaveScreenshot(`${name}.png`);
}

async function on(page) {
    await page.locator('[data-vending-lamp]').click();
    await page.locator('[data-lamp-socket]').click();
    await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'on');
}

for (const width of [390, 1440, 1920]) {
    for (const [name, route] of Object.entries(routes)) {
        test(`${name} at ${width}: hero and footer`, async ({ page }, info) => {
            await page.setViewportSize({ width, height: 1000 });
            const originals = [];
            page.on('request', r => { if (r.url().endsWith('/original')) originals.push(r.url()); });
            await ready(page, route);
            if (name === 'Home') {
                await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'off');
                await expect(page.locator('[data-vending-print]')).toBeDisabled();
                const geometry = await page.locator('[data-machine]').evaluate(n => {
                    const b = n.getBoundingClientRect(); return { width: b.width, ratio: b.width / b.height };
                });
                if (width >= 960) { expect(geometry.width).toBeLessThanOrEqual(1634); expect(geometry.ratio).toBeCloseTo(1634 / 963, 2); }
            }
            const shell = await page.locator('.site-header').boundingBox();
            expect(shell.width).toBeCloseTo(width === 390 ? 358 : width === 1440 ? 1344 : 1792, 0);
            if (process.env.FOODIE_VISUAL_SHIFT_PROBE === '1' && name === 'Home' && width === 1440) {
                await page.locator('.site-header').evaluate(n => n.style.transform = 'translateY(8px)');
            }
            await shot(page, info, `${name.toLowerCase()}-${width}-hero`);
            await shot(page, info, `${name.toLowerCase()}-${width}-footer`, page.locator('.site-footer'));
            expect(originals).toEqual([]);
        });
    }
}

test('cabinet on and printed identity', async ({ page }, info) => {
    await ready(page, '/'); await on(page); await images(page);
    await shot(page, info, 'cabinet-on-1440', page.locator('[data-discovery-scene]'));
    const slot = page.locator('[data-memory-slot]').nth(1);
    const id = await slot.getAttribute('data-memory-slot'), href = await slot.getAttribute('href');
    await slot.click(); await page.locator('[data-vending-print]').click();
    await expect(page.locator('[data-machine]')).toHaveAttribute('data-dispenser', 'ready');
    await expect(page.locator('[data-paper-path] a')).toHaveAttribute('data-printed-visit', id);
    await expect(page.locator('[data-paper-path] a')).toHaveAttribute('href', href);
    await images(page);
    await shot(page, info, 'cabinet-printed-1440', page.locator('[data-discovery-scene]'));
});

for (const route of ['/', routes.Error]) {
    test(`navigation midpoint with normal motion on ${route}`, async ({ page }, info) => {
        await page.emulateMedia({ reducedMotion: 'no-preference' });
        await ready(page, route);
        const link = page.locator('.site-header nav').getByRole('link', { name: 'Jelajah', exact: true });
        await link.hover();
        const scale = await link.evaluate(n => {
            const animation = n.getAnimations({ subtree: true }).find(a => a.transitionProperty === 'transform');
            if (!animation) throw new Error('Missing real underline transition');
            animation.pause(); animation.currentTime = 110;
            return new DOMMatrixReadOnly(getComputedStyle(n, '::after').transform).a;
        });
        expect(scale).toBeCloseTo(.5);
        // Keep the pointer and paused transition in place; screenshot auto-finishing is disabled.
        await info.attach('visual-case', { contentType: 'application/json', body: JSON.stringify({ name: route === '/' ? 'nav-public-midpoint-1440' : 'nav-error-midpoint-1440', route: page.url(), viewport: page.viewportSize(), state: 'normal motion, actual transform paused at 110/220ms' }) });
        await expect(page.locator('.site-header')).toHaveScreenshot(`${route === '/' ? 'nav-public' : 'nav-error'}-midpoint-1440.png`, { maxDiffPixels: 0 });
        expect(await link.evaluate(n => new DOMMatrixReadOnly(getComputedStyle(n, '::after').transform).a)).toBeCloseTo(.5);
    });
}

for (const width of [390, 1440]) {
    test(`staged filter at ${width}`, async ({ page }, info) => {
        await page.setViewportSize({ width, height: 1000 }); await ready(page, '/explore');
        await page.getByRole('button', { name: /^Filter/ }).click();
        const dialog = page.getByRole('dialog', { name: 'Filter arsip', exact: true });
        await expect(dialog).toBeVisible();
        await dialog.locator('[data-filter-group-open=tag]').click();
        const counted = page.waitForResponse(r => r.url().includes('/explore/count?') && r.status() === 200);
        await dialog.locator('input[type=checkbox]:visible').first().check();
        expect((await (await counted).json()).total).toBe(7);
        await expect(dialog.locator('[data-filter-apply]')).toHaveText('Tampilkan 7 hasil');
        await expect(dialog.locator('[data-filter-form]')).not.toHaveAttribute('aria-busy', 'true');
        await expect(page).toHaveURL(`${photo}/explore`);
        await shot(page, info, `filter-staged-${width}`, dialog);
        await dialog.getByRole('button', { name: 'Batal', exact: true }).click();
        await expect(page.getByRole('button', { name: /^Filter/ })).toBeFocused();
    });
}

test('unpublished archive and filtered empty recovery', async ({ page }, info) => {
    await page.setViewportSize({ width: 390, height: 1000 });
    await ready(page, '/explore', empty);
    await expect(page.locator('.empty-state')).toContainText('Arsip belum dipublikasikan');
    await expect(page.locator('.empty-state a')).toHaveCount(0);
    await shot(page, info, 'archive-unpublished-390', page.locator('.memory-reel'));
    await ready(page, '/explore?q=tidak-ada&view=list');
    await expect(page.locator('.empty-state')).toContainText('Tidak ada catatan yang cocok');
    await expect(page.locator('#discovery-search')).toHaveValue('tidak-ada');
    await shot(page, info, 'filtered-empty-390', page.locator('[data-discovery-console]').locator('..'));
});

test('failed browse retains committed results and retry', async ({ page }, info) => {
    await ready(page, '/explore');
    const title = await page.locator('.visit-card__title').first().textContent();
    await page.route('**/explore?**', route => route.request().headers()['x-explore-fragment'] === '1' ? route.fulfill({ status: 500, body: '' }) : route.continue());
    await page.locator('#discovery-search').fill('tidak-ada'); await page.locator('#discovery-search').press('Enter');
    await expect(page.locator('[data-discovery-error]')).toBeVisible();
    await expect(page.locator('[data-discovery-results]')).toHaveAttribute('aria-busy', 'false');
    await expect(page.locator('.visit-card__title').first()).toHaveText(title);
    await expect(page).toHaveURL(`${photo}/explore`);
    await shot(page, info, 'browse-failure-1440', page.locator('[data-discovery-error]'));
});

test('viewer original loads on demand and focus returns', async ({ page }, info) => {
    await page.setViewportSize({ width: 390, height: 1000 });
    const originals = []; page.on('request', r => { if (r.url().endsWith('/original')) originals.push(r.url()); });
    await ready(page, routes.Visit); expect(originals).toEqual([]);
    const trigger = page.locator('[data-view-photo]').first(); await trigger.click();
    const dialog = page.getByRole('dialog', { name: 'Foto kunjungan', exact: true });
    await expect(dialog.locator('[data-viewer-image]')).toBeVisible(); await images(page);
    expect(originals).toHaveLength(1);
    await shot(page, info, 'viewer-original-390', dialog);
    await page.keyboard.press('Escape'); await expect(trigger).toBeFocused();
});
