# Repository guidance

For UI design, implementation or review, load `$HOME/.codex/skills/ui-craft/SKILL.md` first (`$env:USERPROFILE\.codex\skills\ui-craft\SKILL.md` is the Windows equivalent). If that personal skill is unavailable, continue using the repository contracts and record the limitation; do not install or vendor it automatically. Read `.ui-craft/brief.md`, `.ui-craft/tokens.md`, `design-system-specification.md` §1 (composition and Brand Voice & Microcopy), then the relevant canonical experience contract.

## Authority and read order

- Keep the five root specifications authoritative for unchanged product/domain rules. Latest explicit owner instructions govern their stated scope. [Product vision](docs/product-vision.md) defines precedence; [ADRs](docs/adr/README.md) resolve design reversals.
- Current intent: [art direction](docs/art-direction.md), [motion](docs/motion-interaction.md), [page states](docs/page-experience.md). Current evidence: [implementation status](docs/implementation-status.md). Future work: [frontend roadmap](docs/roadmap/frontend-experience.md). Dated reports/QA/input packages are historical, not competing current instructions or fresh PASS results.
- Foodie is one author's Indonesian culinary journal, not a food business/community ranking site. Preserve published-only access, historical IDR prices, nullable ratings, distinct recommendations and allergen uncertainty.

## Locked experience and verification

- One playable vintage Memory Archive on Home; Explore is a discovery board plus complete photographic Visit/Food/Place browsing; Visit/Place share cinematic archival storytelling. Keep shared header/footer, approved Editorial Bold errors and `config/error-pages.php` status copy.
- Resting tube is vertical outside LEFT at RESET height, original socket unchanged, two-gusset bolted side bracket. No visible pickup tutorial/tooltip/label or pagination arrows. Keep indexed rotary, square reset, power/print identity, cancellation and keyboard/touch/no-JS alternatives. See the motion contract before touching mechanics.
- Use semantic tokens, adaptive public shell (152rem cap; 68ch reading measure), real content and shared primitives. Keep source-native cabinet limits; reflow ultrawide result groups. Home 5/Explore 3/Detail 3 are qualitative motion ambitions; meaningful 3D is allowed with measured cost and fallbacks. Admin is minimal, system-font, plain-background and 80rem. Mark requested targets as pending until implemented.
- Place hero is independent of history pagination: latest visited_at then id among published same-visit covers with a ready, existing hero derivative; otherwise use the text/facts composition. Preserve publication/media revocation. New real-wait skeletons belong to P2 (choice 6A); P0-H verifies existing busy/empty/error/retry semantics. See [P0-H decisions](docs/adr/002-p0-hardening.md).
- Preserve working-tree changes. Audit callers/consumers before edits. Run relevant non-destructive tests/build and `git diff --check`; UI work also needs real desktop/mobile, intermediate-state, keyboard, reduced-motion and enlarged-text evidence. Record unknowns; screenshots/axe alone are not WCAG certification.
- Done means scoped behavior verified, no feature/privacy/mechanical regression, and affected contracts, brief/tokens, status and roadmap synchronized. Record reversals in an ADR; retain old QA as dated history.

## Data safety

See [README](README.md), [operations](docs/operations.md), and `codex_instruction/001-foodie-mvp-implementation.md` for setup/scope. Protect owner `.env`, account, media and data. Never refresh/wipe/reseed an owner database. Use isolated fixtures; tests refuse unsafe targets. `DB_DATABASE=mysql` is MariaDB's system schema, never a Foodie target. The active Foodie instance on 3306 and separate shared-XAMPP recovery issue are documented in operations; do not conflate them.
