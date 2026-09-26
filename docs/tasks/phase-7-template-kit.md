# Phase 7 — Template kit

Goal: adding a template is one command plus React work, with a clear
contract and shared hooks. Design: [11 · Template kit](../11-template-kit.md).
Decision: D26.

---

## P7-01 · Template registry replaces the `Template` enum — `done`

- [x] `TemplateDefinition` value object + `TemplateRegistry` (code templates
      from `resources/js/templates/*/template.json`, cached).
- [x] `template.json` for changelog, playground, terminal (values moved from
      the enum).
- [x] `SiteSetting.active_template` → plain string validated by the
      registry; unknown value falls back to the default template.
- [x] `TemplateManager`, `Appearance`, `app.blade.php`, `HandleInertiaRequests`
      and the Pest datasets use the registry. Delete `App\Enums\Template`.

**Acceptance**: no visible change; all existing tests green; a test adds a
fake manifest and sees it in the registry.

## P7-02 · Kit: shared hooks & components — `done`

- [x] `resources/js/kit/index.ts` exporting `useContactForm`,
      `useArchiveFilters`, `useSiteSearch`, `useTheme`, `ArticleBlocks`
      (headless) and the existing shared components and helpers.
- [x] Migrate the three templates' contact forms, archive filters and search
      onto the kit (no visual change).

**Acceptance**: `npm run types:check`, `npm run check` pass; contact and
filter feature tests still pass for every template.

## P7-03 · `minimal` starter template — `done`

- [x] Plain, heavily commented template implementing all 9 pages with the kit.
- [x] Screenshot `public/templates/minimal.webp`.

## P7-04 · `php artisan make:template` — `done`

- [x] Copies `minimal` (or `--from=<id>`) into `resources/js/templates/<id>`,
      generates `resources/js/pages/<id>/*.tsx` wrappers and a placeholder
      screenshot, rewrites ids/class scopes.
- [x] Refuses existing ids and invalid slugs.

**Acceptance**: `make:template demo && npm run build:ssr && php artisan test`
passes with the new template in the matrix (test in CI with a temp dir).

## P7-05 · `/dev/templates` gallery (local only) — `done`

- [x] Route registered only in the local environment; renders any page of any
      template with demo data; light/dark toggle.

## P7-06 · Guide: `docs/templates/building-a-template.md` — `todo`

- [ ] Step-by-step guide + per-page prop reference + rules from 11 §6.
- [ ] Update 06 § "Adding a new template later" to point to it.

---

## Notes

- 2026-09-26 — P7-01: `TemplateRegistry` + `TemplateDefinition` read
  `resources/js/templates/<id>/template.json`; `App\Enums\Template` deleted.
  `active_template` is a plain string; unknown ids fall back to the default
  (config `portfolio.templates.default`). New `template:cache` /
  `template:clear` commands run with `optimize` / `optimize:clear`. The
  shared `template` prop now also carries `name`, so `PreviewBar` no longer
  hard-codes labels. Tests: `tests/Unit/TemplateRegistryTest.php`
  (discovery, fallback, invalid manifests, cache, nine page files per
  template), fallback feature test, commands test; the `templates` dataset
  lives in `tests/Pest.php` (`templateIds()`). `composer ci:check` green
  (185 tests).
- 2026-09-26 — P7-02: `resources/js/kit` (`@/kit`): `useContactForm`,
  `CONTACT_FIELDS`/`CONTACT_TOPICS`, `useArchiveFilters` (moved from
  `shared/inertia/useSearchChange.ts`), `useSiteSearch` + `rankByQuery`
  (now also used by the command palette), `useTheme`, headless
  `ArticleBlocks` with exhaustive `ArticleBlockRenderers`, and re-exports of
  the shared hooks, components and helpers. The three templates' contact
  forms (~70 duplicated lines each), archive wrappers, theme toggles and
  article bodies now use the kit. **Bug fixed:** article `image` blocks
  (supported by the panel's block builder) were rendered by no template;
  the exhaustive renderer type surfaced it and each template now renders a
  figure with caption. Checked in Chromium on all three templates: contact
  validation summary, clearing while typing, submission stored in the
  inbox, success state; image blocks with caption. `composer ci:check`
  green.
- 2026-09-26 — P7-03: `resources/js/templates/minimal` (manifest, styles,
  layout, components, nine pages, README) and `resources/js/pages/minimal`
  wrappers, built only on `@/kit`; system fonts, so `preloadFonts` is empty
  (the registry test now allows that and still checks listed files exist).
  The nav hides pages switched off in the panel. Screenshot
  `public/templates/minimal.webp` (960×600) taken from the running app with
  the demo content. Checked in Chromium: every page renders (404 for
  unknown URLs), project search filter, theme toggle, contact form
  validation and submission, light/dark and mobile.
- 2026-09-26 — P7-04: `php artisan make:template <id> [--from=] [--name=]
[--description=] [--author=] [--no-format]` via
  `App\Support\Templates\TemplateScaffolder`: copies the source template and
  its pages, renames the layout, CSS scope and import paths, writes the
  manifest and a README with next steps, copies the screenshot as a
  placeholder, adds the stylesheet import to `styles/main.css`, formats the
  output with Vite+ and refreshes a warm template cache. Reserved ids:
  `studio`, `reset`. Paths are in `config/portfolio.php`. Tests run against
  a temporary copy (`tests/Feature/MakeTemplateCommandTest.php`). Acceptance
  checked locally: `make:template demo-zine` → `npm run build:ssr` →
  `composer ci:check` green with the new template in the page matrix, then
  removed. The bundled-ids registry test now only checks our templates are
  present, so contributors' templates do not break it.
- 2026-09-26 — P7-05: `/dev/templates` gallery (compare one page across
  templates, or every page of one template; light/dark; desktop/mobile)
  backed by a stateless `?_template=` + `?_theme=` override in
  `TemplateManager` (no session, no preview bar, noindex, theme rendered
  server-side). Gated by `portfolio.templates.dev_gallery`
  (`TEMPLATE_GALLERY`, default on only in `APP_ENV=local`); otherwise 404
  and the override is ignored. Tests in
  `tests/Feature/Site/TemplateGalleryTest.php`; checked in Chromium (frames
  render their own template and theme, no preview bar, no errors).
- 2026-09-26 — Template fixes found by the owner (commit ae7f603): terminal
  experience tabs show company logos; education shows description and all
  achievements in every template; the terminal timeline no longer fades
  over entries; empty certifications/degrees columns are hidden in every
  template; terminal client logos colour in with an animation on hover.
