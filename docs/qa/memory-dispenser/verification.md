> Historical evidence for the earlier small dispenser. Superseded by the
> [Memory Archive verification](../memory-archive/verification.md); timing,
> composition and capture-script references below are no longer current.

# Memory Dispenser verification

5 November 2026 · Foodie Explore rebuild

## Outcome

The Flavor Dial/projector has been replaced with a front-facing archival Memory Dispenser, a matching photographic showcase, a complete Discovery Console and the Memory Reel. The signature interaction is collection selection → genuine processing → masked envelope dispense → flap opening → photo lift/showcase reveal. Results and shareable URL commit before the optional opening.

The cabinet, envelope and showcase containers stay mounted. Native CSS transitions own their individual transforms. New selections cancel earlier work; history/search changes resolve directly; failures retain committed data and expose retry. Reduced motion immediately produces the same content. The first dispense uses 400ms, repeats use 220ms, mobile uses 300ms.

## Changes and review

| File / surface | Before | After | Why |
| --- | --- | --- | --- |
| `app/Services/DiscoveryMachine.php` | Wheel positions / projection | Public presets and result-bound memories | Accurate identity, media and counts |
| `config/discovery.php` | No combined shortcut config | Validated configurable combinations | Curated collections without a new table |
| `app/Http/Controllers/PublicController.php` | Wheel/projection payload | Slots/memory payload | Same SSR and fragment query |
| `app/Services/ExploreQuery.php` | Fractional rating binding depended on DB coercion | Numeric threshold coercion | SQLite/MySQL numeric comparison consistency; place preview retains place rating meaning |
| `components/discovery/dispenser.blade.php` | No dispenser | Cabinet, six compartments, display, masked tray and layered envelope | One coherent local coordinate system |
| `components/discovery/showcase.blade.php` | No showcase | Published photograph, identity, excerpt and detail link | Real matching content and honest empty/photo-failure states |
| `public/explore-fragment.blade.php` | Projector scene and film reel | Dispenser/showcase → console → quieter reel | Discovery remains a useful browsing page |
| `resources/css/memory-dispenser.css` | Perspective-dependent motion | Transform/opacity transitions, relative geometry and responsive cabinet | Stable masking, occlusion and reflow |
| `resources/css/discovery.css` | Metallic film surfaces | Compact console and editorial reel | Photography and controls take precedence |
| `resources/css/app.css` | Old scene import | Dispenser import | No abandoned scene stylesheet |
| `resources/css/tokens.css` | Projector-only palette tokens | Shared Foodie semantic colors | Remove obsolete visual vocabulary |
| `resources/js/pages/explore.js` | Wheel locking/rotation/beam geometry | Cancellable lifecycle, history, media fallback and focus restoration | No stale selection or permanently busy state |
| `tests/Feature/DiscoveryMachineTest.php` | Wheel assertions | Public presets, many taxonomy options, combinations, media and place/recommendation/rating checks | Verify actual new behavior |
| `tests/Browser/discovery-machine.spec.js` | Rotor tests | Ten interaction, error, keyboard, responsive, reduced-motion, media and accessibility checks | Verify rendered behavior |
| `tests/Browser/journal.spec.js` | Accessibility could run before app readiness | Wait on app pages; standalone HTTP errors need no JS | Avoid measuring transient initialization |
| `tests/Browser/performance.spec.js` | Assumed the homepage always has a cover | Verify a real photo or the supported text hero | Retained QA content can legitimately have no recent photo |
| `tests/Feature/DemoSeederTest.php` | Owner fixture could randomly be in Bandung | Explicit separate fixture city | Make the demo-city count deterministic |
| `.ui-craft/brief.md`, `README.md`, `docs/discovery-machine.md` | Projector presented as current | Current dispenser architecture and maintenance guide | Durable, consistent requirements |
| Historical fidelity/reconstruction records | Earlier instructions could look active | Explicitly superseded notices | Preserve evidence without conflicting requirements |
| `tools/capture-memory-dispenser.mjs` | Obsolete capture tools | Reproducible frame, viewport and video capture | Inspect intermediate geometry |

Removed motion: rotor rotation, projection shutter/relighting, beam and film flicker, dust/grain animation and perspective geometry. They belonged to the abandoned concept and competed with culinary content. No idle animation, scrolling spectacle, audio or screen-to-screen flight was added.

Removed production art: old projector chassis, side-axis chassis, wheel metal, projection frame, wheel selection/frame vectors, filmstrip band, film grain and dust. Some were already deleted or untracked at task start. References were checked first. Removed obsolete `discovery-scene.css`, `discovery-projection.js` and the three prior capture/record scripts. Shared card artwork, documentation reference art and uploaded media remain intact. New art is HTML/CSS plus an inline SVG missing-photo frame; no generated bitmap or new stock photography was necessary.

## Data and usability

All presets and advanced filters use ExploreState/ExploreQuery; no new model/table or parallel filtering engine. Public options and media retain published visibility. Configured combinations reject unavailable terms and may legitimately yield no matches. There are at most five curated links plus the full-filter console; taxonomy is not restricted by machine size. Visit/Food/Place, search, sort, grid/list, chips, pagination and history remain operable. Visit totals, historical item unit prices, place rating and recommendation labels keep their separate meanings.

Desktop/tablet use adjacent machine/editorial columns; mobile moves the direct-results link before a compact cabinet, then stacks showcase and browsing. Keyboard links support Enter/Space, focus is visible, targets exceed 44px, status is live and atomic, and opening restores focus to the matching memory link. Failed images have an honest fallback. Reduced motion, including changes during operation, does not gate content.

## Executed verification

- PHP suite: **54 passed, 933 assertions**, isolated SQLite memory; `DiscoveryMachineTest`: **6 passed, 63 assertions** after final service changes.
- Pint: passed.
- Vite production build and Blade view compilation: passed. Vite retains the existing shared visit-card public-asset URL notices; browser tests verify those assets resolve.
- Dedicated Explore browser suite: **10 passed**. After the final stale-image-error guard, the three focused selection/retargeting/failed-media tests also passed.
- Final full browser run: **22 passed / 1 failed**. The failure was an assertion polling past the short 400ms dispensing phase (it saw processing, then the correct ready state). Changed that transient-phase check to requestAnimationFrame polling, started before activation; its targeted rerun **passed**. All 23 scenarios have therefore passed across the full run and corrected targeted rerun; a single subsequent full-suite green run was not performed.
- `git diff --check`: passed.
- Source detector: committed Explore CSS baseline had 5 unit-mixing advisories; the combined replacement source has 5 unit-mixing advisories and no major/critical finding. These advisories concern crisp pixel edges alongside rem geometry. They are not an accessibility score.

Visual QA inspected idle, hover/pending, genuine processing, shutter opening, partial/settled dispense, flap folding, photo extraction, revealed showcase and repeat operation. Rendered layouts were checked at 1920, 1440, 1280, 1024, 768, 390, 375 and 320 CSS px; text reflow was checked at 200%, including 375/320px. Tests assert the envelope/photo stays inside the tray and run axe AA checks on idle, ready, revealed and filter dialog at desktop and mobile. Local paint/layout data is in `performance.json`; it is one unthrottled Chromium observation, not field performance or INP.

## Evidence

- `published-preview.png`: read-only preview of the existing public local journal; it includes pre-existing published/demo material, not newly created visit facts.
- `01-idle.png` through `11-repeated.png`: interaction frames using explicitly fictional QA fixture media.
- `static-*.png`, `scene-320.png`, `scene-375.png`, `scene-390.png`: responsive screenshots, including complete mobile scenes.
- `reduced-320-scene.png`: complete reduced-motion mobile result.
- `frames.json`: frame state and URL log.
- `interaction-review.webm`: review recording. This and intermediate frames deliberately slow CSS transitions to inspect geometry. Production timing is separately tested unmodified.
- `before-projector.png`: initial working-tree screenshot captured before the rebuild.

## Limits and trade-offs

This is a layered 2D illustration, with contained opening and a coordinated showcase reveal rather than fragile cross-screen travel. Repeated interactions are shorter; the full results remain accessible throughout. The fixed curation is configuration-backed rather than admin-managed. Long slot names are visually limited on compact mobile while their full accessible text remains available.

Not verified: physical iOS/Safari, a screen-reader session, animation FPS/GPU-layer profiling, production field Core Web Vitals/INP, production-scale query performance, or a new MySQL test run. This task changes no database schema; PHP tests use isolated SQLite and browser mutations use only the separate QA database/media. The current owner database was not refreshed or seeded. No new dependency or paid service was introduced.

Natural next design pass: `/polish` (rung 1), if further art-direction refinement is wanted. Craft Read: Explore for Indonesian journal readers, product language with editorial photography, charcoal/ivory/copper, variance 7, signature bet: an archival envelope emerging through a masked pickup tray.
