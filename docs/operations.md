# Foodie operations

## Requirements and first install

PHP 8.2+ with PDO MySQL, GD (JPEG/PNG/WebP), fileinfo, mbstring, intl, exif and zip; Composer; Node 22.12+; MySQL 8+ or compatible MariaDB. Use a maintained PHP patch version for deployment. The workspace's PHP 8.2.4 and MariaDB 10.4.28 are development/test runtimes, not deployment recommendations. Laravel 12 was selected for the available PHP 8.2 runtime; plan the PHP/framework upgrade before Laravel 12 security support ends. [Official support policy](https://laravel.com/docs/12.x/releases).

From the repository root:

```powershell
composer install
npm ci
if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
    php artisan key:generate
}
# Set DB_HOST/PORT/DATABASE/USERNAME/PASSWORD in private .env for a NEW database.
php artisan migrate --seed
npm run build
php artisan foodie:doctor
php artisan foodie:admin
php artisan serve --host=127.0.0.1 --port=8006
```

Do not replace an existing `.env`, rotate an existing APP_KEY, or use destructive refresh commands on an application database. On this workspace installation `.env`, APP_KEY, a NEW local database, and the allergen seed are already configured. Run `php artisan foodie:admin` to choose the first owner's name/email/password securely. The command refuses when an admin already exists. Passwords require 12 characters, upper/lowercase and numbers. No registration, public accounts or default admin credentials exist.

As of 20 November 2026, this workspace's active Foodie database uses a dedicated loopback MariaDB instance at port **33079**, with data directory `tools/.runtime/mysql-data-live-20261120` and a database-scoped application account. The active private media directory is configured by `FOODIE_MEDIA_ROOT` in `.env`. Application credentials remain private in `.env`; the instance's root secret and pre/post-upgrade archives are in the restricted `%USERPROFILE%\FoodieJournal-private\` directory. Keep that directory, `.env`, the MariaDB data directory and the media directory together in recovery planning. Neither the older `tools/.runtime/mysql-data` directory nor XAMPP's shared `C:\xampp\mysql\data` is the active Foodie data directory.

On 4 November 2026, an incorrect `.env` value pointed `DB_DATABASE` to MariaDB's reserved `mysql` system schema on XAMPP's shared port 3306. `migrate:fresh` attempted to drop system tables, and that server later failed during a read-only dump. A stopped-state file snapshot of the affected XAMPP data directory is retained at ignored `tools/.runtime/xampp-3306-snapshot-20261104/`; it is not a verified logical backup or a repaired server. On 20 November, the old dedicated Foodie data directory crashed on startup and the shared XAMPP server stopped answering Foodie queries during a read-only check. The current Foodie instance was restored from a verified application archive to a fresh separate data directory; the shared XAMPP server and its unrelated databases remain a separate recovery issue. Keep `migrate:fresh` and `db:wipe` restricted to disposable fixtures.

To restart the current Foodie database on port 33079, if stopped (PowerShell, foreground process), first confirm nothing else is listening on that port:

```powershell
& 'C:\xampp\mysql\bin\mysqld.exe' --no-defaults '--basedir=C:\xampp\mysql' '--datadir=D:\Github repo\FoodieJournal\tools\.runtime\mysql-data-live-20261120' --port=33079 --bind-address=127.0.0.1 --console
```

On other machines use your installed database service and configure `.env` accordingly. `tools/setup-local.php` is an optional workspace helper for this specific disposable cluster; it refuses existing explicit database configuration and never overwrites data.

## Runtime

- Production web root **must be `public/`**, never the repository root. Do not link private media into `public/storage`.
- `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=lax`. Keep storage/bootstrap cache writable only to the app user. Serve static built assets with immutable caching; media remains visibility-checked and `private, no-store`.
- `APP_LOCALE=id`, storage/application time UTC, `FOODIE_TIMEZONE=Asia/Jakarta` for input/display. IDR uses integers. No implicit conversion of null into zero.
- PHP `upload_max_filesize >= 16M`, `post_max_size >= 20M`; web server upload/time limits must also permit 15 MB. `foodie:doctor` reports actual PHP limits. Images are bounded by 24 MP, 10,000 px per dimension, and a lower calculated memory budget if necessary; maximum 40 photos per visit.
- Default queue is **sync**. No worker is required. Processing state and retry are implemented; do not switch to a database queue expecting jobs that the application does not dispatch.
- Raw source, metadata-cleaned full-quality viewer and thumbnail/card/hero variants are private. JPEG scan data is not recompressed in the viewer original; only orientation EXIF is retained. Raw source remains unchanged for recovery. Derivatives are GD/WebP (PNG fallback), no EXIF GPS. Failed derivatives keep the original and render a fallback.
- Unpublishing/soft deletion denies all NEW guest media requests. It cannot retract copies previously downloaded. Deleting a photo detaches cover/item associations and retains private source/variants; deleting a visit retains its data/media. P0 performs no automatic purging.
- For a stuck/failed photo, open its visit editor and use **Proses ulang**. Missing/corrupt originals must be recovered from backup; retries never synthesize or delete them.
- No public data cache is used, so status/rating/count/SEO changes apply on the next request.

## Backup: application database plus all private media

`foodie:backup` writes a ZIP containing `database.json`, all original/viewer/variant media (including retained assets), and a manifest of schema hash, counts, UTC time and SHA-256 hashes. Database snapshots are data only, with no executable SQL. The archive contains private journal content and password hashes: store it outside the web root with restricted access. APP_KEY and deployment credentials are deliberately excluded; keep their recovery copy separately in a secure secret store.

For a consistent point in time: stop all external CLI writers/workers, let synchronous uploads finish, enter maintenance mode, run backup, then leave maintenance. Do not edit the database or filesystem during the snapshot. Pending/processing media, missing originals, corrupt source hashes and missing ready variants stop the backup. Repair/retry first; failed variants with intact sources can be backed up.

```powershell
php artisan down
php artisan foodie:backup
php artisan up
```

Always run `up` after a failure too; inspect the error before treating an archive as valid. Default archive path: `storage/backups/foodie-<UTC>-<random>.zip`. An explicit `--destination` must be a NEW path outside public/media. Existing archives are never overwritten. A failed/incomplete archive may remain for inspection and is not a verified backup. Copy only successfully verified archives to a separate disk/off-site location. Suggested owner-managed retention: daily seven, weekly four, monthly three; no automatic deletion is installed.

The simple snapshot has a 64 MB uncompressed database JSON limit. For larger archives, use the database vendor's native `mysqldump`/restore tools plus a frozen media copy, or extend the exporter with streaming batches. The large-dataset/native alternative is **not exercised** by this implementation's tests; do not silently bypass the limit.

## Restore safely

Use a **trusted** archive and the same migration source version (schema hash checked). Work on an isolated instance first. Restore requires a NEW database/file named `foodie_restore_*`, and a NEW media directory whose parent already exists outside public/active media. It refuses existing targets. The MySQL restore connection needs permission to create that isolated database; the normal application's restricted account intentionally lacks global CREATE privileges. Use a separate isolated restore environment and administrative connection, never broaden the application's production privileges.

```powershell
# In a separate restore checkout configured for an isolated MySQL server:
New-Item -ItemType Directory -Path D:\FoodieRestore -Force
php artisan foodie:restore D:\backups\foodie-archive.zip --database=foodie_restore_drill --media=D:\FoodieRestore\media_drill

# SQLite isolated drill (checkout configured for SQLite):
php artisan foodie:restore D:\backups\foodie-archive.zip --database=D:\FoodieRestore\foodie_restore_drill.sqlite --media=D:\FoodieRestore\media_drill_sqlite
```

The command verifies file hashes before creating targets, runs the current singular migrations, imports the snapshot only into the fresh target, and checks every table's count, foreign references, same-visit cover/photo links and restored original hashes. Failed isolated targets are retained for diagnosis; existing databases and active media are not replaced. Use a different new target for a repeated drill.

After a drill: compare known visit/item IDs, historical prices/ratings, sources and cover relationships; inspect an original and card image; run the application against the restored database/media in an isolated environment. Only after operator review, stop the application, preserve the previous DB/media, and explicitly change deployment configuration to the verified restored targets. The CLI never performs that final production switch automatically.

Automated evidence: `BackupTest` restores an actual JPEG source, three variants, item/photo link, cover, snapshot price and all table counts on isolated SQLite **and** MariaDB; tampering and existing-target reuse are rejected.

## Existing databases: food-only rating upgrade

The forward migration renames `visit_item.taste_rating` to nullable `food_rating` in place, adds nullable `visit.pros` and `visit.cons`, and drops the `service`, `atmosphere`, `cleanliness` and `value` pairs of `_rating` and `_review` columns on `visit`. It stops before schema changes if any legacy review is non-NULL and the process lacks explicit cleanup approval.

1. Stop application writes and workers during cutover. With the pre-change application and schema, create a private database and media archive using [the backup procedure](#backup-application-database-plus-all-private-media). Independently verify it by restoring to separate targets with the pre-change revision. The restore command checks a migration-file hash, so retain that revision alongside the archive. Verify representative historical ratings, notes, prices and media in the drill.
2. Audit the verified Foodie schema with a read-only connection. Count non-NULL notes in each legacy review column, including private, draft and soft-deleted rows. For example, run the following query in a read-only transaction on the verified application schema.

   ```sql
   SELECT COUNT(service_review) AS service_notes,
          COUNT(atmosphere_review) AS atmosphere_notes,
          COUNT(cleanliness_review) AS cleanliness_notes,
          COUNT(value_review) AS value_notes
   FROM visit;
   ```

3. Review legacy notes privately on the restored copy. For a working export, save the Visit ID, slug, status and four review fields directly to a restricted file outside the repository, logs and public storage. Keep each note archived, explicitly select lines for Kelebihan/Kekurangan on the same Visit, or approve discarding it. When mapping involves published Visits, verify the approved public wording before reopening traffic. Retired numeric scores survive only in the backup.
4. After the owner has preserved or mapped the notes or explicitly approved discarding them, rehearse the upgrade and any approved mapping on the restored copy. Keep writers stopped while deploying the new code and applying the migration. For a target with legacy notes, set `FOODIE_APPROVE_LEGACY_REVIEW_CLEANUP=1` in the **migration process** only. The following PowerShell command limits approval to that run.

   ```powershell
   $env:FOODIE_APPROVE_LEGACY_REVIEW_CLEANUP = '1'
   try { php artisan migrate --force } finally { Remove-Item Env:FOODIE_APPROVE_LEGACY_REVIEW_CLEANUP }
   ```

5. Apply any approved mapping through the editor or a reviewed update keyed by Visit ID while the journal is in maintenance. Compare item ratings, Visit statuses, prices, IDs, media and the new observations against the rehearsal before restoring access.

MariaDB can commit DDL independently of the migration record. If an upgrade fails after schema changes, keep the application in maintenance and inspect the schema against the rehearsal before retrying. A schema rollback recreates the retired columns empty and drops the new observation columns. Restoring historical values requires the verified pre-upgrade backup and matching revision. Back up post-upgrade observations separately before rolling back.

## Test and browser environments

```powershell
composer test                    # Isolated SQLite :memory:
php tools/test-mysql.php          # Requires a separate isolated test server on port 33195; never start it against the active 33079 data directory
php vendor/bin/pint --test
npm run build
php tools/prepare-browser.php     # Separate tools/.runtime/browser.sqlite + media
php tools/browser-server.php     # Separate terminal, http://127.0.0.1:8011
npx playwright test              # Chromium + axe + responsive/keyboard journeys
```

First browser setup: `npx playwright install chromium`. Browser credentials are generated randomly into ignored `tools/.runtime/browser-credentials.json`. QA data is explicitly labeled and never inserted into the application's local database. Browser fixtures are retained across runs, with unique names for newly created test visits/places. Do not enable this server on a public interface.

The base test case refuses database refresh outside SQLite memory or a `foodie_test_*` database on a nonstandard loopback port. The MySQL test runner creates a fresh database each invocation and retains it and isolated restore targets for inspection. No active database is dropped. `test-results/` contains local screenshots/traces; `docs/qa/` contains selected durable evidence and lab measurements. Screen reader/device Safari testing and production field Core Web Vitals remain deployment UAT activities.
