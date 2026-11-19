import { chromium, expect } from '@playwright/test';
import { build } from 'esbuild';
import fs from 'node:fs';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
import { execFileSync } from 'node:child_process';
import { gzipSync } from 'node:zlib';

const output = 'docs/qa/p4-integrated', origin = 'http://127.0.0.1:8015';
const mode = process.argv[2]; assert.ok(['navigation', 'interactions', 'place'].includes(mode));
assert.ok(!fs.existsSync(`${output}/${mode}.json`), 'Archive previous measurements explicitly; never overwrite evidence.');
const browser = await chromium.launch();
const report = { browser: browser.version(), build: createHash('sha256').update(fs.readFileSync('public/build/manifest.json')).digest('hex'),
    network: 'Unthrottled loopback, fixed photo-v1 SQLite/fake media; fresh context cold, second same-context navigation warm', samples: [] };
const routes = { home: '/', explore: '/explore', visit: '/visit/demo-bara-di-ujung-hari', place: '/place/demo-bara-senja' };
async function contextFor(width, rate, motion = 'reduce') {
    const context = await browser.newContext({ viewport: { width, height: 1000 }, locale: 'id-ID', timezoneId: 'Asia/Jakarta', reducedMotion: motion });
    const page = await context.newPage(), cdp = await context.newCDPSession(page);
    await cdp.send('Emulation.setCPUThrottlingRate', { rate });
    await page.addInitScript(() => {
        window.metrics = { lcp: 0, cls: 0, shifts: [], lcpElement: null, longTasks: [] };
        new PerformanceObserver(list => { for (const e of list.getEntries()) { window.metrics.lcp = e.startTime; window.metrics.lcpElement = { tag: e.element?.tagName, class: e.element?.className, url: e.url, renderTime: e.renderTime, loadTime: e.loadTime }; } }).observe({ type: 'largest-contentful-paint', buffered: true });
        new PerformanceObserver(list => { for (const e of list.getEntries()) { if (!e.hadRecentInput) window.metrics.cls += e.value; window.metrics.shifts.push({ value: e.value, recentInput: e.hadRecentInput, sources: e.sources.map(s => ({ tag: s.node?.tagName, class: s.node?.className, previous: s.previousRect.toJSON(), current: s.currentRect.toJSON() })) }); } }).observe({ type: 'layout-shift', buffered: true });
        new PerformanceObserver(list => { window.metrics.longTasks.push(...list.getEntries().map(e => ({ start: e.startTime, duration: e.duration }))); }).observe({ type: 'longtask', buffered: true });
    });
    return { context, page, cdp };
}
async function ready(page, route) {
    const response = await page.goto(origin + route); assert.equal(response.headers()['x-foodie-visual-fixture'], 'photo-v1');
    await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true'); await page.evaluate(() => document.fonts.ready);
}
async function arrival(page) {
    // Wait only for assigned above-fold images; retain native loading/sizes and lazy scheduling.
    await page.evaluate(async () => { await Promise.all([...document.images].filter(n => n.src && n.getBoundingClientRect().top < innerHeight && n.getBoundingClientRect().bottom > 0).map(n => n.decode().catch(() => {}))); });
    await page.waitForTimeout(1000);
    return page.evaluate(() => ({ ...window.metrics, navigation: { ttfb: performance.getEntriesByType('navigation')[0].responseStart, duration: performance.getEntriesByType('navigation')[0].duration },
        media: performance.getEntriesByType('resource').filter(e => e.name.includes('/media/')).map(e => ({ url: e.name, bytes: e.encodedBodySize, transfer: e.transferSize, responseEnd: e.responseEnd })),
        imageResources: performance.getEntriesByType('resource').filter(e => e.initiatorType === 'img' || /\.(webp|png|jpe?g|svg)(\?|$)/i.test(e.name) || e.name.includes('/media/')).map(e => ({ url: e.name, bytes: e.encodedBodySize, responseEnd: e.responseEnd })),
        images: [...document.images].filter(n => n.getBoundingClientRect().top < innerHeight && n.getBoundingClientRect().bottom > 0).map(n => ({ src: n.currentSrc, loading: n.loading, sizes: n.sizes })) }));
}
if (mode === 'navigation') {
    for (const [name, route] of Object.entries(routes)) for (const width of [390, 1440]) for (let run = 0; run < 3; run++) {
        const { context, page } = await contextFor(width, 1, name === 'explore' ? 'no-preference' : 'reduce');
        for (const cache of ['cold', 'warm']) {
            await ready(page, route); const metrics = await arrival(page);
            assert.ok(metrics.media.every(m => !m.url.endsWith('/original')));
            report.samples.push({ name, width, run, cache, cpuRate: 1, ...metrics });
        }
        await context.close();
    }
}
if (mode === 'interactions') {
    const clients = {};
    for (const name of ['archive', 'explore']) for (const version of ['old', 'current']) {
        const contents = version === 'old' ? execFileSync('git', ['show', `21e443a7:resources/js/pages/${name}.js`], { encoding: 'utf8' }) : fs.readFileSync(`resources/js/pages/${name}.js`, 'utf8');
        const built = await build({ stdin: { contents, resolveDir: `${process.cwd()}/resources/js/pages` }, bundle: true, format: 'esm', write: false });
        clients[`${name}-${version}`] = built.outputFiles[0].text;
    }
    report.method = 'Alternating paired old/current client diagnostics on identical final markup/CSS/server; both compiled by installed esbuild. Old source 21e443a7. 4x CDP CPU slowdown is synthetic, not physical hardware.';
    for (const name of ['archive', 'explore']) for (const width of [390, 1440]) for (const cpuRate of [1, 4]) for (let run = 0; run < 3; run++) for (const version of (run % 2 ? ['current', 'old'] : ['old', 'current'])) {
        const { context, page, cdp } = await contextFor(width, cpuRate, 'no-preference');
        await page.route(`**/build/assets/${name}-*.js`, r => r.fulfill({ body: clients[`${name}-${version}`], contentType: 'text/javascript' }));
        const arrivals = [];
        if (name === 'explore') for (const cache of ['cold', 'warm']) {
            await ready(page, '/explore'); arrivals.push({ cache, ...await arrival(page) });
        } else await ready(page, '/');
        assert.ok(arrivals.every(a => a.media.every(m => !m.url.endsWith('/original'))));
        await page.evaluate(async () => { await Promise.all([...document.images].filter(n => n.src).map(n => { n.loading = 'eager'; return n.decode().catch(() => {}); })); });
        const control = page.locator(name === 'archive' ? '[data-lamp-socket]' : '.curiosity-surface--style');
        await control.scrollIntoViewIfNeeded();
        if (name === 'archive') await page.locator('[data-vending-lamp]').click();
        await page.evaluate(() => {
            window.frames = []; window.metrics.shifts = []; window.sampling = true; window.interactionStart = performance.now();
            window.interactionEntries = [];
            new PerformanceObserver(list => window.interactionEntries.push(...list.getEntries().map(e => ({ name: e.name, duration: e.duration, inputDelay: e.processingStart - e.startTime, interactionId: e.interactionId })))).observe({ type: 'event', durationThreshold: 16 });
            function frame(t) { window.frames.push(t); if (window.sampling) requestAnimationFrame(frame); } requestAnimationFrame(frame);
        });
        const events = []; cdp.on('Tracing.dataCollected', data => events.push(...data.value));
        await cdp.send('Tracing.start', { categories: 'devtools.timeline,blink.user_timing', transferMode: 'ReportEvents' });
        await control.click();
        if (name === 'archive') await expect(page.locator('[data-machine]')).toHaveAttribute('data-power', 'on');
        else { await expect(page.locator('[data-discovery-results]')).toHaveAttribute('aria-busy', 'false'); await expect(page).toHaveURL(await control.getAttribute('href')); await page.waitForTimeout(250); }
        const metrics = await page.evaluate(() => { window.sampling = false; return { frames: window.frames, events: window.interactionEntries, elapsed: performance.now() - window.interactionStart, shifts: window.metrics.shifts }; });
        const ended = new Promise(resolve => cdp.once('Tracing.tracingComplete', resolve)); await cdp.send('Tracing.end'); await ended;
        const file = `${name}-${version}-${width}-${cpuRate}-${run}.json.gz`; fs.writeFileSync(`${output}/${file}`, gzipSync(JSON.stringify({ traceEvents: events })));
        const gaps = metrics.frames.slice(1).map((t, i) => t - metrics.frames[i]).sort((a, b) => a - b);
        assert.ok(gaps.length && events.length, 'Require real frame/trace evidence.');
        const trace = Object.fromEntries(['Paint', 'Layout', 'UpdateLayoutTree'].map(name => { const found = events.filter(e => e.name === name && e.ph === 'X'); return [name, { count: found.length, ms: found.reduce((sum, e) => sum + (e.dur || 0), 0) / 1000 }]; }));
        report.samples.push({ name, width, cpuRate, run, version, arrivals, traceFile: file, medianGap: gaps[Math.floor(gaps.length / 2)], p95Gap: gaps[Math.floor(gaps.length * .95)], maxGap: gaps.at(-1), trace, ...metrics });
        await context.close();
    }
}
if (mode === 'place') {
    report.method = 'Alternating same-response client presentation diagnostic, not historical before/after. Both variants receive identical current server response/query work; old uses the prior ungrouped card structure. CPU 4x is synthetic.';
    const parser = await browser.newPage();
    for (const width of [390, 1440]) for (const cpuRate of [1, 4]) for (let run = 0; run < 3; run++) for (const version of (run % 2 ? ['current', 'old'] : ['old', 'current'])) {
        const { context, page } = await contextFor(width, cpuRate);
        await page.route(origin + routes.place, async route => {
            const response = await route.fetch(), html = await response.text();
            const body = await parser.evaluate(({ html, version }) => {
                const document = new DOMParser().parseFromString(html, 'text/html');
                const history = document.querySelector('.place-history');
                if (version === 'old') {
                    const cards = [...history.querySelectorAll('.visit-card')], group = history.querySelector('.place-history__group');
                    const list = document.createElement('div'); list.className = 'results results--visit results--list'; list.append(...cards); group.before(list);
                    history.querySelectorAll('.place-history__group').forEach(n => n.remove()); history.removeAttribute('class'); history.className = 'section'; history.removeAttribute('aria-labelledby');
                }
                return '<!doctype html>' + document.documentElement.outerHTML;
            }, { html, version });
            await route.fulfill({ response, body });
        });
        for (const cache of ['cold', 'warm']) {
            await ready(page, routes.place); const metrics = await arrival(page);
            assert.ok(metrics.media.every(m => !m.url.endsWith('/original')));
            report.samples.push({ name: 'place', width, cpuRate, run, version, cache, ...metrics });
        }
        await context.close();
    }
    await parser.close();
}
assert.ok(report.samples.length > 0);
fs.writeFileSync(`${output}/${mode}.json`, JSON.stringify(report, null, 2));
await browser.close(); console.log(`${mode}: ${report.samples.length} measured observations; inspect raw ranges and comparison limits.`);
