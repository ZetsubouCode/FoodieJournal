import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import fs from 'node:fs';

const output = 'docs/qa/p4-integrated';
const origin = 'http://127.0.0.1:8015';
const routes = ['/', '/explore', '/visit/demo-bara-di-ujung-hari', '/place/demo-bara-senja'];
async function ready(page, route) {
    const response = await page.goto(origin + route);
    expect(response.headers()['x-foodie-visual-fixture']).toBe('photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.evaluate(() => document.fonts.ready);
}
async function scan(page, name, report, file = 'accessibility.json') {
    const result = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
    const simplify = nodes => nodes.map(v => ({ id: v.id, impact: v.impact, nodes: v.nodes.map(n => ({ target: n.target, summary: n.failureSummary })) }));
    report.push({ name, violations: simplify(result.violations), incomplete: simplify(result.incomplete), passes: result.passes.length });
    fs.writeFileSync(`${output}/${file}`, JSON.stringify(report, null, 2));
    expect(simplify(result.violations), name).toEqual([]);
    expect(simplify(result.incomplete.filter(v => v.id !== 'color-contrast')), `${name}: manual semantic review`).toEqual([]);
}
async function trap(page, dialog) {
    for (const key of ['Tab', 'Shift+Tab']) for (let n = 0; n < 12; n++) {
        await page.keyboard.press(key);
        expect(await dialog.evaluate(d => d.contains(document.activeElement))).toBe(true);
        expect(await page.evaluate(() => { const r = document.activeElement.getBoundingClientRect(); return r.width > 0 && r.height > 0 && r.bottom > 0 && r.top < innerHeight; })).toBe(true);
    }
}

test('integrated public reflow, enlarged root text and primary focus at all target boundaries', async ({ page }) => {
    test.setTimeout(240000);
    const measurements = [];
    await page.emulateMedia({ reducedMotion: 'reduce' });
    for (const width of [320, 375, 390, 768, 959, 960, 1023, 1024, 1440, 1920, 2560]) {
        await page.setViewportSize({ width, height: 1000 });
        for (const route of routes) {
            await ready(page, route);
            for (const scale of [100, 200]) {
                await page.evaluate(scale => document.documentElement.style.fontSize = `${scale}%`, scale);
                expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `${route}/${width}/${scale}`).toBe(true);
                const controls = await page.locator('.site-footer__nav a').evaluateAll(nodes => nodes.map(n => { const r = n.getBoundingClientRect(); return { width: r.width, height: r.height, x: r.x }; }));
                expect(controls.every(r => r.width >= 44 && r.height >= 44 && r.x >= 0 && r.x + r.width <= width + 1)).toBe(true);
                measurements.push({ width, route, scale, controls });
            }
            if ([375, 1440].includes(width)) await page.screenshot({ path: `${output}/text-${width}-${routes.indexOf(route)}.jpg`, type: 'jpeg', quality: 75, fullPage: true });
        }
    }
    await page.setViewportSize({ width: 812, height: 375 });
    for (const route of routes) {
        await ready(page, route); await page.keyboard.press('Tab');
        await expect(page.getByRole('link', { name: 'Lewati ke isi', exact: true })).toBeFocused();
        await page.keyboard.press('Enter'); await expect(page.locator('main')).toBeFocused();
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
    }
    fs.writeFileSync(`${output}/reflow.json`, JSON.stringify(measurements, null, 2));
});

test('whole-page axe and actual keyboard focus cover drawer, busy error retry and viewer', async ({ page }) => {
    test.setTimeout(180000);
    const report = [], originals = [];
    page.on('request', r => { if (r.url().endsWith('/original')) originals.push(r.url()); });
    for (const width of [390, 1440]) {
        await page.setViewportSize({ width, height: 1000 });
        for (const route of routes) {
            await ready(page, route);
            if (route === '/') await expect(page.getByRole('group', { name: 'Memory Archive', exact: true })).toBeVisible();
            await scan(page, `${width} ${route}`, report);
        }
    }
    expect(originals).toEqual([]);
    await ready(page, '/explore');
    const filter = page.getByRole('button', { name: /^Filter/ });
    await filter.focus(); await page.keyboard.press('Enter');
    const drawer = page.locator('[data-filter-drawer]'); await expect(drawer).toBeVisible();
    await trap(page, drawer); await scan(page, 'drawer', report);
    await page.screenshot({ path: `${output}/drawer-focus.jpg`, type: 'jpeg', quality: 80 });
    await page.keyboard.press('Escape'); await expect(filter).toBeFocused();
    let release; const wait = new Promise(resolve => release = resolve);
    await page.route('**/explore?**', async route => { await wait; await route.fulfill({ status: 503, body: '' }); });
    await page.locator('#discovery-search').fill('p4-zero-result'); await page.locator('#discovery-search').press('Enter');
    await expect(page.locator('[data-discovery-results]')).toHaveAttribute('aria-busy', 'true');
    await expect(page.locator('[data-discovery-results]')).toHaveCSS('opacity', '1');
    await scan(page, 'busy committed results', report);
    await page.screenshot({ path: `${output}/busy.jpg`, type: 'jpeg', quality: 80 }); release();
    await expect(page.locator('[data-discovery-error]')).toBeVisible();
    await scan(page, 'fragment failure', report);
    await page.unroute('**/explore?**');
    await page.locator('[data-discovery-retry]').focus(); await page.keyboard.press('Enter');
    await expect(page.locator('#memory-reel-title')).toBeFocused();
    await expect(page.locator('.empty-state')).toContainText('Tidak ada catatan yang cocok');
    await scan(page, 'filtered empty recovered', report);
    await ready(page, routes[2]);
    const trigger = page.locator('[data-view-photo]').first();
    await trigger.focus(); await page.keyboard.press('Enter');
    const dialog = page.locator('[data-photo-viewer]'); await expect(dialog).toBeVisible();
    await expect(page.locator('[data-viewer-stage]')).toHaveAttribute('aria-busy', 'false');
    await trap(page, dialog); await scan(page, 'viewer', report);
    await page.screenshot({ path: `${output}/viewer-focus.jpg`, type: 'jpeg', quality: 80 });
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.keyboard.press('Escape'); await expect(trigger).toBeFocused();
    expect(originals.length).toBeGreaterThan(0);
});

test('six actual HTTP statuses retain approved copy, recovery, JSON and responsive standalone scenes', async ({ page }) => {
    test.setTimeout(120000);
    const headings = ['Halaman ini tidak bisa diakses', 'Halaman yang dicari tidak ditemukan', 'Sesi telah berakhir', 'Terlalu banyak permintaan', 'Jurnal sedang mengalami gangguan', 'Foodie sementara tidak tersedia'];
    const statuses = [403, 404, 419, 429, 500, 503], report = [], scans = [];
    for (const width of [390, 1440]) for (const [i, status] of statuses.entries()) {
        await page.setViewportSize({ width, height: 1000 });
        const url = `http://127.0.0.1:8017/p0h-error/${status}`, response = await page.goto(url);
        expect(response.status()).toBe(status); expect(response.headers()['x-foodie-hardening-fixture']).toBe('v1');
        await expect(page.locator('h1')).toHaveText(headings[i]);
        await page.evaluate(() => document.fonts.ready);
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        const actions = await page.locator('.error-actions a').allTextContents(); expect(actions.length).toBeGreaterThanOrEqual(2);
        const json = await page.request.get(url, { headers: { Accept: 'application/json' } });
        expect(json.status()).toBe(status); expect(json.headers()['content-type']).toContain('application/json');
        expect(await json.text()).not.toContain('error-scene');
        await scan(page, `${status}/${width}`, scans, 'http-accessibility.json');
        await page.screenshot({ path: `${output}/http-${status}-${width}.jpg`, type: 'jpeg', quality: 75 });
        await page.keyboard.press('Tab');
        await expect(page.getByRole('link', { name: 'Lewati ke isi', exact: true })).toBeFocused();
        await page.keyboard.press('Enter'); await expect(page.locator('main')).toBeFocused();
        report.push({ status, width, heading: headings[i], actions, jsonStatus: json.status() });
        await page.evaluate(() => document.documentElement.style.fontSize = '200%');
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
    }
    fs.writeFileSync(`${output}/http-statuses.json`, JSON.stringify({ responses: report, scans }, null, 2));
});

test('text spacing preserves readable content and keyboard navigation', async ({ page }) => {
    test.setTimeout(120000);
    await page.emulateMedia({ reducedMotion: 'reduce' });
    for (const width of [375, 1440]) for (const [i, route] of [...routes, '/p0h-error/503'].entries()) {
        await page.setViewportSize({ width, height: 1000 });
        if (route.startsWith('/p0h-')) { await page.goto('http://127.0.0.1:8017' + route); await page.evaluate(() => document.fonts.ready); }
        else await ready(page, route);
        await page.evaluate(() => document.querySelectorAll('body *').forEach(n => {
            for (const [property, value] of Object.entries({ 'line-height': '1.5', 'letter-spacing': '.12em', 'word-spacing': '.16em' })) n.style.setProperty(property, value, 'important');
            if (n.tagName === 'P') n.style.setProperty('margin-block-end', '2em', 'important');
        }));
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `${width}/${route}`).toBe(true);
        await page.keyboard.press('Tab'); await page.keyboard.press('Enter'); await expect(page.locator('main')).toBeFocused();
        await page.screenshot({ path: `${output}/spacing-${width}-${i}.jpg`, type: 'jpeg', quality: 80, fullPage: true });
    }
});
