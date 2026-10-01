import type { ReactNode } from 'react';

/**
 * Article text (paragraph, list item, quote and callout text) may contain a small inline
 * Markdown subset: `**bold**`, `*italic*`, `` `code` `` and `[text](url)`, with `\*`, `\``,
 * `\[`, `\]` and `\\` for literal characters. It is written by the server
 * (App\Support\Content\ArticleDocument) and this parser mirrors it. Headings are plain text.
 */
const INLINE =
    /\\([\\`*[\]])|`([^`]+)`|\*\*(.+?)\*\*|\*([^*\s](?:[^*]*[^*\s])?)\*|\[([^\]]+)\]\(((?:[^()\s]|\([^()\s]*\))+)\)/gsu;

/** Only web, mail, relative and in-page links; anything else renders as text. */
const SAFE_URL = /^(https?:\/\/|mailto:|\/|#)/i;

export interface InlineTextClassNames {
    code?: string;
    link?: string;
    strong?: string;
    em?: string;
}

type Visitor<T> = {
    text: (value: string) => T;
    code: (value: string) => T;
    strong: (children: T[]) => T;
    em: (children: T[]) => T;
    link: (href: string | null, children: T[]) => T;
};

function walk<T>(source: string, visit: Visitor<T>): T[] {
    const out: T[] = [];
    let offset = 0;

    for (const match of source.matchAll(INLINE)) {
        const start = match.index ?? 0;

        if (start > offset) out.push(visit.text(source.slice(offset, start)));

        const [, escaped, code, strong, em, label, href] = match;

        if (escaped !== undefined) out.push(visit.text(escaped));
        else if (code !== undefined) out.push(visit.code(code));
        else if (strong !== undefined)
            out.push(visit.strong(walk(strong, visit)));
        else if (em !== undefined) out.push(visit.em(walk(em, visit)));
        else
            out.push(
                visit.link(
                    href !== undefined && SAFE_URL.test(href) ? href : null,
                    walk(label ?? '', visit),
                ),
            );

        offset = start + match[0].length;
    }

    if (offset < source.length) out.push(visit.text(source.slice(offset)));

    return out;
}

/** The text without its markers (search, aria labels, meta descriptions). */
export function plainText(source: string): string {
    return walk<string>(source, {
        text: (value) => value,
        code: (value) => value,
        strong: (children) => children.join(''),
        em: (children) => children.join(''),
        link: (_href, children) => children.join(''),
    }).join('');
}

/**
 * Renders article text with its inline formatting as React elements (never as HTML).
 * External links open in a new tab.
 */
export function InlineText({
    text,
    classNames = {},
}: {
    text: string;
    classNames?: InlineTextClassNames;
}) {
    let key = 0;
    const next = () => (key += 1);

    const nodes = walk<ReactNode>(text, {
        text: (value) => value,
        code: (value) => (
            <code key={next()} className={classNames.code}>
                {value}
            </code>
        ),
        strong: (children) => (
            <strong key={next()} className={classNames.strong}>
                {children}
            </strong>
        ),
        em: (children) => (
            <em key={next()} className={classNames.em}>
                {children}
            </em>
        ),
        link: (href, children) => {
            if (href === null) return <span key={next()}>{children}</span>;

            const external = /^https?:\/\//i.test(href);

            return (
                <a
                    key={next()}
                    href={href}
                    className={classNames.link}
                    {...(external
                        ? { target: '_blank', rel: 'noopener noreferrer' }
                        : {})}
                >
                    {children}
                </a>
            );
        },
    });

    return <>{nodes}</>;
}
