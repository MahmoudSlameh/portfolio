# 09 · Decisions & open questions

## Decision log

| #   | Date       | Decision                                                                                                                                                       | Why                                                                                           |
| --- | ---------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| D01 | 2026-09-24 | Laravel 13 + Filament 5 + Inertia 3 + React 19 with **SSR**                                                                                                    | Owner's stack; SSR required for SEO                                                           |
| D02 | 2026-09-24 | **English only**; drop `Localized<T>`, locale toggle, RTL, Arabic dictionary                                                                                   | Owner: "main language is English, other languages don't matter"                               |
| D03 | 2026-09-24 | **All media via Spatie Media Library** + official `filament/spatie-laravel-media-library-plugin` (^5.8)                                                        | Owner requirement. Added to composer.json/lock (installed version v5.8.4, medialibrary 11.23) |
| D04 | 2026-09-24 | **All content (bio, images, career, …) editable in the panel** — nothing hard-coded                                                                            | Owner requirement                                                                             |
| D05 | 2026-09-24 | Singletons (`Profile`, `SiteSetting`, `NowPage`) are Eloquent single-row models, not `spatie/laravel-settings`                                                 | They need to own Spatie media; one consistent pattern                                         |
| D06 | 2026-09-24 | Template chosen server-side; Inertia component name = `{template}/{Page}`                                                                                      | Code-splitting + SSR only loads the active template; no client registry                       |
| D07 | 2026-09-24 | Archive filtering on the server (Form Requests + Eloquent) instead of zod/TanStack search                                                                      | Single source of truth, SSR-friendly, shareable URLs                                          |
| D08 | 2026-09-24 | Experience "stack" and project "stack" are a polymorphic relation to `skills`                                                                                  | Reuse, consistent names, clickable tech filters                                               |
| D09 | 2026-09-24 | Changelog-only fields (branch, version, commit, message) are auto-derived with optional overrides                                                              | Owner shouldn't have to invent git hashes; template keeps its flavour                         |
| D10 | 2026-09-24 | Countries stored as ISO alpha-2; names via `symfony/intl`                                                                                                      | Clean data, searchable select, JSON-LD friendly                                               |
| D11 | 2026-09-24 | API Resources (not `spatie/laravel-data`) + hand-maintained TS types, guarded by prop-shape tests                                                              | Fewer moving parts; can revisit                                                               |
| D12 | 2026-09-24 | Own sitemap/RSS/robots implementations (Blade views)                                                                                                           | Tiny, no dependency on L13 support of third-party packages                                    |
| D13 | 2026-09-24 | Template preview (`?template=`) is **admin-only** (logged-in panel user); visitors always see the active template; preview pages are `noindex`                 | Owner answer Q01; no duplicate-content SEO issues                                             |
| D14 | 2026-09-24 | Keep **all** public pages (Writing, Books, Uses, Now); `enabled_pages` toggles stay as an option, all on by default                                            | Owner answer Q02                                                                              |
| D15 | 2026-09-24 | Production database **MySQL 8**. Dev/CI keep SQLite. Migrations must work on both (no DB defaults on `json` columns — set defaults in the model `$attributes`) | Owner answer Q05                                                                              |

## Open questions for the owner

Answered so far: Q01 → D13, Q02 → D14, Q05 → D15.
Answer the rest inline (edit this file) — agents must not guess these; until
answered, implement the **default** shown.

| #   | Question                                                                                                                   | Default until answered                                                             |
| --- | -------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------- |
| Q03 | Contact form: where should notifications go (email address), and do you want an auto-reply to the sender?                  | Store in DB + panel notification + email to profile email; no auto-reply           |
| Q04 | Hosting target? SSR needs a long-running Node process (VPS / Forge / Ploi / Laravel Cloud). Shared hosting cannot run SSR. | Assume VPS/Forge-style server                                                      |
| Q06 | Admin panel language: English UI OK?                                                                                       | English                                                                            |
| Q07 | Should the demo content from `Reference-Frontend` (Adam Rahman) be seeded, or start empty and you enter your own data?     | Provide `DemoContentSeeder` (opt-in), `DatabaseSeeder` seeds only admin + settings |
| Q08 | Downloadable CV (PDF) on the site?                                                                                         | Supported (Profile → resume upload), shown only if uploaded                        |
