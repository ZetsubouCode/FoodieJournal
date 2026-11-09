import { chromium, expect } from '@playwright/test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import { createHash } from 'node:crypto';
import { gzipSync } from 'node:zlib';

const phase = process.argv[2];
assert(['before', 'after'].includes(phase));
const output = 'docs/qa/p1-physical-response';
const browser = await chromium.launch();
const framesOnly = process.argv.includes('--frames-only');
const report = framesOnly ? JSON.parse(fs.readFileSync(`${output}/${phase}.json`)) : { phase, browser: browser.version(), buildSha256: createHash('sha256').update(fs.readFileSync('public/build/manifest.json')).digest('hex'), samples: [], frames: [], errors: [] };
assert.equal(report.buildSha256, createHash('sha256').update(fs.readFileSync('public/build/manifest.json')).digest('hex'));
report.frames = [];
async function ready(page) {
    const response = await page.goto('http://127.0.0.1:8015/');
    assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
    await page.evaluate(async () => { await document.fonts.ready; await Promise.all([...document.images].filter(n => n.src).map(n => { n.loading = 'eager'; return n.decode(); })); });
    await page.locator('[data-lamp-socket]').scrollIntoViewIfNeeded();
}
for (const width of [1440, 390]) {
    for (let sample = 0; sample < (framesOnly ? 0 : 3); sample++) {
        const context = await browser.newContext({ viewport: { width, height: 1000 }, reducedMotion: 'no-preference', locale: 'id-ID', timezoneId: 'Asia/Jakarta' });
        const page = await context.newPage(); page.on('pageerror', e => report.errors.push(e.message));
        await ready(page);
        const client = await context.newCDPSession(page), events = [];
        client.on('Tracing.dataCollected', data => events.push(...data.value));
        await page.locator('[data-vending-lamp]').click();
        await page.evaluate(() => {
            window.p1Frames = []; window.p1Shifts = [];
            window.p1Observer = new PerformanceObserver(list => window.p1Shifts.push(...list.getEntries().map(e => ({ value: e.value, input: e.hadRecentInput }))));
            window.p1Observer.observe({ type: 'layout-shift' });
            window.p1Sampling = true;
            function tick(t) { window.p1Frames.push(t); if (window.p1Sampling) requestAnimationFrame(tick); }
            requestAnimationFrame(tick);
        });
        await client.send('Tracing.start', { categories: 'devtools.timeline,blink.user_timing', transferMode: 'ReportEvents' });
        await page.locator('[data-lamp-socket]').click();
        await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'on');
        const measured = await page.evaluate(() => { window.p1Sampling = false; window.p1Observer.disconnect(); return { frames: window.p1Frames, shifts: window.p1Shifts }; });
        const ended = new Promise(resolve => client.once('Tracing.tracingComplete', resolve));
        await client.send('Tracing.end'); await ended;
        const gaps = measured.frames.slice(1).map((t, i) => t - measured.frames[i]).sort((a, b) => a - b);
        const names = ['Paint', 'Layout', 'UpdateLayoutTree', 'CompositeLayers'];
        const trace = Object.fromEntries(names.map(name => { const found = events.filter(e => e.name === name && e.ph === 'X'); return [name, { count: found.length, ms: found.reduce((sum, e) => sum + (e.dur ?? 0), 0) / 1000 }]; }));
        const filename = `${phase}-${width}-trace-${sample + 1}.json.gz`;
        fs.writeFileSync(`${output}/${filename}`, gzipSync(JSON.stringify({ traceEvents: events })));
        report.samples.push({ width, sample: sample + 1, traceFile: filename, frameCount: measured.frames.length, medianGap: gaps[Math.floor(gaps.length / 2)], p95Gap: gaps[Math.floor(gaps.length * .95)], maxGap: gaps.at(-1), shifts: measured.shifts, trace });
        await context.close();
    }
    // Fit the entire compact component so element screenshots cannot resize/cancel an armed lamp.
    const context = await browser.newContext({ viewport: { width, height: width === 390 ? 1800 : 1000 }, reducedMotion: 'no-preference', locale: 'id-ID', timezoneId: 'Asia/Jakarta' });
    const page = await context.newPage();
    // Pose only the two local WAAPI effects; real-time samples above remain unmodified.
    await page.addInitScript(() => {
        window.p1Animations = [];
        const animate = Element.prototype.animate;
        Element.prototype.animate = function (frames, timing) {
            const a = animate.call(this, frames, timing);
            if (this.matches('[data-vending-lamp], [data-lamp-socket] > img')) { a.pause(); window.p1Animations.push(a); }
            return a;
        };
    });
    await ready(page); await page.locator('[data-vending-lamp]').click();
    await page.locator('.dispenser-scene__machine').screenshot({ path: `${output}/${phase}-${width}-lift.png`, animations: 'allow' });
    await page.locator('[data-lamp-socket]').click(); await page.mouse.move(0, 0);
    await expect(page.locator('[data-machine]')).toHaveAttribute('data-lamp', 'animating');
    assert.equal(await page.evaluate(() => window.p1Animations.length), phase === 'after' ? 2 : 1);
    for (const progress of [0, .72, .86, 1]) {
        // Invert the native easing to capture named keyframe progress, not wall-clock percentages.
        await page.evaluate(progress => {
            const bezier = (t, a, b) => 3 * (1 - t) ** 2 * t * a + 3 * (1 - t) * t ** 2 * b + t ** 3;
            let low = 0, high = 1;
            for (let i = 0; i < 40; i++) { const t = (low + high) / 2; if (bezier(t, .72, 1) < progress) low = t; else high = t; }
            const time = bezier((low + high) / 2, .2, .22);
            window.p1Animations.forEach(a => { a.currentTime = a.effect.getTiming().duration * time; });
        }, progress);
        const geometry = await page.locator('[data-machine]').evaluate(n => {
            const rect = selector => { const r = n.querySelector(selector).getBoundingClientRect(); return { x: r.x + r.width / 2, y: r.y + r.height / 2, width: r.width, height: r.height }; };
            return { power: n.dataset.power, lamp: rect('[data-vending-lamp]'), socket: rect('[data-lamp-socket]'), plate: getComputedStyle(n.querySelector('[data-lamp-socket] > img')).transform };
        });
        report.frames.push({ width, progress, ...geometry });
        await page.locator('.dispenser-scene__machine').screenshot({ path: `${output}/${phase}-${width}-${progress}.png`, animations: 'allow' });
    }
    await context.close();
}
assert.equal(report.errors.length, 0);
fs.writeFileSync(`${output}/${phase}.json`, JSON.stringify(report, null, 2) + '\n');
await browser.close();
console.log(`${phase}: ${framesOnly ? 'retained six traces; refreshed' : 'six real-time traces and'} ten posed frames captured.`);
