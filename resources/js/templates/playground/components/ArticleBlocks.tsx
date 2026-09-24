import { Check, Copy, Hash } from 'lucide-react';
import { useMemo } from 'react';
import { highlight } from 'sugar-high';
import { useClipboard } from '@/hooks/useClipboard';
import { useTranslation } from '@/hooks/useTranslation';
import { useToast } from '@/providers/ToastProvider';
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
            className="pg-code pg-card my-10 overflow-hidden bg-[#121212] shadow-[var(--pg-shadow-lg)]"
        >
            <figcaption className="flex items-center justify-between gap-4 border-b-2 border-edge bg-pop-yellow px-4 py-2 text-on-pop">
                <span className="flex items-center gap-3 font-mono text-xs font-bold">
                    <span aria-hidden className="flex gap-1.5">
                        <span className="size-3 rounded-full border-2 border-edge bg-pop-red" />
                        <span className="size-3 rounded-full border-2 border-edge bg-raised" />
                        <span className="size-3 rounded-full border-2 border-edge bg-pop-green" />
                    </span>
                    {block.filename ?? block.language}
                </span>
                <button
                    type="button"
                    onClick={handleCopy}
                    aria-label={t('article.copyCode')}
                    className="inline-flex h-8 items-center gap-1.5 rounded-full border-2 border-edge bg-raised px-3 font-mono text-xs font-bold text-ink"
                >
                    <Icon aria-hidden className="size-3.5" strokeWidth={2.5} />
                    <span aria-hidden>
                        {copied ? t('article.copied') : 'copy'}
                    </span>
                </button>
            </figcaption>
            <pre
                tabIndex={0}
                className="overflow-x-auto p-5 text-[0.875rem] leading-relaxed text-[#fff6e5]"
            >
                <code
                    className="font-mono"
                    dangerouslySetInnerHTML={{ __html: html }}
                />
            </pre>
        </figure>
    );
}

function Heading({ block, index }: { block: HeadingBlock; index: number }) {
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
            className="group mt-16 mb-6 flex scroll-mt-28 items-start gap-3 font-display text-[2rem] leading-tight font-black text-ink [font-stretch:115%] md:text-[2.5rem]"
        >
            <span
                aria-hidden
                className="mt-1 inline-flex size-9 shrink-0 items-center justify-center rounded-lg border-2 border-edge bg-pop-green font-mono text-sm text-on-pop"
            >
                {index}
            </span>
            <span className="min-w-0">{block.text}</span>
            <button
                type="button"
                onClick={handleCopyLink}
                aria-label={`${t('article.copyHeading')}: ${block.text}`}
                className="mt-1 inline-flex size-9 shrink-0 items-center justify-center rounded-full border-2 border-edge bg-raised text-ink opacity-0 transition-opacity group-hover:opacity-100 focus-visible:opacity-100"
            >
                <Hash aria-hidden className="size-4" strokeWidth={2.5} />
            </button>
        </h2>
    );
}

function Block({
    block,
    isFirst,
    headingIndex,
}: {
    block: ArticleBlock;
    isFirst: boolean;
    headingIndex: number;
}) {
    switch (block.type) {
        case 'paragraph':
            return isFirst ? (
                <p className="mb-8 text-[1.5rem] leading-snug font-semibold text-ink first-letter:float-left first-letter:me-3 first-letter:rounded-xl first-letter:border-2 first-letter:border-edge first-letter:bg-pop-yellow first-letter:px-3 first-letter:font-display first-letter:text-6xl first-letter:leading-none first-letter:font-black first-letter:text-on-pop md:text-[1.75rem]">
                    {block.text}
                </p>
            ) : (
                <p className="pg-prose mb-6">{block.text}</p>
            );
        case 'heading':
            return <Heading block={block} index={headingIndex} />;
        case 'code':
            return <CodeCard block={block} />;
        case 'quote':
            return (
                <blockquote className="pg-card my-12 rotate-[-1deg] bg-pop-pink p-6 text-on-pop md:p-10">
                    <span
                        aria-hidden
                        className="block font-display text-7xl leading-none font-black"
                    >
                        “
                    </span>
                    <p className="text-2xl leading-snug font-bold md:text-[1.75rem]">
                        {block.text}
                    </p>
                    {block.cite && (
                        <footer className="mt-4 font-mono text-sm font-bold">
                            — {block.cite}
                        </footer>
                    )}
                </blockquote>
            );
        case 'list':
            return (
                <ul className="mb-8 flex flex-col gap-3">
                    {block.items.map((item) => (
                        <li
                            key={item}
                            className="flex gap-3 text-[1.0625rem] leading-relaxed text-ink-muted"
                        >
                            <span
                                aria-hidden
                                className="mt-2 size-3 shrink-0 rotate-45 border-2 border-edge bg-pop-blue"
                            />
                            {item}
                        </li>
                    ))}
                </ul>
            );
        case 'callout':
            return (
                <aside className="pg-card my-10 flex gap-4 bg-pop-yellow p-6 text-on-pop">
                    <span aria-hidden className="text-3xl">
                        💡
                    </span>
                    <div>
                        <p className="mb-1 text-lg font-bold">{block.title}</p>
                        <p className="text-base leading-relaxed font-medium">
                            {block.text}
                        </p>
                    </div>
                </aside>
            );
    }
}

export function ArticleBlocks({ blocks }: { blocks: ArticleBlock[] }) {
    let headingCount = 0;

    return (
        <div>
            {blocks.map((block, index) => {
                if (block.type === 'heading') headingCount += 1;
                return (
                    <Block
                        key={`${block.type}-${index}`}
                        block={block}
                        isFirst={index === 0}
                        headingIndex={headingCount}
                    />
                );
            })}
        </div>
    );
}
