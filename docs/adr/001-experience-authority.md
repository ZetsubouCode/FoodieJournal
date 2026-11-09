# ADR 001 — One maintained experience contract

Status: accepted for documentation on 8 November 2026, grounded in owner instructions `004-docs hardening.md` and the latest recorded 7 November corrections. The original audit application baseline was `b56db2c`; P0-1/P0-2 follow-ups are recorded below. This ADR consolidates approvals; it does not invent approval for unfinished roadmap work.

## Decisions and reversals

| Decision | Superseded rule | Binding replacement / evidence |
| --- | --- | --- |
| Cabinet ownership | Older projector/cabinet on Explore | One playable vintage Memory Archive on Home; Explore is discovery board + editorial browsing. `801cc57`, current public views, 004 §1 |
| Printed object | Surprise envelope/collection reveal, lower rotary reset, lamp pause/random flicker | Exact selected-visit photograph, upper indexed page dial, square push reset, removable power tube. Current reducer/archive module and vending tests |
| Resting tube | 6 Nov outboard horizontal cradle → 7 Nov integrated left-panel vertical dock → outside-left at socket height | Vertical outside LEFT at RESET height; original socket at 2.8%/27%/4.25%/28.7%; two-gusset bolted side plate; 48–80px gutter. Final CSS, brief's latest correction, `qa/reset-side-lamp/verification.md` |
| Pickup discovery | Delayed visible “Ambil lampu”, discovered-session suppression, rectangular outline | No visible wording/tooltip/tutorial. Finite cap/glass reflection on each mounted OFF page, static reduced-motion cue, screen-reader help retained. `archive.js::dismissHint/render`, final pickup CSS and reset-side-lamp QA |
| Page controls | Visible next/previous buttons near dial | No visible pagination replacements; 32° indexed selector with resistant travel, keyboard, focused wheel, click-half and pointer/touch alternatives. `vending-input.js` and tests |
| Public motion | Blanket interface “motion 2”, perceived ban on 3D from old implementation limitations | Home 5/5, Explore/Detail 3/5 qualitative ambition; physicality → choreography → feedback; meaningful 3D allowed with measured cost and fallbacks. 004 §§3–4 |
| Home collections | Automatic infinite Recent/Picks carousels, discovery tiles on Home | Editorial recent entries and explicit picks; discovery board belongs Explore. Current Home and 004 identity; old option board is historical |
| Shared navigation | Static active underline after cinematic rewrite | Restore warm left-to-right hover progress with defined leave/re-entry/focus/reduced behavior. Historical `4f6365b:home.css`, approved Header B record, 004 explicit correction; not implemented in the original audit phase, subsequently **implemented 9 November as P0-1** ([verification](../qa/navigation-hover-progress/verification.md)) |
| Shell width | Old 80rem design cap treated as timeless approval | Fluid/wide nav/main/footer with full-width scene and separate reading measure. Target numbers in art-direction; not implemented in the original audit phase, subsequently **implemented 9 November as P0-2**, with an isolated public shell and unchanged admin width ([verification](../qa/wide-shell/verification.md)) |
| Footer / environment | Copy exact old markup or accept current flattening as approved forever | Preserve archival warmth and cohesive editorial ending across pages; rebalanced in P0-2 on 9 November with shared warm variants and two-line editorial close ([verification](../qa/wide-shell/verification.md)). Current single chrome architecture remains |

## Consequences

Contracts and brief now contain one active version. Historical narrative reports are visibly classified, with their original evidence retained. The five root specifications retain unchanged domain authority; pertinent design sections reference the new contracts. Aspirational work remains in the roadmap, not a retroactive “PASS” checklist.

No wholesale CSS/JS rewrite, new package, migration, owner-data write or behavior change is authorized by this ADR. Implement small roadmap tasks later, retain exact-visit identity and existing safety, and refresh status/evidence after each accepted change.
