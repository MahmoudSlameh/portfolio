# Phase 9 — AI template builder

Goal: **Generate with AI** on the Appearance page turns a prompt and/or a
reference image into a studio template, in the background, with visible
progress. Design: [12 §§ 5–9](../12-ai-templates.md). Decisions: D27, D28.

---

## P9-01 · Install and configure `laravel/ai` — `todo`

- [ ] `composer require laravel/ai`, publish config/migrations; verify the
      API against the installed version with Boost `search-docs`.
- [ ] `.env.example` gets `STUDIO_AI_PROVIDER`, `STUDIO_AI_MODEL` and the
      provider key names (empty).

## P9-02 · AI settings page (Site → AI) — `todo`

- [ ] Columns on `site_settings`: `ai_enabled`, `ai_provider`, `ai_model`,
      `ai_api_key` (`encrypted`, hidden), `ai_base_url`, `ai_daily_limit`.
- [ ] `App\Support\Studio\AiSettings` resolves panel → `.env` → disabled.
- [ ] Filament page with masked key field and a **Test connection** action.

**Acceptance**: key never appears in HTML, logs or Inertia props (test);
resolution order tested.

## P9-03 · `TemplateDesigner` agent — `todo`

- [ ] `App\Ai\Agents\TemplateDesigner` (`Agent`, `HasStructuredOutput`);
      instructions generated from the schema, section catalogue, class hooks
      and fonts list; image attachments; optional starting spec.

## P9-04 · `GenerateStudioTemplate` job + repair loop — `todo`

- [ ] Status/progress/current_step updates, validation + sanitising, up to 2
      repair turns, version #1 saved, failure stored, database notification.
- [ ] Daily limit and usage (tokens) recorded.

**Acceptance**: tests with the SDK's fakes for success, repair-then-success
and failure; no network in tests.

## P9-05 · Appearance: Generate with AI + live status — `todo`

- [ ] Modal (name, prompt, up to 3 reference images, start from, pages).
- [ ] Card badges `Queued` / `Generating n% — step` / `Ready` / `Failed`
      (+ Retry); `wire:poll.3s` only while something is in progress.

**Acceptance**: Livewire tests for the modal, polling and retry.

## P9-06 · Docs & README — `todo`

- [ ] README "AI template builder" section switched from "in development" to
      usage instructions; 04 (admin panel) and 10 (deployment: queue worker
      is required, timeouts) updated.

---

## Notes
