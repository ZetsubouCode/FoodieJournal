> **Historical approval register (5 November).** Hero/carousels/Home discovery placement and footer geometry were subsequently superseded. Header B hover intent and F Refined identity survive; follow [ADR 001](../adr/001-experience-authority.md) and the [current contracts](../product-vision.md), not this board as a complete present-day layout.

# Foodie — Approved Homepage Design Decisions

**Status:** APPROVED by owner, 5 November 2026 (Asia/Jakarta)  
**Scope:** public homepage and shared primitives needed to implement it.  
**Sources:** explicit choices in the Foodie homepage design conversation. This document is the decision record; the implementation map and QA evidence below record the subsequent repository work.

## Decision register (do not substitute options)

| Area | Approved choice | Non-negotiable details | Reference |
|---|---|---|---|
| Shared Visit Card (previously approved) | **F Refined — Split Narrative** | Real torn-paper effect on inner divider and exterior silhouette; no top-right card arrow; whole noninteractive card surface opens visit detail, tags/actions independent | `references/homepage/00-visit-card-F-refined-PREVIOUSLY-APPROVED.png` |
| 1. Header | **B — Underlined Accent** | Foodie brand on left, uncluttered horizontal navigation, animated amber underline and visible active/focus states. Do not add nonfunctional bookmark/search controls | `references/homepage/01-header-B-underlined-accent.png` |
| 2. Hero | **F — Bold Immersive** + venue accent from Hero A | Wide cinematic photograph, dark gradient for contrast, dominant serif title, **place/venue name in amber/copper**, rating/date/city, primary "Baca kunjungan" CTA | `references/homepage/02-hero-F-bold-immersive.png` |
| 3. Section heading | **A — Editorial Classic (Balanced)** | Fine amber horizontal eyebrow, serif title, concise optional description, outlined pill CTA with arrow, meaningful breathing room before contents | `references/homepage/03-section-heading-A-editorial-classic.png` |
| 4. Recent | **D — Horizontal Carousel** | Reuse F Refined cards; **automatic advancement**, **seamless bidirectional infinite loop**, arrows **overlay on card rail's vertically centered left/right edges** (not near heading) | `references/homepage/04-recent-D-carousel.png` |
| 5. Personal picks | **C — Carousel Kurasi** | Reuse exactly the same carousel engine/primitive as Recent, same controls and rules, with independent instance state and independently curated data | `references/homepage/05-personal-C-curated-carousel.png` |
| 6. Discovery | **C — Asymmetric Bento** | **Large tile: Makanan (left)**, **top-right: Kota**, **bottom-right: Gaya masak**. Note: some subsequent non-approved mockups showed Kota as the large tile; they DO NOT supersede the approved Option C source board | `references/homepage/06-discovery-C-asymmetric-bento.png` |
| 7. Footer | **C — Signature Statement** | Editorial closing with two-line personal statement, smaller brand caption, restrained divider and simple navigation/back-to-top | `references/homepage/07-footer-C-signature-statement.png` |

## Explicit overrides of the illustrative images

- **Visit Card:** Remove the circular arrow in its top-right. Do not display the reference screenshot as a rendered image/card; use real visit data and preserve the previously approved reusable SVG torn-edge technique.
- **Recent carousel:** Source board D places small arrow buttons beside the heading, but the **later explicit owner instruction overrides this**: the navigation buttons MUST overlay the left/right *sides of the card rail*, each vertically centered.
- **Hero:** The selected F board happens to show a non-accented venue name. The owner explicitly approved changing that **venue name only** to the warm amber/copper accent shown in Hero A.
- **Discovery:** Approved C source tile orientation is **Makanan large + Kota and Gaya masak right**. Follow it over any later composite illustration with a different orientation.
- **Header:** Illustration contains a bookmark icon and possibly routes that might not exist. A visual-only sample must **not** cause fake bookmarks, a new search feature, or dead links to be added.
- **Personal picks:** Labels such as Must Try / Worth Revisit / Hidden Gem are example data, not a request to hardcode labels or equate high rating with editorial recommendation.
- **Footer:** Illustrative words are preferred visual copy. If real editorial copy is adjusted, retain intent: a personal journal of *tasting, visiting, reviewing, and remembering*, **never** a food seller or chef.

## Cross-page constraints

- Laravel + Blade + current MySQL stack; no SPA solely for visual redesign. Reuse semantic design tokens (charcoal surfaces, warm ivory text, amber/copper accents, accessible muted labels, borders/focus/motion). Avoid one-off hex codes and ad hoc CSS.
- Preserve Visit Card F Refined as its own settled component/design. The homepage work should **reuse or integrate it, not silently redesign it**. If the card work is still pending in the actual repository, report and coordinate the scope instead of substituting a different card.
- Only `published` visits and their public information enter homepage content, images, links, statistics, discovery suggestions and metadata; no leakage of `draft` or `private` data.
- Overall visit rating remains the project's simple mean of populated criteria (taste/service/atmosphere/cleanliness/value), with taste calculated from rated foods, null values excluded, 1–5 in 0.5 inputs; display one decimal appropriately, **Belum Dinilai** when none. Personal labels are distinct from rating.
- No invented counts, destinations, label semantics, food type, city, or sample data on live pages. Use admin-curated content if available, with clear deterministic fallback to published content.
- Dates in Indonesian, timezone Asia/Jakarta, meaning *visit date* rather than creation date. Costs remain correctly identified (visit total versus item price) and prices in IDR.
- Prioritize reduced motion, keyboard navigation, swipe/touch, visible focus, readable contrast, image optimization, stable layout, and honest error/empty states.

## Visual fidelity hierarchy

1. Explicit latest owner instructions / approved modifications in this record.
2. Individual selected-option crop (`01`..`07`) for composition, styling, proportions and placement.
3. Earlier F Refined card reference and approved Visit Card instructions for card anatomy and signature torn edge.
4. Repository's current semantic design system and real data/route conventions.
5. Illustrative copy/photos, which are placeholders only and should be replaced by real published data or appropriate fallbacks.

The design board crops are **UI design references**, not pixel-perfect screenshots of a working application. Implement their visual language faithfully while handling real text length, mobile widths and WCAG access needs.

## Implementation map (5 November 2026)

The approved homepage is implemented in `resources/views/public/home.blade.php`, with reusable heading, carousel and discovery tile components in `resources/views/components/home/`. Shared navigation and closing statement live in `resources/views/components/site/header.blade.php` and `footer.blade.php`. Styling is in `resources/css/home.css`; the existing F Refined card and SVG edges remain in `components/domain/visit-card.blade.php`, `resources/css/visit-card.css`, and `public/assets/visit-card/`.

`PublicController::home` selects up to ten recent published visits by visit date then ID, including the featured visit. Hero selection prefers the first recent visit with a ready hero variant. Personal picks require an actual recommendation relationship and use the same date/ID fallback order because the current schema has no manual pick position. Their heading has no “all recommendations” CTA because Explore has no filter for “any editorial recommendation”; individual badges retain their real label-filter links. A visit may appear in both rails.

`HomepageDiscovery` uses published options from `PublicOptions` and the actual `ExploreQuery` filters. The first alphabetic available category, city, and cooking style supplies each tile's real label and URL. Associated published visits supply ready card photos. A tile without an associated photo uses the existing “Foto belum tersedia” presentation; a tile without a published taxonomy option points to its broad Explore tab and has no invented detail label. Discovery order is Makanan left, Kota upper right, Gaya masak lower right.

`<x-home.visit-carousel>` takes `id`, `label`, `visits`, and optional `interval` in milliseconds (minimum 4000 in JS). Both instances use `resources/js/components/visit-carousel.js`, each with separate timers, indices and pause state. It creates inert, `aria-hidden` copies of the complete item sequence on each side and removes cloned IDs. The track moves to an adjacent copy, then maps to the visually identical real slide without a visible seam. Manual controls, horizontal drag/swipe, and automatic steps use the same path. Only the current real slide participates in the keyboard tab order; neighboring real cards remain pointer clickable. Autoplay pauses on hover, focus, drag, hidden document, off-screen rail, reduced motion and reduced data. The accessible pause/play button persists the user's choice. No dots are shown. Empty rails explain their state; a single item is static with no controls.

Visual QA is recorded in `docs/qa/home-editorial-1440.png`, `docs/qa/home-editorial-320.png`, and `tests/Browser/homepage.spec.js`; each browser run also captures 1200, 768, and 375px in the temporary `test-results/` directory. The isolated browser fixture deliberately uses synthetic QA imagery; its composition checks are valid, but final photographic art direction must be judged again with the owner's real published photos. The browser card reference check stays in `tests/Browser/visit-card.spec.js`.
