import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';

const browser = await chromium.launch(), report = [];
for (const width of [1440, 375]) {
    const context = await browser.newContext({ viewport: { width, height: 900 }, hasTouch: width === 375, reducedMotion: width === 375 ? 'reduce' : 'no-preference', recordVideo: { dir: 'tools/.runtime/p3p-video', size: { width, height: 900 } } });
    const page = await context.newPage();
    const response = await page.goto('http://127.0.0.1:8017/place/qa-stable-cover'); assert.equal(response.headers()['x-foodie-hardening-fixture'], 'v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true'); await page.evaluate(() => document.fonts.ready);
    await page.waitForTimeout(400); await page.locator('.place-history__group').first().scrollIntoViewIfNeeded(); await page.waitForTimeout(400);
    const next = page.getByRole('link', { name: 'Berikutnya', exact: true });
    if (width === 375) await next.tap(); else { await next.focus(); await page.keyboard.press('Enter'); }
    await page.waitForLoadState('load'); await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true'); await page.evaluate(() => document.fonts.ready);
    await page.locator('.place-history__group').last().scrollIntoViewIfNeeded(); await page.waitForTimeout(400);
    const link = page.locator('.place-history .visit-card__link').last(); const href = await link.getAttribute('href');
    if (width === 375) await link.tap(); else { await link.focus(); await page.waitForTimeout(400); await page.keyboard.press('Enter'); }
    await expect(page).toHaveURL(href); await page.waitForLoadState('load'); await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true'); await page.waitForTimeout(400);
    const back = page.getByRole('link', { name: 'Lihat tempat', exact: true });
    if (width === 375) await back.tap(); else { await back.focus(); await page.keyboard.press('Enter'); }
    await page.waitForLoadState('load'); await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.goBack(); await page.goBack(); await expect(page).toHaveURL('http://127.0.0.1:8017/place/qa-stable-cover?page=2');
    await page.locator('.site-footer').scrollIntoViewIfNeeded(); await page.waitForTimeout(400);
    const video = page.video(); await context.close(); const file = `docs/qa/p3-place/realtime-${width}.webm`; await video.saveAs(file);
    report.push({ width, file, input: width === 375 ? 'emulated touch, reduced motion' : 'keyboard', timing: 'native production timing; short observation holds on real states, no clock/slowMo/animation rewrite' });
}
fs.writeFileSync('docs/qa/p3-place/realtime.json', JSON.stringify(report, null, 2)); await browser.close();
