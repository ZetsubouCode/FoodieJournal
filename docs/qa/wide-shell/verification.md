# P0-2 — Wide public shell and archival environment

Implemented 9 November 2026 (Asia/Jakarta). Baseline: local `main` at `bcbd9b7269cfd4d08e34082c1dda6a57f78fac94` **plus the completed, uncommitted P0-1 changes**. Those changes, their tests and their evidence were retained. No reset, checkout, commit or push was performed.

## Result and scope

Public header, main and footer inner now share `--public-content-max:112rem` and `--public-gutter`: 1rem through 480px, 1.5rem through tablet, and `clamp(2rem,3.333vw,4rem)` from 1024px. The width formula is applied once to each sibling shell element. Error main uses the same rule in the standalone generated CSS. Global `--content-max:80rem` remains unchanged for admin; an explicit `public-page--admin` body modifier isolates its existing container, fonts, atmosphere and compact footer.

| Viewport | Measured public content width | Side gutter |
| --- | --- | --- |
| 320 | 288px | 16px |
| 375 | 343px | 16px |
| 390 | 358px | 16px |
| 768 | 720px | 24px |
| 1024 | 955.73px | 34.12px |
| 1440 | 1344px | 48px |
| 1920 | 1792px | 64px |

At 1920px the old public shell measured 1280px with 320px side gutters. Header/main/footer inner align within 1px at all tested sizes; footer background remains full width. Long narrative remains capped at the existing 68ch measure, with added caps for assessment prose and place address. This does not force every card or paragraph to fill the wide shell.

The cabinet retains its source coordinate system, LED centres and circular lenses. Its wrapper caps the cabinet at its actual 1634px raster width, with the existing 48–80px outboard-lamp gutter inside the scene allocation. No source art, assets, socket coordinates, dial/printing logic or animation timings changed. A new 960px boundary check exposed a reset alignment issue: its minimum 44px touch size could displace the button centre by 4.2px. Anchoring the reset at 81.55% with a half-height translation fixes the drift while retaining the hit area, resting-lamp alignment and source position. Compact layout is unchanged.

The existing atmosphere image stays static and full bleed. Home keeps .12 opacity; Explore .32, Visit .5 and Place .42 make archival light more present, with deliberate reading-page crops and a 90rem fade. Admin keeps its original .7/right-top/75rem treatment. Public/error footers share a warm semantic surface, an expressive two-line statement, description/copyright and desktop vertical/mobile wrapping navigation. The approved error kitchen scene, status copy and recovery actions remain; no footer markup copy or separate page implementation was introduced. The larger public cabinet may extend below the initial desktop viewport; it remains scrollable rather than being distorted or upscaled to fill the shell.

## Responsive media

Visit hero hints now describe the wide shell, capped at 1792px; Place hero and the priority Curiosity photograph have bounded column-aware hints. Lazy images in the shared image component use `sizes="auto, …"` with the existing explicit fallback list, actual dimensions and CSS geometry retained. This uses the browser's rendered slot size without adding a resizing script. The [HTML Standard](https://html.spec.whatwg.org/multipage/images.html#sizes-attributes) permits auto sizes for lazy images and fallback entries for older user agents. Eager images do not use auto.

The derivative pipeline remains thumbnail/card/hero (maximum long edge 400/900/1600); originals still load only on explicit viewer open. Existing 1600px photo derivatives may be slightly upscaled in a 1792px hero; generating larger media or changing owner uploads is outside this layout task. Cabinet art is separately capped at its native dimensions. Capture metadata records actual slots, `sizes`, selected derivative URLs and natural widths; no production field-performance claim is made.

## Executed checks

Existing isolated photographic SQLite/media served on loopback 8012; existing functional SQLite/media on 8011. No fixture-preparation command, application migration, owner-data seed or owner credential/media change was performed. PHP tests use isolated SQLite memory with the repository safety guard. Environment: Windows, PHP 8.2.4, Chromium 156.0.8078.4 and installed Vite/Playwright.

| Command / check | Actual result |
| --- | --- |
| `npm run build` | **Passed**, [log](build.txt). CSS 97.49kB / 19.01kB gzip; archive JS unchanged at 16.88kB / 5.80kB gzip. Existing runtime-asset notices remain |
| `npx playwright test tests/Browser/public-shell.spec.js --reporter=list --output=tools/.runtime/p0-2-shell` | Initial **3 passed, 1 failed**: enlarged text/reading, admin and media passed; new boundary case detected the reset-centre drift. [Original log](shell-initial.txt) retained |
| `npx playwright test tests/Browser/public-shell.spec.js --grep 'public shell aligns' --reporter=list --output=tools/.runtime/p0-2-shell-boundary` | **1 passed**, 56.7s after the reset anchor correction; all 55 route/width checks passed. [Recheck](boundary-recheck.txt). All four distinct new cases now pass across these targeted runs |
| `npx playwright test tests/Browser/vending-machine.spec.js tests/Browser/homepage.spec.js tests/Browser/error-pages.spec.js tests/Browser/public-navigation.spec.js tests/Browser/discovery-machine.spec.js tests/Browser/visit-card.spec.js --reporter=list --output=tools/.runtime/p0-2-regression` | **36 passed**, 3.5m; [log](regression-tests.txt). Includes all 19 machine cases, P0-1 feedback, history/filter/query behavior, card media/focus and public/error axe checks |
| `npx playwright test tests/Browser/journal.spec.js --grep 'gallery loads\|admin mobile' --reporter=list --output=tools/.runtime/p0-2-admin-viewer` | **2 passed**, 10.4s; original only on explicit viewer open, dialog focus restoration and actual authenticated admin editor reflow/axe. [Log](admin-viewer-tests.txt). Remove Markdown's table escape backslash from the alternatives when copying |
| `php artisan test --compact --filter='CinematicPublicTest\|VendingMachineTest\|PublicTest\|SystemSecurityTest'` | **21 passed, 365 assertions**, 2.26s; [log](php-tests.txt). Published eligibility, null/historical values, shared chrome, errors and machine rendering. Use ordinary `|` alternatives in the shell |
| `node docs/qa/wide-shell/capture.mjs before` / `after` | **15 before + 35 final after route/width measurements**, no document overflow or uncaught page errors |
| `node docs/qa/wide-shell/capture-states.mjs` | Ten 200%-root-text captures without overflow, powered/ready cabinet at exactly 1634px source width, footer keyboard focus, real no-photo Visit, DOM-only photo-free Place stress, admin width 1280px |
| `git diff --check`, documentation links/evidence assertions, JS syntax and generated error CSS parity | **Passed** |

The shell test covers 320/375/390/768/959/960/1023/1024/1440/1920/2560px across Home/Explore/Visit/Place/real 404. It checks width/alignment, overflow, cabinet aspect ratio, source-size cap, circular LED positions, lamp clearance and reset centre. Separate checks cover 200% text at 375/1440 and short landscape 812×375, reading measure, admin width/compact close and media source selection without originals. Screenshot smoke checks are not accessibility certification.

## Evidence and reproduction

**95 JPEGs** (~5.8MB total) keep matched evidence compact. `before.json` and `after.json` store the route, HTTP status, dimensions, selected media URLs and geometry; `states.json` records supplemental state checks. Photos are explicitly fictional bundled demo content, not owner experiences.

- `before-{home,explore,visit,place,error}-{375,1440,1920}.jpg` plus matching footer crops preserve the pre-P0-2 baseline.
- `after-{home,explore,visit,place,error}-{320,375,390,768,1024,1440,1920}.jpg` plus footer crops at 375/1440/1920 show final shared alignment and atmosphere.
- [Wide Home](after-home-1920.jpg), [Explore](after-explore-1440.jpg), [Visit](after-visit-1920.jpg), [error scene](after-error-1440.jpg), [desktop footer](after-home-footer-1920.jpg), [mobile footer](after-place-footer-375.jpg).
- [Powered/printed cabinet](after-machine-ready-1920.jpg), [keyboard footer](after-footer-focus-1920.jpg), [admin](after-admin-login-1920.jpg), [no-photo Visit](after-visit-no-photo.jpg) and `after-*-text200-{375,1440}.jpg` cover the complementary states.
- `after-place-photo-removed.jpg` is a **DOM-only layout stress** on the real fixture route; it neither creates a database record nor claims the pending P3 text-only Place composition is implemented. The existing bounded history-card layout and empty header track are preserved.

Visual review inspected matched desktop Home/Explore/error, wide Visit, mobile Home/footer, desktop footer, native-size powered/printed machine, admin and the photo-free layout stress. All five page footer variants have matched crops; not every screenshot pixel was manually inspected. Comparisons use the same labeled data, fonts, reduced-motion setting, viewport and image-decoding waits; supplemental machine readiness uses normal motion. JPEG crops are evidence, not reviewed pixel-diff baselines (P0-4 remains pending).

From the repository root with the existing isolated servers on 8011/8012 and built assets:

```powershell
node docs/qa/wide-shell/capture.mjs after
node docs/qa/wide-shell/capture-states.mjs
php artisan test --compact --filter='CinematicPublicTest|VendingMachineTest|PublicTest|SystemSecurityTest'
npx playwright test tests/Browser/journal.spec.js --grep 'gallery loads|admin mobile'
```

Do not rerun `before` on the new implementation if preserving this baseline; it intentionally writes the named before evidence. The capture scripts use only the dedicated loopback fixtures; inspect the existing preparation/server helpers before reproducing elsewhere. No owner database switch is part of reproduction.

## Remaining limits

No full test suite, production field LCP/INP/CLS, physical iOS/Android, Safari/Firefox or actual screen-reader run. Native auto sizing was verified in Chromium; explicit fallback hints remain for engines without it. Root-font enlargement is not actual browser zoom. An actual all-photo-free or >18-visit Place fixture remains untested; source/feature-test and DOM-stress evidence are distinguished. Six HTTP statuses retain PHP coverage, while this visual pass captures real 404 only. P0-3 readability/empty-state work, P0-4 pixel baselines and later storytelling refinements remain pending.

Owner `.env`, MariaDB/system schemas, account, uploads, original artwork and old QA directories were untouched. No dependencies, domain queries or route behavior changed. The task's QA server processes were stopped after verification.
