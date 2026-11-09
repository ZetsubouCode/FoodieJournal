# Experience decision register

Read [authority and precedence](../product-vision.md#authority-and-read-order) first. This register tracks approved decisions and reversals; it does not approve a proposed implementation as completed.

- [001 — Experience authority, placement and state corrections](001-experience-authority.md)
- [002 — P0 hardening: adaptive width, eligible Place cover, minimal admin, P2 skeleton ownership](002-p0-hardening.md)

## Document classification

| Class | Documents | How to use |
| --- | --- | --- |
| Product/domain authority | Five root specifications | Preserve business rules; their original draft questions are not new blockers or tested status |
| Maintained experience contracts | `product-vision`, `art-direction`, `motion-interaction`, `page-experience`, `ux-principles`; design-system §1 | Current approved direction and constraints |
| Maintained observation/plan | `implementation-status`, `roadmap/frontend-experience`, this ADR register | Current-vs-target, evidence and future work |
| Operational authority | `operations.md`, README setup/safety | Environment, media, backup and isolated testing |
| Working design memory | `.ui-craft/brief.md`, `.ui-craft/tokens.md` | Concise current summary; synchronize with contracts |
| Historical narrative | `cinematic-redesign`, `discovery-machine`, `discovery-fidelity-correction`, `projection-system-reconstruction`, `memory-archive-completion`, `memory-archive-v2-completion`, `vending-machine-interactive`, `vending-machine-refinement`, `hanging-lamp-correction` | Retain rationale/assets/results; superseded mechanics are not implementation instructions |
| Historical delivery/security records | `implementation-report`, `implementation-checklist`, `implementation-decisions`, `error-page-redesign` | Dated results and domain rationale, not current all-green certification; approved error contract still lives in design-system/config |
| Historical option boards and source packages | `docs/design/homepage-design-decisions.md`, old surface notes, `codex_instruction/001`, `002`, visit-card and vending packages, `Foodie_Memory_Archive` | Provenance; retain F Refined identity and surviving requirements, resolve overridden layout/mechanics through ADR 001 |
| Historical QA | Existing `docs/qa/*` and recordings | Evidence for the recorded fixture/build/date only; preserve files and limitations |
| Current audit evidence | `docs/qa/frontend-experience` | 8 November baseline; missing targets are not passing screenshots |

An old report may describe an active domain rule accurately; its historical label means it cannot override a newer contract or claim today's test result. Update a maintained contract when intent changes; add a new ADR for a reversal; retain dated QA unchanged.
