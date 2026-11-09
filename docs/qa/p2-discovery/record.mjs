import { chromium, expect } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';

const output = 'docs/qa/p2-discovery', browser = await chromium.launch(), recordings = [];
for (const width of [1440, 375]) {
    const context = await browser.newContext({ viewport: { width, height: 1000 }, reducedMotion: 'no-preference', hasTouch: width === 375,
        locale: 'id-ID', timezoneId: 'Asia/Jakarta', recordVideo: { dir: 'tools/.runtime/p2-video', size: { width, height: 1000 } } });
    const page = await context.newPage(), errors = []; page.on('pageerror', e => errors.push(e.message));
    const response = await page.goto('http://127.0.0.1:8015/explore'); assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    const link = page.locator('.curiosity-surface--style'), target = await link.getAttribute('href');
    if (width === 375) await link.tap(); else { await link.focus(); await page.keyboard.press('Enter'); }
    await expect(page).toHaveURL(target); await expect(link).toHaveAttribute('data-selected', 'true');
    await page.getByRole('button', { name: /^Filter/ }).click(); await page.locator('[data-filter-group-open=rating]').click();
    await page.locator('[name=price_min]').fill('1000'); await page.locator('[data-filter-apply]').click();
    await expect(page).toHaveURL(/price_min=1000/);
    await page.getByRole('link', { name: 'Daftar', exact: true }).click(); await expect(page.locator('.results')).toHaveClass(/results--list/);
    let fail = true;
    await page.route('**/explore?**', route => route.request().headers()['x-explore-fragment'] === '1' && fail ? route.fulfill({ status: 503, body: '' }) : route.continue());
    await page.getByRole('link', { name: 'Tempat', exact: true }).click(); await expect(page.locator('[data-discovery-error]')).toBeVisible();
    await page.locator('[data-discovery-error]').scrollIntoViewIfNeeded();
    // Observe the actual recovery state in the unedited recording.
    await page.waitForTimeout(500); fail = false; await page.locator('[data-discovery-retry]').click();
    await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', 'place');
    await page.goBack(); await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', 'food');
    await expect(page).toHaveURL(/price_min=1000/);
    const visit = page.locator('.results article a[href*="/visit/"]').first(), href = await visit.getAttribute('href'); await visit.click();
    await expect(page).toHaveURL(href); await expect(page.locator('.visit-dossier')).toBeVisible(); await page.waitForTimeout(500);
    assert.equal(errors.length, 0); const video = page.video(); await context.close(); const file = `${output}/realtime-${width}.webm`; await video.saveAs(file);
    recordings.push({ width, target, retrieved: href, file, sha256: createHash('sha256').update(fs.readFileSync(file)).digest('hex'), errors });
}
fs.writeFileSync(`${output}/realtime.json`, JSON.stringify({ browser: browser.version(), build: createHash('sha256').update(fs.readFileSync('public/build/manifest.json')).digest('hex'), method: 'Normal-time keyboard/touch board, staged price Apply, list, actual failed request/retry, Back restoration and actual Visit navigation. No clock, seeking, slowMo, motion override or edits. Observation holds do not alter application work.', recordings }, null, 2) + '\n');
await browser.close(); console.log('Recorded desktop keyboard and compact touch discovery journeys.');
