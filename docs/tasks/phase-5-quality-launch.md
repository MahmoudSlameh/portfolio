# Phase 5 — Quality & launch

---

## P5-01 · Test hardening & CI — `todo`

- [ ] Coverage of every item in [08 · Testing](../08-conventions.md#testing-pest).
- [ ] CI workflow (`.github/workflows/tests.yml`) also runs
      `npm run build:ssr` and publishes/links the storage (media tests use
      `Storage::fake`).
- [ ] Larastan level 7 clean with no baseline growth.

**Acceptance**: `composer ci:check` green locally and on GitHub Actions.

## P5-02 · Content health & admin polish — `todo`

- [ ] Walk through every screen in 04 with an empty DB and with demo data;
      fix empty states, helper texts, icons, column widths, mobile layout.
- [ ] Content health widget reports correctly.

## P5-03 · Remove `Reference-Frontend/` — `todo`

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

_(add dated notes here)_
