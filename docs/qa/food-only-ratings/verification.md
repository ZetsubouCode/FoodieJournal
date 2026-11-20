# Food-only ratings verification

19 November 2026. Started from `68961103be9240bd99058d5bfdda988a5e4c0934` on `main`, one commit past the audited P4 baseline. Preserved the owner's untracked task 005 file and the committed P4 work.

## Executed checks

| Check | Result |
| --- | --- |
| Focused PHP Feature files on SQLite in memory | 63 passed, 1,111 assertions. Covered upgraded and fresh schemas, rated item weighting and eligibility, original price and foreign-key retention, rounding and filtering, stale form fields, ownership/version/idempotency, public rendering and publication, Place chronology and media, query counts, and demo content. |
| Four-case MariaDB parity smoke on a newly initialized loopback server, port 33195 | 4 passed, 79 assertions. The legacy-note check stopped before DDL. Explicit approval on the fixture allowed cleanup while keeping existing item ratings, prices and relations. |
| Vite/CSS build | Passed. Vite left existing absolute public asset URLs for runtime resolution. |
| Scoped Chromium 156.0.8078.4 tests | 8 passed across the changed admin editor, Explore filters, Visit story and viewer, and Place history and empty ratings. |
| Direct Chromium render check | Inspected Admin editor observations, Visit detail observations, Place detail facts and a filtered Explore Place card at 1,440px and 375px. Verified doubled root text without horizontal overflow, reduced motion, keyboard focus and return, and a held Explore response before commit. Captured eight crops in ignored `test-results/review-005/`. |
| Static checks | Changed PHP files passed syntax checks. `git diff --check` passed. Legacy names in executable source remain in migration history and intentional regression assertions. |

The 375 × 812 editor check verifies that tabbing into the second observation field keeps the full focused textarea above the sticky save bar at both text sizes. Visit observations appear side by side on desktop and in Kelebihan-then-Kekurangan order on mobile. The photo fixture's Bara Senja Place card and detail both show 4,3 from four rated items across two Visits.

Commands from the project root. Set `FOODIE_QA_OUTPUT` when replaying the P0 case to keep its new captures in the ignored QA directory.

```powershell
php artisan test tests/Feature/DomainTest.php tests/Feature/FoodRatingMigrationTest.php tests/Feature/AdminTest.php tests/Feature/CinematicPublicTest.php tests/Feature/PublicTest.php tests/Feature/VisitCardTest.php tests/Feature/PlaceChronologyTest.php tests/Feature/PlaceHeroTest.php tests/Feature/DiscoveryMachineTest.php tests/Feature/IntegrityTest.php tests/Feature/DemoSeederTest.php
# MariaDB command used only after selecting the disposable foodie_test_review_005 database on port 33195.
php artisan test tests/Feature/FoodRatingMigrationTest.php tests/Feature/DomainTest.php tests/Feature/PublicTest.php --filter='test_upgrade_guards|test_visit_food_score|test_place_score|test_prices_ratings_sort'
npm run build
$env:FOODIE_QA_OUTPUT = 'test-results/review-005-p0-captures'
npx playwright test tests/Browser/journal.spec.js tests/Browser/visit-story.spec.js tests/Browser/place-chronology.spec.js tests/Browser/p0-hardening.spec.js --grep 'filters stage|login, keyboard combobox|admin mobile|story, long prose|original wait|current-page month groups|long photo-free facts|photo-free Place' --output=test-results/review-005-final-playwright
node tools/.runtime/review-005-browser.mjs
git diff --check
```

Browser fixtures ran on 8011, 8014, 8015 and 8017. The 8015 photographic fixture used a new SQLite and media target under the ignored runtime directory, so the earlier QA database and its score-era screenshots were kept. The test suite scope excluded the full visual and vending suites. Native zoom, physical devices, other browser engines and assistive technology remain unverified.

## Owner data as of 19 November

The local MariaDB audit used a read-only transaction and counted legacy review fields without reading note bodies. Legacy notes exist. Owner cleanup awaits a verified database and media backup and the owner's preservation, mapping or discard decision. The migration and its rollback were exercised on isolated fixtures only. [Upgrade and recovery procedure](../../operations.md#existing-databases-food-only-rating-upgrade).

## Local cutover on 20 November

The owner authorized choosing the safest target and handling the old notes. The configured Foodie schema on XAMPP port 3306 contained 22 demo Visits, 66 Items, 42 media records and 126 variants; all 22 Visits had each of the four retired review fields populated. The older dedicated `tools/.runtime/mysql-data` instance crashed on startup. The shared XAMPP server then stopped answering Foodie queries during the final read-only comparison. No migration was run on either unstable instance, and no unrelated XAMPP database was changed.

After fixing `JournalBackup` to list tables from the selected MariaDB schema, `foodie:backup` created a verified 31-table, 421-file pre-change archive at `%USERPROFILE%\FoodieJournal-private\pre-food-rating-20261120-234804.zip`. A separate private `legacy-reviews-20261120-234804.json` preserves all eight retired Visit rating/review fields. The archive restored successfully to SQLite and to a fresh dedicated MariaDB data directory on port 33079. Before migration, every restored table matched the archive; the restore also checked table counts, references and media hashes.

The forward migration ran on the fresh MariaDB target in batch 2. A rehearsal on restored SQLite and the live cutover both applied the curated `pros`/`cons` from `demo-journal.php` to the 22 unchanged demo Visits: 13 have pros and 7 have cons. The original legacy notes and numeric ratings remain in the private pre-change archive and export; food-related value notes were not assigned to unrelated Visit fields. The post-migration comparison matched all 31 archived tables after accounting for the renamed rating column and retired/new Visit fields. The active `.env` now uses a database-scoped account on port 33079 and the restored private media directory. Its previous contents are saved privately as `pre-switch-env-20261120`.

A new 31-table, 421-file archive at `%USERPROFILE%\FoodieJournal-private\post-food-rating-20261120-235820.zip` restored successfully to a separate SQLite database and media directory. `migrate:status` reports all five migrations ran, `foodie:doctor` reports MySQL connected and private media, and Home, Explore, a published Visit, its Place, admin login, and a published WebP image returned HTTP 200 from the local server on port 8006. The 63 scoped PHP tests (1,111 assertions), two backup tests (12 assertions), Vite build and `git diff --check` passed. The shared XAMPP server's unrelated databases were not recovered or validated; Foodie now runs independently on its dedicated instance.
