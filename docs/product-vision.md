# Foodie product vision

Approved experience contract, 8 November 2026 (Asia/Jakarta). Implementation claims belong in [implementation status](implementation-status.md); this document describes intent.

## Authority and read order

1. Latest explicit owner instruction governs its stated scope. The earlier 004 task authorized documentation/planning; the 9 November P0-H owner instructions authorize scoped corrective implementation under [ADR 002](adr/002-p0-hardening.md), without starting P1–P4 creative work.
2. The five root specifications remain authoritative for product/domain rules: [blueprint](../product-blueprint.md), [sitemap](../sitemap.md), [ERD](../erd-database-specification.md), [flows](../user-flow-wireframe.md), [design system](../design-system-specification.md). Their planning checkboxes and proposed routes are not implementation evidence. Design-system §1 owns visual composition and Indonesian brand voice; `config/error-pages.php` owns approved HTTP status copy.
3. These maintained experience contracts elaborate the latest approved direction: [art direction](art-direction.md), [motion](motion-interaction.md), [page experience](page-experience.md), [UX principles](ux-principles.md). [ADRs](adr/README.md) resolve design reversals explicitly. `.ui-craft/brief.md` and `tokens.md` are concise working summaries, not competing authority.
4. [Implementation status](implementation-status.md) describes observed code/runtime at a named baseline. [Roadmap](roadmap/frontend-experience.md) describes work still to do. Code establishes current behavior, not automatic design approval.
5. Dated implementation reports, QA captures, input packages and old design boards are historical evidence. They cannot reinstate an envelope, lamp tutorial, carousel or dock that a later decision superseded. Use the document classification in the ADR index.

If a new instruction contradicts a locked decision, record the reversal in an ADR and update the affected contract, brief and status together. Do not solve contradictions by accumulating “latest” paragraphs above active opposing rules.

## Product and audience

Foodie is one author's personal culinary memory journal. Readers explore published visits, dishes and places without accounts. Only the owner manages content. Indonesian UI, integer IDR, UTC storage and Asia/Jakarta input/display remain the domain contract.

Foodie does not sell, cook, deliver or book meals. It is not a recipe collection, community review platform, objective ranking service or allergen-safety authority. Kitchen imagery is atmosphere; copy must not imply a Foodie food business. Recommendations are explicit personal endorsements, independent of scores.

## Public information architecture

| Surface | Visitor's purpose | Experience responsibility |
| --- | --- | --- |
| Home `/` | Play with a memory, or enter a real story directly | One vintage Memory Archive vending hero; recent visits and personal picks; ordinary navigation always available |
| Explore `/explore` | Discover and narrow the archive | Interactive discovery board plus bold photographic editorial composition; complete Visit/Food/Place browse/search/filter/sort/view/pagination/history |
| Visit `/visit/{slug}` | Read one dated experience | Cinematic, tactile archival story; photos, narrative, dishes, historical costs, nullable personal assessment |
| Place `/place/{slug}` | Understand context across visits | Related cinematic environment; location and published visit chronology, aggregate with rated-visit count |
| HTTP errors | Understand a failure and recover | Approved Editorial Bold composition, shared chrome and safe status-specific actions |
| Admin | Record and maintain truthful content efficiently | Quiet, accessible forms, progressive enrichment, explicit publication and safe recovery |

Explore has no second vending machine. P1 city/food/category/cuisine/style/recommendation/about routes are proposals; current discovery links use Explore queries. Do not create empty SEO destinations.

## Integrity and non-goals

Reuse `Visit::publishedVisible()`, `Place::publishedVisible()`, `ExploreState`, `ExploreQuery`, `PublicOptions`, `VendingMachine` and the visibility-aware media controller. Eligibility covers cards, counts, related records, options, previews, metadata and new media requests. Draft/private/deleted content stays private. Previously downloaded public images cannot be retracted.

Ratings ignore nulls and round only for display; no rating is not zero. Visit total differs from the item unit-price snapshot. Food type, category, cuisine, technique, ingredient, allergen, tag and recommendation retain distinct meanings. Missing allergen evidence is unknown, never safe. No fabricated records, photos, counters or endorsements fill empty layouts.

No new SPA, animation engine, CMS, public accounts, sound system or WebGL dependency is required by this phase. Meaningful CSS perspective/2.5D/3D remains allowed when readable, accessible and measured. Owner `.env`, credentials, media and databases are protected; use [isolated QA environments](operations.md#test-and-browser-environments).

## Success criteria

A reader can reach a published story without playing, use Explore with persistent compatible query state, distinguish price/rating meanings, and follow Visit ↔ Place history. Play adds memorable physical response while preserving those paths. Wide layouts use the viewport deliberately, mobile remains readable at 320px, and motion reduction preserves every action. Accessibility and performance claims require recorded evidence, not visual confidence.
