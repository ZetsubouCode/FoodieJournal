# P0-1 — Shared navigation hover progress

Implemented 9 November 2026 (Asia/Jakarta), from clean local `main` at `bcbd9b7269cfd4d08e34082c1dda6a57f78fac94`. This fixes A01 in the frontend audit. P0-2 and later roadmap tasks remain pending.

## Change and scope

`resources/css/public-chrome.css` now gives primary public navigation one thin copper pseudo-element, scaled from its left edge. It uses existing `--action-primary-bg`, `--motion-standard` (220ms enter) and `--motion-fast` (140ms exit), with a linear progress fill. Native CSS transitions retarget from their current scale when interrupted. No tokens, dependencies, markup, navigation handlers, network waits or routes were added.

Active route and `:focus-visible` show the full underline immediately; the existing keyboard outline remains visible. Reduced motion disables interpolation, including when the preference changes during a transition. Hover motion is restricted to hover-capable pointers; touch and keyboard retain ordinary link behavior. Footer and admin navigation are outside this selector.

`npm run build` regenerated the tracked standalone `public/assets/css/error.css` from the same chrome source, as well as the ignored Vite output. A real 404 exercises that independent bundle without Vite JavaScript. Error copy, scene and recovery actions are unchanged. Owner environment, credentials, database, journal content, uploads and cabinet mechanics were untouched.

## Executed checks

Environment: Windows, existing isolated `tools/.runtime/browser.sqlite` / `browser-media`, `php tools/browser-server.php` on loopback 8011. No seeding or migration was run. Chromium 156.0.8078.4; existing installed Playwright/Vite toolchain.

| Command/check | Result |
| --- | --- |
| `npm run build` | **Passed**; [log](build.txt). Existing runtime `/assets/...` resolution notices remain, no new missing-asset failure |
| `npx playwright test tests/Browser/public-navigation.spec.js --reporter=list --output=tools/.runtime/p0-1-browser` | **5 passed**, 34.5s; [log](browser-tests.txt) |
| `npx playwright test tests/Browser/homepage.spec.js tests/Browser/error-pages.spec.js --reporter=list --output=tools/.runtime/p0-1-regression` | **3 passed**, 16.2s; homepage geometry/200% text/axe, real links and standalone error regression; [log](regression-tests.txt) |
| `php artisan test --compact --filter='CinematicPublicTest\|SystemSecurityTest'` | **9 passed, 190 assertions**, 10.77s; isolated SQLite memory. Remove Markdown's table escape backslashes from the filter alternatives when copying. [Log](php-tests.txt) |
| `npx playwright test tests/Browser/public-navigation.spec.js --grep 'interruptible' --reporter=list --output=tools/.runtime/p0-1-focus` | **2 passed**, 5.6s, after changing the two focus captures to include the full viewport so the crop does not cut the outline; [log](focus-recheck.txt) |
| `git diff --check`, focused documentation links, generated CSS parity, JS syntax | **Passed**; no unrelated source/data changes |

Exact PHP filter command:

```powershell
php artisan test --compact --filter='CinematicPublicTest|SystemSecurityTest'
```

## State and visual evidence

The new browser spec asserts the actual pseudo-element's transform origin, copper color, computed durations and scale; it also checks unchanged link bounding boxes. On Home and real 404, CSS transitions are paused/seeked for deterministic intermediate samples: entry 0ms→0, 110ms→.5, 220ms→1; exit 70ms→.5; re-entry starts from that same .5. Finishing those local transitions permits ordinary keyboard/native navigation checks. This is state/frame evidence, not a performance measurement or pixel regression baseline.

Thirty PNGs are retained here:

- `navigation-{public,error}-{start,mid,end,leave-mid,reenter-start}.png`: local header crops of actual CSS states. [Public midpoint](navigation-public-mid.png), [error midpoint](navigation-error-mid.png).
- `navigation-{public,error}-keyboard-focus.png`: full viewport captures preserve the whole focus ring. [Keyboard focus](navigation-public-keyboard-focus.png).
- `navigation-{home,explore,visit,place,error}-{375,1440,1920}.png`: 15 route/width combinations, hovering an inactive link with the selected route still full where applicable. Visit and Place URLs were discovered from real fixture links, not invented.
- `navigation-text200-375.png`: header at 200% root font size, with no horizontal document overflow; this is not actual browser zoom.
- `navigation-reduced-{public,error}.png`: immediate non-interpolated hover state with reduced motion.

The tests also check active persistence after hover leave, real Tab traversal and outline, native Enter navigation, runtime motion-preference changes, and a touch/no-JavaScript context whose links still navigate. Responsive checking covers Home, Explore, Visit, Place and real 404; ordinary navigation remains available immediately.

Visual inspection covered public start/mid/end, error midpoint, keyboard focus, desktop/mobile shared headers and enlarged-text header. Existing 8 November missing-effect screenshots remain historical in `../frontend-experience/`; none were overwritten.

## Limits

No full PHP/browser suite, fresh field/performance measurement, physical touch device, Safari/Firefox or screen-reader run. Existing axe checks are regression coverage, not WCAG certification. The 200% header check does not fix the separately documented Explore headline readability gap. This task restores shared navigation feedback only; wide-shell/atmosphere/readability and other roadmap work remain pending.
