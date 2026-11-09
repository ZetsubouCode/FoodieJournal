import { defineConfig } from '@playwright/test';

if (process.platform !== 'win32') throw new Error('Reviewed baselines require Windows Chromium; review a separate platform set before changing this gate.');

export default defineConfig({
    testDir: './tests/Browser', testMatch: 'public-visual.spec.js',
    workers: 1, fullyParallel: false, retries: 0, timeout: 60000,
    outputDir: 'tools/.runtime/visual-results',
    reporter: [['list'], ['json', { outputFile: 'tools/.runtime/visual-results.json' }]],
    snapshotPathTemplate: '{testDir}/__screenshots__/public-visual-win32-chromium/{arg}{ext}',
    updateSnapshots: 'none',
    expect: { toHaveScreenshot: { animations: 'allow', caret: 'hide', scale: 'css', threshold: .15, maxDiffPixels: 30 } },
    use: { browserName: 'chromium', baseURL: 'http://127.0.0.1:8015', locale: 'id-ID', timezoneId: 'Asia/Jakarta',
        viewport: { width: 1440, height: 1000 }, deviceScaleFactor: 1, colorScheme: 'dark', reducedMotion: 'reduce',
        trace: 'retain-on-failure', screenshot: 'only-on-failure', actionTimeout: 10000, navigationTimeout: 20000 },
});
