import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import fs from 'node:fs';

const fixture = JSON.parse(fs.readFileSync('tools/.runtime/browser-credentials.json', 'utf8'));
const qaCard = page => page.locator('.visit-card').filter({ has: page.locator(`.visit-card__link[href$="/visit/${fixture.visitSlug}"]`) });

test('F Refined visit card reflows and keeps independent destinations', async ({ page }) => {
    test.setTimeout(120000);
    for (const width of [1440, 768, 375]) {
        await page.setViewportSize({ width, height: 900 });
        await page.goto('/explore?tab=visit&food_type=qa-mie-ayam');
        const card = qaCard(page);
        await expect(card).toBeVisible();
        await card.locator('img').scrollIntoViewIfNeeded();
        await expect(card.locator('img')).toHaveJSProperty('complete', true);
        await expect(card.locator('img')).not.toHaveJSProperty('naturalWidth', 0);
        await expect(card.locator('.visit-card__link')).toHaveAttribute('href', /\/visit\//);
        await expect(card.locator('.visit-card__tag').first()).toHaveAttribute('href', /\/explore\?/);
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth)).toBe(true);
        await card.screenshot({ path: `test-results/visit-card-${width}.png` });
    }

    await page.setViewportSize({ width: 1440, height: 900 });
    await page.route('**/media/**', route => route.fulfill({ path: 'database/seeders/assets/coffee.jpg', contentType: 'image/jpeg' }));
    await page.goto('/explore?tab=visit&food_type=qa-mie-ayam');
    const card = qaCard(page);
    await card.locator('img').scrollIntoViewIfNeeded();
    await expect(card.locator('img')).toHaveJSProperty('complete', true);
    await expect(card.locator('img')).not.toHaveJSProperty('naturalWidth', 0);
    await card.screenshot({ path: 'test-results/visit-card-coffee-photo.png' });
    await page.unroute('**/media/**');
    const detailUrl = await card.locator('.visit-card__link').getAttribute('href');
    await card.scrollIntoViewIfNeeded();
    const titleBox = await card.locator('.visit-card__title').boundingBox();
    await page.mouse.click(titleBox.x + titleBox.width / 2, titleBox.y + titleBox.height / 2);
    await expect(page).toHaveURL(detailUrl);

    await page.goto('/explore?tab=visit&food_type=qa-mie-ayam');
    await qaCard(page).scrollIntoViewIfNeeded();
    const photoBox = await qaCard(page).locator('img').boundingBox();
    await page.mouse.click(photoBox.x + photoBox.width / 3, photoBox.y + photoBox.height / 2);
    await expect(page).toHaveURL(detailUrl);

    await page.goto('/explore?tab=visit&food_type=qa-mie-ayam');
    const recommendation = qaCard(page).locator('.visit-card__recommendation').first();
    const recommendationUrl = await recommendation.getAttribute('href');
    await recommendation.click();
    await expect(page).toHaveURL(recommendationUrl);

    await page.goto('/explore?tab=visit&food_type=qa-mie-ayam');
    const tag = qaCard(page).locator('.visit-card__tag').first();
    const tagUrl = await tag.getAttribute('href');
    await tag.click();
    await expect(page).toHaveURL(tagUrl);

    await page.goto('/explore?tab=visit&food_type=qa-mie-ayam');
    const overflow = qaCard(page).locator('[data-card-overflow]');
    const button = overflow.locator('button');
    await button.click();
    await expect(button).toHaveAttribute('aria-expanded', 'true');
    await expect(overflow.locator('[data-card-overflow-panel]')).toBeVisible();
    const accessibility = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
    expect(accessibility.violations.map(violation => violation.id)).toEqual([]);
    await page.keyboard.press('Escape');
    await expect(button).toBeFocused();
    await expect(overflow.locator('[data-card-overflow-panel]')).toBeHidden();
    await button.click();
    await page.locator('h1').click();
    await expect(overflow.locator('[data-card-overflow-panel]')).toBeHidden();
    await button.click();
    const hiddenTag = overflow.locator('[data-card-overflow-panel] a').first();
    const hiddenTagUrl = await hiddenTag.getAttribute('href');
    await hiddenTag.click();
    await expect(page).toHaveURL(hiddenTagUrl);

    await page.setViewportSize({ width: 375, height: 900 });
    await page.goto('/explore?tab=visit&food_type=qa-mie-ayam');
    await page.evaluate(() => { document.documentElement.style.fontSize = '200%'; });
    await qaCard(page).locator('.visit-card__title').evaluate(title => { title.textContent = 'Cerita yang sangat panjang dan terus berlanjut tentang pengalaman makan di kota lama dengan banyak detail untuk diuji'; });
    await qaCard(page).locator('.visit-card__tag').first().evaluate(tag => { tag.textContent = 'Kategori dengan nama yang sangat panjang untuk memastikan chip tetap berada di dalam kartu'; });
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth)).toBe(true);
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await expect(qaCard(page).locator('img')).toHaveCSS('transition-duration', '0s');
});

test('failed visit-card media reveals a stable fallback', async ({ page }) => {
    await page.route('**/media/**', route => route.abort());
    await page.goto('/explore?tab=visit&food_type=qa-mie-ayam');
    const card = qaCard(page);
    await card.scrollIntoViewIfNeeded();
    await expect(card.locator('[data-visit-card-image-fallback]')).toBeVisible();
    await expect(card.locator('img')).toBeHidden();
});

test('touch taps keep card navigation separate from tag disclosure', async ({ browser }) => {
    const context = await browser.newContext({ hasTouch: true, isMobile: true, viewport: { width: 375, height: 812 } });
    const page = await context.newPage();
    await page.goto('/explore?tab=visit&food_type=qa-mie-ayam');
    const card = qaCard(page);
    const more = card.locator('[data-card-overflow-button]');
    await more.tap();
    await expect(more).toHaveAttribute('aria-expanded', 'true');
    await expect(card.locator('[data-card-overflow-panel]')).toBeVisible();
    await card.locator('.visit-card__tag').first().tap();
    await expect(page).toHaveURL(/\/explore\?category/);
    await page.goto('/explore?tab=visit&food_type=qa-mie-ayam');
    await qaCard(page).locator('.visit-card__link').tap({ position: { x: 20, y: 20 } });
    await expect(page).toHaveURL(new RegExp(`/visit/${fixture.visitSlug}$`));
    await context.close();
});
