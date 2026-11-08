> **Historical record — original dated content below.** Not a current implementation contract or fresh PASS claim. See [authority/classification](adr/README.md), [latest decisions](adr/001-experience-authority.md), [motion contract](motion-interaction.md) and [current baseline](implementation-status.md). Superseded docks, visible pickup labels, pagination arrows, envelopes, lamp-pause/recurrence and carousel descriptions must not be reinstated. Domain rationale and dated QA remain evidence for their original scope.

# Cinematic projection reconstruction — 5 November 2026

## Diagnosis and reference review

Reviewed the approved concept and the supplied 5.78-second WhatsApp recording at 0.0, 0.9, 1.7, 2.6, 3.5 and 4.9 seconds. `docs/qa/projection-reconstruction/video-review.jpg` records those samples. The recording is defect evidence, not a target animation.

| Cause | Correction |
| --- | --- |
| The original chassis baked in a viewer-facing aperture | Replaced the optical assembly artwork with a connected side-facing cylindrical barrel and narrow vertical aperture |
| Oversized machine nearly intersected the projected frame | Calibrated desktop machine/screen proportions around a visible optical gap; automated checks require >40 CSS px at 1280, 1440 and 1920 widths |
| Independently guessed beam percentages did not track the optics | SVG polygon connects measured aperture and transformed screen-edge anchors, recalculated by ResizeObserver |
| A full discovery-root replacement reset physical layers during every category change | Scene, rotor element, button, frame, shutter and projection surfaces remain connected; update only rotor contents, projection contents and results/controls |
| Category labels used unrelated visit cover photos | Selected types resolve only item-linked media from matching published items inside the current page's results |
| “Jenis lainnya” rotated into a fake selected position | It opens the existing selector without committing a selection; center-button progression skips that action slot |

The previous CSS/DOM layout and standard QA dataset were captured before changes in `before-1440.png` and `before-390.png`. All artifacts below are in `docs/qa/projection-reconstruction/`.

## Assets and geometry

The current asset is `public/assets/discovery/projector-side-axis.webp`: 1536×1024 RGBA, 269,422 bytes. The built-in ImageGen tool edited the previous transparent chassis, preserving its footprint, rear reels and wheel opening while replacing the optical barrel. Source PNG: `docs/references/projector-side-axis-source.png`. The old deployed `projector-chassis.webp` is removed; Git retains its history. The photographic reel (1000×1000) and celluloid frame (1200×900) remain suitable and are retained. Neither contains dynamic text or food content. Grain/dust remain separate, low-opacity image layers.

Generation brief: preserve the exact 1536×1024 canvas, base, rear reels, dark copper materials and transparent circular wheel opening; replace only the right optical assembly with a connected horizontally right-pointing brass barrel in near-side profile; show mostly machined side wall, a thin lens lip and a narrow vertical elliptical aperture about 1:4 width/height; no beam, text, screen, food or dynamic wheel baked into the art. Reference inputs were the previous chassis and approved concept. The output was inspected for aperture orientation and alpha, then encoded to WebP at quality 86. No remote asset service is required by the application.

The chosen camera is near the machine's side, with a slightly turned dial plane. Chassis and wheel share the projector parent's perspective. The reel has a stationary depth shadow behind its rotating face. Desktop positions are local scene configuration variables in `discovery-scene.css`, not global tokens. At the main desktop range the machine is 49% of scene width and the screen begins at 69%; this restores room for the barrel and light path while retaining a large wheel. The screen's frame, image, contrast overlay, text, grain and shutter share one parent transform, with no independent child skew.

`discovery-projection.js` reads the aperture rectangle and two 1 px anchors on the projection plane. Those transformed anchor coordinates generate the cone in scene coordinates. This measurement runs on resize and content commit, not every animation frame. Tablet/mobile deliberately stack the shared machine and screen and hide the horizontal cone. The machine fits the available width; the center control remains at least 56 px. Reduced motion keeps immediate selection and removes pulse, shutter travel, rotation and exposure animation.

## Data contract

`ExploreState` and `ExploreQuery` remain authoritative. Default `/explore` is all newest published visits. Count and Memory Reel still come from the same paginated result set. When `food_type` is selected, `ExploreQuery::previewItems()` reuses the existing visit and same-item facet predicates; `DiscoveryMachine` restricts the candidate to current result visits, items or places. Only ready media explicitly linked to that matching item can become the category photo. No image is inferred from a title, category name or unrelated cover. A missing item photo gives “Foto hidangan yang cocok belum tersedia.” Initial all-types browsing may still use an actual published visit cover.

This fixes the recording's repeated roast image under Teh and Salad. The new recording shows different item-linked Tea and Salad media, captions and actual filtered counts. The demo uses the repository's separately seeded SQLite/media fixture on loopback 8012; it is clearly fictional content with illustrative stock photos. Owner data is untouched. Tests also cover a private item with a photo and verify that it cannot supply the public projection.

## Motion and state

One center activation commits one next concrete category; repeated activations while busy are ignored. The icon is a rotation arrow, with accessible name “Kategori berikutnya” and a “Putar ke jenis berikutnya” title. Wedge links remain SSR navigation without JS. Drag was not established and is not introduced.

The lifecycle is `idle → shutting_down → changing → reigniting → idle`. The physical apparatus stays mounted throughout. The two shutter halves close while lens/beam exposure dims; the rotor advances with weighted deceleration. Fetch begins concurrently. The shutter stays closed until both detent and image decode finish, with a visible preparation message during genuine loading. Then projection contents and Memory Reel commit together, the shutter opens and lens/beam receive a short exposure ramp. Image decoding has an 8-second fallback and abort handling. Request IDs discard stale responses. Back/Forward can supersede an operation; pagehide resets a stable committed state for browser-cache restoration. Failed requests restore the prior position/content and show persistent retry.

## Evidence and checks

- Static captures: `static-{1440,1280,1920,768,390,320}.png`.
- Operation: `projection-operation.mp4`; source capture uses `tools/record-projection-reconstruction.mjs` and local Playwright video, converted to H.264 with FFmpeg.
- Eight state captures: `01-idle.png` through `08-next-selected.png`; `animation-events.json` records actual phase, selected label, image URL and rotor animation time for each capture. Screenshot capture overhead means these are observed states, not exact nominal millisecond timestamps.
- Baseline targeted PHP gate: 12 tests / 125 assertions. Updated targeted PHP gate: 13 tests / 149 assertions, including cross-tab item-photo identity and deliberate missing-photo fallback. Full PHP gate: 52 tests / 920 assertions.
- `npx playwright test`: 18 passed. Browser coverage includes forward rotation/wrap, persistent rotor/projection identity, no apparatus detachment, >40 px desktop optical gap, Back/Forward, retry, rapid-click locking, keyboard, axe, reduced motion, 320–1920 reflow and 200% text. The first full run had 17 passes and an existing homepage drag-test readiness race; waiting for carousel initialization fixed the test, and the final full run passed all 18.
- `npm run build`, `php vendor/bin/pint --test`, and `git diff --check` are the build/style gates. Vite's same-origin public-asset warnings leave valid deployed URLs unchanged.

The retained full-suite local performance sample recorded LCP 2156 ms and CLS 0 (`performance.json`), with no network throttling and local fixture/server load. It is not a before/after benchmark. The scene is still a 2.5D composite, not a physical optical simulation. Patina, rivet placement and lighting are not pixel-identical to the approved painting. The beam is a restrained, measured light volume without simulated smoke. Browsers other than local Chromium were not exercised in this pass; no field INP or comparative performance improvement is claimed.
