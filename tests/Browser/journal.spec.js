import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import fs from 'node:fs';

const fixture = JSON.parse(fs.readFileSync('tools/.runtime/browser-credentials.json', 'utf8'));
async function login(page) {
    await page.goto('/admin/login');
    await page.getByRole('textbox', { name: 'Email', exact: true }).fill(fixture.email);
    await page.getByRole('textbox', { name: 'Kata sandi', exact: true }).fill(fixture.password);
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await expect(page).toHaveURL(/\/admin$/);
}
async function noOverflow(page) {
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth)).toBe(true);
}
async function loadNavigationImages(page) {
    for (const image of await page.locator('img[src]').all()) {
        await image.scrollIntoViewIfNeeded();
        await expect(image).toHaveJSProperty('complete', true);
        await expect(image).not.toHaveJSProperty('naturalWidth', 0);
    }
    await page.evaluate(() => window.scrollTo(0, 0));
}
async function noAxeViolations(page) {
    const result = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
    expect(result.violations.map(v => ({ id: v.id, impact: v.impact, nodes: v.nodes.map(n => n.target) }))).toEqual([]);
}

test('public pages reflow at 320, 375, 768, 1024 and 1440 pixels', async ({ page }) => {
    test.setTimeout(120000);
    for (const width of [320, 375, 768, 1024, 1440]) {
        await page.setViewportSize({ width, height: 900 });
        for (const route of ['/', '/explore', `/visit/${fixture.visitSlug}`, `/place/${fixture.placeSlug}`]) {
            await page.goto(route); await expect(page.locator('h1')).toBeVisible(); await noOverflow(page);
        }
        await page.goto('/explore'); await loadNavigationImages(page); await page.screenshot({ path: `test-results/explore-${width}.png`, fullPage: true });
    }
    await page.goto('/'); await loadNavigationImages(page); await page.screenshot({ path: 'test-results/home-desktop.png', fullPage: true });
    await noAxeViolations(page);
});

test('filters stage, count, cancel, apply and restore browser history', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 }); await page.goto('/explore');
    const filter = page.getByRole('button', { name: /^Filter/ }); await filter.click();
    const dialog = page.getByRole('dialog', { name: 'Filter pengalaman' }); await expect(dialog).toBeVisible();
    await dialog.locator('summary').filter({ hasText: /^Kota$/ }).click();
    await dialog.getByLabel('Semarang', { exact: true }).check();
    await expect(page).toHaveURL('/explore');
    await expect(dialog.locator('[data-filter-apply]')).toHaveText(/Tampilkan \d+ hasil/);
    await page.keyboard.press('Escape'); await expect(filter).toBeFocused();
    await filter.click(); await expect(dialog.getByLabel('Semarang', { exact: true })).not.toBeChecked();
    await dialog.getByLabel('Semarang', { exact: true }).check();
    await noAxeViolations(page);
    await dialog.locator('[data-filter-apply]').click(); await expect(page).toHaveURL(/city/);
    await page.getByRole('link', { name: 'Hidangan', exact: true }).click(); await expect(page).toHaveURL(/tab=food/);
    await page.goBack(); await expect(page).toHaveURL(/tab=visit/);
    await page.goForward(); await expect(page).toHaveURL(/tab=food/);
    await noOverflow(page);
});

test('login, keyboard combobox, inline create, repeatable editor and publish preview', async ({ page }) => {
    test.setTimeout(90000); await login(page); await page.goto('/admin/visit/create');
    await page.getByLabel('Judul (opsional)').fill('Catatan browser QA');
    await page.getByRole('link', { name: 'Kembali', exact: true }).click();
    await page.getByRole('dialog', { name: 'Konfirmasi tindakan' }).getByRole('button', { name: 'Batal', exact: true }).click();
    await expect(page.getByLabel('Judul (opsional)')).toHaveValue('Catatan browser QA');
    await page.locator('[data-combo-create]').first().click();
    const inline = page.getByRole('dialog', { name: 'Tambah pilihan' });
    const unique = `Tempat browser QA ${Date.now()}`;
    await inline.getByRole('textbox', { name: 'Nama', exact: true }).fill(unique);
    await inline.getByLabel('Kota (opsional)').fill('Semarang');
    await inline.getByRole('button', { name: 'Simpan & pilih' }).click();
    await expect(inline).not.toBeVisible(); await expect(page.getByLabel('Judul (opsional)')).toHaveValue('Catatan browser QA');
    await expect(page.locator('input[name=place_id]')).toHaveValue(/\d+/);
    await page.getByRole('button', { name: 'Simpan draft', exact: true }).click(); await expect(page).toHaveURL(/\/admin\/visit\/\d+\/edit/);
    await page.getByRole('button', { name: 'Tambah hidangan', exact: true }).click();
    const item = page.locator('[data-items] [data-item]').first(); await item.getByLabel('Nama menu saat dipesan').fill('Mie dari browser QA');
    await item.getByLabel('Harga satuan saat dipesan (Rp)').fill('25000');
    await item.getByLabel('Rating rasa', { exact: true }).selectOption('4.5');
    const cuisine = item.getByRole('combobox', { name: 'Asal/tradisi kuliner' });
    await cuisine.fill('Jawa'); await expect(item.getByRole('option', { name: 'Jawa QA' })).toBeVisible();
    await cuisine.press('ArrowDown'); await cuisine.press('Enter'); await expect(item.locator('input[name$="[cuisines][]"]')).toHaveCount(1);
    await page.getByRole('button', { name: 'Tambah hidangan', exact: true }).click();
    await page.locator('[data-items] [data-item]').nth(1).getByLabel('Nama menu saat dipesan').fill('Teh dari browser QA');
    await page.getByRole('button', { name: 'Simpan perubahan', exact: true }).click(); await expect(page.locator('[data-items] [data-item]')).toHaveCount(2);
    await noAxeViolations(page);
    await page.screenshot({ path: 'test-results/admin-editor-desktop.png', fullPage: true });
    await page.getByRole('button', { name: 'Publikasikan', exact: true }).click();
    const confirm = page.getByRole('dialog', { name: 'Konfirmasi tindakan' }); await expect(confirm).toBeVisible();
    await confirm.getByRole('button', { name: 'Lanjutkan', exact: true }).click(); await expect(page.locator('.badge--published')).toBeVisible();
    await page.getByRole('link', { name: 'Pratinjau tersimpan', exact: true }).click(); await expect(page).toHaveURL(/\/preview$/); await expect(page.getByText('Pratinjau admin', { exact: true })).toBeVisible();
});

test('gallery loads originals only when opened and dialog restores focus', async ({ page }) => {
    let originals = 0; page.on('request', request => { if (request.url().endsWith('/original')) originals++; });
    await page.goto(`/visit/${fixture.visitSlug}`); await expect(page.locator('[data-view-photo]').first()).toBeVisible(); expect(originals).toBe(0);
    const trigger = page.locator('[data-view-photo]').first(); await trigger.click();
    const dialog = page.getByRole('dialog', { name: 'Foto kunjungan', exact: true }); await expect(dialog).toBeVisible();
    await expect(dialog.locator('[data-viewer-image]')).toBeVisible(); expect(originals).toBe(1);
    for (let i = 0; i < 7; i++) {
        await page.keyboard.press('Tab'); expect(await dialog.evaluate(element => element.contains(document.activeElement))).toBe(true);
    }
    await noAxeViolations(page); await page.keyboard.press('Escape'); await expect(trigger).toBeFocused();
});

test('admin mobile, reduced motion, errors and 200 percent text reflow', async ({ page }) => {
    await login(page);
    for (const width of [320, 375, 768, 1024, 1440]) {
        await page.setViewportSize({ width, height: 900 }); await page.goto(`/admin/visit/${fixture.visitId}/edit`); await noOverflow(page);
    }
    await page.setViewportSize({ width: 375, height: 812 }); await page.screenshot({ path: 'test-results/admin-editor-mobile.png', fullPage: true });
    await noAxeViolations(page); await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto('/explore'); await page.evaluate(() => { document.documentElement.style.fontSize = '200%'; }); await noOverflow(page);
    await page.goto('/does-not-exist'); await expect(page.getByRole('heading', { name: 'Catatan tidak ditemukan' })).toBeVisible(); await noAxeViolations(page);
});
