import { test, expect } from '@playwright/test';
async function ready(page, url = '/') {
    await page.goto(url);
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
}

test('Explore modes retain the document, query, layout and browser history', async ({ page }) => {
    await ready(page, '/explore?city=semarang&view=list');
    await page.evaluate(() => { window.publicHeader = document.querySelector('.site-header'); window.indexHero = document.querySelector('.curiosity-index'); });
    for (const [label, mode] of [['Hidangan', 'food'], ['Tempat', 'place'], ['Kunjungan', 'visit']]) {
        await page.getByRole('link', { name: label, exact: true }).click();
        await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', mode);
        await expect(page).toHaveURL(/city/); await expect(page).toHaveURL(/view=list/);
        expect(await page.evaluate(() => window.publicHeader === document.querySelector('.site-header') && window.indexHero === document.querySelector('.curiosity-index'))).toBe(true);
    }
    await page.goBack(); await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', 'place');
    await expect(page.locator('[data-machine]')).toHaveCount(0);
});

test('rapid requests discard stale modes; failed requests retain committed data', async ({ page }) => {
    await ready(page, '/explore');
    await page.route('**/explore?**', async route => {
        if (route.request().url().includes('tab=food')) await new Promise(resolve => setTimeout(resolve, 500));
        await route.continue();
    });
    await page.getByRole('link', { name: 'Hidangan', exact: true }).click();
    await page.getByRole('link', { name: 'Tempat', exact: true }).click();
    await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', 'place');
    await page.waitForTimeout(650);
    await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', 'place');
});

test('curiosity photographs apply actual filtering and restore with Back', async ({ page }) => {
    await ready(page, '/explore');
    const url = await page.locator('.curiosity-surface--style').getAttribute('href');
    await page.locator('.curiosity-surface--style').click();
    await expect(page).toHaveURL(url);
    await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', 'food');
    await expect(page.locator('.discovery-chips')).toContainText('Rebus QA');
    await page.goBack(); await expect(page.locator('[data-discovery]')).toHaveAttribute('data-mode', 'visit');
});
test('filter drawer stages hierarchy, long searchable multiselect, tags and recommendations; apply and cancel', async ({ page }) => {
    await ready(page, '/explore');
    await page.getByRole('button', { name: /^Filter/ }).click();
    const drawer = page.getByRole('dialog', { name: 'Filter arsip' });
    await expect(drawer).toBeVisible();
    await drawer.locator('[data-filter-group-open=food]').click();
    await expect(drawer.locator('.archive-choice--depth-1')).toContainText('Makanan QA');
    await drawer.getByLabel('Kelompok arsip QA', { exact: true }).check();
    await drawer.locator('[data-filter-group-open=style]').click();
    const search = drawer.locator('[data-filter-search]');
    await search.fill('arsip 01');
    await expect(drawer.locator('[data-filter-option]:visible')).toHaveCount(1);
    await drawer.getByLabel('Teknik arsip 01', { exact: true }).check();
    await search.fill('arsip 02'); await drawer.getByLabel('Teknik arsip 02', { exact: true }).check();
    await drawer.locator('[data-filter-group-open=tag]').click(); await drawer.getByLabel('Tag arsip QA', { exact: true }).check();
    await drawer.locator('[data-filter-group-open=recommendation]').click(); await drawer.getByLabel('Ingin kembali QA', { exact: true }).check();
    await expect(page).toHaveURL('/explore');
    await expect(drawer.locator('[data-filter-selected] button')).toHaveCount(5);
    await drawer.getByRole('button', { name: 'Batal', exact: true }).click();
    await page.getByRole('button', { name: /^Filter/ }).click();
    await expect(drawer.getByLabel('Ingin kembali QA', { exact: true })).not.toBeChecked();
    await drawer.getByLabel('Ingin kembali QA', { exact: true }).check();
    await drawer.locator('[data-filter-group-reset=recommendation]').click();
    await expect(drawer.getByLabel('Ingin kembali QA', { exact: true })).not.toBeChecked();
    await drawer.locator('[data-filter-group-open=style]').click();
    await drawer.locator('[data-filter-search]').fill('');
    await drawer.getByLabel('Teknik arsip 01', { exact: true }).check();
    await drawer.getByLabel('Teknik arsip 02', { exact: true }).check();
    await drawer.locator('[data-filter-apply]').click();
    await expect(page).toHaveURL(/style/);
    expect(await page.evaluate(async () => {
        const response = await fetch('/explore/count' + location.search);
        return (await response.json()).total === Number(document.querySelector('.memory-reel__head > p').textContent.trim().split(' ')[0]);
    })).toBe(true);
});

test('mode-specific prices, group reset, search/sort/grid/list/pagination and error retry remain coherent', async ({ page }) => {
    test.setTimeout(90000);
    await ready(page, '/explore');
    await page.locator('#discovery-search').fill('QA'); await page.getByRole('button', { name: 'Cari', exact: true }).click();
    await expect(page).toHaveURL(/q=QA/);
    await page.locator('#discovery-sort').selectOption('oldest'); await expect(page).toHaveURL(/sort=oldest/);
    await page.getByRole('link', { name: 'Daftar', exact: true }).click(); await expect(page.locator('.results')).toHaveClass(/results--list/);
    await page.getByRole('button', { name: /^Filter/ }).click(); await page.locator('[data-filter-group-open=rating]').click();
    await page.getByLabel('Harga minimum (Rp)', { exact: true }).fill('1000');
    await page.locator('[data-filter-apply]').click(); await expect(page).toHaveURL(/price_min=1000/);
    await page.getByRole('link', { name: 'Hidangan', exact: true }).click();
    await expect(page).not.toHaveURL(/price_min=/);
    await page.getByRole('link', { name: 'Tempat', exact: true }).click();
    await page.getByRole('button', { name: /^Filter/ }).click(); await page.locator('[data-filter-group-open=rating]').click();
    await expect(page.locator('[name=price_min]')).toHaveCount(0);
    await page.keyboard.press('Escape');
    await ready(page, '/explore');
    let fail = true;
    await page.route('**/explore?**', route => fail ? route.fulfill({ status: 503, body: 'Test unavailable' }) : route.continue());
    await page.getByRole('link', { name: 'Hidangan', exact: true }).click();
    await expect(page).toHaveURL('/explore'); await expect(page.locator('[data-discovery-error]')).toBeVisible();
    fail = false; await page.locator('[data-discovery-retry]').click();
    await expect(page).toHaveURL(/tab=food/);
});


test('calendar and location metadata are shared by Home and all Explore card modes', async ({ page }) => {
    await ready(page);
    await expect(page.locator('.discovery-intro > p')).toHaveCount(0);
    await expect(page.getByText('Jeda cahaya', {exact:true})).toHaveCount(0);
    await expect(page.locator('[data-archive-explore]')).toHaveCount(0);
    await expect(page.locator('[data-machine-status]')).toHaveClass(/sr-only/);
    await expect(page.locator('.archive-entry .record-meta').first().locator('svg')).toHaveCount(2);
    for(const tab of ['visit','food','place']) {
        await ready(page, '/explore?tab='+tab+'&food_type=qa-mie-ayam');
        const card=page.locator(tab==='visit'?'.visit-card':'.journal-card').first();
        await expect(card.locator('time .meta-icon')).toHaveCount(1);
        await expect(card.locator('.record-meta > span .meta-icon')).toHaveCount(1);
    }
});
