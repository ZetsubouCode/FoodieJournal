import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test('approved homepage renders responsively with independent seamless carousels', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('.home-hero h1')).toBeVisible();
    await expect(page.locator('.site-footer__statement')).toContainText('Ceritanya tinggal.');
    await expect(page.locator('.home-discovery__grid .discovery-tile').first()).toContainText('Makanan');
    const recent = page.locator('[data-visit-carousel]').first();
    const picks = page.locator('[data-visit-carousel]').nth(1);
    await expect(recent.locator('[data-carousel-next]')).toBeEnabled();
    await expect(picks.locator('[data-carousel-next]')).toBeEnabled();
    await recent.scrollIntoViewIfNeeded();
    const total = await recent.locator('[data-carousel-slide]').count();
    const picksStart = await picks.locator('[data-carousel-count]').textContent();
    await recent.locator('[data-carousel-prev]').click();
    await expect(recent.locator('[data-carousel-count]')).toHaveText(`${total} / ${total}`);
    await expect(picks.locator('[data-carousel-count]')).toHaveText(picksStart);
    await recent.locator('[data-carousel-next]').click();
    await expect(recent.locator('[data-carousel-count]')).toHaveText(`1 / ${total}`);
    await recent.locator('[data-carousel-toggle]').click();
    await expect(recent.locator('[data-carousel-toggle]')).toContainText('Putar otomatis');
    await expect(recent.locator('[data-carousel-slide] .visit-card__link')).toHaveCount(total);
    expect(await recent.evaluate(root => [...root.querySelectorAll('[inert] .visit-card')].every(card => !card.querySelector('[id]')))).toBe(true);

    for (const width of [1440, 1200, 768, 375, 320]) {
        await page.setViewportSize({ width, height: 900 });
        await page.goto('/');
        await expect(page.locator('.home-hero h1')).toBeVisible();
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth)).toBe(true);
        await page.screenshot({ path: `test-results/homepage-${width}.png`, fullPage: true });
    }
    await page.setViewportSize({ width: 375, height: 900 });
    await page.goto('/');
    await page.evaluate(() => { document.documentElement.style.fontSize = '200%'; });
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth)).toBe(true);
    const accessibility = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
    expect(accessibility.violations.map(violation => violation.id)).toEqual([]);
});

test('carousel autoplay, pause and reduced motion', async ({ page }) => {
    await page.goto('/');
    const recent = page.locator('[data-visit-carousel]').first();
    await recent.scrollIntoViewIfNeeded();
    await page.mouse.move(0, 0);
    const total = await recent.locator('[data-carousel-slide]').count();
    await expect(recent.locator('[data-carousel-count]')).toHaveText(`2 / ${total}`, { timeout: 9000 });
    await recent.locator('[data-carousel-toggle]').click();
    const pausedCount = await recent.locator('[data-carousel-count]').textContent();
    await page.waitForTimeout(7000);
    await expect(recent.locator('[data-carousel-count]')).toHaveText(pausedCount);
    const picks = page.locator('[data-visit-carousel]').nth(1);
    await picks.scrollIntoViewIfNeeded();
    await page.mouse.move(0, 0);
    await expect(picks.locator('[data-carousel-count]')).toHaveText('2 / 3', { timeout: 10000 });
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await expect(recent.locator('[data-carousel-toggle]')).toBeHidden();
    await expect(picks.locator('[data-carousel-toggle]')).toBeHidden();
    await recent.locator('[data-carousel-next]').click();
    await expect(recent.locator('[data-carousel-count]')).not.toHaveText(pausedCount);
});

test('card destinations stay independent and drag advances without opening detail', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 900 });
    await page.goto('/');
    const recent = page.locator('[data-visit-carousel]').first();
    await recent.scrollIntoViewIfNeeded();
    const card = recent.locator('[data-carousel-slide]').first();
    const box = await card.boundingBox();
    await page.mouse.move(box.x + box.width / 2, box.y + box.height / 2);
    await page.mouse.down();
    await page.mouse.move(box.x + box.width / 2 - 90, box.y + box.height / 2, { steps: 5 });
    await page.mouse.up();
    await expect(page).toHaveURL('/');
    await expect(recent.locator('[data-carousel-count]')).toContainText('2 /');
    await page.goto('/');
    await recent.scrollIntoViewIfNeeded();
    const detail = await recent.locator('[data-carousel-slide]').first().locator('.visit-card__link').getAttribute('href');
    const title = await recent.locator('[data-carousel-slide]').first().locator('.visit-card__title').boundingBox();
    await page.mouse.click(title.x + title.width / 2, title.y + title.height / 2);
    await expect(page).toHaveURL(detail);
    await page.goto('/');
    const picks = page.locator('[data-visit-carousel]').nth(1);
    await picks.scrollIntoViewIfNeeded();
    const tag = picks.locator('[data-carousel-slide]').first().locator('.visit-card__tag').first();
    const tagUrl = await tag.getAttribute('href');
    await tag.click();
    await expect(page).toHaveURL(tagUrl);
});
