import { Check, Copy, Hash, Lightbulb } from 'lucide-react';
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

function CodeCard({ block }: { block: CodeBlock }) {
    const { t } = useTranslation();
    const { copied, copy } = useClipboard();
    const { notify } = useToast();
    const html = useMemo(() => highlight(block.code), [block.code]);
    const Icon = copied ? Check : Copy;

    const handleCopy = async (): Promise<void> => {
        if (await copy(block.code)) notify(t('article.copied'));
    };

    return (
        <figure
            dir="ltr"
            className="tm-code my-10 overflow-hidden rounded-lg border border-[#3b413d] bg-[#1f1f24]"
        >
            <figcaption className="flex items-center justify-between gap-4 border-b border-[#3b413d] bg-[#272730] px-4 py-2 text-sm text-[#8f8f92]">
                <span className="flex items-center gap-3">
                    <span aria-hidden className="flex gap-1.5">
                        <span className="size-3 rounded-full bg-[#ec4040]" />
                        <span className="size-3 rounded-full bg-[#ffd45d]" />
                        <span className="size-3 rounded-full bg-[#a8ff53]" />
                    </span>
                    {block.filename ?? block.language}
                </span>
                <button
                    type="button"
                    onClick={handleCopy}
                    aria-label={t('article.copyCode')}
                    className="inline-flex h-8 items-center gap-1.5 rounded-md border border-[#3b413d] px-3 text-xs text-[#e9e9ea] hover:border-[#a8ff53] hover:text-[#a8ff53]"
                >
                    <Icon aria-hidden className="size-3.5" />
                    <span aria-hidden>
                        {copied ? t('article.copied') : 'copy'}
                    </span>
                </button>
            </figcaption>
            <pre
                tabIndex={0}
                className="overflow-x-auto p-5 text-sm leading-relaxed text-[#e9e9ea]"
            >
                <code dangerouslySetInnerHTML={{ __html: html }} />
            </pre>
        </figure>
    );
}

function Heading({ block }: { block: HeadingBlock }) {
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
            className="group mt-14 mb-5 flex scroll-mt-10 items-start gap-2 text-[1.75rem] font-medium"
        >
            <span aria-hidden className="text-tm-primary">
                #
            </span>
            <span className="min-w-0">{block.text}</span>
            <button
                type="button"
                onClick={handleCopyLink}
                aria-label={`${t('article.copyHeading')}: ${block.text}`}
                className="mt-1 inline-flex size-8 shrink-0 items-center justify-center rounded-md border border-tm-border text-tm-300 opacity-0 transition-opacity group-hover:opacity-100 focus-visible:opacity-100"
            >
                <Hash aria-hidden className="size-4" />
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
    paragraph: (block, { isFirst }) => (
        <p
            className={
                isFirst
                    ? 'mb-8 text-xl leading-relaxed text-ink'
                    : 'tm-prose mb-6'
            }
        >
            <InlineText text={block.text} classNames={inline} />
        </p>
    ),
    heading: (block) => <Heading block={block} />,
    code: (block) => <CodeCard block={block} />,
    quote: (block) => (
        <blockquote className="my-10 border-s-2 border-tm-primary ps-6">
            <p className="text-xl leading-relaxed text-ink">
                <span className="text-tm-secondary">&gt; </span>
                <InlineText text={block.text} classNames={inline} />
            </p>
            {block.cite && (
                <footer className="mt-3 text-sm text-tm-300">
                    — {block.cite}
                </footer>
            )}
        </blockquote>
    ),
    list: (block) => (
        <ul className="mb-8 list-disc ps-6 marker:text-tm-primary">
            {block.items.map((item) => (
                <li key={item} className="tm-prose mb-2">
                    <InlineText text={item} classNames={inline} />
                </li>
            ))}
        </ul>
    ),
    callout: (block) => (
        <aside className="tm-box my-10 flex gap-4 p-6">
            <Lightbulb
                aria-hidden
                className="size-6 shrink-0 text-tm-primary"
            />
            <div>
                <p className="mb-1 font-medium text-ink">{block.title}</p>
                <p className="mb-0 leading-relaxed text-tm-300">
                    <InlineText text={block.text} classNames={inline} />
                </p>
            </div>
        </aside>
    ),
    image: (block) => (
        <figure className="my-10">
            <ResponsiveImage
                image={block.image}
                sizes="(min-width: 768px) 720px, 100vw"
                className="tm-box"
            />
            {block.caption && (
                <figcaption className="mt-3 text-sm text-tm-300">
                    {block.caption}
                </figcaption>
            )}
        </figure>
    ),
};

export function ArticleBlocks({ blocks }: { blocks: ArticleBlock[] }) {
    return <KitArticleBlocks blocks={blocks} renderers={renderers} />;
}
