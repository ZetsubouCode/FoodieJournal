# Foodie

A visit-first personal culinary journal. Laravel 12, MySQL, Blade, Vite, Indonesian UI, IDR, Asia/Jakarta input/display and UTC storage. Public readers need no account; only the owner can edit.

Foodie is not a restaurant, food-sales site, recipe service, community-review platform or objective ranking authority. Home has one playable vintage **Memory Archive** cabinet; Explore combines a photographic discovery board with complete Visit/Food/Place browsing; Visit and Place are related cinematic archival stories. The owner admin remains a practical data-entry surface. Ordinary navigation works without playing.

Read [AGENTS](AGENTS.md) and [product vision / document precedence](docs/product-vision.md). The five root specifications retain authority for unchanged domain rules. `design-system-specification.md` §1 owns visual composition and Indonesian voice; `config/error-pages.php` owns approved HTTP copy. Current experience contracts are linked below. Dated design/QA reports are historical evidence, not competing current instructions.

**Current status (9 November 2026):** P0-1 restores the shared copper hover underline, keyboard/active/reduced-motion states and standalone error styling; [verification](docs/qa/navigation-hover-progress/verification.md). The [frontend audit](docs/implementation-status.md) retains its `b56db2c` baseline and now tracks this fix. P0-2 now adds the wide public shell, warmer environment/editorial footer and responsive media sizing while preserving admin geometry; [before/after evidence](docs/qa/wide-shell/verification.md). P0-3 now distinguishes empty/filtered/partial states and improves essential text/control sizing; [isolated fixtures and evidence](docs/qa/readability-states/verification.md). P0-4 adds 40 reviewed deterministic pixel baselines, an intentional-shift rejection proof and real-time video; [verification/setup](docs/qa/visual-baselines/verification.md). P1 onward in the [roadmap](docs/roadmap/frontend-experience.md) remains pending.

## Run this workspace

The existing Foodie database and controlled allergen vocabulary are configured privately in `.env`. The isolated local MariaDB data directory now serves Foodie on loopback port 3306; it is separate from XAMPP's shared `C:\xampp\mysql\data`. No default owner/password is created. See [operations](docs/operations.md) before switching database instances or running migrations.

```powershell
php artisan foodie:admin
npm run build
php artisan serve --host=127.0.0.1 --port=8006
```

Open [Foodie](http://127.0.0.1:8006); owner login is `/admin/login`. For Vite development run `npm run dev` separately. Images process synchronously; no queue worker is required.

## Preview with isolated demo content

Use the existing isolated photographic QA environment, never owner data:

```powershell
php tools/prepare-cinematic-qa.php   # dedicated cinematic.sqlite + cinematic-media
php tools/cinematic-server.php      # loopback http://127.0.0.1:8012
```

The local demo has 12 fictional places, 20 published visits, one draft/private example each, dishes and bundled illustrative photos. It is labeled demo content, not real owner memories. Preparation verifies its SQLite target and uses a separate media root; it does not create content in the active Foodie database. Existing fixtures can be served without preparing again.

Functional Playwright tests use a different fixture: `php tools/prepare-browser.php`, then `php tools/browser-server.php` on 8011. Tests expecting labels such as “Ingin kembali QA” require 8011; photographic captures use 8012. Do not infer a product regression from mixing these datasets. See [test safety and environment](docs/operations.md#test-and-browser-environments). P0-3 `readability-states.spec.js` also needs the photographic server on 8012 and the dedicated empty/sparse servers on 8013/8014; prepare them with `php tools/readability-qa.php prepare empty` / `prepare sparse`. Full startup and verification commands are in [readability/state evidence](docs/qa/readability-states/verification.md).

`DemoJournalSeeder` remains available for explicitly chosen disposable local/testing installations; running it through ordinary Artisan against the owner-configured app inserts public demo records there. It is not a required owner setup or audit step. It uses the existing owner and is designed not to overwrite their credentials/edits. Normal `db:seed` seeds allergen vocabulary only. Do not refresh an owner database.

For first installation, database/server restart, secure deployment, media limits, backup/restore and test setup, follow [operations](docs/operations.md). Preserve existing `.env`, APP_KEY and application data.

## Verification and implementation records

```powershell
composer test
npm run build
php vendor/bin/pint --test
```

The visual gate runs separately: `npx playwright test --config playwright.visual.config.js`, after building assets and preparing/serving the dedicated fixed-date photo/empty fixtures on 8015/8016. The default functional config excludes this spec. See [setup, reviewed manifest and explicit update procedure](docs/qa/visual-baselines/verification.md); ordinary runs never create/update baselines.

Maintained documentation:

- [Product vision and authority](docs/product-vision.md): identity, audience, IA, domain integrity and read order.
- [Art direction](docs/art-direction.md): historical references, warm archival materials, wide responsive shell, shared atmosphere/nav/footer.
- [Motion and interaction](docs/motion-interaction.md): Home 5/Explore 3/Detail 3, current mechanics, hover-progress and interruption/reduced-motion behavior.
- [Page experience and states](docs/page-experience.md), [UX principles](docs/ux-principles.md): page responsibilities, empty/loading/skeleton/error behavior and concrete rationale.
- [Implementation status / gap matrix](docs/implementation-status.md): evidence-backed current-versus-target findings.
- [Frontend roadmap](docs/roadmap/frontend-experience.md): scoped priorities, file scope, risks, tests and acceptance gates.
- [ADRs and historical document index](docs/adr/README.md): approved decisions, reversals and document classification.
- [8 November audit evidence](docs/qa/frontend-experience/verification.md): fresh commands/results, screenshots, limitations and reproduction.
- [Operations](docs/operations.md): installation, owner bootstrap, data protection, media, backup and isolated testing.

Historical records (dated claims, not current all-green certification): [implementation report](docs/implementation-report.md), [P0 checklist](docs/implementation-checklist.md), [domain decisions](docs/implementation-decisions.md), [cinematic migration](docs/cinematic-redesign.md), [vending implementation](docs/vending-machine-interactive.md), [refinement](docs/vending-machine-refinement.md), [lamp correction](docs/hanging-lamp-correction.md), [HTTP error work](docs/error-page-redesign.md), and retained `docs/qa/` captures. These preserve provenance; current contracts supersede conflicting mechanics/layouts.

All table names are singular, including framework support tables. Raw originals and derivatives are private; media requests check current visit eligibility. Unpublishing denies subsequent requests. Ratings ignore nulls and round only for display; visit totals remain separate from historical item prices. Allergen information is observational and never a safety guarantee.
