import fs from 'node:fs';

const report = JSON.parse(fs.readFileSync('test-results/browser-results.json', 'utf8'));
const tests = [];
function collect(suites) {
    for (const suite of suites) {
        for (const spec of suite.specs || []) for (const test of spec.tests || []) tests.push({ title: spec.title, status: test.results.at(-1)?.status, duration_ms: test.results.at(-1)?.duration });
        collect(suite.suites || []);
    }
}
collect(report.suites);
if (tests.length !== 6 || tests.some(test => test.status !== 'passed')) throw new Error('Complete six-test browser run must pass before evidence collection.');
fs.mkdirSync('docs/qa', { recursive: true });
fs.writeFileSync('docs/qa/browser-summary.json', JSON.stringify({ stats: report.stats, tests }, null, 2));
for (const [source, target] of [['home-desktop.png', 'home-1440.png'], ['explore-375.png', 'explore-375.png'], ['admin-editor-mobile.png', 'admin-375.png']]) fs.copyFileSync(`test-results/${source}`, `docs/qa/${target}`);
console.log('Saved successful browser summary and three explicitly labeled QA-fixture screenshots.');
