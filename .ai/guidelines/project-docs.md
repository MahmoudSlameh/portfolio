# Project docs (read first)

This is a Laravel 13 + Filament 5 + Inertia 3 (React 19, SSR) personal
portfolio. **All design, data-model and workflow documentation lives in
[`docs/`](docs/README.md)** and the step-by-step plan with live status is in
[`docs/tasks/README.md`](docs/tasks/README.md).

Before doing any work:

1. Read `docs/README.md` (hard rules: English only, all content managed from
   Filament, all media through Spatie Media Library, SSR/SEO first).
2. Pick the next task from `docs/tasks/README.md` and follow its acceptance
   criteria.
3. Keep docs and task statuses updated in the same commit as the code.

The three public templates (changelog, playground, terminal) were ported from
a reference design that was removed after the port (see docs/09-decisions.md, D24);
keep their look — extend them, don't redesign them.
