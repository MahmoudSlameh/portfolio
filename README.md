<div align="center">

# Portfolio

**A self-hosted developer portfolio with a real admin panel, swappable
templates and, coming soon, templates you can generate with AI.**

Laravel 13 · Filament 5 · Inertia 3 · React 19 · Server-side rendering

[![tests](https://github.com/MahmoudSlameh/portfolio/actions/workflows/tests.yml/badge.svg)](https://github.com/MahmoudSlameh/portfolio/actions/workflows/tests.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777bb4.svg)
![Laravel 13](https://img.shields.io/badge/Laravel-13-ff2d20.svg)

[Features](#features) · [Quick start](#quick-start) · [Templates](#templates) ·
[AI template builder](#ai-template-builder-in-development) ·
[Build your own template](#build-your-own-template) · [Docs](docs/README.md)

</div>

---

## Why

Most portfolio starters are either a static site you edit in code or a
page builder that is slow and bad for SEO. This project is both a
**CMS** and a **fast, SEO-first site**:

- You manage **everything** (bio, experience, projects, articles, books,
  images) from a polished admin panel. Nothing personal is hard-coded.
- Visitors get **server-rendered HTML** with full meta tags, JSON-LD,
  a sitemap and RSS.
- You switch the **whole design** with one click, and preview any design
  privately before it goes live.

## Features

**Public site**

- Pages: Home, project archive, case studies, writing archive, articles,
  bookshelf, `/uses`, `/now` and a custom 404. Writing, Books, Uses and Now
  can be switched off.
- Three complete templates (Changelog, Playground, Terminal), all rendering
  the same content.
- SSR with Inertia: title, description, canonical URL, Open Graph, Twitter
  cards and JSON-LD (`Person`, `WebSite`, `BlogPosting`, `CreativeWork`, breadcrumbs, …) in the
  first HTML response.
- `sitemap.xml`, `robots.txt` and `rss.xml`.
- Command palette search (<kbd>Ctrl</kbd>/<kbd>⌘</kbd> + <kbd>K</kbd>),
  light/dark theme with no flash, archive filters in the URL.
- Contact form that saves to the panel inbox and emails you.
- Self-hosted fonts, responsive WebP images and code-split templates.

**Admin panel** (`/admin`, Filament 5)

- Profile & bio, work experience, education, companies & clients, projects
  (with architecture diagrams, metrics and galleries), articles (block
  editor), books, skills, certifications, testimonials, socials, uses, now
  page.
- Inbox for contact messages, dashboard with content health.
- **Appearance**: activate a template, or preview it privately before
  switching.
- SEO & site settings: site name, meta defaults, favicon, OG image,
  verification codes, analytics snippet, page toggles.
- All media through Spatie Media Library (responsive conversions, alt text).

## Templates

| Changelog                                                         | Playground                                                    | Terminal                                                         |
| ----------------------------------------------------------------- | ------------------------------------------------------------- | ---------------------------------------------------------------- |
| ![Changelog template](public/templates/changelog.webp)            | ![Playground template](public/templates/playground.webp)      | ![Terminal template](public/templates/terminal.webp)             |
| Editorial "engineer's changelog" with a git-graph career timeline | Playful bento layout with stickers, career tickets and a dock | Dark developer-terminal look with mono type and a project slider |

There is also **Minimal**, a plain, fully commented starter template to copy
when you build your own (`resources/js/templates/minimal`).

Visitors always see the template activated in **Site → Appearance**. While
you are signed in to the panel, `/?template=<id>` previews another template
just for you (preview pages are `noindex`). `/?template=reset` ends the
preview.

## AI template builder (in development)

> 🚧 **Not available yet.** This feature is designed and planned (phases
> P8–P10 in the [task board](docs/tasks/README.md)). The full design is in
> [docs/12-ai-templates.md](docs/12-ai-templates.md). Contributions are
> welcome.

The goal: go to **Site → Appearance → Generate with AI**, describe the
design you want or upload a screenshot, and get a new template that renders
your real content.

How it will work:

1. **Bring your own AI provider.** Built on the official
   [Laravel AI SDK](https://laravel.com/ai), so you can use Anthropic, OpenAI,
   Gemini, Groq, xAI, DeepSeek, Mistral, OpenRouter or a local Ollama model.
   Choose the provider and model and paste your API key in **Site → AI**
   (stored encrypted), or set them in `.env`:

    ```dotenv
    STUDIO_AI_PROVIDER=anthropic
    STUDIO_AI_MODEL=
    ANTHROPIC_API_KEY=
    ```

2. **Describe it.** Write a prompt, attach up to three reference images, or
   start from an existing template.
3. **Watch it build.** Generation runs on the queue. The Appearance page
   shows the template as `Queued`, then `Generating 45% — Composing pages…`,
   then `Ready`.
4. **Preview, refine, activate.** Preview it privately, ask for changes ("make
   it darker, use a serif headline") to get a new version, roll back to any
   version, then activate it.
5. **Share it.** Export a template as a JSON file and import templates other
   people made.

**Safe by design:** the AI never writes code. It produces a _Template Spec_
(design tokens, layout and section choices, plus a sanitised stylesheet
scoped to the template) that is validated and stored in the database. One
built-in React engine renders any spec, so generated templates keep SSR and
SEO, need no rebuild and survive deploys. Developers can later _eject_ a
generated template into regular React code.

## Tech stack

| Layer    | Tools                                                                             |
| -------- | --------------------------------------------------------------------------------- |
| Backend  | PHP 8.4, Laravel 13, Filament 5, Spatie Media Library, Laravel Wayfinder          |
| Frontend | React 19 (React Compiler), Inertia 3 with SSR, Tailwind CSS 4, Motion, Vite+      |
| Data     | SQLite for local development and CI, MySQL 8 in production                        |
| Quality  | Pest 5, Larastan (level 7), Pint, TypeScript, Vite+ lint & format, GitHub Actions |

## Quick start

**Requirements:** PHP 8.4+ (with `gd` or `imagick` built with WebP, `intl`,
`exif`), Composer 2, Node.js 22.

```bash
git clone https://github.com/MahmoudSlameh/portfolio.git
cd portfolio

composer setup      # composer install, .env, app key, migrations, npm install, build
php artisan db:seed # admin user + default settings
php artisan storage:link

# Optional: a complete demo portfolio to explore the panel and templates
php artisan db:seed --class=DemoContentSeeder

composer dev        # Laravel server + queue worker + Vite
```

- Site: <http://localhost:8000>
- Admin panel: <http://localhost:8000/admin>

The seeder creates the admin user from `ADMIN_NAME`, `ADMIN_EMAIL` and
`ADMIN_PASSWORD` in `.env`. Locally, the defaults are `admin@example.com`
with the password `password`. You can also run
`php artisan make:filament-user`.

To run it with SSR like production:

```bash
npm run build:ssr
php artisan inertia:start-ssr
```

## Configuration

The important `.env` values (see [`.env.example`](.env.example)):

| Variable                                        | Purpose                                                                 |
| ----------------------------------------------- | ----------------------------------------------------------------------- |
| `APP_URL`                                       | Canonical URLs, sitemap and Open Graph images use it                    |
| `ADMIN_NAME` / `ADMIN_EMAIL` / `ADMIN_PASSWORD` | First admin user created by the seeder                                  |
| `ADMIN_EMAILS`                                  | Comma-separated emails allowed into `/admin` in production              |
| `MEDIA_DISK`                                    | `public` or `s3` for uploaded images                                    |
| `MAIL_*`                                        | Contact-form notifications                                              |
| `QUEUE_CONNECTION`                              | Queue for notifications and media conversions (and AI generation later) |

Everything else (site name, SEO defaults, favicon, analytics, enabled pages,
active template) is edited in the panel.

## Build your own template

A template is a set of React components that receive the same props from
Laravel (the _template contract_) and decide how they look. Templates never
fetch data themselves.

Today, adding a template means:

1. Create `resources/js/templates/<id>/` with a layout and the nine pages
   (Home, ProjectArchive, CaseStudy, WritingArchive, Article, Books, Uses,
   Now, NotFound). The props for each page are typed in
   [`resources/js/templates/types.ts`](resources/js/templates/types.ts).
2. Add the thin Inertia wrappers in `resources/js/pages/<id>/`. Copy an
   existing template's wrappers.
3. Add a `template.json` manifest next to it (id, name, description,
   fonts to preload, screenshot) and `public/templates/<id>.webp`. The
   template is discovered automatically; no PHP change is needed.
4. Run `composer ci:check`. The test suite renders every page of every
   template automatically.

Behaviour comes from the template kit (`@/kit`): `useContactForm`,
`useArchiveFilters`, `useSiteSearch`, `useTheme`, a headless
`ArticleBlocks`, `ResponsiveImage` and more, so a template only decides how
things look. See [docs/11-template-kit.md](docs/11-template-kit.md). A
`php artisan make:template <id>` scaffold and a commented `minimal` starter
template are next on the roadmap (phase P7).

## Testing & code quality

```bash
composer test       # Pint (check), Larastan, Pest
npm run check       # lint + format (Vite+)
npm run types:check # TypeScript
composer ci:check   # everything CI runs
```

## Deployment

SSR needs a long-running Node process, so use a VPS or a platform like
Laravel Forge, Ploi or Laravel Cloud (shared hosting will not work). The
step-by-step guide covers server requirements, environment, build steps,
Supervisor programs for the SSR server and the queue worker, Nginx, backups
and a launch checklist: [docs/10-deployment.md](docs/10-deployment.md).

## Documentation

The [`docs/`](docs/README.md) folder is the full design documentation:
architecture, data model, admin panel, media, templates, SEO, conventions,
the decision log and the phased [task board](docs/tasks/README.md) with
live status.

## Roadmap

| Phase | Status  | What                                                                                 |
| ----- | ------- | ------------------------------------------------------------------------------------ |
| P0–P5 | Done    | Data layer, admin panel, three templates, SEO & performance, tests, deployment guide |
| P6    | Done    | Open-source release: this README, license, contributing guides                       |
| P7    | Planned | Template kit: registry, shared hooks, `make:template`, starter template, guide       |
| P8    | Planned | Studio engine: templates stored as a JSON spec and rendered by one React engine      |
| P9    | Planned | AI template builder with the Laravel AI SDK                                          |
| P10   | Planned | Refine & versions, export/import, eject to code                                      |

## Contributing

Contributions are welcome, especially new templates. Read
[CONTRIBUTING.md](CONTRIBUTING.md) first, then pick an open task from the
[task board](docs/tasks/README.md). Please follow the
[Code of Conduct](CODE_OF_CONDUCT.md). To report a vulnerability, see
[SECURITY.md](SECURITY.md).

## License

Released under the [MIT License](LICENSE).
