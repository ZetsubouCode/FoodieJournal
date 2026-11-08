# CODEX TASK — Foodie: Frontend Experience Audit, Canonical Documentation, and Improvement Roadmap

**Role:** Act as senior Laravel architect, frontend creative technologist, UI/UX design director, interaction/motion designer, accessibility specialist, and QA engineer. Be critical, evidence-driven, and willing to propose bold but maintainable designs.

**Primary objective:** Audit the current repository and its history, rebuild the canonical documentation so future work preserves a coherent Foodie experience, then produce a prioritized, testable frontend improvement roadmap. **This task is an audit/documentation/planning phase, NOT a blanket frontend redesign.** Do not silently implement a new UI, remove working features, or claim unperformed browser tests.

## 0. First: establish actual repository state

1. Inspect `git status`, branch, HEAD, recent commit history, modified/untracked files. Preserve all working-tree changes. Never assume GitHub `main` equals local workspace. Never reset, checkout destructively, clean, or overwrite local changes without explicit permission.
2. Inspect all existing root product/ERD/sitemap/flow/design specifications, `README.md`, `AGENTS.md`, `.ui-craft/*`, current and historical `docs/*`, `codex_instruction/*`, current routes/controllers/services, layouts, Blade components, Vite/CSS/JS, media, tests, and QA reports.
3. Trace actual browser experiences for `/`, `/explore`, `/visit/{slug}`, `/place/{slug}`, and applicable error pages. Find real route URLs from the app and controlled fixtures; do not invent them. Inspect real data safety and visibility rules before creating test content.
4. Use `git log`, `git show`, and `git diff` to compare the old Explore-era Memory Archive/vending art direction against the current post-move experience. Useful historical anchors from the previously inspected GitHub branch (verify these against **local** history): `4f6365b` (older Explore machine), `801cc57` (Homepage migration and cinematic public redesign), `b56db2c` (later vending refinements). Do not blindly restore historical code; extract useful **visual and interaction principles**.
5. Inspect actual user-visible UI in a browser when available. Record evidence, not assumptions. Include desktop and mobile screenshots and meaningful intermediate interaction frames. If no working browser/runtime or fixtures exist, label observations as **code-inferred**, not browser-verified.
6. Protect owner `.env`, local MariaDB/MySQL, uploaded media, existing admin account, and any personal content. Never run destructive migrations, refresh or re-seed a live database. Use isolated fixtures for testing.

## 1. Nonnegotiable product identity

Foodie is a **single-author personal culinary memory journal**, NOT a restaurant, food-sales site, community-review site, recipe site, or objective food-ranking authority. Public users explore published records without accounts; only the owner manages content. Language: Indonesian. Currency: IDR. Local display timezone: Asia/Jakarta. Backend/domain correctness and published-only data access remain intact.

Public-facing information architecture:
- **Home:** one distinctive, playable, vintage/retro **Memory Archive vending machine** as the hero. The main attraction is interacting with real physical-looking objects; secondary editorial sections (latest visits, personal picks) still communicate real content.
- **Explore:** a hybrid **interactive discovery board + bold photographic editorial discovery**, with practical and complete Visit/Food/Place browse, search, filters, sort, view, URLs, pagination, empty/error behavior. It is NOT a second vending machine and NOT a plain commodity catalog.
- **Details:** `/visit/{slug}` and `/place/{slug}` are related facets of one **cinematic + tactile archival storytelling** experience, differing in information hierarchy but sharing atmosphere, navigation, motion grammar, and reusable components.
- **Admin:** remains pragmatic, efficient, comprehensible, accessible, and safe; high-intensity animations do not belong in routine data entry.

## 2. Art direction — capture old mood, improve layout utilization

**Visual reference:** the overall mood of the **older Explore when it hosted the vending machine**. Preserve and translate its charcoal/sepia/brown/copper/amber materials, warm localized lighting, photographic richness, texture, vintage/retro personality, and cohesive footer. Do not reintroduce old navigation/layout problems.

**Width requirement:** current and future navbar, main content, atmospheric layers, and footer must visually make good use of available viewport width. Avoid an arbitrarily narrow central rectangle with large, unjustified side voids. Use a responsive fluid/wide layout and purposeful gutters; consider full-bleed scene layers with content-aligned text and controls. This is **not** a request to stretch every paragraph edge-to-edge: long-form reading measure remains comfortable (roughly 60–75ch where appropriate), focal images and bento layouts can be wide, and no horizontal overflow occurs. Document measurable desktop/mobile width rules and screenshot checkpoints; don't hardcode one viewport.

**Cross-page consistency:** Home/Explore/Detail may have distinct background crop, intensity, and atmospheric treatment, but must unequivocally look like the **same world**. Footer, navbar, surface treatment, color roles, typography, and transition language must remain cohesive. An atmospheric change should be a deliberate art-direction variation, never accidental CSS divergence.

Use semantic design tokens (color, type, spacing, shadows, borders, radius, focus, motion, responsive density), not scattered one-off magic values. Vintage/retro art direction need not mean tiny fonts, illegible texture, or forced skeuomorphism everywhere. Preserve proper contrast, focus and readability.

## 3. Motion and game-feel philosophy

Set the **relative interaction ambition**: **Home 5/5**, **Explore 3/5**, **Detail 3/5**. These are qualitative creative targets, **not** instructions to make every animation long or visually noisy.

Home must have outstanding vintage mechanical game feel. Prioritize, in order:
1. **Physicality (A):** controls feel weighted, resistant, snappy and causal; knob detents, button compression/rebound, lamp pickup/insertion, contact response, tray/paper handling, light and shadows communicate mechanics.
2. **Choreography (B):** input -> anticipation -> mechanical actuation -> system response -> content reveal -> settled/ready state. Effects are coordinated, interruptible, and consistent with physical geometry. Never animate unrelated properties just to add movement.
3. **State feedback (D):** hover, focus, press, active selection, loading, success, empty, error, disabled, interruption, cancellation, and recovery look/feel distinct and actionable.

Ambient motion and occasional surprise are optional later layers, not P0. Ensure meaningful off-state cues and discovery affordances without distracting loops. Keep important current contracts (one machine on Home; no extra pagination arrows near knob; index-controlled rotary; square reset control; hanging tube and latest approved placement; real published record identity; reset/power behavior; accessible alternatives). The exact mechanical contract must be derived from the latest code + latest explicit approved design decision, not stale reports.

**Correct missing navbar behavior:** This is specifically **on mouse HOVER of a navigation tab**, not route-loading animation or Explore loading feedback. A thin line/underline in an approved **warm copper/amber accent** should **fill gradually from LEFT to RIGHT like a progress bar** while hovered. Define hover enter/leave/re-enter, selected/active state, keyboard `:focus-visible`, and `prefers-reduced-motion` behavior. It is a visual affordance; do **not** add artificial network delays or force SPA navigation. Reuse the effect consistently where appropriate; avoid putting it on every unrelated link. Current CSS may have only a static `[aria-current=page]::after`; this is a regression candidate, not proof of historical implementation. Find evidence in history before asserting its original design.

Explore/Detail should feature tasteful reveal, tactile selection, real loading feedback, clear hover/focus, photo interactions and layout-responsive motion. Keep information the main attraction. Use animation tokens and shared helpers where justified. Prefer opacity/transform, bounded layers, interruption-safe async state, reduced-motion alternatives, and no expensive continuously animated full-viewport filters.

**3D elements are allowed and encouraged for meaningful spatial depth** (CSS perspective, 2.5D layers, 3D transforms; WebGL only if justified and measured). Do not impose a blanket ban on 3D. Preserve touch, mobile, keyboard, fallback and no-JS navigation; avoid gratuitous heavy libraries and fragile perspective that makes content unreadable.

## 4. UI/UX requirements — explicitly required, not optional polish

- **Empty state:** design context-specific states for zero published visits, filtered-out results, missing photos, no ratings, no recommendations, unavailable data, and a newly initialized journal. Provide meaningful next actions when appropriate; never fake content.
- **Skeleton loading:** design layout-matched placeholders for actual asynchronous loading and image/content waiting when beneficial; avoid fake wait, flash-of-skeleton for instant responses, layout shifts and hidden failures. Define initial/refresh/optimistic/progressive transitions and stable content geometry.
- **Complete states:** initial, idle, hover, focus, active, processing, loading, success, empty, error, disabled, updating, cancelled/interrupted, reduced motion. Different domains may need different subsets; avoid unnecessary state proliferation.
- **Meaningful visual design:** purposefully combine editorial hierarchy, typography, materiality, imagery, dividers, labels, icons, varying scale, layering, and bento layouts. Use **bento grids** for grouping diverse information where they improve recognition and scanning; keep mobile ordering semantic and avoid bento as uniform generic cards or visual clutter.
- **Readability/icons:** meaningful, visually consistent icons for dates, places, price, rating, navigation, actions, status and other concepts where they reduce scanning effort. Icons supplement text or have accessible labels; never rely only on ambiguous pictograms. Minimum comfortably readable type, contrast, paragraph measure and line height.
- **Buttons:** avoid visually cramped/tiny padding. Define coherent size variants with sufficient horizontal/vertical padding, consistent clickable areas (aim around >=44x44 px for primary touch controls where feasible), clearly visible focus and disabled state. Do not shrink touch area to solve layout overflow.
- **UX laws/heuristics applied pragmatically:** Hick–Hyman (choice complexity and progressive disclosure), Fitts (target size and access distance), Miller (information chunking without literal '7±2' enforcement), Gestalt grouping, Jakob's law (predictable behavior), recognition over recall, Nielsen usability heuristics, Tesler (complexity), Doherty threshold (feedback/responsiveness), peak–end (memorable reveal/closure), and cognitive load. Each cited principle needs a concrete Foodie-specific application; do not sprinkle buzzwords or distort scientific claims.
- **Accessibility:** WCAG 2.2 AA target, robust touch and keyboard behavior, native semantic HTML, focus management, live announcements for real changes, 200% text resize/reflow, contrast, reduced motion, efficient image alt text, error recovery. Ensure custom alert, modal and toast foundations remain consistent, not duplicated across pages.
- **Performance:** prioritize LCP/INP/CLS, efficient media derivatives, responsive sizes, lazy loading beyond first paint, avoid render-blocking/oversized textures, costly paint and WebGL where unjustified. Measure where tools permit rather than inventing numbers.

## 5. Repository-specific audit checklist

Inspect at minimum (paths may have changed locally):
- `AGENTS.md`, `README.md`, `product-blueprint.md`, `sitemap.md`, `erd-database-specification.md`, `user-flow-wireframe.md`, `design-system-specification.md`, `.ui-craft/brief.md`, `.ui-craft/tokens.md`.
- `resources/views/components/layout/public.blade.php`, `resources/views/components/site/header.blade.php`, `resources/views/components/site/footer.blade.php`.
- `resources/views/public/home.blade.php`, `resources/views/public/home-archive.blade.php`, `resources/views/public/explore.blade.php`, `resources/views/public/explore-fragment.blade.php`, `resources/views/public/visit.blade.php`, `resources/views/public/place.blade.php`, `resources/views/components/discovery/*`.
- `resources/css/tokens.css`, `public-chrome.css`, `cinematic.css`, `home.css`, `discovery.css`, `dossier.css`, `vending-machine.css`, `app.css`, and relevant component styles.
- `resources/js/app.js`, `resources/js/pages/archive.js`, `resources/js/pages/explore.js`, `resources/js/components/vending-input.js`, vending state reducer, modal/gallery/filter logic, and Playwright/PHP tests.
- `docs/cinematic-redesign.md`, `docs/vending-machine-interactive.md`, `docs/vending-machine-refinement.md`, `docs/hanging-lamp-correction.md`, older Memory Archive QA/reference evidence, `docs/implementation-*`, and `codex_instruction`.

Investigate and report with **file/selector/function-level evidence**:
1. Why the navbar hover-progress underline is missing; whether it was truly in Git history and where it was defined.
2. Why atmosphere and footer composition feel different between older Explore reference and current Home/Explore/Detail; separate CSS differences from subjective design judgments.
3. Whether max content width, breakpoints, nested containers, and footer background produce disproportionate unused side areas at common desktop widths.
4. Which animation sequences in current vending machine lack physical response, choreography or state distinction. Look at real runtime transitions, not just `@keyframes` counts.
5. Why Explore/Detail are less interactive; identify opportunities without making reading/filtering cumbersome.
6. Status and correctness of empty/loading/skeleton/error states, image fallbacks, button hit areas, icons and accessible focus.
7. Which docs contradict the latest explicit decisions, especially side-hung lamp vs inside-panel references, delayed visible pickup text vs material-only hint, and superseded pagination controls.
8. Whether tests actually guard visual regression and interactive state behavior; propose missing tests and screenshot/video baselines.

## 6. Canonical documentation architecture (create/update as justified)

Do not keep appending newer paragraphs above contradictory older ones. Build a **single clear chain of authority**, preserve historical documents as historical evidence, label superseded design decisions, and link everything. Suggested structure:

1. `AGENTS.md` — concise, high-priority instructions for all coding agents: read order, what is locked vs experimental, no regression, verification, documentation maintenance, no destructive DB actions, definition of done. Keep it short enough to be followed reliably; link to long details instead of duplicating them.
2. `README.md` — what Foodie is and is not; public site architecture; setup, scripts, technical stack, entry points and docs index; accurate current status. Keep working setup instructions and safety precautions.
3. `docs/product-vision.md` — purpose, audience, IA, core content/data integrity, experience principles and non-goals.
4. `docs/art-direction.md` — historical approved references, material/palette/photography/type, wide-layout strategy, per-page atmosphere variants, navbar/footer visual contract, tokens and immutable decisions.
5. `docs/motion-interaction.md` — shared motion grammar, Home 5/Explore 3/Detail 3, hover underline, game feel, tactile sequences, accessibility, reduced motion, interruptible state, performance budgets/gates and concrete examples.
6. `docs/page-experience.md` — page-specific responsibilities, sample states/interaction journeys for Home/Explore/Visit/Place, empty/skeleton/error and bento use.
7. `docs/implementation-status.md` — evidence-backed implemented/partial/missing/unknown table, actual gaps, regressions, baseline commit and next actions.
8. `docs/ux-principles.md` or concise section in page-experience — concrete application of design/UX laws with decision rationale; avoid a disconnected academic encyclopedia.
9. `docs/adr/` (or equivalent decision log) — approved decisions, reversals, design rationale and references; add clear superseded markers to historical narrative reports. Do not destroy QA history.
10. `docs/roadmap/frontend-experience.md` — prioritized remediation with dependencies, file scope, risks, tests and acceptance criteria.

Preserve the original root product/domain specifications as authoritative for unchanged business rules. If their design language conflicts with newly approved current direction, update/annotate **only the pertinent sections** with clear history and references; never silently rewrite domain truths. Explain document precedence. Ensure source-of-truth docs contain no contradictions and no aspirational feature is mislabeled as already implemented.

## 7. Roadmap design and prioritization

Deliver actionable phases, for example:

- **P0 Visual identity + state baseline:** recover historical mood reference, establish responsive wide shell, navigation hover-progress contract, atmosphere/footer alignment, semantic tokens, screenshot baselines, accessible button sizing/readability. Identify breaking visual regressions first.
- **P1 Vending-machine game feel:** audit input/physicality, reducer-driven sequences, knob/tube/button/LED/tray feedback, orchestration, state clarity, reduced motion and regression QA. Preserve interactions and real data.
- **P2 Explore interaction-led redesign:** bento/editorial discovery board plus full query/filter functionality, transitions, feedback, skeletons, empty/error, URL persistence/history, performance.
- **P3 Visit/Place storytelling:** atmospheric continuity, cinematic hero, photo/detail/bento composition, readable text, gallery behaviors, related place timeline, honest missing data and reduced motion.
- **P4 Cross-page polish + gates:** accessibility, responsive screens, regression checks, performance, consistency, documentation refresh.

Reprioritize based on evidence, not these labels alone. For every task: name objective, inspected current behavior, expected behavior, affected files/components, dependencies, tradeoffs, measurable acceptance criteria, edge cases, tests (manual/automated), and a visual evidence plan. Separate 'must fix' from 'creative experiment'. Avoid proposing a complete rewrites or dependencies without measured value.

## 8. Validation evidence and acceptance

When runtime is available, record 320/375/390/768/1024/1440/1920px render checks, hover-progress **starting/mid/end** frames, keyboard focus, reduced-motion and 200%-text states, off/boot/selected/printing/retrieval vending states, Explore loading/filter/empty/error, Visit/Place missing photos/rating, footer/nav cross-page comparison. Do not equate screenshot smoke tests with full accessibility certification.

Run feasible non-destructive checks (`git diff --check`, tests related to docs/code if touched, and relevant build/test commands). Report commands, results, environment limitations, and evidence locations. Update existing test scripts only if within planning scope and safe; otherwise write exact future test requirements.

**Document-phase definition of done:**
- An accurate baseline audit exists and distinguishes **verified**, **code-inferred**, and **requested target**.
- Contradictory active documentation is resolved with traceable historical references.
- README/AGENTS and canonical docs give Codex one coherent creative and engineering direction.
- Current-vs-target gap matrix exists for Home, Explore, Visit, Place, nav, footer, states and accessibility.
- Roadmap and acceptance gates are concrete enough for Codex to execute in small, reviewable tasks later.
- No working site behavior, owner data, or previously approved mechanics are accidentally changed.
- Finish with a concise user-facing report describing audit results, changed documents, gaps, open risks, evidence, and next proposed implementation prompt(s). Explicitly state if any verification could not be performed.

## 9. Required sequencing and communication

**STEP 1 — Audit**: inspect actual repo/history/runtime and create an evidence-backed findings matrix.
**STEP 2 — Resolve source-of-truth conflicts**: classify active vs historical docs; draft canonical contracts and update links.
**STEP 3 — Update documentation**: write/repair AGENTS, README, canonical docs, mark superseded rules.
**STEP 4 — Plan implementation**: create scoped priorities and testing gates; do not perform wholesale UI changes as part of this phase.
**STEP 5 — Validate and report**: check docs for consistency, produce structured final report, list unverified assertions.

Do not stop at a superficial audit or a vague list of animations. Exercise senior design judgment, but defend suggestions with user-approved art direction, actual repository evidence, accessibility, maintainability, and tangible improvements to the visitor experience.