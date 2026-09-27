import { Info } from 'lucide-react';
import {
    ArticleBlocks,
    ResponsiveImage,
    type ArticleBlockRenderers,
    InlineText,
    type InlineTextClassNames,
} from '@/kit';
import type { ArticleBlock } from '@/types/content';

/** Inline formatting inside article text (bold, italic, code, links). */
const inline: InlineTextClassNames = {
    code: 'rounded bg-current/10 px-1 py-0.5 font-mono text-[0.9em]',
    link: 'underline decoration-current/40 underline-offset-4 transition-colors hover:decoration-current',
    strong: 'font-semibold',
};

const renderers: ArticleBlockRenderers = {
    paragraph: (block, { isFirst }) => (
        <p className={isFirst ? 'mb-6 text-xl text-ink' : 'mb-5'}>
            <InlineText text={block.text} classNames={inline} />
        </p>
    ),
    heading: (block) => (
        <h2
            id={block.id}
            className="mt-12 mb-4 scroll-mt-24 text-2xl font-bold text-ink"
        >
            {block.text}
        </h2>
    ),
    code: (block) => (
        <figure className="st-code my-6 overflow-hidden rounded-[var(--st-radius)] border border-line bg-surface">
            {block.filename && (
                <figcaption className="border-b border-line px-4 py-2 font-mono text-xs text-ink-subtle">
                    {block.filename}
                </figcaption>
            )}
            <pre className="overflow-x-auto p-4 font-mono text-sm text-ink">
                <code>{block.code}</code>
            </pre>
        </figure>
    ),
    quote: (block) => (
        <blockquote className="st-quote my-8 border-s-4 border-signal ps-5 text-xl text-ink">
            <p>
                <InlineText text={block.text} classNames={inline} />
            </p>
            {block.cite && (
                <footer className="mt-2 text-sm text-ink-subtle">
                    — {block.cite}
                </footer>
            )}
        </blockquote>
    ),
    list: (block) => (
        <ul className="mb-5 list-disc ps-6 marker:text-signal">
            {block.items.map((item) => (
                <li key={item} className="mb-1.5">
                    <InlineText text={item} classNames={inline} />
                </li>
            ))}
        </ul>
    ),
    callout: (block) => (
        <aside className="st-callout my-8 flex gap-3 rounded-[var(--st-radius)] bg-signal-soft p-5">
            <Info aria-hidden className="mt-0.5 size-5 shrink-0 text-signal" />
            <div>
                <p className="font-bold text-ink">{block.title}</p>
                <p>
                    <InlineText text={block.text} classNames={inline} />
                </p>
            </div>
        </aside>
    ),
    image: (block) => (
        <figure className="my-8">
            <ResponsiveImage
                image={block.image}
                sizes="(min-width: 768px) 720px, 100vw"
                className="rounded-[var(--st-radius)]"
            />
            {block.caption && (
                <figcaption className="mt-2 text-sm text-ink-subtle">
                    {block.caption}
                </figcaption>
            )}
        </figure>
    ),
};

export function ArticleBody({ blocks }: { blocks: ArticleBlock[] }) {
    return (
        <ArticleBlocks
            blocks={blocks}
            renderers={renderers}
            className="st-article-body text-lg leading-relaxed text-ink-muted"
        />
    );
}
