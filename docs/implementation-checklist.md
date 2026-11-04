# Foodie P0 implementation checklist

Status: **P0 implemented; available local acceptance gates PASS**. Production rollout and external-device limitations are explicit in the report. Evidence is actual execution, not a claim of universal browser/production certification.

## Phase gates

| Phase | Result | Evidence |
| --- | --- | --- |
| 0: audit and five approved root documents | PASS | Original repository only LICENSE/.gitignore plus user specifications; decisions recorded; source documents preserved |
| 1: Laravel/Vite, semantic tokens and shared shell | PASS | FoundationTest; successful Vite build; Blade view cache; browser/contrast evidence |
| 2: singular schema, domain, authentication | PASS | 31 singular tables, four migrations; DomainTest, IntegrityTest, AdminTest on isolated SQLite and MariaDB |
| 3: admin Quick Entry/full editor/status/taxonomy | PASS | AdminTest and complete real Chromium create/edit/publish/preview journey |
| 4: protected media and viewer | PASS | MediaTest (seven scenarios), original hash, metadata, EXIF 5/7, retries, associations, revocation; Chromium viewer |
| 5: public pages and Explore | PASS | PublicTest (eight scenarios); all viewport widths, staged filters, URL/Back/Forward browser tests |
| 6: SEO, errors, security, backup/restore | PASS | SystemSecurityTest, BackupTest, actual local backup command and read-only runtime inspection |
| 7: integrated QA | PASS | 38 tests / 343 assertions each on SQLite and MariaDB; six Chromium tests; Pint, audits, build, route/view checks |

## Requirement traceability

| P0 requirement | Main files | Automated / rendered evidence | Result |
| --- | --- | --- | --- |
| Admin-only login/logout, CSRF, authorization, no registration | AuthController; AdminOnly; VisitPolicy; routes/web.php | AdminTest; SystemSecurityTest; browser login | PASS |
| Secure first-owner bootstrap without seeded password | CreateAdmin command; User | SystemSecurityTest CLI prompts and second-owner refusal | PASS |
| Useful dashboard/drafts/failed media | DashboardController; admin/dashboard | AdminTest route rendering; browser admin shell | PASS |
| Place CRUD, nullable location, duplicate candidates, safe links | PlaceController; PlaceRequest; Place | AdminTest; inline place browser journey | PASS |
| Taxonomy CRUD/search/inline creation, hierarchy, restricted deletion | TaxonomyController/Request; SuggestionController; Taxonomies | AdminTest cycle, usage, domain distinction and allergen denial; browser selection | PASS |
| Minimal idempotent draft and Jakarta default/input | VisitRequest; SaveVisit; Presentation; visit/editor | AdminTest repeated token, midnight conversion; FoundationTest; browser Quick Entry | PASS |
| Full editor with multiple dishes, historical snapshots, optional fields | SaveVisit; VisitController; item/evidence partials | AdminTest; IntegrityTest; browser two-dish save/publish | PASS |
| Server validation, old-input recovery, stale-version detection | VisitRequest; SaveVisit; field/combobox primitives | AdminTest; IntegrityTest malformed/nested errors and preserved unsaved item | PASS |
| Published/private/draft transitions and protected preview | SaveVisit; VisitPolicy; preview action | AdminTest publish gate/owner denial; PublicTest; browser confirmation/preview | PASS |
| Auto/manual slugs, collisions, stable existing slugs, safe 301 | Slugger; HasSlug; slug_redirect | DomainTest; PublicTest redirects/current eligibility/private reservation | PASS |
| Exact half-step input, null-aware taste/overall/place, rated count | HalfRating; RatingSql; Visit; Place; rating components | DomainTest, PublicTest, FoundationTest; 3.75 displays 3,8 | PASS |
| Nullable IDR unit snapshot separate from total | migration; SaveVisit; ExploreQuery; price component | DomainTest, IntegrityTest, PublicTest; 25,000 item vs 60,000 visit | PASS |
| Cuisine, cooking technique, ingredients, allergen evidence kept distinct | Models/pivots; SaveVisit; evidence UI | AdminTest, IntegrityTest, PublicTest | PASS |
| FDA9/EU14 reference vocabulary, wheat/group distinction, no inferred safety | AllergenSeeder; ExploreQuery; disclosure UI | Idempotent 17-term seed; contains/may_contain/unknown and ingredient-reference tests | PASS |
| Source preservation, private derivatives, MIME/signature/size/pixel bounds | MediaRequest; MediaProcessor; ImageMetadata | MediaTest hash, mismatch/SVG/bytes/pixels, EXIF direction and metadata | PASS |
| Retry/failure lifecycle, same-visit cover and item associations, retained deletion | MediaProcessor; MediaController; SaveVisit | MediaTest negative associations, retries and source retention | PASS |
| Every media request respects current eligibility; original fetched on demand | MediaController; image/gallery/viewer | MediaTest public revocation of all four variants; browser request observation and focus trap | PASS |
| Editorial Home, Visit detail, Place history | PublicController; public views; domain cards | PublicTest; rendered viewport matrix/screenshots | PASS |
| Explore three tabs, same-item AND / dimension OR, descendants | ExploreQuery; ExploreState; PublicOptions | PublicTest; browser filters/count/apply/history | PASS |
| Price/rating sort semantics, null-last, stable pagination, URL persistence | ExploreQuery; ExploreState | PublicTest sort injection, ties, prices, tab reset, pagination | PASS |
| Shared Blade shell, semantic tokens, responsive fields/cards | components/layout/site/ui/domain; modular CSS | FoundationTest; 320/375/768/1024/1440 reflow; 200% text; contrast checks | PASS |
| Alert, dialog, toast, combobox, empty/loading/error states | resources/js/core/components; UI primitives | Browser axe, keyboard combobox, dialog Tab/Escape/focus restore, unsaved cancellation | PASS |
| Public privacy on options/counts/cards/detail/SEO/related | publishedVisible scopes; PublicOptions; PublicController | PublicTest negative tests for draft/private and unpublish | PASS |
| Metadata/canonical/OG, sitemap, robots | shared layout; PublicController; sitemap view | PublicTest; SystemSecurityTest | PASS |
| Branded 403/404/419/429/500/503 and security headers | errors; SecurityHeaders; bootstrap | SystemSecurityTest; browser rendered 404 | PASS |
| Free DB + media backup/isolated restore, hashes/counts/FKs | JournalBackup; BackupJournal; RestoreJournal; operations | BackupTest on both engines, actual local backup, original + variants + cover/link restore | PASS |
| No N+1 card growth, native whitelist queries, relevant indexes | ExploreQuery/RatingSql; migrations | 13 queries for one and eighteen cards; real EXPLAIN uses status/date/id index | PASS on measured fixtures |
| Durable report and reproducible run/continuation instructions | README; docs/operations; report; docs/qa | Evidence files and command results | PASS |

No P0 implementation item is deferred. Physical-device/Safari/screen-reader QA, real owner photographs, Oracle MySQL 8 specifically and production field INP/Core Web Vitals remain **UNVERIFIED environment/deployment checks**; see the report. P1/P2 remain deliberately excluded.
