import { chromium } from '@playwright/test';
import fs from 'node:fs';
import { spawnSync } from 'node:child_process';

const browser = await chromium.launch();
for (const [width, scale] of [[1440, 1], [390, 2]]) {
    const page = await browser.newPage({ viewport: { width, height: 1000 }, reducedMotion: 'reduce' });
    await page.goto('http://127.0.0.1:8012/explore');
    await page.evaluate(() => document.fonts.ready);
    await page.evaluate(scale => document.documentElement.style.fontSize = `${scale * 100}%`, scale);
    await page.locator('.curiosity-surface img').evaluateAll(nodes => Promise.all(nodes.map(n => n.decode())));
    const selector = '.curiosity-surface__copy > span:first-child, .curiosity-surface__copy strong, .curiosity-index__intro >p, .visit-card__meta, .visit-card__place';
    const samples = await page.locator(selector).evaluateAll(nodes => nodes.slice(0, 11).map(n => {
        const range = document.createRange(); range.selectNodeContents(n);
        const s = getComputedStyle(n);
        return { text: n.textContent.trim(), color: s.color, font: s.fontSize,
            rects: [...range.getClientRects()].map(r => ({ x: r.x, y: r.y, width: r.width, height: r.height })) };
    }));
    await page.locator(selector).evaluateAll(nodes => nodes.forEach(n => n.style.visibility = 'hidden'));
    const path = `tools/.runtime/readability-contrast-${width}-${scale}`;
    await page.screenshot({ path: `${path}.png`, fullPage: true });
    fs.writeFileSync(`${path}.json`, JSON.stringify({ width, scale, browser: browser.version(), samples }));
    await page.close();
}
await browser.close();
const result = spawnSync('python', ['docs/qa/readability-states/contrast.py'], { encoding: 'utf8' });
if (result.status !== 0) throw new Error(result.stderr || result.stdout);
console.log(result.stdout.trim());
