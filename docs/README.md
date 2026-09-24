# Portfolio — Project Documentation

> **AI agents / new contributors: start here.** Read this file, then
> [`tasks/README.md`](tasks/README.md) to find the next open task. Every task
> links back to the doc section it implements.

## ملخص سريع (Arabic summary)

مشروع Portfolio شخصي مبني على **Laravel 13 + Filament 5 (لوحة التحكم) + Inertia 3 + React 19 (الواجهة) مع SSR لدعم الـ SEO**.
الواجهة الأمامية فيها 3 قوالب (`changelog` · `playground` · `terminal`) منقولة من مجلد `Reference-Frontend/` (انحذف بعد النقل، آخر commit فيه `eb06a9a`).
من لوحة التحكم بتختار القالب الفعّال، وبتدير **كل** المحتوى: البروفايل والـ Bio، الخبرات، الدراسة، الشركات والعملاء، المشاريع، المقالات، الكتب، المهارات… وكل الصور عن طريق **Spatie Media Library**.
اللغة الوحيدة هي **الإنكليزية**.

## Documentation map

| #   | Document                                         | What it answers                                                                                   |
| --- | ------------------------------------------------ | ------------------------------------------------------------------------------------------------- |
| 01  | [Project overview](01-project-overview.md)       | Goals, scope, stack, what exists today                                                            |
| 02  | [Architecture](02-architecture.md)               | How Laravel, Filament, Inertia, SSR and templates fit together; folder layout; request lifecycle  |
| 03  | [Data model](03-data-model.md)                   | Every table, column, enum and relation, and how each maps to the frontend TypeScript types        |
| 04  | [Admin panel](04-admin-panel.md)                 | Filament panel design: navigation, every resource's form/table/filters/actions                    |
| 05  | [Media](05-media.md)                             | Spatie Media Library: collections, conversions, alt text, how images reach React                  |
| 06  | [Frontend & templates](06-frontend-templates.md) | Template system, porting from `Reference-Frontend`, template contract, preview                    |
| 07  | [SEO](07-seo.md)                                 | SSR, meta tags, JSON-LD, sitemap, RSS, performance budget                                         |
| 08  | [Conventions](08-conventions.md)                 | Code style, commands, testing, git workflow, definition of done                                   |
| 09  | [Decisions & open questions](09-decisions.md)    | Architecture decision log + questions still waiting for the owner                                 |
| 10  | [Deployment & launch](10-deployment.md)          | Server requirements, env, build steps, Supervisor (SSR + queue), Nginx, backups, launch checklist |
| —   | [Tasks](tasks/README.md)                         | The phased task plan and live status board                                                        |

## Hard rules (non-negotiable)

1. **All content is managed from the Filament panel.** No hard-coded bio,
   career, project, image or link data in React. UI micro-copy (button labels,
   section kickers) may stay in template code.
2. **All media goes through Spatie Media Library** via the official
   `filament/spatie-laravel-media-library-plugin`
   (`SpatieMediaLibraryFileUpload` / `SpatieMediaLibraryImageColumn`). Never
   store image paths in plain string columns.
3. **English only.** No locale switching, no RTL, no `Localized<T>` objects —
   the `en`/`ar` structures of the reference design were flattened to plain
   values when ported.
4. **SEO first.** Every public page is server-side rendered (Inertia SSR) and
   ships its title, description, canonical, Open Graph and JSON-LD in the
   initial HTML.
5. **The active template is chosen in the panel** (`Site → Appearance`).
   Visitors always see the active template; `?template=<id>` is a preview
   mechanism (see [06](06-frontend-templates.md#preview)).
6. **Keep templates swappable.** All three templates receive the same props
   (the _template contract_). Templates never fetch data themselves.

## Quick start (local)

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed          # admin user + settings
php artisan db:seed --class=DemoContentSeeder   # optional demo portfolio
php artisan storage:link
npm install
composer dev                        # serve + queue + vite
# SSR (production-like):
npm run build:ssr && php artisan inertia:start-ssr
```

Admin panel: `http://localhost:8000/admin` (create a user with
`php artisan make:filament-user`).
