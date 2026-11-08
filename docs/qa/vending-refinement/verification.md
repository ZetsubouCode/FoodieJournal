# Refinement QA · 7 November 2026

Implemented and visually inspected the off/on desktop and compact states against the established cinematic cabinet. [Changes and rationale](../../vending-machine-refinement.md).

Executed checks:

- `npm run build`: passed. Existing public asset URLs remain runtime references; no packages added.
- `node --test tests/Unit/vending-state.test.mjs`: 10 passed.
- `php artisan test --compact --filter='VendingMachineTest|CinematicPublicTest|PublicTest|DiscoveryMachineTest'`: 27 passed, 355 assertions. [Log](feature-tests.txt). Isolated SQLite; owner data untouched.
- `FOODIE_BROWSER_URL=http://127.0.0.1:8012 npx playwright test tests/Browser/vending-machine.spec.js --reporter=list`: 18 passed. [Log](browser-tests.txt). Dedicated cinematic.sqlite and demo media, not owner records.
- Final CSS recheck with `--grep 'responsive geometry|resting tube|refined hardware'`: 3 passed after compact off-state/boot and first-paint refinements. [Log](visual-accessibility-recheck.txt).
- `git diff --check`: passed.

The browser suite exercises release-only installation, valid/invalid removal, cancellation, keyboard and touch, persistence/first paint, error/retry and stale requests, printed record identity, actual retrieval links, reset, runtime reduced motion, image failures, the discovery hint and JavaScript-free links. New checks verify absence of pagination widgets, resisted sub-threshold dial travel without a page change, indexed release positions, one page per crossed notch, forward/backward wrap direction, dock containment and off-state tray opacity. Automated axe scans passed in powered, printed and off states at desktop/mobile. The reset press test now scrolls the repositioned button into view before dispatching physical pointer input.

Visual inspection found and corrected a compact-only issue: hiding the whole grid also hid its physical frames. Off now retains the empty slot hardware, while photo/code layers are dark; boot wakes only their contents. The tray has an early off-state CSS rule so startup does not briefly expose its warm interior. The final three checks above passed after those changes.

Evidence: [off desktop with pickup hint](off-desktop.png), [powered desktop](on-desktop.png), [off compact](off-mobile.png), [powered compact](on-mobile.png), [viewport/error observations](evidence.json). No overflow at 320, 375, 768, 1024, 1440 or 1920px; no page errors in capture. Generated dock/reset SVGs replace the old parts without modifying the approved cabinet bitmap. Screenshots use labeled fictional demo visits and bundled illustrative photography.

The revision is a layered 2D mechanical treatment, with no new animation engine or runtime third-party assets. Physical iOS/Android, Safari/Firefox, pen hardware, actual screen readers and production field performance were not tested in this pass. No new field-performance or full WCAG-conformance claim is made. The broader 34-case browser and 66-case PHP results from 6 November remain historical; this pass reports the specific checks actually rerun.
