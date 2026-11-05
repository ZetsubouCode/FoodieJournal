# Explore Discovery Machine

Implementation record for the approved A1 projector handoff (5 November 2026).

## Audit and contract

- Existing stack: Laravel 12 / PHP 8.2, Blade components, Vite 7, vanilla ES modules, semantic CSS tokens. The public route is `GET /explore` in `PublicController`; `ExploreState` normalizes URL input and `ExploreQuery` supplies Visit, Food, and Place queries. `publishedVisible()` is the public eligibility source. `PublicOptions` builds filter choices from published records.
- The prior Explore view had a text heading, tabs, search, filter dialog, sort, grid/list, cards, and pagination. Its toolbar used generic `.toolbar` flex alignment; the search field had different label geometry from sort and button controls. The new toolbar shares one grid baseline and `--control-height`.
- Baseline before changes: `php artisan test --filter=PublicTest` passed 9 tests / 99 assertions. No pre-change Explore screenshots were captured. Existing browser QA uses an isolated SQLite/media fixture through `php tools/prepare-browser.php` and `php tools/browser-server.php`; it never targets the owner database.
- The wheel facet is `food_type`, not a new table. The first position is **Semua rasa**, and `/explore` still renders newest published visits unfiltered. Up to five published types appear; when more than five exist, four appear plus **Jenis lainnya**, which opens the existing searchable filter dialog. The current deep-linked type is included among the visible entries even when outside the top four. Fewer real types leave blank sectors; the center button skips those sectors and advances to the next actual option. Occupied sectors are spaced around the six-spoke reel. No type is seeded just to fill a wedge.
- Wheel counts are published `visit_item` counts. The projection count comes from the same paginated `ExploreQuery` as Memory Reel. The projected photo/caption comes from the first current-page result with a ready card derivative, then falls back to the first result or an honest photo placeholder. Visit covers remain historical visit photos; a cover need not depict every matching dish in a visit.
- OR within a filter dimension, AND across dimensions, and same-item food predicates are unchanged. Visit prices remain visit totals, Food prices remain item unit prices, and Place has no price filter. Tab changes navigate normally to rebuild the context-specific filter dialog. Public fragment fetches return the same server-rendered state as initial SSR, with `Cache-Control: no-store`; no public draft/private query path was added.

## Composition and interaction

The scene has an independent transparent chassis image, six-position DOM wheel, independent wheel frame SVG, stationary semantic center button, lens glow, beam, projected screen, film textures, and a vertically browsable Memory Reel. The supplied approved PNG is retained at `docs/references/discovery-machine-approved-concept.png` for comparison and used only as art direction. The center button is keyboard accessible; wedge links also work without JavaScript. Drag was optional in the handoff and is not implemented.

One committed category press starts a `shutting_down` phase, dims the image/text and beam, rotates the actual rotor with the Web Animations API, then swaps in a server-rendered fragment and `reigniting` phase. The old results remain visible with `aria-busy` until the response is ready. One press is accepted per completed detent; later presses during fetch are ignored. Failed fetches restore the previous reel position and projection, keep the URL unchanged, and show an inline retry. `pushState` and `popstate` synchronize category, projection, filters, and results. Reduced motion removes the shutdown/reel/pulse animation and updates immediately. Ambient flicker pauses when hidden or offscreen. No sound autoplays.

The app's CSP allows only same-origin styles and scripts. Reel positions and wedge angles therefore live in `discovery.css`; JavaScript uses Web Animations rather than inline style attributes. The isolated browser QA runtime selects an unused Vite hot-file path so an unrelated local Vite process cannot cause CSP-blocked cross-origin assets during test runs.

## Assets and limitations

| Asset | Origin | Deploy size | Use |
| --- | --- | ---: | --- |
| `public/assets/discovery/projector-chassis.webp` | Generated with OpenAI ImageGen from the supplied approved concept, then converted to alpha WebP | 270 KB | Static wheel-less metal chassis and lens |
| `public/assets/discovery/vector/wheel-frame.svg` | Supplied handoff vector | 4.3 KB | Rotating rim and spokes |
| `public/assets/discovery/vector/filmstrip-band.svg` | Supplied handoff vector | <1 KB | Projection and Memory Reel perforation |
| `public/assets/discovery/textures/film-grain.webp` / `film-dust.png` | Supplied handoff textures; grain converted to WebP | 80 KB / 3.2 KB | Low-opacity clipped film detail |

The generated chassis gives the machine a photographic material base, but the wheel rim/spokes remain the supplied vector rather than independently photo-textured metal. The screen frame and beam are CSS/SVG treatments and do not match every irregular edge and lighting detail in the approved composite. Screenshots using the isolated QA fixture show a deliberately synthetic QA plate image and many no-photo cards; real public photography depends on published media. This is a visual-fidelity gap, not a claim of pixel-perfect A1 reproduction. No LCP/INP/CLS improvement is claimed without comparative field measurement.

## Verification

Run the feature suite on the PHPUnit in-memory SQLite database:

```powershell
php artisan test
npm run build
```

Run browser QA on its separate fixture/database and media directory:

```powershell
php tools/prepare-browser.php
php tools/browser-server.php # separate terminal, loopback 8011
npx playwright test tests/Browser/discovery-machine.spec.js
npx playwright test
```

The Discovery Machine browser test writes `test-results/discovery-ready-1440.png`, `discovery-dark-1440.png`, `discovery-selected-1440.png`, and responsive screenshots at 1920, 768, 390, and 320 pixels. Those files are ignored QA artifacts. It also checks URL history, reduced motion, keyboard activation, horizontal overflow, axe WCAG 2 A/AA through 2.2 AA tags, and failed-fetch retry. Re-run after changes to wheel slots or filter semantics.

The same test writes `test-results/discovery-performance.json` with a local Chromium 156 LCP/CLS sample at 1440×900 using isolated QA content and no network throttling. The final run recorded LCP 360 ms and CLS 0. This is one lab observation, not a field Core Web Vitals estimate; INP and before/after comparison were not measured.
