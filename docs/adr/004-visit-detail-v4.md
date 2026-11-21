# 004 — Visit Detail V4 and V1 shared chrome

Accepted 21 November 2026. The owner's V4 visual handoff approves the V3 body layout with corrections in `VISUAL_ANATOMY_AND_OVERRIDE.md` and `IMPLEMENTATION_BRIEF.md`. The repository keeps the shared Laravel chrome under the written V1 contract. The private V1 reference was unavailable for pixel comparison.

The subsequent owner-approved screenshots govern shared chrome. [ADR 005](005-approved-v1-public-chrome.md).

## Decision

- Use the same public shell for the full-width photographic or text-led opening and the Visit body. Join the three snapshot facts in one tonal strip.
- Pair tasting photographs with a paper note, put long authored reviews into a flowing spread, and give photo-free dishes a sepia text-only record. Show real historical prices, food scores and allergy evidence.
- Number the optional Cerita, Daftar Rasa, Catatan, Foto and Pendapat chapters in render order. The desktop rail fades when idle and remains available on focus. Keep source order through mobile reflow.
- Move recommendation labels from the former linked cluster into the closing Pendapat section. Align its eyebrow to the start of the saved conclusion. Present each saved recommendation as a noninteractive ink stamp below the quote. A label can appear alone.
- Open the matching protected original from either its tasting photo or gallery entry. Keep derivative links usable without JS, native viewer focus restoration, fallback and retry.

This supersedes the earlier Visit presentation recorded in P3-1 and ADR 003. Food-only rating calculations, publication eligibility, routes, media authorization and admin preview rules continue to apply. [Current verification](../qa/visit-v4/verification.md). [Historical P3-1 evidence](../qa/p3-visit/verification.md).
