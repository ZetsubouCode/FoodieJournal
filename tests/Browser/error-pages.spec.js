import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const base = process.env.FOODIE_BASE_URL || 'http://127.0.0.1:8011';
const sizes = [
    [375, 812], [390, 844], [768, 1024], [1366, 768], [1920, 1080], [320, 568], [812, 375],
];

test('editorial 404 scene reflows, loads its photograph and remains keyboard accessible', async ({ page }) => {
    test.setTimeout(120000);
    for (const [width, height] of sizes) {
        await page.setViewportSize({ width, height });
        const response = await page.goto(`${base}/missing-error-page-preview`);
        expect(response.status()).toBe(404);
        await expect(page.getByRole('heading', { name: 'Halaman yang dicari tidak ditemukan' })).toBeVisible();
        await expect(page.locator('.error-code-text')).toHaveText('404');
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        const image = await page.request.get(`${base}/assets/images/error/cinematic-kitchen.webp`);
        expect(image.ok()).toBe(true);
        const css = await page.request.get(`${base}/assets/css/error.css`);
        expect(css.ok()).toBe(true);
        await page.screenshot({ path: `test-results/error-404-${width}x${height}.png`, fullPage: true });
    }

    await page.setViewportSize({ width: 1366, height: 768 });
    await page.goto(`${base}/missing-error-page-preview`);
    await page.keyboard.press('Tab');
    await expect(page.getByRole('link', { name: 'Lewati ke isi' })).toBeFocused();
    await page.keyboard.press('Tab');
    await expect(page.getByRole('link', { name: 'Foodie, beranda' })).toBeFocused();
    const accessibility = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
    expect(accessibility.violations.map(v => v.id)).toEqual([]);

    for (const code of ['403', '500']) {
        await page.locator('.error-code-text').evaluate((node, value) => { node.textContent = value; }, code);
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        await page.screenshot({ path: `test-results/error-code-${code}-desktop.png` });
    }

    await page.emulateMedia({ reducedMotion: 'reduce' });
    expect(await page.locator('.error-action--primary').evaluate(node => getComputedStyle(node).transitionDuration)).toBe('0s');
    await page.route('**/cinematic-kitchen.webp', route => route.abort());
    await page.reload();
    await expect(page.getByRole('heading', { name: 'Halaman yang dicari tidak ditemukan' })).toBeVisible();
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
});
