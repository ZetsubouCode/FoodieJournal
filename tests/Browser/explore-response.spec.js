import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const origin = 'http://127.0.0.1:8015';
async function ready(page, path = '/explore') {
    const response = await page.goto(origin + path);
    expect(response.headers()['x-foodie-visual-fixture']).toBe('photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
}
function gate() { let open; const wait = new Promise(resolve => { open = resolve; }); return { wait, open }; }

test('board pending intent commits its actual facet once, keeps the board and restores keyboard focus', async ({ page }) => {
    await ready(page); await page.emulateMedia({ reducedMotion: 'reduce' });
    const held = gate();
    await page.route('**/explore?**', async route => { await held.wait; await route.continue(); });
    await page.evaluate(() => { window.board = document.querySelector('.curiosity-index'); window.results = document.querySelector('[data-discovery-results]'); window.count = document.querySelector('.memory-reel__head > p').textContent; });
    const tile = page.locator('.curiosity-surface--style'), href = await tile.getAttribute('href');
    await tile.focus(); await page.keyboard.press('Enter');
    await expect(tile).toHaveAttribute('data-pending', 'true');
    await expect(tile).not.toHaveAttribute('data-selected', 'true');
    await expect(page).toHaveURL(origin + '/explore');
    expect(await page.evaluate(() => window.results === document.querySelector('[data-discovery-results]') && window.count === document.querySelector('.memory-reel__head > p').textContent)).toBe(true);
    held.open(); await expect(page).toHaveURL(href);
    await expect(tile).toHaveAttribute('data-selected', 'true'); await expect(tile).not.toHaveAttribute('data-pending');
    await expect(tile).toHaveAttribute('aria-label', /Filter aktif/);
    await expect(page.locator('#memory-reel-title')).toBeFocused();
    expect(await page.evaluate(() => window.board === document.querySelector('.curiosity-index'))).toBe(true);
    await page.goBack(); await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', 'visit');
    await expect(tile).not.toHaveAttribute('data-selected', 'true');
    await page.goForward(); await expect(tile).toHaveAttribute('data-selected', 'true');
});

test('malformed response cannot partially replace committed controls or results, and retry recovers', async ({ page }) => {
    await ready(page);
    const before = await page.locator('[data-discovery-results]').innerHTML();
    await page.evaluate(() => { window.consoleBefore = document.querySelector('[data-discovery-console]'); });
    let invalid = true;
    await page.route('**/explore?**', route => invalid ? route.fulfill({ status: 200, contentType: 'text/html', body: '<div data-discovery data-mode="food"><div data-discovery-console>Partial response</div></div>' }) : route.continue());
    await page.getByRole('link', { name: 'Hidangan', exact: true }).click();
    await expect(page.locator('[data-discovery-error]')).toBeVisible();
    await expect(page).toHaveURL(origin + '/explore');
    expect(await page.locator('[data-discovery-results]').innerHTML()).toBe(before);
    expect(await page.evaluate(() => window.consoleBefore === document.querySelector('[data-discovery-console]'))).toBe(true);
    invalid = false; await page.locator('[data-discovery-retry]').click();
    await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', 'food');
    await expect(page.locator('[data-discovery-error]')).toBeHidden();
});

test('offline failure and malformed state keep committed facets until a successful retry', async ({ page }) => {
    await ready(page); const tile = page.locator('.curiosity-surface--style');
    await page.context().setOffline(true); await tile.click();
    await expect(page.locator('[data-discovery-error]')).toBeVisible();
    await expect(tile).not.toHaveAttribute('data-selected', 'true'); await expect(tile).not.toHaveAttribute('data-pending');
    await page.context().setOffline(false);
    let invalid = true;
    await page.route('**/explore?**', route => invalid ? route.fulfill({ status: 200, contentType: 'text/html', body: '<div data-discovery data-mode="food" data-browse-state="null"><div data-discovery-console>Wrong controls</div><div data-discovery-results><header class="memory-reel__head"><p>0</p></header></div><div data-filter-drawer><form data-filter-form></form></div></div>' }) : route.continue());
    await page.locator('[data-discovery-retry]').click();
    await expect(page.locator('[data-discovery-error]')).toBeVisible();
    await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', 'visit');
    await expect(page.locator('#discovery-search')).toBeVisible();
    invalid = false; await page.locator('[data-discovery-retry]').click();
    await expect(tile).toHaveAttribute('data-selected', 'true');
});

test('a failed Back rolls back, and retry restores the requested history URL with its actual data', async ({ page }) => {
    await ready(page); await page.locator('.curiosity-surface--style').click();
    await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', 'food');
    const committed = page.url(); let fail = true;
    await page.route('**/explore', route => route.request().headers()['x-explore-fragment'] === '1' && fail ? route.fulfill({ status: 503, body: '' }) : route.continue());
    await page.goBack(); await expect(page.locator('[data-discovery-error]')).toBeVisible();
    await expect(page).toHaveURL(committed); await expect(page.locator('.curiosity-surface--style')).toHaveAttribute('data-selected', 'true');
    fail = false; await page.locator('[data-discovery-retry]').click();
    await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', 'visit');
    await expect(page).toHaveURL(origin + '/explore');
    await expect(page.locator('.curiosity-surface--style')).not.toHaveAttribute('data-selected', 'true');
});

test('rapid board intents discard older responses and markers; committed controls keep logical focus', async ({ page }) => {
    await ready(page); const held = gate();
    await page.route('**/explore?**', async route => { if (route.request().url().includes('style')) await held.wait; await route.continue().catch(() => {}); });
    await page.locator('.curiosity-surface--style').click();
    const city = page.locator('.curiosity-surface--city'), target = await city.getAttribute('href'); await city.click();
    await expect(page).toHaveURL(target); held.open();
    await expect(page.locator('.curiosity-surface--style')).not.toHaveAttribute('data-pending');
    await expect(city).toHaveAttribute('data-selected', 'true');
    await page.locator('#discovery-sort').focus(); await page.locator('#discovery-sort').selectOption('oldest');
    await expect(page.locator('#discovery-sort')).toHaveValue('oldest'); await expect(page.locator('#discovery-sort')).toBeFocused();
    await page.getByRole('link', { name: 'Daftar', exact: true }).focus(); await page.keyboard.press('Enter');
    await expect(page.locator('.results')).toHaveClass(/results--list/);
    await expect(page.getByRole('link', { name: 'Daftar', exact: true })).toBeFocused();
    await page.locator('.discovery-chips .chip').first().focus(); await page.keyboard.press('Enter');
    await expect(page.locator('.discovery-chips .chip')).toHaveCount(0);
    await expect(page.locator('#memory-reel-title')).toBeFocused();
});

test('actual delayed derivatives get static box-matched placeholders; success and failure remove them', async ({ page }) => {
    const held = gate(); let fail = false; const originals = [];
    page.on('request', request => { if (request.url().endsWith('/original')) originals.push(request.url()); });
    await page.route('**/media/**', async route => { await held.wait; if (fail) await route.abort(); else await route.continue(); });
    const response = await page.goto(origin + '/explore', { waitUntil: 'domcontentloaded' });
    expect(response.headers()['x-foodie-visual-fixture']).toBe('photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true'); await page.evaluate(() => document.fonts.ready);
    const image = page.locator('.curiosity-surface--food img'), box = await image.boundingBox();
    await expect(image).toHaveAttribute('data-photo-waiting', 'true');
    expect(await image.boundingBox()).toEqual(box);
    expect(await image.evaluate(n => n.getAnimations().length)).toBe(0);
    await page.emulateMedia({ reducedMotion: 'reduce' });
    const scan = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
    expect(scan.violations.map(v => v.id)).toEqual([]);
    held.open(); await expect(image).not.toHaveAttribute('data-photo-waiting');
    await expect(image).toHaveJSProperty('complete', true); expect(await image.boundingBox()).toEqual(box);
    await page.unroute('**/media/**'); fail = true;
    await page.route('**/media/**', route => route.abort()); await ready(page);
    await expect(page.locator('.curiosity-surface--food .photo-fallback')).toContainText('Foto belum dapat dimuat');
    await expect(page.locator('[data-photo-waiting]')).toHaveCount(0); expect(originals).toEqual([]);
});

test('decoded fast photos never flash a placeholder and runtime reduction finishes visible result entrances', async ({ page }) => {
    await page.addInitScript(() => { window.photoWaits = []; new MutationObserver(records => records.forEach(r => { if (r.attributeName === 'data-photo-waiting') window.photoWaits.push(r.target.currentSrc); })).observe(document, { subtree: true, attributes: true }); });
    await ready(page); await page.evaluate(async () => { await Promise.all([...document.querySelectorAll('.curiosity-surface img')].map(n => { n.loading = 'eager'; return n.decode(); })); });
    await page.reload(); await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await expect(page.locator('[data-photo-waiting]')).toHaveCount(0);
    expect(await page.evaluate(() => window.photoWaits)).toEqual([]);
    await page.evaluate(() => {
        window.entrances = []; const animate = Element.prototype.animate;
        Element.prototype.animate = function (frames, timing) {
            const animation = animate.call(this, frames, timing);
            if (this.matches('.results > article')) { animation.pause(); window.entrances.push(animation); }
            return animation;
        };
    });
    await page.getByRole('link', { name: 'Hidangan', exact: true }).click(); await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', 'food');
    const effects = await page.evaluate(() => window.entrances.map(a => ({ duration: a.effect.getTiming().duration, rect: a.effect.target.getBoundingClientRect().toJSON() })));
    expect(effects.length).toBeGreaterThan(0); expect(effects.length).toBeLessThanOrEqual(4);
    expect(effects.every(e => e.duration === 220 && e.rect.top < 1000 && e.rect.bottom > 0)).toBe(true);
    await page.emulateMedia({ reducedMotion: 'reduce' });
    expect(await page.locator('[data-discovery-results]').evaluate(n => n.getAnimations({ subtree: true }).filter(a => a.playState === 'running').length)).toBe(0);
});

test('real outstanding request timeout keeps committed data and clears pending intent', async ({ page }) => {
    test.setTimeout(30000); await ready(page); const held = gate();
    await page.route('**/explore?**', async route => { await held.wait; await route.abort().catch(() => {}); });
    await page.locator('.curiosity-surface--style').click();
    await expect(page.locator('[data-discovery-error]')).toBeVisible({ timeout: 18000 });
    await expect(page.locator('[data-discovery-results]')).toHaveAttribute('aria-busy', 'false');
    await expect(page.locator('.curiosity-surface--style')).not.toHaveAttribute('data-pending');
    await expect(page).toHaveURL(origin + '/explore'); held.open();
});

test('a response committed in a hidden tab settles without starting a result entrance', async ({ page }) => {
    await ready(page); const held = gate();
    await page.route('**/explore?**', async route => { await held.wait; await route.continue(); });
    await page.getByRole('link', { name: 'Hidangan', exact: true }).click();
    await page.evaluate(() => {
        window.hiddenEntrances = 0; const animate = Element.prototype.animate;
        Element.prototype.animate = function (frames, options) { if (this.matches('.results > article')) window.hiddenEntrances++; return animate.call(this, frames, options); };
        Object.defineProperty(document, 'hidden', { value: true, configurable: true }); document.dispatchEvent(new Event('visibilitychange'));
    });
    held.open(); await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', 'food');
    expect(await page.evaluate(() => window.hiddenEntrances)).toBe(0);
    await expect(page.locator('[data-discovery-results]')).toHaveAttribute('aria-busy', 'false');
    await page.evaluate(() => { delete document.hidden; document.dispatchEvent(new Event('visibilitychange')); });
});

test('responsive board keeps semantic order, touch links, no-JS and enlarged-text access', async ({ browser }) => {
    test.setTimeout(90000);
    const context = await browser.newContext({ viewport: { width: 375, height: 1000 }, hasTouch: true, reducedMotion: 'reduce' });
    const page = await context.newPage();
    for (const width of [320, 375, 768, 1440, 1920]) {
        await page.setViewportSize({ width, height: 1000 }); await ready(page);
        await page.evaluate(() => { document.documentElement.style.fontSize = '200%'; });
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        expect(await page.locator('.curiosity-index__photos > a').evaluateAll(nodes => nodes.map(n => n.dataset.curiosityKey))).toEqual(['category', 'city', 'style']);
    }
    await page.setViewportSize({ width: 375, height: 1000 }); await ready(page);
    await page.locator('.curiosity-surface--style').tap(); await expect(page.locator('.curiosity-surface--style')).toHaveAttribute('data-selected', 'true');
    await context.close();
    const plain = await browser.newContext({ javaScriptEnabled: false }); const nojs = await plain.newPage();
    await nojs.goto(origin + '/explore'); const link = nojs.locator('.curiosity-surface--style'), href = await link.getAttribute('href');
    await link.click(); await expect(nojs).toHaveURL(href); await expect(nojs.locator('.curiosity-surface--style')).toHaveAttribute('data-selected', 'true');
    await plain.close();
});
