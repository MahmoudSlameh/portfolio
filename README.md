<div align="center">

# Portfolio

**A self-hosted developer portfolio with a real admin panel, swappable
templates and templates you can generate with AI.**

Laravel 13 · Filament 5 · Inertia 3 · React 19 · Server-side rendering

[![tests](https://github.com/MahmoudSlameh/portfolio/actions/workflows/tests.yml/badge.svg)](https://github.com/MahmoudSlameh/portfolio/actions/workflows/tests.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777bb4.svg)
![Laravel 13](https://img.shields.io/badge/Laravel-13-ff2d20.svg)

[Features](#features) · [Quick start](#quick-start) · [Templates](#templates) ·
[AI template builder](#ai-template-builder) ·
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
  (with architecture diagrams, metrics and galleries), articles (rich
  editor: paste from any page, or import Markdown), books, skills, certifications, testimonials, socials, uses, now
  page.
- Inbox for contact messages, dashboard with content health.
- **Appearance**: activate a template, or preview it privately before
  switching; generate new templates with AI, or edit a template's spec by
  hand.
- **AI**: provider, model and API key for the template builder.
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

## AI template builder

Describe the design you want, or upload a screenshot you like, and get a new
template that renders your real content. It is generated in the background
and shows up on **Site → Appearance** when it is ready.

**1. Choose a provider.** Built on the official
[Laravel AI SDK](https://laravel.com/ai): Anthropic, OpenAI, Gemini, xAI,
Mistral, DeepSeek, Groq, OpenRouter, a local Ollama model or any
OpenAI-compatible endpoint. Pick the provider and model and paste your API
key in **Site → AI** (stored encrypted, never shown again), then press
**Test connection**. Or set it in `.env`; the panel wins when both are set:

```dotenv
STUDIO_AI_PROVIDER=anthropic
STUDIO_AI_MODEL=            # empty = the provider's default model
ANTHROPIC_API_KEY=
```

Use a model that accepts images if you want to generate from screenshots.

**2. Run a queue worker.** Generation is a queued job that can take a
minute or two (up to 5 minutes), so a worker must be running:
`composer dev` starts one locally; in production see
[docs/10-deployment.md](docs/10-deployment.md#5-long-running-processes-supervisor).

**3. Generate.** On **Site → Appearance**, click **Generate with AI**: give it
a name, describe the look, attach up to three reference images, and
optionally start from an existing template. The card shows the progress
(`Generating 70% — Checking the design…`) and you get a notification when it
is done. If the design does not pass validation, the AI gets the errors back
and fixes them (up to twice); if it still fails, the card shows why and
offers **Retry**.

**4. Preview, tweak, activate.** **Preview** it privately, adjust the spec
by hand with **Edit** if you like (every save is a new version), then
**Activate** it. A daily limit (20 by default, set in Site → AI) keeps the
bill predictable; each version shows the tokens it used.

**Safe by design:** the AI never writes code. It produces a _Template Spec_
(design tokens, layout and section choices, plus an optional stylesheet)
that is validated against a schema; the CSS is sanitised and scoped to the
template, and it may not load fonts, images or anything else from the
internet. One built-in React engine renders any spec, so generated templates
keep SSR and SEO, need no rebuild and survive deploys. Details:
[docs/12-ai-templates.md](docs/12-ai-templates.md).

**5. Keep iterating.** **Refine** asks the AI for changes and saves a new
version (the live template keeps showing the current one until you
activate the new version); **Versions** previews and rolls back any version;
**Export**/**Import** shares templates as `.studio.json` files
([guide](docs/templates/sharing-studio-templates.md)). With Chromium on the
server, cards show real screenshots; otherwise a colour swatch.

**Graduate to code.** `php artisan template:eject "Night Shift" night-shift`
turns a studio template into a regular code template: a copy of the studio
engine with the design frozen in `frozenSpec.ts`, ready to edit in TSX.

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

| Variable                                        | Purpose                                                      |
| ----------------------------------------------- | ------------------------------------------------------------ |
| `APP_URL`                                       | Canonical URLs, sitemap and Open Graph images use it         |
| `ADMIN_NAME` / `ADMIN_EMAIL` / `ADMIN_PASSWORD` | First admin user created by the seeder                       |
| `ADMIN_EMAILS`                                  | Comma-separated emails allowed into `/admin` in production   |
| `MEDIA_DISK`                                    | `public` or `s3` for uploaded images                         |
| `MAIL_*`                                        | Contact-form notifications                                   |
| `QUEUE_CONNECTION`                              | Queue for notifications, media conversions and AI generation |
| `STUDIO_AI_PROVIDER` / `STUDIO_AI_MODEL`        | AI template builder defaults (Site → AI overrides them)      |

Everything else (site name, SEO defaults, favicon, analytics, enabled pages,
active template) is edited in the panel.

## Build your own template

A template is a set of React components that receive the same props from
Laravel (the _template contract_) and decide how they look. Templates never
fetch data themselves.

Create one with a single command:

```bash
php artisan make:template magazine --name="Magazine"   # copies the commented `minimal` starter
php artisan make:template retro --from=terminal        # or start from any existing template
composer dev                                           # then preview /?template=magazine
```

While you work, open <http://localhost:8000/dev/templates> (local only) to
see every page of every template side by side, light or dark, desktop or
mobile.

It copies the template into `resources/js/templates/<id>/` with its nine
Inertia pages, renames the layout and the CSS scope, writes the
`template.json` manifest, adds a placeholder screenshot and imports the
stylesheet. The template is discovered automatically: no PHP change is
needed, and `composer ci:check` renders every page of it.

Then change how it looks. Behaviour comes from the template kit (`@/kit`):
`useContactForm`, `useArchiveFilters`, `useSiteSearch`, `useTheme`, a
headless `ArticleBlocks`, `ResponsiveImage` and more. The props of every
page are typed in
[`resources/js/templates/types.ts`](resources/js/templates/types.ts).

**Step-by-step guide:
[docs/templates/building-a-template.md](docs/templates/building-a-template.md)**
(design details in [docs/11-template-kit.md](docs/11-template-kit.md)).

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

| Phase | Status  | What                                                                                  |
| ----- | ------- | ------------------------------------------------------------------------------------- |
| P0–P5 | Done    | Data layer, admin panel, three templates, SEO & performance, tests, deployment guide  |
| P6    | Done    | Open-source release: this README, license, contributing guides                        |
| P7    | Done    | Template kit: registry, shared hooks, `make:template`, starter template, guide        |
| P8    | Done    | Studio engine: templates stored as a JSON spec and rendered by one React engine       |
| P9    | Done    | AI template builder with the Laravel AI SDK                                           |
| P10   | Done    | Refine & versions, export/import, card screenshots, eject to code                     |
| P11   | Started | Paste-friendly article editor (done); ATS-friendly CV generated as PDF from the panel |

## Contributing

Contributions are welcome, especially new templates. Read
[CONTRIBUTING.md](CONTRIBUTING.md) first, then pick an open task from the
[task board](docs/tasks/README.md). Please follow the
[Code of Conduct](CODE_OF_CONDUCT.md). To report a vulnerability, see
[SECURITY.md](SECURITY.md).

## License

Released under the [MIT License](LICENSE).
