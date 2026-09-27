# Sharing studio templates

Studio templates (the ones made on **Site → Appearance**, by hand or with AI)
can be exported as a small JSON file and imported into any other copy of this
portfolio. The file only describes the design; your content never leaves your
site.

## Export

- On **Appearance**, a studio card's **Export** downloads its active version
  as `<name>.studio.json`.
- On a template's **Versions** page, **Export** on a row downloads that
  version (`<name>-v<n>.studio.json` when it is not the active one).

## Import

On **Appearance**, click **Import** and either upload a `.studio.json` file
(up to 100 KB) or paste its contents. The name is taken from the file unless
you type one.

The import goes through the same checks as AI output:

1. The spec is validated against `studio/v1`. If anything is wrong, nothing
   is created and you see the problems (for example
   `tokens.radius must be one of: none, sm, md, lg, full.`).
2. The CSS is sanitised and scoped to the template. Anything unsafe (external
   `url()`, `@import`, `@font-face`, `expression()`, escapes…) is removed, and
   a warning lists what was dropped.
3. The template is saved as a new studio template (`Imported`), with version 1.
   It is **never activated automatically**: use **Preview**, then
   **Activate**.

## The file format

```json
{
    "format": "portfolio-studio-template",
    "formatVersion": 1,
    "name": "Night Shift",
    "description": "Dark, neon accents, mono headings.",
    "exportedAt": "2026-09-27T12:00:00+00:00",
    "spec": { "$schema": "studio/v1", "name": "Night Shift", "tokens": { … }, "layout": { … }, "pages": { … } }
}
```

| Key             | Notes                                                                                       |
| --------------- | ------------------------------------------------------------------------------------------- |
| `format`        | Always `portfolio-studio-template`                                                          |
| `formatVersion` | `1`. A file with a newer version is refused with a message instead of being half-understood |
| `name`          | Used as the template name when you import (≤ 60 characters)                                 |
| `description`   | Optional; HTML is stripped                                                                  |
| `exportedAt`    | Informational                                                                               |
| `spec`          | The Template Spec, see [12 § 2](../12-ai-templates.md#2-the-template-spec)                  |

A bare spec (an object with `"$schema": "studio/v1"` at the top) is accepted
too, so you can paste the JSON from the **Edit** slide-over of another site.

**Not included:** your content (it always comes from your own panel),
reference images and screenshots, the prompt that generated the design, the
provider and model, token usage and older versions.

## Editing a file by hand

The spec is plain JSON, so you can tweak a file in any editor before
importing it: change colours in `tokens.colors`, reorder `pages.home`, swap a
variant. The fonts, sections, variants and CSS classes you can use are listed
in [12 § 2](../12-ai-templates.md#2-the-template-spec), and the machine-readable
schema is [`resources/studio/schema/v1.json`](../../resources/studio/schema/v1.json)
(keep `"$schema": "studio/v1"` in the spec; editors that support JSON Schema
can be pointed at that file in their settings). The **Edit**
action on a studio card does the same inside the panel, with validation as
you save.

## Safety

Importing a file cannot run code: a spec is data, rendered by the built-in
studio engine. The only free-form part, the CSS, is sanitised with an
allow-list and scoped to `[data-template="studio"]`, and it cannot load
anything from the internet. Text in `copy` is plain text, length-limited and
escaped by React.
