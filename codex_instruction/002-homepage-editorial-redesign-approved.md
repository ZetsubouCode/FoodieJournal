# CODEX IMPLEMENTATION INSTRUCTIONS — Foodie Homepage Editorial Redesign (APPROVED)

**Priority:** HIGH — owner-approved design decisions.  
**Project:** Foodie, personal culinary journal, Laravel + MySQL, Blade-first, Bahasa Indonesia, IDR, Asia/Jakarta.  
**Task type:** Inspect → implement → test → visual-compare → document. **Do the implementation, not just a design proposal.**  
**Scope:** Public homepage, its shared header/footer and reusable presentation primitives, plus tests/docs. Keep other pages and admin workflows working.

## 0 — Mandatory files to read BEFORE editing

1. `docs/design/homepage-design-decisions.md` — the authoritative owner-approved decisions and explicit image overrides.
2. `docs/design/references/homepage/01-header-B-underlined-accent.png`
3. `docs/design/references/homepage/02-hero-F-bold-immersive.png`
4. `docs/design/references/homepage/03-section-heading-A-editorial-classic.png`
5. `docs/design/references/homepage/04-recent-D-carousel.png`
6. `docs/design/references/homepage/05-personal-C-curated-carousel.png`
7. `docs/design/references/homepage/06-discovery-C-asymmetric-bento.png`
8. `docs/design/references/homepage/07-footer-C-signature-statement.png`
9. `docs/design/references/homepage/00-visit-card-F-refined-PREVIOUSLY-APPROVED.png`, plus any **existing** Visit Card F Refined implementation/instructions.

Do not choose a different option just because the original design board marked another option as "recommended." Owner selection supersedes the generated recommendation annotations.

## 1 — Objective and visual quality bar

Turn the present public homepage from a sparse, seemingly half-finished listing page into a **coherent, cinematic editorial personal food journal** with polished hierarchy, active link/button states, balanced whitespace, responsive cards, purposeful animation and credible storytelling. The design must resemble the **specific selected crops**, not a generic dark Tailwind template.

Mandatory design mapping:

- **Header B**: Underlined Accent.
- **Hero F**: Bold Immersive, venue/place name in amber/copper (an explicit adaptation from Hero A).
- **Section Heading A**: Editorial Classic (Balanced).
- **Recent D**: horizontal auto-advancing bidirectional infinite-loop carousel, with overlay side arrows vertically centered on card rail.
- **Personal Picks C**: curated carousel with the **same shared component and behavior**, independent data/configuration/state.
- **Discovery C**: Asymmetric Bento — **big Makanan left, small Kota top-right, small Gaya masak bottom-right**.
- **Footer C**: Signature Statement.
- **Cards F Refined**: reuse previously approved Split Narrative with signature torn-paper contours, tags/actions independent from stretched card link, no arrow in upper-right card corner.

The visual references are illustrative only. Do not hardcode their photographs, dates, names, scores, menu content, review text, label lists, counts, fake search icon, bookmark icon, or routes into production. Use real published data. Do not serve reference screenshot crops as homepage content or backgrounds.

## 2 — Inspect the real codebase first; minimize regression

1. Read `AGENTS.md` if present, project instructions, README, sitemap/ERD/design system docs, relevant earlier Codex instructions, `composer.json`, `package.json`, Vite config, Laravel routes/controllers/services/models/scopes, existing tests.
2. Locate the homepage Blade view/controller/query/route; header/footer layout; `VisitCard` and rating/tag/badge/link/button primitives; CSS token files; JS entrypoints; existing `IntersectionObserver`/carousel helper/library; optimized media handling.
3. Confirm the exact `published` scope, personal recommendation and/or featured ordering, city/category/cooking-style taxonomy and corresponding Explore filter URLs. Never infer table fields or make unneeded migrations. Singular MySQL table naming is mandatory for any justified new schema (prefer **no database changes**).
4. Check which parts already exist and reuse them; avoid duplicate header, two separate carousel implementations, duplicate CSS animations or DOM handlers. Protect Visit Card F Refined's approved design and all existing functionality.
5. Record real file paths and any limitations; if a required route such as `Arsip` doesn't exist, **do not create a dead/placeholder link**. Use appropriate existing routes or omit non-real controls; don't silently invent a new product feature.

## 3 — Design system and shared component implementation

Use existing semantic tokens and add missing *semantic* ones (background/surface, text primary/secondary/muted, copper accent, border, overlay/gradient, focus, spacing, radius, shadow and motion). Keep a unified layout max width, consistent padding scales, breakpoints, section gutters and typographic hierarchy, with **WCAG 2.2 AA** contrast (body text >=4.5:1, essential UI affordances >=3:1).

Prefer existing Blade components and modular CSS/JS. Candidate shared components (names illustrative; inspect existing convention):

- `SiteHeader`, `SiteFooter` — shared across public pages where appropriate.
- `SectionHeading` — props for eyebrow, heading, optional description, secondary action/route; optional divider variants without forcing all sections to be identical.
- `VisitCard` — **reuse F Refined**, do not fork or redesign as a smaller generic thumbnail card to fill three columns.
- `VisitCarousel` — **single reusable interactive implementation**, slots/collection/config for Recent and Personal Picks; independent instances (no shared state collisions). Module should be decoupled from data selection.
- `DiscoveryBentoTile` — accessible link tile with real image, eyebrow, title, optional explanation, graceful fallback.
- `IconLink` / shared button primitive for text links, outline-pill CTAs and focus/hover states.

Do not hardcode recurring CSS inline in Blade; avoid scattered event listeners, unneeded SPA framework, dependency bloat, and magic values. If the existing carousel library can provide accessible seamless looping reliably, reuse it; otherwise implement a small, testable modular solution. Do not add a heavyweight library only for this effect.

### Header: B — Underlined Accent

- Brand **Foodie.** serif left, horizontal text nav right. Use published routes that actually exist; `Beranda`, `Jelajah`, `Arsip` are a **visual example**, not an authorization to invent an archive page.
- On active nav item: recognizable amber underline. On hover: animated accent underline/colour transition, not ordinary browser default permanent underline. Keyboard: strong `:focus-visible` ring and discernible active state independent of hover/colour.
- Responsive: nav remains usable and touch-friendly on narrow widths; add a mobile menu only if truly necessary, using existing accessible dialog/menu pattern; Escape, focus management and aria state. No nonfunctional search/bookmark icons.
- Keep design restrained to avoid competing with hero. Sticky header is NOT mandatory; decide only if site behavior supports it.

### Hero: F — Bold Immersive (venue accent)

- Wide panoramic hero with dominant food image, deep/gradient dark contrast layer, overlay text aligned left, striking large serif headline, location/place title **amber/copper** under headline (this change is mandatory), rating, visit date, city and one primary `Baca kunjungan` CTA.
- Reuse actual featured/published visit; preserve content selection logic if suitable. If content not available, intentional no-data/fallback, not invented review copy or dates.
- Name of place must be distinct from story headline. Rating uses real overall score; null = `Belum Dinilai`. Visit date localized; handle long titles and missing city.
- Ensure readable text across bright/high-contrast photos using gradients; ensure sensible focus/click targets. CTA must navigate to real detail route; do not make nested conflicting links.
- Optimize LCP: appropriate responsive image source, preload/high fetch priority for actual hero image if warranted, `object-position` consistent with user-selected cover, no unnecessary lazy-loading of above-the-fold hero. Avoid fake slide indicator: hero is only a slideshow if an actual multi-hero feature exists and is required; do not introduce a new hero carousel from illustration dots.
- Mobile: retain text legibility and dominant photo, prevent CTA overlapping photo subject, avoid tiny metadata. No fixed-height clipping for long names.

### Section heading: A — Editorial Classic

- Amber hairline/eyebrow (e.g. THE LATEST ENTRIES, PERSONAL PICKS), warm-ivory serif heading, short supporting description when meaningful, outlined pill secondary CTA with arrow; intentional gap to card rail and section separation.
- Use shared component/tokens for spacing; initial visual rhythm to tune after screenshot QA: ~24–36px between section heading and content, ~80–112px between major sections on desktop, smaller on mobile. Do **not** blindly hardcode these as magic per-section values; adjust after real rendering.
- Provide hover/focus/pressed states for pill CTA. If a secondary destination is absent, render a coherent non-link heading rather than a dead CTA.
- Maintain readable metadata, dates, prices and labels. Generic underlined browser links must not appear as unresolved default styling.

## 4 — Shared carousel behavior (CRITICAL)

Implement ONE modular `VisitCarousel` used by both Recent and Personal Picks. Preserve Visit Card F Refined in the rail without shrinking it until its text becomes unreadable. Rail may show partially visible next/previous card to suggest interaction. Layout responds to actual available width rather than forcing exactly three tiny cards.

**Explicit owner requirements (all blocking):**

1. **Automatic sliding**: the carousel moves autonomously with gentle animation. Prefer discrete step advancement with a comfortable dwell time so a user can read cards; do not implement ceaseless rapid marquee motion unless it proves just as readable. Make timing configurable in shared component, not duplicated page code.
2. **Seamless infinite loop in BOTH directions**: pressing next at the rightmost end shows the first item as a natural continuation; pressing previous at the leftmost end shows the last. Autoplay wraps likewise. No visible snap-back/reset flash, white gap, flickering or hard jump. If using clones, manage seamless transforms and transition reset only while invisible; clones must be `aria-hidden`/`inert` or equivalent so keyboard/screen readers do not encounter duplicate interactive content, IDs, or names.
3. **Overlay arrows**: circular/outlined left and right arrow buttons must overlay the **left and right edges of the card rail** respectively, both vertically centered relative to visible cards. **NOT** up by the heading; NOT below the rail. Give controls high enough contrast and layering but avoid covering important text/tags/clickable areas; reserve safe rail insets and adjust mobile arrow size/position. Minimum comfortable touch target ~44x44px.
4. Recent and Personal Picks share the **same carousel primitive, UI, motion rules and CSS**, yet have independent item collections, active index, pause state, control IDs, timers and event listeners. Avoid a global singleton causing one section to change the other.
5. Native touch swipe/drag and manual next/previous must work. A drag shouldn't accidentally trigger Visit Detail; ordinary click/tap on card still navigates. Buttons should not trigger card click.

**Professional UX/accessibility guardrails:**

- Auto-advance **pauses** on pointer hover, keyboard focus within rail, active touch/pointer drag, and while off-screen / browser document hidden. It must not fight manual navigation. Resume conservatively only when appropriate; an explicitly user-paused carousel stays paused until resumed.
- Provide an accessible **Pause/Play control** for any continually updating carousel (WCAG 2.2.2) in a restrained location that does not violate the approved positioning of the left/right arrows. Honor `prefers-reduced-motion`: no autoplay/continuous motion and much reduced transitions. Respect reduced-data mode if relevant.
- Semantic region with descriptive label, `aria-roledescription="carousel"` where helpful, descriptive previous/next buttons, manageable tab order and no autoplay announcements; any live announcements should be limited to user-initiated changes. Keep focus stable when looping.
- Do not fake pagination dots. If using dots from the visual reference, they must reflect actual items/pages, be keyboard accessible if interactive, and remain consistent in loop mode; omit when pointless.
- If 0 items: contextual empty state; 1: static single card, no autoplay/arrows; few items: only duplicate/loop if a real seamless loop can remain accessible; otherwise degrade without dead controls, and report tradeoff. Never clone published state objects with new fake IDs.
- Avoid layout shift during image load; use stable aspect ratios/sizes, loading skeleton **only if there is a real asynchronous load**. Avoid mounting two separate interval timers after page transitions/hot reload.

### Recent: D — Horizontal Carousel

- Order by most recent **published visit date**, with deterministic secondary ordering. Use project's existing approved query semantics, pagination/limit, eager loading and optimized covers.
- Section heading A with `Kunjungan terbaru` and a real CTA (e.g. `Semua kunjungan` → correct Explore visit view).
- Use F Refined card. User-facing date/city/rating/recommendations remain visible and accurate. Do not assume high-rated or recent implies editorial endorsement.

### Personal Picks: C — Carousel Kurasi

- Use exact shared `VisitCarousel` (same arrows, loop, autoplay, reduced-motion/pause behavior, card component, click and tag semantics), not a clone with minor CSS differences.
- Curated personal recommendations ONLY from the project's actual admin-managed recommendation/featured flags/labels; never derive solely from top rated or randomly mark items as `Must Try`.
- Exclude drafts/private. Keep sensible deterministic ordering/admin order if present; otherwise document the fallback. A visit may legitimately appear in both Recent and Picks; do not introduce artificial uniqueness unless product requirement says so.
- Section heading A, with label/description clearly signaling personal selection rather than numeric popularity. CTA to a real recommendation-filtered Explore route when supported; omit/adjust otherwise, do not invent a route.

## 5 — Discovery: C — Asymmetric Bento (CRITICAL mapping)

- Approved visual: **one LARGE Makanan tile on the LEFT**. On RIGHT: **Kota at TOP**, **Gaya masak / Cooking Style at BOTTOM**. Do not reverse those based on subsequent non-approved homepage composites.
- Strong photographic backgrounds, readable dark overlay, intentional serif headings, restrained eyebrow/link icon/CTA, asymmetry that breaks the rhythm of preceding carousels. Card shapes and borders stay related to Foodie theme, but do not add torn-paper borders everywhere.
- Tiles are genuine navigational links, not clickable divs with artificial JS routing. Target existing Explore tabs and filters for food/category, city and cooking style. Use REAL taxonomies and published data; a category without published results must not be falsely advertised. Avoid inventing a city name, cooking technique, entry counts, or image.
- Prefer meaningful display photos from actually published visits associated with that discovery path, with deterministic selection and a stable fallback; no hardcoded illustration photos from design boards, no fetching remote dummy food photography at runtime.
- On hover/focus: light zoom/gradient/border/arrow movement. Full tile click if no inner interactive controls; avoid nested anchor tags. Keyboard focus and touch affordance visible. Mobile: stack intelligently (large tile first, then two smaller ones) with no tiny unreadable titles.
- Share filtering/query logic with Explore; retain filter parameters and published-only constraints. If a genuine path lacks content, prefer contextual link to a broad valid Explore view rather than showing misleading content.

## 6 — Footer: C — Signature Statement

- Match `07-footer-C-signature-statement.png`: near-black understated editorial closing, left narrative/statement zone and right compact navigation zone with subtle vertical divider, brand line and quiet back-to-top control.
- The chosen illustrative headline is **“Rasa boleh lewat. Ceritanya tinggal.”** with the second line in warm copper and warm-ivory first line. Preserve its personal-journal intent and prominent typography. A short supporting line can clarify this is a personal record of memorable places, dishes and visits; never imply Foodie cooks or sells meals.
- Use only real links (Beranda/Jelajah/etc). `Kembali ke atas` should work as an actual `#top` anchor or equivalent accessible control. Dynamic copyright year if copyright displayed; no invented newsletter/social proof/social links/contact/address.
- On mobile, columns stack gracefully with appropriate divider adaptation and touch target sizes.

## 7 — Click handling, metadata and content contracts

- **Visit Card F Refined is ALREADY A SETTLED DESIGN DECISION.** Reuse its actual component and torn-paper SVG masks. The **image-left / panel-right split, vertical deckled divider with warm fibrous edge, exterior worn outline** must remain faithful. Do not replace with a straight divider, generic zigzag or screenshot-as-background.
- The **upper-right circular arrow button INSIDE Visit Card must be absent**. This does not prohibit the **outside overlay carousel next/previous controls**, which the owner explicitly requested.
- Whole **noninteractive card surface** (photo/title/metadata/whitespace) opens the correct published Visit Detail. Category/cooking-style/general tag links and genuinely linked recommendation badges remain independently clickable without triggering parent navigation. Existing `+N` overflow behavior must remain consistent with the **already implemented/approved card spec**; do not unnecessarily change it during homepage work.
- HTML-valid links: prefer layered stretched-link `<a>` with sibling tag anchors/buttons and reliable z-index over nested anchors and blanket JS `stopPropagation` hacks. Ensure focus visible, keyboard access and mouse/touch behavior.
- Ratings, prices, tags, published status and date always use correct data relationships. `Belum Dinilai` is not zero, no placeholder editorial label, image fallback is clear. Date uses visit date and proper locale, no fabricated metadata; only show total-visit cost where labeled as such.

## 8 — Motion, layout, states and performance quality

- Purposeful micro-interactions for nav underline, CTA outline, hero CTA/image, card hover/focus, discovery image/tile and carousels. Use transform/opacity where appropriate, generally ~150–250ms for ordinary feedback; carousel duration/timing independently readable and configurable. No overbearing parallax, excessive glow or global motion that competes with content.
- `prefers-reduced-motion`, high contrast/AA, focus-visible and visible active states required. Auto-moving UI must be pauseable. Do not rely on hover alone for critical content. Keep motion minimal on mobile and lower-power devices.
- Robust design at desktop >=1440, 1200, tablet 768, mobile 375/320, long localized text, 200% zoom where feasible. Do not shrink F Refined typography to force three columns; adjust number of visible cards and rail peek instead.
- `initial`, `loading` (only genuine async), `empty`, `success`, `error`, `disabled`, `updating` states where applicable. Header/hero/section CTA should remain useful if sections have no published data. No blank/unexplained large section that feels unfinished.
- Preserve original visit photos; use existing optimized media pipeline, responsive formats/sizes, lazy-load below-fold cards, stable aspect ratios to avoid CLS, correct srcset if present; avoid oversized background images and JS-driven layout thrash. Avoid costly queries/N+1. `published` scope must be enforced in backend/query layer, not merely hidden in Blade.
- Keep homepage SEO and metadata correct for public content; never index private/draft.

## 9 — Validation and required tests (execute, never fabricate results)

**Functional / backend:**
- Only published visits appear in hero, recent, curated picks, discovery images/labels and links. Draft/private visits and data are absent from public HTML and queries.
- Recent date order and picks' true editorial curation; unrated score state; null city/cover; invalid/deleted linked content; no published items; one/two/many carousel items; long multi-line titles; 0/1/2/many tags.
- Real CTA/tag/Explore filter links resolve to correct published content, no dead icons or fake routes. Ensure no N+1.

**Carousel / interaction:**
- Timed auto-advance on both independent carousels; next on last wraps to first, previous on first wraps to last, and **transition has no visible seam/reset**.
- Left and right arrows precisely overlay **vertically centered rail sides** in desktop/tablet/mobile; do not cover card tag links.
- Hover/focus/drag/offscreen/hidden-document pause behavior; pause/play control, reduced-motion disabling autoplay, manual resume rules; keyboard previous/next and swipe.
- Clicking card noninteractive area opens visit detail; clicking tag/label/+N does not navigate card; cloned elements are not duplicated tab stops and IDs; keyboard focus remains sane.

**Visual QA:**
- Compare a real browser screenshot of implementation with **each selected reference crop**; inspect at desktop 1440/1200, tablet 768 and mobile 375/320. Compare headline, contrast, real spacing and proportions, and correct Discovery tile mapping. Include before/after screenshots if the environment supports it.
- Specifically ensure **F Refined paper silhouette and photo-panel torn boundary have NOT regressed**, and no arrow has reappeared in top-right of the Visit Card.
- Ensure nav hover/active/focus, buttons, section-heading-to-content spacing, footer composition and hero amber venue name match selection, not default HTML link styling.
- Run existing relevant PHP and JS build/tests, e.g. `php artisan test` targeted feature tests, `npm run build`, `npm run lint` if configured. Choose existing browser test runner if available; otherwise document manual browser QA honestly. No "tests passed" statement without running them.

## 10 — Documentation and final delivery

Update the existing project's real docs rather than creating a contradictory parallel guide. Integrate/point to `docs/design/homepage-design-decisions.md`, update design-system tokens/components, shared carousel API/options and pause/reduced-motion controls, Blade templates, homepage/Explore navigation and states as applicable, responsive specifications, route data-source rules, and F Refined compatibility. If prior docs conflict, identify and reconcile them, preserving owner-approved decisions. Preserve references under `docs/design/references/homepage/`.

In Codex's final report provide:
1. Audited existing paths/components and what was reused.
2. Files added/modified, notable component architecture, carousel algorithm/clone accessibility and motion safeguards.
3. Comparison against the seven selected visuals and F Refined, including any visual differences honestly called out.
4. Build/test commands actually run with outcomes, screenshots actually reviewed, and known issues.
5. Documentation updated, any routes/features omitted because unavailable, and remaining follow-ups.

## 11 — Definition of Done (blocking acceptance checklist)

- [ ] Header **B** animated amber underline, real routes, accessible focus/responsive.
- [ ] Hero **F** cinematic overlay with **amber/copper place name**, genuine visit metadata and CTA.
- [ ] Shared Section Heading **A** and pill CTA with polished whitespace and interaction states.
- [ ] Recent **D** real-data auto-advancing horizontal carousel, **bidirectional seamless infinite**.
- [ ] Recent carousel **side overlay arrows vertically centered over rail/cards** (explicit override of source art).
- [ ] Picks **C** carousel reuses **same carousel module + same F Refined card**, independent state, genuinely curated data.
- [ ] Both carousels pauseable, swipeable, keyboard accessible, viewport aware, `prefers-reduced-motion` compliant.
- [ ] Discovery **C** exact orientation: **large Makanan left; Kota top-right; Gaya masak bottom-right**, genuine links and published data.
- [ ] Footer **C** signature statement, restrained nav, working back-to-top, no fake restaurant/commercial framing.
- [ ] Existing F Refined torn-paper edges, whole-card click/independent tags, and **no card top-right arrow** preserved.
- [ ] Responsive/empty/long-title/image-fallback/contrast/focus states deliberately verified.
- [ ] No security/publication leakage or existing business logic regression.
- [ ] Existing docs updated and tests/build/browser findings truthfully reported.

**Execution instruction:** Work autonomously after initial inspection. Do not stop at mockups or a plan; deliver code and docs that satisfy the approved design. Avoid unrelated refactoring or new paid/heavy dependencies.
