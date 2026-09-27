import { Check, Copy, Link2 } from 'lucide-react';
import { useMemo } from 'react';
import { highlight } from 'sugar-high';
import { useClipboard } from '@/hooks/useClipboard';
import { useTranslation } from '@/hooks/useTranslation';
import { useToast } from '@/providers/ToastProvider';
import {
    ArticleBlocks as KitArticleBlocks,
    ResponsiveImage,
    type ArticleBlockRenderers,
    InlineText,
    type InlineTextClassNames,
} from '@/kit';
import type { ArticleBlock } from '@/types/content';

type CodeBlock = Extract<ArticleBlock, { type: 'code' }>;
type HeadingBlock = Extract<ArticleBlock, { type: 'heading' }>;

function CodeSample({ block }: { block: CodeBlock }) {
    const { t } = useTranslation();
    const { copied, copy } = useClipboard();
    const { notify } = useToast();
    const html = useMemo(() => highlight(block.code), [block.code]);

    const handleCopy = async (): Promise<void> => {
        if (await copy(block.code)) notify(t('article.copied'));
    };

    const Icon = copied ? Check : Copy;

    return (
        <figure
            dir="ltr"
            className="code-block not-prose my-8 overflow-hidden rounded-[4px] border border-line bg-raised"
        >
            <figcaption className="flex items-center justify-between gap-4 border-b border-line bg-surface px-4 py-2">
                <span className="flex items-center gap-3 font-mono text-[0.6875rem] text-ink-subtle">
                    <span className="flex gap-1.5" aria-hidden>
                        <span className="size-2 rounded-full bg-line-strong" />
                        <span className="size-2 rounded-full bg-line-strong" />
                        <span className="size-2 rounded-full bg-line-strong" />
                    </span>
                    {block.filename ?? block.language}
                </span>
                <button
                    type="button"
                    onClick={handleCopy}
                    aria-label={t('article.copyCode')}
                    className="inline-flex h-7 items-center gap-1.5 rounded-[3px] px-2 font-mono text-[0.6875rem] text-ink-muted hover:bg-raised hover:text-ink"
                >
                    <Icon aria-hidden className="size-3.5" />
                    <span aria-hidden>
                        {copied ? t('article.copied') : 'copy'}
                    </span>
                </button>
            </figcaption>
            <pre
                className="overflow-x-auto p-4 text-[0.8125rem] leading-relaxed md:p-5"
                tabIndex={0}
            >
                <code
                    className="font-mono"
                    dangerouslySetInnerHTML={{ __html: html }}
                />
            </pre>
        </figure>
    );
}

function SectionHeading({ block }: { block: HeadingBlock }) {
    const { t } = useTranslation();
    const { copy } = useClipboard();
    const { notify } = useToast();

    const handleCopyLink = async (): Promise<void> => {
        const url = `${window.location.origin}${window.location.pathname}#${block.id}`;
        if (await copy(url)) notify(t('article.linkCopied'));
    };

    return (
        <h2
            id={block.id}
            className="group relative mt-14 mb-5 scroll-mt-28 font-display text-[2rem] leading-tight text-ink md:text-[2.375rem]"
        >
            {block.text}
            <button
                type="button"
                onClick={handleCopyLink}
                aria-label={`${t('article.copyHeading')}: ${block.text}`}
                className="ms-3 inline-flex size-7 translate-y-[-0.2em] items-center justify-center rounded-[3px] align-middle text-ink-subtle opacity-0 transition-opacity group-hover:opacity-100 hover:bg-surface hover:text-ink focus-visible:opacity-100"
            >
                <Link2 aria-hidden className="size-4" />
            </button>
        </h2>
    );
}

/** Inline formatting inside article text (bold, italic, code, links). */
const inline: InlineTextClassNames = {
    code: 'rounded bg-current/10 px-1 py-0.5 font-mono text-[0.9em]',
    link: 'underline decoration-current/40 underline-offset-4 transition-colors hover:decoration-current',
    strong: 'font-semibold',
};

const renderers: ArticleBlockRenderers = {
    paragraph: (block, { isFirst }) =>
        isFirst ? (
            <p className="mb-6 font-display text-[1.625rem] leading-[1.35] text-ink md:text-[1.875rem]">
                <InlineText text={block.text} classNames={inline} />
            </p>
        ) : (
            <p className="mb-6 text-[1.0625rem] leading-[1.8] text-ink-muted md:text-[1.125rem]">
                <InlineText text={block.text} classNames={inline} />
            </p>
        ),
    heading: (block) => <SectionHeading block={block} />,
    code: (block) => <CodeSample block={block} />,
    quote: (block) => (
        <blockquote className="relative my-10 rounded-2xl border border-line bg-surface/70 p-6 ps-8 md:p-8 md:ps-10">
            <span
                aria-hidden
                className="absolute inset-y-6 start-0 w-1 rounded-full bg-[image:var(--gradient-brand)]"
            />
            <p className="font-display text-[1.75rem] leading-snug text-ink">
                <InlineText text={block.text} classNames={inline} />
            </p>
            {block.cite && (
                <footer className="mt-3 font-mono text-xs text-ink-subtle">
                    — {block.cite}
                </footer>
            )}
        </blockquote>
    ),
    list: (block) => (
        <ul className="mb-6 flex flex-col gap-3">
            {block.items.map((item) => (
                <li
                    key={item}
                    className="flex gap-3 text-[1.0625rem] leading-relaxed text-ink-muted"
                >
                    <span
                        aria-hidden
                        className="mt-[0.7em] h-px w-3 shrink-0 bg-ink-subtle"
                    />
                    <InlineText text={item} classNames={inline} />
                </li>
            ))}
        </ul>
    ),
    callout: (block) => (
        <aside className="my-8 border border-line bg-surface p-5 md:p-6">
            <p className="eyebrow mb-2 text-signal-ink">{block.title}</p>
            <p className="text-[1rem] leading-relaxed text-ink">
                <InlineText text={block.text} classNames={inline} />
            </p>
        </aside>
    ),
    image: (block) => (
        <figure className="my-10">
            <ResponsiveImage
                image={block.image}
                sizes="(min-width: 768px) 720px, 100vw"
                className="border border-line"
            />
            {block.caption && (
                <figcaption className="mt-3 font-mono text-xs text-ink-subtle">
                    {block.caption}
                </figcaption>
            )}
        </figure>
    ),
};

export function ArticleBody({ blocks }: { blocks: ArticleBlock[] }) {
    return <KitArticleBlocks blocks={blocks} renderers={renderers} />;
}
