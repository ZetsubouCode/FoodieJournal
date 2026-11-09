# P2 — Explore interaction-led discovery

9 November 2026, Asia/Jakarta. Starting HEAD `21e443a7254ae3a3e72989a83aa06584dba0c005` **plus completed, uncommitted P1 work**. That source and its evidence are preserved. Read completed [P0-1](../navigation-hover-progress/verification.md), [P0-2](../wide-shell/verification.md), [P0-3](../readability-states/verification.md), [P0-4](../visual-baselines/verification.md), [P0-H](../p0-hardening/verification.md), [P1](../p1-physical-response/verification.md), design memory, design-system §1 and current page/motion contracts. This report implements only the roadmap's Explore P2 scope; earlier QA remains dated history.

## Behavior and boundaries

The [storyboard](storyboard.md) retains the photographic category/city/technique bento, directory lenses, crop geometry and semantic mobile order. Local hover/keyboard arrow movement and press compression now lead to a **dashed pending-intent outline**, then a **solid applied-facet outline** only after server commitment. Outlines sit outside the photo so positioned children cannot cover them; focus uses its separate offset/ring. Applied facets are reflected in accessible link names and SSR/no-JS output. The same board DOM remains mounted across query/history changes.

`CuriosityIndex` exposes the value it already selected; selection/photo queries are unchanged. `ExploreState` continues to normalize all tab/search/dimension/sort/view/price/page values. The fragment exposes only that normalized public query state. No second query engine, added selection query, media-processing change or private/master count.

During fragment requests, existing useful results/counts/URL remain with immediate busy/status feedback. Every required replacement region and facet metadata is validated before mutation. Only the latest request generation commits console/results/drawer synchronously. There is no entrance wait before commitment. At most **four currently visible cards** (or a visible empty state) reveal through a 4px/opacity transition using the existing **220ms** token; offscreen cards do not acquire layers. New intent, hidden/pagehide and runtime reduction cancel entrances to the settled underlying CSS state. A response arriving while hidden starts none.

Sort/view/filter controls regain their logical replacement focus; removed chips/pagination and board-to-results navigation use the result heading. User focus moved elsewhere during a wait is respected. A demonstrated bug is repaired: after failed Back rolls the URL back to committed state, a successful retry now restores the originally requested history URL along with its data, without pushing an extra history entry.

Useful committed results are **never covered by refresh skeletons**. Only native, visible, assigned derivative slots still unresolved after **200ms** gain a static, box-matched CSS material placeholder. CSS decoration has no accessibility-tree content; real image alternatives/captions remain. No fake count, placeholder text, image retry loop, minimum display time or original request. Complete/fast images skip it; native load/error removes it immediately and existing shared/Visit-card fallbacks retain ownership. Offscreen lazy slots have no running wait timer. Native `loading`/`sizes="auto"` remain intact; shared image fallback and viewer implementations are unchanged.

P1's exact-visit identity, source geometry, cancellation, print clearing and accessible alternatives are untouched. P3 story/viewer/chronology and P4 integrated device/access/field gates remain pending.

## Safe execution and actual checks

Only retained isolated fixtures were served: **8011** functional QA, **8012** photographic caption QA, **8013/8014** private-only/sparse QA, and fixed-date **8015/8016** photographic/empty visual QA. New capture/tests assert `photo-v1`/`empty-v1` identity where used. No fixture preparation/reseed/migration, owner environment/account/media access, owner database/3306/system-schema action, commit, push or deployment. Guarded PHP uses SQLite memory and fake media. Guest checks do not log in or edit the owner journal.

| Gate | Actual result / evidence |
| --- | --- |
| Initial unchanged reviewed gate | **23 passed / 40 PNGs**, [before](visual-before.log) |
| Production build | **PASS**, [build](build.log); existing installed dependencies only |
| PHP discovery/public/same-item/media/security scope | **41 passed / 550 assertions**, [PHP](php.log). Existing photo relationship/publication and query dimension/price/null checks remain |
| Changed PHP format | **PASS**, [Pint](pint.log); no unrelated whole-repo format sweep |
| Pure machine reducer | **10 passed**, [Node](reducer.log); no reducer edit |
| Existing discovery + new response + P1 seating regressions | **21 passed**, [final release](release-regressions.log): six existing discovery cases, ten new response cases, five preserved P1 seating cases |
| Existing staged filter/history | **1 passed**, [staged filter](staged-filter.log), including actual count/Cancel/Apply/Back |
| Existing readability/partial/image/privacy scope | **6 passed**, [readability](readability.log), including all-photo failure, private/sparse data, long chips, enlarged captions and no-JS access |
| Final reviewed pixel gate | **23 passed / 40 PNGs**, [release](visual-release.log), updates disabled; 39 original hashes unchanged, one named native-select crop reviewed below |
| Negative control | Intentional 8px Home header shift must fail, clean removal must pass; [proof](probe.json), [rejected](probe-rejected.log), [reverted](probe-reverted.log); no production mutation |
| Actual intermediate states | **40 captures** at 375/768/1440/1920, [states](states.json), plus six local start/mid/end [poses](poses.json) at desktop/compact |
| Normal-time journeys | [1440 keyboard](realtime-1440.webm), [375 touch](realtime-375.webm), [metadata/hashes](realtime.json): board, staged price Apply, list, actual failed request/retry, Back and actual Visit navigation |
| Comparable lab samples / network investigation | Three cold/warm arrivals and navigation traces per width before/after; [comparison](performance.json), [before](before.json), [after](after.json), raw compressed CDP traces and [controlled media audit](network-investigation.json) |
| Integrity / syntax / whitespace | [Manifest](manifest.json) checks all hashes, final-build capture/sample linkage, preserved P1 source and explicit baseline lineage; JS syntax and [diff check](diff-check.log) pass |

There are **51 distinct passing browser cases** across 21 + 1 + 6 + 23. Repeated attempts/probe reruns are not additional cases. New cases exercise held/stale requests, invalid/missing fragment regions, malformed facet metadata, offline/retry, failed Back/retry/Forward, actual timeout, pending/applied distinction, fast/delayed/failed images, bounded visible entrances, hidden/runtime reduction, 200% root text, keyboard/touch/no-JS and a pending-photo axe scan. No WCAG certification follows.

Initial logs remain honest history: [first run](browser-initial.log) had a test that changed sort with `selectOption()` without first focusing it; corrected to model keyboard use. A later combined run timed out its five-width/touch/no-JS case at 45s under concurrent capture load; its isolated rerun passed in 33s, and the combined-journey test now has an explicit 90s bound. Capture readiness was separated from actual application waits, with a 15s assertion bound matching the existing request budget. Final release gates run sequentially. No application response delay or matcher tolerance was increased.

## Reviewed visuals and baseline lineage

Opened the [four-width review sheet](review-states.jpg), full-size desktop/compact pending/applied/photo-wait states, local result start/mid/end frames and decoded keyboard/touch recordings. The board remains photographic and asymmetric, pending/applied/focus treatments stay distinct, captions remain real/readable, and placeholders reserve actual photo boxes while text stays useful. These are scoped design judgments with named geometry/state assertions.

The first full pixel comparison passed **22/23 cases**, rejecting **262 pixels** in the filtered-empty 390px crop. [Original PNG](filtered-empty-before.png), [reviewed actual](filtered-empty-reviewed.png), [diff](filtered-empty-diff.png), and enlarged [expected](select-expected.png) / [actual](select-actual.png) show a **one-pixel vertical change in native sort-select text**, confined to `(18,260)–(136,276)`. Font, 44px box, padding, labels, values, options and actions are unchanged. All other pixels in that crop match. The precise Chromium initialization/rasterization trigger was not established; font-timing/metadata probes were diagnostic, not accepted production workarounds.

After actual/diff inspection and control/behavior review, **only this named crop was explicitly refreshed**, using `--grep 'unpublished archive' --update-snapshots=all`; the companion unpublished crop retained its hash. [Named update](visual-named-review.log). Original bytes/hash are retained here for historical lineage. No mask, threshold, maxDiffPixels, viewport, fixture, font, browser pin or existing assertion changed. Final clean comparison uses `--update-snapshots=none`. This is an explicitly reviewed baseline change, not 40 unchanged-PNG claims or an automatic update to silence a failure.

Local posed frames pause/seek only the bounded card WAAPI effects at 0/110/220ms. They are not performance evidence. State captures may eagerly decode already assigned derivatives for deterministic screenshot readiness; application loading remains native lazy. Photo-wait screenshots use actual outstanding intercepted derivative requests and static reduced-motion material. Normal-time videos/traces use no clock, seeking, slowMo or effect rewrite; observation holds on actual error/Visit states do not slow application work. Fixed server date affects fixture content only. Raw videos are unedited; decoded sheets are review aids.

## Rendering/network cost and limits

Windows Chromium **156.0.8078.4**, scale 1, local id-ID/Asia-Jakarta, same isolated fixture/browser/fonts; no throttling. Baseline is the actual completed P1 build. Six native navigation traces before and six after cover three cold/warm arrivals per 1440/390 width. [Final values/ranges](performance.json) retain the raw results rather than a field-vitals claim.

| Median of three samples | 1440 before → after | 390 before → after |
| --- | --- | --- |
| Cold arrival LCP | 2628 → 1868ms | 748 → 664ms |
| Warm arrival LCP | 2920 → 3336ms | 952 → 920ms |
| Navigation trace Paint | 31.570 → 37.692ms | 32.015 → 28.106ms |
| Style updates | 6.240 → 7.806ms | 5.990 → 6.631ms |
| Median / p95 frame interval | 16.7 / 16.8 → 16.7 / 16.8ms | 16.7 / 16.8 → 16.7 / 16.7ms |
| Maximum arrival CLS | .02099 → .02099 | .00272 → .00272 |
| Interaction LayoutShift trace events | 0 → 0 | 0 → 0 |

**Desktop warm LCP is 14.2% slower (+416ms)** in this small sample; desktop Paint increases **19.4% (+6.122ms)** and style work **25.1% (+1.566ms)**. Those crossed the contract's investigation threshold. The trace has bounded extra intent/selection/card paints, no new continuous loop, animated filter, large promoted result grid, dependency or query. Frame cadence remains similar, although the final maximum desktop gap rises from 16.8 to 33.4ms; compact maxima are 33.4/33.5ms. No new recorded layout instability. The warm desktop timing increase is retained as a performance limitation, not labeled a clean speed pass. Slower-device and field attribution remain unverified/P4.

Investigation caught and removed eager promotion of visible lazy images, which would disable responsive `sizes="auto"` eligibility. Final source leaves `loading` unchanged. Arrival-time resource sums include only already-finished lazy requests and are **not a total-byte budget**. A separate controlled audit decodes every visible image before accounting and compares old versus P2 clients against the same markup/style/fixture: **identical selected derivatives, loading/sizes attributes and completed media URL/byte sets at both widths**. [Audit](network-investigation.json). No original request was observed by negative network tests. This resolves image-selection concerns; it does not explain away the measured LCP difference or substitute for the real before/after lab samples.

## Reproduce and completion boundary

Use the existing documented fixed QA helpers on the ports above, without owner-configured preparation. Build first, then run browser/capture gates sequentially:

```powershell
npm run build
php artisan test --compact --filter='CinematicPublicTest|PublicTest|DiscoveryMachineTest|VisitCardTest|MediaTest|SystemSecurityTest'
php vendor/bin/pint --test app/Services/CuriosityIndex.php tests/Feature/CinematicPublicTest.php
node --test tests/Unit/vending-state.test.mjs
npx playwright test tests/Browser/discovery-machine.spec.js tests/Browser/explore-response.spec.js tests/Browser/vending-seating.spec.js
npx playwright test tests/Browser/journal.spec.js --grep 'filters stage'
npx playwright test tests/Browser/readability-states.spec.js
node docs/qa/p2-discovery/capture.mjs
node docs/qa/p2-discovery/record.mjs
node docs/qa/p2-discovery/pose.mjs
npx playwright test --config playwright.visual.config.js --update-snapshots=none
node tools/prove-visual-baseline.mjs docs/qa/p2-discovery
git diff --check
node docs/qa/p2-discovery/manifest.mjs
```

Do not overwrite genuine `before` evidence with P2 code or rerun any blanket baseline update. Reproducing captures/videos changes hashes; inspect replacement media before regenerating their manifest. Previously completed P1 and older dated QA are retained; only the named reviewed test baseline changed. QA servers started here are stopped afterward; isolated fixtures remain.

**Implemented/verified:** scoped P2 behavior, complete existing discovery/query/history contract, honest photo waits and reviewed Chromium visuals. **Partial:** lab rendering evidence with explicit warm-LCP/paint/style cost increases; sampled automated accessibility. **Pending:** P3 storytelling and P4 integrated access/device/field gates. **Unverified:** native text zoom, physical Safari/iOS/Android/Firefox, screen readers/assistive hardware, owner production load/field INP. Root-font 200%, emulated touch, screenshots and axe do not replace those checks. Full PHP/default browser suites, deployment, viewer/admin placeholders and a new bento geometry rewrite were not performed.
