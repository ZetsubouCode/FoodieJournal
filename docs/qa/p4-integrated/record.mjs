import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';

const output = 'docs/qa/p4-integrated', browser = await chromium.launch(), report = [];
for (const width of [1440, 390]) {
    const context = await browser.newContext({ viewport: { width, height: 1000 }, hasTouch: width === 390,
        reducedMotion: width === 390 ? 'reduce' : 'no-preference', recordVideo: { dir: 'tools/.runtime/p4-video', size: { width, height: 1000 } } });
    const page = await context.newPage();
    const activate = async locator => { if (width === 390) await locator.tap(); else { await locator.focus(); await page.keyboard.press('Enter'); } };
    const ready = async () => { await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true'); await page.evaluate(() => document.fonts.ready); };
    const navigate = async locator => {
        const href = await locator.getAttribute('href'); await activate(locator);
        await expect(page).toHaveURL(href); await page.waitForLoadState('load'); await ready();
    };
    const response = await page.goto('http://127.0.0.1:8015/'); assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1'); await ready();
    await activate(page.locator('[data-vending-lamp]')); await activate(page.locator('[data-lamp-socket]'));
    await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'on');
    const selected = page.locator('[data-memory-slot][aria-current=true]');
    const id = await selected.getAttribute('data-memory-slot'), href = await selected.getAttribute('href');
    await activate(page.locator('[data-vending-print]')); await expect(page.locator('[data-machine]')).toHaveAttribute('data-dispenser', 'ready');
    await expect(page.locator('[data-paper-path] a')).toHaveAttribute('data-printed-visit', id);
    await expect(page.locator('[data-paper-path] a')).toHaveAttribute('href', href);
    await navigate(page.locator('[data-paper-path] a'));
    await expect(page).toHaveURL(href);
    const visit = page.url();
    const trigger = page.locator('[data-view-photo]').first(); await activate(trigger);
    await expect(page.locator('[data-photo-viewer]')).toBeVisible();
    await expect(page.locator('[data-viewer-stage]')).toHaveAttribute('aria-busy', 'false');
    await page.keyboard.press('Tab'); await page.waitForTimeout(350); await page.keyboard.press('Escape'); await expect(trigger).toBeFocused();
    await navigate(page.getByRole('link', { name: 'Lihat tempat', exact: true }));
    await page.locator('.place-history').scrollIntoViewIfNeeded(); await page.waitForTimeout(350);
    await navigate(page.locator('.site-header').getByRole('link', { name: 'Jelajah', exact: true }));
    await activate(page.locator('.curiosity-surface--style')); await expect(page.locator('[data-discovery-results]')).toHaveAttribute('aria-busy', 'false');
    await activate(page.getByRole('button', { name: /^Filter/ })); await expect(page.locator('[data-filter-drawer]')).toBeVisible();
    await page.keyboard.press('Tab'); await page.waitForTimeout(350); await page.keyboard.press('Escape');
    await page.goBack(); await expect(page).toHaveURL('http://127.0.0.1:8015/explore');
    await expect(page.locator('[data-discovery-results]')).toHaveAttribute('aria-busy', 'false');
    await page.locator('.site-footer').scrollIntoViewIfNeeded(); await page.waitForTimeout(350);
    const video = page.video(); await context.close(); const file = `${output}/realtime-${width}.webm`; await video.saveAs(file);
    report.push({ width, file, visit, selectedId: id, browser: browser.version(), build: createHash('sha256').update(fs.readFileSync('public/build/manifest.json')).digest('hex'), input: width === 390 ? 'emulated touch with reduced motion' : 'keyboard activation and native Tab/Escape', timing: 'Production time, no clock/slowMo/animation rewrite; 350ms observation holds at settled states' });
}
fs.writeFileSync(`${output}/realtime.json`, JSON.stringify(report, null, 2)); await browser.close();
