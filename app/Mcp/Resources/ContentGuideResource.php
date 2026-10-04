<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('content_guide')]
#[Title('Portfolio content guide')]
#[Description('Where each project field appears on the site, recommended lengths and the writing style to use.')]
#[Uri('portfolio://guides/content')]
#[MimeType('text/markdown')]
class ContentGuideResource extends Resource
{
    public function handle(Request $request): Response
    {
        return Response::text(<<<'MARKDOWN'
# Portfolio content guide

The site is in **English** and written in the owner's voice ("I built…", "we shipped…"), plain and specific.
Prefer concrete nouns and verbs over adjectives; never invent numbers, clients or results.

## Where project fields appear

| Field | Shown | Length |
| --- | --- | --- |
| `title` | Cards, case study heading, page title | 1–4 words |
| `tagline` | Under the title on cards and the case study | One sentence, ≤ 120 characters |
| `summary` | Cards, archive, search results, meta description fallback | 2–3 sentences, ≤ 300 characters |
| `year`, `version`, `status`, `category` | Card metadata and archive filters | — |
| `role`, `team`, `timeline` | Case study fact sheet | Short phrases |
| `stack` | Tags on cards and the case study, most important first | 3–10 skills |
| `overview`, `problem` | Case study sections | 1–3 paragraphs each, 40–120 words per paragraph |
| `approach`, `features`, `challenges` | Case study sections as titled items | 2–5 items; title ≤ 6 words, description 1–3 sentences |
| `architecture` | Diagram on a grid (columns × rows ≤ 6) | 3–9 nodes; kinds: client, service, store, queue, external |
| `metrics` | Big numbers on the case study | 2–4 real figures (`value` "12 min", `label` "close time", `detail` "down from 9 hours") |
| `links` | Buttons on the case study | kinds: live, source, writeup, talk |
| cover | Cards, case study, social previews | 16:9, ≥ 1600 × 900 |
| gallery | Case study gallery | 4:3, 2–6 images, each with alt text and a short caption |
| `meta_title` / `meta_description` | Search engines (optional overrides) | ≤ 60 / ≤ 160 characters |

## Architecture diagram example

```json
{
  "caption": "Requests flow left to right; jobs run on the queue.",
  "columns": 3, "rows": 2,
  "nodes": [
    {"id": "web", "label": "Web app", "detail": "React · Inertia", "kind": "client", "column": 1, "row": 1},
    {"id": "api", "label": "API", "detail": "Laravel 11", "kind": "service", "column": 2, "row": 1},
    {"id": "db", "label": "Database", "detail": "PostgreSQL", "kind": "store", "column": 3, "row": 1},
    {"id": "jobs", "label": "Workers", "detail": "Horizon", "kind": "queue", "column": 2, "row": 2}
  ],
  "edges": [
    {"from": "web", "to": "api", "label": "HTTPS"},
    {"from": "api", "to": "db"},
    {"from": "api", "to": "jobs", "label": "events"}
  ]
}
```

## Publishing

New projects are drafts. Publish (`is_published: true`) only when the owner asks; `is_featured`
puts a project on the home page. Deleting moves a project to the trash (`restore_project` undoes it).
MARKDOWN);
    }
}
