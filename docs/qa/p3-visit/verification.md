# P3-1 — Visit storytelling refinements

9 November 2026, Asia/Jakarta. Starting local HEAD `3c9f3e64db880dbd7f6cdffa9e714e5a83c7547f`, clean working tree. Read completed [P0-1](../navigation-hover-progress/verification.md), [P0-2](../wide-shell/verification.md), [P0-3](../readability-states/verification.md), [P0-4](../visual-baselines/verification.md), [P0-H](../p0-hardening/verification.md), [P1](../p1-physical-response/verification.md) and [P2](../p2-discovery/verification.md), design memory, design-system §1 and current page/art/motion contracts. Earlier reports remain dated history. Only Visit P3-1 is implemented here; Place chronology P3-2 and integrated P4 remain pending.

## Change and scope

The existing cover → facts → narrative → tasting notes → populated criteria → gallery → conclusion sequence remains. Tasting photographs have a light raised-material frame beside their notes; note edges group the author's words without turning the story into cards. The existing 68ch narrative measure, wide hero, historical price labels, ratings, allergen uncertainty and distinct personal recommendations remain. Recommendations retain their explicit label even when the narrative is absent. Gallery layout follows source order without dense grid backfilling. A real enlarged-text review caught squeezed criterion headings; a local 26rem container query now stacks each heading and score before text becomes cramped.

Only media with ready card/thumbnail metadata produces a gallery/heading/viewer. Card-only and thumbnail-only records both work, and the actual available derivative is the fallback. A physically missing/revoked derivative still uses existing failure handling and protected routes; metadata readiness is not a new filesystem-health promise. No controller/query/model/media-policy/processor/derivative-size/schema change. The controller and sole gallery/tasting-note consumers, shared image/modal/button/dialog/form bootstrap, viewer imports and existing feature/browser consumers were audited first. Shared dialog core and image fallback stay unchanged.

The native viewer reserves a scrollable image stage through genuine original wait, success, derivative fallback, total failure and retry. Status remains separate from the busy image region; retry/close remain available on failure, zoom is disabled without an image and exposes its pressed state. Current-thumbnail feedback is static. Each selection owns its image callbacks; detached/closed-image callbacks cannot commit a stale image or status. Closing removes the source and restores the opening thumbnail. Retry moves focus to the visible close control before hiding itself. Arrows preserve input/select/textarea/contenteditable shortcuts and previous/next stay bounded.

Original URLs remain inert data attributes until explicit activation. No prefetch, raw-original listing fallback, new reveal, entrance gate, skeleton timer, artificial wait, dependency, idle animation, palette/font, public-shell or hero-width change. Existing 140/220ms material feedback and global reduced-motion behavior are reused; text is settled immediately with reduced motion or no JS. Admin preview continues to consume the same Visit template; no admin workflow change is claimed.

## Isolation and executed checks

Only retained dedicated SQLite/fake-media fixtures were served: fixed-date **8015 photo-v1**, **8016 empty-v1**, and **8014 readability-sparse**. Photo identity headers are asserted. No preparation, refresh, migration, seed, owner `.env`/account/media access, MariaDB/3306/system-schema action, commit, push or deployment. PHP's existing test guard enforces SQLite memory. QA processes started here are stopped after verification; fixtures remain.

| Gate | Result / evidence |
| --- | --- |
| Production build | **PASS**, [build](build.log), installed dependencies only |
| PHP Visit/public/historical/nullable/publication/media/security | **29 passed / 413 assertions**, [PHP](php.log); includes two new tests for absent/partial sections, free versus null price, labeled endorsement without score/story, unready gallery omission, and 40 ready card/thumbnail records |
| New viewer/story browser checks | **4 passed**, [final](browser-final.log); real held original, 403 fallback, both-source failure, keyboard retry, stable stage, rapid changes/stale callbacks, pending close, zoom, Escape/focus, editable arrows, bounds, no-JS text story, four widths, short landscape and reduced/root-text 200%; scoped waiting-dialog axe scan has zero reported violations |
| Final reviewed visual gate | **23 passed / 40 PNGs**, updates disabled; [final](visual-final.log). Three named reviewed changes described below; 37 baseline hashes retained |
| Matcher negative control | Intentional 8px Home header shift rejected; clean reversion passes with identical Home baseline hash, [proof](probe.json), [rejected](probe-rejected.log), [reverted](probe-reverted.log) |
| Actual visual states | **44 captures**, [states](states.json): full story, tasting note, photo hover, gallery focus, real viewer wait/fallback/failure/success, restored focus, reduced/root-text 200%, photo-free story at 375/768/1440/1920 |
| Normal-time journeys | [1440 keyboard](realtime-1440.webm), [375 emulated touch](realtime-375.webm), [metadata](realtime.json); open, next/previous, zoom, actual original failure, derivative fallback, retry and close/focus restore. No slowMo, clock rewrite or seeking in recordings |
| Lab/navigation media | Three cold/warm samples per 390/1440 width before and final after, [before](before.json), [after](after.json), [comparison](performance.json); negative original requests and arrival media sets checked |
| Formatting / syntax / whitespace | Changed PHP Pint, viewer and QA JS syntax, `git diff --check`; [checks](checks.log), [integrity/lineage](manifest.json) |

There are **27 distinct passing browser cases** (4 new + 23 existing visual), excluding repeats and the deliberate failing probe. The one/40 browser boundary case uses a read-only intercepted HTML fixture with one or forty copies of a real derivative-backed thumbnail; the PHP case separately renders forty actual isolated metadata records, alternating thumbnail-only/card-only. This is control/grain coverage, not forty unique full-resolution network stress or owner-media validation.

## Reviewed visual gate

Opened full-size desktop/compact note and viewer wait/failure/success captures, full story/photo-free layouts, enlarged-text evidence, and sampled decoded normal-time video sheets. Photography and historical facts remain primary; note framing stays local, gallery order is coherent, the image stage remains fixed across wait/failure, and close/focus controls remain visible. These are scoped design judgments plus named geometry/state checks, not WCAG certification.

The initial and final pre-review comparisons each passed **20/23 cases** and rejected exactly these three crops:

- `viewer-original-390`: expected change from the old image-sized dialog to the reserved stage/status geometry. [Before](viewer-original-390-before.png), [reviewed actual](viewer-original-390-reviewed.png), [diff](viewer-original-390-diff.png).
- `visit-1440-footer`: footer element crop changed from 353 to 352 pixels; the differing strip is the bottom crop boundary, with unchanged footer content/style. [Before](visit-1440-footer-before.png), [actual](visit-1440-footer-reviewed.png), [diff](visit-1440-footer-diff.png).
- `visit-1920-footer`: 328 differing pixels confined to copyright rasterization after the upstream story height changed. [Before](visit-1920-footer-before.png), [actual](visit-1920-footer-reviewed.png), [diff](visit-1920-footer-diff.png). Footer source/styles were not edited.

After inspecting actual/expected/diff images and behavior, the named Visit 1440/1920 and viewer cases were explicitly refreshed; their unchanged companion hero baselines retain their original bytes. [Named update](visual-named-review.log), [initial rejection](visual-initial.log), [final pre-review rejection](visual-pre-review.log). No matcher tolerance, mask, font/browser pin, fixture, geometry/privacy/network assertion or default update policy changed. The final entire gate runs with `--update-snapshots=none`; no blanket update was used.

Capture readiness eagerly decodes assigned derivatives for deterministic screenshots only; application lazy loading remains unchanged. Actual held/intercepted requests produce intermediate waits/failures. Full-page 200% captures are tall and should be inspected at useful scale; root-font enlargement is not native browser text zoom. Video sheets sample decoded frames and do not replace the original recordings; short observation holds on real settled states improve legibility without delaying application work. The first concurrent after sample is retained as `after-concurrent-diagnostic.json` and excluded; `after-pre-reflow.json` predates the final criterion fix and is also excluded. Final before/after measurements run without other browser gates.

## Rendering/network cost

Windows Chromium **156.0.8078.4**, viewport 390/1440×1000, scale 1, reduced motion, local fixed-date fixture, existing self-hosted fonts, no throttling. Cold means a fresh browser context; warm means second same-context navigation, not a production CDN/cache model. Three samples per cell are small lab evidence, not field LCP/INP or a speed-improvement claim.

| Median of three / maximum CLS | 390 before → after | 1440 before → after |
| --- | --- | --- |
| Cold LCP | 688 → 536ms | 776 → 576ms |
| Warm LCP | 536 → 488ms | 564 → 500ms |
| Maximum arrival CLS | .013963 → .013963 | .003147 → .003147 |
| Completed arrival media bytes, cold | 150,870 → 150,870 | 469,756 → 469,756 |
| Completed arrival media bytes, warm | 150,870 → 150,870 | 333,094 → 333,094 |

No >10% median LCP regression or new sampled CLS; same hero/arrival derivative URL/encoded-byte sets and no original before activation. These bytes cover completed arrival resources, not every lazy image in the full story. Raw ranges remain in the comparison: the first baseline cold compact sample includes a 9,232ms startup outlier, so do not claim causal speed gains. Hero width, responsive `sizes` and derivatives were not changed; photo frame padding is local. No new animation/timeline/network loop. Field/slower-device attribution remains P4.

## Reproduce and completion boundary

Serve the retained isolated fixtures in separate terminals, then run browser/capture/measurement gates sequentially:

```powershell
php tools/visual-qa.php serve photo
php tools/visual-qa.php serve empty
php tools/readability-qa.php serve sparse
# In the task terminal:
npm run build
php artisan test --compact --filter='CinematicPublicTest|PublicTest|MediaTest|SystemSecurityTest'
php vendor/bin/pint --test tests/Feature/CinematicPublicTest.php
npx playwright test tests/Browser/visit-story.spec.js
npx playwright test --config playwright.visual.config.js --update-snapshots=none
node tools/prove-visual-baseline.mjs docs/qa/p3-visit
node docs/qa/p3-visit/capture.mjs
node docs/qa/p3-visit/record.mjs
node docs/qa/p3-visit/review-video.mjs
git diff --check
node docs/qa/p3-visit/manifest.mjs
```

Do not overwrite genuine `before` evidence or perform another snapshot update merely to silence a failure. `measure.mjs before|after` refuses to overwrite its JSON; an explicitly archived run and visual review are required when repeating it. Replacing captures/videos changes manifest hashes; inspect them before regenerating integrity evidence.

**Complete within the scoped Chromium gate:** Visit P3-1 story/viewer refinement, isolated nullable/historical/publication/media checks, original-on-demand, actual state/focus/reflow review and reviewed pixel gate. **Pending:** P3-2 and P4. **Unverified:** native text zoom, physical Safari/iOS/Android/Firefox, screen readers/assistive hardware, field vitals, owner production load and forty unique-original stress. Full PHP/default browser suites, standalone admin-preview UAT and deployment were not performed. Screenshots, emulated touch, root-font enlargement and axe are not accessibility certification.
