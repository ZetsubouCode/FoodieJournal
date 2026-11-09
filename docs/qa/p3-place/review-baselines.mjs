import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';

const output = 'docs/qa/p3-place', browser = await chromium.launch();
for (const width of [390, 1440, 1920]) {
    const context = await browser.newContext({ viewport: { width, height: 1000 }, locale: 'id-ID', timezoneId: 'Asia/Jakarta', reducedMotion: 'reduce', colorScheme: 'dark', deviceScaleFactor: 1 });
    const page = await context.newPage(), originals = [];
    page.on('request', request => { if (request.url().endsWith('/original')) originals.push(request.url()); });
    const response = await page.goto('http://127.0.0.1:8015/place/demo-bara-senja'); assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true'); await page.evaluate(() => document.fonts.ready);
    await page.locator('img[src]').evaluateAll(nodes => Promise.all(nodes.map(n => { n.loading = 'eager'; return n.decode(); })));
    await page.mouse.move(0, 0);
    for (const state of ['hero', 'footer']) {
        const name = `place-${width}-${state}`;
        const before = `${output}/${name}-before.png`;
        if (!fs.existsSync(before)) fs.copyFileSync(`tests/Browser/__screenshots__/public-visual-win32-chromium/${name}.png`, before);
        await (state === 'hero' ? page : page.locator('.site-footer')).screenshot({ path: `${output}/${name}-reviewed.png`, animations: 'allow', caret: 'hide', scale: 'css' });
    }
    assert.deepEqual(originals, []); await context.close();
}
await browser.close();
