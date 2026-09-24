# Phase 5 — Quality & launch

---

## P5-01 · Test hardening & CI — `done`

- [x] Coverage of every item in [08 · Testing](../08-conventions.md#testing-pest).
- [x] CI workflow (`.github/workflows/tests.yml`) also runs
      `npm run build:ssr` and publishes/links the storage (media tests use
      `Storage::fake`).
- [x] Larastan level 7 clean with no baseline growth.

**Acceptance**: `composer ci:check` green locally and on GitHub Actions.

## P5-02 · Content health & admin polish — `done`

- [x] Walk through every screen in 04 with an empty DB and with demo data;
      fix empty states, helper texts, icons, column widths, mobile layout.
- [x] Content health widget reports correctly.

## P5-03 · Remove `Reference-Frontend/` — `done`

Only when all templates are ported, P1-09 seeder no longer reads from it,
and the owner confirms. Remove it and any references in docs (keep a note in
09 with the last commit hash that contained it).

## P5-04 · Deployment guide & launch checklist — `todo`

Write `docs/10-deployment.md`: server requirements (PHP 8.3+, Node 22,
imagick/gd with WebP, image optimizers), env vars, build steps
(`composer install --no-dev -o`, `npm ci && npm run build:ssr`,
`php artisan migrate --force`, `optimize`, `storage:link`), Supervisor
programs (queue worker, SSR), scheduler (if used), backups (DB + media disk),
and the launch checklist from 07 §6.

---

## Notes

- 2026-09-24 — P5-01: 171 tests / 1455 assertions green; Larastan level 7 with no baseline. CI (.github/workflows/tests.yml) now runs PHP 8.4 (the lock's Symfony 8 needs ≥ 8.4.1; composer.json requires ^8.4) with gd/intl/exif and builds the SSR bundle. Media tests use Storage::fake, so no storage:link is needed in CI. Added tests: book edit, uses-group list/edit, and an empty-install test for every template. Removed the starter's placeholder tests.
- 2026-09-24 — P5-02: Walked the admin and site with an empty DB (seeders without demo content) and with demo data, at desktop and mobile widths. Admin empty states, helper texts and content health are correct. Found and fixed a client crash on the changelog home when there are no companies. Home sections in all templates now render only when their list has items (see docs/08 § Empty content).
- 2026-09-24 — P5-03: Reference-Frontend/ and scripts/export-reference-data.cjs removed. The last commit that contains them is eb06a9a (restore with `git checkout eb06a9a -- Reference-Frontend`). Lint, Tailwind and seeder references cleaned up; living docs updated (D24).
