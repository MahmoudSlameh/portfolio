# 09 · Decisions & open questions

## Decision log

| # | Date | Decision | Why |
|---|------|----------|-----|
| D01 | 2026-09-24 | Laravel 13 + Filament 5 + Inertia 3 + React 19 with **SSR** | Owner's stack; SSR required for SEO |
| D02 | 2026-09-24 | **English only**; drop `Localized<T>`, locale toggle, RTL, Arabic dictionary | Owner: "main language is English, other languages don't matter" |
| D03 | 2026-09-24 | **All media via Spatie Media Library** + official `filament/spatie-laravel-media-library-plugin` (^5.8) | Owner requirement. Added to composer.json/lock (installed version v5.8.4, medialibrary 11.23) |
| D04 | 2026-09-24 | **All content (bio, images, career, …) editable in the panel** — nothing hard-coded | Owner requirement |
| D05 | 2026-09-24 | Singletons (`Profile`, `SiteSetting`, `NowPage`) are Eloquent single-row models, not `spatie/laravel-settings` | They need to own Spatie media; one consistent pattern |
| D06 | 2026-09-24 | Template chosen server-side; Inertia component name = `{template}/{Page}` | Code-splitting + SSR only loads the active template; no client registry |
| D07 | 2026-09-24 | Archive filtering on the server (Form Requests + Eloquent) instead of zod/TanStack search | Single source of truth, SSR-friendly, shareable URLs |
| D08 | 2026-09-24 | Experience "stack" and project "stack" are a polymorphic relation to `skills` | Reuse, consistent names, clickable tech filters |
| D09 | 2026-09-24 | Changelog-only fields (branch, version, commit, message) are auto-derived with optional overrides | Owner shouldn't have to invent git hashes; template keeps its flavour |
| D10 | 2026-09-24 | Countries stored as ISO alpha-2; names via `symfony/intl` | Clean data, searchable select, JSON-LD friendly |
| D11 | 2026-09-24 | API Resources (not `spatie/laravel-data`) + hand-maintained TS types, guarded by prop-shape tests | Fewer moving parts; can revisit |
| D12 | 2026-09-24 | Own sitemap/RSS/robots implementations (Blade views) | Tiny, no dependency on L13 support of third-party packages |
| D13 | 2026-09-24 | Template preview (`?template=`) allowed for admins always; for public only if `allow_public_preview` is on; preview pages are `noindex` | Visitors must see the active template; no duplicate-content SEO issues |

## Open questions for the owner

Answer inline (edit this file) — agents must not guess these; until
answered, implement the **default** shown.

| # | Question | Default until answered |
|---|----------|------------------------|
| Q01 | Should anyone be able to preview templates with `?template=…` (like the reference), or only you when logged in? | Only logged-in admin; toggle `allow_public_preview` exists |
| Q02 | Keep all secondary pages (Writing/blog, Books, Uses, Now)? | Keep all, each can be switched off in *Site → Settings* |
| Q03 | Contact form: where should notifications go (email address), and do you want an auto-reply to the sender? | Store in DB + panel notification + email to profile email; no auto-reply |
| Q04 | Hosting target? SSR needs a long-running Node process (VPS / Forge / Ploi / Laravel Cloud). Shared hosting cannot run SSR. | Assume VPS/Forge-style server |
| Q05 | Production database: MySQL or PostgreSQL? | MySQL 8 |
| Q06 | Admin panel language: English UI OK? | English |
| Q07 | Should the demo content from `Reference-Frontend` (Adam Rahman) be seeded, or start empty and you enter your own data? | Provide `DemoContentSeeder` (opt-in), `DatabaseSeeder` seeds only admin + settings |
| Q08 | Downloadable CV (PDF) on the site? | Supported (Profile → resume upload), shown only if uploaded |
