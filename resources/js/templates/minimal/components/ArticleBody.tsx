import { Info } from 'lucide-react';
import {
    ArticleBlocks,
    ResponsiveImage,
    type ArticleBlockRenderers,
} from '@/kit';
import type { ArticleBlock } from '@/types/content';

/*
 * One render function per block type of the panel's article builder. The type is exhaustive:
 * if a block type is added later, TypeScript points here until the template renders it.
 */
const renderers: ArticleBlockRenderers = {
    paragraph: (block, { isFirst }) => (
        <p className={isFirst ? 'mb-6 text-xl text-ink' : 'mb-5'}>
            {block.text}
        </p>
    ),
    // Heading ids come from the server and match the table-of-contents anchors.
    heading: (block) => (
        <h2
            id={block.id}
            className="mt-10 mb-4 scroll-mt-8 text-2xl font-semibold text-ink"
        >
            {block.text}
        </h2>
    ),
    code: (block) => (
        <figure className="my-6 overflow-hidden rounded-md border border-line">
            {block.filename && (
                <figcaption className="border-b border-line bg-surface px-4 py-2 font-mono text-xs text-ink-subtle">
                    {block.filename}
                </figcaption>
            )}
            <pre className="overflow-x-auto bg-surface p-4 font-mono text-sm">
                <code>{block.code}</code>
            </pre>
        </figure>
    ),
    quote: (block) => (
        <blockquote className="my-6 border-s-2 border-ink ps-4 text-lg text-ink">
            <p>{block.text}</p>
            {block.cite && (
                <footer className="mt-2 text-sm text-ink-subtle">
                    — {block.cite}
                </footer>
            )}
        </blockquote>
    ),
    list: (block) => (
        <ul className="mb-5 list-disc ps-6">
            {block.items.map((item) => (
                <li key={item} className="mb-1">
                    {item}
                </li>
            ))}
        </ul>
    ),
    callout: (block) => (
        <aside className="my-6 flex gap-3 rounded-md bg-signal-soft p-4">
            <Info aria-hidden className="mt-0.5 size-5 shrink-0 text-signal" />
            <div>
                <p className="font-medium text-ink">{block.title}</p>
                <p>{block.text}</p>
            </div>
        </aside>
    ),
    image: (block) => (
        <figure className="my-8">
            <ResponsiveImage
                image={block.image}
                sizes="(min-width: 768px) 672px, 100vw"
                className="rounded-md"
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
            className="leading-relaxed text-ink-muted"
        />
    );
}
