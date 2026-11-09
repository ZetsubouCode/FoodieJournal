import { test, expect } from '@playwright/test';

async function ready(page) {
    await page.emulateMedia({ reducedMotion: 'no-preference' });
    await page.addInitScript(() => {
        if (window.seating) return;
        window.seating = [];
        const animate = Element.prototype.animate;
        Element.prototype.animate = function (frames, timing) {
            const a = animate.call(this, frames, timing);
            if (this.matches('[data-vending-lamp], [data-lamp-socket] > img')) { a.pause(); window.seating.push(a); }
            return a;
        };
    });
    await page.goto('/');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
}

for (const width of [1440, 390]) {
    test(`weighted seating follows contact without moving anchors at ${width}`, async ({ page }) => {
        await page.setViewportSize({ width, height: 1000 }); await ready(page);
        const lamp = page.locator('[data-vending-lamp]'), socket = page.locator('[data-lamp-socket]');
        await lamp.scrollIntoViewIfNeeded();
        const original = await socket.boundingBox();
        await lamp.evaluate(n => n.addEventListener('pointerdown', () => requestAnimationFrame(() => {
            window.contact = { cursor: getComputedStyle(n).cursor, filter: getComputedStyle(n).filter, power: n.closest('[data-machine]').dataset.power };
        }), { once: true }));
        await lamp.click();
        expect(await page.evaluate(() => window.contact)).toMatchObject({ cursor: 'grabbing', power: 'off' });
        expect(await page.evaluate(() => window.contact.filter)).toContain('6px 10px 5px');
        await socket.click();
        await expect(page.locator('[data-machine]')).toHaveAttribute('data-lamp', 'animating');
        expect(await page.evaluate(() => window.seating.map(a => a.effect.getTiming().duration))).toEqual([270, 270]);
        for (const [progress, dy, scaleX, scaleY] of [[.72, -4, 1, 1], [.86, 0, 1.035, .985], [1, 0, 1, 1]]) {
            const measured = await page.evaluate(progress => {
                // Linear posing isolates authored keyframe geometry from native easing.
                window.seating.forEach(a => { a.effect.updateTiming({ easing: 'linear' }); a.currentTime = 270 * progress; });
                const a = document.querySelector('[data-vending-lamp]').getBoundingClientRect(), b = document.querySelector('[data-lamp-socket]').getBoundingClientRect();
                const m = new DOMMatrix(getComputedStyle(document.querySelector('[data-lamp-socket] > img')).transform);
                return { dx: a.x + a.width / 2 - b.x - b.width / 2, dy: a.y + a.height / 2 - b.y - b.height / 2, scaleX: m.a, scaleY: m.d };
            }, progress);
            // offsetWidth/Height round the source-sized tube to integers; keep subpixel anchor tolerance.
            expect(Math.abs(measured.dx)).toBeLessThan(.5); expect(Math.abs(measured.dy - dy)).toBeLessThan(.5);
            expect(measured.scaleX).toBeCloseTo(scaleX, 3); expect(measured.scaleY).toBeCloseTo(scaleY, 3);
            expect(await socket.boundingBox()).toEqual(original);
            await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'off');
            await expect(page.locator('[data-vending-print]')).toBeDisabled();
        }
        await page.evaluate(() => window.seating.forEach(a => a.finish()));
        await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'on');
        await expect(page.locator('[data-vending-print]')).toBeEnabled();
    });
}

test('seating interruptions finish both local effects and retain exact print/off semantics', async ({ page }) => {
    for (const mode of ['hidden', 'resize', 'reduced']) {
        await ready(page);
        await page.locator('[data-vending-lamp]').click(); await page.locator('[data-lamp-socket]').click();
        await page.evaluate(() => window.seating.forEach(a => { a.currentTime = 120; }));
        if (mode === 'reduced') await page.emulateMedia({ reducedMotion: 'reduce' });
        else if (mode === 'resize') await page.setViewportSize({ width: 768, height: 900 });
        else await page.evaluate(() => { Object.defineProperty(document, 'hidden', { value: true, configurable: true }); document.dispatchEvent(new Event('visibilitychange')); delete document.hidden; });
        await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'on');
        expect(await page.evaluate(() => window.seating.every(a => a.playState === 'finished'))).toBe(true);
        const alignment = await page.locator('[data-vending-lamp]').evaluate(n => { const a = n.getBoundingClientRect(), b = document.querySelector('[data-lamp-socket]').getBoundingClientRect(); return Math.hypot(a.x + a.width / 2 - b.x - b.width / 2, a.y + a.height / 2 - b.y - b.height / 2); });
        expect(alignment).toBeLessThan(1);
        const slot = page.locator('[data-memory-slot]').nth(1); await slot.click();
        await page.locator('[data-vending-print]').click();
        await expect(page.locator('[data-machine]')).toHaveAttribute('data-dispenser', 'ready');
        await expect(page.locator('[data-paper-path] a')).toHaveAttribute('data-printed-visit', await slot.getAttribute('data-memory-slot'));
        // Disable posing for the unchanged reverse path.
        await page.emulateMedia({ reducedMotion: 'reduce' });
        await page.locator('[data-vending-lamp]').click(); await page.locator('[data-lamp-cradle]').click();
        await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'off');
        await expect(page.locator('[data-paper-path] a')).toHaveCount(0);
        await expect(page.locator('[data-vending-print]')).toBeDisabled();
    }
});

test('reduced load and missing WAAPI keep immediate seating and cancel a pending page on power-off', async ({ page }) => {
    for (const mode of ['reduce', 'no-waapi']) {
        await page.emulateMedia({ reducedMotion: mode === 'reduce' ? 'reduce' : 'no-preference' });
        if (mode === 'no-waapi') await page.addInitScript(() => { Element.prototype.animate = undefined; });
        await page.goto('/'); await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
        await page.locator('[data-vending-lamp]').click(); await page.locator('[data-lamp-socket]').click();
        await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'on');
        expect(await page.locator('[data-vending-lamp]').evaluate(n => n.getAnimations().length)).toBe(0);
        if (mode === 'no-waapi') {
            await page.locator('[data-vending-lamp]').click(); await page.locator('[data-lamp-cradle]').click();
            await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'off');
            continue;
        }
        const selected = await page.locator('[data-machine]').getAttribute('data-selected-visit');
        let release;
        const held = new Promise(resolve => { release = resolve; });
        await page.route('**/*machine_page=*', async route => { await held; await route.continue().catch(() => {}); });
        await page.locator('[data-vending-knob]').focus(); await page.keyboard.press('ArrowRight');
        await expect(page.locator('[data-machine]')).toHaveAttribute('data-page-busy', 'true');
        await page.locator('[data-vending-lamp]').click(); await page.locator('[data-lamp-cradle]').click();
        await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'off');
        release(); await page.unroute('**/*machine_page=*');
        await expect(page.locator('[data-machine]')).toHaveAttribute('data-page-busy', 'false');
        await expect(page.locator('[data-machine]')).toHaveAttribute('data-page', '1');
        await expect(page.locator('[data-machine]')).toHaveAttribute('data-selected-visit', selected);
        await expect(page.locator('[data-paper-path] a')).toHaveCount(0);
    }
});

test('200% root text preserves seated geometry and keyboard print identity', async ({ page }) => {
    await page.addInitScript(() => sessionStorage.setItem('foodie.vending.power', 'off'));
    for (const width of [390, 1440]) {
        await page.setViewportSize({ width, height: 1000 }); await page.emulateMedia({ reducedMotion: 'reduce' });
        await page.goto('/'); await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
        await page.evaluate(() => { document.documentElement.style.fontSize = '200%'; });
        await page.locator('[data-vending-lamp]').focus(); await page.keyboard.press('Enter');
        await expect(page.locator('[data-lamp-socket]')).toBeFocused(); await page.keyboard.press('Enter');
        await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'on');
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        await page.locator('[data-vending-print]').focus(); await page.keyboard.press('Enter');
        await expect(page.locator('[data-machine]')).toHaveAttribute('data-dispenser', 'ready');
        await expect(page.locator('[data-paper-path] a')).toHaveAttribute('data-printed-visit', await page.locator('[data-machine]').getAttribute('data-selected-visit'));
    }
});
