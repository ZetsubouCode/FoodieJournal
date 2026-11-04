# Repository guidance

For UI design or implementation, load `C:\Users\kigna\.codex\skills\ui-craft\SKILL.md` first. Read `.ui-craft/brief.md` and the canonical Visual Composition and Brand Voice & Microcopy guidance in `design-system-specification.md` §1 before editing a surface. Use the approved Editorial Bold composition for HTTP errors; the approved status copy lives in `config/error-pages.php`.

Keep the five root specifications authoritative for product and domain requirements. See `README.md` for setup and `codex_instruction/001-foodie-mvp-implementation.md` for scope and safety conventions. Use isolated test databases; never refresh owner data. `DB_DATABASE=mysql` is MariaDB's system schema, never a Foodie target. The current port-3306 Foodie instance and the separate shared-XAMPP recovery issue are documented in `docs/operations.md`.
