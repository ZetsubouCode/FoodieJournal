import { test, expect } from '@playwright/test';
import fs from 'node:fs';

test('record local paint, layout stability and token contrast measurements', async ({ page, browser }) => {
    await page.addInitScript(() => {
        window.foodieMeasurements = { lcp_ms: 0, cls: 0 };
        new PerformanceObserver(list => { for (const entry of list.getEntries()) window.foodieMeasurements.lcp_ms = entry.startTime; }).observe({ type: 'largest-contentful-paint', buffered: true });
        new PerformanceObserver(list => { for (const entry of list.getEntries()) if (!entry.hadRecentInput) window.foodieMeasurements.cls += entry.value; }).observe({ type: 'layout-shift', buffered: true });
    });
    await page.setViewportSize({ width: 1440, height: 1000 }); await page.goto('/');
    await expect(page.locator('img[fetchpriority=high]')).toHaveJSProperty('complete', true);
    await expect.poll(() => page.evaluate(() => window.foodieMeasurements.lcp_ms)).toBeGreaterThan(0);
    const measured = await page.evaluate(() => {
        const style = getComputedStyle(document.documentElement);
        const luminance = value => {
            const rgb = value.trim().replace('#', '').match(/.{2}/g).map(hex => parseInt(hex, 16) / 255).map(v => v <= .04045 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4);
            return rgb[0] * .2126 + rgb[1] * .7152 + rgb[2] * .0722;
        };
        const contrast = (a, b) => { const x = luminance(style.getPropertyValue(a)), y = luminance(style.getPropertyValue(b)); return Number(((Math.max(x, y) + .05) / (Math.min(x, y) + .05)).toFixed(2)); };
        const pairs = [
            ['--text-primary', '--surface-page', 4.5], ['--text-secondary', '--surface-raised', 4.5],
            ['--text-muted', '--surface-hover', 4.5], ['--action-primary-text', '--action-primary-bg', 4.5],
            ['--border-subtle', '--surface-hover', 3], ['--focus-ring', '--surface-page', 3],
        ];
        return { ...window.foodieMeasurements, navigation_ms: performance.getEntriesByType('navigation')[0].duration, contrast: pairs.map(([foreground, background, minimum]) => ({ foreground, background, minimum, ratio: contrast(foreground, background) })) };
    });
    for (const pair of measured.contrast) expect(pair.ratio).toBeGreaterThanOrEqual(pair.minimum);
    fs.mkdirSync('docs/qa', { recursive: true });
    fs.writeFileSync('docs/qa/lab-measurements.json', JSON.stringify({ environment: 'Local Chromium, isolated QA fixtures, no network throttling. Single lab sample, not field Core Web Vitals.', browser: browser.version(), viewport: { width: 1440, height: 1000 }, ...measured, inp: 'Not measured; no field INP claim.' }, null, 2));
});
