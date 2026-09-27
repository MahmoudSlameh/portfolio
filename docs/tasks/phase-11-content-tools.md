# Phase 11 — Content tools (planned, owner requests 2026-09-26)

Two owner requests, planned for after the AI builder: an article editor you
can paste into, and an ATS-friendly CV generated from the panel's data.
Nothing here is built yet.

---

## P11-01 · Article editor you can paste into — `done`

**Problem.** Articles are written with Filament's block Builder: every
paragraph, heading, list or code snippet is a separate block added by hand,
so copying an existing article in is impractical. Also, the form promised
inline `**bold**`, `` `code` `` and links, but every template printed paragraph
text as plain text.

**Approved approach (2026-09-27, no third-party plugin).** Filament 5's
TipTap-based `RichEditor`, stored as TipTap JSON, converted on the server to
the existing `ArticleBlock[]` contract.

### Storage

- `articles.body` holds a TipTap document (`{"type":"doc","content":[…]}`)
  instead of the Builder list. The `ArticleBlock[]` sent to templates keeps
  its shape.
- `App\Support\Content\ArticleDocument`:
  `fromBuilder(list $blocks): array` (old format → document; used by the
  migration, `ArticleFactory` and `DemoContentSeeder`) and
  `toBuilder(array $doc): list` (for the migration's `down()`).

### Editor (`ArticleForm::body()`)

- [x] `RichEditor::make('body')->json()`. Toolbar: bold, italic, inline code,
      link · H2, H3 · blockquote, code block, bullet list, ordered list ·
      attach image, custom blocks · undo, redo. No tables, colours, alignment
      or H1 (the contract has no place for them).
- [x] Images: `SpatieMediaLibraryFileAttachmentProvider` on the
      `body_images` collection (`Article` implements `HasRichContent`), alt
      text asked on upload. Images can be added once the article is saved
      (Filament's rule for media-library attachments); images no longer in
      the body are removed on save, so the separate "Images" section goes.
- [x] Custom blocks: **Callout** (title, text) and **Code** (language,
      filename, code editor) for a snippet with a file name. A plain code
      block (pasted or typed) keeps the language from the pasted
      `language-*` class, else `text`.
- [x] **Import Markdown** action on the Body section: paste Markdown,
      choose _replace_ or _append_; `Str::markdown()` (raw HTML stripped,
      unsafe links dropped) → HTML → editor document.

### Conversion to `ArticleBlock[]` (`ArticleBody::toBlocks`)

| TipTap node                      | Block                                                                        |
| -------------------------------- | ---------------------------------------------------------------------------- |
| `paragraph`                      | `paragraph` (empty ones skipped)                                             |
| `heading` (any level)            | `heading`, plain text, unique slug id                                        |
| `bulletList` / `orderedList`     | `list`; each item's text; nested items become further items                  |
| `blockquote`                     | `quote`; a last paragraph starting with `—` or `--` becomes `cite`           |
| `codeBlock`                      | `code` (`language` attr or `text`)                                           |
| `customBlock` `code` / `callout` | `code` with filename / `callout`                                             |
| `image` (`id` = media uuid)      | `image` (alt from the node, caption from its `title`); missing media skipped |
| `table`                          | one paragraph per row, cells joined with `·`                                 |
| `horizontalRule`, `hardBreak`    | dropped / a space                                                            |
| anything else with content       | its children, converted the same way                                         |

Marks become the inline Markdown subset: bold `**x**`, italic `*x*`, code
`` `x` ``, link `[x](url)` (only `http(s)`, `mailto` and relative URLs; others
keep the text). Other marks keep their text. Headings are always plain.

### Templates (contract)

- [x] Kit `InlineText` renders that subset as React elements (no
      `dangerouslySetInnerHTML`), and `plainText()` strips it (search,
      aria labels). Used for paragraph, list item, quote and callout text in
      all templates (terminal, playground, changelog, minimal, studio);
      documented in `types.ts` and the template guide.
- [x] Word count / reading time from the converted blocks, markers stripped.

### Migration

- [x] Converts existing Builder bodies to documents (image block → image
      node with the media uuid, caption → `title`, code with filename →
      Code block, callout → Callout block); `down()` converts back.

**Done 2026-09-27.** `ArticleDocument` (converter both ways, inline Markdown
with escapes, safe links only), `CalloutBlock` / `CodeBlock`
(`App\Filament\RichContent`), `Article` implements `HasRichContent`, form
with Import Markdown, migration `2026_09_27_100000`, kit `InlineText` used by
terminal, playground, changelog, minimal and studio. Custom heading anchors
are not kept (ids come from the heading text). Checked in Chromium: pasting
HTML (h1, marks, link, list, quote, `language-php` code, table) saves as the
expected blocks; Import Markdown refreshes the open editor and keeps the
existing content; the Callout block inserts and saves; all four code
templates render bold, italic and the link. Tests:
`tests/Unit/ArticleDocumentTest.php`,
`tests/Feature/ArticleBodyMigrationTest.php`,
`tests/Feature/Filament/ArticleResourceTest.php`.

**Acceptance**: an article pasted from a web page keeps its headings, lists,
links, bold/italic, quotes and code; all templates render it; existing
articles render the same as before; tests cover the converter both ways, the
migration, the Markdown import and the form.

## P11-02 · CV data and three ATS-friendly CV templates — `done`

- [x] `App\Support\Cv\CvData::build(CvOptions)`: everything a CV shows, as
      plain data, read with the **same visibility and ordering as the
      site**: profile (name, role, summary — `summary`, else `headline`,
      location, email, phone, website = `APP_URL`, visible socials as plain
      `host/path` text), experience (newest first: role, organization,
      location or work mode, `MMM YYYY – MMM YYYY | Present`, summary,
      highlights, stack), education (current first, then newest: degree,
      field, institution, location, dates, grade, description,
      achievements), skills by category, certifications (newest first,
      expired ones left out), selected projects (featured + published:
      title, year, one line, stack, URL).
- [x] `CvOptions`: template, paper (`a4` | `letter`), include projects,
      max roles (older roles dropped), include certifications.
- [x] `App\Enums\CvTemplate` (Classic, Modern, Compact; label,
      description) and `App\Support\Cv\CvRenderer::html()`.
      `resources/views/cv/{classic,modern,compact}.blade.php` share
      `cv/partials/body.blade.php` (the sections, one order and heading set)
      and differ in styles: **Classic** serif, centred header, rules;
      **Modern** sans, accent-coloured headings and name; **Compact** sans,
      smaller type, skills inline, tighter spacing.
- [x] ATS rules for all three: single column; the section headings
      Summary, Experience, Education, Skills, Certifications, Projects;
      real text only (no images, icons or text boxes; no layout tables; no
      flex/grid either, so the pure-PHP PDF engine renders them); standard
      font stacks (DejaVu/Helvetica/Times); dates `MMM YYYY` on their own
      line after the role, so extraction keeps the reading order; contact
      details in the body; A4 or Letter; empty sections omitted.

## P11-03 · PDF generation — `todo`

- [ ] Choose the engine: `barryvdh/laravel-dompdf` (pure PHP, works on any
      host) or `spatie/laravel-pdf` (headless Chromium, better CSS). Default:
      dompdf, since text PDFs are what ATS need.
- [ ] `php artisan cv:generate --template=classic` for scripting/tests.

## P11-04 · Panel: CV page — `todo`

- [ ] **Profile → CV** page: pick a template (cards with thumbnails),
      options (paper size, include projects, max roles), **Generate PDF**
      (download).
- [ ] **Use as my resume** action: stores the PDF in the profile's `resume`
      media collection, so the site's "Download CV" link serves it.

## P11-05 · Tests & docs — `todo`

- [ ] Tests: each template renders with demo data and with an empty
      database; the PDF text (parsed in tests) contains the name, every
      section heading and every role, in reading order.
- [ ] Docs: a "CV" section in 04 (admin panel) and the README.

---

## Notes

- 2026-09-27 — P11-02: `CvData`, `CvOptions`, `CvRenderer`, `CvTemplate`,
  `resources/views/cv/{layout,classic,modern,compact}.blade.php` +
  `partials/body.blade.php`. The RSS and Email socials are left out of the
  contact links (the email is already listed). Checked in Chromium at A4
  with the demo content: all three read top to bottom in one column. Tests:
  `tests/Feature/Cv/CvTemplatesTest.php` (10 cases).
