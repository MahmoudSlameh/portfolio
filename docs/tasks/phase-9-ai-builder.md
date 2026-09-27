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

## P9-03 · `TemplateDesigner` agent — `done`

- [x] `App\Ai\Agents\TemplateDesigner` (`Agent`, `HasStructuredOutput`,
      `Promptable`); `maxTokens()` and `timeout()` from `config/studio.php`;
      `#[CacheInstructions]` so repair turns reuse the cached instructions
      (Anthropic).
- [x] **Output** (D31): `{ "spec": string, "summary": string }`. `spec` is
      the Template Spec as a JSON document **in a string**, not typed
      fields: a typed schema would need a free-form `props` object and
      per-section variant rules, which strict structured-output modes
      (OpenAI) reject and others ignore. `SpecValidator` is the real check;
      its errors drive the repair turns (P9-04).
- [x] **Instructions** generated from `SpecCatalogue` (never hand-written,
      so they cannot drift): the output contract; the spec shape (top-level
      keys, colour roles for light and dark, fonts per role from
      `config/studio.php`, token values, header/footer/container, home
      sections with variants and props, page variants, copy keys with
      lengths, CSS class hooks); rules (no personal content, no fonts or
      URLs outside the list, `url()` only for small `data:` images, AA
      contrast in both themes, 6–12 home sections, CSS optional and small);
      how to use reference images (palette, type, layout; never their text);
      one complete example spec.
- [x] `App\Ai\TemplateBrief` builds the user message: name, the owner's
      prompt, the starting point (a studio spec as JSON, or a built-in
      template's name and description), and how many reference images are
      attached. `TemplateDesigner::repairPrompt(errors, cssRemoved)` for
      repair turns. `TemplateDesigner::specFrom($response)` decodes the
      `spec` string (tolerating a code fence) or throws
      `InvalidSpecException` with a readable reason.
- [x] Tests with the SDK fakes: instructions mention every section,
      variant, font, class hook and copy key; the brief with and without a
      starting point; `specFrom()` for valid, fenced and broken output; a
      faked prompt with an image attachment returns a valid spec.

## P9-04 · `GenerateStudioTemplate` job + repair loop — `done`

- [x] `studio_generations` table + `StudioGeneration` model: one row per
      attempt (template, status, provider, model, turns, input/output
      tokens, error). Failed attempts cost tokens too, so the **daily
      limit** counts these rows, not versions.
- [x] `App\Support\Studio\StudioGenerator::start()` (template, prompt,
      starting point): refuses (`GenerationRefused`, readable message) when AI
      is not ready or today's limit is reached; otherwise records the
      attempt, sets the template to `queued` ("Waiting for a worker…"),
      `source = ai`, and dispatches the job. `remainingToday()` for the UI.
- [x] `App\Jobs\GenerateStudioTemplate` (1 try, 300 s, `failOnTimeout`):
      5 % Starting → 15 % Reading your references (the template's
      `reference` media → `Image::fromStorage`) → 30 % Designing →
      70 % Checking → up to **2 repair turns** (80 %, 90 %; the conversation
      so far + `TemplateDesigner::repairPrompt()`) → 95 % Saving →
      `addVersion()` with prompt, provider, model and summed tokens →
      `activate()` → `ready` 100 %. The summary becomes the description
      when there is none.
- [x] Failure (exception, still invalid after 2 repairs, AI not ready,
      timeout via `failed()`): template `failed` with a readable error
      (key redacted, ≤ 500 characters), attempt `failed`, reported.
- [x] Filament database notification to the panel users on success and on
      failure.

**Acceptance**: tests with the SDK's fakes for success, repair-then-success
and failure; no network in tests.

## P9-05 · Appearance: Generate with AI + live status — `done`

- [x] Header action **Generate with AI** (hidden when AI is switched off in
      Site → AI; disabled with the reason as tooltip when AI is not ready).
      Modal: name, prompt (required unless an image is given), up to 3
      reference images (≤ 5 MB each, private `local` disk → the template's
      `reference` media), **start from** (none, a built-in template or a
      ready studio template), and "N of M generations left today". Calls
      `StudioGenerator::start()`; a refusal is shown as a notification and
      nothing is created. The planned "pages" field is dropped: every spec
      must define every page, so the prompt says what matters.
- [x] Cards: `Queued` / `Generating n% — step` / `Ready` / `Failed` (with
      the error and **Retry**, which reuses the last attempt's prompt,
      starting point and images). Edit is hidden while a template is
      queued or generating. AI versions show their token usage.
- [x] `wire:poll.3s` on the studio section **only while** a template is
      queued or generating.

**Acceptance**: Livewire tests for the modal, polling and retry.

## P9-06 · Docs & README — `done`

- [x] README "AI template builder" section switched from "in development" to
      usage instructions (provider, worker, generate, preview/edit/activate,
      safety, what comes next); features, configuration and roadmap rows
      updated.
- [x] 04 (Appearance and Site → AI) and 10 (deployment: AI env, queue worker
      required, two worker processes, `retry_after` above the 300 s job,
      launch checklist, troubleshooting) updated.

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
- 2026-09-27 — P9-03: `App\Ai\Agents\TemplateDesigner` + `App\Ai\TemplateBrief`.
  The instructions (~9 KB, generated by `TemplateDesigner::guide()`) also
  list the theme's CSS variables (`--paper`, `--ink`, `--signal`, …; the
  names `StudioStyles` really writes, checked by a test) so CSS can reuse
  the palette. `specFrom()` also accepts an object (some providers return
  one despite the string schema). Broken JSON is left to
  `SpecValidator::validateJson()` (" is not valid JSON."), so the repair
  loop handles it like any other error. Tests:
  `tests/Feature/Studio/TemplateDesignerTest.php` (12 cases; SDK fake with
  an image attachment, no network).
- 2026-09-27 — P9-04: `StudioGeneration`, `StudioGenerator`,
  `GenerationRefused`, `GenerateStudioTemplate`. The saved spec's `name` is
  always the template name (the model's is ignored). Broken JSON goes
  through the repair loop like a validation error. CSS the sanitiser
  removes does not trigger a repair: the version is saved with the notes
  (cheaper; the owner sees them on Edit). Notifications are Filament
  database notifications (queued, so they need the worker too) with a
  Preview link, or "Open Appearance" on failure. Tests:
  `tests/Feature/Studio/GenerateStudioTemplateTest.php` (14 cases, SDK
  fakes, no network). Not run against a real provider (no key in this
  environment).
- 2026-09-27 — P9-05: `Appearance` gains **Generate with AI** and **Retry**;
  the studio section polls every 3 s only while a card is queued or
  generating (checked in Chromium: a card moved from 30 % to 80 % with its
  new step without a reload). A refused start deletes the just-created
  template, so nothing half-made is left. Uploads go to the private
  `local` disk (`studio-uploads/`) and are moved into the template's
  `reference` media. Tests: `tests/Feature/Studio/AppearanceGenerateTest.php`
  (9 cases).
- 2026-09-27 — P9-06: writing the deployment notes surfaced two real
  timeout problems, both fixed: the queues' `retry_after` defaulted to 90 s,
  shorter than the 300 s generation job (a running job would be handed to a
  second worker and fail as "attempted too many times") → default 360 s in
  `config/queue.php`, guarded by `tests/Feature/Studio/QueueTimeoutTest.php`;
  and `composer dev` ran `queue:listen` with its default 60 s process
  timeout, which would kill local generations → `--timeout=320`.
