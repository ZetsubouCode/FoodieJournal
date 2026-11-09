import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';

const browser = await chromium.launch(), recordings = [];
for (const width of [1440, 375]) {
    const context = await browser.newContext({ viewport: { width, height: 900 }, hasTouch: width === 375, recordVideo: { dir: 'tools/.runtime/p3-video', size: { width, height: 900 } } });
    const page = await context.newPage();
    const response = await page.goto('http://127.0.0.1:8015/visit/demo-bara-di-ujung-hari'); assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.waitForTimeout(350);
    await page.locator('.dossier-story').scrollIntoViewIfNeeded(); await page.locator('.tasting-note').first().scrollIntoViewIfNeeded();
    await page.waitForTimeout(350);
    const trigger = page.locator('[data-view-photo]').first();
    if (width === 375) await trigger.tap(); else { await trigger.focus(); await page.keyboard.press('Enter'); }
    await expect(page.locator('[data-viewer-image]')).toBeVisible();
    await page.waitForTimeout(500);
    await page.keyboard.press('ArrowRight'); await expect(page.locator('[data-viewer-status]')).toHaveText(/Foto 2 dari/);
    await page.waitForTimeout(350);
    await page.locator('[data-viewer-zoom]').click(); await page.locator('[data-viewer-zoom]').click();
    await page.route('**/media/**/original', route => route.fulfill({ status: 403, body: '' }));
    await page.keyboard.press('ArrowLeft'); await expect(page.locator('[data-viewer-status]')).toContainText('Menampilkan pratinjau');
    await page.waitForTimeout(500);
    await page.unroute('**/media/**/original'); await page.locator('[data-viewer-retry]').click(); await expect(page.locator('[data-viewer-status]')).toHaveText(/Foto 1 dari/);
    await page.waitForTimeout(350);
    if (width === 375) await page.locator('[data-dialog-close]:visible').tap(); else await page.keyboard.press('Escape');
    await expect(trigger).toBeFocused(); await page.locator('.dossier-conclusion').scrollIntoViewIfNeeded();
    await page.waitForTimeout(350);
    const video = page.video(); await context.close(); const file = `docs/qa/p3-visit/realtime-${width}.webm`; await video.saveAs(file);
    recordings.push({ width, file, timing: 'native production time; no clock, slowMo or seeking', input: width === 375 ? 'emulated touch plus arrows' : 'keyboard plus zoom/retry clicks' });
}
fs.writeFileSync('docs/qa/p3-visit/realtime.json', JSON.stringify(recordings, null, 2)); await browser.close();
