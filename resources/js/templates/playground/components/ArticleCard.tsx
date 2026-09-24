import { Link } from '@/lib/router';
import { ArrowUpRight } from 'lucide-react';
import type { ArticleSummary } from '@/lib/content';
import { cn, formatDate } from '@/lib/utils';
import { usePlaygroundCopy } from '../copy';
import { popBg, popForIndex } from '../lib/pops';

export function ArticleCard({
    article,
    index,
}: {
    article: ArticleSummary;
    index: number;
}) {
    const p = usePlaygroundCopy();
    const pop = popForIndex(index);

    return (
        <Link
            to="/writing/$slug"
            params={{ slug: article.slug }}
            className="pg-card pg-press group flex h-full flex-col overflow-hidden"
        >
            <span
                className={cn(
                    'border-edge text-on-pop flex items-center justify-between gap-3 border-b-2 px-5 py-3',
                    popBg[pop],
                )}
            >
                <time
                    dateTime={article.publishedAt}
                    className="font-mono text-xs font-bold"
                >
                    {formatDate(article.publishedAt)}
                </time>
                <span className="border-edge bg-raised text-ink rounded-full border-2 px-2 font-mono text-[0.6875rem] font-bold">
                    {p('article.stickerRead', {
                        count: article.readingMinutes,
                    })}
                </span>
            </span>
            <span className="flex flex-1 flex-col gap-3 p-5">
                <span
                    lang="en"
                    className="font-display text-ink text-2xl leading-tight font-extrabold [font-stretch:110%]"
                >
                    {article.title}
                </span>
                <span
                    lang="en"
                    className="text-ink-muted text-[0.9375rem] leading-relaxed"
                >
                    {article.excerpt}
                </span>
                <span className="mt-auto flex flex-wrap items-center justify-between gap-3 pt-2">
                    <span className="flex flex-wrap gap-1.5">
                        {article.tags.map((tag) => (
                            <span
                                key={tag}
                                lang="en"
                                className="pg-tag text-ink"
                            >
                                #{tag}
                            </span>
                        ))}
                    </span>
                    <span
                        aria-hidden
                        className="border-edge bg-pop-yellow text-on-pop inline-flex size-9 items-center justify-center rounded-full border-2 transition-transform duration-300 group-hover:rotate-45"
                    >
                        <ArrowUpRight
                            className="size-4 rtl:-scale-x-100"
                            strokeWidth={2.5}
                        />
                    </span>
                </span>
            </span>
        </Link>
    );
}
