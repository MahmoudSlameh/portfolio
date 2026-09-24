# Phase 0 — Foundation

Docs: [01-project-overview](../01-project-overview.md), [05-media](../05-media.md), [08-conventions](../08-conventions.md)

---

## P0-01 · Install dependencies & verify baseline — `todo`

**Steps**
- [ ] `composer install`, `cp .env.example .env`, `php artisan key:generate`,
      `touch database/database.sqlite`, `php artisan migrate`.
- [ ] `npm install` (root). Note: `pnpm-workspace.yaml` exists but
      `package-lock.json` is committed → use **npm**.
- [ ] `composer dev` → `/` renders `welcome`, `/admin/login` renders Filament.
- [ ] `composer ci:check` passes on the untouched baseline (fix only tooling
      issues, if any).
- [ ] Add `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `ADMIN_EMAILS`,
      `MEDIA_DISK=public` to `.env.example`. Set `APP_NAME="Portfolio"`.

**Acceptance**: fresh clone → the commands in `docs/README.md#quick-start`
work; CI green.

---

## P0-02 · Laravel Boost + AI guidelines — `todo`

- [ ] `php artisan boost:install` (select Filament, Inertia React, Pest,
      Tailwind guidelines). It generates `CLAUDE.md`/`AGENTS.md` locally
      (both are gitignored by the starter kit). The project pointer lives in
      the committed custom guideline `.ai/guidelines/project-docs.md`, which
      Boost merges into those files — verify it appears in the generated
      `CLAUDE.md`.
- [ ] Commit `.mcp.json`/boost config if generated (no secrets).

**Acceptance**: agents can call `search-docs` for Filament 5 docs.

---

## P0-03 · Spatie Media Library setup — `in-progress`

Already done (2026-09-24): `filament/spatie-laravel-media-library-plugin: ^5.8`
added to `composer.json`; `composer.lock` resolved (plugin v5.8.4,
spatie/laravel-medialibrary 11.23.8, spatie/image 3.9.6). Files were **not**
downloaded in that environment — run `composer install`.

**Steps**
- [ ] Publish migrations + config (commands in [05-media](../05-media.md#setup)), migrate.
- [ ] Configure `config/media-library.php`: disk from `MEDIA_DISK`, queued
      conversions, image driver.
- [ ] `php artisan storage:link`; ensure `public/storage` ignored.
- [ ] Listener storing `width`/`height` custom properties on
      `MediaHasBeenAddedEvent` (for raster images).
- [ ] Smoke test (Pest): a temporary test model… or wait for P1-02 and test
      `Profile` portrait upload + conversion generation.

**Acceptance**: uploading an image through a Filament
`SpatieMediaLibraryFileUpload` creates a `media` row, the file on the `public`
disk and the `webp`/`thumb` conversions after the queue runs.

---

## P0-04 · Extra packages & `config/portfolio.php` — `todo`

- [ ] `composer require symfony/intl` (country names). Record in 09 if a
      different approach is chosen.
- [ ] `config/portfolio.php`: `admin_emails` (from `ADMIN_EMAILS`, comma
      separated), `reading_wpm` (220), `cache_ttl`.
- [ ] Root `package.json`: add `lucide-react`, `motion`, `sugar-high`, `zod`
      (versions from `Reference-Frontend/package.json`) — may be deferred to
      P3-01.

**Acceptance**: `Countries::getNames()` works in tinker; config readable.

---

## Notes

- 2026-09-24 — Docs & task plan created. Media plugin added to composer files (P0-03 partially done).
