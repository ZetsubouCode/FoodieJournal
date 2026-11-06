import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './tests/Browser',
    fullyParallel: false,
    workers: 1,
    timeout: 45000,
    retries: 0,
    reporter: [['list'], ['json', { outputFile: 'test-results/browser-results.json' }]],
    use: { baseURL: process.env.FOODIE_BROWSER_URL || 'http://127.0.0.1:8011', browserName: 'chromium', actionTimeout: 10000, navigationTimeout: 20000, trace: 'retain-on-failure', screenshot: 'only-on-failure' },
});
