# P0-3 — Readability and honest states

Implemented 9 November 2026, Asia/Jakarta. Scope: only P0-3 in the frontend roadmap. Baseline HEAD remains `bcbd9b7269cfd4d08e34082c1dda6a57f78fac94`; the workspace already contained completed, uncommitted P0-1/P0-2 changes. Those changes and their QA directories were preserved. Read against [P0-2 evidence](../wide-shell/verification.md), [page contracts](../../page-experience.md) and [current status](../../implementation-status.md).

## Behavior and boundaries

| Context | Implemented behavior | Verification |
| --- | --- | --- |
| Zero published visits, including private/draft-only journals | “Arsip belum dipublikasikan”; no empty-state reset/create CTA. Existing search/chips remain available. Home cabinet/recent remain truthfully empty | Dedicated 8013 fixture, full/fragment PHP responses, after-unpublish assertion |
| Filters match nothing | “Tidak ada catatan yang cocok”; query/chips retained; reset preserves tab and view | SSR, asynchronous search/reset, URL/status assertions on 8014 |
| Nonempty total but empty page number | First-page recovery retains criteria instead of claiming the archive is empty | PHP and browser page=9 checks |
| Published visits without food details | Specific missing-food copy and link to actual published visits | Isolated in-memory PHP fixture |
| No personal picks | Explicit unpublished-recommendation copy on Home and quiet unavailable directory text on Explore; no generic link pretending to be a recommendation | Real sparse fixture; explicit relationships remain the only endorsement source |
| No photo / failed derivative | “Foto belum tersedia” for absence, “Foto belum dapat dimuat” for a failed card request; existing text/fallback primitives | Real photo-free Visit/Place/cards, synthetic denied derivative requests, zero listing-original requests |
| Null ratings/prices and recorded zero price | Existing “Belum Dinilai” / “Penilaian belum dicatat” / “Harga belum dicatat”; explicitly recorded zero remains `Rp0` | PHP and real sparse browser fixture with one unknown-price item and one zero-price item |

`PublicController::explore` adds one short-circuited `publishedVisible()->exists()` only when the current result page is empty. No private/master counts, new scope, price calculation, status gate, schema or media pipeline change. Both full and `X-Explore-Fragment` responses receive the signal; legacy archive fragments keep their existing path.

Home excerpts are 1rem. Essential Home/result/date/location/filter metadata is normally .875rem; .75rem archive engraving remains secondary. Shared `.btn` now has minimum width as well as height. Card tag/overflow/recommendation actions and filter chips use at least 44px hit heights; icon overflow controls have a 44px minimum width. Existing focus, disabled, no-JS, drawer and history behaviors remain.

Explore’s responsive heading enlarges with root text while keeping “penasaran” together. Tile captions have a local dark backing for contrast and participate in grid height; no-photo labels occupy their own row. Text-space container queries reflow columns before enlarged captions become too narrow. Photographic tiles retain their preferred ratios when content fits; enlargement and missing-photo states may grow. This is a readability repair, not a new discovery layout or Place redesign.

## Fixtures and render evidence

`tools/readability-qa.php` validates its two fixed fixture names and uses dedicated SQLite files/media roots under ignored `tools/.runtime/`. It migrates only those files, never refreshes data, and asserts the expected published count. The empty target has one private and one draft visit. The sparse target adds one fixed-title published visit, no photos/ratings/recommendations/Maps, two food prices (null/zero), and ten long Indonesian tags. Existing photographic demo media remains on 8012; functional QA remains on 8011. No owner `.env`, database, account, media or MariaDB schema was touched.

[Measurements](measurements.json) contain browser/build-time viewport, overflow, font, color and actual control rectangles. Chromium **156.0.8078.4**, built Vite assets, fonts ready, reduced-motion capture, widths **320/390/768/1440**. There are **28 measured page layouts and six supplemental screenshots**. Image decoding is awaited; lazy images are made eager only by the capture helper to render complete full-page evidence, not as a production loading or performance change. JPEG evidence is not a pixel regression baseline; P0-4 is still pending.

- Empty, filtered, sparse-with-ten-chips, root-text 200%, photo-free Visit and photo-free Place at all four widths.
- Photographic Explore at root-text 200% at all four widths.
- Supplemental empty Home, no-picks Home, unknown/zero Food, keyboard focus, actual photographic board and denied derivative card.

Visual review inspected the matched 390px empty/filtered pages, sparse card/focus, enlarged heading and tile captions, 1440px photographic board/denied card, and photo-free Visit/Place. No overflow was observed in the capture matrix. Browser assertions additionally check caption bounds, word wrapping and hit areas. The photo-free Place still has its existing empty desktop grid track; that P3 composition work is intentionally pending.

[Contrast samples](contrast.json) use actual demo photographs, gradients and panels at 1440px, hiding only the sampled text while recording background pixels. At 1440px normal text, conservative minima across entire text rectangles were **5.25:1** for intro copy, **13.15–14.18:1** for photo captions, and **5.99–10.77:1** for sampled card metadata/place text. At 390px root-text 200%, sampled photo captions were **8.40–11.04:1** and metadata/place text **5.89–10.49:1**. Enlarged captions initially failed this check; a local .64 black backing now keeps text readable over bright details without changing tile boxes. Text rectangles include spaces; this is a bounded sample, not a sitewide conformance claim. Focus/disabled states were also visually inspected; axe checks remain regression checks rather than certification.

## Executed checks

All **48 distinct browser cases** passed across the regression and focused repair runs; repeated cases are not counted twice.

| Check | Result / evidence |
| --- | --- |
| Production build | PASS, [build log](build.log) and manifest SHA-256 in measurements |
| PHP public/domain/media/security scope | **32 passed, 497 assertions**, [PHP log](php.log) |
| Existing browser regressions | **40 distinct cases passed**: navigation 5, shell/admin/media 4, cabinet 19, discovery 6, Home 2, cards 3, errors 1. [Regression log](regression-before-caption-fix.log) records these 40 plus five new passes and the new caption failure that was subsequently fixed |
| Affected surface rerun after grid correction | **18 passed**, including all six new cases, [affected rerun](affected-final.log) |
| Final readability rerun after contrast backing | **6 passed**, [final focused log](readability-final.log) |
| Gallery on-demand originals / admin mobile | **2 passed**, [supplemental log](gallery-admin.log) |
| Render matrix | **28 layouts + six supplemental captures**, no horizontal overflow/page errors; [capture log](capture.log), [measurements](measurements.json) |
| Actual-image/panel contrast | PASS at 1440 normal and 390 root-text 200%; [samples](contrast.json), reproducible with capture-contrast.mjs + existing Python/Pillow |
| Syntax / whitespace | New PHP helper/controller/test lint passed; `git diff --check` exit 0 (only line-ending notices) |

P0-1/P0-2 regressions explicitly retain hover timing/active/focus/reduced-motion behavior, 55 route/width shell checks, native cabinet/LED/lamp/reset alignment, 68ch prose, admin geometry and derivative sizing. No cabinet, navigation or shell source was edited by P0-3.

The initial parallel Playwright invocations collided in the shared `test-results` directory (ENOENT trace artifacts). They were rerun sequentially. The new photo-caption assertion subsequently exposed an intrinsic grid-width defect at enlarged text; the track now uses `minmax(0, 1fr)` and the affected checks were rerun. Neither failure was hidden by weakening expectations.

## Reproduce

From the repository root, using only the dedicated loopback fixtures:

```powershell
php tools/readability-qa.php prepare empty
php tools/readability-qa.php prepare sparse
npm run build
```

Run the servers in separate terminals (`browser` and `cinematic` use the already-prepared fixtures):

```powershell
php tools/browser-server.php
php tools/cinematic-server.php
php tools/readability-qa.php serve empty
php tools/readability-qa.php serve sparse
```

Run checks sequentially to avoid Playwright artifact collisions:

```powershell
php artisan test --compact --filter='PublicTest|CinematicPublicTest|VendingMachineTest|SystemSecurityTest|VisitCardTest|MediaTest'
npx playwright test tests/Browser/readability-states.spec.js tests/Browser/public-shell.spec.js tests/Browser/public-navigation.spec.js tests/Browser/vending-machine.spec.js tests/Browser/discovery-machine.spec.js tests/Browser/homepage.spec.js tests/Browser/visit-card.spec.js tests/Browser/error-pages.spec.js
npx playwright test tests/Browser/journal.spec.js --grep 'gallery loads|admin mobile'
node docs/qa/readability-states/capture.mjs
node docs/qa/readability-states/capture-contrast.mjs
git diff --check
```

The capture script overwrites only this P0-3 evidence, not previous QA directories. PHP feature fixtures use isolated in-memory SQLite and fake media. Browser denial is injected as HTTP 403; actual published→private revocation of original/thumbnail/card/hero is separately tested by `MediaTest`.

## Limits

No full suite, native browser text-only zoom, physical mobile, Safari/Firefox or screen-reader run. Root-font 200% is an explicit approximation, not a native zoom claim. Production field performance and larger Place history remain unverified. No P0-4 snapshots, P1 mechanics, P2 skeletons, P3 redesign, dependency, commit, push or deployment was added.
