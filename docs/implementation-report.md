# Foodie implementation report


## Homepage refinement — 6 November 2026

Applied the owner's follow-up: removed hero instruction/author blurbs, routine visible status, overview slogan and the “Jeda cahaya” / duplicate “Jelajahi koleksi” controls below the cabinet. The collection screen remains the real filtered navigation surface. Live announcements stay off-screen and meaningful error/empty states remain available.

LEDs now use warm luminous cores and local halos, with a softer completed state. Lamp modulation now recurs with fresh random idle intervals (6–22s), duration (1.1–1.6s) and depth. The physical lamp itself provides keyboard-accessible pause/resume; reduced motion, hidden tabs and compact layouts suspend the recurring scheduler. Selection changes do not restart it.

Shared card-meta / meta-icon Blade components add calendar and location-pin icons beside readable metadata in Home editorial entries and Visit/Food/Place cards across public pages. Icons inherit text size/color and remain decorative to assistive technology. No database, schema or owner-content changes.

Current verification: relevant PHP regression **26 passed / 310 assertions**; build, Pint, Blade cache and diff checks passed. Browser regression **18 passed** (2.6m), including live repeated flicker scheduling, keyboard/reduced motion, all card metadata modes, axe and responsive/200% text checks. Idle waits were accelerated in the recurrence unit of the browser test; the visual capture also waits for a real production-timing recurring burst. Visual capture: **29 screenshots / 30 viewport checks, zero page errors, overflow findings or broken images**. [Evidence](qa/hero-refinement/evidence.json), [updated hero](qa/hero-refinement/home-hero.png), [card metadata](qa/hero-refinement/home-recent.png), [recurring lamp dim](qa/hero-refinement/lamp-occasional-dim.png). See [current architecture](cinematic-redesign.md#hero-refinement--6-november-2026).

## Global cinematic redesign — 6 November 2026

Current architecture: [cinematic-redesign.md](cinematic-redesign.md). This section supersedes earlier public-page composition descriptions below. Existing uncommitted archive/filter work was preserved and refactored in place. The five root specifications, current brief, canonical composition/voice guidance, controllers, scopes, layouts, artwork, motion, media pipeline and tests were audited before editing. No schema or owner data was changed.

Implemented: shared public chrome/fonts/container/background variants; native-proportion Homepage Memory Archive; caption-free accessible envelope; aligned LEDs and restrained pausable lamp; editorial recent entries and explicit recommendation gallery; photographic Curiosity Index with matching-item image provenance; separate asynchronous browsing; immersive Visit Dossier; related Place Detail and shared chrome on approved HTTP error scenes.

Validation and final evidence are listed below. The original P0 report follows as historical context.

| Current command / check | Exact result |
| --- | --- |
| `php artisan test --compact` | **63 passed, 1,046 assertions**, 33.96s; isolated SQLite. Includes new chrome/location, dossier snapshot/null criteria and matching-item photographic provenance coverage. |
| `npx playwright test --output tools/.runtime/cinematic-full-browser --reporter=list` | **23 passed**, 2.8m. Public responsive journeys, cabinet/keyboard/touch/reduced motion, drawer/history/error recovery, gallery original access, admin regression and axe scans. |
| Final targeted browser run after source/photo and mobile refinements | **4 passed**, 53.9s: real Curiosity filter navigation, native LED center/ratio checks, archive/drawer axe scans, shared geometry and 200% text. |
| `npx playwright test tests/Browser/performance.spec.js` | **1 passed**, 3.4s after measuring the actual public body tokens. |
| `npm run build` | **PASS**, Vite 7.3.7, 315ms final build. Existing absolute public-asset references remain runtime URLs; actual screenshot/image checks found no failures. |
| `php vendor/bin/pint --test` | **PASS** after formatting the changed PHP files. |
| `php artisan view:cache` | **PASS**, Blade templates cached successfully. |
| `git diff --check` | **PASS**, exit 0. |
| Route / migration inspection | Three existing Explore routes retained; all four existing migrations reported **Ran**. No migration/schema changes. |
| `node tools/capture-cinematic.mjs` | **29 actual screenshots, 30 viewport checks; zero page errors, overflow checks or broken-image findings.** |

Screenshots and machine/lamp intermediate states: [evidence index](qa/cinematic/evidence.json), [Home hero](qa/cinematic/home-hero.png), [recent visits](qa/cinematic/home-recent.png), [personal picks](qa/cinematic/home-picks.png), [Curiosity Index](qa/cinematic/explore-hero.png), [dossier hero](qa/cinematic/visit-hero.png), [tasting notes](qa/cinematic/visit-food.png), [ratings](qa/cinematic/visit-ratings.png), [gallery](qa/cinematic/visit-gallery.png), [mobile Home](qa/cinematic/home-375.png), [mobile dossier](qa/cinematic/visit-375.png). Lamp phase captures additionally wait for navigation photography to decode while holding the actual animation phase.

Validation uses the retained functional fixture on port 8011 and a new, dedicated photographic demo database/media target on port 8012. No owner data, credentials or uploads were seeded, refreshed or rewritten. The earlier database backup/restore implementation was preserved; this visual change did not require another owner backup or restore operation.

The final local Chromium sample recorded **LCP 1,024ms, CLS 0.000832**, unthrottled functional fixtures at 1440×1000. These are one lab sample, not performance-improvement or field Core Web Vitals claims; INP was not measured. See [measurements](qa/lab-measurements.json).

All six requested redesign phases are implemented. No requested code phase remains deferred. Physical-device Safari, assistive-technology reading checks and production field performance remain unverified deployment checks. No new dependencies, paid services, font files, raster artwork, migrations or admin features were introduced. The existing asset/font licenses and protected media pipeline remain authoritative.

Reproduce the photographic preview with `php tools/prepare-cinematic-qa.php`, then `php tools/cinematic-server.php`; open `http://127.0.0.1:8012`. Its content is explicitly labeled demo. Full functional regression setup remains documented in [operations](operations.md). The currently running application on port 8006 uses the same updated build; its data was preserved.


Date: 4 November 2026 (Asia/Jakarta). **Historical P0 implementation record.** At the time of this report, the application ran at loopback port 8027 with the isolated database on 33079, and the recorded acceptance gates passed. The current local runtime has since moved to app port 8006 and an isolated Foodie database on port 3306; see [operations](operations.md) and the [error-page follow-up](error-page-redesign.md) for the later copy and database incident. Port 8000 was already in use, so the existing application there was left intact.

No P0 code item remains deferred. This report does not claim production deployment, universal accessibility certification, physical-device testing or Oracle MySQL 8 verification. Those limitations are distinguished below.

## Historical v1.1 baseline and preservation

Initial HEAD `092758e`; repository contained LICENSE/.gitignore and the five user-supplied root specifications plus the execution instruction, with no application, database, environment, dependency manifests, routes or tests. All five specifications and the full implementation instruction were inspected. There was no runnable pre-change test/build baseline; it was unavailable, not a pre-existing failure.

User source documents remain at their original root paths and were not edited. Original LICENSE and existing ignore rules remain. No existing journal/data/media was removed, no destructive reset/clean or auto-commit was performed, and the pre-existing database service on port 3306 was not contacted. Test refreshes only ran against isolated SQLite memory or NEW `foodie_test_*` databases on loopback port 33079. A test-base guard rejects other destructive refresh targets.

Runtime: PHP 8.2.4, Laravel 12.71.0, Composer 2.5.5, PHPUnit 11.5.57, Pint 1.30.4, Node 26.3.1, npm 11.16.0, Vite 7.3.7, GD/WebP/exif/fileinfo/zip/PDO. MySQL-compatible verification used the installed MariaDB 10.4.28 binary in a newly initialized, separate data directory, bound only to 127.0.0.1:33079. Its root secret and the dedicated restricted local app account are generated privately, not committed or production credentials.

## Implemented by phase

| Phase | Concrete implementation |
| --- | --- |
| 0 | Audit, U1-U10/D1-D7 default decisions, scoped traceability, preservation strategy and `.ui-craft` brief/tokens |
| 1 | Laravel/Blade/Vite foundation, Indonesian UI, explicit UTC/Jakarta conversion; semantic palette/type/spacing/focus/motion tokens; shared public/admin layouts/header/footer/breadcrumb; button/field/select/combobox/rating/price/badge/empty/error/skeleton primitives; native dialog with explicit focus loop/restore and central toast service |
| 2 | Four migrations, **31 singular tables** including framework support tables; all domain models/relationships, factories for owner/place/visit/item, controlled idempotent allergen seed, scopes, SQL precision calculations, slug reservations/redirects, half-step validation, policies/admin guard and secure interactive owner bootstrap |
| 3 | Useful dashboard; place CRUD/duplicate detection/optional coordinates/HTTP(S) links; taxonomy management and searchable/inline creation; Quick Entry idempotent drafts; full editor with multiple items, optional evidence, reviews, historical prices, cover links, optimistic version checks, protected preview and transactional publication/private/draft flows |
| 4 | Private raw source and full-quality cleaned viewer; GD/WebP thumbnail/card/hero, validated signature/extension/bytes/pixels/memory, EXIF-sensitive metadata removal, correct mirrored orientations, source checksum preservation, idempotent synchronous retry/failure lifecycle, same-visit links and retained deletion; every resource request checks current eligibility |
| 5 | Editorial Home, Explore Visit/Food/Place, visit stories and place timeline/aggregate; one filter/query-state engine, descendant taxonomy, OR within dimensions / AND across dimensions on the same dish, contextual prices/ratings, null-last sort, stable pagination, URL chips/reset/back/forward, staged accessible filter dialog and actual async count; unknown allergen warnings and source disclosure |
| 6 | Public-only sitemap/metadata/OG/canonical/related/options/counts, private-safe redirects; robots, six branded HTTP errors, CSRF/session/rate limits/headers/CSP, independent rate-limit budgets; free data-only ZIP backup and verified restore into new isolated targets |
| 7 | Unit/feature/integration and actual Chromium/axe/responsive/keyboard journeys; runtime/EXPLAIN/contrast/lab evidence; final audits/build/format checks; operations and continuation documentation |

Main implementation paths: `app/Http/{Controllers,Requests,Middleware}`, `app/{Models,Policies,Rules,Services,Support,Console/Commands}`, `database/{migrations,factories,seeders}`, `resources/{views,css,js}`, `routes/web.php`, configuration/bootstrap and `tests/{Feature,Browser}`. Public/admin/domain markup is shared through Blade components; CSS/JS are modular. The full endpoint inventory is generated by `php artisan route:list --except-vendor` (35 application routes, plus Laravel's health route).

No SPA/Livewire/Alpine, paid service, remote font dependency, artificial analytics, fake production reviews or generic CMS was added. Production JavaScript has no third-party UI framework. Playwright and axe are development-only QA dependencies; GD avoids an image-service dependency. Lockfiles are present and Composer validation passes.

## Domain and implementation decisions

Full rationale is in [implementation-decisions.md](implementation-decisions.md). All approved defaults are used: a place needs a published visit; private differs from draft; optional ratings/prices remain null; broad category and specific food type differ; cuisine and technique belong to items; recommendations are editorial visit labels; unknown allergen information is never absence/safety.

Publish requires place, timestamp, and narrative or a named dish, not every rating/price/photo. Sources and all optimized variants are private. `/original` returns a lossless metadata-stripped full-quality image; only JPEG orientation EXIF is kept, while the raw uploaded bytes remain unchanged privately. Processing is controlled/synchronous, so no queue worker is required. Failed variants retain originals and have an actionable retry/fallback.

Single/multi filters accept scalar deep links and normalized bracket arrays, with bounded lengths and whitelisted sorting. Price is cleared with feedback when switching contexts. Parent categories/cuisines include descendants. Place item/editorial conditions match the same eligible visit and combined dish conditions match the same dish. Ingredient-allergen reference mappings are not automatically promoted to exposure evidence.

Slug namespaces reserve old slugs, but only slugs that were public get public redirects, and redirects re-check current eligibility. No public data cache is used. Media is `private, no-store`; already downloaded copies cannot be retracted. Soft deletion retains source/history; there is no automatic original-file purge.

## Commands and actual results

| Command / gate | Actual result |
| --- | --- |
| Initial `git status`, file inventory, PHP/Composer/Node/extensions inspection | PASS; specifications-only baseline, no application/data to migrate |
| `composer install --no-interaction --prefer-dist` | PASS; locked dependencies installed |
| `npm install` / later QA dev dependency installation | PASS; lockfile updated; audit at installation showed zero vulnerabilities |
| Phase 1 `php artisan test --filter=FoundationTest` | 2 passed, 10 assertions |
| Phase 2 domain + foundation | 7 passed, 41 assertions |
| Phase 3 admin + previous gates | 12 passed, 102 assertions |
| Phase 4 initial MediaTest | 5 passed, 39 assertions; subsequently expanded to seven media scenarios |
| Phase 5 PublicTest | 8 passed, 92 assertions |
| Final `composer test` | **38 passed, 343 assertions**, isolated SQLite memory |
| Final `php tools/test-mysql.php` | **38 passed, 343 assertions**, NEW isolated `foodie_test_4a3862e0e7`, MariaDB 10.4.28 on 33079 |
| Final `npx playwright test` | **6 passed**, Chromium 156.0.8078.4, with axe scans and actual UI/network assertions |
| Final `npm run build` | PASS; Vite assets produced; CSS 12.49 kB (3.32 kB gzip), core JS 6.42 kB (2.43 kB gzip), page-specific chunks loaded conditionally |
| `php vendor/bin/pint --test --format=txt` | PASS after formatting |
| `composer validate --strict` | PASS |
| `composer audit --no-interaction` | No security vulnerability advisories found |
| `npm audit` and `npm audit --omit=dev` | Zero vulnerabilities |
| `php artisan route:list --except-vendor` | PASS, 35 application routes |
| `php artisan view:cache` | PASS, Blade templates compiled |
| `php artisan migrate --seed --no-interaction` on NEW local app DB | PASS, four migrations, 17 controlled allergen terms; zero admins/places/visits/media |
| `php artisan migrate:status` / `foodie:doctor` | All four migrations ran; MySQL connected; GD/WebP; locale id, UTC storage/Jakarta display; actual upload/post limits both 256M |
| Real `php artisan down`, `foodie:backup`, `up` | PASS; verified 31-table initial local archive; application returned live |
| `git diff --check` | No whitespace errors (Git's CRLF normalization notice only) |
| HTTP checks at 127.0.0.1:8027 | Home and login 200 with Foodie-specific content |

Development failures were fixed, not counted as successful verification: schema-dependent scaffold test setup, MySQL's 64-character generated index-name limit, global throttle counter collision, async-control initialization before listeners, native dialog Tab escape into browser chrome, 200% text fallback overflow, skip-link hiding with enlarged text, nested malformed-array/UTF-8 byte-length validation, mixed document encoding, and mirrored EXIF 5/7 direction. Final runs above pass.

## QA evidence

[Browser summary](qa/browser-summary.json), [lab measurements](qa/lab-measurements.json), [runtime/schema/EXPLAIN](qa/runtime-inspection.json). Selected actual screenshots: [Home 1440](qa/home-1440.png), [Explore 375](qa/explore-375.png), [Admin 375](qa/admin-375.png).

Screenshots use explicitly labeled **isolated QA records and synthetic image fixtures**, not invented production visits or genuine food photographs. They exercise rendering, crop, responsive media, controls and data semantics. Application data remains empty for the owner to populate.

| Browser / UAT scenario | Observed result |
| --- | --- |
| Home/Explore/Visit/Place at 320, 375, 768, 1024, 1440 CSS px | Headings visible, no page horizontal overflow; navigation images loaded before screenshots |
| Admin editor across the same five widths | No horizontal overflow; real mobile/desktop screenshots inspected |
| Enlarged text at 200% and reduced-motion preference | Reflow passed; no unsupported motion used |
| Filter dialog | Staged selections do not alter URL; Escape cancels and restores focus; async count, apply, preserved city/tab and Back/Forward pass |
| Quick Entry and full editor | Real login, inline place without lost title, minimum draft, two dishes, keyboard cuisine selection, save, publish confirmation, protected preview pass |
| Unsaved changes | Internal navigation cancellation retains typed title |
| Photo viewer | No original request before explicit open; original then loads, seven Tab operations remain inside dialog, Escape restores trigger focus |
| Automated accessibility scans | Zero axe violations on exercised Home, filter dialog, full editor, viewer and branded 404 states; this is not universal WCAG certification |
| Backend privacy / safety | Draft/private and unpublish negative cases on routes/cards/counts/options/media/SEO; owner tampering, cross-visit links, unsafe URL, rating/price bounds, MIME/pixels, retries and snapshots pass |

## Performance observations

The simple isolated Explore Visit fixture uses **13 queries for one card and 13 for eighteen**, demonstrating no card-count N+1 growth in that fixture. Existing editor selection labels reuse eager-loaded data instead of one HTTP lookup per selected taxonomy; actual search remains debounced. Read-only MariaDB EXPLAIN uses `visit_status_visited_at_id_index` for the default published/newest lookup. The inspected local application table was empty; this is not a high-volume benchmark.

Synthetic QA image: original 61,313 bytes at 1600×1100; thumbnail 2,802 bytes at 400×275; card 7,156 at 900×619; hero 16,810 at 1600×1100. These are measured fixture sizes, not a guarantee for real owner photographs.

Latest single unthrottled local lab sample: LCP **408 ms**, CLS **0**, navigation about **393 ms** at 1440×1000. These numbers are environment-specific, vary between runs, and do not establish production field performance or improvement against a previous application. **INP not measured.** Contrast pairs: primary/page 16.54:1, secondary/raised 11.09:1, muted/hover 6.78:1, primary button 8.92:1, border/hover 3.35:1, focus/page 11.89:1; tested normal-text and UI thresholds pass.

## Backup and recovery evidence

`BackupTest` executes on both isolated engines. It restores a real JPEG source, cleaned viewer and three variants, historical item price, cover and item/photo relationship, all table counts, foreign references and original hashes into new targets. Tampered archives and target reuse are rejected. Existing active DB/media are never selected as a restore target. The data-only archive avoids executing arbitrary SQL. Schema hash requires matching migration source. Source corruption or missing ready variants stops a backup.

Actual initial local backup: `storage/backups/foodie-20261104-053244-RXEt4HX2.zip`, verified with 31 tables; only the database payload was present because the owner journal has no media yet. Populated-media recovery evidence comes from the integration tests. Backup files and test restore targets are private/ignored. The normal app account lacks global CREATE privileges; MySQL restore drills use separate isolated administrative credentials.

[Operations](operations.md) provides maintenance/frozen-writer requirements, archive protection, retention, first install, exact safe restore commands, worker/media settings and database-server restart instructions. Database JSON snapshots are limited to 64 MB; the large-data native-dump alternative is documented as unverified, not silently substituted.

## Remaining environment checks and owner actions

No P0 implementation is left unfinished. Before a public deployment:

1. Choose the first owner privately with `php artisan foodie:admin`. No production/default password or fake production content was created.
2. Use maintained PHP/database patch versions, real HTTPS/web-server configuration, correct public web root, secure cookies and deployment secrets. Local PHP 8.2.4/MariaDB 10.4.28 are test runtimes; Laravel 12's upgrade timeline is in operations.
3. Repeat deployment checks on the chosen database/server. Oracle MySQL 8 specifically was **not** installed/tested; MariaDB and SQLite were tested.
4. Physical Android/iOS devices, Safari/Firefox and a real screen reader are **UNVERIFIED**. Chromium responsive/text/keyboard/axe checks are verified. Browser-toolbar zoom and physical-device rendering were not separately measured; the verified checks use CSS viewport widths and 200% text enlargement.
5. Review real owner photographs/crops and captions, real-volume query plans and production field LCP/CLS/INP; only labeled fixtures and the local lab measurements above were available.
6. Keep encrypted/restricted off-site backup and APP_KEY recovery copies, and repeat a restore drill on the actual operational environment before relying on it.

P1/P2 deliberately deferred: dedicated city/food/category/cuisine/style/recommendation/about pages, global media-maintenance/settings, public API/accounts/comments/reviews/payments, analytics and bulk import. P0 discovery shortcuts use Explore.

## Consistent continuation checkpoint

Working tree is uncommitted for review; user specification files remain intact. Foodie is on loopback **8027**; isolated browser QA server uses **8011** with separate database/media/session cookie/session files/cache. The dedicated MariaDB cluster uses **33079**; its data and generated credentials remain under ignored local runtime/private environment files.

If continuing after a restart, follow operations to start that local database server, then:

```powershell
php artisan foodie:doctor
php artisan migrate:status
composer test
php vendor/bin/pint --test
npm run build
php artisan serve --host=127.0.0.1 --port=8027
```

For browser regression: `php tools/prepare-browser.php`, run `php tools/browser-server.php` in a separate terminal, then `npx playwright test` and `node tools/collect-qa.mjs`. For MySQL-compatible regression use `php tools/test-mysql.php` on the isolated cluster, never an existing application database. No repeated migration, reset or data cleanup is needed to continue feature work.


## Playable vending machine completion · 6 November 2026

Implemented the supplied interactive vending package in the existing homepage cabinet. The front-left tube/cradle/socket, release-only reversible power, angular bidirectional pagination, independent physical reset, published-visit preview and photograph printing/retrieval are complete. Native cabinet geometry, public chrome, existing journal pages, filters and privacy rules remain. No dependencies, migrations or owner data writes were introduced.

Verification: **66 PHP tests / 1,126 assertions**, **10 pure JavaScript tests**, **34 browser scenarios**, and **two final keyboard/compiled-duration rechecks** passed. Targeted Pint and the production build passed. Browser checks include touch drag/tap, keyboard, reduced motion, blocked storage, interrupted gestures, first-paint persistence, stale fragments, true empty states, media failures, print identity, actual navigation and accessibility scans. The minified CSS seconds conversion and a release/scroll race were corrected during QA.

[Full evidence and commands](qa/vending-machine/verification.md), [state diagrams, asset provenance, control mapping and file inventory](vending-machine-interactive.md). Evidence includes before/after desktop/compact screenshots, real boot/print stages, short video and local frame/paint measurements. Physical-device/Safari/screen-reader and field performance validation remain unverified. The earlier continuation checkpoint is historical; use docs/operations.md for the current port-3306 Foodie instance and separate XAMPP recovery context.


## Indexed vending refinement · 7 November 2026

Removed visible page arrows; implemented resistant 32° indexed selector motion with settled release positions and coherent wrap-direction cassette changes. Moved the tube to an integrated vertical dock; redrew dock and square reset assets. Refined dark tray power states, finite pickup cues, selected slots, LED button affordances, engraved plaques and preview spacing. Compact off-state retains structural frames rather than a blank grid.

Validation: 18 machine browser tests, 3 final visual/accessibility rechecks, 10 JavaScript helper tests and 27 PHP feature tests (355 assertions) passed. Production build and whitespace checks passed. [Current evidence](qa/vending-refinement/verification.md), [implementation notes](vending-machine-refinement.md). No data rules, dependencies, migrations or owner data changed; physical-device/assistive/manual browser checks remain outstanding.


## User-corrected side-hung lamp · 7 November 2026

Restored the original socket position and moved the upright resting tube onto an external LEFT-side mounting arm, separated from the socket. Reserved desktop/compact gutters prevent clipping/overflow. Added an immediate finite pickup-outline/reflection cue on the tube, including previously discovered off sessions; meaningful interaction dismisses it, and reduced motion retains a static affordance. Corrected runtime reduced-motion handling found by the new cue test. Build, whitespace check and four final browser checks passed; [evidence](qa/hanging-lamp/verification.md). This explicitly supersedes the previous inside-panel dock decision.


## Resting lamp beside reset · 7 November 2026

Moved the outside-left resting lamp down to RESET height while keeping the original socket unchanged. Replaced the pipe with an illustrated two-point gusset mount and bolted plate. Removed every visible pickup label and its timer; hints are now cap-edge reflection, glass sweep and hover/focus response only. Production build, 19 machine browser tests, two final visual/accessibility checks and whitespace checks passed. [Evidence](qa/reset-side-lamp/verification.md). No data/dependency changes.
