# Codex Implementation Instructions — Foodie Visit Card: F Refined (Split Narrative)

## OBJECTIVE
Refactor the public-facing **Visit Card** to faithfully reproduce the approved **F Refined / Split Narrative** visual reference, while remaining data-driven, responsive, accessible, efficient, and consistent across the Foodie Laravel + Blade application.

**Approved source of truth:** `docs/design/references/visit-card-f-refined.png` (copy the PNG provided alongside these instructions into this path BEFORE implementation). It is the **top-middle F′ card** extracted from the approved design board: warm, cinematic coffee-and-dessert photo on the left; textured charcoal information panel on the right; an irregular, warm ivory torn-paper divider and authentic deckled edges along the card's outer boundary. Use the reference's *composition, proportions, contrast, typography, spacing, border treatments, and torn-paper contours* rather than inventing a new aesthetic.

**Deliberate difference from the reference:** **REMOVE the circular arrow button in the upper-right corner entirely.** Do not replace it with another CTA icon or an invisible extra button. The card surface itself is navigable.

## REQUIRED REPOSITORY INSPECTION (BEFORE EDITING)
1. Inspect the actual Laravel version, routing, home/Explore/detail Blade views, visit/place models and relationships, publication scopes, tag/category/recommendation structures, existing Blade components, CSS tokens, Vite entrypoints and JS modules.
2. Read the project's current README, architecture/sitemap/ERD, design-system/UI rules, `AGENTS.md`, and any relevant Codex instructions. Follow existing patterns and preserve established functionality.
3. Locate all consumers of Visit Cards: homepage recent visits, personal picks, Explore list/grid, city/food discovery if applicable. Identify variants and avoid duplicating implementations.
4. Identify actual URLs/filters for visit details, tags, food categories, cooking styles, and recommendation labels. Do not invent destination routes or bypass publication visibility.
5. Record source component/file paths in the final implementation summary. Do not claim any test passed without executing it.

## DESIGN — PIXEL-LEVEL FIDELITY TO F REFINED

### Main composition
- Landscape editorial card. Desktop/tablet at a sufficiently wide card width: approximately **34–38% image left / 62–66% information panel right**, matching the approved reference. Do not squeeze this into a three-column, narrow card grid; change the grid layout or choose a documented compact treatment where needed.
- Warm, rich image crop using existing optimized visit media; `object-fit: cover` with a sensible focal position and no duplicated image assets. Preserve image loading/performance behavior and alt text.
- Dark charcoal/near-black information panel with subtle tactile/grain texture, layered warm ivory/copper accents, refined serif heading, compact sans-serif metadata. Use semantic design tokens, not independent hex values scattered across CSS.
- Main information hierarchy, following the reference from top to bottom:
  1. Small tracked uppercase **eyebrow** identifying the editorial type/context (must come from real data or safe generic copy; never invent a food category).
  2. Large, prominent serif **visit story title** (allow wrapping, do not hard-code the sample text).
  3. Place name, with understated supporting icon only when semantically meaningful.
  4. Rating in an outlined/star-accented pill (e.g. ★ 4,3 / 5) and **personal recommendation label** in a distinct premium badge when the label exists.
  5. Date and city in a legible metadata row, with appropriate spacing or separators.
  6. Bottom tags/chips, visually quieter than title/rating/recommendation.
- Date localized to Indonesian and Asia/Jakarta; maintain the existing date semantics (visit date, not created_at). The displayed visit rating must use the project's real overall-rating computation and null handling: **"Belum Dinilai"** if unrated, not `0/5`. Recommendations are independent from ratings. No fabricated counts, scores, or labels.
- The visual hierarchy matters more than exact pixel values on responsive widths, but at the reference-width screenshot the proportions and silhouette should be as close as practical.

### Non-negotiable torn-paper effect — replicate the approved reference
This is the signature visual feature and a **blocking acceptance criterion**, not an optional embellishment.
- Study/zoom into the attached reference before implementing. The dividing edge between the left photo and right panel is a **hand-torn, nonuniform vertical deckle**, not a straight line. It has irregular erosion, a narrow warm cream paper-pulp/fiber fringe, subtle shadow/depth, and believable natural variation from top to bottom.
- The **outer silhouette** also has worn/deckled, nonuniform top/bottom/right paper contours with thin warm exposed-edge highlights, rather than a clean rounded rectangle with a CSS border. Reproduce the reference's subdued irregularity; avoid excessive grunge or an exaggerated scrapbook/cartoon effect.
- **Trace/reconstruct the actual contour from the reference** into a stable, local reusable **SVG mask/path and paper-edge overlay asset**, with separate layers for (a) photo clipping, (b) exposed warm paper fiber/sliver and shadow, and (c) irregular outer card mask/border. The edge geometry should be deliberate and repeatable, **not CSS `clip-path: polygon(...)` with regular teeth, random clip points, zigzags, a generic torn-paper npm package, or an unrelated paper texture**.
- Prefer an SVG mask/viewBox with correctly scaled aspect ratios, plus CSS pseudo-elements/background layers where appropriate. It must work with **arbitrary visit photos and dynamic text**; NEVER display or crop the supplied reference screenshot as the actual card background/content.
- Provide distinct reusable **desktop vertical-tear** and **mobile horizontal-tear** treatments where necessary. On narrow screens the image may move above the panel; the torn separator should adapt naturally instead of squeezing text or leaving broken mask seams. Preserve the design character even if a mobile adaptation cannot match identical desktop pixels.
- Ensure the paper-fringe is visible on both dark and bright photography, does not obscure critical text, has no visibly repeated rectangular texture seams, is not clipped accidentally by parent overflow, and survives high-DPI rendering. Avoid reliance on remote assets or paid libraries.
- Store assets under the project's existing public/resources convention (e.g. `resources/images/visit-card/` or `public/assets/visit-card/`) and document the paths and technique. If exact contour tracing cannot be achieved from the available source resolution, state the limitation explicitly and still implement the closest faithful stable contour; do not claim a perfect pixel match.

## INTERACTION — CARD SURFACE AND INNER CONTROLS
**Remove top-right arrow entirely** from DOM and CSS, not just visually hidden.

- Pointer/touch click on **noninteractive card space** (photo, headings, metadata, whitespace, background) navigates to that visit's published detail page.
- Actual interactive elements on the card (category tags, food tags, cooking-style tags, recommendation labels when linked, `+N` overflow, and any legitimate future buttons) act independently and **never accidentally open Visit Detail**.
- Use valid accessible HTML: `<article>` as card container plus **one sibling stretched/overlay `<a>`** for the card destination, and separately layered `<a>`/`<button>` children for tags/actions. **Never nest anchor elements or buttons inside an anchor.** If adapting an existing pattern is preferable, ensure it remains HTML-valid with equivalent semantics.
- Implement stacking/pointer-events deliberately so overlay card link covers blank regions while tags/actions remain above it. Keep keyboard navigation, visible `:focus-visible` styles and sensible focus order. The card link must have a descriptive accessible name; avoid redundant links with identical destinations.
- Hover/focus-within: extremely subtle image zoom, warm border/paper-edge emphasis, or elevation. Animations ~150–250ms using transform/opacity where possible; honor `prefers-reduced-motion`. No distracting loops, layout shift, or unreadable hover-only information.
- Avoid `onclick` on the card as the primary interaction, `window.location`, nested links, and blanket `stopPropagation()` hacks when native link-layer semantics suffice.

## TAGS — MULTI-TAG WITHOUT CLUTTER
- Recommendation labels, food/category tags, cooking styles, and general tags are **distinct entities**. Respect actual taxonomy and their filter URL conventions.
- Visually match F Refined's **low-profile outlined warm-copper chips** on the lower information panel. Include an icon only if it consistently communicates an actual category/type; do not assign random emojis to tags.
- Show at most **two highest-priority context tags** plus a `+N` count, adjusted to one plus `+N` on tight viewports if necessary. Do **not** include recommendation badge in the generic tag quota because it has its own separate visual identity/position. Do not repeat the same semantic label twice.
- Prioritize tags deterministically (relevant food/category, then cooking style, then general tags; tune to the actual schema) without changing the underlying data.
- Visible tag links navigate to the matching Explore filtered result using the existing query parameter rules, preserving filter semantics, and must not trigger Visit Detail.
- `+N` must be a real accessible **button** that opens a compact disclosure/popover of the hidden tags with their own links; support Escape, click-away, keyboard, touch, and focus management consistent with the project's shared modal/popover primitives. If the existing accessible primitive is not ready, integrate with a suitable existing lightweight solution (e.g., Alpine.js already installed) without adding a heavy dependency. Do not nest links. No hover-only disclosure.
- If no tags are present, hide the entire tag row; if few tags, do not display a pointless `+0`. Long labels and many tags must not overflow or overlap rating/date/title.
- Recommendation badge is linked only if a meaningful recommendation-filter route actually exists; otherwise display as a noninteractive badge, never a dead/fake link. This exception is intentional: only genuine interactive tags must be separately clickable.

## RESPONSIVENESS AND STATES
- Prioritize real content dimensions: desktop wide split layout; tablet and narrow cards may use a vertical stacked layout with the torn edge horizontal; mobile touch targets ~44px or safe accessible equivalent, readable heading, well-spaced metadata, and no horizontal overflow.
- Re-evaluate homepage/explore grid sizing so this relatively wide split card is NOT forced into three overly narrow columns. Maintain rhythm and separation of section headings/cards; do not shrink typography to make content fit.
- Handle exceptionally long visit titles/place names, missing cover photo, missing rating, absent recommendations, missing city, empty tags, high tag count, no visible visits and image loading failure. Maintain an intentional fallback image/state and stable dimensions (prevent CLS).
- The published-only backend behavior must remain enforced; do not surface draft/private visits, tags, counts, metadata or media on public views. Preserve current queries/pagination/eager loading where possible.
- Retain lightweight optimized media delivery (`srcset`/sizes if already available, lazy-load below the fold, don't lazy-load meaningful LCP hero image), and avoid JS/CSS dependencies solely for decoration.

## ARCHITECTURE / CLEAN IMPLEMENTATION
- Default Laravel Blade + existing CSS/Vite modules and Alpine only when appropriate. Prefer one shared `VisitCard` domain component with a primary `split-narrative` visual variant rather than copying markup/CSS into home, Explore, and recommendation sections.
- Reuse existing rating, badge, link, toast/modal/popover primitives where possible; adjust the primitives if they lack required interaction states rather than implementing inconsistent new styles.
- Isolate paper edge SVG/mask and semantic tokens (surface, text, border, copper accents, radius, shadow, motion, focus) into documented reusable assets. No scattered inline `style` blobs, global selectors that unintentionally affect other cards, or duplicated click handlers.
- Keep current features intact: real routes, images, publication status, filters, historical ratings/prices, and visit details. Do not introduce database changes unless a concrete existing-data problem is verified and justified.

## VALIDATION — MUST EXECUTE WHERE THE REPO SUPPORTS IT
1. Run the project's build/lint/tests relevant to the changed files. Add targeted feature/interaction tests following existing testing patterns; don't install a heavy browser-test framework merely for this card.
2. Verify mouse/touch and keyboard: clicking photo/title/date/background opens Visit Detail; clicking visible category/recommendation tag goes to its filter (if link exists); clicking `+N` only opens disclosure, and hidden tags navigate correctly; Escape closes the disclosure and restores focus. No nested links and no click-through.
3. Verify scenarios: 0/1/2/8+ tags, absent/non-clickable recommendations, unrated visit, long title, absent city, missing photo, private/draft exclusions, different visit photos/brightness.
4. Inspect at least desktop (~1440px viewport), tablet (~768px), and mobile (~375px), and at common browser zoom settings. Screenshot compare the reconstructed visual against `docs/design/references/visit-card-f-refined.png` at a comparable card size.
5. **Explicit visual QA of the torn edge:** compare location, contour irregularity, warm fibrous fringe thickness, shadow, corner/bottom/top outlines and texture subtlety; refine until recognizably faithful. A straight separator, generic zigzag, rectangular panel border, or screenshot used as content is a FAIL.
6. Verify contrast/focus treatment against WCAG 2.2 AA, `prefers-reduced-motion`, image aspect-ratio/CLS, and desktop/mobile legibility. Do not report visually verified behavior unless you actually captured/inspected it.

## DOCUMENTATION AND DELIVERY
- Update the existing design-system/component documentation: card anatomy, visual reference path, split proportions and breakpoints, torn-edge asset generation/reuse, semantic tokens, status/rating/tag rules, stretched-link layering, hover/focus/reduced motion, and overflow-popover behavior.
- Update relevant homepage/Explore docs if their grid strategy changes. Do not leave conflicting earlier Visit Card guidelines.
- In final response, supply: files changed, new reusable components/assets, before/after design notes, decisions/tradeoffs, test/build commands and actual results, browser/screenshot checks actually performed, and any outstanding mismatch from the reference.

## ACCEPTANCE CRITERIA (ALL REQUIRED)
- [ ] F Refined's image-left/text-right cinematic editorial composition faithfully implemented with real data.
- [ ] **Torn-paper divider AND outer torn paper edges** strongly match the reference's distinctive irregular, fibrous warm paper treatment; created as reusable layers/masks, not a rough CSS approximation.
- [ ] No arrow CTA at the upper right or equivalent replacement.
- [ ] Whole noninteractive card area navigates to Visit Detail; independent tag/actions are clickable and keyboard-accessible without nested links or click-through.
- [ ] Recommendation label, rating and generic tags have independent semantic and visual hierarchies.
- [ ] Tags are capped with reliable `+N` disclosure; many tags and long titles never break the layout.
- [ ] Responsive/empty/fallback/hover/focus/reduced-motion states are deliberate and verified.
- [ ] Published-only data, historical ratings and all existing functionality preserved.
- [ ] Shared Blade/component architecture and project design-system docs are updated.
- [ ] Tests/build run and results honestly reported.

**Work autonomously after inspection. Implement the changes, not just a proposal or screenshot mock-up. Avoid unrelated redesigns.**
