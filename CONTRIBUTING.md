# Contributing

Thanks for helping! This guide covers setup, the workflow and what a good
pull request looks like.

## Ways to contribute

- **Templates**: new public-site designs are the most wanted contribution.
  Open a _Template proposal_ issue first so we can agree on the idea.
- **Tasks from the board**: [`docs/tasks/README.md`](docs/tasks/README.md)
  lists every planned task with its status, dependencies and acceptance
  criteria. Pick the first `todo` task whose dependencies are `done`.
- **Bugs and ideas**: open an issue with the matching template.

## Setup

Follow the [Quick start](README.md#quick-start), then seed the demo content
so every page has data:

```bash
php artisan db:seed --class=DemoContentSeeder
composer dev
```

## Before you write code

1. Read the hard rules in [`docs/README.md`](docs/README.md#hard-rules-non-negotiable).
   In short: all content comes from the panel, all media goes through Spatie
   Media Library, English only, every public page is SSR, templates receive
   the same props and never fetch data.
2. Read the doc linked from your task and
   [`docs/08-conventions.md`](docs/08-conventions.md) (code style, testing,
   definition of done).
3. If your change alters a design decision, add a row to
   [`docs/09-decisions.md`](docs/09-decisions.md).

## Workflow

1. Fork the repository and create a branch from `main`
   (`feat/…`, `fix/…`, `docs/…`).
2. Keep the change focused. Update docs and the task status in the same
   pull request as the code.
3. Run everything CI runs:

    ```bash
    composer ci:check
    ```

    It runs the JS lint/format check, TypeScript, Pint, Larastan and the Pest
    suite. Fix formatting with `composer lint` and `npm run check:fix`.

4. Write commit messages in the imperative mood ("Add book filters", not
   "Added book filters").
5. Open a pull request and fill in the template. Include screenshots (light
   and dark, desktop and mobile) for any visual change.

## Adding a template

Until the template kit ([phase P7](docs/tasks/phase-7-template-kit.md))
ships, follow [Build your own template](README.md#build-your-own-template)
and [`docs/06-frontend-templates.md`](docs/06-frontend-templates.md). A
template pull request needs:

- all nine pages, working with empty content and with the demo content;
- CSS scoped to `[data-template="<id>"]` and fonts from Fontsource packages
  (no third-party requests);
- a `public/templates/<id>.webp` screenshot (960 × 600);
- a green `composer ci:check`.

## Code of Conduct

This project follows the [Code of Conduct](CODE_OF_CONDUCT.md). By taking
part you agree to uphold it.
