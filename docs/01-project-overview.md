# 01 · Project overview

## Goal

A personal developer portfolio that:

- is fully **content-managed** from a professional Filament 5 admin panel;
- renders a public site in **one of several visual templates**, chosen from the
  panel (one active at a time);
- is **SEO-complete**: SSR HTML, rich meta, structured data, sitemap, RSS, fast
  Core Web Vitals.

## Scope

### Public site (React + Inertia, SSR)

| Route                                     | Page            | Content                                                                                                                                                 |
| ----------------------------------------- | --------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `/`                                       | Home            | Hero/bio, stats, skills, career timeline, featured projects, clients, testimonials, education & certifications, latest writing, bookshelf, contact form |
| `/projects`                               | Project archive | Filter by search / technology / category, sort, grid/table view                                                                                         |
| `/projects/{slug}`                        | Case study      | Overview, problem, approach, architecture diagram, features, challenges, metrics, gallery, links, prev/next                                             |
| `/writing`                                | Writing archive | Search + tag filter                                                                                                                                     |
| `/writing/{slug}`                         | Article         | Block-based body, TOC, reading progress, related projects                                                                                               |
| `/books`                                  | Library         | Reading status/category filters, stats, per-year chart                                                                                                  |
| `/uses`                                   | Uses            | Hardware / software / development tool groups                                                                                                           |
| `/now`                                    | Now             | Current focus, learning, reading                                                                                                                        |
| `POST /contact`                           | —               | Contact form submission (stored + notified)                                                                                                             |
| `/sitemap.xml`, `/robots.txt`, `/rss.xml` | —               | SEO endpoints                                                                                                                                           |

### Admin panel (Filament 5, `/admin`)

Management for **everything** the templates display — see
[04-admin-panel.md](04-admin-panel.md). Highlights requested by the owner:

- **Work experience**: company, work mode (remote / on-site / hybrid), country,
  optional address, job title, short description, achievements, start date and
  optional end date ("currently working here").
- **Education**: qualification name (e.g. _Diploma in Software Engineering_),
  institution, start / optional end ("currently studying"), field of study,
  grade, achievements.
- **Clients / companies**: name, logo, website link.
- **Profile / bio**, portrait and all other images.
- **Active template** selection.

## Tech stack (as installed)

| Layer        | Package                                                                    | Version                     |
| ------------ | -------------------------------------------------------------------------- | --------------------------- |
| Runtime      | PHP                                                                        | ^8.3 (8.4 in dev container) |
| Framework    | laravel/framework                                                          | 13.x                        |
| Admin        | filament/filament                                                          | 5.8.x (Livewire 4)          |
| Media        | filament/spatie-laravel-media-library-plugin → spatie/laravel-medialibrary | 5.8.x → 11.x                |
| Bridge       | inertiajs/inertia-laravel + @inertiajs/react                               | 3.x                         |
| Routes in TS | laravel/wayfinder + @laravel/vite-plugin-wayfinder                         | 0.1.x                       |
| UI           | React 19 + React Compiler, Tailwind CSS 4                                  |                             |
| Build        | Vite 8 via `vite-plus` (`vp`)                                              |                             |
| Tests        | Pest 5, Larastan (level 7), Pint                                           |                             |
| AI tooling   | laravel/boost (dev)                                                        | 2.x                         |

## What exists today (starting point)

- A fresh **Laravel React starter kit** (blank): `routes/web.php` renders a
  single `welcome` Inertia page; `config/inertia.php` already has SSR enabled.
- A default **Filament admin panel** (`app/Providers/Filament/AdminPanelProvider.php`)
  with no resources.
- `Reference-Frontend/` — a standalone Vite + TanStack Router SPA containing the
  **three templates** and all mock data (`src/data/*.ts`) and types
  (`src/types/content.ts`). This is the **design source of truth**; it is
  ported into `resources/js` and the data model is derived from it. It is
  deleted at the end of the project (task P5).
- `filament/spatie-laravel-media-library-plugin` has been added to
  `composer.json` / `composer.lock` (run `composer install` to fetch it).

## Out of scope

- Multi-language content (English only).
- Multiple authors / public user accounts (single admin owner).
- Comments on articles.
