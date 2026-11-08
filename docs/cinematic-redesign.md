> Current art direction and interaction revision: [7 November refinement](vending-machine-refinement.md). It supersedes the older outboard dock and pagination-button descriptions below.

# Global cinematic public redesign

Current architecture, 6 November 2026. This supersedes the machine-on-Explore descriptions in earlier archive reports.

## Responsibilities and composition

| Surface | Opening composition | Content and interaction |
| --- | --- | --- |
| Home | Native-proportion Memory Archive cabinet | Six published visits per page, removable power tube, bidirectional rotary pages, physical reset and printable visit photograph. See vending-machine-interactive.md. Recent visits use a chronological lead and supporting entries; Personal Picks use actual recommendation relationships. |
| Explore | Photographic Curiosity Index | Category, city and cooking-style photographs plus food-type/recommendation index links. Complete Visit/Food/Place browsing, search, advanced filters, sorting, grid/list, Memory Reel and pagination. |
| Visit Detail | Wide actual visit cover and editorial title | Metadata rail, readable narrative, tasting notes, historical item prices, nullable criteria, gallery, personal conclusion and published related visits. |
| Place Detail | Editorial place identity and latest available visit photograph | Existing published-only aggregate and historical visit timeline. |
| HTTP errors | Approved Editorial Bold kitchen scene | Existing status-specific copy and safe recovery actions, with the shared public header/footer. |

Home no longer renders advanced filters, Curiosity tiles, autoplay carousels or their counters. Visit selection and pagination stay local to the cabinet; the Home URL stays `/`. The full-surface cabinet screen links to the selected actual visit; there is no duplicate link below the machine. Ordinary slot links remain usable without JavaScript.

## Shared foundation

- `components/layout/public.blade.php` remains the public document shell. One `x-site.header` and one `x-site.footer` serve all public pages, including standalone errors. Do not introduce page-specific navigation markup.
- `.container` uses `--content-max: 80rem` with the existing shared gutters. Full photographs and cabinet art stay within that alignment; narrative text has its own reading measure.
- `public-chrome.css` owns self-hosted Cormorant Garamond / DM Sans font faces and public header/footer geometry. Admin retains its practical layout and system fonts.
- `cinematic.css` owns the shared atmospheric foundation and its Home/Explore/Visit/Place crop and light-density variants. One source atmosphere, static composition, no moving viewport-sized layer. Error pages retain their approved kitchen scene.
- `home.css` owns the recent-entry and recommendation compositions. `dossier.css` owns the Visit Detail reading and photographic hierarchy. Existing Visit Cards remain the result/related components.
- The standalone error build includes tokens, public chrome and error-scene CSS; it does not depend on Vite JavaScript or database-backed content.

## Machine ownership and geometry

`public/home-archive.blade.php` composes the reusable `x-discovery.dispenser`, published-visit windows and preview. The approved source raster remains 1634×963; source LED centres remain (1012,267), (1012,405), (1012,546). `config/archive.php` compiles the fixed aperture geometry. New local vector hardware replaces the old complete left instrument strip, with separate removable tube, socket, front-left cradle, knurled upper dial and lower push-button.

The current interaction contract, pure reducer, fragment lifecycle, session persistence, photo dispensing, touch/keyboard alternatives, reduced motion and asset sources are documented in [Playable vending machine](vending-machine-interactive.md). The earlier lamp-pause, random-draw knobs and envelope-opening descriptions are superseded by the supplied vending implementation package. Existing Explore collection/surprise endpoints remain compatible through their own fragment view; they do not control the homepage cabinet.

The cabinet starts off in a fresh session. Valid release commits magnetic travel, then staged ignition. Upper dial paginates actual published visits in both directions; lower reset depresses without rotating. The three LED controls cycle selection, print the selected visit and open its story. A real photo feeds behind the roller and can be retrieved. All standard public navigation remains available without playing. Compact layouts keep the lamp/socket/cradle together and reserve space for paper above the preview.

## Explore state and domain integrity

`pages/explore.js` now owns browsing only. The Curiosity Index stays mounted while console/results/drawer fragments change. It cancels obsolete requests, retains committed results on errors, offers retry and updates URL/Back/Forward. Existing price-context resets, same-item filtering, allergen wording, sort allowlists and published eligibility remain enforced by ExploreState/ExploreQuery/PublicOptions. City/category/food/style discovery remains filtered Explore; unimplemented P1 landing pages were not invented.

All recommendations come from the explicit relationship, never inferred from ratings. Ratings retain their existing 1–5 half-step inputs, item-derived taste and simple mean of populated criteria. Dossier missing ratings are omitted or described as not recorded; they are never zero. Item price snapshots and visit totals remain separate. Existing ingredient/allergen evidence and uncertainty disclosures are retained.

The existing viewer retains focus trapping/restoration, Escape/arrows, loading/failure/retry and on-demand original access. Gallery navigation uses optimized derivatives with responsive sizes. Missing item photography uses text composition; failed navigation images have a stable fallback. No schema, owner data, price, rating or unrelated admin behavior was changed.

## Evidence and reproduction

`docs/qa/cinematic/` contains actual browser screenshots for the homepage hero/recent/picks, Explore hero/results, dossier hero/story/items/ratings/gallery/conclusion, Place Detail, errors, mobile layouts and intermediate machine/lamp states. `evidence.json` records viewport, overflow, image and browser-error checks. The photography is bundled, labeled **demo** content in a dedicated isolated database, not represented as owner experiences.

```powershell
php tools/prepare-cinematic-qa.php
php tools/cinematic-server.php       # isolated photographic preview, 127.0.0.1:8012
node tools/capture-cinematic.mjs     # shared public surfaces
node tools/capture-vending-machine.mjs # current physical states and evidence

php tools/prepare-browser.php
php tools/browser-server.php         # isolated functional fixtures, 127.0.0.1:8011
npx playwright test
php artisan test --compact
php vendor/bin/pint --test
npm run build
```

No migration refresh, owner seeding or system-schema writes are part of these commands. Exact final command results and remaining deployment checks are recorded in `docs/implementation-report.md`.

## Hero refinement — 6 November 2026

The hero now contains its title and cabinet without author/instruction blurbs, routine visible status copy or duplicate links underneath. The collection overview no longer carries a slogan or explanatory paragraph. Footer identity remains the place for the journal description. Accessible selection/reveal announcements remain off-screen; actionable failures remain visible.

The current tube acts as the removable power source. Boot flicker is bounded and then light stays steady, with no repeating lure. The first-discovery hint is brief and suppressed after success. Accessible status stays off-screen; actionable errors and honest empty states remain visible. LED power matches the machine state and circular source geometry is unchanged. See the focused vending document for current durations and evidence.

The shared card-meta Blade component supplies calendar/date and location-pin/location pairs to Home editorial entries, Explore Visit/Food/Place cards and related cards wherever those components appear. SVGs are decorative and inherit metadata size/color; date/location values stay readable and wrap on narrow screens. No icon font or dependency was introduced.
