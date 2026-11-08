> **Historical record — original dated content below.** Not a current implementation contract or fresh PASS claim. See [authority/classification](adr/README.md), [latest decisions](adr/001-experience-authority.md), [motion contract](motion-interaction.md) and [current baseline](implementation-status.md). Superseded docks, visible pickup labels, pagination arrows, envelopes, lamp-pause/recurrence and carousel descriptions must not be reinstated. Domain rationale and dated QA remain evidence for their original scope.

# Foodie Memory Archive V2 — completion report

6 November 2026. Implemented in the existing working tree, preserving the approved
realistic cabinet foundation and unrelated work. No commit, database migration,
owner-data refresh or uploaded-photo alteration was performed.

## Actual changes

- The envelope now contains a surprise from the entire current matching published
  set. A collection overview remains visible until the visitor explicitly opens
  the package; then the individual record appears in one full-surface semantic
  link. Visit, Food and Place have their correct detail destinations.
- The upper knob draws again with the same filters and excludes the previous ID
  when alternatives exist. Single and empty sets are stated honestly. The lower
  knob retracts the package and subtracts only the curated preset's values,
  preserving unrelated search/filter/view/sort context.
- Repaired the actual cabinet source: continuous blank identification plaque,
  six brass frames, one center faceplate, clean screen and unified recessed tray.
  Removed the duplicate wordmark and console explanation. Status lives outside
  the complete machine bounds and package trajectory.
- Home and Explore use one public header and the shared 80rem container/gutters.
  Header visual treatment follows Explore at the larger shared site proportions.
  The Home hero and unrelated sections retain their composition.
- Visit/Food/Place switches use asynchronous server-rendered fragments, keeping
  the header, shell and cabinet mounted. URL, history, mode-specific prices,
  sort/view, loading, retry and stale-response cancellation are maintained.
- Rebuilt filtering as an Archive Filter Drawer: desktop group navigation and
  options pane, real hierarchy, searchable long lists, multiselect styles,
  distinct tags/recommendations, pending chips/counts, group/all reset, staged
  Apply/Cancel and persistent footer. Mobile uses a compact bottom dialog.

## Source and component architecture

The existing ExploreState / ExploreQuery remain the sole public normalization and
filter engine. DiscoveryMachine supplies curated preset metadata, preset ownership
and the random published selection. GET /explore/memory returns no-store inert
screen/insert templates; it does not create a parallel search system or persist
private discovery history.

showcase.blade.php owns the collection overview; memory.blade.php owns an individual
full-screen link; dispenser.blade.php owns physical hardware and semantic controls.
explore.js owns request generations, cancellation, fragments, history and package
lifecycle. filter-drawer.js owns staged form interaction. The existing native modal
foundation retains Escape, focus trapping/restoration and scroll locking.

The lifecycle is idle → selection/processing → dispensing → ready → opening →
revealed. Repeated draws retract safely before preparing. CSS transitions and
small native Web Animations coordinate local transforms; no new animation/3D
framework was added. Reduced motion keeps the explicit open-to-reveal contract.

config/archive.php measures one 1634×963 source coordinate system. The build
compiler emits external geometry CSS without inline styles or weakened CSP.
The envelope starts above the local pickup aperture, travels inside its clip,
and rests behind the extracted real metal lip. Flap/photo transforms stay local.
Only one indicator stage is strongly lit. Below 960px, photographed hardware crops
support a deliberate compact composition and labeled touch controls.

## Assets used, rebuilt and removed

- Supplied empty cropped cabinet: primary source for the targeted built-in
  image-generation repair. Supplied geometry established the initial layout;
  final live geometry was remeasured against the edited output.
- New master: [archive-cabinet-v2.png](../resources/artwork/archive-cabinet-v2.png).
  The exact generation request and provenance are in
  [archive-v2-generation.md](../resources/artwork/archive-v2-generation.md).
- Runtime cabinet: cabinet-v2.webp, 467,600 bytes. Matching upper/lower knob,
  pickup lip, compact pickup and compact slot crops derive from that same master.
  [prepare-archive-assets.py](../tools/prepare-archive-assets.py) reproduces them.
- Supplied atmosphere and bronze textures remain selective material layers.
  Existing licensed self-hosted Cormorant Garamond/DM Sans WOFF2 remain in use.
  Food photographs and metadata come from the real protected media pipeline.
- Removed obsolete V1 cabinet.webp and shell-mask.svg after reference checks.
  No raster repair-mask stack remains. Earlier abandoned Flavor Dial/projector
  runtime assets remain removed. Original package and historical QA evidence
  are retained; no user uploads were removed.

## Verification actually executed

| Command / check | Final result |
| --- | --- |
| php artisan test --compact | 60 passed, 994 assertions; 40.20s |
| php vendor/bin/pint --test | Passed |
| npm run build | Passed; external public asset URLs left for runtime as expected |
| npx playwright test | 25 passed, 3.9m, local Chromium |
| node tools/capture-memory-archive.mjs | Completed; 17 logged frames, no unexpected browser errors |
| git diff --check | Passed |

PHP tests use isolated SQLite memory. Browser CRUD uses the separate guarded
SQLite fixture on port 8011. Screenshot capture reads existing published content
on port 8000. Test-only hierarchy/long-list cases never touch the owner database.

Coverage includes visibility and media authorization, all filter dimensions and
prices, surprise selection across the whole set, immediate-repeat exclusion,
zero/single results, preset subtraction, active-tab preservation, asynchronous
modes, rapid retargeting, URL/history, drawer pending/cancel/reset/apply, photo
failure, request retry, keyboard/touch, reduced motion and 200% text reflow.
Existing Home, error, editor/gallery and visit-card regressions also pass.
Automated Axe scans reported no violations in the tested desktop/mobile idle,
package-ready, revealed and open-drawer states.

Responsive checks span 1920, 1440, 1280, 1024, 768, 390 and 320px. Measured shared
header at 1440px: x=80, width=1280, height=72 on both pages; at 390px:
x=16, width=358, height=72 on both. Main content boundaries also match.

## Rendered evidence

- [Full-page current rendering](qa/memory-archive-v2/full-page-reference.png),
  compared directly with the supplied illustration.
- [Recorded interaction](qa/memory-archive-v2/interaction-review-v2.webm).
- [Collection overview with dispensed package](qa/memory-archive-v2/07-dispensed-collection-overview.png)
  and [opened individual](qa/memory-archive-v2/10-revealed-full-screen-link.png).
- [Partial dispense](qa/memory-archive-v2/tray-05-partial-dispense.png),
  [later partial](qa/memory-archive-v2/tray-06-partial-dispense-later.png),
  [opening](qa/memory-archive-v2/tray-08-opening.png) and
  [photo emergence](qa/memory-archive-v2/tray-09-photo-emerging.png).
- [Desktop drawer](qa/memory-archive-v2/drawer-desktop-categories.png),
  [mobile drawer](qa/memory-archive-v2/drawer-mobile.png) and
  [compact cabinet](qa/memory-archive-v2/scene-390.png).
- [Verification detail](qa/memory-archive-v2/verification.md), including frame
  geometry, error accounting and a clearly labeled local performance sample.

## Limits and trade-offs

The repaired cabinet is a realistic two-dimensional composite with live DOM
content, rather than a 3D simulation. The compact layout intentionally recomposes
its hardware for legible touch controls. Existing published photos differ from
the reference illustration; no replacement visits, ratings or counts are invented.

Random selection avoids the immediately preceding record, not every record seen
over a long session. Random ordering is adequate for this journal-scale dataset;
production-scale random-query latency has not been benchmarked.

The recording slows/pauses native transitions for intermediate-frame review;
browser tests exercise ordinary production timings. One unthrottled local mobile
Chromium sample recorded LCP 692ms and CLS 0; this is not a field Core Web Vitals
claim and INP was not measured. Physical-device Safari and screen-reader testing
remain unverified. There are no known blocking failures in the executed checks.
