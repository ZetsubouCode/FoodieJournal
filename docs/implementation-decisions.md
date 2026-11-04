# Foodie implementation decisions

## Phase 0 audit — 4 November 2026

Read in full: the five root specifications v1.1 and `codex_instruction/001-foodie-mvp-implementation.md`. Initial git HEAD `092758e`; only LICENSE and .gitignore tracked. The specifications/instructions are pre-existing untracked user work and are preserved. No README, composer/package manifests, application, routes, migrations, tests, environment, data or assets existed. Baseline tests/build therefore unavailable, not failed.

Environment: Windows PowerShell; PHP 8.2.4, Composer 2.5.5, Node 26.3.1, npm 11.16.0. PDO MySQL/SQLite, GD, exif, fileinfo, intl, zip available. XAMPP MySQL binaries exist; no server listening on 3306. No existing database was contacted or modified.

Laravel 12 chosen for PHP 8.2 compatibility (official support ends February 2027; upgrade PHP/Laravel before then). Standard skeleton copied only into absent paths; original root files preserved. Minimal Blade + vanilla CSS/ES modules + Vite. No SPA, Alpine, Livewire, charting, image service or paid dependencies. PHPUnit/Pint development tools; GD supplies derivatives.

## Approved defaults

U1: places require a published visit. U2/D1: broad category and specific food type separate. U3: recommendations on visits only, independent of rating. U4: private separate from draft. U5: nullable ratings/prices accepted on publish. U6: discovery links use Explore; dedicated landings P1. U7/D2: item cuisine multi-select, optional short hierarchy (maximum three levels); Western editorial, never inferred. U8/D3: allergen evidence informational; absent evidence unknown; exclude known contains/may_contain only. U9/D4: private originals AND derivatives served through current-visibility controller. U10/D5/D7: quick draft, local datetime default at form creation, optional draft time, quantity default 1 only in UI, auto editable slug. D6: soft-delete visits/places with explicit confirmation; media retained on deletion and denied publicly.

## Implementation choices

- Store instants in UTC; parse datetime-local explicitly in Asia/Jakarta and format locally. Application locale `id`; UI time zone a separate configuration. Do not rely on host timezone.
- All tables, including framework migration/session/cache/queue support tables, singular. File sessions/cache and synchronous image processing avoid a worker requirement for P0.
- Publish requires valid place, visit time, and at least one meaningful narrative field or named item; no forced photos, prices or ratings. Derivative failures render a meaningful fallback, never original in navigation.
- Admin authorization requires explicit `is_admin` plus visit ownership; no automatic first-user privilege. Bootstrap through prompted CLI only, no passwords in seeds.
- Database unique creation token scoped to owner makes repeat Quick Entry idempotent. Optimistic version checks and transactions prevent stale editor overwrites; slug namespaces reserve old redirects to prevent reuse/cycles. Foreign references checked server-side.
- Filter arrays use repeated bracket keys, accept single values on direct links, deduplicate/bound values. OR per dimension, AND across dimensions on the SAME item. Place item/editorial filters match the same published visit. Unknown allergen info remains explicit. Price reset on changing tabs is visible.
- No public data cache initially; status checks on every request. Media `private, no-store` prioritizes revocation on subsequent requests. Already downloaded files cannot be retracted.
- Raw original bytes preserved privately, including metadata. `/original` serves a full-quality lossless metadata-stripped copy, with only JPEG orientation EXIF restored. Optimized derivatives strip metadata through decode/re-encode. No significant dimensions/quality loss is imposed on the source.
- System serif display and system sans body avoid remote font requests and licensing/network dependencies. Approved charcoal/ivory/copper palette; motion restricted to short state changes.

## Phases and gates

0 audit → 1 foundation/tokens/shared shell → 2 schema/domain/auth → 3 admin journey → 4 protected media → 5 public/Explore → 6 SEO/backup/security → 7 integrated tests, actual browser QA and report. Run relevant tests/build at each gate; report actual results. MySQL checks on isolated server only. No destructive commands against any active database.

P1/P2 deliberately excluded: dedicated discovery/about pages, global media maintenance, broad settings, API, public accounts/comments/reviews, payments, analytics, bulk import.
