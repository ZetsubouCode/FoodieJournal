# Visit Detail V5 verification

21 November 2026. Windows Chromium 156.0.8078.4 with built Vite assets, one CSS pixel per device pixel and the existing deterministic published Visit `/visit/demo-semangkuk-hangat-blok-m` on the isolated `visit-v1` fixture at port 8018. The photographed dishes, long review, unrated states, two gallery entries and related Visit use fixture content. Mobile uses Chromium touch emulation at 390 × 844. Before captures show the local V4 working tree at the start of V5, including its then-uncommitted Visit redesign.

## Source of the mismatch

The header template put the shell width on the header itself and exposed only Beranda and Jelajah. The computed header was 80px tall and transparent. Domain defaults and multiple shared chrome blocks both styled the footer. The final public override set a column direction, top alignment and a desktop 83.2px statement on a 410px closing panel. The shared Blade components now receive the Visit context from its view. Public chrome owns the surface, width and layout for Vite routes and the generated standalone error bundle.

## Matched captures

| View | Before | After |
| --- | --- | --- |
| Header · 1843 × 202, viewport 1843 × 1000 | [Before](before-opening-1843.png) | [After](after-opening-1843.png) |
| Footer · 1867 × 623, viewport 1867 × 1000 | [Before](before-footer-1867.png) | [After](after-footer-1867.png) |
| Opening · 1440 × 1000 | [Before](before-opening-1440.png) | [After](after-opening-1440.png) |
| Footer · 1440 × 623 | [Before](before-footer-1440.png) | [After](after-footer-1440.png) |
| Opening · 768 × 1000 | [Before](before-opening-768.png) | [After](after-opening-768.png) |
| Footer · 768 × 623 | [Before](before-footer-768.png) | [After](after-footer-768.png) |
| Opening · 390 × 844 | [Before](before-opening-390.png) | [After](after-opening-390.png) |
| Footer · 390 × 560 | [Before](before-footer-390.png) | [After](after-footer-390.png) |
| Full Visit · 1843px wide | [Before](before-full-1843.jpg) | [After](after-full-1843.jpg) |
| Full Visit · 390px wide | [Before](before-full-390.jpg) | [After](after-full-390.jpg) |

- **Header against `V1_HEADER_APPROVED.png`.** The live 86px band has a warm translucent surface and a fine bottom keyline. Its five controls follow the reference order, with a divider at approximately x1418 and the group starting around x1247. The serif mark retains the copper dot and the public gutter puts it at x61, about 15px left of the reference. The copper line below Catatan ini is the existing persistent active treatment. The in-flow header adds 6px above the desktop hero.
- **Footer against `V1_FOOTER_APPROVED.png`.** The panel now measures 470px, with the top rule at y153 in the matched crop. The 102px roman and italic statement starts around y256 with an inner x62 position. The description wraps into two lines at y463. Three horizontal links share a baseline around y500 and the tracked copyright sits around y582. The shared gutter and Chromium's hidden scrollbar account for some horizontal difference from the prototype. The closing gradient and self-hosted Garamond have slightly different texture and letterforms.
- **Responsive behavior.** At 390px the Visit group moves below the primary row, the closing story precedes footer links and touch targets measure at least 44 × 44 CSS px. At 768px the header fits a row and the footer links move beneath the story. At 200% root text the same elements wrap and stay reachable. [Enlarged header](after-header-390-text200.png). [Enlarged footer](after-footer-390-text200.png).
- **Shared surfaces.** The public routes and both error scenes have the same warm header and closing panel. The authenticated admin shell uses system fonts and its compact footer. [Home, Explore, Place, 404, 500 and admin](after-shared-routes.jpg).

## Interaction comparison

- The copper underline fills from the left in 220ms and retracts in 140ms. Keyboard focus paints a separate outline, while the active link keeps its line. [Before hover](before-hover-1843.png). [After hover](after-hover-1843.png). [Start, midpoint, finish and exit](after-underline-frames.png).
- The Visit switch defaults to the system setting and updates the dot, text, checked state and visible motion. A deliberate selection lasts for that document. Runtime system preference changes reapply the system setting. [Reduced motion](after-reduced-1440.png).
- The chapter rail appears during scroll, fades after about 1.8 seconds and reappears on keyboard focus. The current chapter follows the viewport. The existing narrative is readable immediately on anchor navigation and without JavaScript. [Rail frames](after-rail-frames.png).
- The dish opens its own photo. Zoom, native fullscreen and its fallback, dismissal and focus restore were exercised. Native ingredient disclosure opens and closes from the keyboard with the complete allergen section. [Viewer before](before-viewer-1843.png). [Viewer after](after-viewer-1843.png).

## Executed checks

- `npm run build` passed. Vite reported the existing runtime asset URL notices.
- `php artisan test --filter 'CinematicPublicTest|DomainTest|MediaTest|AdminTest|PublicTest|IntegrityTest|PlaceChronologyTest|SystemSecurityTest'` passed 48 tests and 678 assertions. PHPUnit uses SQLite in memory.
- `npx playwright test public-navigation.spec.js public-shell.spec.js visit-story.spec.js error-pages.spec.js homepage.spec.js --output tools/.runtime/visit-v5-final-browser` passed 19 cases.
- `npx playwright test p0-hardening.spec.js vending-machine.spec.js visit-story.spec.js -g 'six real HTTP|selection prints exactly|reduced-motion runtime|V1 Visit chrome' --output tools/.runtime/visit-v5-focused` passed all four selected cases. HTTP fixtures cover actual 403, 404, 419, 429, 500 and 503 responses. The test screenshots were directed to an ignored output directory with `FOODIE_QA_OUTPUT`.
- The initial combined run of `public-navigation.spec.js public-shell.spec.js visit-story.spec.js error-pages.spec.js place-chronology.spec.js homepage.spec.js explore-response.spec.js` passed 20 cases and failed 13 against the legacy 8015 photo fixture. Its Explore query returns HTTP 500 because `ri.food_rating` is absent. Temporary copies of `explore-response.spec.js` and `place-chronology.spec.js` used 8018 and `visit-v1` in `npx playwright test --config tools/.runtime/visit-v5-regression/playwright.config.js`. Thirteen of fifteen passed on that run. The two time-sensitive Explore cases, rapid board intents and decoded fast photos, passed on a focused rerun. The legacy SQLite fixture was preserved.
- `vendor/bin/pint --test tests/Feature/CinematicPublicTest.php` and `git diff --check` passed.
- `node tools/.runtime/visit-v5-mobile.mjs` passed the 390px touch switch, 44 × 44px control measurements, scoped axe scan, keyboard disclosure and visible tasting anchor checks. The enlarged footer image joins two viewport captures at their original scroll coordinates.

## Files changed for V5

- Blade context and composition in [header.blade.php](../../../resources/views/components/site/header.blade.php), [footer.blade.php](../../../resources/views/components/site/footer.blade.php), [layout/public.blade.php](../../../resources/views/components/layout/public.blade.php) and the opening [public/visit.blade.php](../../../resources/views/public/visit.blade.php) layout tag.
- CSS ownership and motion in [public-chrome.css](../../../resources/css/public-chrome.css), [domain.css](../../../resources/css/domain.css), [base.css](../../../resources/css/base.css), [dossier.css](../../../resources/css/dossier.css) and generated [error.css](../../../public/assets/css/error.css).
- Switch and rail behavior in [app.js](../../../resources/js/app.js) and [pages/visit.js](../../../resources/js/pages/visit.js).
- Coverage in [public-navigation.spec.js](../../../tests/Browser/public-navigation.spec.js), [public-shell.spec.js](../../../tests/Browser/public-shell.spec.js), [visit-story.spec.js](../../../tests/Browser/visit-story.spec.js) and [CinematicPublicTest.php](../../../tests/Feature/CinematicPublicTest.php).
- Maintained documentation in `.ui-craft/brief.md`, `.ui-craft/tokens.md`, `design-system-specification.md`, `docs/art-direction.md`, `docs/motion-interaction.md`, `docs/page-experience.md`, `docs/implementation-status.md`, `docs/roadmap/frontend-experience.md`, `docs/adr/README.md` and ADR 004. ADR 005 and this verification folder record V5.

Physical phones, other browser engines, screen readers and native browser zoom remain outside the evidence. The V1 photography, scroll position and browser chrome differ from the fixture captures. The older Playwright pixel baselines predate this V5 comparison and remain pending refresh.
