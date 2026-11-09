# P1 — Vending-machine physical response

9 November 2026, Asia/Jakarta. Scoped P1 implementation from clean local `main`, HEAD `21e443a7254ae3a3e72989a83aa06584dba0c005`. Read completed [P0-1](../navigation-hover-progress/verification.md), [P0-2](../wide-shell/verification.md), [P0-3](../readability-states/verification.md), [P0-4](../visual-baselines/verification.md), [P0-H](../p0-hardening/verification.md), design memory, design-system §1 and the locked [mechanical contract](../../motion-interaction.md#home-mechanical-contract). Older QA remains dated history.

## Implemented response

The [pre-implementation storyboard](storyboard.md) chooses one refinement: weighted socket seating. Only valid cradle-to-socket installation in `archive.js::settle()` adds the final 4px seating stroke. At .72 of **eased progress** the tube is 4px above its existing anchor; at .86 it seats exactly while the socket image flexes to scale(1.035, .985); by 1 the plate recovers. These are authored progress offsets, not wall-clock percentages. Both effects share the existing **270ms** snap and easing, replacing the uniform approach and separate 125ms post-snap plate tween. The **960ms** boot follows seating; **820ms** print is unchanged.

The production diff adds nine lines and removes three, including the final newline. Existing motion helper, animation set, reducer and interruption handlers retain ownership. No new legal state/transition requires a reducer change. Invalid return and reverse/off keep their direct path; cancellation never flexes the plate. Existing pointer contact changes lifted material by the next rendered frame; the new test observes that frame. No new control, hint, announcement, idle loop, dependency, filter, geometry, template, route or data change.

Exact selected/printed visit ID, photo and link remain frozen through print/retrieval. `clearPrint()` still clears immediately on selection/page/reset/committed off. Optional retraction was evaluated and omitted to keep one physical improvement without adding another concurrency boundary. Square reset, 32° commitments, <9° residual, 160ms detent, six actual visits, outboard RESET-height tube, original socket and LED anchors remain.

## Isolated execution and results

Retained dedicated fixed-date photographic/empty SQLite and media fixtures were served on loopback **8015/8016** using `tools/visual-qa.php serve photo|empty`; identity headers asserted. They contain 20/0 published visits with private/draft exclusions. No preparation, migration, reseed, owner environment/account/media access, MariaDB/system-schema action, commit, push or deployment. PHP uses guarded SQLite memory/fake media.

| Gate | Actual result / evidence |
| --- | --- |
| Initial unchanged reviewed gate | **23 passed / 40 PNGs**, updates none; [before](visual-before.log) |
| Production build / generated error CSS | **PASS**, [build](build.log); no dependency/token/CSS change |
| Pure reducer | **10 passed**, [Node](reducer.log); no new event |
| All existing cabinet cases | **19 passed**, [combined run](browser.log); original cases unchanged |
| New seating cases, final source | **5 passed**, [final](browser-final.log): desktop/compact contact and posed geometry; hidden/resize/runtime reduction; exact print/off after interruption; reduced-load/no-WAAPI seating; pending-fetch power-off; keyboard printing at 200% root text |
| PHP public/media/security/foundation scope | **30 passed / 402 assertions**, [PHP](php.log) |
| Final reviewed visual gate | **23 passed / 40 PNGs**, updates none; [final](visual-final.log). Baselines/masks/tolerances/existing assertions unchanged |
| Matcher negative control | **Expected rejection then clean pass**, 2249 differing pixels > 30; [proof](probe.json), [rejected](probe-rejected.log), [reverted](probe-reverted.log); identical baseline hash |
| Rendering comparison | Six genuine normal-time samples before and six after, three per 1440/390 width; [before](before.json), [after](after.json), [comparison](performance.json), compressed raw CDP traces in this directory |
| Normal-time complete journeys | [1440 mouse-drag video](realtime-1440.webm), [390 touch tap-target video](realtime-390.webm), [identity/hash metadata](realtime.json): forward, select, print, retrieve actual Visit, Back, print again, reverse/off clears paper |
| Integrity / syntax / whitespace | [Manifest](manifest.json) checks 40 original reviewed hashes, current inputs, poses/samples/videos; JS syntax checked; [diff check](diff-check.log) exit 0, existing CRLF notice only |

**47 distinct passing browser cases**: 19 existing cabinet + five new + 23 visual. Repeats/probe reruns are not additional cases. The initial combined run had one new assertion failure: it assumed <.05px centre error, but existing integer offsetWidth/offsetHeight pose computation yields .133px source rounding. Final assertions permit <.5px, stricter than existing 1–2px checks; authored 4px travel and plate scale are checked. No production geometry or pixel tolerance changed to fix this test. The initial log is retained with its failure.

## Visual review and capture validity

Opened actual [seating](review-seating.jpg) and [contact](review-contact.jpg) sheets, full-size [desktop](after-1440-0.86.png) and [compact](after-390-0.86.png) contact frames, and decoded raw-video frames at both widths. The stroke is local and restrained, the plate accepts the tube before ignition, and settled source positions/materials remain. This is scoped design judgment with geometry/state checks, not a generic quality score.

Posed lift/start/.72/.86/end captures pause/seek **only the two local WAAPI effects**, inverting native easing for authored offsets. Desktop capture is 1440×1000; compact posed capture is 390×1800 so the whole tall component fits without screenshot-driven viewport resizing. Poses are not timing/performance measurements. Real-time traces/videos use 1440/390×1000, normal clocks, no seeking/slowMo/animation rewrite. Fixed server date affects content only. Videos are unedited; [decoded desktop](review-video-1440.jpg) / [compact](review-video-390.jpg) sheets are review aids. Observation holds on actual retrieved/final off pages do not alter app timing.

An initial 390×1000 element screenshot resized the viewport while armed, correctly triggering cancellation. Five invalid baseline compact poses were discarded, documented in `before.json`, and excluded from review. Final after poses use the fitting viewport and assert both installation effects. All six baseline real-time traces had completed ON without screenshot posing and remain valid. The unchanged pre-change 390px gate supplies the reviewed resting composition; no invalid compact before/after pose comparison is claimed.

## Measured rendering cost

Windows Chromium **156.0.8078.4**, scale 1, id-ID, Asia/Jakarta; same fixture/fonts/browser and unthrottled local runtime. Traces ran separately from other browser gates, covering actual socket activation through ON including existing boot painting. This is not navigation LCP, field INP, physical touch latency or GPU power usage.

| Median of three samples | 1440 before → after | 390 before → after |
| --- | --- | --- |
| Frame interval / p95 | 16.7 / 16.7ms → 16.7 / 16.7ms | 16.7 / 16.7ms → 16.7 / 16.7ms |
| Paint duration across journey | 15.045 → 18.174ms | 18.969 → 20.329ms |
| Paint events | 65 → 67 | 71 → 73 |
| Layout duration | 5.752 → 5.776ms | 6.489 → 6.012ms |
| Recorded layout shifts, all three samples | 0 → 0 | 0 → 0 |

The **20.8% desktop Paint increase (+3.129ms total)** crosses the investigation threshold. Raw desktop sample-2 traces each have 29 document paints; across all three samples median document paints are 28→29 and total Paint events 65→67. Document paint duration accounts for about 3ms of the difference; non-document paint duration is effectively flat. Median style-update time (UpdateLayoutTree) also rises 19.318→23.372ms, **+4.054ms / +21.0%**, with 89→90 updates. Small absolute costs include existing boot/style invalidation and run variance; these samples cannot fully attribute them to the local effect.

Source review confirms only two existing small hardware transforms are animated; no filter/layout/viewport animation was added. All twelve samples have maximum frame gaps ≤16.8ms, unchanged median/p95 cadence and zero shifts. This investigation supports the bounded scoped refinement; it does **not** claim a paint/style speed improvement or slower-device performance. The measured increase remains explicit for device profiling.

## Reproduction and limits

Serve retained 8015/8016 fixtures in separate terminals, then run browser gates sequentially:

```powershell
npm run build
node --test tests/Unit/vending-state.test.mjs
php artisan test --compact --filter='PublicTest|MediaTest|SystemSecurityTest|FoundationTest'
$env:FOODIE_BROWSER_URL = 'http://127.0.0.1:8015'
npx playwright test tests/Browser/vending-machine.spec.js tests/Browser/vending-seating.spec.js
Remove-Item Env:FOODIE_BROWSER_URL
npx playwright test --config playwright.visual.config.js --update-snapshots=none
node tools/prove-visual-baseline.mjs docs/qa/p1-physical-response
node docs/qa/p1-physical-response/record-realtime.mjs
git diff --check
node docs/qa/p1-physical-response/manifest.mjs
```

Evidence producers overwrite their own outputs. Do not recapture `before` using refined code. `capture.mjs after --frames-only` retains measured samples when repairing only posing. After reproducing videos, inspect them and refresh their decoded review aids before regenerating the manifest. Old P0 evidence remains unchanged. The QA servers started here were stopped after checks; fixtures are retained.

Scoped P1 is implemented and verified here. P2/new real-wait skeletons, P3 and P4 remain pending. Full PHP/default browser suites, native text zoom, physical iOS/Android/Safari/Firefox, screen readers/assistive hardware, field vitals and deployment UAT were not run. Root-font 200% and emulated touch are separate checks; axe/screenshots do not certify WCAG. No general token cleanup or optional paper-retraction feature was added.
