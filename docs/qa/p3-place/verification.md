# P3-2 — Place chronology

9 November 2026, Asia/Jakarta. Starting HEAD `3c9f3e64db880dbd7f6cdffa9e714e5a83c7547f` **plus completed, uncommitted P3-1**, preserved. [Starting state](starting-state.json) records the dirty tree and 140 current file hashes, including all 40 reviewed PNGs, P3-1 source/tests/evidence, controller and shared card/pagination primitives. Read completed [P0-1](../navigation-hover-progress/verification.md), [P0-2](../wide-shell/verification.md), [P0-3](../readability-states/verification.md), [P0-4](../visual-baselines/verification.md), [P0-H](../p0-hardening/verification.md), [P1](../p1-physical-response/verification.md), [P2](../p2-discovery/verification.md), [P3-1](../p3-visit/verification.md), design memory and canonical composition/voice/page/motion contracts. Older QA remains dated history; its old baseline hashes are not rewritten to describe this change.

## Hypothesis and implemented scope

The [before](before-1440-place.jpg) Place page already has the approved hero/text opening and independent Visit cards. One hypothesis: **quiet month/year markers make revisits easier to scan without changing the cards or history model**. The [after](after-1440-place.jpg) history groups only the existing current-page collection by `Presentation::localTime(visited_at, 'F Y')`, matching the cards' Asia/Jakarta dates. Each visible period names an ordered list. A missing date uses the existing “Tanggal belum dicatat” label. A period can repeat on the next page; no complete-month count, visit numbering or continuation claim is invented.

Only `public/place.blade.php` and local `.place-history*` rules in `cinematic.css` change production behavior. A 12rem month track sits beside the existing 58rem card measure; at ≤60rem available container width, including enlarged text, it moves above the list. The short copper edge and existing serif/secondary text use current material tokens. Initial inspection caught a split “November” in an 8rem track; the final track and a word-wrapping assertion correct it. Existing card hover/focus/recommendation/tag behavior is inherited. No new JS, dependency, animation, sticky marker, reveal, skeleton, wait, global token, hero size or responsive derivative change.

The controller, model/query/order, media pipeline, shared card/image/pagination/dialog primitives and P3-1 source/tests are unchanged. Callers/consumers were inspected before editing. The controller still selects the latest eligible published same-visit cover by visited_at/id with ready existing hero media, independently of history pagination; no cover is re-selected from a group. Publication/deletion/variant revocation, absent facts/Maps, null aggregates and the out-of-range recovery remain P0-H behavior. Date/id order, 18-visit page boundaries, public-only counts and query budget remain. Each visit retains its own historical facts/score, explicit recommendations and story link; no current menu/price or crowd-review claim was added.

## Isolation and actual checks

Retained dedicated SQLite/fake-media fixtures only: **8017 p0-hardening v1** (20 published visits to one Place, only cover on page 2, long photo-free second Place, private/draft exclusions), **8015 photo-v1**, **8016 empty-v1**, and **8014 readability-sparse** for preserved Visit browser cases. Identity headers are asserted on photo/hardening routes. No preparation, migration, seed, refresh, owner `.env`/account/media access, MariaDB/3306/system-schema action, commit, push or deployment. PHP uses the existing destructive-test guard and SQLite memory. Only servers started here are stopped; fixtures remain.

| Gate | Actual result / evidence |
| --- | --- |
| Initial reviewed P3-1 pixel set | **23 passed / 40 PNGs**, updates disabled; [before](visual-before.log) |
| Production build | **PASS**, [build](build.log); existing installed dependencies |
| PHP public/Visit/Place/visibility/media/security | **39 passed / 497 assertions**, [final](php.log); includes existing eight stable-hero/revocation/query-growth cases and two new chronology tests |
| Place + preserved Visit browser scope | **9 passed**, [final](browser-final.log): five new Place cases, four unchanged P3-1 cases |
| Final reviewed pixel gate | **23 passed / 40 PNGs**, updates disabled; [final](visual-final.log). Five named Place PNG changes, 35 starting hashes preserved, including all P3-1 updates |
| Negative control | DOM-only 8px Home header shift rejected, clean removal passes, same baseline hash; [proof](probe.json), [rejected](probe-rejected.log), [reverted](probe-reverted.log) |
| Actual visual states | **44 captures**, [states](states.json), at 375/768/1440/1920: photographic history/footer, keyboard focus, Visit/return, page-2 periods/stable hero, empty-page recovery, long photo-free/all-null facts, reduced/root-text 200%, failed photos |
| Normal-time journeys | [1440 keyboard](realtime-1440.webm), [375 reduced/emulated touch](realtime-375.webm), [metadata](realtime.json), decoded [desktop](review-video-1440.jpg) / [compact](review-video-375.jpg) samples: real pagination, independent older Visit, return and browser Back to page 2/footer |
| Lab/media investigation | Three cold/warm samples per 390/1440 width [before](before.json) / [final after](after.json), [comparison](performance.json), [controlled font swap](font-shift-investigation.json), [paired same-build presentation diagnostic](presentation-diagnostic.json) |
| Format/syntax/whitespace and preservation | Changed PHP Pint, JS syntax, `git diff --check`, [checks](checks.log); [manifest](manifest.json) verifies baseline lineage, final-build capture/sample linkage and preserved P3-1/controller/card/pagination hashes |

There are **32 distinct passing browser cases**, 9 + 23, excluding repeated reviews and the deliberate failing probe. New PHP checks cover UTC→Jakarta month/year boundaries, same-time id ties, absent dates, each Visit's historical total/rating, recorded zero, endorsement without score, 18/2 published pagination, repeated months across pages, private/draft exclusion, empty page and revocation to a single remaining period then 404. Existing hero cases retain cross-visit/unready/missing/deleted media, stable older cover, null facts and bounded query-count coverage.

Browser checks retain the page-2 hero and 18/2 count, keyboard Visit↔Place/Back, no original request before activation, eight widths (320/375/768/959/960/1440/1920/2560) at 100/200% root text, short landscape, no-JS pagination, emulated touch, reduced motion at load/runtime, failed derivatives and a zero-violation scoped history axe scan. One held-font test verifies the caption does not move the month groups when fonts finish loading. These are scoped observations, not native text zoom, physical device or WCAG certification.

The [initial browser log](browser-initial.log) contains four failures: two unchanged Visit cases lacked their 8014 fixture server, one new assertion used a strict locator for two lists, and one measured a returned document before its stylesheet loaded. The latter diagnostic showed `readyState=interactive`, no stylesheet, natural image widths; `load`/UI/font readiness restored the correct 375px geometry. Final tests wait for actual readiness, with no app delay, tolerance relaxation or production overflow workaround. [Initial compact](initial-compact.png) and [narrow initial month marker](initial-history-1440.jpg) remain diagnostic history, not accepted final captures.

## Reviewed visual gate and lineage

Opened final full-size desktop/compact history groups, page-2 stable hero, photo-free/long facts, failed photos, enlarged text, all six Place before/actual gate crops, footer pairs and decoded video samples. Markers keep whole month words at wide widths, move above cards on compact columns, and identify real periods. Shared cards and footer content/material remain. This is a scoped design judgment with geometry/navigation/privacy checks.

Both pre-review full gates passed **20/23 cases**, rejecting only Place at 390/1440/1920; Home/Explore/Visit/errors/viewer remain matched. [First rejection](visual-pre-review.log), [final-source rejection](visual-final-pre-review.log). After actual inspection, only those three named Place cases were explicitly refreshed; the identical 1440 footer was restored from the task's starting copy. [Named review](visual-named-review.log), [six-crop dimensions/bounds](reviewed-place-crops.json).

- Three `place-*-hero` PNGs intentionally include the new chronology at the lower fold. The hero's source selection, media slot and facts are unchanged; the larger diff is below the historical introduction, with minor text-rasterization differences also retained in the reviewed crops.
- `place-390-footer`: 378→377px crop after changed upstream story height, retaining the same text/actions/styles.
- `place-1920-footer`: fractional crop/rasterization differences after upstream height changes; content/geometry/style source remains unchanged.
- `place-1440-footer`: pixel-identical starting crop retained. All other 34 PNGs, including the three P3-1 baseline updates, retain their starting hashes.

Each `place-WIDTH-STATE-before.png` preserves the starting bytes and `-reviewed.png` the inspected actual. No blanket update, mask, tolerance/font/browser/fixture change or weakened existing geometry/privacy/network assertion. Final comparison uses `--update-snapshots=none`. Earlier P3-1 manifest/baseline references stay dated history; this task's manifest describes the new lineage without editing them.

Screenshots eagerly decode already assigned derivatives only for deterministic review; runtime lazy loading stays unchanged. Recordings use native production time, with short observation holds on actual states, no slowMo/clock/effect rewrite. Font diagnostics deliberately hold real font requests to inspect swap geometry; they are not performance samples. `before-concurrent-diagnostic.json` is excluded from the comparison because it overlapped a browser gate. `after-with-caption-cap.json` predates the removed caption cap and is also excluded. Final before/after navigation samples run without other browser gates; raw samples are retained.

## Performance investigation and limits

Windows Chromium **156.0.8078.4**, scale 1, local fixture/self-hosted fonts, id-ID/Asia/Jakarta, reduced motion, unthrottled loopback, 390/1440×1000. Cold is a fresh context, warm its second navigation; this is not a production cache/CDN or field-vitals model.

| Median of three / maximum CLS | 390 before → after | 1440 before → after |
| --- | --- | --- |
| Cold LCP | 476 → 544ms (**+14.3%, +68ms**) | 504 → 612ms (**+21.4%, +108ms**) |
| Warm LCP | 468 → 512ms (+9.4%) | 464 → 492ms (+6.0%) |
| Maximum arrival CLS | .015841 → .015841 | .001374 → .001596 |
| Completed arrival media bytes, cold/warm | 273,324 → 273,324 | 455,548 → 455,548 |

**Cold LCP crossed the 10% investigation threshold at both widths.** Same selected hero, positive-byte derivative URL/encoded-byte sets, image `sizes`/processing dimensions and no arrival originals; no new query, JS, explicit request path or ongoing effect. Desktop cold before samples also record a zero-byte timing entry for the first photo's card derivative; two of three final after samples record that entry, while the first does not. The original recorded fields do not distinguish cancellation/cache behavior, so exact timing-entry equality is not claimed; zero entries and raw samples remain in the comparison. An alternating-order paired diagnostic compares old ungrouped HTML with grouped HTML on the same final build/server/query work, with identical interception for both. Desktop cold median is **524→524ms**; compact **504→572ms**, with median navigation TTFB **221.3→251.1ms** in that compact pair (desktop 218.0→211.1ms). This does not fully attribute the increase or replace genuine before/after results. Compact cost remains visible; desktop's original increase is not stable in the paired client comparison. No clean performance-pass or causal speed claim is made. Slower-device/production attribution remains P4.

The first after build also raised desktop CLS to **.004859**. Controlled font requests identified the added 68ch caption cap: fallback text occupied 52.78px, then 26.39px after swap, moving the history by 26.39px. Removing that unnecessary cap preserves the prior caption width; the uncapped caption and group y stay fixed during swap. The regression case verifies this. Remaining sampled font/card shifts are explicitly retained at .001596 rather than claimed zero. Shared font loading/card design was not changed to absorb a wider P4 task.

## Reproduction and completion boundary

Serve retained isolated fixtures in separate terminals, then execute browser/measurement/capture gates sequentially:

```powershell
php tools/visual-qa.php serve photo
php tools/visual-qa.php serve empty
php tools/p0-hardening-qa.php serve
php tools/readability-qa.php serve sparse
# Task terminal:
npm run build
php artisan test --compact --filter='PlaceHeroTest|PlaceChronologyTest|PublicTest|MediaTest|CinematicPublicTest|SystemSecurityTest'
php vendor/bin/pint --test tests/Feature/PlaceChronologyTest.php
npx playwright test tests/Browser/place-chronology.spec.js tests/Browser/visit-story.spec.js
npx playwright test --config playwright.visual.config.js --update-snapshots=none
node tools/prove-visual-baseline.mjs docs/qa/p3-place
node docs/qa/p3-place/capture.mjs
node docs/qa/p3-place/record.mjs
node docs/qa/p3-place/review-video.mjs
git diff --check
node docs/qa/p3-place/manifest.mjs
```

`measure.mjs before|after` refuses to overwrite its JSON; never rerun `before` against new code. Archive repeat samples explicitly, and inspect replacement images/videos before regenerating their hashes. `review-baselines.mjs` captures review candidates and preserves before copies; it does not update the gate. A future baseline change needs its own named review, not a blanket update to silence a failure.

**Complete within the scoped Chromium behavior/visual gate:** P3-2 current-page chronology and associated fixtures/regression/navigation/visual evidence, preserving completed P3-1. **Partial:** performance evidence retains measured cold-LCP increases and residual font/card CLS. **Pending:** P4 integrated access/device/performance/consistency. **Unverified:** native text zoom, physical Safari/iOS/Android/Firefox, assistive technologies, field vitals and owner production load. Full repository test suites, unrelated admin UAT and deployment were not performed. No owner data was changed.
