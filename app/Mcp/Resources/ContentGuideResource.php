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
#[Description('Where each project and article field appears on the site, recommended lengths, the article Markdown and the writing style to use.')]
#[Uri('portfolio://guides/content')]
#[MimeType('text/markdown')]
class ContentGuideResource extends Resource
{
    public function handle(Request $request): Response
    {
        return Response::text(<<<'MARKDOWN'
# Portfolio content guide

The site is in **English** and written in the owner's voice ("I built…", "we shipped…"), plain and specific.
Articles are the exception: each is written in its own language (`language`: `en` or `ar`).
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

## Where article fields appear

| Field | Shown | Length |
| --- | --- | --- |
| `title` | Cards, article heading, page title | ≤ 80 characters |
| `excerpt` | Cards, search results, meta description fallback | 1–2 sentences, ≤ 300 characters |
| `language` | Sets `lang` and the direction of the article (`ar` = right to left) | — |
| `slug` | `/writing/<slug>` | Latin letters, numbers, dashes; always English, also for Arabic articles |
| `series`, `series_order` | Above the title: "System Design · 01" | Same series name on every part |
| `tags` | Chips and the archive filter | 2–5 |
| `video_url` | Player under the cover | YouTube or Vimeo link |
| `body` | The article | Markdown, see below |
| `sources` | "Sources" list at the end | Every fact that needs one; `{title, url}` |
| cover | Cards, article, social previews | 16:9, ≥ 1600 × 900 |

Reading time is calculated from the body.

### Article Markdown

~~~markdown
Opening paragraph: what the reader will learn.

## A section heading

Text with **bold**, *italic*, `code` and [a link](https://example.com).

1. Numbered step
2. Another step
   - a nested point

> A quote.
> — Who said it

> [!NOTE] Callout title
> The callout text.

| Option | When to use it |
| --- | --- |
| Index | Reads outnumber writes |

```php title="app/Models/User.php"
class User extends Model {}
```

![Step 1: the request reaches the load balancer](image:step-1 "Step 1")
~~~

Images: `image:<key>` is a placeholder filled later by `add_article_image` (or `request_image_upload`
with target `article_image`) with `placeholder: "<key>"`; `media:<uuid>` is an image already on the
article (as `get_article` returns it); an https URL is downloaded when the body is saved. Images
removed from the body are deleted. Raw HTML is shown as text. Lists or quotes inside a callout
become plain callout text.

## Images

- **Local files** (screenshots, mockups): call `request_image_upload` with the target (`cover`,
  `gallery`, `company_logo`, `article_cover` or `article_image`), the file's `mime_type` and alt text, then run the returned curl
  command: `curl -X PUT --data-binary @file.png -H "Content-Type: image/png" <upload_url>`. The URL
  works once and expires after a few minutes; the response says where the image was attached.
- **Images already online**: `image_url` on `set_project_cover`, `add_project_image`, `set_company_logo`,
  `set_article_cover` or `add_article_image`.
- **Live sites**: `screenshot_url`, when `capabilities.screenshots` is true in the overview.
- `image_base64` only for tiny images (under ~200 KB).

## Publishing

New projects and articles are drafts. Publish (`is_published: true`) only when the owner asks; a future
`published_at` schedules an article. `is_featured` puts a project on the home page. Deleting moves a
project or article to the trash (`restore_project` / `restore_article` undo it).
MARKDOWN);
    }
}
