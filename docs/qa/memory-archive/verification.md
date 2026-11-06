# Memory Archive verification

Historical V1 evidence. See the [current V2 verification](../memory-archive-v2/verification.md)
for the repaired source, surprise-envelope contract, drawer and final checks.

5 November 2026. Final implementation verified in local Chromium 156.0.8078.4.
Owner data and uploaded media were not modified. Database tests used isolated
SQLite memory; browser CRUD used the existing separate tools/.runtime fixture.

## Commands actually executed

| Check | Final result |
| --- | --- |
| `npm run build` | Passed. Compiled archive geometry/mask, Vite production assets and standalone error CSS. |
| `php artisan test --compact` | **56 passed, 951 assertions**, 83.72s. |
| `php artisan test --compact --filter=DiscoveryMachineTest` | **8 passed, 81 assertions** after media/preset additions. Included in full Laravel run above. |
| `php vendor/bin/pint --test` | Passed. |
| `php -l tools/build-archive-geometry.php` | No syntax errors. |
| `git diff --check` | Passed; repository line-ending notices only. |
| `npx playwright test --reporter=list` | **25 passed**, one worker, 5.9 minutes in the final complete run. |
| `node tools/capture-memory-archive.mjs` | Completed: 21 logged captures plus scene/reduced-motion images and video; zero unexpected browser errors. |
| Production-source reference search | No Flavor Dial/projector references in app/resources/public/config; no inline styles in archive components. A no-match rg exit status is expected. |

Vite reports absolute public asset URLs as unresolved at build time, matching
the existing project's asset convention. Browser verification loads these local
files successfully; no unexpected 404s were observed.

## Functional and security coverage

The Laravel suite covers public-only results/options/media, guessed private
routes, unpublish behavior, same-item filter semantics, hierarchical dimensions,
price/rating meanings, combined and unavailable presets, six curated positions,
matching item photographs, gallery-without-cover and older-place-photo fallbacks,
plus existing domain, admin, integrity, media, backup and security regressions.

The browser suite covers:

- Results/URL committed before optional opening; physically mounted cabinet and
  package; repeated selections and new input during dispensing.
- Superseded requests, Back/Forward restoration, failed-fetch retry, empty results
  and genuine media failures.
- Decoded compartment photographs retained at the first dispensing frame.
- Search, sort, chips, view, pagination, mode context and advanced filter dialog
  staged/cancel/apply/count behavior.
- Keyboard activation/focus, touch destinations and disclosure, reduced motion,
  and a motion preference change during dispensing.
- No horizontal overflow at 1920, 1440, 1280, 1024, 768, 390, 375 and 320px;
  envelope/insert containment and 200% text sizing.
- Axe WCAG A/AA scans of idle, ready, revealed and filter-dialog states at desktop
  and mobile, plus existing homepage/admin/card checks.
- Existing homepage, detail, gallery, editor/publish-preview and HTTP error flows.

## Visual and motion inspection

Compared actual rendering directly with the supplied 1122×1402 reference.
Inspected machine silhouette and scale, material/lighting, photographic area,
editorial type, console proximity, result photos and footer hierarchy.

| Before | Corrected | Evidence |
| --- | --- | --- |
| Small CSS cabinet beside detached story | One horizontal realistic shell with an integrated screen | reference-comparison.png |
| Composite strips and old screen text | Geometry-driven repair mask and reconstructed live interiors | scene-1440.png |
| Screen photograph compressed by copy | Expanded screen bounds and tighter editorial hierarchy | 10-revealed.png |
| Bronze texture visibly tiled | Material scales to each physical surface | reference-comparison.png |
| Shelf overflow; large-text rail/count overflow | Bounded compact shelf and intrinsic wrapping/columns | scene-320.png and automated reflow |
| Protected images flashed empty after fragment adoption | Same-document decoded-image retention; normalized srcset order | first-frame browser assertion and 05-partially-dispensed.png |

Viewed idle/focus/hover, selection/processing, partial/full dispensing, flap opening,
photo emergence, reveal, repeated interactions, empty/error and compact layouts.
The envelope has a tray-local origin and clipping boundary; the lintel remains
in front, the package rests within its tray bounds, and the insert lifts locally.
No viewport-to-screen flight or global overflow mask is used.

Intermediate captures intentionally slow CSS transitions. Frame coordinates are
sampled immediately before each screenshot; screenshot capture itself can advance
the transition. Production timing is tested separately. A simulated 503 is the
only expected console resource error and is separated from unexpected errors
in frames.json.

## Local performance sample

Explore's final unthrottled Chromium sample at 1440×1000:

- LCP: **1,676 ms**
- CLS: **0.0032073**
- INP: not measured

This is one local fixture-based sample, not field Core Web Vitals or a production
load benchmark. Runtime artwork is 208,972 bytes and Latin WOFF2 fonts total
113,832 bytes. No 3D or animation library was added.

## Verdict and limits

**Implemented and verified within the local Chromium environment.**
No remaining critical/major defect was observed in the checked interactions.
Native controls and variable-length real content produce a taller console/reel
than the static illustration. Existing photos differ from the concept, and
reconstructed hardware/envelope detail is not pixel-identical. These are recorded
trade-offs, not claims of a 1:1 raster reproduction.

Physical-device Safari, assistive screen-reader use, field INP, production-scale
query load and live MySQL load were not tested. No database schema change was
introduced. Full architecture and delivery details:
[completion report](../../memory-archive-completion.md).
