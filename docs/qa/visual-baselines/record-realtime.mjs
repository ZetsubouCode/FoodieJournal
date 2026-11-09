import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import { createHash } from 'node:crypto';

const browser = await chromium.launch();
const context = await browser.newContext({ viewport: { width: 1440, height: 1000 }, deviceScaleFactor: 1,
    locale: 'id-ID', timezoneId: 'Asia/Jakarta', colorScheme: 'dark', reducedMotion: 'no-preference',
    recordVideo: { dir: 'tools/.runtime/visual-video', size: { width: 1440, height: 1000 } } });
const page = await context.newPage();
await page.goto('http://127.0.0.1:8015/');
await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
await page.evaluate(() => document.fonts.ready);
await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'off');
await page.locator('[data-vending-lamp]').click(); await page.locator('[data-lamp-socket]').click();
await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'on');
const slot = page.locator('[data-memory-slot]').nth(1);
const id = await slot.getAttribute('data-memory-slot'), href = await slot.getAttribute('href');
await slot.click(); await page.locator('[data-vending-print]').click();
await expect(page.locator('[data-machine]')).toHaveAttribute('data-dispenser', 'ready');
await expect(page.locator('[data-paper-path] a')).toHaveAttribute('data-printed-visit', id);
await page.locator('[data-paper-path] a').click(); await expect(page).toHaveURL(href);
await expect(page.locator('.visit-dossier')).toBeVisible();
await page.locator('.dossier-hero img').evaluate(n => n.decode());
await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
// Hold the completed page briefly so the raw recording includes its final paint.
await page.waitForTimeout(500);
const video = page.video(); await context.close();
await video.saveAs('docs/qa/visual-baselines/realtime-machine.webm');
fs.writeFileSync('docs/qa/visual-baselines/realtime.json', JSON.stringify({ videoSha256: createHash('sha256').update(fs.readFileSync('docs/qa/visual-baselines/realtime-machine.webm')).digest('hex'),
    buildManifestSha256: createHash('sha256').update(fs.readFileSync('public/build/manifest.json')).digest('hex'), browser: browser.version(), viewport: { width: 1440, height: 1000 },
    route: 'http://127.0.0.1:8015/', selectedVisit: id, retrieved: href, finalObservationMs: 500,
    method: 'Normal motion and real-time recording; no clock install, seeking, slowMo, animation overrides, screenshot fast-forward or video edits. Fixed server date affects content only. Not a performance benchmark.' }, null, 2) + '\n');
await browser.close();
console.log('Recorded unmodified off → on → select → print → retrieve journey.');
