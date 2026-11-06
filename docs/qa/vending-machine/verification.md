# Playable vending machine verification · 6 November 2026

Implemented against the supplied package and original reference. [Interaction architecture and change inventory](../../vending-machine-interactive.md). All database work used SQLite memory or the dedicated browser/cinematic fixtures. No owner data, system schema, private environment, route schema or dependency was changed.

## Executed checks

| Command | Actual result |
| --- | --- |
| `php artisan test --compact` | **66 passed, 1,126 assertions**; isolated SQLite memory. [Log](php-tests.txt) |
| `php artisan test --compact --filter='VendingMachineTest\|CinematicPublicTest\|PublicTest\|DiscoveryMachineTest'` | **27 passed, 355 assertions** on the final Blade/data changes |
| `node --test tests/Unit/vending-state.test.mjs` | **10 passed**: minified CSS units, angular normalization, wrap, expanded hit zones, release/commit guards, reverse operation, off controls and print identity/locks. [Log](unit-tests.txt) |
| `npx playwright test --reporter=list` | **34 passed (4.4 minutes)**, Chromium on dedicated 8011 fixtures. [Full log](browser-tests.txt) |
| Focused keyboard and compiled-duration recheck | **2 passed** after adding keyboard focus restoration; verifies blocked storage, Enter/Space/Escape and actual compiled 270/960/820ms motion durations. [Log](keyboard-duration-recheck.txt) |
| `php vendor/bin/pint --test` with controller, vending service and three affected feature-test files | **Passed** for the five explicitly checked files |
| `npm run build` | **Passed**. Archive module 16.56 KB / 5.58 KB gzip; no new dependencies. Public asset URL warnings remain runtime references and load successfully. [Log](build.txt) |
| `git diff --check` | No whitespace errors; Git reported existing CRLF normalization notices |
| `node tools/capture-vending-machine.mjs` | **22 new screenshots**, two retained before screenshots and a short real-time WebM. No browser errors, no overflow at 320/375/768/1024/1440/1920; tube/socket centres differ by less than 0.3 CSS px. [Evidence](evidence.json) |
| `node tools/measure-vending-motion.mjs` | Separate warm, unrecorded timing sample. [Measurements](motion-measurements.json) |

The final browser suite covers fresh off state, no power while holding, forgiving edge release, invalid return, successful/failed removal, cancel/capture/Escape, secondary pointer, scroll/resize/visibility, persistence and pre-module first paint, both dial directions and angular wrap, rapid/stale/failing requests and retry, reset depression, selected photo identity, single-sheet printing and actual visit retrieval, Back, real touch drag and tap alternatives, runtime reduced motion, blocked sessionStorage, one-time hint, now-empty server response, photo failure and JavaScript-free links. It also retains Explore/filter/history, editorial, shared-card, admin/publish, gallery/viewer and HTTP-error regressions.

Automated WCAG 2/2.1/2.2 AA axe scans passed in off, powered and printed states at desktop/mobile, alongside keyboard and 200% text reflow. These are automated checks, not a full conformance certification.

The exact focused formatter and final browser recheck commands were:

```powershell
php vendor/bin/pint --test app/Services/VendingMachine.php app/Http/Controllers/PublicController.php tests/Feature/VendingMachineTest.php tests/Feature/DiscoveryMachineTest.php tests/Feature/PublicTest.php
npx playwright test tests/Browser/vending-machine.spec.js --grep 'keyboard lamp actions|compiled CSS durations' --reporter=list
```

## Visual evidence

| Stage | Desktop | Compact |
| --- | --- | --- |
| Before | [Previous cabinet](before-desktop.png) | [Previous compact composition](before-mobile.png) |
| Off / front-left resting tube | [Fresh session](after-off-desktop.png) | Hardware is recomposed above the windows, with cradle beside socket |
| Powered | [Cabinet](after-on-desktop.png) | [375px](after-on-375.png), [320px](after-on-320.png), [768px](after-on-768.png) |
| Print ready | [Selected real visit](after-print-desktop.png) | [375px with reserved paper clearance](after-print-375.png) |

[Held magnetic preview](magnetic-preview-held.png), [early ignition](post-ignition.png), [displays waking](displays-awake.png), [early paper feed](print-feed-early.png), [later paper feed](print-feed-late.png), [reset face pressed](reset-pressed.png), [second page](page-two.png), [real-time interaction recording](interaction-review.webm).

Compared with the supplied reference, the approved dominant cabinet, brass/charcoal materials, six-window rhythm, large right preview, amber lighting and recessed dispenser remain. The old illuminated tube and two misshapen knobs are replaced by a complete textured instrument rail and independent SVG parts. The initial cradle sits at the front left below the cabinet. Compact hardware remains tangible and readable, and printed paper cannot cover the preview. The windows now represent actual visits rather than implying a category is a specific visit; the package explicitly permits concrete visit slots. Explore's existing category/filter navigation remains available separately.

## Performance and limits

The inherited whole-cabinet drop-shadow was moved onto the static shell. The final local comparison measured a 16.7ms median and 16.8ms p95 frame interval for the current printing variant; the retired filtered-container variant measured 50.1ms p95. One sample per variant is sensitive to local load and is not proof of fixed frame rate on all devices. Raw maximum intervals are retained in the JSON. No video ran during this timing sample.

The full-suite local paint sample recorded LCP 760ms and CLS 0.0031 at 1440×1000. [Paint/contrast evidence](paint-measurements.json). No field INP or production Core Web Vitals claim is made. Seven vector assets total 28,374 bytes; the existing cabinet bitmap and fonts are reused.

Physical iOS/Android, Safari/Firefox, pen hardware and an actual screen reader were not exercised. Audio was optional and is omitted. The art remains a deliberate layered 2D reconstruction; no original layered illustration was available. MySQL-specific tests were not rerun for this UI pass; the full PHP suite used isolated SQLite. The owner's database was neither seeded nor refreshed.

## Corrections found during QA

Mobile tube coordinates were corrected to use the actual hardware offset parent. Release checks now compare scroll position directly, covering a scroll event that arrives after pointerup. CSS duration parsing now accepts the seconds emitted by Vite minification, preventing near-instant snap/boot/printing. A new empty fragment replaces the old preview instead of leaving stale visit content. Keyboard placement/cancellation returns focus to the tube. Image load/error handlers detach when finished; page lifecycle work is abortable.

Earlier exploratory browser runs included an unavailable preview server and an overlapping trace-output collision; they were superseded by the clean, serialized 34-pass run above. The scroll and compiled-duration defects were fixed and explicitly retested. No failed run is represented as a pass.
