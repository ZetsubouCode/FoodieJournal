# Foodie art direction

Approved target, 8 November 2026. See [authority](product-vision.md#authority-and-read-order), [current gaps](implementation-status.md) and [implementation phases](roadmap/frontend-experience.md). Target geometry below is not yet implemented.

## Reference lineage

Local anchors were verified with `git show`, `git log` and `git diff`:

| Reference | What it actually establishes | What to preserve |
| --- | --- | --- |
| `d4e40dd` / `4f6365b` | Homepage approval and older Explore projector; animated header underline in `resources/css/home.css` | Warm copper navigation, photographic richness, tactile metal, purposeful editorial hierarchy |
| [Memory Archive V2 Explore capture](qa/memory-archive-v2/explore-1440.png) | Cabinet-on-Explore intermediate work, retained inside the broad `801cc57` commit; not a separately committed `4f6365b` cabinet | Charcoal/sepia/brown materials, amber local illumination, textured warm foliage environment, coherent archival atmosphere |
| `801cc57` | Cabinet moved Home; Curiosity Index and Dossier; shared chrome and darker page variants | One Home machine, useful information architecture, ordinary discovery and narrative paths |
| `b56db2c` | Indexed dial, side-hung tube at reset height, material-only pickup cue | Latest mechanics and placement, not superseded inside-panel coordinates |

Reference screenshots are mood/composition evidence, not functioning backgrounds or replacement content. [Original full-page asset](../Foodie_Memory_Archive/reference_full_page.png) and [homepage boards](design/references/homepage/approved-overview.jpg) retain provenance. Restore their useful principles without restoring navigation problems or old surprise-envelope semantics.

## Material, photography and typography

Charcoal is the environment; sepia/brown metals and warm ivory provide depth; copper/amber marks action, selected state and localized light. Build surface, text, action, border, focus and status roles through semantic variables. Use component material tokens for glass, brushed bronze and paper. Literal raster geometry can retain its source-coordinate values; unrelated spacing/colors should not become more appended overrides.

Real published photography carries the story. Use optimized derivatives with truthful subject provenance: dish/technique tiles depict matching items, not an unrelated visit cover. Missing photos become intentional text/material layouts. Never use stock photos as owner memories. Current demo photography is explicitly fictional QA content.

Public display: self-hosted Cormorant Garamond (`Archive Garamond`); body: DM Sans (`Archive Sans`), with readable fallbacks. Admin keeps practical system fonts. Narrative measure is 60–75ch, normally the existing `--reading-max: 68ch`. Body starts at 1rem with generous leading. Small engraved labels are secondary; important instructions, dates, prices and actions must remain comfortably readable. Audit current sub-12px metadata before expanding decoration.

## Wide responsive shell

Current `.container` is capped at 80rem: 1280px at 1440/1920, with 80/320px side margins. The footer background already spans the viewport; its inner content uses the same cap. There is no evidence of a second nested `.container` shrinking the principal public shell. Home additionally reserves 48–80px for the outboard lamp; that space has a mechanical purpose.

Requested next implementation uses one shared wide shell for nav, main and footer contents, with full-width atmospheric layers and a separate reading measure:

| Width | Target gutter on each side | Wide shell rule / expected width at 16px root |
| --- | --- | --- |
| 320 / 375 / 390 | 16px | 288 / 343 / 358px |
| 768 | 24px | 720px |
| 1024 | `clamp(32px, 3.333vw, 64px)` | About 956px |
| 1440 | Same fluid rule | About 1344px; 48px gutters |
| 1920 | Same fluid rule | 1792px; 64px gutters |
| Larger | Same gutter, max 112rem | Reading remains narrow; outer scene may continue full bleed |

Implement through semantic shell/gutter tokens, not per-page width patches. Never apply the container width formula twice to a child. Keep lamp gutter inside the scene allocation and source proportions intact. Wide photos and asymmetric bento groups may use the shell; paragraphs never stretch to 112rem. At 200% text, wrap controls and simplify columns before shrinking type or hit areas. Test at 959/960 and 1023/1024 as well as the named screenshot widths. No horizontal document overflow, clipped tube, text or controls; header/main/footer alignment within 1 CSS px.

## One environment, deliberate variants

Use the older archive's warm localized light and texture across the public journey. Home may show more environment around the cabinet; Explore balances illuminated discovery photos with a quiet filter work surface; Visit/Place lower visual intensity behind reading. Crops and density may differ, while material, palette, fonts, focus and motion remain recognizably related. Error pages keep the approved kitchen image and Editorial Bold layout.

Current `cinematic.css` overrides `memory-dispenser.css`: Home opacity .12, Explore .7, Visit .8, Place .6, varying crop and a fade toward 75rem rather than the older 90rem. Those are observed implementation values, not a new immutable palette. Rebalance against matched screenshots in P0; do not merely make every page equally bright.

## Header and footer contract

One `x-site.header`, one `x-site.footer`, one `public-chrome.css`, including standalone errors. Header brand and simple Beranda/Jelajah remain. The approved copper left-to-right hover underline follows [motion behavior](motion-interaction.md#navigation-hover-progress); it is not a loading bar.

Footer is a recognizable editorial ending with personal-journal identity, real navigation and back-to-top. Recover deliberate scale, warm material continuity and space for the closing statement. Current CSS hides the line break and eyebrow, reduces the statement to 2.7rem maximum, and makes navigation horizontal; the older homepage rules used up to 5.6rem and a stronger two-column close. Preserve meaning and responsiveness, not every historical divider or fixed font size. Compare full footer crops on Home/Explore/Visit/Place/error at 375/1440/1920 before approval.

## Locked and experimental

Locked: single author, published-only photographs, one Home cabinet, current lamp/reset/dial geometry, no visible pickup tutorial or pagination arrows, accessible alternatives, shared chrome, approved HTTP error scene/copy, F Refined result-card identity and no autoplay Home rails.

Experiments: broader bento arrangements, shallow perspective, local latch/paper response and warmer atmospheric balance. Prototype one at a time after correctness gates; reject any that hurts reading, discovery speed, touch, keyboard or rendering cost. See [decision register](adr/001-experience-authority.md).
