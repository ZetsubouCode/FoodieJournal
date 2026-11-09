import { test, expect } from '@playwright/test';
import fs from 'node:fs';

const fixture = JSON.parse(fs.readFileSync('tools/.runtime/browser-credentials.json', 'utf8'));
const routes = ['/', '/explore', `/visit/${fixture.visitSlug}`, `/place/${fixture.placeSlug}`, '/missing-error-page-preview'];
const shellWidth = width => Math.min(2432, width - 2 * (width <= 480 ? 16 : width < 1024 ? 24 : Math.min(64, Math.max(32, width * .03333))));
async function ready(page, route) {
    await page.goto(route);
    if (!route.includes('missing-error')) await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.evaluate(() => document.fonts.ready);
}

test('public shell aligns header, main and footer at target and boundary widths without scaling cabinet artwork up', async ({ page }) => {
    test.setTimeout(180000);
    await page.emulateMedia({ reducedMotion: 'reduce' });
    for (const width of [320, 375, 390, 768, 959, 960, 1023, 1024, 1440, 1920, 2560, 3440]) {
        await page.setViewportSize({ width, height: 1000 });
        for (const route of routes) {
            await ready(page, route);
            const rects = await page.evaluate(() => {
                const rect = selector => { const r = document.querySelector(selector).getBoundingClientRect(); return { x: r.x, width: r.width }; };
                return { header: rect('.site-header'), main: rect('main'), inner: rect('.site-footer__inner'), footer: rect('.site-footer'), scrollWidth: document.documentElement.scrollWidth };
            });
            expect(rects.scrollWidth, `${route} at ${width}`).toBeLessThanOrEqual(width);
            expect(rects.main.width).toBeCloseTo(shellWidth(width), 0);
            for (const name of ['header', 'inner']) {
                expect(Math.abs(rects[name].width - rects.main.width)).toBeLessThan(1);
                expect(Math.abs(rects[name].x - rects.main.x)).toBeLessThan(1);
            }
            expect(rects.footer.width).toBe(width);
            if (route === '/') {
                const geometry = await page.locator('[data-machine]').evaluate(n => {
                    const b = n.getBoundingClientRect(), lamp = n.querySelector('[data-vending-lamp]').getBoundingClientRect();
                    const reset = n.querySelector('[data-memory-reset]').getBoundingClientRect();
                    return { width: b.width, ratio: b.width / b.height, lampLeft: lamp.left, resetDy: Math.abs(lamp.y + lamp.height / 2 - reset.y - reset.height / 2), lenses: [...n.querySelectorAll('.memory-machine__step i')].map((node, i) => { const r = node.getBoundingClientRect(); return { ratio: r.width / r.height, dx: (r.x + r.width / 2 - b.x) / b.width * 1634 - 1012, dy: (r.y + r.height / 2 - b.y) / b.height * 963 - [267,405,546][i] }; }) };
                });
                expect(geometry.lampLeft).toBeGreaterThanOrEqual(0);
                expect(geometry.resetDy).toBeLessThan(2);
                if (width >= 960) {
                    expect(geometry.width).toBeLessThanOrEqual(1634);
                    expect(geometry.ratio).toBeCloseTo(1634 / 963, 2);
                    for (const lens of geometry.lenses) {
                        expect(lens.ratio).toBeCloseTo(1, 2); expect(Math.abs(lens.dx)).toBeLessThan(1); expect(Math.abs(lens.dy)).toBeLessThan(1);
                    }
                }
            }
        }
    }
});

test('reading measure, enlarged text and short landscape remain usable across public surfaces', async ({ page }) => {
    test.setTimeout(90000);
    for (const viewport of [{width:375,height:812},{width:1440,height:900},{width:2560,height:1000},{width:812,height:375}]) {
        await page.setViewportSize(viewport);
        for (const route of routes) {
            await ready(page, route);
            await page.evaluate(() => document.documentElement.style.fontSize = '200%');
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), route).toBe(true);
            for (const link of await page.locator('.site-footer__nav a').all()) {
                const rect = await link.boundingBox();
                expect(rect.x).toBeGreaterThanOrEqual(0); expect(rect.x + rect.width).toBeLessThanOrEqual(viewport.width + 1); expect(rect.height).toBeGreaterThanOrEqual(44);
            }
        }
    }
    await page.setViewportSize({ width: 1920, height: 1000 }); await ready(page, `/visit/${fixture.visitSlug}`);
    const prose = page.locator('.dossier-story__prose');
    expect(await prose.evaluate(n => { const s=getComputedStyle(n); const measure=document.createElement('span');measure.style.cssText=`position:absolute;width:68ch;font:${s.font};`;n.append(measure);const max=measure.getBoundingClientRect().width;measure.remove();return n.getBoundingClientRect().width <= max + 1; })).toBe(true);
});

test('admin keeps its existing narrow container and compact footer', async ({ page }) => {
    for (const width of [375, 1440, 1920]) {
        await page.setViewportSize({ width, height: 900 }); await ready(page, '/admin/login');
        await expect(page.locator('body')).toHaveClass(/public-page--admin/);
        const rect = await page.locator('main').boundingBox();
        expect(rect.width).toBe(Math.min(width - (width <= 480 ? 32 : 48), 1280));
        expect(await page.locator('.site-footer__statement br').evaluate(n => getComputedStyle(n).display)).toBe('none');
    }
});

test('wide images use derivatives with layout-aware lazy sizes and an eager capped hero', async ({ page }) => {
    let originals = 0; page.on('request', r => { if (r.url().endsWith('/original')) originals++; });
    await page.setViewportSize({ width: 1920, height: 1000 }); await ready(page, `/visit/${fixture.visitSlug}`);
    const hero = page.locator('.dossier-hero img');
    await expect(hero).toHaveAttribute('loading', 'eager'); await expect(hero).toHaveAttribute('sizes', /2432px/);
    await expect(hero).toHaveJSProperty('complete', true);
    expect(await hero.evaluate(n => n.currentSrc.endsWith('/hero'))).toBe(true);
    const image = page.locator('.dossier-gallery img').first();
    await image.scrollIntoViewIfNeeded(); await expect(image).toHaveJSProperty('complete', true);
    await expect(image).toHaveAttribute('sizes', /^auto, /);
    expect(await image.evaluate(n => n.width > 0 && n.height > 0 && n.srcset.split(',').some(candidate => candidate.trim().split(' ')[0] === n.currentSrc))).toBe(true);
    expect(originals).toBe(0);
});
