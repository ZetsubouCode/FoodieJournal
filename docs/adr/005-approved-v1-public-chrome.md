# 005 — Approved V1 header and footer screenshots

Accepted 21 November 2026. The owner supplied `V1_HEADER_APPROVED.png` and `V1_FOOTER_APPROVED.png` in the V5 repair package. They settle the chrome interpretation in ADR 004. The V4 body remains the approved Visit composition. [ADR 004](004-visit-detail-v4.md).

- Use one full-width header surface and one inner public shell. Beranda and Jelajah are global. A public Visit adds the active Catatan ini link, its Place destination and a real motion switch in a separate contextual group. The switch follows the system preference on load. A deliberate change applies to the current Visit page. A system preference change restores system control.
- Use the two-line editorial statement, warm full-width panel, horizontal lower-right navigation and small bottom-left copyright. Visit links go to the top, the tasting chapter when present and the Place. Other public surfaces use Beranda and Jelajah with the same return-to-top link. The compact admin shell retains its own presentation.
- Keep content in normal flow and allow navigation to wrap at narrow widths or with enlarged text. Error pages use the same shared chrome through the standalone CSS build.

The comparison and test scope are recorded in the [V5 verification](../qa/visit-v5/verification.md).
