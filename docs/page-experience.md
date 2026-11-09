# Page experience and states

Approved target, 8 November 2026. [Status](implementation-status.md) records which parts exist. Follow [art direction](art-direction.md), [motion](motion-interaction.md), and [domain authority](product-vision.md). All public copy follows design-system §1; English archive labels already in the UI are branding, not permission to write new utility copy in English.

## Home: one playable memory archive

Arrival gives one distinctive physical cabinet and direct Beranda/Jelajah navigation. A fresh session is OFF with material pickup cues; users may install the tube, turn the index, select a real visit, print and retrieve its photograph. A returning ON session resumes quietly. The screen/photo links identify that exact visit. Latest visits and explicit personal picks communicate real content below the hero.

Keep the approved shell/LED geometry and [latest mechanical contract](motion-interaction.md#home-mechanical-contract). No advanced filters, second cabinet, autoplay rails, page arrows beside the knob, visible pickup label, auxiliary hero tutorial or duplicate Explore link below the machine. Screen-reader instructions, errors and honest empty states remain. Standard navigation and no-JS visit links bypass play.

At compact widths preserve semantic hardware → windows → actions → tray → preview ordering, fully visible outboard tube and reserved paper space. The 768px hardware/window stack is tall in the current capture; improve compact density only if labels, touch areas and hand-to-target relationships remain usable. Do not squeeze the desktop raster into tiny controls.

## Explore: interactive discovery plus practical browsing

An asymmetric photographic discovery board invites category/city/technique exploration; compact food-type/recommendation links offer alternative entry points. Build bento grouping around those distinct purposes, not equal decorative cards. One clear browse work area contains Visit/Food/Place navigation, search, filters, sort, grid/list and result count. Memory Reel remains real results, not a second game.

Sample journey: choose a technique photograph → compatible Food URL/filter → adjust staged drawer selections → Apply → results/count/chips commit together → open a dated visit → Back restores state. Cancel discards pending drawer changes. Group reset and all reset have explicit scope. Preserve query on sorting/view/pagination and compatible tab changes. Reset price context between Food/Visit/Place with feedback; Food uses item unit price, Visit total, Place has no price filter. Same-item AND, per-dimension OR and published-only counts remain backend responsibilities.

During requests retain the discovery board and committed results. Busy feedback belongs to the region being updated. On failure retain the committed URL/data and offer retry; a failed history restoration returns to the committed URL. Rapid requests must never display stale fragments. Focus returns to the logical initiating control or replacement equivalent. Full GET navigation remains usable without JS.

## Visit: one dated culinary dossier

Actual cover or intentional text opening → expressive title/place/date → facts rail → narrative → tasting notes → populated criteria → gallery → personal conclusion → place/related visits. Photos, historical price labels and the author's words are primary; headings/metadata/icons support scanning. Recommendation is not a score badge. Preserve 68ch narrative measure while hero/gallery/bento groups can use the wide shell.

Use bento for complementary facts, photo and note groups only where it helps recognition. Mobile DOM order follows the story; CSS visual reordering must not scramble keyboard/reading order. No forced scroll animation to unlock text. Empty optional narrative, tags, conclusion and gallery may be omitted without filler. No rating becomes an honest “not recorded” state; no synthesized zero.

Gallery thumbnail activation opens the shared native viewer and only then requests the protected original. Keep loading status, derivative fallback, error/retry, bounded previous/next, Escape/close and focus restoration. Keyboard arrows must not steal input shortcuts. A missing image must not leave a broken icon or collapse geometry.

## Place: context and history

Lead with actual place identity/location, optional Maps, rating and number of rated published visits. Follow with dated published history and each visit's own score. It shares Dossier atmosphere/components while emphasizing chronology. No invented current menu or price, no crowd-review wording. Null aggregate is unrecorded, never zero. Without any eligible visit the public route is 404.

Current hero selects a photo only from the current paginated visit collection; a photo may disappear on another history page, and a photo-free header retains a two-column grid. Plan explicit text-only geometry and assess a stable published photo selection if needed; do not silently change the query in this documentation phase.

## State contract

The following is required behavior, not a claim of complete implementation. P0-3 implements the empty/partial readability slice; [current verification](qa/readability-states/verification.md). Real-wait skeleton work remains P2. Use the existing Alert/Modal/Toast, image fallback and empty-state components; add page-specific variants rather than duplicate foundations.

| Context | Initial / waiting / updating | Empty or partial | Failure / recovery / cancellation |
| --- | --- | --- | --- |
| New journal / zero published visits | Immediate server-rendered truthful layout; no pretend loading | Home cabinet has no selectable visit; recent/picks state absence. Explore says “Arsip belum dipublikasikan”, with no empty-state reset CTA. A bounded published-only existence check runs only for an empty result page | Guest navigation remains; do not expose owner draft counts or suggest guest content creation |
| Empty page number / no food details | Render real published availability only | A nonempty total with an empty page offers the first page without clearing criteria; an unfiltered Food tab without items points to published visits | Do not describe pagination or missing food details as a new journal |
| Filtered results | Keep committed results/count, announce actual loading; stable region height | Explain no match, preserve query/chips, remove one criterion or reset filters | Visible retry; retain committed data/URL; abort stale requests; no stuck busy state |
| No recommendations | Render actual recommendation relationships only | “Belum ada rekomendasi pribadi yang dipublikasikan”; absent recommendation links become quiet text, ordinary discovery remains available | Do not infer endorsement from high score |
| Missing/unready image | Reserve aspect ratio; neutral material/placeholder while genuinely waiting | Stable “Foto belum tersedia”, text-only story/item/place composition when there is no photo | Failure switches to fallback, never raw original in a listing; retry only where meaningful |
| Missing ratings / prices | Render known values immediately | “Penilaian belum dicatat” / “Belum Dinilai” per component and “Harga belum dicatat”; retain zero price when actually recorded | No score bar, star fill, total or currency fabricated from null |
| Cabinet page / print | Distinguish OFF/boot/selected/page busy/printing/ready/retrieving; lock competing actions | Empty six-window page has honest apertures and disabled print/index as appropriate | Keep committed selection on page error; retry; reset/cancel do not leave paper or ghost state |
| Viewer original | Fixed viewer geometry + status; optional geometry-matched skeleton | Derivative remains useful if original unavailable | Error text + retry/close; no trapped keyboard; close releases image and restores focus |
| Unavailable page/data | Do not fake content | 404 for ineligible detail; approved error scene for actual request errors | Status-specific action/copy from config, no exception leakage; retry only safe methods |
| Admin first run / save / upload | Clear bootstrap/empty guidance, real submit/upload status | Missing data stays editable; publish gate explains required facts | Keep fields on failure, shared error summary and inline messages, no optimistic publication or destructive reset |

### Skeleton transitions

Initial server-rendered GETs need no simulated skeleton or forced delay. For actual image/async waits, reserve geometry from image dimensions or last committed layout. Refresh keeps useful content instead of blanking it; if a delay persists (starting design threshold about 200ms), show a noninteractive region-matched placeholder only where it adds clarity. The threshold never postpones a request/response. Fast responses skip it, reduced motion uses static material, `aria-busy`/status express the real task, and failure replaces the placeholder with recovery. Placeholders are `aria-hidden`; no fake count/text/data. Avoid minimum display timers that slow success.

No optimistic result count/filter URL before server commitment. Progressive images may load into reserved slots without reflow. A delayed older image/request cannot overwrite the latest selection. Initial/refresh/progressive/failed transitions need separate test assertions; one global `.skeleton { min-height:10rem }` is not a finished system.

## Readability, controls and accessibility

Main body is at least 1rem at default settings; supporting metadata normally .875rem, with exceptions justified by secondary engraving. P0-3 raises result/Home metadata to .875rem and Home excerpts to 1rem; .75rem archive captions/engravings remain secondary. Coherent button sizes reuse `.btn`: ≥44×44px internal primary touch target, adequate horizontal/vertical padding, same height/min-width within an action group, wrapping for long Indonesian labels. Do not shrink hit areas to eliminate overflow. WCAG 2.2 AA target-size minimum is distinct from the internal 44px goal.

Use consistent SVG icons for date/place/price/rating/navigation/action/status when they improve scanning. Keep readable labels or accessible names; decorative SVGs are hidden from assistive technology. Existing calendar/location components are the starting point, not an invitation to add an icon to every sentence.

Explore uses a root-relative mobile heading and text-space container reflow; tile captions contribute to height and photo-free tiles separate fallback text from captions. Root-text 200% is verified without clipping at 320/390/768/1440; it is not native browser zoom.

Verify headings, landmarks, skip link, contrast over actual images, visible unobscured focus, keyboard-only and touch operation, dialog focus restoration, announcements, 200% text and reflow. Respect reduced motion at load/runtime. Automated axe and screenshot smoke checks cannot certify WCAG conformance. Admin remains efficient and quiet as public play grows richer.
