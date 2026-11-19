# P4 — Integrated accessibility, performance and consistency

9–10 November 2026, Asia/Jakarta. Starting HEAD `5a4adfbe224c6b688b2de88f533801149fda82ae`, initially clean working tree. [Starting hashes](starting-state.json) record the accepted source/tests/QA, including all 40 PNG baselines. Read completed [P0-1](../navigation-hover-progress/verification.md), [P0-2](../wide-shell/verification.md), [P0-3](../readability-states/verification.md), [P0-4](../visual-baselines/verification.md), [P0-H](../p0-hardening/verification.md), [P1](../p1-physical-response/verification.md), [P2](../p2-discovery/verification.md), [P3 Visit](../p3-visit/verification.md), [P3 Place](../p3-place/verification.md), design memory, composition/voice and current authority/page/motion contracts. Older reports remain dated history.

## Scope and repairs

Only P4. Callers, shared consumers, existing assertions and fixture targets were inspected before editing. No controller/query/model/policy/media-processing/schema/dependency change. Published-only access, historical IDR/null facts, personal recommendations, allergen uncertainty, stable eligible Place hero and all media revocation rules remain. Cabinet artwork, anchors, tube/bracket/socket, square Reset, rotary, exact print/photo/link identity, cancellation and 270/960/820ms clocks remain.

| Before | After | Why / evidence |
| --- | --- | --- |
| Explore at 960px / root 200% pushed view actions to 1036px | Existing toolbar controls reflow within a 48rem console container; no smaller targets | `reflow-before.json`, `reflow-after.json` (960px, no escaping nodes), before/after toolbar PNGs; integrated width test |
| Cabinet, visit-tag and active-filter labels were on unnamed generic divs | Add native group semantics; retain existing names/DOM/content | Whole-page axe **incomplete** review and explicit cabinet group assertion |
| Error skip link scrolled but left BODY focused | Make existing main target programmatically focusable; remove unsupported paragraph aria-label while retaining status text | Reproduced native keyboard behavior; all six real HTTP scenes assert Tab/Enter transfers focus |
| Normal text over bright imagery and small engraved plaque text fell below 4.5:1 | Two local dark text-shadow layers use existing surface tokens; marquee/Reset use semantic secondary text | Rendered glyph/background measurements; named reviewed crops; no artwork/palette/geometry rewrite |
| Pending Explore opacity .65 reduced metadata contrast to about 3.8–4.1:1 | Delete the fade/dimming rules; retain full-contrast useful results with existing busy/status/pending feedback | Held real request, computed opacity 1 assertion and repeated actual pending-state contrast |
| Computed outlines passed while rings lost contrast over bright materials, and the main card overlay ring was covered by its photo/body | Dark backing under existing header/hardware rings; frame the visible card title on main-link keyboard focus | Twelve actual outline samples at 390/1440 now pass ≥12.029:1, with painted PNGs reviewed; hit areas and independent actions remain |
| Older Place test assumed one `.results` list after P3 grouping | Assert history content across its existing period lists | Preserved stable hero, 18/2 count, publication and original-request assertions |
| Older full-suite QA wrote into historical evidence paths | Optional `FOODIE_QA_OUTPUT` redirects only rerun artifacts | Current run uses `legacy-rerun/`; earlier reports/PNGs remain byte-preserved |

These are supported accessibility fixes within the current composition/mechanical contracts, not creative P1–P3 expansion. No new library, wait, skeleton, texture, reveal or generic framework.

## Isolation and results

Retained loopback fixtures only: 8011 functional SQLite/media (isolated CRUD tests), 8012 photographic-caption fixture, 8013 private-only, 8014 sparse, 8015 fixed-date `photo-v1`, 8016 `empty-v1`, 8017 hardening `v1` (20 published visits, stable page-2 cover, long photo-free facts and six error routes). The retained server fixtures were served without preparation, refresh, reseed or migration. PHP may rebuild its guarded in-memory test schema. No owner `.env`/account/database/media modification or MariaDB/3306/system-schema action. Maintenance tests substitute temporary isolated storage. Only task-started servers are stopped afterward.

| Gate | Actual result |
| --- | --- |
| Production build, including standalone error CSS | PASS; final `build-final-focus.log` |
| Full guarded PHP suite, repaired templates | **81 passed / 1,281 assertions**; `php-release.log` |
| Pure machine reducer | **10 passed**; `node-release.log` |
| Initial unchanged visual comparison | **23 passed / 40 PNGs**, updates disabled; `visual-initial.log` |
| Initial integrated functional run | **83 passed / 2 failed**; `browser-initial.log/json`: reproduced enlarged-toolbar overflow and outdated single-list test |
| Repair checks | Reflow/history/real-status cases pass; initial semantic follow-up exposed shared tag labels; final semantic/keyboard flow and separate text-spacing case pass. Failed attempts remain in `repairs.log/json`, followed by `semantic-repair.log` and `text-spacing.log` |
| Full integrated functional gate | **86 passed**, `browser-release.log/json`, before the last focus-paint repair. Final affected navigation/card/hardware/keyboard cases and pixel gate follow separately; no full-suite claim is silently transferred to later CSS |
| Normal photographic/status text contrast | **1,040 measured text runs**, no failures; 20 unfocused offscreen skip-link runs explicitly unmeasured. Normal minimum **5.175:1**, large-text minimum **3.406:1**; `contrast.json` |
| Powered/printed/drawer/pending/viewer contrast | `contrast-states.json`: **852 text runs**, no measured failures. Offscreen/clipped items remain explicitly unmeasured; see access matrix |
| Actual focus-ring contrast | **12 painted samples passed** at 390/1440, minimum **12.029:1**; `focus-contrast.json`, `focus-release.log` and actual `focus/` PNGs |
| Final affected focus regressions | **8 navigation/card + 8 cabinet keyboard/touch/interruption cases passed**, `browser-focused.log/json`, `browser-keyboard.log/json`; these repeat existing cases and are not added to the distinct count |
| Final reviewed pixel gate | **23 passed / 40 PNGs**, updates disabled; `visual-release.log/json` |
| Matcher negative control | Deliberate 8px header shift rejected at **2,261 pixels**, clean removal passed with unchanged baseline hash; `probe.json`, rejected/reverted logs |
| Production-time integrated journeys | `realtime-1440.webm` (keyboard) / `realtime-390.webm` (emulated touch, reduced motion), build/selected ID in `realtime.json`; decoded samples in `review-video-*.jpg` reviewed |
| Format / whitespace | `git diff --check` passes. Whole-repository read-only Pint repeats pre-existing untouched `PublicTest.php` line-ending and `visual-qa.php` style failures (`pint.log`); no unrelated format sweep |
| Final syntax / preservation / cleanup | 15 JS files plus Python helpers parse; final whitespace passes. Integrity checks preserve **1,416** starting files, all five root contracts and 34 baseline hashes. Registered fixture server trees stopped, 8011–8017 clear, owner 8006 remains listening; `cleanup.json`, final `manifest.json` |

The [manual access matrix](access-matrix.md) separates observed keyboard/layout/semantics from assistive speech and physical hardware. Root text 200% is not native zoom. Seven target widths plus 959/960/1023 boundaries and 2560, short landscape, text-spacing overrides, runtime/load reduced motion, no-JS/touch alternatives, real async intermediate/failure/retry, original-on-demand and negative publication/media assertions stay beside pixels. Actual JSON/safe retry/Retry-After and Laravel maintenance middleware are verified in PHP against temporary storage; browser status scenes are real isolated HTTP errors, not numeral edits.

## Contrast method and limits

`contrast.mjs` captures actual and background PNGs with only glyph fill transparent; it retains photographs, gradients, material, text shadow and layout. `contrast.py` uses foreground/ancestor alpha and identifies pixels fitting at least 75% glyph coverage with a bounded residual. It reports the minimum background ratio at those glyph positions, retaining the more conservative whole-rectangle minimum separately. Screenshots decode already assigned derivatives only for deterministic review; this is not performance sampling or a lazy-loading change. Closed/hidden/inert/disabled content is excluded. Unmeasured text is never silently counted as passing.

The original rectangle diagnostic (`contrast-rectangle-diagnostic.json`) included underlines, decorative strokes and invisible/inert/collapsed text. The first glyph pass (`contrast-glyph-before.json`) retained 20 actual failures; the first state diagnostic (`contrast-states-before.json`) retained pending-result dimming and compact Reset failures. They prompted the bounded repairs above. Modal measurements use viewport coordinates/captures so scrolled underlying pages cannot misplace a fixed dialog. Temporary raw screenshot paths in diagnostic input JSON are scratch files and may be overwritten on reproduction; accepted starting PNGs and reviewed pairs preserve actual visual lineage. These bounded samples do not certify every real photograph, native control, assistive output or WCAG criterion.

The first capture helper's inline style tag was correctly rejected by the application's CSP (`contrast.log`). The final helper changes glyph-fill properties through CSSOM in its isolated document; CSP and application security settings are unchanged. `contrast-states-before.json` is diagnostic history with the earlier full-page modal coordinate method; final modal samples use matching viewport coordinates. Offscreen horizontal drawer tabs and the clipped last city, along with unfocused skip links, remain explicitly unmeasured in those captures; staged-tab/focus behavior is exercised separately.

The later `focus-contrast.mjs` compares painted solid outline pixels against the same focused viewport with only the indicator's outline color made transparent. It retains the new dark backing and all other focused material. `focus-before.json` records low contrast on header/lamp and no painted pixels on the covered card link; its original `width` field is the control-box width, with viewport identified by image filename. A first top-layer frame still met the existing rough mask (`focus-mask-diagnostic.json`); final feedback instead frames the already visible story title. The true click overlay stays below independent actions. No mask/artwork or z-index of clickable targets was changed. This fixes the evidenced hiding rather than accepting a computed-style-only pass.

## Reviewed pixels

Reviewed all 40 separately generated candidate PNGs through five route hero/footer sheets and a state sheet, full-size desktop/compact crops, the six named before/after pairs and final Reset pairs. Materials remain warm/engraved; no source position, photograph, reading order, footer, control box or timing changes. Before acceptance, the real baseline gate rejected five cases (**18 passed / 5 failed**, `visual-pre-review.log`); the cabinet case stops at ON, so its printed candidate was also explicitly captured and reviewed.

Exactly six named updates: Home hero at 390/1440/1920, Explore hero at 390, cabinet ON and printed at 1440. [Review/hash lineage](baseline-review.json) retains their original PNGs in `previous-baselines/`; the other **34 starting PNG hashes remain unchanged**. Candidate generation uses the existing assertions/config with a separate output path, never the accepted baseline directory. No tolerance, mask, browser, font, fixture, state or privacy/network assertion was weakened. Final accepted comparisons use updates disabled; historical P0–P3 manifests are not rewritten.

P2's earlier native-select rasterization mismatch did not recur as a failing case: the filtered-empty starting PNG and exact nav-midpoint hashes remain unchanged. Its original platform/font-timing trigger is still unattributed; no tolerance change or replacement crop was used to conceal it.

The first final comparison process was interrupted after 12 logged cases; its tool session disappeared while the registered isolated PHP servers remained alive. `visual-interrupted.log` is retained as an incomplete run, not a PASS. Source/build and fixture listeners were checked before restarting only unfinished work; completed full/focused suites were retained. Performance sampling had not started, so no cross-interruption timing sample is reused.

The first integrated recorder checked readiness before the keyboard-triggered retrieval navigation committed, so an old document could satisfy the ready attribute and a new disabled gallery button received activation too early (`record-initial.log`). The helper now waits for the actual href, document load and UI readiness, and asserts the viewer is open before checking its close/focus behavior. No application wait, timeout relaxation or viewer-focus repair was added for that harness race.

Final raw videos retain normal navigation/startup, including initial blank/partially loaded artwork frames; these are real arrival states, not settled design references. Desktop keyboard and compact emulated-touch journeys install, print/retrieve the exact selected Visit, open/close the original viewer, visit Place, navigate to Explore, apply a photographic facet, open/close filters, go Back and reach the footer. Short 350ms observation holds do not slow application work. Decoded fraction samples aid review and do not replace the unedited recordings or physical-device testing.

## Performance investigation

**Investigation complete within this lab; no blanket performance or field pass.** Genuine earlier regressions remain historical evidence: P1 desktop Paint +20.8% / style +21.0%; P2 warm desktop LCP +14.2%, Paint +19.4% / style +25.1%; P3 Place cold LCP +14.3% compact / +21.4% desktop. Those results are retained, not relabeled as fresh PASS results. New raw data: [48 production arrivals](navigation.json), [48 paired interaction traces](interactions.json), [48 Place presentation arrivals](place.json), and [summary/ranges](performance.json). Every cell has three samples; paired Explore traces additionally record three cold/warm arrivals per client/width/CPU cell. No sample was discarded post hoc.

Windows Chromium **156.0.8078.4**, scale 1, 390/1440×1000, id-ID/Asia/Jakarta, fixed `photo-v1`, unthrottled loopback. Production navigation uses reduced motion except normal-motion Explore, matching the earlier route protocols. Cold is a fresh context; warm is the next same-context navigation. Observe assigned above-fold images without changing native `loading`/`sizes`, then retain a 1s arrival observation. The paired interaction setup decodes assigned derivatives before tracing to isolate interaction work; it is not arrival/network evidence. All producers and final contrast/video outputs are tied to build `128df10b610e835c3603c6dc57a65401c6bb1d496649fa18acea270352092639`.

| Production route / width | Cold / warm LCP median (ms) | Maximum arrival shift sum | Completed image bytes, cold / warm |
| --- | --- | --- | --- |
| Home / 390 | 2060 / 1964 | .054873 | 1,312,796 / 1,312,796 |
| Home / 1440 | 1396 / 1268 | .000120 | 1,566,542 / 1,566,542 |
| Explore / 390 | 612 / 644 | .002725 | 367,091 / 367,091 |
| Explore / 1440 | 1568 / 2340 | .020992 | 498,875 / 498,875 |
| Visit / 390 | 628 / 552 | .013970 | 182,123 / 182,123 |
| Visit / 1440 | 504 / 472 | .003150 | 500,825 / 364,163 |
| Place / 390 | 500 / 460 | .015883 | 304,577 / 304,577 |
| Place / 1440 | 516 / 480 | .001598 | 486,617 / 486,617 |

Image accounting includes completed cabinet/atmosphere/SVG assets as well as protected derivatives; raw zero-byte entries remain. No arrival original URL. These are completed-arrival totals, not all lazy images or a production byte budget. Visit's warm totals retain the previously documented timing/cache difference rather than inventing identical arrival request sets. The largest Home candidate is the existing bronze cabinet background. No new texture, derivative size, font or runtime library was added.

| Paired native-time interaction, 1× CPU | Paint median old → current | Style-update median old → current | Interpretation |
| --- | --- | --- | --- |
| Home / 390 | 17.314 → 17.649ms | 25.031 → 23.806ms | +.335ms paint; no >10% regression |
| Home / 1440 | 16.307 → 15.836ms | 19.425 → 23.097ms | Style +3.672ms / **18.9%**; paint increase not reproduced |
| Explore / 390 | 3.586 → 4.087ms | 4.880 → 5.477ms | Paint +.501ms / **14.0%**, style +.597ms / **12.2%** |
| Explore / 1440 | 3.266 → 2.913ms | 4.527 → 5.970ms | Style +1.443ms / **31.9%**; paint increase not reproduced |

The >10% style/paint cells are explicitly retained. Native 1× median p95 frame intervals stay 16.7–16.8ms, maximum gaps 16.8ms, and all **48 traces record zero layout-shift entries**, including recent-input entries. Source/trace review bounds the extra work to seating/contact, validated region replacement, intent/applied feedback, focus and the finite visible-card entrance; there is no new continuous loop or viewport filter. Small absolute costs and overlapping ranges do not support a causal speed claim or hiding the percentage increases.

At **synthetic 4× CPU**, Home median p95 stays about 16.7–16.8ms but both clients have ~50ms maximum gaps. Explore is materially less smooth in both: compact p95 median **100→83.3ms**, desktop **116.6→116.6ms**; current maximum gaps **150/166.6ms**. Current sampled Event Timing durations peak at **48ms**, with input delay at most **21.8ms**; these activation samples are not field INP and do not erase asynchronous frame gaps. Physical slower-device/GPU attribution remains unverified.

The earlier **P2 desktop warm-LCP +14.2%** is not stable in the paired client repeat: **2124→2128ms (+.2%)** at 1×, **2632→2756ms (+4.7%)** at 4×. The 1× median navigation TTFB is **1271→1277.8ms**; the production warm range is **2124–2812ms**. Visible derivative selection is identical in every paired cell. Completed offscreen/zero-byte timing sets still vary, so exact whole-arrival byte equality is not claimed. A serialized local PHP server plus native image-request scheduling is a plausible contributor to TTFB variance, not a proven exclusive cause; this is not a clean field speed pass.

The earlier **P3 Place cold-LCP +14.3% / +21.4%** is also not reproduced consistently by same-response presentation pairs: compact **556→544ms**, desktop **632→544ms** at 1×. Compact warm is **536→564ms (+5.2%)**, with TTFB **227.9→257.6ms**; paired shift maxima are identical. At 4×, current cold medians are **784/716ms** and warm **644/604ms**. These are client-presentation diagnostics with identical current server/query work, not a replacement for the genuine P3 before/after or proof of a chronology speed gain.

Remaining arrival movement is real. Raw sources identify font swaps in header/facts/cards and compact Home's headline/scene. [Controlled font requests](font-diagnostic.json) reproduce Home title height **129→86px** and scene **−43px** at 390; desktop geometry stays fixed. Home geometry CSS is byte-identical to the P4 starting tree and font declarations are unchanged, supporting an existing font-metric/wrapping limitation rather than a P4 geometry change. The measured compact Home shift sum **.054873** remains in the report. P3's existing held-font chronology check also passes; no zero-CLS or native font/zoom claim follows. Unlike the earlier Visit startup outlier, all current arrival ranges are retained without removal; the small repeat cannot exclude future startup tails.

`measure.mjs navigation|interactions|place` refuses to overwrite previous outputs. It records the browser/build, fixed fixture/network, cold/warm contexts, LCP candidate, arrival shifts/sources, completed encoded media bytes and native-time traces. Paired old/current JS is compiled with the same installed esbuild against final markup/CSS/server; the old source is `21e443a7`. Place presentation pairs use the same current response/query work and restore only prior ungrouped card structure. These diagnostics do not replace genuine historical before/after. Synthetic 4× CDP CPU slowdown is not physical hardware. Event Timing samples are not field INP. Arrival shift sums are bounded navigation observations, not long-session field CLS; completed arrival bytes are not a whole-story byte budget.

## Unavailable evidence and completion boundary

Native zoom was attempted with headed Chromium. Its initial metrics and repeated returned-window ownership error are retained in `native-zoom-attempt.json` and `native-zoom.log`; **no native zoom input was sent**, and the wait was interrupted. Firefox/WebKit launch probes found absent executables (`engines.json`). No browser or assistive software was installed. Physical Safari/iOS/Android, screen-reader speech/switch/voice/Braille, native page/text zoom, production deployment/cache/load/media and field LCP/INP/CLS remain **unverified**. Field targets 2.5s / 200ms / .1 remain targets, not certification.

No commit, push, deployment, owner maintenance or new creative experiment. Completion is limited to the available isolated gates and explicitly reviewed repairs; external evidence remains a release/UAT limitation.

## Reproduce without replacing history

Use the retained fixture servers listed above and the existing helpers from P0/P3; never prepare an owner-configured target. Run functional, capture and performance jobs sequentially. Archive prior P4 run artifacts before reproduction; measurement JSON producers refuse overwrites, and baseline acceptance must never be rerun just to silence a mismatch.

```powershell
npm run build
php artisan test --compact
node --test tests/Unit/vending-state.test.mjs
$env:FOODIE_QA_OUTPUT = 'docs/qa/p4-integrated/legacy-rerun'
npx playwright test
npx playwright test --config playwright.visual.config.js --update-snapshots=none
node tools/prove-visual-baseline.mjs docs/qa/p4-integrated
node docs/qa/p4-integrated/record.mjs
node docs/qa/p4-integrated/contrast.mjs
node docs/qa/p4-integrated/contrast.mjs --states
node docs/qa/p4-integrated/focus-contrast.mjs
node docs/qa/p4-integrated/measure.mjs navigation
node docs/qa/p4-integrated/measure.mjs interactions
node docs/qa/p4-integrated/measure.mjs place
node docs/qa/p4-integrated/summarize.mjs
git diff --check
node docs/qa/p4-integrated/integrity.mjs
```

`integrity.mjs` verifies final build/capture/sample linkage, passing gates, the five unchanged root contracts, explicit changed-source/baseline scope and preservation of older QA. Its final manifest records the actual artifact hashes; inspect replacement visuals before regenerating it.
