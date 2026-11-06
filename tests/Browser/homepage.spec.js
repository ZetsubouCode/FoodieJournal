import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test('homepage editorial collections and shared geometry reflow through desktop, mobile and 200% text', async ({ page }) => {
    test.setTimeout(90000);
    for (const width of [1440, 1200, 768, 375, 320]) {
        await page.setViewportSize({ width, height: 900 }); await page.goto('/');
        await expect(page.locator('[data-home-archive] h1')).toBeVisible();
        await expect(page.locator('.fresh-archive__lead .archive-entry')).toHaveCount(1);
        await expect(page.locator('.personal-gallery .archive-entry').first()).toContainText('Ingin kembali QA');
        await expect(page.locator('[data-visit-carousel], .home-discovery')).toHaveCount(0);
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        const home = await page.locator('.site-header').boundingBox();
        await page.goto('/explore');
        const explore = await page.locator('.site-header').boundingBox();
        expect(home.x).toEqual(explore.x); expect(home.width).toEqual(explore.width); expect(home.height).toEqual(explore.height);
    }
    await page.goto('/'); await page.evaluate(() => { document.documentElement.style.fontSize='200%'; });
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
    const result = await new AxeBuilder({page}).withTags(['wcag2a','wcag2aa','wcag21aa','wcag22aa']).analyze();
    expect(result.violations.map(v=>v.id)).toEqual([]);
});

test('personal picks link to actual records and keep endorsements distinct from score', async ({ page }) => {
    await page.goto('/');
    const pick=page.locator('.personal-gallery .archive-entry').first();
    await expect(pick.locator('.archive-entry__recommendation')).toContainText('Ingin kembali QA');
    await expect(pick.locator('.archive-entry__rating')).toHaveCount(0);
    const href=await pick.locator('a').getAttribute('href'); await pick.locator('a').click();
    await expect(page).toHaveURL(href); await expect(page.locator('.visit-dossier')).toBeVisible();
});
