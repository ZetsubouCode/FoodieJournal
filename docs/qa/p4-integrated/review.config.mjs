import { defineConfig } from '@playwright/test';
import path from 'node:path';
import visual from '../../../playwright.visual.config.js';

// Generate review candidates separately; this never writes the accepted baseline directory.
export default defineConfig({ ...visual,
    testDir: path.resolve('tests/Browser'),
    outputDir: path.resolve('tools/.runtime/p4-candidate-results'),
    snapshotPathTemplate: path.resolve('docs/qa/p4-integrated/candidates/{arg}{ext}'),
    updateSnapshots: 'all',
    reporter: [['list'], ['json', { outputFile: 'docs/qa/p4-integrated/candidates.json' }]],
});
