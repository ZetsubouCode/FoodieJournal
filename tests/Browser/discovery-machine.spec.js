import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import fs from 'node:fs';

test('center button rotates the real wheel and updates projection, URL and Memory Reel', async ({ page }) => {
    page.on('pageerror', error => console.log('PAGE ERROR', error.message));
    page.on('console', message => { if (message.type() === 'error') console.log('CONSOLE ERROR', message.text()); });
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto('/explore');
    const machine = page.locator('[data-discovery]');
    await expect(machine).toHaveAttribute('data-selected-label', 'Semua rasa');
    await expect(machine.locator('[data-wheel-next]')).toBeEnabled();
    await page.screenshot({ path: 'test-results/discovery-ready-1440.png', fullPage: true });
    const before = await machine.locator('[data-wheel-rotor]').evaluate(node => getComputedStyle(node).transform);
    await machine.locator('[data-wheel-next]').click();
    await expect(machine.locator('[data-discovery-scene]')).toHaveAttribute('data-phase', 'changing');
    await page.screenshot({ path: 'test-results/discovery-dark-1440.png' });
    await expect(machine).toHaveAttribute('data-selected-label', 'Mie ayam QA');
    await expect(page).toHaveURL(/food_type/);
    await expect(machine.locator('.projection h2')).toHaveText('Mie ayam QA');
    await expect(machine.locator('.memory-reel .visit-card')).toHaveCount(6);
    const after = await machine.locator('[data-wheel-rotor]').evaluate(node => getComputedStyle(node).transform);
    expect(after).not.toBe(before);
    await page.screenshot({ path: 'test-results/discovery-selected-1440.png', fullPage: true });
    await page.goBack();
    await expect(machine).toHaveAttribute('data-selected-label', 'Semua rasa');
    await expect(machine.locator('.projection h2')).toHaveText('Semua rasa');
    await page.goForward();
    await expect(machine).toHaveAttribute('data-selected-label', 'Mie ayam QA');
    await expect(machine.locator('[data-wheel-next]')).toBeEnabled();
    await machine.locator('[data-wheel-next]').click();
    await expect(machine.locator('[data-discovery-scene]')).toHaveAttribute('data-phase', 'changing');
    const wrapFrames = await machine.locator('[data-wheel-rotor]').evaluate(node => node.getAnimations()[0]?.effect.getKeyframes().map(frame => frame.transform));
    expect(wrapFrames).toEqual(['rotate(-120deg)', 'rotate(-300deg)']);
    await expect(machine).toHaveAttribute('data-selected-label', 'Semua rasa');
    await expect(page).not.toHaveURL(/food_type/);
});

test('machine reflows, remains keyboard usable and honors reduced motion', async ({ page }) => {
    for (const width of [1920, 768, 390, 320]) {
        await page.setViewportSize({ width, height: width < 500 ? 844 : 1000 });
        await page.goto('/explore');
        await expect(page.locator('[data-wheel-next]')).toBeEnabled();
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth)).toBe(true);
        await page.screenshot({ path: `test-results/discovery-${width}.png`, fullPage: true });
    }
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto('/explore');
    await expect(page.locator('[data-wheel-next]')).toBeEnabled();
    await page.locator('[data-wheel-next]').focus();
    await page.keyboard.press('Enter');
    await expect(page.locator('[data-discovery]')).toHaveAttribute('data-selected-label', 'Mie ayam QA');
    expect(await page.locator('[data-wheel-next]').evaluate(node => getComputedStyle(node).animationName)).toBe('none');
    const accessibility = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
    expect(accessibility.violations.map(violation => ({ id: violation.id, nodes: violation.nodes.map(node => node.target) }))).toEqual([]);
    await page.evaluate(() => { document.documentElement.style.fontSize = '200%'; });
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth)).toBe(true);
});

test('records a local Explore paint and layout sample', async ({ page, browser }) => {
    await page.addInitScript(() => {
        window.discoveryMetrics = { lcp_ms: 0, cls: 0 };
        new PerformanceObserver(list => { for (const entry of list.getEntries()) window.discoveryMetrics.lcp_ms = entry.startTime; }).observe({ type: 'largest-contentful-paint', buffered: true });
        new PerformanceObserver(list => { for (const entry of list.getEntries()) if (!entry.hadRecentInput) window.discoveryMetrics.cls += entry.value; }).observe({ type: 'layout-shift', buffered: true });
    });
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto('/explore');
    await expect(page.locator('.projection__image img')).toHaveJSProperty('complete', true);
    await expect.poll(() => page.evaluate(() => window.discoveryMetrics.lcp_ms)).toBeGreaterThan(0);
    const metrics = await page.evaluate(() => window.discoveryMetrics);
    expect(metrics.cls).toBeLessThan(.1);
    fs.writeFileSync('test-results/discovery-performance.json', JSON.stringify({
        environment: 'Local Chromium, isolated QA fixtures, no network throttling; single sample, not field Core Web Vitals.',
        browser: browser.version(), viewport: { width: 1440, height: 900 }, ...metrics, inp: 'Not measured',
    }, null, 2));
});

test('failed category fetch preserves the old projection and offers retry', async ({ page }) => {
    await page.goto('/explore');
    await expect(page.locator('[data-wheel-next]')).toBeEnabled();
    let fail = true;
    await page.route('**/explore?**', route => {
        if (route.request().headers()['x-explore-fragment'] === '1' && fail) return route.fulfill({ status: 500, body: 'Unavailable' });
        return route.continue();
    });
    await page.locator('[data-wheel-next]').click();
    await expect(page.locator('[data-discovery-error]')).toBeVisible();
    await expect(page.locator('.projection h2')).toHaveText('Semua rasa');
    await expect(page).toHaveURL('/explore');
    fail = false;
    await page.locator('[data-discovery-retry]').click();
    await expect(page.locator('[data-discovery-error]')).toBeHidden();
    await expect(page.locator('[data-wheel-next]')).toBeEnabled();
});
