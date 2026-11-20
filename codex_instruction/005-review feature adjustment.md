FoodieJournal — Food-Only Rating & Visit Observations
Codex implementation instructions | 19 November 2026

Repository: ZetsubouCode/FoodieJournal
Audited baseline: main at 5a4adfbe224c6b688b2de88f533801149fda82ae (reinspect HEAD before implementation).
Decision status: Approved by owner: Only individual food/drink items receive entered numeric ratings. Non-food positives and complaints belong in optional Visit-level Kelebihan/Kekurangan. Place retains a clearly labeled derived average rating of rated dishes (Option A). This supersedes the earlier five-criteria Visit rating model.
0. Execution contract
Implement the change, including clean schema and source cleanup, not merely a proposal. Inspect the current worktree and dependencies first; preserve unrelated and uncommitted work. Do not push, deploy, modify owner production data, reset/reseed any owner database, or delete media. Work within the existing Laravel/Blade/MySQL architecture and singular MySQL table naming. No new framework, SPA, scoring library, REST endpoint, extra admin screen, or broad visual redesign.

Obey AGENTS.md. For UI work, try the referenced personal UI Craft skill; if absent, use .ui-craft/brief.md, .ui-craft/tokens.md, design-system-specification.md §1 and docs/page-experience.md. Existing semantic tokens and Blade components are mandatory. Latest explicit instructions in this file override older product/rating rules only within the scope of this change. Use an ADR for the decision reversal.

Before editing:
1. Record actual HEAD, git status and any user changes. If HEAD differs from the audited baseline, inspect differences and adapt paths/implementation. Never overwrite user modifications.
2. Trace every consumer of service_rating, atmosphere_rating, cleanliness_rating, value_rating, their four *_review text columns, taste_rating, overall_rating, overall_score, rating_score, rated_visit_count, RatingSql, withRating, rating_min and all review/score UI copy. Search migrations, models, requests, save actions, admin/public views, Explore, seeds/factories, tests, CSS/JS, QA scripts and specifications.
3. Examine existing data safely using read-only queries if an appropriate non-system, non-production target is available. Never infer an owner's database from DB_DATABASE=mysql (this is a MariaDB system schema). If a real data target is not accessible, note uncertainty; do not fabricate counts.
4. Read existing publication/privacy, historical price, media, and admin concurrency protections before editing. Keep them intact.
1. Target domain semantics (non-negotiable)
1.1 Primary input
- A Visit Item represents one actually ordered food or beverage during one dated Visit. Food and drinks both belong to the existing visit_item domain.
- The only entered numeric review score is visit_item.food_rating: nullable decimal(2,1), values 1.0, 1.5, ..., 5.0; NULL = Belum Dinilai. Keep the existing HalfRating rule and improve field labels to "Rating hidangan".
- The rating is a holistic judgment of the dish: aroma, taste, texture, doneness, ingredient execution, portion/value of that dish, etc. Do not add per-aspect subratings, weights, automatic AI scoring or a compulsory scoring rubric.
- Keep visit_item.notes as the existing nullable narrative review field; label it "Ulasan hidangan" in admin. Do not create another duplicated item review column.
- A missing rating never contributes zero to an average. Keep exact arithmetic until display; localized display uses one decimal.
1.2 Non-food observations
- At Visit level introduce only two optional TEXT columns: pros and cons. Both accept multiple plain-text lines; each nonblank line represents one observation in original order.
- Examples: helpful service / clean tables / accessible location belong in pros; long waiting time / limited parking / noisy room belong in cons. These fields never influence ratings.
- Food-related praise/criticism, portion-to-price judgment of the dish, taste/aroma/texture belong in the corresponding Item's narrative review, not in Visit pros/cons.
- No fixed subcategories or ratings for service, atmosphere, cleanliness or general value.
- Keep visit.experience as a personal visit story, visit.conclusion as optional editorial closing, visit.total_spent_idr as independent historical total, tags and recommendation labels as independent editorial metadata.
- A Visit may be published without any rated Item, subject to its existing minimum publication requirements. Unrated is not bad; render "Belum Dinilai".
1.3 Derived scores — define once
Visit food score: arithmetic average of all non-NULL visit_item.food_rating values belonging to that Visit, regardless of item order, quantity, price or category. Each rated item occurrence has exactly one equal-weight vote. NULL if none rated. Count rated items separately. Do not persist a manually entered Visit score.

Place food score (approved Option A): arithmetic average of every rated Item occurrence across eligible published, non-deleted Visits of that Place. This is NOT the average of per-Visit averages. Each rated item occurrence has equal weight, including a repeat tasting on a different Visit. NULL if none eligible/rated. Do not persist a manually entered Place rating.

Place counts: rated_item_count = number of eligible rated Item occurrences; rated_visit_count = number of distinct eligible published Visits having >=1 rated Item. Never label either as number of reviewers (only one author).

Eligibility: Place aggregate includes only Visits with status=published, visit.deleted_at IS NULL, an existing non-deleted Place, and associated rated Items. Draft/private/deleted Visit and its Items must never affect public Place score or counts.

Precision / worked cases:
- One Visit: [4.5, 3.5, NULL] => 4.0, rated item count 2.
- One Visit: [4.5, 4.0] => exact 4.25 => displayed 4.3, not 4.2.
- Place: Visit A has [5.0]; Visit B has [3.0, 3.0, 3.0] => Place 3.5, 4 rated items over 2 rated Visits (not 4.0, which would be AVG of Visit means).
- A Visit with only legacy service scores and no food ratings => NULL Visit food score; its Place contribution is none.
Do not change unrelated IDR calculations, numeric input rating step, UTC storage / Asia-Jakarta display, visibility, or editorial recommendation behavior.
2. Schema migration and removal of obsolete data model
Add a new forward migration rather than editing the original already-applied domain migration:
1. Rename visit_item.taste_rating to visit_item.food_rating, preserving its current nullable decimal(2,1) values without rounding/reset. Rename all real consumers. No duplicate taste_rating and food_rating columns should remain in the target schema.
2. Add visit.pros and visit.cons as nullable TEXT (no default empty string).
3. Remove exactly these obsolete Visit columns in the final target schema:
   service_rating
   atmosphere_rating
   cleanliness_rating
   value_rating
   service_review
   atmosphere_review
   cleanliness_review
   value_review
4. The current repository has no known dedicated service/atmosphere/value review table to delete. Inspect actual migrations and schema; remove a dedicated obsolete table only if it genuinely exists and its sole function is this retired model. Preserve place, visit, visit_item, food_type, recommendation_label, tag, media, pivots, historical prices and unrelated tables.
5. Remove related obsolete model casts, fill/save mappings, Form Request rules, Blade inputs, field labels, seeds, factories, stale helper logic and unused CSS selectors/JS handlers after verifying callers. Avoid a stranded legacy fallback calculation.
6. No schema column for Visit/Place aggregates is needed: compute via query/accessor. Keep index choices aligned to actual queries; do not introduce caches, materialized scores or unnecessary new tables.
2.1 Data-loss safeguards for existing records
The owner explicitly wants unused columns removed, but not silent loss of valuable existing note text.
- Before any destructive migration on a real owner database, require an independently verified database + media backup and a read-only audit of non-NULL content in the four legacy *_review columns. Do not run the destructive migration against owner data during this task.
- Old notes may be positive, negative, mixed or neutral; do NOT classify them as pros/cons from rating thresholds, keywords or assumptions. Do NOT append them automatically to unrelated story/conclusion fields.
- If existing owner notes are present, provide a brief, explicit manual review/mapping/export path before running the cleanup on that database. An export must be owner-private, outside the repository, logs and public storage. Mark deployment cleanup as blocked until the owner has preserved/classified notes or explicitly authorized discarding them. Do not silently bypass this safeguard to claim completion.
- Legacy numeric service/atmosphere/cleanliness/value scores are retired, not converted into food ratings. Record that their historical values require backup for recovery and will no longer affect the app. Never synthesize new food scores from them.
- Code migrations and tests must still establish the requested clean final schema on an isolated fresh database. If sequencing is required for an owner with real notes, provide staged migration/deployment instructions rather than leaving unused columns permanently in the final target schema.
- Explain that a schema rollback alone cannot restore dropped historical values; actual restoration requires the verified backup. Make the migration behavior and limitations explicit.
Use Laravel migration APIs compatible with the installed Laravel/MySQL versions and the project's test database. If the existing migration engine requires special handling for renaming/dropping constraints, address it within the scoped migration. Do not introduce plural table names.
3. Backend and validation
Update at least the relevant logic in:
- app/Models/Visit.php, VisitItem.php, Place.php
- app/Support/RatingSql.php
- app/Http/Requests/VisitRequest.php
- app/Services/SaveVisit.php
- app/Http/Controllers/Admin/VisitController.php
- app/Services/ExploreQuery.php and any other actual callers discovered
Requirements:
1. Use one shared, fixed-alias SQL formulation for Visit and Place food aggregates so query scopes, filters and display agree. No user-supplied table aliases or SQL fragments. Keep SQL portable to the configured test engine when feasible.
2. Name derived values semantically (e.g. food_score / food_rating, not "overall visit" in newly written business logic). If old public methods must remain temporarily for compatibility, do not leave divergent calculations or obsolete semantics; update all app callers and tests.
3. Laravel model accessors should match SQL scopes exactly (including NULL and precision), whether Items are eager-loaded or not. Avoid N+1 when listing Home, Explore, Place history, admin list or related Visits.
4. Support both item-level input validation and nullable pros/cons text validation (including the existing TextByteLimit approach). Preserve old-value repopulation on failed form submit; sanitize/escape output as plain text.
5. SaveVisit must preserve existing DB transaction, optimistic version check, creation_token idempotency, item ownership validation, media ownership, relations, soft deletion and publish intent. Update the set of accepted fields, not the transaction architecture.
6. Publication gate continues to allow a dated Visit with a valid Place and either an Item or meaningful narrative (experience, conclusion, pros or cons). No mandatory numeric rating, photo or price; "only total_spent_idr" alone is not sufficient narrative.
7. Repeated visits and repeat item names stay as independent snapshots; do not deduplicate by menu name, quantity or food_type. Item price and Visit total remain separate.
8. New/stale requests cannot modify dropped legacy columns or supply old service-derived scores. No ghost writes or accidental data re-creation.
9. Keep admin authorization, draft/private visibility, media policy, SEO/sitemap and public status scopes unchanged.
4. Admin UI — focused edits, no redesign
Primary files: resources/views/admin/visit/editor.blade.php, admin/visit/item.blade.php, admin/visit/index.blade.php, resources/views/components/ui/rating-input.blade.php where necessary, and relevant admin CSS/JS.

Quick Entry: keep minimal Place, date, title/story, draft flow, slug defaults and currently supported fields. No new mandatory question.

Full Editor:
- Keep existing step navigation and order: Dasar -> Hidangan -> Catatan kunjungan (formerly Ulasan) -> Foto -> Pratinjau & publikasi.
- Under each Item, label the existing single score selector "Rating hidangan" and the existing notes textarea "Ulasan hidangan". Keep type, techniques, cuisine, historical quantity/price, ingredients, allergens and media association unchanged.
- Delete all eight service/atmosphere/cleanliness/value input controls, not merely hide them with CSS. Do not add four new categorical inputs.
- Under Catatan kunjungan, show two optional multi-line textareas "Kelebihan" and "Kekurangan" with concise examples of non-food observations (tooltip/help as appropriate). One line = one bullet on public display; blank lines are ignored. Keep total spent, conclusion, tags and recommendations in this area.
- Do not add repetitive tutorials, long instructional paragraphs, new modal flows, required positives/negatives or additional JS dependencies.
- Preserve preview, status actions, unsaved-change behavior, version/validation errors and anti-double-submit protections.
- Admin Visit index should show the derived food score only with a truthful label/accessible name. "Belum Dinilai" if no rated Items. An optional small "X/Y hidangan dinilai" summary is acceptable if it reuses existing data without N+1; do not make it a new dashboard project.
- Maintain the approved minimal admin style: system font, semantic plain background, existing 80rem shell and shared controls; focus, labels, text contrast and 44px controls remain usable.
5. Public UI — preserve the existing cinematic design
Visit detail: resources/views/public/visit.blade.php and related domain components/styles.
- Hero facts: change "Penilaian keseluruhan" to "Rata-rata rating hidangan"; show computed score with "/ 5", or "Penilaian belum dicatat" when NULL. Optionally show the count of rated Items as secondary factual context.
- Preserve story, recommendations, prices, item taxonomy, photo/gallery, conclusion, related Visit order and media fallbacks.
- Under "Hidangan yang dicoba", display each Item's food_rating and its notes as the food review. Prefer "Rating hidangan" or a concise "Rating" in places where food context is already explicit; do not call it service/overall rating.
- Remove the five-criterion "Catatan penilaian" grid and its prose about averaging criteria. Never render an empty assessment panel.
- Insert an optional "Kelebihan & kekurangan" Visit-level section after tasting notes, before gallery. Display only provided sides. Desktop: two balanced editorial columns using the existing charcoal/ivory/copper semantic system, subtle borders/material and meaningful positive/negative icons. Mobile: vertical readable sequence with Kelebihan before Kekurangan. Preserve source reading order and keyboard access. Avoid loud green/red blocks, e-commerce cards, arbitrary ornamental rules and massive whitespace.
- Each nonblank pros/cons line becomes an escaped list item. Preserve author order and original meaning. No HTML/Markdown execution; no duplicated bullets for empty lines. If both sides empty, remove the entire section. If just one side exists, let it occupy the available content width gracefully.
- Keep personal recommendation labels separate from numeric rating and do not derive recommendations from scores.
Shared cards/Home/Explore/Place:
- Visit Card: rating represents average of rated Items on that Visit, not experience or service. Update screen-reader labels.
- Food Card and tasting note: show each Item's food_rating. Rating NULL remains "Belum Dinilai".
- Place Card and Place Detail: derived average of all eligible rated Items at that Place; label "Rata-rata rating hidangan" or equally unambiguous concise equivalent. Display rated_item_count and rated_visit_count in a readable, compact form. Never call the metric a community/place/service review, and never imply that all dishes at the venue have the same score.
- Place history remains dated, paginated and composed of independent Visit Cards with their own food scores.
- Home's current Memory Archive machinery, navigation, visual assets and layout must remain unchanged except any actual score copy/consumer that depends on the retired model.
- If a score is NULL, use existing "Belum Dinilai"/"Penilaian belum dicatat" semantics; never show 0/5 or invent a score.
- Keep responsive photograph-first editorial design and existing tokens, shared Header/Footer/Alert/Modal/Toast, original-on-demand viewer and reduced-motion support.
6. Explore rating filters and sorting
Relevant code: app/Services/ExploreQuery.php, app/Support/ExploreState.php (only if needed), resources/views/components/domain/filter-panel.blade.php and Visit/Food/Place result components.

Keep existing Visit/Food/Place tabs, URL parameters, pagination, relevant filters, view mode, state restoration and price semantics. Do not silently change unrelated query behavior.

Rating definitions must be explicit and stable:
Tab	Displayed and filtered score	Price context
Food	food_rating of the matched Item itself	Item unit price
Visit	average food_rating of all rated Items of the Visit	Visit total spent
Place	average food_rating of all rated Items across eligible published Visits at that Place	No Place price filter


- Keep rating_min and rating sorting, NULL-last ordering, and precision-aware comparisons; do not round before filtering.
- When Visit/Place is filtered by food_type/category/style, matching eligibility remains governed by the existing item filters, but their score still means the overall food average across all rated Items in that Visit/Place, not just the matched Soto or other matching items. Update the concise filter help text so this distinction is obvious; do not silently convert the metric into a context-dependent score.
- Food tab must apply rating_min to the same matched Item (so Soto 3.0 cannot pass a Food-tab minimum 4.0 just because another Item in the Visit scores 5.0).
- Keep existing item-price vs Visit-total separation. Recommendation labels remain explicit editorial facets, not score thresholds.
- If a future product decision asks for "Soto-only average per Place," that is a separate scope; do not implement it now.
7. Data fixtures, CSS cleanup and docs
- Update DemoJournalSeeder and its data sources to enter only real Item food scores and author-written pros/cons as applicable. Curate demo non-food sentences rather than sentiment-classifying them from numeric values. No old service/rating seeder keys should remain active.
- Update affected factories to food_rating and optional observations; keep demo IDs/links/media associations stable if possible. Do not rewrite unrelated demo photography or taxonomy.
- Remove dead criterion/legacy CSS selectors only when a reference search confirms they are unused. Keep reusable rating input and generic domain components.
- Update the five canonical root specs where the retired five-criteria rules occur: product-blueprint.md, erd-database-specification.md, user-flow-wireframe.md, design-system-specification.md and sitemap.md only where affected. Avoid rewriting unrelated sections.
- Update docs/product-vision.md, docs/page-experience.md, docs/implementation-status.md, .ui-craft/brief.md, .ui-craft/tokens.md only where behavior/labels changed; keep dates and implementation claims accurate. Add an ADR documenting the owner's reversal and Option A's per-Item-weighted Place calculation.
- Keep dated QA reports historical; do not overwrite past evidence or claim old screenshots/tests certify the new UI. Add a concise implementation note with actual test commands/results and remaining limits.
- Search and remove stale active text saying overall Visit = taste+service+atmosphere+cleanliness+value or Place = average of Visit averages. If legacy text must be retained for historical context, mark it superseded.
8. Testing — scoped and proportionate
Do NOT run an expansive visual regression campaign, the entire vending-machine interaction suite, whole-site performance profiling or unrelated backup/security test suites simply because this request changed a rating. No automatic baseline screenshot updates.

Required focused automated coverage:
1. Domain/SQL (essential): one/many/no rated Items; NULL exclusion; values 1..5 with .5 step; exact 4.25 rounding only at presentation; equivalence between SQL eager/card query and Visit model accessor.
2. Place aggregation (essential): item-weighted vs Visit-weighted example above; rated_item_count and rated_visit_count; published vs draft/private/deleted; only service-rated legacy Visit does not contribute; revisit Item occurrences counted separately.
3. Form/save (essential): nullable multiline pros/cons, HTML escaping and preserving line order; invalid item rating (0, 0.5, 4.2, 5.5); existing Item values migrate correctly; old keys not accepted as scoring inputs; save/update/preview and publishing with no food score. Preserve version/idempotency/ownership checks by reusing existing tests.
4. Explore (essential): Food threshold tests the Item, Visit threshold uses all its rated Items, Place threshold uses all published rated Items; sorts/NULL-last and relevant food_type filter; do not conflate item price and Visit total.
5. Public rendering (essential): Visit with pros+cons, only one side, neither side; item rating and text; no old criterion headings; Place/Visit/Food labels and counts; unpublished content still unavailable.
6. Migration schema (essential): on isolated DB, verify eight retired Visit columns absent, pros/cons present and nullable, visit_item.food_rating present with preserved test value, taste_rating absent, singular tables and unaffected history/foreign keys still intact.
Prefer update/extend relevant existing tests in tests/Feature/DomainTest.php, AdminTest.php, PublicTest.php, VisitCardTest.php, PlaceChronologyTest.php and a small new regression file only if cleaner. Update only browser cases directly tied to changed Visit/Place content (e.g. tests/Browser/visit-story.spec.js and place-chronology.spec.js) when assertions/selectors break. Avoid duplicating test cases across files.

Execution strategy:
- Run the relevant focused PHP Feature test files first on the project's existing isolated test configuration. If a migration is DB-specific and an isolated MySQL-compatible test instance is available, run one bounded SQL parity smoke test; never risk the live owner database.
- Run npm run build only if source CSS/JS/Blade asset changes require it.
- Render/inspect the directly affected Admin Visit Editor, Visit Detail, Place Detail and one filtered Explore result at approximately 375px and 1440px; spot-check focus and 200% text where changed. Run only affected browser smoke cases, not the entire pre-existing screenshot suite.
- Run git diff --check and inspect the final diff for unrelated edits, stale old-field references and potential migration data loss.
- If tests or runtime cannot execute, state exactly what was not run and why; never report imaginary PASS results.
9. Final acceptance criteria
- Only food/drink Items have an entered numeric rating; Visit and Place scores are labeled derived food averages.
- Place score is weighted by rated Item occurrences, not by Visit means, and excludes unpublished/deleted Visits. Counts clearly distinguish Items and Visits.
- Pros/cons are plain-text, optional, Visit-specific and never part of a score. Food reviews stay on Items; story/conclusion stay intact.
- The eight obsolete Visit review/rating columns and the obsolete taste_rating column are absent from the final migrated schema. No obsolete dedicated table, validation rule, seed key, score fallback, form control or active UI copy remains.
- No unrelated table, pivot, saved price, visit history, recommendation, media or record is removed.
- Existing notes are not sentiment-classified automatically; owner-data cleanup is guarded by audit/backup and an explicit mapping/export decision when necessary.
- Home, Explore, Visit, Place and Admin display the correct score semantics with NULL states and without N+1 or privacy regression.
- No broad redesign or extra dependencies. Minimal targeted tests and relevant UI checks have documented outcomes.
- Affected root specs, maintained contract docs and ADR agree on the new rules.
10. Codex handoff format
After implementation, provide a compact report:
1. Current base commit and any user-owned changes preserved.
2. Exact migration/schema fields renamed, added and dropped; explicitly state whether it was executed only on an isolated test DB. Note any owner-data backup/mapping blocker.
3. Key files/components changed and how derived ratings work (including counts and Explore scope).
4. Actual test/build/UI commands, pass/fail counts, and anything not verified.
5. Remaining focused risks or owner actions before a real database migration.
Do not claim production deployment, data migration of the owner's live database, or complete accessibility certification without evidence. Stop and ask if preserving existing non-food review text requires a choice that cannot be resolved safely.