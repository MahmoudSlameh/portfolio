# Tasks — plan & status board

## How to work on this project (for AI agents and humans)

1. Read [`../README.md`](../README.md) (hard rules) and skim the doc linked by
   the task.
2. Pick the **first task whose status is `todo` and whose dependencies are
   `done`** (top-to-bottom order in the board below is the intended order).
3. Set its status to `in-progress` in both this board and the phase file.
4. Implement it. Follow [`../08-conventions.md`](../08-conventions.md).
   Use Laravel Boost MCP tools (`search-docs`, `tinker`, `database-schema`,
   `list-routes`) to check Filament 5 / Inertia 3 / Laravel 13 APIs — do not
   rely on memory of older versions.
5. Verify every acceptance criterion; run `composer ci:check`.
6. Tick the checkboxes, set status `done`, add a one-line note (date + commit
   / anything the next person must know) under **Notes** in the phase file.
7. If you discover something that changes the design, update the relevant doc
   and add a row to [`../09-decisions.md`](../09-decisions.md).
8. If a task is blocked by an owner decision (see _Open questions_ in 09),
   implement the documented default, or mark it `blocked` with the reason.

Statuses: `todo` · `in-progress` · `blocked` · `done`.

## Phases

| Phase | File                                                     | Outcome                                           |
| ----- | -------------------------------------------------------- | ------------------------------------------------- |
| P0    | [phase-0-foundation.md](phase-0-foundation.md)           | Project installs, packages & tooling ready        |
| P1    | [phase-1-data-layer.md](phase-1-data-layer.md)           | Enums, migrations, models, factories, seeders     |
| P2    | [phase-2-admin-panel.md](phase-2-admin-panel.md)         | Complete Filament panel                           |
| P3    | [phase-3-frontend.md](phase-3-frontend.md)               | Three templates running on Inertia with real data |
| P4    | [phase-4-seo-performance.md](phase-4-seo-performance.md) | SSR, meta, JSON-LD, sitemap, RSS, perf            |
| P5    | [phase-5-quality-launch.md](phase-5-quality-launch.md)   | Tests hardening, cleanup, deployment              |
| P6    | [phase-6-open-source.md](phase-6-open-source.md)         | README, license, community files, English docs    |
| P7    | [phase-7-template-kit.md](phase-7-template-kit.md)       | Template registry, kit, scaffold command, guide   |
| P8    | [phase-8-studio-engine.md](phase-8-studio-engine.md)     | Spec-driven studio templates stored in the DB     |
| P9    | [phase-9-ai-builder.md](phase-9-ai-builder.md)           | Generate templates with AI (Laravel AI SDK)       |
| P10   | [phase-10-studio-extras.md](phase-10-studio-extras.md)   | Refine, versions, export/import, eject to code    |

> **Status (2026-09-26): P0–P6 are done. P7–P10 (template kit and the AI
> template builder) are planned** — design in
> [11](../11-template-kit.md) and [12](../12-ai-templates.md).
>
> Status 2026-09-24: all phases P0–P5 were done. The owner still has to
> decide the open questions in [09](../09-decisions.md#open-questions-for-the-owner) (their
> defaults are implemented) and run the post-launch checks in
> [10 § 8](../10-deployment.md#8-launch-checklist). Add new work as a new phase file
> and new rows below.

> P2 and P3 can run in parallel once P1 is done (different agents), as long as
> P3 starts with P3-01…P3-04.

## Status board

| ID     | Task                                                               | Depends on          | Status |
| ------ | ------------------------------------------------------------------ | ------------------- | ------ |
| P0-01  | Install dependencies & verify baseline                             | —                   | done   |
| P0-02  | Laravel Boost + AI guidelines                                      | P0-01               | done   |
| P0-03  | Spatie Media Library setup (package already required)              | P0-01               | done   |
| P0-04  | Extra packages (symfony/intl) & config file `config/portfolio.php` | P0-01               | done   |
| P1-01  | Enums                                                              | P0-04               | done   |
| P1-02  | Singletons: Profile, SiteSetting, NowPage                          | P1-01, P0-03        | done   |
| P1-03  | Companies, Experiences, Education, Certifications, Testimonials    | P1-01, P0-03        | done   |
| P1-04  | Skills, categories, skillables                                     | P1-01               | done   |
| P1-05  | Projects + gallery items                                           | P1-03, P1-04        | done   |
| P1-06  | Articles, Books, Uses, Socials, ContactMessages, NowPage books     | P1-05               | done   |
| P1-07  | Derived-data support classes                                       | P1-06               | done   |
| P1-08  | API Resources + `ImageData`                                        | P1-07               | done   |
| P1-09  | Seeders (admin, settings, DemoContentSeeder)                       | P1-08               | done   |
| P2-01  | Panel configuration & shared building blocks                       | P1-02               | done   |
| P2-02  | Profile & Bio page                                                 | P2-01               | done   |
| P2-03  | Work experience resource                                           | P2-01, P1-03        | done   |
| P2-04  | Education resource                                                 | P2-01, P1-03        | done   |
| P2-05  | Companies & clients resource                                       | P2-01, P1-03        | done   |
| P2-06  | Certifications & testimonials                                      | P2-05               | done   |
| P2-07  | Skills & categories                                                | P2-01, P1-04        | done   |
| P2-08  | Projects resource                                                  | P2-05, P2-07, P1-05 | done   |
| P2-09  | Articles resource                                                  | P2-08, P1-06        | done   |
| P2-10  | Books, Uses, Socials, Now page                                     | P2-01, P1-06        | done   |
| P2-11  | Inbox (contact messages)                                           | P2-01, P1-06        | done   |
| P2-12  | Appearance (templates) & Site settings pages                       | P2-01, P1-02        | done   |
| P2-13  | Dashboard widgets                                                  | P2-03…P2-11         | done   |
| P3-01  | Frontend deps, folder structure, shared code port                  | P0-01               | done   |
| P3-02  | TemplateManager, shared props, Blade shell                         | P1-02, P3-01        | done   |
| P3-03  | Routes & controllers (all public pages)                            | P1-08, P3-02        | done   |
| P3-04  | Inertia app/SSR entry, page resolver, `SeoHead` stub               | P3-02               | done   |
| P3-05  | Port **Terminal** template                                         | P3-03, P3-04        | done   |
| P3-06  | Port **Changelog** template                                        | P3-03, P3-04        | done   |
| P3-07  | Port **Playground** template                                       | P3-03, P3-04        | done   |
| P3-08  | Contact form end-to-end                                            | P3-05, P2-11        | done   |
| P3-09  | Command palette & search index                                     | P3-05               | done   |
| P3-10  | Preview bar & template switching                                   | P3-05…P3-07, P2-12  | done   |
| P4-01  | SSR production-ready                                               | P3-07               | done   |
| P4-02  | SeoData + SeoHead + per-page meta                                  | P4-01               | done   |
| P4-03  | JSON-LD                                                            | P4-02               | done   |
| P4-04  | sitemap.xml, robots.txt, rss.xml                                   | P4-02               | done   |
| P4-05  | Performance pass                                                   | P4-02               | done   |
| P5-01  | Test hardening & CI                                                | P4-*                | done   |
| P5-02  | Content health & admin polish                                      | P2-*                | done   |
| P5-03  | Remove `Reference-Frontend/`                                       | P3-*, P5-01         | done   |
| P5-04  | Deployment guide & launch checklist                                | P4-*                | done   |
| P6-01  | Root `README.md` (English)                                         | —                   | done   |
| P6-02  | License & community files                                          | —                   | done   |
| P6-03  | English-only docs & project metadata                               | P6-01               | done   |
| P7-01  | Template registry replaces the `Template` enum                     | P6-*                | done   |
| P7-02  | Kit: shared hooks & components                                     | P7-01               | done   |
| P7-03  | `minimal` starter template                                         | P7-02               | done   |
| P7-04  | `php artisan make:template`                                        | P7-03               | todo   |
| P7-05  | `/dev/templates` gallery (local only)                              | P7-01               | todo   |
| P7-06  | Guide: building a template                                         | P7-04               | todo   |
| P8-01  | Spec schema `studio/v1` + validator                                | P7-01               | todo   |
| P8-02  | CSS sanitiser                                                      | —                   | todo   |
| P8-03  | Studio models, migrations, enums                                   | P8-01               | todo   |
| P8-04  | `studio` React engine + section library                            | P7-02, P8-01…P8-03  | todo   |
| P8-05  | Appearance: studio templates without AI                            | P8-04               | todo   |
| P9-01  | Install and configure `laravel/ai`                                 | P8-*                | todo   |
| P9-02  | AI settings page (Site → AI)                                       | P9-01               | todo   |
| P9-03  | `TemplateDesigner` agent                                           | P9-01               | todo   |
| P9-04  | `GenerateStudioTemplate` job + repair loop                         | P9-02, P9-03        | todo   |
| P9-05  | Appearance: Generate with AI + live status                         | P9-04               | todo   |
| P9-06  | Docs & README for the AI builder                                   | P9-05               | todo   |
| P10-01 | Refine & versions                                                  | P9-*                | todo   |
| P10-02 | Export / import JSON                                               | P8-05               | todo   |
| P10-03 | Card screenshots                                                   | P8-05               | todo   |
| P10-04 | `php artisan template:eject`                                       | P8-04, P7-04        | todo   |
