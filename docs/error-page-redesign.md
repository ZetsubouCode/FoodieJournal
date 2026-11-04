# HTTP error page redesign — 4 November 2026

## Inspection and decisions

- Existing `403/404/419/429/500/503` views used one partial, but that partial reused the full public shell and ordinary centered section. `500` linked both actions to Home; `503` labelled a Home redirect as a retry. The status number was small metadata.
- The supplied pot photograph was available outside the repository; the requested in-repository background and full mockup were absent. `docs/references/cinematic-kitchen-source.png` preserves the supplied original. ImageMagick produced the 1672×941, 55,124-byte WebP at `public/assets/images/error/cinematic-kitchen.webp` from the 1,743,235-byte source.
- Existing specifications already called for cinematic editorial photography, but the guidance could be read as whitespace and restraint alone. `design-system-specification.md` §1 now defines the canonical composition system. The product blueprint, sitemap, wireframe, implementation instructions, README, `.ui-craft/brief.md`, and `AGENTS.md` point to it. The database specification was inspected and left unchanged because it contains no visual rules.
- Laravel 12's maintenance middleware throws an HTTP 503 when `artisan down` is used without `--render`; the normal exception handler then selects `errors/503.blade.php` for HTML and keeps JSON for JSON requests. `--render` serves a prerendered snapshot before the exception handler, so Foodie's documented path is maintenance without `--render`.

## Implementation

`config/error-pages.php` owns the Indonesian labels and messages. Six thin status views invoke `resources/views/errors/layout.blade.php`. The layout is standalone: no database/auth query, session UI, CDN, or application JavaScript. GET/HEAD 500 and 503 offer a true URL retry; state-changing requests get safe navigation instead. Other actions go to distinct destinations. `bootstrap/app.php` keeps JSON response shape and status while replacing exception messages for these six codes with safe headings.

The CSS source is `resources/css/error.css`. `npm run build` prepends the shared `resources/css/tokens.css` and writes a stable `public/assets/css/error.css`, so rendering does not depend on a Vite manifest. A dark fallback remains readable without the photograph. The number and accent stroke are live HTML/SVG, not part of the image.

## Verification

- `npm run build`: passed; static CSS and image served over HTTP.
- `php artisan test --filter=SystemSecurityTest`: 6 passed, 134 assertions after the approved copy update. This includes six HTML/JSON statuses, a controlled 500 exception, safe retry behavior, and an isolated `artisan down`/`up` 503 flow with `Retry-After` and JSON.
- `npx playwright test tests/Browser/error-pages.spec.js` against a local Laravel server: passed. Screenshots captured at 375×812, 390×844, 768×1024, 1366×768, 1920×1080, 320×568, and 812×375. Inspected desktop, tablet, mobile, and landscape renders; no horizontal overflow. Keyboard skip link and brand focus passed; axe found no WCAG 2 A/AA, 2.1 AA, or 2.2 AA violations on the 404 render. Reduced motion and failed-photo fallback passed. The shared typography also fit simulated 403 and 500 numerals.
- `php vendor/bin/pint --test`: passed. `git diff --check`: passed (Git reported line-ending notices only).
- `composer test`: 44 passed, 1 failed. The failure is the unchanged demo seeder assertion expecting four Bandung count results; the application returned five. Re-running `DemoSeederTest` reproduced that failure. It is outside this error-page change.

## Limits

The full approved mockup was not supplied, so the render was compared with the supplied production photograph and written Editorial Bold composition. Visual checks used Chromium; physical devices, Safari/Firefox, and a screen reader were not tested. The browser flow exercised an actual 404; other statuses were checked by feature tests and the same shared layout, with 403/500 numeral shapes simulated in the browser.

## Brand copy refinement and port-3306 follow-up

The six approved Indonesian error titles and descriptions are now asserted explicitly in `SystemSecurityTest`. The `500` message no longer says "dapur kami", and `503` no longer describes Foodie as a kitchen. The existing header, footer, actions, status rendering, photograph, and CSS composition were retained. A search of public-facing views found no other claim that Foodie cooks, serves, sells, or delivers food. The canonical Brand Voice & Microcopy rules are in `design-system-specification.md` §1 and referenced by `.ui-craft/brief.md`, `AGENTS.md`, and the implementation instructions.

The reported HTTP 500 was a database configuration failure: `.env` pointed to the reserved `mysql` system schema on port 3306, then the shared XAMPP instance refused connections after `migrate:fresh` attempted to drop system tables. A stopped-state copy of its data directory was taken; that instance also failed during a subsequent read-only dump and remains a separate recovery issue. Foodie was reconnected to its pre-existing, isolated MariaDB data directory on **port 3306**, with a new account restricted to the existing Foodie database. The one owner and 22 visits were preserved. `migrate:status` showed all four migrations already ran; no fresh migration was performed.

Follow-up checks: `SystemSecurityTest` 6 passed (134 assertions); the error-page Playwright test 1 passed across seven viewports; Pint and `git diff --check` passed; `foodie:doctor` connected to MySQL; HTTP Home returned 200 and revised 404 returned 404. XAMPP's shared data directory is not repaired or serving on 3306. Its snapshot is under ignored `tools/.runtime/xampp-3306-snapshot-20261104/` and is not a verified logical backup.
