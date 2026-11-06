> Historical implementation record. The machine now lives on Homepage; Explore uses the Curiosity Index. Current architecture: [cinematic redesign](cinematic-redesign.md).

# Foodie Explore — Memory Archive completion report

Historical V1 record. Current behavior and evidence are documented in
[Memory Archive V2](memory-archive-v2-completion.md); V1 mask-based artwork and
direct reduced-motion reveal descriptions below are superseded.

5 November 2026. Implementation lives in the existing working tree; no commit,
database migration, owner-data refresh or upload modification was performed.
Pre-existing uncommitted work was preserved and extended.

## Actual changes

- Replaced the small CSS dispenser and detached showcase with one dominant
  horizontal metal cabinet. Six photographic compartments, selection/processing
  lamps, pickup opening and story screen now share one physical composition.
- Added `config/archive.php` and a build compiler for external geometry CSS
  and SVG repair masks. Fixed the supplied reconstruction's panel strips, old
  screen text, pickup patch and baked envelope remnant. Production CSP remains
  unchanged; no inline layout styles or JavaScript exceptions are required.
- Prioritized six curated presets in `config/discovery.php`, validated against
  published options. Bounded compartment previews reuse ExploreQuery and the
  selection service. Default browsing remains latest published visits.
- Rebuilt the console with real search, filter-dialog trigger, mode links,
  sort, view controls and removable URL-backed chips. Retained every advanced
  filter dimension and the distinction between visit totals and item prices.
- Reused shared Visit cards with scoped Explore density. Visit gallery photos
  now cover missing-cover cases; Place cards can use an older published photo
  visit; Food cards never substitute an unrelated cover for a dish photograph.
- Added a compact cabinet layout below 960px, with readable live controls,
  local dispensing and the screen below. Fixed shelf overflow and 200% text
  wrapping. Reduced motion skips physical travel.
- Scoped self-hosted Cormorant Garamond normal/italic and DM Sans to Explore.
  Refined atmospheric background, glass reflections, amber lamps, bronze
  surfaces, torn-edge photographic results and the restrained footer.

Key source: `resources/views/components/discovery/`,
`resources/views/public/explore-fragment.blade.php`,
`resources/css/memory-dispenser.css`, `resources/css/discovery.css`,
`resources/js/pages/explore.js`, `app/Services/DiscoveryMachine.php`,
`app/Services/ExploreQuery.php`, and `app/Models/Place.php`.

## Assets used, rebuilt and excluded

Runtime artwork totals **208,972 bytes**: cropped empty cabinet → cabinet.webp;
warm foliage background → atmosphere.webp; resized bronze material → bronze.webp.
The three Latin WOFF2 files total **113,832 bytes**, with OFL licenses bundled.
Source artwork and the supplied full-page reference remain intact.

The real metal shell is retained. Defective interiors, boundary repairs, display
frame, tray and envelope are rebuilt as aligned SVG/CSS/HTML layers. Text and
photographs remain dynamic. The filled machine, console and card illustrations
are references only. The supplied front bezel repeats the reconstruction flaws
and was excluded; the rough envelope extract was replaced.

No obsolete production Flavor Dial/projector references remain. The projector
asset deletions already pending at task start were preserved. The obsolete
small-dispenser capture script was replaced with
`tools/capture-memory-archive.mjs`. Historical QA/reference documents and images
remain outside the production payload.

## Animation and data architecture

The cabinet, envelope and screen containers stay mounted. A request generation
and AbortController prevent stale data or animation continuations. State follows
idle → processing → dispensing → ready → revealing → extracting → revealed,
with explicit empty/error paths. Retraction runs alongside the real request;
results/URL commit before optional envelope opening.

The envelope emerges inside one tray-local mask, underneath its top lintel,
and rests in front of the lower rim. Its flap hinges behind the insert, then the
photograph lifts locally. First dispense is 640ms, repeated 360ms; detail stages
are 300ms / 220ms. Reduced motion reveals directly. Unchanged decoded images
are retained without crossing the inert parser document; srcset order is
normalized so equivalent candidate lists do not reload protected media.

Full storyboard, layers, bounds, interruption, media and accessibility contracts:
[discovery-machine.md](discovery-machine.md).

## Rendered evidence and verification

[Full-page comparison](qa/memory-archive/reference-comparison.png),
[mobile cabinet](qa/memory-archive/scene-390.png),
[partial dispense](qa/memory-archive/05-partially-dispensed.png),
[opening](qa/memory-archive/08-opening.png),
[revealed memory](qa/memory-archive/10-revealed.png),
[interaction recording](qa/memory-archive/interaction-review.webm).

The screenshot run covers idle, focus/hover, selection/processing, partial/full
dispense, opening/extraction/reveal, repeated use, updated results, empty/error,
reduced motion and 320/390/768/1024/1280/1440/1920px views. Intermediate captures
deliberately slow transitions to inspect geometry; automated tests exercise
production timings. The frame log records coordinates and states. Capture
reported zero unexpected browser errors; the simulated 503 is recorded
separately as expected.

Executed: production build; 56 Laravel tests / 951 assertions on isolated
SQLite; Pint; PHP compiler syntax check; whitespace diff check; Playwright
functional, responsive, touch, keyboard, history, cancellation, media, reduced
motion and axe checks. The final complete browser run and lab sample are recorded
in [verification.md](qa/memory-archive/verification.md).

## Remaining limitations and deliberate trade-offs

- Existing published records and photography are authoritative, including
  previously seeded demo content. They differ from the illustration; no new
  illustrative reviews, ratings or culinary photos were inserted.
- This is a lightweight raster-metal/DOM composition, not a 3D simulation.
  Hardware and envelope details are reconstructed rather than pixel-identical.
- Accessible native controls and variable-length real content make the console
  and cards taller than the static illustration. Mobile deliberately recomposes
  the cabinet instead of shrinking desktop text.
- Six bounded live preview queries preserve immediate visibility correctness.
  Production-scale query/load profiling remains separate work.
- Chromium automation does not establish physical-device Safari, screen-reader
  behavior or field Core Web Vitals. INP and production MySQL load were not
  measured. No MySQL schema change was needed.
