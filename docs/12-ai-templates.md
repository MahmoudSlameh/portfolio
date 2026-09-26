# 12 · Studio templates & the AI template builder

> Status: **planned** (phases [P8](tasks/phase-8-studio-engine.md),
> [P9](tasks/phase-9-ai-builder.md), [P10](tasks/phase-10-studio-extras.md)).
> Decisions: D25–D29 in [09](09-decisions.md).

The owner opens **Site → Appearance → Generate with AI**, describes a design
(a prompt, a reference screenshot or both), and a few minutes later a new
template appears next to the built-in ones, ready to preview and activate.
It renders the same content from the panel, through the same controllers,
with SSR and SEO intact.

## 1. Storage decision (D25): a spec in the database, not generated code

Code templates are TSX compiled by Vite at deploy time, and SSR runs a
pre-built bundle. We compared three options:

| Option                            | How                                                                  | Verdict                                                                                                                                                                                                                   |
| --------------------------------- | -------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| A. Generate TSX files             | The AI writes React code into `resources/js/templates/<id>`          | ✗ Needs `npm run build` on the server per template (the site breaks while the build output is replaced), runs AI-written code inside the SSR Node process (remote code execution risk), files are lost on the next deploy |
| B. Spec (JSON) in the database    | The AI writes a **Template Spec**; one React engine renders any spec | ✓ No build, SSR/SEO intact, safe, instant. Freedom is limited to the section library                                                                                                                                      |
| **C. Spec + scoped CSS** (chosen) | Like B, plus a sanitised stylesheet scoped to the template           | ✓ All of B, with much more visual freedom and still **no JavaScript** from the AI                                                                                                                                         |

Consequences:

- A generated template is **data**: a row in `studio_templates` with
  versions in `studio_template_versions`. It survives deploys, is backed up
  with the database and can be exported/imported as a JSON file.
- The same engine renders hand-written specs, so the feature is useful even
  without an AI provider (write or import a spec).
- Developers who want full control can **eject** a studio template into a
  real code template (`php artisan template:eject`, P10) and continue in TSX.

## 2. The Template Spec

A versioned JSON document (`"$schema": "studio/v1"`) validated on the server
(JSON Schema in `resources/studio/schema/v1.json` + a PHP validator) and typed
on the client (`resources/js/templates/studio/spec.ts`).

```json
{
    "$schema": "studio/v1",
    "name": "Neon Brutalist",
    "tokens": {
        "colors": {
            "light": {
                "bg": "#f6f5f0",
                "surface": "#ffffff",
                "text": "#111111",
                "muted": "#5b5b5b",
                "accent": "#ff3d7f",
                "border": "#111111"
            },
            "dark": {
                "bg": "#0c0c0f",
                "surface": "#16161c",
                "text": "#f2f2f2",
                "muted": "#9a9aa5",
                "accent": "#ff3d7f",
                "border": "#f2f2f2"
            }
        },
        "fonts": {
            "display": "space-grotesk",
            "body": "inter",
            "mono": "jetbrains-mono"
        },
        "radius": "none",
        "density": "comfortable",
        "shadow": "hard",
        "motion": "subtle"
    },
    "layout": {
        "header": { "variant": "bar-sticky" },
        "footer": { "variant": "columns" },
        "container": "wide"
    },
    "pages": {
        "home": [
            {
                "section": "hero",
                "variant": "split-portrait",
                "props": { "showAvailability": true }
            },
            { "section": "stats", "variant": "inline" },
            {
                "section": "projects",
                "variant": "bento",
                "props": { "limit": 6 }
            },
            { "section": "career", "variant": "timeline" },
            { "section": "clients", "variant": "marquee" },
            { "section": "testimonials", "variant": "carousel" },
            {
                "section": "writing",
                "variant": "list",
                "props": { "limit": 3 }
            },
            { "section": "contact", "variant": "card" }
        ],
        "projects": { "variant": "grid" },
        "caseStudy": { "variant": "longform" },
        "writing": { "variant": "list" },
        "article": { "variant": "centered" },
        "books": { "variant": "shelf" },
        "uses": { "variant": "columns" },
        "now": { "variant": "notes" },
        "notFound": { "variant": "big-number" }
    },
    "copy": {
        "heroKicker": "Available for select projects",
        "contactHeading": "Let's build something loud"
    },
    "css": "[data-template=\"studio\"] .st-hero h1 { letter-spacing: -0.04em; text-transform: uppercase; }"
}
```

Rules:

- **Content is never in the spec.** Names, bios, projects and images always
  come from the controllers' props (hard rule 1). `copy` is limited to UI
  micro-copy keys defined by the section library, each with a max length.
- **Fonts** are chosen from a fixed allow-list of Fontsource families that
  ship with the app (hard rule / D20: no third-party requests). The list
  lives in `config/studio.php` and is sent to the AI.
- **Unknown sections, variants or props are rejected** by validation (they
  are not silently ignored), so the AI gets precise errors to repair.
- Every home section is optional; sections whose data is empty render
  nothing (same rule as the code templates).

## 3. The `studio` engine (P8)

A fourth code template, `resources/js/templates/studio/`, that renders a
spec. It is a normal Inertia template: `resources/js/pages/studio/*.tsx`
wrappers, the same page props, plus a shared `studio` prop with the active
spec version.

- **Tokens → CSS variables.** The server turns `tokens` into a `<style>`
  block of custom properties (`--st-bg`, `--st-accent`, `--st-radius`, …) for
  light and dark, emitted in `app.blade.php` so SSR paints the right colours
  with no flash.
- **Section library.** Each section is a React component with a small set of
  variants, built on the [template kit](11-template-kit.md) hooks:

    | Page   | Sections / variants (initial set)                                                                                                                                                                                                                                                                                                                                                                 |
    | ------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
    | Layout | header: `bar-sticky`, `floating-pill`, `sidebar`, `minimal` · footer: `minimal`, `columns`, `big-name`                                                                                                                                                                                                                                                                                            |
    | Home   | `hero` (`centered`, `split-portrait`, `editorial`, `terminal`), `about`, `stats`, `skills` (`chips`, `grid`, `bars`), `career` (`timeline`, `cards`, `table`), `projects` (`grid`, `bento`, `list`, `slider`), `clients` (`logos`, `marquee`), `testimonials` (`carousel`, `wall`), `education`, `writing` (`list`, `cards`), `books` (`shelf`, `covers`), `contact` (`card`, `split`, `minimal`) |
    | Other  | one to three variants each for project archive, case study, writing archive, article, books, uses, now, 404                                                                                                                                                                                                                                                                                       |

- **Scoped CSS.** `css` is sanitised on save (see §6) and injected after the
  engine's base styles, scoped under `[data-template="studio"][data-studio="<ulid>"]`.
- **Stable class hooks.** Every section exposes documented class names
  (`.st-hero`, `.st-hero__title`, `.st-card`, …) so the AI's CSS targets a
  known surface. The list is generated into the AI instructions.

## 4. Data model (P8/P9)

`studio_templates`

| Column              | Type                 | Notes                                                   |
| ------------------- | -------------------- | ------------------------------------------------------- |
| `id`                | ulid                 | Template id is `studio:<id>`                            |
| `name`              | string               |                                                         |
| `description`       | string, nullable     |                                                         |
| `source`            | enum `StudioSource`  | `ai` · `manual` · `import`                              |
| `status`            | enum `StudioStatus`  | `draft` · `queued` · `in_progress` · `ready` · `failed` |
| `progress`          | tinyint 0–100        | Shown on the Appearance card                            |
| `current_step`      | string, nullable     | e.g. "Composing pages…"                                 |
| `error`             | text, nullable       | Last failure, shown to the owner                        |
| `active_version_id` | foreign id, nullable | Version rendered on the site                            |
| timestamps          |                      |                                                         |

`studio_template_versions`

| Column                          | Type                   | Notes                                        |
| ------------------------------- | ---------------------- | -------------------------------------------- |
| `id`                            | id                     |                                              |
| `studio_template_id`            | foreign id             |                                              |
| `number`                        | unsigned int           | 1, 2, 3 … per template                       |
| `spec`                          | json                   | Validated Template Spec (no DB default, D15) |
| `prompt`                        | text, nullable         | Prompt (v1) or refine instruction (v2+)      |
| `parent_id`                     | foreign id, nullable   | Version this one refined                     |
| `provider`, `model`             | string, nullable       | What generated it                            |
| `input_tokens`, `output_tokens` | unsigned int, nullable | Usage, for the cost display                  |
| timestamps                      |                        |                                              |

Media (Spatie, hard rule 2): `StudioTemplate` collections `reference`
(uploaded screenshots/mockups, private disk) and `screenshot` (card image).

## 5. AI generation pipeline (P9)

Built on the official **Laravel AI SDK** (`laravel/ai`, D27): agents,
structured output, image attachments, queueing and per-request
provider/model selection across Anthropic, OpenAI, Gemini, Groq, xAI,
DeepSeek, Mistral, OpenRouter and Ollama.

```
Appearance ─ "Generate with AI" modal
   │  name · prompt · reference images (0–3) · start from (optional template) · pages
   ▼
StudioTemplate (status=queued) + GenerateStudioTemplate job (queue)
   ▼  in_progress 10%  "Reading your references…"
TemplateDesigner agent  (instructions = spec schema + section catalogue + class hooks + font list)
   │  structured output → Template Spec
   ▼  in_progress 70%  "Validating…"
SpecValidator + CssSanitizer
   │  invalid? → send the errors back to the agent (repair turn), max 2 retries
   ▼
StudioTemplateVersion #1 → active_version_id → status=ready 100%
   (any exception → status=failed, error saved, owner notified)
```

- **Agent**: `App\Ai\Agents\TemplateDesigner` implements `Agent` and
  `HasStructuredOutput`; its schema mirrors `studio/v1`. Reference images are
  passed as attachments. When "start from" is set, the current spec (or a
  description of the code template) is included as a starting point.
- **Job**: `App\Jobs\GenerateStudioTemplate` (timeout 300 s, 1 try, the
  repair loop happens inside the job). It updates `progress`/`current_step`
  between steps. Requires the queue worker that production already runs
  ([10](10-deployment.md)).
- **Refine** (P10): "Make it darker / use a serif display font" creates
  version _n+1_ from version _n_ plus the instruction; older versions stay
  available (activate or roll back any version).
- **Notifications**: a Filament database notification when a generation
  finishes or fails.

## 6. Safety

- **No executable output.** The AI produces JSON; JSON is validated against
  the schema; nothing is ever `eval`-ed, compiled or written to disk.
- **CSS sanitiser** (`App\Support\Studio\CssSanitizer`, allow-list based):
  parses the stylesheet, drops `@import`, `@font-face`, `@charset`,
  `url(...)` (except `data:` SVG/PNG under 20 KB), `expression()`,
  `behavior`, `-moz-binding`, `</style`, HTML comments, and any selector not
  prefixed by the template scope (the scope is prepended when missing). Size
  limit 40 KB.
- **Copy** strings are plain text, length-limited, escaped by React.
- **Preview first.** A new or refined template is never activated
  automatically; the owner previews it (`?template=studio:<ulid>`, admin
  only, `noindex`, D13) and activates it.
- **Cost guard.** A daily generation limit (default 20) and a per-request
  max output tokens setting; usage is stored per version and shown on the card.
- **Secrets.** API keys are stored with Laravel's `encrypted` cast, never
  sent to the browser (masked field in the panel), never logged.

## 7. AI settings (P9)

New singleton page **Site → AI** (backed by new columns on `site_settings`,
D28):

| Field                    | Notes                                                                                     |
| ------------------------ | ----------------------------------------------------------------------------------------- |
| Enable AI features       | Hides the "Generate with AI" button when off                                              |
| Provider                 | Select from the SDK's supported text providers                                            |
| Model                    | Free text with suggestions per provider (e.g. the provider's latest vision-capable model) |
| API key                  | `encrypted`; leave empty to use the `.env` key                                            |
| Base URL                 | Only for Ollama / OpenAI-compatible endpoints                                             |
| Daily generation limit   | Default 20                                                                                |
| Test connection (action) | Sends a tiny prompt and reports success, latency and model                                |

Resolution order for every request: **panel settings → `.env` → disabled**.
The `.env` path makes it work for people who prefer config files:

```dotenv
STUDIO_AI_PROVIDER=anthropic       # any provider supported by laravel/ai
STUDIO_AI_MODEL=                   # the provider's model id
ANTHROPIC_API_KEY=                 # provider keys use laravel/ai's own env names
OPENAI_API_KEY=
GEMINI_API_KEY=
```

At runtime the key from the panel is applied to the SDK's provider config for
that request only (`config(['ai.providers.<name>.key' => …])` inside the job),
then the agent is prompted with `provider:` and `model:`.

## 8. The Appearance page (P9)

- Built-in templates first, then studio templates, as the same cards.
- Card badges: `Draft` · `Queued` · `Generating 45% — Composing pages…` ·
  `Ready` · `Failed` (with the error and a Retry action) · `Active`.
- The page polls (`wire:poll.3s`) **only while** a template is queued or in
  progress.
- Actions on a ready studio template: **Preview**, **Activate**, **Refine**
  (P10), **Versions** (P10), **Duplicate**, **Export JSON** (P10),
  **Delete** (disabled while active).
- Header actions: **Generate with AI** (hidden when AI is disabled, with a
  hint linking to Site → AI), **New blank studio template**, **Import JSON**
  (P10).

## 9. Testing

- Spec validator and CSS sanitiser: unit tests with valid, invalid and
  malicious fixtures.
- The job: the SDK's agent fakes return canned specs (valid, invalid-then-
  valid, always invalid) to cover the repair loop and the failed state; no
  network in tests.
- The template matrix (`tests/Feature/...` datasets) includes a studio
  template built from a fixture spec, so every public page is rendered
  through the engine in CI.
- Appearance page: Livewire tests for the generate modal, polling state and
  activation of a studio template.
