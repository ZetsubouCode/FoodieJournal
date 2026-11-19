import fs from 'node:fs';
import assert from 'node:assert/strict';
const output = 'docs/qa/p4-integrated';
const read = name => JSON.parse(fs.readFileSync(`${output}/${name}.json`));
const median = values => [...values].sort((a, b) => a - b)[Math.floor(values.length / 2)];
const range = values => [Math.min(...values), Math.max(...values)];
const navigation = [];
for (const name of ['home', 'explore', 'visit', 'place']) for (const width of [390, 1440]) for (const cache of ['cold', 'warm']) {
    const samples = read('navigation').samples.filter(s => s.name === name && s.width === width && s.cache === cache); assert.equal(samples.length, 3);
    assert.ok(samples.every(s => s.media.every(m => !m.url.endsWith('/original'))));
    navigation.push({ name, width, cache, lcpMedian: median(samples.map(s => s.lcp)), lcpRange: range(samples.map(s => s.lcp)),
        clsMaximum: Math.max(...samples.map(s => s.cls)), ttfbMedian: median(samples.map(s => s.navigation.ttfb)),
        encodedMediaByteRange: range(samples.map(s => s.media.reduce((sum, m) => sum + m.bytes, 0))), candidates: samples.map(s => s.lcpElement), shifts: samples.flatMap(s => s.shifts) });
    navigation.at(-1).encodedImageBytes = { median: median(samples.map(s => s.imageResources.reduce((sum, m) => sum + m.bytes, 0))), range: range(samples.map(s => s.imageResources.reduce((sum, m) => sum + m.bytes, 0))) };
}
const pairs = [];
for (const name of ['archive', 'explore']) for (const width of [390, 1440]) for (const cpuRate of [1, 4]) {
    const select = version => read('interactions').samples.filter(s => s.name === name && s.width === width && s.cpuRate === cpuRate && s.version === version);
    const old = select('old'), current = select('current'); assert.equal(old.length, 3); assert.equal(current.length, 3);
    const trace = {};
    for (const metric of ['Paint', 'Layout', 'UpdateLayoutTree']) {
        const before = median(old.map(s => s.trace[metric].ms)), after = median(current.map(s => s.trace[metric].ms));
        trace[metric] = { oldMedian: before, currentMedian: after, changePercent: (after / before - 1) * 100,
            oldRange: range(old.map(s => s.trace[metric].ms)), currentRange: range(current.map(s => s.trace[metric].ms)) };
    }
    const arrivals = name === 'explore' ? ['cold', 'warm'].map(cache => {
        const a = old.map(s => s.arrivals.find(a => a.cache === cache)), b = current.map(s => s.arrivals.find(a => a.cache === cache));
        const before = median(a.map(s => s.lcp)), after = median(b.map(s => s.lcp));
        return { cache, oldMedian: before, currentMedian: after, changePercent: (after / before - 1) * 100, oldRange: range(a.map(s => s.lcp)), currentRange: range(b.map(s => s.lcp)),
            oldTtfbMedian: median(a.map(s => s.navigation.ttfb)), currentTtfbMedian: median(b.map(s => s.navigation.ttfb)),
            sameVisibleImageSelection: a.every((s, i) => JSON.stringify(s.images) === JSON.stringify(b[i].images)),
            completedMediaByteRanges: { old: range(a.map(s => s.media.reduce((sum, m) => sum + m.bytes, 0))), current: range(b.map(s => s.media.reduce((sum, m) => sum + m.bytes, 0))) } };
    }) : [];
    pairs.push({ name, width, cpuRate, trace, arrivals, oldP95Median: median(old.map(s => s.p95Gap)), currentP95Median: median(current.map(s => s.p95Gap)),
        oldMaximumGap: Math.max(...old.map(s => s.maxGap)), currentMaximumGap: Math.max(...current.map(s => s.maxGap)),
        events: current.flatMap(s => s.events), shifts: current.flatMap(s => s.shifts.filter(e => !e.recentInput)) });
}
const place = [];
for (const width of [390, 1440]) for (const cpuRate of [1, 4]) for (const cache of ['cold', 'warm']) {
    const select = version => read('place').samples.filter(s => s.width === width && s.cpuRate === cpuRate && s.cache === cache && s.version === version);
    const old = select('old'), current = select('current'); assert.equal(old.length, 3); assert.equal(current.length, 3);
    const before = median(old.map(s => s.lcp)), after = median(current.map(s => s.lcp));
    place.push({ width, cpuRate, cache, oldMedian: before, currentMedian: after, changePercent: (after / before - 1) * 100,
        oldRange: range(old.map(s => s.lcp)), currentRange: range(current.map(s => s.lcp)), oldTtfbMedian: median(old.map(s => s.navigation.ttfb)), currentTtfbMedian: median(current.map(s => s.navigation.ttfb)),
        oldClsMaximum: Math.max(...old.map(s => s.cls)), currentClsMaximum: Math.max(...current.map(s => s.cls)) });
}
fs.writeFileSync(`${output}/performance.json`, JSON.stringify({ navigation, interactions: pairs, place,
    limits: 'Three samples per cell; same-build paired diagnostics do not replace genuine historical before/after or physical/field measurements. Raw ranges, layout-shift sources, event timing and CPU throttling remain explicit. Event duration is not field INP.' }, null, 2));
console.log(JSON.stringify({ navigation: navigation.map(({ name, width, cache, lcpMedian, clsMaximum }) => ({ name, width, cache, lcpMedian, clsMaximum })),
    interactions: pairs.map(s => ({ name: s.name, width: s.width, cpuRate: s.cpuRate, trace: s.trace, currentP95Median: s.currentP95Median })), place }, null, 2));
