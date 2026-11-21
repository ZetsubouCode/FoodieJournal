# Visit Detail V4 verification

21 November 2026. Starting revision `8e5c9d4`, clean tree. Windows Chromium 156.0.8078.4, built assets, fixed-date SQLite fixtures and bundled illustrative photographs. The V4 fixture uses `/visit/demo-semangkuk-hangat-blok-m` with one long review, one photo-free dish, two gallery images, both observation panels and a saved recommendation. Owner data and media were outside the fixture. The private V1 URL was unavailable for pixel comparison, so shared chrome was reviewed against the written V1 contract.

## Reviewed captures

| View | Desktop 1440 × 1000 | Mobile 390 × 844, touch emulation |
| --- | --- | --- |
| Entire Visit, including footer | [Desktop page](visit-1440.jpg) | [Mobile page](visit-390.jpg) |
| Header, hero and integrated facts | [Desktop opening](hero-1440.jpg) | [Mobile opening](hero-390.jpg) |
| Pendapat and ink stamp | [Desktop closing](pendapat-1440.jpg) | [Mobile closing](pendapat-390.jpg) |
| Viewer opened from the second dish photo | | [Mobile viewer](viewer-390.jpg) |

Reviewed the hero crop, photo and paper balance, long review columns, reading order, half stars, observation ink, gallery and related card. The desktop caption starts at the top of the quote region and the stamp sits below it on the right. Mobile stacks caption, quote and stamp. The header, hero, each section, and footer inner edges align within one CSS pixel at both screenshot widths. The captures show the resting rail state. [Earlier P3-1 layout and evidence](../p3-visit/verification.md).

## Scoped checks

- `npm run build` passed. Vite continues to resolve existing `/assets/` URLs at runtime. The screenshots loaded photography, fonts and the paper texture.
- `php artisan test --filter 'CinematicPublicTest|DomainTest|MediaTest|AdminTest|PublicTest|IntegrityTest'` passed 40 tests and 501 assertions. `php artisan test --filter PlaceChronologyTest` passed 2 tests and 36 assertions. They cover actual display values, clipped-star markup, stored stamps and observations, publication and media protection, and preview authorization/noindex.
- `npx playwright test visit-story.spec.js` passed 6 cases. Checked original-on-demand, exact dish/photo identity, viewport and native fullscreen, zoom, waiting/fallback/failure/retry, obsolete image callbacks, keyboard focus, bounded navigation, reduced motion, 200% root text and no-JS derivative links. The viewer scan reported zero violations for the selected axe rules.
- `npx playwright test public-navigation.spec.js` passed 5 cases. The width-aware derivative, sparse-data and Place-to-Visit history smoke cases also passed. The V4 screenshot case was repeated after the last CSS change.
- Changed PHP files passed Pint. `git diff --check` passed. The scoped stylesheet scan reported zero findings on both starting and final source.

## Reproduce

Prepare and serve each of the dedicated fixtures in its own terminal. The photo fixture listens on 8018 and the sparse fixture on 8014.

```powershell
php tools/visual-qa.php prepare visit
php tools/visual-qa.php serve visit
```

```powershell
php tools/readability-qa.php prepare sparse
php tools/readability-qa.php serve sparse
```

Build and capture from a third terminal. Captures are written under `test-results`.

```powershell
npm run build
$env:FOODIE_VISIT_EVIDENCE = '1'
npx playwright test visit-story.spec.js
```

## Changed files and verification limits

- Views are `resources/views/public/visit.blade.php`, the `gallery`, `image`, `photo-link`, `rating` and `tasting-note` templates in `resources/views/components/domain/`, and `resources/views/components/ui/meta-icon.blade.php`.
- Styles and interactions are `resources/css/dossier.css`, `resources/css/public-chrome.css`, generated `public/assets/css/error.css`, `resources/js/app.js`, `resources/js/components/viewer.js` and `resources/js/pages/visit.js`.
- Verification changes are `tools/visual-qa.php`, `CinematicPublicTest.php`, `AdminTest.php`, `PublicTest.php` and `PlaceChronologyTest.php` in `tests/Feature/`, and `visit-story.spec.js`, `readability-states.spec.js` and `place-chronology.spec.js` in `tests/Browser/`.
- Maintained contracts are `design-system-specification.md`, `.ui-craft/brief.md`, `.ui-craft/tokens.md`, `docs/adr/README.md`, `docs/adr/004-visit-detail-v4.md`, `docs/art-direction.md`, `docs/page-experience.md`, `docs/motion-interaction.md`, `docs/implementation-status.md` and `docs/roadmap/frontend-experience.md`.

Remaining evidence includes the historical pixel gate refresh, physical-device and other-browser runs, assistive technology, native browser zoom, and field vitals. The 8015 photographic fixture already present locally was held by the legacy note migration guard. The new fixture has a separate database and media directory.
