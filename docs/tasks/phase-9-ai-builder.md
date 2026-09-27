# Phase 9 — AI template builder

Goal: **Generate with AI** on the Appearance page turns a prompt and/or a
reference image into a studio template, in the background, with visible
progress. Design: [12 §§ 5–9](../12-ai-templates.md). Decisions: D27, D28.

---

## P9-01 · Install and configure `laravel/ai` — `done`

- [x] `composer require laravel/ai` (`^1.0`, v1.0.0 installed). Nothing is
      published: the SDK merges its own `ai` config, and its only migration
      (`agent_conversations`) is for conversation memory, which the builder
      does not use. The API was checked against the installed source.
- [x] `.env.example` gets `STUDIO_AI_PROVIDER`, `STUDIO_AI_MODEL`, the
      optional limits and the provider key names (empty).
- [x] `config/studio.php` → `ai`: provider, model, daily limit, max output
      tokens, timeout, and the providers the panel offers.

## P9-02 · AI settings page (Site → AI) — `done`

- [x] Columns on `site_settings`: `ai_enabled` (default on), `ai_provider`,
      `ai_model`, `ai_api_key` (`encrypted`, `$hidden`), `ai_base_url`,
      `ai_daily_limit`.
- [x] `App\Support\Studio\AiSettings` resolves panel → `.env` → disabled,
      with the reason when it is not ready; `register()` writes the result
      into the SDK as the `studio` provider.
- [x] `App\Filament\Pages\AiSettingsPage` (Site → AI): status line,
      provider Select, model (placeholder = the SDK default), masked key
      that is never filled back, "Remove the saved key", base URL (Ollama /
      OpenAI-compatible only), daily limit, **Test connection**.

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

- 2026-09-27 — P9-01: `laravel/ai` v1.0.0. What the next tasks rely on,
  checked in the SDK source:
    - `Agent::prompt($prompt, attachments: [...], provider:, model:, timeout:)`;
      `provider` may be a config name, so the job registers a runtime provider
      `ai.providers.studio` (driver + panel key + URL) and calls
      `AiManager::forgetInstance('studio')` (instances are cached per name).
    - An empty model uses the provider's `defaultTextModel()`; only
      `openai-compatible` has no default (`'model' => true` in the config,
      so P9-02 must require a model for it).
    - Structured output: `HasStructuredOutput::schema(JsonSchema)`; the
      response is a `StructuredAgentResponse` (array access) with `usage`
      (`inputTokens`, `outputTokens`).
    - Images: `Laravel\Ai\Files\Image::fromStorage($path, $disk)` /
      `fromPath()`; fakes: `TemplateDesigner::fake([...])`,
      `assertPrompted()`, so tests need no network.
      Test: `tests/Feature/Studio/StudioAiConfigTest.php` (every provider the
      panel offers resolves to an SDK text provider).
- 2026-09-27 — P9-02: resolution rules — the panel provider wins; its model
  and URL come from the panel only (the `.env` model belongs to the `.env`
  provider); the key is the panel key, else that provider's `.env` key.
  "Enable AI features" off disables AI even when `.env` is set. Not ready
  when: no provider, no key (except Ollama / OpenAI-compatible), no base URL
  (those two), no model (OpenAI-compatible). An `.env` provider may be any
  SDK text provider in `config/ai.php` (e.g. Azure), the panel offers the
  list in `config/studio.php`. **Test connection** uses the form's unsaved
  values (an empty key field keeps the saved key) and does not save;
  provider errors are shown with the key replaced by `[key]`.
  `AiSettings::__debugInfo()` hides the key. Checked in Chromium: the page,
  required fields per provider, the "not ready" notice. Tests:
  `tests/Feature/Studio/AiSettingsTest.php` (18 cases, SDK fakes, no
  network).
