# Foodie

A visit-first personal culinary journal. Laravel 12, MySQL, Blade, Vite, Indonesian UI, IDR, Asia/Jakarta input/display and UTC storage. Public readers need no account; only the owner can edit.

The five approved v1.1 specifications remain in the repository root. Scope and acceptance gates come from `codex_instruction/001-foodie-mvp-implementation.md`. For visual work, read the canonical composition guidance in `design-system-specification.md` §1 and `.ui-craft/brief.md`; the Editorial Bold HTTP error scene is documented there.

## Run this workspace

The existing Foodie database and controlled allergen vocabulary are configured privately in `.env`. The isolated local MariaDB data directory now serves Foodie on loopback port 3306; it is separate from XAMPP's shared `C:\xampp\mysql\data`. No default owner/password is created. See [operations](docs/operations.md) before switching database instances or running migrations.

```powershell
php artisan foodie:admin
npm run build
php artisan serve --host=127.0.0.1 --port=8006
```

Open [Foodie](http://127.0.0.1:8006); owner login is `/admin/login`. For Vite development run `npm run dev` separately. Images process synchronously; no queue worker is required.

## Preview with demo content

After creating the owner, populate the local site with sample content:

```powershell
php artisan db:seed --class=DemoJournalSeeder
```

This adds 12 fictional places in six cities, 20 published visits, one draft, one private visit, 66 dishes, and 42 photos with their processed variants. Categories, cuisines, ingredients, tags, recommendations, and allergen examples populate the Explore filters. Repeat visits show place history; an entry without photos or a recorded total exercises the missing-data presentation. Every story identifies itself as demo content; stock photos are illustrative.

The seeder works offline using bundled photos and requires `APP_ENV=local` or `testing`. It uses the existing owner, preserves their credentials, and adds no accounts. It can be rerun without duplicating content or overwriting edits, renamed visits, visibility changes, or soft-deleted entries. Normal `php artisan db:seed` continues to seed only the allergen vocabulary. No database refresh is needed.

For a fresh machine, database/server restart, secure deployment, media limits, backup/restore and test setup, follow [operations](docs/operations.md). Do not replace an existing `.env` or refresh an application database.

## Verification and implementation records

```powershell
composer test
npm run build
php vendor/bin/pint --test
```

- [Implementation report](docs/implementation-report.md): actual commands/results, QA evidence and limitations.
- [P0 checklist](docs/implementation-checklist.md): requirement, implementation and evidence.
- [Decisions](docs/implementation-decisions.md): domain defaults and repository preservation.
- [Operations](docs/operations.md): installation, first owner, backup and isolated restore drills.
- [HTTP error redesign](docs/error-page-redesign.md): composition, asset source, behavior, and verification.

All table names are singular, including framework support tables. Raw originals and derivatives are private; media requests check current visit eligibility. Unpublishing denies subsequent requests. Ratings ignore nulls and round only for display; visit totals remain separate from historical item prices. Allergen information is observational and never a safety guarantee.
