# P0-4 — Reviewed visual and state baselines

9 November 2026, Asia/Jakarta. Testing/tools/documentation only; no application UI, domain, route, mechanical or media-pipeline change. Existing uncommitted P0-1/P0-2/P0-3 work was preserved. Reviewed against [navigation](../navigation-hover-progress/verification.md), [wide shell](../wide-shell/verification.md), [readability/states](../readability-states/verification.md), and the current canonical contracts. Earlier QA directories remain dated evidence and were not overwritten; the pixel gate starts at the completed P0-3 implementation, not retrospectively at the pre-P0 audit.

## Reviewed set

**40 PNG baselines in 23 cases** under `tests/Browser/__screenshots__/public-visual-win32-chromium/`. The required three-width/five-route hero/footer matrix accounts for 30; ten focused state crops complete the set. [Manifest](manifest.json) records every file, SHA-256, dimensions, route, fixture, viewport, state and environment/build inputs.

| Group | Coverage / visual review |
| --- | --- |
| Home / Explore / Visit / Place / real 404 | Arrival viewport (height 1000) and complete footer at **390, 1440, 1920**. Reviewed all 30 in contact sheets: [Home](review-home.jpg), [Explore](review-explore.jpg), [Visit](review-visit.jpg), [Place](review-place.jpg), [error](review-error.jpg) |
| Navigation | Public and standalone error header, actual normal-motion transform paused at **110/220ms**, asserted scale .5 before and after comparison |
| Cabinet | Home arrivals are OFF; full scene crops cover ON and printed second-visit identity. Native aspect/source cap and exact printed ID/link assertions remain |
| Filter / empty / error / viewer | Staged filter at 390/1440 after real count settles; unpublished archive, filtered empty with query/chips/reset, actual failed fragment with retained results/retry, on-demand original viewer and focus restoration |

The ten state crops are reviewed together in [state sheet](review-states.jpg). Full-resolution Home 1440, Visit 390, printed cabinet, filter 390, viewer and nav midpoint crops were additionally inspected. Contact sheets are review aids; the matcher compares the original PNGs. These arrival screenshots intentionally record the current viewport composition, including below-fold continuation, rather than shrinking the cabinet to fit.

No pixels, exception text, IDs, dates, photographs or content are masked. Generated DOM IDs do not affect pixels. Intentional P0-3 caption backings and the existing editorial footer/error scene are preserved. This review approves the current scoped baseline, not all future P1–P4 design targets or accessibility conformance.

## Determinism and safety

`tools/visual-qa.php prepare|serve photo|empty` permits only two fixed dedicated SQLite/media targets beneath ignored `tools/.runtime/`, on loopback **8015/8016**. It validates the database configuration before queries/migrations, never refreshes/deletes data, and asserts 20/0 published visits. The photographic fixture reuses the existing bundled fictional DemoJournalSeeder, with one draft and one private demo excluded. The empty fixture has two hidden visits. Owner `.env`, database, account, uploads, MariaDB/system schemas and the older QA fixtures were untouched.

The server's Carbon date is fixed at **2026-11-09 12:00 UTC** for seed dates/footer year. Browser timers run normally. Guest-only QA uses per-request array cache/session stores, avoiding a frozen-time shared rate-limit cache; this is not a rate-limit/security benchmark. An identifying response header prevents accidental captures of another service on these ports. Built Vite assets are forced through the existing QA environment rather than a dev hot file.

Pinned Windows + Chromium **156.0.8078.4**, device scale **1**, locale **id-ID**, timezone **Asia/Jakarta**, dark scheme, new browser context/sessionStorage per case, fixed viewports, fonts ready, all assigned images decoded. Only the capture harness eagerly loads lazy derivatives to eliminate below-fold loading races; it excludes the unassigned viewer image and asserts that originals are not requested until explicit viewer open. Staged filter counts settle on the real server response before capture. The manifest pins the installed Playwright/Node/OS, build manifest, fixture source and local fixture hashes; hashes identify the reviewed run, not a requirement to reuse random media UUIDs on a fresh fixture.

Default comparisons use reduced motion. `animations: 'allow'` prevents screenshot auto-fast-forward. Only the two designed nav midpoint cases use normal motion and pause their actual local transition. No global animation rewrite is injected into snapshots.

## Pixel tolerance and negative control

`playwright.visual.config.js` sets `threshold: .15` (per-pixel color difference) and **maxDiffPixels: 30**, without a size-proportional allowance. This keeps small edge/antialias rasterization differences from dominating a large photographic scene while permitting far less than a normal text/layout displacement. The same Windows/browser/font environment passed repeated clean comparisons; no tolerance was increased to hide a failure. **Nav midpoint crops allow zero differing pixels**, because a thin partial underline can contain fewer than 30 pixels. Semantic/geometry/network assertions remain beside images; a small pixel allowance does not replace them.

The deliberate negative control changes only the test page's header transform to `translateY(8px)` on Home 1440. `--update-snapshots=none` remains in force. It produced **2,249 differing pixels**, exited **1**, and retained [actual](probe-actual.png) / [diff](probe-diff.png) evidence. Removing the child-process flag and reopening the page passed, exit **0**. [Probe record](probe.json) proves the baseline SHA-256 was identical before and after. No production stylesheet was edited. [Rejected log](probe-rejected.log), [reverted log](probe-reverted.log).

`tools/prove-visual-baseline.mjs` asserts both outcomes and the measured pixel budget; a server error or unrelated failure cannot count as a successful negative control. It never updates snapshots. The normal suite defaults to `updateSnapshots: 'none'`; missing or changed images fail.

## Real-time evidence

[Unmodified machine video](realtime-machine.webm) and [metadata](realtime.json) record normal-motion OFF → tube/socket → ON → second visit → print → retrieve. No clock install, seeking, slowMo, animation override, screenshot fast-forward or video edit is used. A 500ms final observation hold lets the raw recording include the retrieved page; it does not alter application timing. The fixed server date affects content only. The video is separate from deterministic snapshot posing and is not a performance measurement. Representative decoded frames were reviewed for actual loading, tube movement, ignition, printed identity and the retrieved Visit page.

## Executed checks

| Check | Actual result / evidence |
| --- | --- |
| Production build | **PASS**, [build](build.log); no dependency/application source changes |
| Clean pixel comparison, updates disabled | **23 passed / 40 snapshots**, twice: [first](verify-1.log), [second](verify-2.log) |
| Intentional shift / removal | **Expected failure then pass**, 2,249 pixels > 30; unchanged baseline hash, [proof](probe.json) |
| Existing navigation/cabinet contracts | **24 passed**, [regressions](contracts.log), using the dedicated photographic fixture. Covers all five nav cases and all 19 cabinet cases, including compiled timing, source/LED/lamp geometry, exact print identity, cancellation, touch, no-JS, missing photos, reduced motion and axe |
| PHP public/domain/media/security scope | **30 passed, 466 assertions**, [PHP](php.log), SQLite memory/fake media |
| Safety / test separation | Invalid fixture `mysql` rejected before application bootstrap; default functional config lists 52 cases and zero visual cases; [rejected target](rejected-target.log), [functional list](default-test-list.log) |
| Evidence integrity | 40 PNG hashes/dimensions, current build/fixture/font/photo hashes, manifest links, probe hash and raw-video hash verified |
| Syntax / whitespace | New PHP helper lint, new JavaScript syntax, `git diff --check` exit 0; only existing line-ending notices |

There are **47 distinct passing browser cases** across the 23 new visual cases and 24 existing regressions; repeated comparisons/probe reruns are not counted as additional distinct cases. The full PHP/browser suite was not run.

Initial baseline creation exposed a test-harness decode of the viewer's intentionally unassigned image. The image readiness helper now waits only for assigned sources, retaining strict decode failure for real images. [Initial creation log](generation-initial.log), [missing-image completion](generation-completed.log). Missing images were then created explicitly; existing baselines were not blindly overwritten. Visual review preceded the clean comparison/probe runs.

## Reproduce and maintain

From the repository root:

```powershell
php tools/visual-qa.php prepare photo
php tools/visual-qa.php prepare empty
npm run build
```

Serve each in its own terminal:

```powershell
php tools/visual-qa.php serve photo
php tools/visual-qa.php serve empty
```

Then run sequentially:

```powershell
npx playwright test --config playwright.visual.config.js
node tools/prove-visual-baseline.mjs
npx playwright test --config playwright.visual.config.js
node tools/visual-evidence.mjs --reviewed
node docs/qa/visual-baselines/record-realtime.mjs
```

To repeat the unchanged nav/cabinet regressions against this fixture, set `$env:FOODIE_BROWSER_URL = 'http://127.0.0.1:8015'` in that test terminal and run `npx playwright test tests/Browser/public-navigation.spec.js tests/Browser/vending-machine.spec.js --output=tools/.runtime/p0-4-contract-results`. Remove the variable before ordinary functional fixture tests.

The default functional Playwright config excludes this spec, because the visual config owns its pinned environment, separate output directory and baseline paths. Existing functional tests are unchanged. The dedicated fixture setup is independent of owner-configured Artisan. Fresh image processing uses the installed PHP/GD toolchain; review a separate environment set if fonts, renderer, browser or image-decoder versions change.

For an intentional product change: inspect actual/diff first, check geometry/state/network tests and source intent, explicitly regenerate only named approved images using the visual config's `--grep` and `--update-snapshots=all`, review full PNGs/contact sheets, run clean comparisons again, rerun the shift proof and refresh the manifest using its explicit --reviewed acknowledgement. An update command is not approval. Do not regenerate P0-1/2/3 QA folders or increase tolerances to conceal a regression.

## Limits

Windows Chromium only; no Linux/macOS/Safari/Firefox/device baselines, native zoom or screen-reader certification. Only real 404 is a status-page pixel baseline; other statuses retain PHP coverage and the injected browse 500 tests recovery, not the standalone 500 scene. The selected crops are not full-page coverage. No production field vitals, full test suite, larger Place history, P1 mechanics, P2 skeletons or P3 redesign. No commit, push or deployment.

The two visual QA server process trees were stopped after verification; isolated fixtures are retained for reproduction.
