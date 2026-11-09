import { chromium } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';

const browser = await chromium.launch(), report = [];
for (const width of [1440, 375]) {
    const page = await browser.newPage({ viewport: { width: width === 1440 ? 1440 : 750, height: 1100 } });
    const bytes = fs.readFileSync(`docs/qa/p3-visit/realtime-${width}.webm`);
    await page.setContent(`<video preload="auto" src="data:video/webm;base64,${bytes.toString('base64')}"></video><div id="frames" style="display:grid;grid-template-columns:1fr 1fr;gap:8px"></div>`);
    await page.waitForFunction(() => document.querySelector('video').readyState >= 2);
    const duration = await page.evaluate(async () => {
        const video = document.querySelector('video');
        // Recorded WebM initially reports Infinity until seeking establishes its end.
        if (!Number.isFinite(video.duration)) { video.currentTime = 1e6; await new Promise(resolve => video.addEventListener('seeked', resolve, { once: true })); }
        return video.duration;
    });
    assert.ok(Number.isFinite(duration) && duration > 0);
    await page.evaluate(async duration => {
        const video = document.querySelector('video'); video.hidden = true;
        for (const fraction of [.05, .2, .4, .6, .8, .95]) {
            video.currentTime = duration * fraction; await new Promise(resolve => video.addEventListener('seeked', resolve, { once: true }));
            const canvas = document.createElement('canvas'); canvas.width = video.videoWidth; canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0); canvas.style.width = '100%'; document.querySelector('#frames').append(canvas);
        }
    }, duration);
    await page.locator('#frames').screenshot({ path: `docs/qa/p3-visit/review-video-${width}.jpg`, type: 'jpeg', quality: 82 });
    report.push({ width, duration, sampledFractions: [.05, .2, .4, .6, .8, .95] }); await page.close();
}
fs.writeFileSync('docs/qa/p3-visit/video-review.json', JSON.stringify(report, null, 2)); await browser.close();
