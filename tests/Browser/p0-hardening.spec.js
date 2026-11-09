import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import fs from 'node:fs';

const qa = 'http://127.0.0.1:8017';
const output = 'docs/qa/p0-hardening';
const widths = [320, 375, 390, 768, 959, 960, 1023, 1024, 1440, 1920, 2560, 3440];
async function ready(page, path) {
    const response = await page.goto(qa + path);
    expect(response.headers()['x-foodie-hardening-fixture']).toBe('v1');
    if (response.status() === 200) await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.evaluate(() => document.fonts.ready);
    return response;
}
async function noOverflow(page) {
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
}

test('Place hero comes from older published history and stays the same on pages 1 and 2', async ({ page }) => {
    const originals = []; page.on('request', r => { if (r.url().endsWith('/original')) originals.push(r.url()); });
    let source;
    for (const number of [1, 2]) {
        await ready(page, `/place/qa-stable-cover?page=${number}`);
        const hero = page.locator('.place-dossier__photo img');
        await expect(hero).toBeVisible(); await expect(hero).toHaveJSProperty('complete', true);
        expect(await hero.evaluate(n => n.naturalWidth)).toBeGreaterThan(0);
        if (source) await expect(hero).toHaveAttribute('src', source);
        source = await hero.getAttribute('src');
        await expect(page.locator('body')).not.toContainText(/P0H-(PRIVATE|DRAFT)/);
        if (number === 1) await expect(page.locator('.results')).not.toContainText('Foto lama QA di halaman kedua');
        else await expect(page.locator('.results')).toContainText('Foto lama QA di halaman kedua');
        await page.screenshot({ path: `${output}/stable-hero-page-${number}.jpg`, quality: 75 });
    }
    expect(originals).toEqual([]);
    await ready(page, '/place/qa-stable-cover?page=99');
    await expect(page.locator('.empty-state')).toContainText('Halaman ini sudah kosong');
    await page.getByRole('link', { name: 'Ke halaman pertama', exact: true }).click();
    await expect(page.locator('.visit-card')).toHaveCount(18);
});

test('photo-free Place has genuine facts and no unused photo track at every width and enlarged text', async ({ page }) => {
    test.setTimeout(180000);
    await page.emulateMedia({ reducedMotion: 'reduce' });
    const geometry = [];
    for (const width of widths) {
        await page.setViewportSize({ width, height: 1000 });
        await ready(page, '/place/qa-text-place');
        await expect(page.locator('.place-dossier')).toHaveClass(/place-dossier--text/);
        await expect(page.locator('.place-dossier')).toContainText('0 kunjungan dinilai');
        await expect(page.locator('.place-dossier')).toContainText('Belum Dinilai');
        await expect(page.locator('.place-dossier img, .place-dossier__photo, .place-dossier a[href*="maps"]')).toHaveCount(0);
        for (const scale of [100, 200]) {
            await page.evaluate(scale => document.documentElement.style.fontSize = `${scale}%`, scale);
            await noOverflow(page);
            const facts = await page.locator('.place-dossier__facts').boundingBox();
            const title = await page.locator('h1').boundingBox();
            expect(title.width).toBeGreaterThan(0); expect(facts.x).toBeGreaterThanOrEqual(0); expect(facts.x + facts.width).toBeLessThanOrEqual(width);
            geometry.push({ width, scale, title, facts });
            if ([390, 1440, 2560].includes(width)) await page.screenshot({ path: `${output}/text-place-${width}-${scale}.jpg`, quality: 75 });
        }
    }
    fs.writeFileSync(`${output}/text-place-geometry.json`, JSON.stringify(geometry, null, 2) + '\n');
    await page.setViewportSize({ width: 812, height: 375 }); await ready(page, '/place/qa-text-place'); await noOverflow(page);
    await page.keyboard.press('Tab'); await expect(page.getByRole('link', { name: 'Lewati ke isi', exact: true })).toBeFocused();
    await page.keyboard.press('Enter'); await expect(page.locator('main')).toBeFocused();
    const axe = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
    expect(axe.violations.map(v => ({ id: v.id, nodes: v.nodes.map(n => n.target) }))).toEqual([]);
});

test('minimal admin uses system fonts, measured controls, persistent errors and one saved announcement', async ({ page }) => {
    const fixture = JSON.parse(fs.readFileSync('tools/.runtime/browser-credentials.json'));
    await page.goto('/admin/login');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    expect(await page.locator('body').evaluate(n => getComputedStyle(n).fontFamily)).toContain('system-ui');
    expect(await page.locator('body').evaluate(n => getComputedStyle(n).backgroundImage)).toBe('none');
    await page.getByRole('textbox', { name: 'Email', exact: true }).fill(fixture.email);
    await page.getByRole('textbox', { name: 'Kata sandi', exact: true }).fill(fixture.password);
    await page.getByRole('button', { name: 'Masuk', exact: true }).click(); await expect(page).toHaveURL(/\/admin$/);
    const measured = [];
    for (const width of [320, 390, 768, 1440, 1920, 2560]) {
        await page.setViewportSize({ width, height: 1000 });
        for (const route of ['/admin/visit', '/admin/place', '/admin/visit/create']) {
            await page.goto(route); await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
            expect(await page.locator('h1').evaluate(n => getComputedStyle(n).fontFamily)).toContain('system-ui');
            await noOverflow(page);
            for (const button of await page.locator('main .btn:visible').all()) {
                const metrics = await button.evaluate(n => { const r = n.getBoundingClientRect(), s = getComputedStyle(n); return { text: n.textContent.trim(), width: r.width, height: r.height, paddingX: parseFloat(s.paddingLeft), paddingY: parseFloat(s.paddingTop) }; });
                expect(metrics.width).toBeGreaterThanOrEqual(44); expect(metrics.height).toBeGreaterThanOrEqual(44);
                expect(metrics.paddingX).toBeGreaterThanOrEqual(12); expect(metrics.paddingY).toBeGreaterThanOrEqual(8);
                measured.push({ viewport: width, route, ...metrics });
            }
            if ([390, 1440].includes(width)) await page.screenshot({ path: `${output}/admin-${route.split('/').at(-1)}-${width}.jpg`, quality: 75 });
            await page.evaluate(() => document.documentElement.style.fontSize = '200%'); await noOverflow(page);
        }
    }
    fs.writeFileSync(`${output}/control-metrics.json`, JSON.stringify(measured, null, 2) + '\n');
    await page.goto('/admin/place/create');
    await page.getByRole('textbox', { name: 'Nama tempat', exact: true }).fill(' ');
    await page.getByRole('button', { name: 'Simpan tempat', exact: true }).click();
    await expect(page.locator('[data-error-summary]')).toBeFocused();
    await expect(page.locator('[aria-invalid=true]')).toHaveCount(1);
    await page.getByRole('textbox', { name: 'Nama tempat', exact: true }).fill(`Tempat P0H QA ${Date.now()}`);
    await page.getByRole('button', { name: 'Simpan tempat', exact: true }).click();
    await expect(page.locator('.alert--success')).toBeVisible();
    await expect(page.locator('[data-toast-container] .toast')).toHaveCount(0);
    const axe = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
    expect(axe.violations.map(v => v.id)).toEqual([]);
});

test('six real HTTP error responses preserve approved scene, status and standalone shared CSS', async ({ page }) => {
    const responses = [];
    for (const width of [390, 1440, 2560]) {
        await page.setViewportSize({ width, height: 1000 });
        for (const status of [403, 404, 419, 429, 500, 503]) {
            const response = await ready(page, `/p0h-error/${status}`);
            expect(response.status()).toBe(status); await expect(page.locator('.error-code-text')).toHaveText(`${status}`);
            await expect(page.locator('h1')).toBeVisible(); await noOverflow(page);
            responses.push({ width, status, heading: await page.locator('h1').textContent(), actions: await page.locator('.error-actions a').allTextContents() });
            await page.screenshot({ path: `${output}/http-${status}-${width}.jpg`, quality: 65 });
        }
    }
    fs.writeFileSync(`${output}/http-errors.json`, JSON.stringify(responses, null, 2) + '\n');
});

test('real Explore wait keeps committed results and URL, then offers recovery without a skeleton', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/explore'); await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    const committed = page.url(), count = await page.locator('.visit-card').count();
    let release;
    const waiting = new Promise(resolve => release = resolve);
    await page.route('**/explore?**', async route => {
        if (route.request().headers()['x-explore-fragment'] !== '1') return route.continue();
        await waiting; await route.fulfill({ status: 503, body: 'QA unavailable' });
    });
    await page.locator('#discovery-search').fill('p0h-no-match'); await page.locator('#discovery-search').press('Enter');
    await expect(page.locator('[data-discovery-results]')).toHaveAttribute('aria-busy', 'true');
    await expect(page.locator('.visit-card')).toHaveCount(count); await expect(page).toHaveURL(committed);
    await expect(page.locator('.skeleton')).toHaveCount(0);
    await page.screenshot({ path: `${output}/explore-real-wait-390.jpg`, quality: 75 });
    release();
    await expect(page.locator('[data-discovery-error]')).toBeVisible();
    await expect(page.locator('[data-discovery-results]')).toHaveAttribute('aria-busy', 'false');
    await expect(page).toHaveURL(committed); await expect(page.locator('.visit-card')).toHaveCount(count);
    await page.unroute('**/explore?**'); await page.locator('[data-discovery-retry]').click();
    await expect(page).toHaveURL(/q=p0h-no-match/);
    await expect(page.locator('.empty-state')).toContainText('Tidak ada catatan yang cocok');
    await expect(page.locator('[data-discovery-results]')).toHaveAttribute('aria-busy', 'false');
});

test('ultrawide discovery caps photo height and reflows result columns', async ({ page }) => {
    await page.setViewportSize({ width: 2560, height: 1000 });
    await page.goto('http://127.0.0.1:8015/explore');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.evaluate(() => document.fonts.ready);
    for (const tile of await page.locator('.curiosity-surface').all()) {
        const rect = await tile.boundingBox();
        expect(rect.height).toBeLessThanOrEqual(513);
        const caption = await tile.locator('.curiosity-surface__copy').boundingBox();
        expect(caption.y + caption.height).toBeLessThanOrEqual(1000);
    }
    expect(await page.locator('.results').evaluate(n => getComputedStyle(n).gridTemplateColumns.split(' ').length)).toBe(3);
    await page.goto('http://127.0.0.1:8015/explore?tab=food');
    expect(await page.locator('.results').evaluate(n => getComputedStyle(n).gridTemplateColumns.split(' ').length)).toBe(4);
    await noOverflow(page);
    await page.goto('http://127.0.0.1:8015/place/demo-bara-senja');
    expect((await page.locator('.place-dossier__photo').boundingBox()).width).toBeLessThanOrEqual(800);
});
