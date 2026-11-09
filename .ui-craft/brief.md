# Foodie design brief

Maintained 9 November 2026. [Authority](../docs/product-vision.md#authority-and-read-order), [decision register](../docs/adr/001-experience-authority.md), [actual baseline](../docs/implementation-status.md). Intent below is not a claim that all targets are implemented.

## Identity and composition

One author's Indonesian personal culinary memory journal; public readers need no account. IDR historical prices, Asia/Jakarta display, published-only content, nullable ratings, explicit personal recommendations, no fabricated content or allergen-safety promises. Admin records a minimum draft and enriches it progressively.

Approved art direction: vintage archival warmth, charcoal/sepia/brown/copper/amber materials, localized light, photographic richness, texture and expressive editorial typography. Public Cormorant Garamond + DM Sans remain self-hosted. Shared chrome/environment connect Home, Explore and both detail pages; approved Editorial Bold kitchen scene and config-owned copy remain on errors. Read design-system §1 for canonical composition and brand voice.

Home: one playable Memory Archive hero, chronological editorial recent entries and explicit personal picks. No advanced filters, autoplay rails/counters, duplicate discovery board, auxiliary hero blurb/tutorial or link below the cabinet. Header/footer navigation and real story links work independently of play.

Explore: interactive discovery board + bold photographic editorial browsing, not another machine. Category/city/technique photos and food-type/recommendation lenses use the published-only query engine. Preserve complete Visit/Food/Place search/filter/sort/view/pagination/URL/history. Quiet staged two-pane filter drawer; keep discovery mounted while fragments update.

Visit: cinematic/tactile archival story with cover/text fallback, facts, readable narrative, historical dishes/prices, populated criteria, gallery and conclusion. Place shares the world while emphasizing identity and dated visit history. Original images load only on explicit viewer open. Shared Alert/Modal/Toast and focus restoration remain.

## Locked mechanics — latest approved correction

Keep the 1634×963 cabinet, six real published visits per page and LED centres (1012,267), (1012,405), (1012,546). The tube rests VERTICALLY OUTSIDE LEFT, centred beside RESET. Original socket: left 2.8%, top 27%, width 4.25%, height 28.7%. Two-gusset bracket and bolted sidewall plate; reserved responsive gutter, no pipe. Desktop resting centre 81.55% high, compact centre 134px. Inside-panel and socket-height resting docks are superseded.

Pickup cue is material-only: three finite cap-edge/reflection cycles after .6s on each mounted OFF page, including previously discovered sessions; meaningful interaction dismisses it. Reduced motion retains a static highlight. No visible pickup label, tooltip, tutorial, timer-driven wording or rectangular pickup outline. Keep screen-reader instructions and actionable errors.

Fresh power OFF; valid RELEASE or explicit accessible target activation snaps/locks then boots; reverse returns/powers down. Indexed 32° selector, resistant residual <9°, 160ms detent settle, coherent wrap direction. No visible pagination arrows/replacements. Reset is square recessed and depresses without rotation. Selected window uses brighter code/thin amber frame; LEDs have real hover/focus/press/disabled response; plaques are engraved. Off tray stays locally dark.

Preview and print share exact selected visit ID/photo/link. One sheet, competing print/retrieval actions lock; selection/page/reset/committed off clear ready paper. Pure reducer plus generation/abort guards own legal async state. Home URL stays `/`. Session power/discovery storage fails safely. Legacy Explore collection/surprise compatibility does not drive the cabinet.

## Targets and quality bar

[Art direction](../docs/art-direction.md) defines the adaptive editorial shell (choice 1B, P0-H): up to 152rem, 16px mobile / 24px tablet / fluid 32–64px desktop gutters, warm page variants and an editorial two-line footer. It preserves P0-2 widths through 1920px, uses 2432px at 2560px and deliberately caps further enlargement at 3440px. Keep 60–75ch reading measure. Admin uses system UI fonts, plain semantic background, compact footer and the existing 80rem shell (choice 4A); the cabinet caps at native 1634px without losing its outboard gutter. [P0-H evidence](../docs/qa/p0-hardening/verification.md). Copper left-to-right nav HOVER fill is restored (P0-1, 9 November): 220ms enter / 140ms leave, smooth re-entry, persistent active, immediate keyboard/reduced-motion states. Public and standalone errors share the rule; [verification](../docs/qa/navigation-hover-progress/verification.md).

Home 5/5, Explore 3/5, Detail 3/5 interaction ambition; physicality → choreography → state feedback. Admin stays restrained. Meaningful CSS perspective/2.5D/3D is allowed with accessible/mobile/no-JS fallback and measured cost. No gratuitous library, fake wait or moving full-viewport filter. Variance 7 public / 3 admin, craft 7, density 4 public / 5 admin remain composition guidance.

320px+, comfortably readable type, coherent button padding and ≥44px primary touch goal, visible focus, WCAG 2.2 AA target, real empty/loading/skeleton/error/recovery, keyboard/touch and reduced motion at load/runtime. [Page states](../docs/page-experience.md) and [roadmap](../docs/roadmap/frontend-experience.md) define pending work. Never promote historic QA results to current verification.

P0-3 (9 November) implements honest unpublished/filtered/page-empty/no-food states, explicit absent recommendations and separate missing/failed photo copy. Body excerpts are 1rem, essential metadata normally .875rem, shared buttons and card/filter actions have measured 44px targets. Explore headings and captions reflow at 200% root text without clipping; tile captions must stay in flow. Preserve P0-1 chrome and cabinet anchors. [Dated P0-3 evidence](../docs/qa/readability-states/verification.md). Choice 6A keeps new real-wait skeletons in P2; P0-H verifies current state/retry semantics only.

P0-H (choices 2B+C, 5B, 7B) gives Place a stable published same-visit cover, latest visited_at/id with ready existing hero derivative, independent of history pagination. Without one, genuine title/location/address/rating/rated count/optional Maps form a responsive text/material opening with no empty photo track. Shared primitives are inventoried, saved success announced once, out-of-range history offers recovery. Preserve the older archive's warm materials; no palette/mechanical/storytelling redesign. [Seven decisions](../docs/adr/002-p0-hardening.md).

P0-4 (9 November) adds the reviewed Windows Chromium visual gate: 40 PNGs / 23 cases, fixed-date isolated fixtures, no automatic snapshot updates, demonstrated 8px shift rejection and clean reversion, plus separate normal-time video. [Manifest and review](../docs/qa/visual-baselines/verification.md). Geometry/state/privacy/network tests remain mandatory beside pixels; this changes no UI or design intent.
