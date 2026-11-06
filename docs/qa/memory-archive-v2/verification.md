# Memory Archive V2 — verification record

6 November 2026. Final V2 runtime, local Chromium, isolated browser fixtures.
Owner data and uploaded photographs were not altered.

## Final executed checks

| Check | Observed result |
| --- | --- |
| php artisan test --compact | 60 passed / 994 assertions / 40.20s |
| php vendor/bin/pint --test | Passed |
| npm run build | Passed; Vite 7.3.7, 13 modules transformed |
| npx playwright test | 25 passed / 3.9m / one worker / no retries |
| node tools/capture-memory-archive.mjs | Completed / 17 logged motion-state frames |
| git diff --check | Passed |

Vite reports absolute public URLs as unresolved at build time; they remain
runtime URLs intentionally. Captures/tests found no unexpected missing assets.
The final capture reports errors=[]; one deliberately intercepted 503 is
separately recorded under expectedFailures for the retry state.

PHP uses SQLite :memory: from phpunit.xml. Browser tests use the guarded separate
tools/.runtime SQLite/media fixture and port 8011. prepare-browser.php includes
the V2 hierarchy/long-list fixture helper. Capture uses existing published data on
port 8000 read-only. No owner database reset or migration was run.

## Behavior and regression evidence

The 12 Memory Archive browser tests cover:

1. Initial empty tray, overview until explicit opening, semantic full-screen link.
2. Upper-knob draw without filter changes or immediate duplicate ID.
3. Lower-knob subtraction preserving manual criteria.
4. Async mode switches without document/header/cabinet replacement; correct links.
5. Rapid modes/collections, stale-response rejection and Back/Forward restoration.
6. Staged hierarchy, searchable styles, multiselect, tags/recommendations, chips,
   real counts, apply/cancel.
7. Mode price context, group reset, search/sort/grid/list/pagination and retry.
8. Honest single/empty sets and failed media.
9. Keyboard interaction and reduced-motion opening contract.
10. Desktop/tablet/mobile bounds, shared Home geometry and 200% text.
11. Axe WCAG A/AA scans at desktop/mobile in four interaction states.
12. Touch knob controls and a local paint/layout sample.

The other 13 browser tests cover Home, HTTP errors, journal filters, editor,
gallery, visit-card navigation/media/touch and performance smoke checks.
Retained QA entries can grow between runs; visit-card tests now scope their known
fixture so pagination does not hide the intended record.

Feature tests exercise whole-set surprise selection even with page=999,
published-only eligibility, mode-correct links, immediate exclusion, single/zero
sets, manually retained preset dimensions and same-mode price/sort preservation.

## Inspected actual renders

[frames.json](frames.json) records phases, package/tray/status bounds, alignment
and expected/unexpected error accounting. Review images include:

| State / area | Evidence |
| --- | --- |
| Idle complete pickup | 01-idle-empty-tray.png |
| Keyboard focus / hover | 02-focus.png / 03-hover.png |
| Selection / processing | 04-selection-processing.png |
| Partial emergence | 05-partial-dispense.png / 06-partial-dispense-later.png |
| Resting package, overview | 07-dispensed-collection-overview.png |
| Flap / emerging photo | 08-opening.png / 09-photo-emerging.png |
| Individual / repeat / reset | 10-revealed-full-screen-link.png / 11-another-memory.png / 12-reset-empty-tray.png |
| Detailed tray occlusion | tray-05 through tray-09 crops |
| Drawer | drawer-desktop-categories.png / drawer-desktop-pending.png / drawer-mobile.png |
| Responsive cabinet | scene-1920.png / scene-1280.png / scene-1024.png / scene-768.png / scene-390.png / scene-320.png |
| Content modes | mode-food-mobile.png / mode-place-mobile.png |
| Empty / retry / reduced motion | empty-state.png / error-state.png / reduced-motion-mobile.png |
| Full composition | full-page-reference.png |
| Motion | interaction-review-v2.webm |

Direct inspection found and corrected the original plate/tray/wordmark patches
in the bitmap source. Final partial frames show paper emerging behind the upper
rim and resting inside the recessed opening with the foreground lip above it.
The flap/photo rise locally; status stays below the whole machine. Mobile now
uses photographed frame/tray crops with correct lip occlusion rather than flat
replacement hardware. The center rail is one shared physical faceplate.
The display is an overview before opening and an individual only afterward.

Compared full-page rendering against Foodie_Memory_Archive/reference_full_page.png:
preserved dominant horizontal metal cabinet, copper detail, warm window lighting,
six photographic windows, integrated display, console, editorial reel and quiet
footer. V2 intentionally removes duplicate cabinet branding and retains the
larger shared Home proportions. Real content photos differ from the illustration.

Home/Explore measured header/main alignment:
- 1440px viewport: header x=80, width=1280, height=72 on both; main x=80,width=1280.
- 390px viewport: header x=16, width=358,height=72 on both; main x=16,width=358.
- Responsive tests also cover 320px and 200% text without horizontal document overflow.

## Timing and performance limits

Capture deliberately slows/pauses native CSS transitions to inspect partial
geometry. It is an inspection recording, not a representation of production
interaction speed. Production first/repeat travel is 520/320ms and retraction
200/160ms, with 260ms flap then 260ms photo reveal. Reduced motion removes travel
while retaining the explicit opening step. Ordinary timings pass browser tests.

[explore-lab.json](explore-lab.json): one unthrottled local Chromium mobile sample:
LCP 692ms, CLS 0; INP not measured. No field or cross-device performance claim.
Physical Safari, screen-reader sessions and production-scale query benchmarking
remain unverified.

