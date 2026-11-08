# Frontend documentation audit verification

8 November 2026 (Asia/Jakarta), local `main` at `b56db2c584cbfb2dcd7a55592d8a3a5d3dd7fcbd`. All results below were executed during this audit unless explicitly labeled historical. Application code and behavior were not changed.

## Environment and provenance

PHP 8.2.4, Node 26.3.1, Vite 7.3.7, Chromium 156.0.8078.4, Windows. The photographic fixture already existed at `tools/.runtime/cinematic.sqlite` with separate `cinematic-media`; server `php tools/cinematic-server.php` bound only 127.0.0.1:8012. The separate functional fixture already existed at `tools/.runtime/browser.sqlite`; `php tools/browser-server.php` served 8011. No preparation/seeding/migration was needed for either. Sessions/cache use the existing QA paths. Owner `.env`, account, database and uploads were not modified or used as browser fixtures.

Route discovery: Home slot link → `/visit/demo-bara-di-ujung-hari`; its real place link → `/place/demo-bara-senja`. Home `/` and Explore `/explore` come from actual routes. `/missing-error-page-preview` intentionally requests a nonexistent route to observe real 404. Read-only SQLite query of the isolated fixture located the published no-photo visit `/visit/demo-teh-tanpa-kamera`; its public page links `/place/demo-rumah-teh`. No private titles, credentials or owner content were exported.

The photographic demo is labeled fictional, with bundled illustrative images. It is not evidence of owner visits or image quality on a live deployment.

## Executed checks

| Command / inspection | Result |
| --- | --- |
| `git status --short`, branch/HEAD/log, `git show 4f6365b`, `git show 801cc57`, `git show b56db2c`, CSS log/diff | Anchors verified locally; only task 004 initially untracked; no destructive Git operations |
| `php artisan test --compact --filter='VendingMachineTest\|CinematicPublicTest\|PublicTest\|DiscoveryMachineTest\|SystemSecurityTest'` | **33 passed, 489 assertions**, 15.49s; isolated SQLite memory through test configuration/safety guard. In the shell command, filter alternatives use ordinary `|` inside the quoted value, without Markdown escape backslashes |
| `node --test tests/Unit/vending-state.test.mjs` | **10 passed**, including duration parsing, angle/wrap/drop geometry and legal power/print/reset state |
| `npm run build` | **Passed**. Vite emitted runtime-reference notices for existing `/assets/...` URLs; no dependency added. CSS 94.33kB / 18.47kB gzip; archive JS 16.88kB / 5.80kB gzip. Build produced no tracked application diff |
| `$env:FOODIE_BROWSER_URL='http://127.0.0.1:8012'; npx playwright test tests/Browser/vending-machine.spec.js tests/Browser/homepage.spec.js --reporter=list --output=tools/.runtime/docs-audit-browser` | **19 vending passed; 2 homepage failed due to wrong fixture**, 2.1m. Homepage expected literal `Ingin kembali QA`, absent from photographic demo. This run is not an all-pass suite |
| `$env:FOODIE_BROWSER_URL='http://127.0.0.1:8011'; npx playwright test tests/Browser/homepage.spec.js tests/Browser/discovery-machine.spec.js tests/Browser/error-pages.spec.js --reporter=list --output=tools/.runtime/docs-audit-browse` | **9 passed**, 41.0s, including both previously mismatched homepage cases, all six discovery cases and real-404 browser coverage |
| `node tools/.runtime/audit-frontend.mjs` (same source retained as `capture.mjs` here) | **35 route/width checks**, no document horizontal overflow, no uncaught page errors; six meaningful machine state captures; Explore delayed loading, drawer, no-match, controlled 503 and successful retry |
| `node docs/qa/frontend-experience/capture-states.mjs` | **10 root-text-200% checks and 10 reduced-motion checks** at 375/1440 across all five surfaces, no horizontal overflow. Axe Explore/Visit/Place scans: **zero reported violations** for selected WCAG A/AA tags. Actual no-photo Visit rendered text hero and omitted gallery; price stayed “Harga belum dicatat” |
| Canonical local-link/anchor and evidence checks; `git diff --check` | Passed after fixing an extra README EOF blank line; final check documented below |

The first supplemental capture attempt stopped at axe because it used Playwright's implicit page context. The retained script now creates an explicit context; its complete rerun produced `states.json`. No application change was involved. The first local-link check preceded creation of this verification file and correctly reported its two pending links; the final check includes this file.

Final documentation checks: **15 entry/canonical documents checked**, zero missing local links or heading anchors; evidence assertions confirmed 35 render, 10 enlarged-text and 10 reduced-motion records, three clean axe surface scans and 90 screenshots. Existing CSS runtime asset paths were present. Both retained `.mjs` scripts passed `node --check`; final `git diff --check` passed. Tracked changes are Markdown only; new executable files are audit capture helpers under this evidence directory. Both QA server processes started for this audit were stopped afterward.

Exact PHP filter command (without Markdown table escaping):

```powershell
php artisan test --compact --filter='VendingMachineTest|CinematicPublicTest|PublicTest|DiscoveryMachineTest|SystemSecurityTest'
```

The 19 vending tests verify release-only power, invalid drops, reverse removal, indexed pagination/wrap, stale requests/retry, printed visit identity, reset, touch/keyboard, storage failure, responsive/LED geometry, missing images, no JS, interruption, compiled production timings, no pagination widgets, outboard reset-height placement and finite non-electrical pickup cues. Axe state scans in that suite cover off/on/ready desktop/mobile. These are functional/geometry tests, not pixel baseline comparisons or accessibility certification.

## Visual evidence and reading guide

`evidence.json` stores route, status, viewport, scroll width, shell/footer geometry, machine state attributes and page errors. `states.json` stores enlarged-text/reduced checks, limited axe scan results and the genuine no-photo fixture observation. There are **90 retained JPEGs**. All primary viewport shots are 1000px high; scene/result crops can extend beyond the viewport. JPEG quality 68 keeps this documentation evidence compact; these are not pixel-perfect visual-regression baselines. Visual review found that narrow enlarged Explore type splits “penasaran” within the word despite passing overflow checks; A10/P0-3 tracks this readability gap.

| Evidence | What was observed |
| --- | --- |
| `{home,explore,visit,place,error}-{320,375,390,768,1024,1440,1920}.jpg` | 35 current rendered route/width combinations, real 404 for error route |
| `{home,explore,visit,place,error}-footer-{375,1440,1920}.jpg` | Full-width footer background versus aligned/capped inner content, cross-page atmosphere/ending |
| `nav-start.jpg`, `nav-mid.jpg`, `nav-end.jpg`, `nav-keyboard-focus.jpg` | Current hover-progress **absent** at rest, ~110ms and >350ms; keyboard outline present. These do not demonstrate the future effect |
| `machine-{off,boot,selected,printing,ready,retrieving}.jpg` | Real reducer states logged alongside images; physical print feed and settled paper are distinct |
| `explore-{loading,food,filter,empty,error}.jpg` | Actual busy/committed view, staged filter, zero-result and recovery surface |
| `visit-failed-images.jpg`, `place-failed-images.jpg` | Network-aborted image fallback, distinct from a record with no photograph |
| `{home,explore,visit,place,error}-{reduced,text200}-{375,1440}.jpg` | Reduced motion and root-font 200% smoke checks; not actual browser zoom |
| `visit-no-photo.jpg`, `place-of-no-photo-visit.jpg` | Genuine published photo-free Visit; associated Place may still have photos from other visits, so this is not an all-photo-free Place test |

Visual review inspected the older `qa/memory-archive-v2/explore-1440.png`, current Home 768/1920, Explore 768/1440, Visit 375, footer 1920 and intermediate printing, then focused sparse-data/enlarged states. The complete capture matrix is retained for review; generating a screenshot does not mean every pixel was manually inspected. Historical reference tests/numbers were not rerun simply because the report links them.

Capture caveats: initial script uses normal animation times, but screenshot operations add gaps between user actions. A controlled 800ms response delay exposes real Explore waiting; a controlled 503 exposes failure. Retrieval navigation is held 800ms to retain the old document for the retrieving frame; do not use this as navigation-latency evidence. Deliberate media aborts/404/503 are expected network errors, separate from the zero uncaught `pageerror` count. No fresh unmodified real-time video or production performance metric was recorded.

## Reproduction

From the repository root, with existing isolated fixture data and built assets:

```powershell
php tools/cinematic-server.php
# In a second terminal:
node docs/qa/frontend-experience/capture.mjs
node docs/qa/frontend-experience/capture-states.mjs
```

The captures overwrite only their named audit evidence files. To rebuild absent isolated fixtures, inspect/run the dedicated prepare script documented in operations; never substitute owner `.env` or a live database. Functional label-specific tests require 8011; vending state tests can use the photographic 8012 fixture. New baselines must record their actual commit/build; these scripts' baseline metadata is deliberately fixed to this audit.

## Limits

No full `composer test`, MySQL/MariaDB test run, deployment, physical-device/Safari/Firefox, actual screen reader, real browser text zoom, production field vitals or fresh LCP/INP/CLS measurement. All-null ratings and zero-publication behavior have source/feature-test evidence, not new browser fixtures here. No all-photo-free or >18-visit Place browser fixture was exercised. Actual six-status browser copy/action captures remain future work; the existing error browser test changes 404 numerals for two layout samples, while PHP tests verify real statuses/JSON/recovery.

Owner database/system schemas, recovery snapshots, credentials, uploaded media and original cabinet artwork were untouched. Old QA evidence remains intact. Remaining gaps are explicitly planned in the roadmap; no frontend improvement is misreported as implemented by this documentation phase.
