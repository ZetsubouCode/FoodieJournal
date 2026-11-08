# Frontend experience implementation roadmap

Approved planning direction, 8 November 2026. **These tasks are pending.** This documentation phase implements none of the UI changes below. Start from [baseline findings](../implementation-status.md), follow [authority](../product-vision.md#authority-and-read-order), and preserve [mechanical contracts](../motion-interaction.md). Work in small reviewable changes; no blanket rewrite or new dependency is justified.

Order: P0 shared identity, state clarity and baselines → P1 physical response → P2 discovery → P3 storytelling → P4 integrated gates. Correctness/accessibility failures found during a later phase move ahead of creative work. Each task updates status, relevant contract and evidence. Keep screenshots deterministic and fixtures isolated.

## P0-1 — Restore navigation feedback

- **Objective / current:** A01; shared chrome has only a static active underline. Restore the historically approved warm hover fill using one pseudo-element and semantic duration/accent tokens.
- **Files:** `resources/css/public-chrome.css`, `tokens.css` only if a semantic token is genuinely missing, `tests/Browser/homepage.spec.js` or one shared-chrome case, standalone error build outputs; contracts/status/QA.
- **Dependencies:** none; precedes final P0 screenshot approval. No navigation code change.
- **Acceptance:** inactive hover fills left→right from 0→1 in 220ms; exits in 140ms; re-entry reverses smoothly; active stays full after leave; focus-visible shows immediate line plus outline; reduced motion is immediate. Line is thin copper/amber, no layout shift or navigation delay. All public routes/errors consume the same rule.
- **Edges / risks:** active+focus+hover overlap, rapid enter/leave, no hover touch, 200% text, built error CSS lag. Avoid specificity fights with old home selectors.
- **Tests / visual evidence:** computed origin/scale and active/focus assertions; deterministic start/mid/end at 0/110/220ms, mid-exit/re-entry frames, 375/1440/1920 header crops, real keyboard traversal and reduced-motion screenshots. Verify both Vite and standalone error bundle. Preserve actual route navigation.

## P0-2 — Wide shell and environment

- **Objective / current:** A02/A03; existing shared 80rem cap wastes desktop width; atmosphere variants and reduced footer hierarchy weaken older archival mood. Implement [measurable shell](../art-direction.md#wide-responsive-shell) and align the public world.
- **Files:** `resources/css/tokens.css`, `base.css`, `public-chrome.css`, `cinematic.css`, `home.css`, `dossier.css` only for affected child geometry; `components/layout/public.blade.php`, site header/footer only if required; `tools/build-error-css.mjs` and generated error CSS; image `sizes` consumers and viewport tests.
- **Dependencies:** P0-1 can land separately; P0-4 captures before/after. Check all root consumers of `.container`, including admin and standalone errors, before changing the global token. Scope public width separately if necessary to preserve pragmatic admin geometry.
- **Acceptance:** shell approximately 288/343/358/720/956/1344/1792px at 320/375/390/768/1024/1440/1920 with 16px root; header/main/footer inner align within 1px; footer/background full width; no doubled gutters. Narrative remains 60–75ch. Tube/LED/source geometry and readable mobile controls survive 959/960 breakpoint and 200% text. Update `sizes` for wider actual media slots; never upscale source artwork arbitrarily or crop controls.
- **Edges / tradeoffs:** longer line lengths, source photo resolution/network bytes, ultrawide limits, short desktop/landscape, text-only Place, lamp gutter, source-canvas circular lenses. Warm local light can increase richness without brightening prose backgrounds. Footer statement scale is art-directed, not a forced return to 5.6rem.
- **Tests / evidence:** width/overflow/reading-measure assertions, image requested variant/size inspection, machine alignment suite, 7-width route set, boundary widths, footer crops Home/Explore/Visit/Place/error at 375/1440/1920, compare retained old Explore mood reference. No unreviewed global container side effects.

## P0-3 — Readability and honest states

- **Objective / current:** A07/A10; generic Explore no-match copy is also used for a new journal; tiny result metadata and inconsistent local sizing need review. Define first-run/filtered/partial states before decorative additions.
- **Files:** `public/home*.blade.php`, `public/explore-fragment.blade.php`, discovery empty templates, domain card/image/empty-state/button components, `components.css`, `discovery.css`, `visit-card.css`, `PublicController` only if a bounded published-existence signal is necessary; feature/browser fixtures and tests.
- **Dependencies:** P0-2 geometry and existing domain/media gates. Reuse empty/fallback/button primitives, not a parallel state library.
- **Acceptance:** zero published visits says archive not published yet; filtered zero says criteria matched nothing and keeps chips/query/reset. No recommendations, photos, ratings or prices are separately honest. Main text ≥1rem, supporting metadata normally ≥.875rem; smaller engraving must stay secondary and readable. At 200% text, responsive headings should wrap at word boundaries where practicable; fix the captured narrow Explore word split without preventing enlargement. Primary touch actions ≥44×44px, equal-level groups share height/min-width/padding, visible focus/disabled state. No fake Maps/score/photo/guest-create CTA.
- **Edges / risks:** zero versus unknown availability, null versus free price, no records after unpublish, long Indonesian labels and >8 chips, status privacy/count leakage. Never add raw originals to replace failed derivatives.
- **Tests / evidence:** isolated empty journal, filtered-out journal, no picks, no-photo/unrated records, denied media and long-content fixtures; assert no private data/zero synthesis. Capture 320/390/768/1440, keyboard focus and root-text 200%; verify true browser text zoom manually when available. Check contrast on actual imagery and measure hit rectangles.

## P0-4 — Reviewed visual and state baselines

- **Objective / current:** A12; screenshots exist but no pixel regression matcher guards the shared experience. Add a small deterministic baseline set, not hundreds of brittle snapshots.
- **Files:** existing Playwright config/tests and QA capture helpers; dedicated fixtures under existing isolated preparation tools; `docs/qa/` baseline manifest and verification notes. No owner fixtures.
- **Dependencies:** capture current baseline before P0 edits, approve new baselines only after P0-1/2/3 review. Fixed font load, image decode, viewport, session state and content; pin browser/build metadata.
- **Acceptance:** reviewed snapshots for 390/1440/1920 Home/Explore/Visit/Place/error hero/footer; representative state crops for nav progress, off/on/print, filter/empty/error and viewer. Use `toHaveScreenshot` with documented tolerance justified by text rasterization; never blindly update snapshots to silence a failure. Geometry/state/network assertions remain beside pixels.
- **Edges / tradeoffs:** bounded animation versus screenshot auto-fast-forward, sessionStorage first paint, failed images, theme/font load and unstable content. Freeze a local effect only for designed intermediate frames; never treat slowed captures as performance evidence.
- **Tests / evidence:** introduce one intentional CSS shift locally to verify the matcher rejects it, then revert that probe. Record unmodified real-time interaction video separately. Keep baseline review manifest with route/fixture/width/state/browser/build and unknown device coverage. Preserve old QA directories unchanged.

## P1 — Vending-machine physical response

- **Objective / current:** A04/A05; power, detents, masked print and identity already work. Refine the contact→actuation→response chain and optional paper clearing without reimplementing the machine.
- **Files:** `resources/js/pages/archive.js`, `components/vending-input.js`, `components/vending-state.mjs` only for necessary legal state changes, `resources/css/vending-machine.css`, discovery hardware/print views only if needed, Node reducer and machine browser tests. Audit all `clearPrint`, `motion`, `settle`, `loadPage`, `bindRotary` callers before edits.
- **Dependencies:** P0 baselines/legibility; existing exact-visit and cancellation tests must stay green. First make a short storyboard with ownership, interruption and reduced behavior; implement one mechanical improvement per change.
- **Acceptance:** press/contact feedback by next frame; 32° commitments and <9° residual remain; socket/LED/reset anchors unchanged; no visible extra controls/hints. Snap/boot/print timings remain 270/960/820 unless a reviewed reason changes their tokens/tests. If adding retraction, semantic paper identity clears immediately, old sheet cannot be retrieved and new actions cannot race the exit. New selection/page/reset/off cannot strand print or lamp. No fresh idle loop.
- **Edges / risks:** fast wrapping/rapid input, page failure while turning, power removal during pending fetch, hidden tab, resize, Escape/lost capture/scroll, blocked storage, runtime reduced motion, missing photograph and no/single page. New transitions must not introduce impossible states or late continuations.
- **Tests / evidence:** reducer assertion for each new transition; all 19 machine cases plus repeated/interrupted retraction. Production-time off→pickup→snap→boot→selected→print→ready→retrieve and reverse/off recording, contact/mid/end frames at desktop/compact, reduced/no JS/keyboard/touch. Compare frame/paint trace against baseline; no animated viewport-sized filter or dependency.

## P2 — Explore interaction-led discovery

- **Objective / current:** A06/A07; three photo links and practical fragments work, but discovery/selection/loading feels less tactile than the cabinet. Strengthen a photographic bento discovery board and result transitions without obscuring the form.
- **Files:** `components/discovery/curiosity-index.blade.php`, `public/explore*.blade.php`, `resources/css/cinematic.css`, `discovery.css`, `archive-filter.css`, `resources/js/pages/explore.js`, `filter-drawer.js`, shared image fallback; `CuriosityIndex` only if truthful content selection needs change; existing Explore feature/browser tests.
- **Dependencies:** P0 contracts/baselines, then reuse P1's token grammar only where appropriate. Preserve ExploreState/ExploreQuery/PublicOptions; no second machine or separate query engine.
- **Acceptance:** complete tab/search/all filter dimensions/sort/view/pagination URL contract passes; hero remains mounted; Apply/Cancel/reset semantics intact; price context feedback correct. Immediate busy cue, stable committed results on refresh, bounded group transition and meaningful hover/focus/press. Actual delayed waits can show layout-matched skeletons after ~200ms; fast loads skip them; failure reveals retry and keeps data. No optimistic URL/count commit or fake delay. Semantic mobile bento order and no horizontal overflow.
- **Edges / risks:** rapid navigation/Back/Forward, request timeout/abort/503/invalid fragment, zero results/zero options, long multiselects, all-photo failures, missing ratings, reduced motion/offline/no JS. Avoid confusing a decorative tile animation with applied filter selection.
- **Tests / evidence:** retain full discovery suite; delayed request + fast response + failure + stale request tests, focus after replaced controls, history rollback, original-on-demand and same-item query assertions. Capture initial/hover/selected/filter pending/loading/success/empty/error/retry at 375/768/1440/1920, with uninterrupted interaction recording. Compare network, layout shifts and repeated lab samples.

## P3-1 — Visit storytelling refinements

- **Objective / current:** A08; existing Dossier already has a strong story hierarchy. Add restrained tactile photo/note grouping and consistent feedback only where it helps reading.
- **Files:** `public/visit.blade.php`, domain tasting-note/gallery/image/viewer components, `resources/css/dossier.css`, `resources/js/components/viewer.js`; shared dialog core only for a demonstrated shared issue; related feature/browser tests.
- **Dependencies:** P0 width/type/state baseline and P2 media/wait patterns; no domain changes.
- **Acceptance:** 60–75ch narrative, readable wide cover and meaningful facts/bento; optional sections omit honestly, recommendations stay distinct from score. Photo-free item/visit remains composed; gallery original waits/failures give stable geometry, status, fallback/retry/close and focus restore. Reveal never blocks content/no JS; reduced motion shows settled content immediately. No original request before explicit open.
- **Edges / risks:** no photos, one/40 photos, revoked original, all-null or partial criteria, missing total/free item, long titles/prose, external Maps absent, keyboard arrows in input, narrow landscape/200% text. Do not fragment one story into a generic card catalog.
- **Tests / evidence:** nullable/historical/visibility feature cases, original network observation, fast gallery changes, error/retry, Escape/focus restoration; full story sections and intermediate viewer wait/failure at 375/768/1440/1920 plus reduced/text enlargement. Measure media bytes and LCP after width changes.

## P3-2 — Place history and missing-photo composition

- **Objective / current:** A09; shared atmosphere exists, but header assumes two columns and photo selection depends on the current history page. Give chronology and sparse-data layouts explicit composition.
- **Files:** `public/place.blade.php`, `.place-dossier` in `cinematic.css`, reused history/visit-card components; `PublicController::place` or `Place` photo relation only if a stable published-photo query is justified; PHP/browser fixtures/tests.
- **Dependencies:** P0/P3-1 reusable environment; inspect photo-query consumers before any service change.
- **Acceptance:** no-photo header intentionally uses available width; place name/location and rated-visit count stay primary; dated visits remain independently navigable with own score. If stabilizing hero selection, only eligible published photos and bounded queries, stable across page 1/2. No published visits still 404; no fake menu/current prices, Maps or community-review language.
- **Edges / risks:** all visits unrated/photo-free, >18 visits, older eligible photo versus private newer photo, deleted place/unpublish, long address/name, no location. Do not add N+1 or fetch every visit merely to find a photo.
- **Tests / evidence:** new isolated >18-visit/photo-free/null fixtures, pagination and photo provenance assertions, query-count check if query changes. Desktop/mobile header/history/footer and empty-photo captures; keyboard link order and reduced-motion journey Visit↔Place.

## P4 — Integrated accessibility, performance and consistency gates

- **Objective / current:** A10–A12; strong Chromium state checks exist, but full device/assistive/performance/visual coverage is not established. Close evidence gaps after each scoped feature, then run one integrated gate.
- **Files:** existing PHP/Node/browser tests, controlled fixture/capture scripts, shared affected UI only for supported defects, maintained docs and QA manifests.
- **Dependencies:** accepted P0–P3 tasks; do not wait until P4 to fix an accessibility regression.
- **Acceptance:** all seven widths plus breakpoint boundaries/short landscape, real 200% text/zoom and reflow, keyboard-only touch and reduced-motion load/runtime journeys. No hidden focus, trapped dialog, misleading announcements or broken fallback. No normal-text contrast below 4.5:1 or important UI below 3:1. Verify WCAG 2.2 AA criteria manually as applicable; do not call axe certification. Record physical Safari/iOS/Android and screen-reader results when available; otherwise label unverified explicitly.
- **Performance:** three comparable cold/warm lab samples per changed route with network/device/fixture/build documented; LCP/CLS, interaction trace and image bytes; investigate >10% median regression or new layout instability. Field targets LCP 2.5s / INP 200ms / CLS .1 remain unverified until field data exists. No new giant texture, full-viewport animated filter or WebGL engine without justified profiling/fallback.
- **Edges / risks:** six real HTTP statuses including copy/actions/JSON/Retry-After and isolated maintenance; real errors must not be simulated merely by changing numerals. No owner server `down`, refresh, seed or media mutation. Use isolated SQLite/functional fixtures and separate nonstandard-port MariaDB only when needed.
- **Tests / visual evidence:** relevant PHP, Node, Playwright + build; reviewed pixel comparisons; all status scenes and negative privacy/media assertions; manual access matrix, production-time video and repeat lab manifest. Update implementation status and docs with actual passes/failures/unknowns before claiming ready. Scope any newly discovered defect as its own small repair.

## Must fix versus experiments

Must fix: confirmed hover regression, shared wide shell/identity contract, unreadable/undersized essential controls, misleading empty/wait/error behavior, stale state/identity/privacy failures and baseline gaps. Preserve working content/query/mechanics throughout.

Creative experiments after those gates: local latch resistance, short ready-paper retraction, deeper photo/note bento, shallow 3D selection layers and subtle story reveals. Evaluate one hypothesis at a time against discoverability, reading effort and measured rendering cost. No dependency or whole-page rewrite is a default deliverable.

## Next implementation prompts

1. “Implement only P0-1 in `docs/roadmap/frontend-experience.md`. Read AGENTS and canonical contracts, restore shared copper hover-progress behavior including standalone errors, add focused state/frame regression coverage, preserve native navigation, and update status/evidence.”
2. “Implement only P0-2 after capturing the current baseline. Apply the canonical wide public shell and archival environment/footer contract across Home/Explore/Visit/Place/errors, retain admin practicality and exact cabinet geometry, update responsive image sizes, verify the complete width matrix and record before/after evidence.”
