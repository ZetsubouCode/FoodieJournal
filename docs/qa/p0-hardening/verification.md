# Foodie P0-H verification

9 November 2026, Asia/Jakarta. Corrective hardening before P1, implementing the owner's seven decisions in [ADR 002](../../adr/002-p0-hardening.md). This is scoped implementation/verification, not a P1/P2/P3 redesign or WCAG certification.

## Actual baseline and safety

Local branch **main**, HEAD **5ca68464f17af5e8ea330a5bcfe3bd98760ba10d** (`implement P0 steps`), following `bcbd9b7`, `b56db2c`, `801cc57`, `4f6365b`. `git status --short` was empty at start. No remote-state assumption, reset, checkout, clean, auto-commit, push or deployment. The pasted owner instructions were read from the attachment; repository guidance, design memory, maintained contracts, ADRs, root product/domain/design sections, actual consumers and previous P0 verification records informed the scope.

Owner `.env`, APP_KEY, accounts, originals, MariaDB 3306/system schemas and XAMPP recovery data were not read or changed. PHP tests use guarded SQLite memory/fake media. Browser setup uses separate existing QA databases/media and a new fixed QA target; no refresh/wipe or owner seed. Browser CRUD creates only labeled QA records in the functional fixture. The existing performance test writes its legacy lab-result path; its fresh result was copied [here](lab-measurements.json), and the previous tracked file restored byte-for-byte. Older QA reports/PNGs remain unchanged.

| Port | Identity used / purpose |
| --- | --- |
| 8011 | `browser.sqlite` / browser-media; existing random QA credentials; functional/admin/editor/filter/card/machine regression |
| 8012 | cinematic.sqlite / cinematic-media; bundled fictional photographic demo; existing readability caption regression |
| 8013 / 8014 | readability-empty / readability-sparse; private-only archive and genuine unrated/photo-free sparse cases |
| 8015 / 8016 | fixed-date visual-photo / visual-empty; `X-Foodie-Visual-Fixture: photo-v1/empty-v1`, 20/0 published visits, P0-4 gate |
| 8017 | new p0-hardening.sqlite / p0-hardening-media, `X-Foodie-Hardening-Fixture: v1`; 21 published + 2 hidden visits, two places; only cover on the older page-2 history visit, long photo-free title/address with no city/Maps, isolated real HTTP-error routes |

Fixtures are fixed by their inspected helpers before application bootstrap; no owner database is a fallback. Only QA servers started for this task are stopped after verification; fixtures remain for reproduction.

## Traceable findings and scope

V means source plus named runtime/test evidence; C means source-inferred; U means unmeasured. Design judgment is labeled separately.

| ID / choice | Before | Implemented / current evidence | Status |
| --- | --- | --- | --- |
| H01 / 1B | At 2560, 112rem left 384px per side even for the photographic board/footer | Public cap 152rem uses 2432px with 64px gutters, retains earlier widths through 1920. Explore adds three Visit / four Food/Place columns at ≥2000px; lists stay one. Photo height capped at 32rem to keep discovery captions visible. Place photo capped at 50rem, cabinet stays native, prose stays bounded | **Implemented / V**, [geometry](after.json), [sections](sections.json), [wide comparison](public-2560.jpg) |
| H02 / 2B+C | Place selected `$visits->first(...)` from only the current paginated collection | Controller explicitly supplies `coverMedia`, latest visited_at then id descending among published-visible same-visit covers with a ready existing hero. Eager batches of 50 stop at first usable cover. Missing responsive files are omitted from the selected hero srcset. No per-history-visit queries, new index or schema | **Implemented / V**, eight PHP cases, [page 1](stable-hero-page-1.jpg) / [page 2](stable-hero-page-2.jpg) |
| H03 / 2B+C | Photo-free Place retained a dead second photo track | One outer text track, expressive title/location and genuine responsive facts group. Missing city/address/Maps omitted; nullable aggregate and rated count retained. Long title/address/no-city case tested separately. History stays chronological and unchanged in purpose | **Implemented / V**, [comparison](place-admin.jpg), [24 width/text combinations](text-place-geometry.json), [390px long facts](text-place-390-100.jpg) |
| H04 / 3B | Need to validate public mood against actual historical reference | Opened the intermediate cabinet-on-Explore screenshot and original full-page reference; compared actual current/repaired heroes and footers at 390/1440/1920/2560. Current charcoal/sepia/copper/local illumination already meets the scoped mood intent, so no palette/brightness/crop rewrite | **Reviewed / design judgment**, [old/current/repaired](mood-lineage.jpg), shared footer comparisons below |
| H05 / 4A | Computed admin body/display used Archive Sans/Garamond and cinematic background | Exclude admin from public font overrides; system-ui body/headings, plain semantic page surface, compact footer. Retain 80rem shell and shared controls. Login, authenticated lists/forms, validation, focus and save exercised | **Implemented / V**, [before/after](place-admin.jpg), [computed fonts](before.json) / [after](after.json) |
| H06 / 5B | Saved success populated both persistent status alert and live toast | Remove duplicate toast bootstrap/dataset; retain the persistent status once. Shared toast API remains exported, with no new async consumer. Complete [primitive/state inventory](primitive-inventory.md) maps actual sources/consumers and later enhancements | **Implemented / V**, feature assertion and actual invalid-then-successful admin save |
| H07 / 6A | Unused generic skeleton was not a real region-specific loading experience | No new skeleton or artificial wait. Controlled outstanding Explore request retains committed results/URL, announces busy, then shows error/retry and commits success. Existing viewer/filter/combobox/cabinet busy/recovery tests remain | **Implemented verification / V**, [real wait](explore-real-wait-390.jpg); **new skeleton experiences pending P2** |
| H08 / 7B | Out-of-range Place history gave an empty list without contextual recovery | Shared empty-state offers the first history page while retaining place identity/eligible cover. No published visits still 404 | **Implemented / V**, PHP/page-99 browser cases |
| H09 / docs | Personal machine skill path and contradictory current Place/admin/width summaries | Portable home-relative skill instruction with non-blocking repository fallback, ADR 002, synchronized art/page/motion/UX/status/roadmap/brief/tokens/README/root design sections | **Implemented / source reviewed** |

No mechanics, auth/policy, pricing/rating/allergen semantics, media processor, derivative dimensions, public registration, dependencies or schema changed. The added 8017 error routes exist only inside the isolated QA router, never the application route files. The proof tool accepts an output directory so new evidence does not overwrite P0-4 history.

## Width diagnosis and measurements

Default 16px root. All five public routes (Home/Explore/Visit/Place/real 404) were measured at all 12 widths. [After JSON](after.json) contains **68 records** including sparse Place and admin; [before JSON](before.json) has 28 matched baseline records. Zero document horizontal overflow; header/main/footer-inner x alignment maximum error **0 CSS px**. Footer background spans the viewport. The complete shell regression also verifies source ratio, LED/lamp/reset anchors and reading measure.

| Viewport | Shell | Side gutter |
| --- | --- | --- |
| 320 / 375 / 390 | 288 / 343 / 358 | 16 |
| 768 | 720 | 24 |
| 959 / 960 / 1023 | 911 / 912 / 975 | 24 |
| 1024 | 955.73 | 34.12 |
| 1440 | 1344 | 48 |
| 1920 | 1792 | 64 |
| 2560 | 2432 | 64 |
| 3440 | 2432 | 504 |

The 1023→1024 gutter increase is the existing desktop breakpoint, not a new discontinuity. At 3440 the deliberate 152rem cap bounds photographic enlargement/card density; it is not an accidental 112rem holdover.

| Section | Diagnosis / disposition |
| --- | --- |
| Home cabinet | Native 1634×963 cap and outboard tube clearance intentionally retain lateral scene space. No upscaling, control relocation or mechanical animation change. No width change needed through 1920 |
| Home recent/picks | Existing asymmetric editorial groups expand into the wider visual shell; actual photographic subjects and bounded excerpts stay primary. [Recent 2560](section-home-recent-2560.jpg), [picks](section-home-picks-2560.jpg) |
| Explore | Unintended centered visual constraint at 2560 corrected. Photo-height cap avoids enlarging all three tiles vertically. Three Visit cards measure **800px each** at 2560; Food/Place use four columns. [Actual result group](section-explore-results-2560.jpg) |
| Visit | Wide hero/gallery/facts expand independently of 68ch narrative. Actual protected derivatives remain maximum existing processed size; no original fetched by the shell/card checks. [Story 2560](section-visit-story-2560.jpg), [gallery](section-visit-gallery-2560.jpg) |
| Place | Genuine photo opening retains a maximum **800px photo** and real identity/facts. Photo-free layout has no unused image track. History cards retain existing **928px / 58rem** list measure; lateral space supports chronology reading rather than stretching each record |
| Error/footer | Shared wider x alignment, full-bleed kitchen/closing surface and bounded error text; original scene/status copy retained. No change required to 390/1440/1920 pixels |
| Admin | Deliberate 1280px / 80rem practical cap; no ultrawide expansion. System typography/plain background are intentional choice 4A |

At 200% root text, all named widths are checked on the long photo-free Place; the five public surfaces are checked at 375, 1440, 2560 and 812×375 landscape. Admin controls/forms/lists are checked at 320/390/768/1440/1920/2560 with normal/doubled root text. **Native browser zoom was not executed**; root-font enlargement is not reported as native zoom.

[174 measured admin controls](control-metrics.json): minimum width **47.19px**, height **44px**, horizontal padding **19.2px**, vertical padding **8.8px**. Existing card/filter/machine tests verify their specialized controls, keyboard/disabled/touch/no-JS paths. Whole-app conformance is not inferred from these samples.

## Opened screenshot evidence and review

Matched public captures use the same fixed-date visual photo fixture, viewport **height 1000**, fresh guest session, reduced motion, loaded fonts/assigned derivatives, arrival scroll and footer-element crop. Admin uses the same functional fixture; its live default visit date/time differs between before/after captures and is not a pixel comparison. Historical reference content/IA is different and is labeled mood-only.

| Matched set | Review |
| --- | --- |
| [Public 390](public-390.jpg), [1440](public-1440.jpg), [1920](public-1920.jpg), [2560](public-2560.jpg) | Shared archival environment preserved. Widths through 1920 retain P0-4 pixels; 2560 puts meaningful content into the shell without enlarging cabinet art |
| [Footers 390](footers-390.jpg), [1440](footers-1440.jpg), [1920](footers-1920.jpg), [2560](footers-2560.jpg) | Actual five-route closing crops, not only top-of-page/token review. Warm closing surface, real navigation, statement and copyright retained; ultrawide alignment expands |
| [Place/admin comparison](place-admin.jpg) | Genuine facts replace the stranded photo track; system-font/plain admin separates owner work from public spectacle |
| [Historical/current/repaired mood](mood-lineage.jpg) | Intermediate 801cc57 cabinet-on-Explore provenance preserved; no claim of pixel-equal restoration or reintroduction of old mechanics |

The contact sheets were actually opened, as were selected full-size ultrawide Explore/Place, mobile photo-free Place, desktop admin form, public footer and lower-page Home/Explore/Visit views. The original `Foodie_Memory_Archive/reference_full_page.png` and actual `qa/memory-archive-v2/explore-1440.png` were opened. Not every original screenshot pixel was manually inspected. Subjective warmth/hierarchy judgments are design review; dimensions, HTTP/network/eligibility and matcher results are objective checks.

Visual review caught two geometry tradeoffs before finalization: unrestricted wide discovery photos hid captions below the fold, and the Place photo grew unnecessarily. The final component caps (32rem height / 50rem width) retain discovery and chronology priorities. [15 section captures and dimensions](sections.json) also inspect below the hero. No global brightness/filter/animation or original-image fallback was introduced.

## Executed commands and results

| Gate | Actual result / log |
| --- | --- |
| Initial unchanged P0-4 comparison, updates none | **23 passed / 40 snapshots**, [before](visual-before.log), before application edits |
| Production build, including standalone error CSS | **PASS**, [build](build.log) |
| Full guarded PHP suite after final changes | **77 passed / 1222 assertions**, [PHP](php.log); includes eight Place cases plus existing domain/media/security/backup/admin coverage |
| Pure machine reducer | **10 passed**, [Node](reducer.log) |
| Full default functional browser suite | **58 passed**, [full Chromium suite](browser-all.log): discovery, filters/history/staging, Home, public/errors, admin editor/publish, gallery, cards, readability/empty/sparse/denied media, navigation, shell, 19 cabinet cases, new hardening cases |
| Final affected-case rerun | **10 passed**, [final scope](browser-final.log), after the final Place derivative/file filtering and photo cap; full suite was not needlessly repeated for those narrow changes |
| Final P0-4 comparison | **23 passed / 40 snapshots**, updates none, [final](visual-final.log). No baseline PNG intentionally changed; no mask/tolerance/assertion weakening |
| Intentional shift/reversion | **Expected failure then pass**, 2249 differing pixels > 30; baseline SHA-256 unchanged, [proof](probe.json), [rejected](probe-rejected.log), [reverted](probe-reverted.log) |
| Manifest integrity | [Current manifest](manifest.json) validates all **40 original snapshot hashes**, links the untouched historical manifest, and records current build/fixture/environment inputs separately |
| Changed PHP Pint | **PASS**, [changed PHP](pint-changed.log). Whole-repository `php vendor/bin/pint --test` **fails pre-existing formatting** in untouched `tests/Feature/PublicTest.php` (line ending) and `tools/visual-qa.php` (style), [global log](pint.log); neither was reformatted |
| Syntax / diff | New JS helpers/spec/proof syntax checked; PHP helper included in changed-file Pint and exercised by fixtures. `git diff --check` result recorded in [diff check](diff-check.log) |

Browser: Windows Chromium **156.0.8078.4**, pinned visual configuration, device scale 1; new fixture date fixed to 2026-11-09 12:00 UTC. Repeated gate runs are not counted as additional distinct cases. The controlled Explore wait/failure is an actual outstanding intercepted request, not an application sleep. Six real isolated error responses at 390/1440/2560 have [status/heading/actions](http-errors.json) and `http-STATUS-WIDTH.jpg` captures. Other keyboard/reduced-motion/no-JS/touch journeys use existing full-suite cases.

[Single local lab sample](lab-measurements.json) records LCP 520ms and CLS 0.00020 on the functional Home fixture, without throttling; it is neither a before/after performance study nor field INP. Sampled semantic contrasts pass their thresholds (e.g. muted/hover 6.49:1, border/hover 3.23:1, focus/page 12.21:1). These token pairs and axe checks do not prove all photographic contrast or WCAG compliance.

## Reproduction

Use the existing documented isolated 8011–8016 helpers from [operations](../../operations.md) and the [P0-4 setup](../visual-baselines/verification.md); verify the identities above. Never run these against owner-configured Artisan targets. Prepare/serve 8017 separately:

```powershell
php tools/p0-hardening-qa.php prepare
php tools/p0-hardening-qa.php serve
```

In a separate terminal, execute sequential browser gates to avoid output collisions:

```powershell
npm run build
php artisan test --compact
node --test tests/Unit/vending-state.test.mjs
php vendor/bin/pint --test app/Http/Controllers/PublicController.php tests/Feature/PlaceHeroTest.php tests/Feature/FoundationTest.php tools/p0-hardening-qa.php
npx playwright test
node tools/prove-visual-baseline.mjs docs/qa/p0-hardening
npx playwright test --config playwright.visual.config.js --update-snapshots=none
node docs/qa/p0-hardening/capture.mjs after
node docs/qa/p0-hardening/capture-sections.mjs
python docs/qa/p0-hardening/contact-sheets.py
# Open/review the actual screenshots before recording manifest review.
node docs/qa/p0-hardening/manifest.mjs
git diff --check
```

Do not rerun `capture.mjs before` against repaired code; it would overwrite this task's genuine baseline. The legacy full-suite performance test rewrites `docs/qa/lab-measurements.json`; retain its fresh output separately and preserve prior dated evidence as this run did. No baseline update command is needed: **zero reviewed PNG changes**. The new manifest/proof output paths preserve the older P0-4 records.

## Completion boundary

| State | Scope |
| --- | --- |
| Implemented / verified within recorded environment | Detected P0 defects corrected; stable published hero/photo-free composition; adaptive geometry and warm public identity visually reviewed; system-font minimal admin; shared primitive/state inventory; intact P0-4 gate; seven decisions synchronized |
| Partial | Automated Chromium plus sampled visual/keyboard/contrast evidence, not exhaustive accessibility; whole-repository Pint still has unrelated formatting failures |
| Pending | P1 physical-response refinement, P2 discovery/new real-wait skeletons, P3 optional storytelling/history enhancements, P4 integrated access/device/performance work |
| Unknown | Native zoom, screen readers, physical touch hardware/Safari/Firefox, owner long-content/production load, field vitals and pathological archives with many missing derivatives. No isolated MariaDB rerun in this task |

Hero selection uses metadata existence checks in eager 50-row batches and selected-cover variant checks. A very large damaged archive can require multiple batches; measure before adding media-health indexing/caching, and preserve publication invalidation. A file can disappear after selection; existing image fallback and media-route checks handle subsequent failure without exposing originals. Existing 1600px hero derivatives can be enlarged in a wide Visit composition; processing dimensions were deliberately not increased.

Recommended next P1 entry: storyboard one local lamp/socket contact response with interruption/reduced-motion behavior, then measure it against the untouched source geometry, exact-print identity and current gate. Do not start new skeletons or broader discovery/storytelling work in that change.
