import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';

const output = 'docs/qa/p1-physical-response';
const browser = await chromium.launch(), records = [];
for (const width of [1440, 390]) {
    const context = await browser.newContext({ viewport: { width, height: 1000 }, deviceScaleFactor: 1,
        locale: 'id-ID', timezoneId: 'Asia/Jakarta', colorScheme: 'dark', reducedMotion: 'no-preference', hasTouch: width === 390,
        recordVideo: { dir: 'tools/.runtime/p1-video', size: { width, height: 1000 } } });
    const page = await context.newPage(), errors = [];
    page.on('pageerror', e => errors.push(e.message));
    await page.addInitScript(() => {
        window.p1Stages = [];
        addEventListener('DOMContentLoaded', () => {
            const n = document.querySelector('[data-machine]');
            if (n) new MutationObserver(() => window.p1Stages.push({ ms: performance.now(), power: n.dataset.power, lamp: n.dataset.lamp, dispenser: n.dataset.dispenser })).observe(n, { attributes: true, attributeFilter: ['data-power', 'data-lamp', 'data-dispenser'] });
        });
    });
    const response = await page.goto('http://127.0.0.1:8015/'); assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true'); await page.evaluate(() => document.fonts.ready);
    async function install(target) {
        const lamp = page.locator('[data-vending-lamp]'), dock = page.locator(target);
        await lamp.scrollIntoViewIfNeeded();
        if (width === 390) { await lamp.tap(); await dock.tap(); }
        else {
            const a = await lamp.boundingBox(), b = await dock.boundingBox();
            await page.mouse.move(a.x + a.width / 2, a.y + a.height / 2); await page.mouse.down();
            await page.mouse.move(b.x + b.width / 2, b.y + b.height / 2, { steps: 16 }); await page.mouse.up();
        }
    }
    await install('[data-lamp-socket]'); await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'on');
    const slot = page.locator('[data-memory-slot]').nth(1), id = await slot.getAttribute('data-memory-slot'), href = await slot.getAttribute('href');
    await slot.click(); await page.locator('[data-vending-print]').click();
    await expect(page.locator('[data-machine]')).toHaveAttribute('data-dispenser', 'ready');
    await expect(page.locator('[data-paper-path] a')).toHaveAttribute('data-printed-visit', id);
    const stages = await page.evaluate(() => window.p1Stages);
    await page.locator('[data-paper-path] a').click(); await expect(page).toHaveURL(href);
    await expect(page.locator('.visit-dossier')).toBeVisible();
    await page.locator('.dossier-hero img').evaluate(n => n.decode());
    // Keep the retrieved story on screen long enough to appear in the raw recording.
    await page.waitForTimeout(500);
    await page.goBack(); await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'on');
    await page.locator('[data-vending-print]').click(); await expect(page.locator('[data-machine]')).toHaveAttribute('data-dispenser', 'ready');
    await install('[data-lamp-cradle]'); await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'off');
    await expect(page.locator('[data-paper-path] a')).toHaveCount(0); assert.equal(errors.length, 0);
    // Observe the final dark tray without changing application timing.
    await page.waitForTimeout(500);
    const video = page.video(); await context.close(); const file = `${output}/realtime-${width}.webm`; await video.saveAs(file);
    records.push({ width, video: file, videoSha256: createHash('sha256').update(fs.readFileSync(file)).digest('hex'), id, href, stages, errors });
}
fs.writeFileSync(`${output}/realtime.json`, JSON.stringify({ browser: browser.version(), method: 'Unmodified production-time recording; no clock, seeking, slowMo, screenshot posing, animation override or edits. Mouse drag at 1440; touch tap-target at 390. Retrieve, Back, print again, reverse/off clears paper.', records }, null, 2) + '\n');
await browser.close();
console.log('Recorded complete forward/retrieve/Back/reverse journeys at desktop and compact.');
