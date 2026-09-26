# Phase 11 — Content tools (planned, owner requests 2026-09-26)

Two owner requests, planned for after the AI builder: an article editor you
can paste into, and an ATS-friendly CV generated from the panel's data.
Nothing here is built yet.

---

## P11-01 · Article editor you can paste into — `todo` (awaiting owner decision)

**Problem.** Articles are written with Filament's block Builder: every
paragraph, heading, list or code snippet is a separate block added by hand,
so copying an existing article in is impractical.

**Proposal (no third-party plugin needed).** Filament 5 ships a TipTap-based
`RichEditor`:

- [ ] Replace the Builder with `RichEditor::make('body')->json()`; toolbar:
      H2/H3, bold, italic, link, bullet/ordered list, blockquote, code block,
      horizontal rule, attach files. Pasting from a web page, Google Docs or
      Word keeps headings, lists, links and code.
- [ ] Images: attachments stored through Spatie Media Library (hard rule 2);
      check the media-library plugin's RichEditor attachment provider with
      Boost `search-docs` first.
- [ ] "Callout" as a `RichContentCustomBlock` (the one block type without a
      standard equivalent).
- [ ] `App\Support\Content\ArticleBody` converts TipTap JSON to the existing
      `ArticleBlock[]` (paragraph, heading, code, quote, list, callout,
      image), so **no template changes**; unknown nodes become paragraphs.
- [ ] Optional **Import Markdown** action on the article form: paste Markdown
      (league/commonmark ships with Laravel) → HTML → editor.
- [ ] Migration converting existing Builder bodies to TipTap JSON (tested
      on the demo articles), reversible.

**Acceptance**: an article pasted from a web page keeps its structure; all
templates render it unchanged; existing articles still render.

## P11-02 · CV data and three ATS-friendly CV templates — `todo`

- [ ] `App\Support\Cv\CvData` built from the panel: profile (name, role,
      summary, location, email, phone, links), experience (role, company,
      period, work mode, summary, achievements, stack), education (degree,
      field, institution, period, grade, details), skills by category,
      certifications, selected projects.
- [ ] Three Blade templates in `resources/views/cv/`: **Classic** (serif
      headings, single column), **Modern** (sans, accent colour rule),
      **Compact** (dense, fits two pages).
- [ ] ATS rules for all three: single column, standard section headings
      (Summary, Experience, Education, Skills, Certifications, Projects),
      real selectable text (no text in images, no tables or text boxes for
      layout, no icons carrying meaning), standard fonts, dates as
      `MMM YYYY`, contact details in the body (not a header/footer),
      A4 or Letter.

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
