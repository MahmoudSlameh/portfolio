# Minimal — the starter template

A deliberately plain template: system fonts, one stylesheet, no animation.
It exists to be read and copied. Every file is commented.

| File                         | What it shows                                                                                |
| ---------------------------- | -------------------------------------------------------------------------------------------- |
| `template.json`              | The manifest the registry discovers (id = folder name, name, description, fonts, screenshot) |
| `styles.css`                 | Design tokens for light and dark, all scoped to `[data-template='minimal']`                  |
| `layout/MinimalLayout.tsx`   | Header, nav that hides disabled pages, theme toggle, command palette, footer                 |
| `components/ContactForm.tsx` | A contact form that is only markup on top of `useContactForm()`                              |
| `components/ArticleBody.tsx` | One renderer per article block type (`ArticleBlockRenderers`)                                |
| `components/Lists.tsx`       | Project and article lists with `Link` route patterns                                         |
| `pages/*.tsx`                | The nine pages of the template contract (`resources/js/templates/types.ts`)                  |

The Inertia entry points live in `resources/js/pages/minimal/*.tsx`. They are
thin wrappers (SEO head + page + persistent layout) and you rarely edit them.

## Rules

1. Render only the props you receive. Never fetch, never hard-code personal
   content; UI copy is fine.
2. Every list can be empty. Render nothing instead of an empty section.
3. Scope CSS under `[data-template='<id>']`.
4. Import behaviour from `@/kit`; keep templates about how things look.

Step-by-step guide: [docs/templates/building-a-template.md](../../../../docs/templates/building-a-template.md).
