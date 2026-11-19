import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import { execFileSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import assert from 'node:assert/strict';
const output = 'docs/qa/p4-integrated', browser = await chromium.launch(), layouts = [];
const states = process.argv.includes('--states'), prefix = states ? 'contrast-states' : 'contrast';
const visit = '/visit/demo-bara-di-ujung-hari';
const jobs = states ? [['home-on', '/'], ['home-printed', '/'], ['drawer', '/explore'], ['busy', '/explore'], ['viewer', visit]] :
    ['/', '/explore', visit, '/place/demo-bara-senja', ...[403, 404, 419, 429, 500, 503].map(n => `/p0h-error/${n}`)].map(route => [route, route]);
for (const width of [390, 1440]) for (const [i, [name, route]] of jobs.entries()) {
    const page = await browser.newPage({ viewport: { width, height: 1000 }, reducedMotion: 'reduce' });
    const response = await page.goto(`http://127.0.0.1:${route.startsWith('/p0h-') ? 8017 : 8015}${route}`);
    assert.equal(response.headers()[route.startsWith('/p0h-') ? 'x-foodie-hardening-fixture' : 'x-foodie-visual-fixture'], route.startsWith('/p0h-') ? 'v1' : 'photo-v1');
    if (!route.startsWith('/p0h-')) await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.evaluate(async () => { await document.fonts.ready; await Promise.all([...document.images].filter(n => n.src).map(n => { n.loading = 'eager'; return n.decode().catch(() => {}); })); });
    let release = () => {};
    if (name.startsWith('home-')) {
        await page.locator('[data-vending-lamp]').click(); await page.locator('[data-lamp-socket]').click();
        await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'on');
        if (name === 'home-printed') { await page.locator('[data-vending-print]').click(); await expect(page.locator('[data-machine]')).toHaveAttribute('data-dispenser', 'ready'); }
    }
    if (name === 'drawer') await page.getByRole('button', { name: /^Filter/ }).click();
    if (name === 'viewer') { await page.locator('[data-view-photo]').first().click(); await expect(page.locator('[data-viewer-stage]')).toHaveAttribute('aria-busy', 'false'); }
    if (name === 'busy') {
        const wait = new Promise(resolve => release = resolve);
        await page.route('**/explore?**', async r => { await wait; await r.fulfill({ status: 503, body: '' }).catch(() => {}); });
        await page.locator('#discovery-search').fill('p4-held'); await page.locator('#discovery-search').press('Enter');
        await expect(page.locator('[data-discovery-results]')).toHaveAttribute('aria-busy', 'true');
    }
    const modalOpen = await page.locator('dialog[open]').count() > 0;
    const samples = await page.evaluate(() => {
        const result = [], walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
        while (walker.nextNode()) {
            const node = walker.currentNode, parent = node.parentElement;
            if (!node.textContent.trim() || parent.closest('script, style, noscript, .sr-only, [hidden], [inert], [aria-hidden=true], [aria-disabled=true], :disabled, dialog:not([open])')) continue;
            const modal = document.querySelector('dialog[open]'); if (modal && !modal.contains(parent)) continue;
            if (parent.closest('details:not([open])') && !parent.closest('summary')) continue;
            const style = getComputedStyle(parent), box = parent.getBoundingClientRect();
            if (style.visibility !== 'visible' || !box.width || !box.height) continue;
            const range = document.createRange(); range.selectNodeContents(node);
            const rects = [...range.getClientRects()].filter(r => r.width && r.height).map(r => ({ x: r.x + (modal ? 0 : scrollX), y: r.y + (modal ? 0 : scrollY), width: r.width, height: r.height }));
            if (!rects.length) continue;
            let opacity = 1; for (let n = parent; n; n = n.parentElement) opacity *= Number(getComputedStyle(n).opacity);
            if (!opacity) continue;
            result.push({ text: node.textContent.trim().slice(0, 120), tag: parent.tagName, class: parent.className, color: style.color, opacity, size: parseFloat(style.fontSize), weight: style.fontWeight, rects });
        }
        return result;
    });
    const actual = `tools/.runtime/p4-${prefix}-${width}-${i}-actual.png`;
    await page.screenshot({ path: actual, fullPage: !modalOpen });
    await page.evaluate(() => document.querySelectorAll('body *').forEach(n => n.style.setProperty('-webkit-text-fill-color', 'transparent', 'important')));
    const background = `tools/.runtime/p4-${prefix}-${width}-${i}.png`;
    await page.screenshot({ path: background, fullPage: !modalOpen }); layouts.push({ width, route, state: name, browser: browser.version(), build: createHash('sha256').update(fs.readFileSync('public/build/manifest.json')).digest('hex'), modalOpen, actual, background, samples }); release(); await page.close();
}
await browser.close(); fs.writeFileSync(`${output}/${prefix}-input.json`, JSON.stringify(layouts));
console.log(execFileSync('python', [`${output}/contrast.py`, prefix], { encoding: 'utf8' }));
