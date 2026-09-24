import { Link } from '@/lib/router';
import { ArrowUpRight } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';
import type { ArticleSummary } from '@/lib/content';
import { formatDate } from '@/lib/utils';
import { ResponsiveImage } from '@/shared/ui/ResponsiveImage';
import type { ImageData } from '@/types/content';
import { monogram } from '../lib/monogram';

function FallbackCover({ article }: { article: ArticleSummary }) {
    return (
        <div
            aria-hidden
            className="relative flex aspect-[16/10] items-center justify-center overflow-hidden bg-[#1f1f24]"
        >
            <div className="tm-grid-bg absolute inset-0 opacity-60 [--tm-grid-line:rgb(255_255_255/0.07)]" />
            <span className="relative font-medium text-[#a8ff53]">
                <span className="text-[#f778ba]">&lt;</span>
                {monogram(article.title)}
                <span className="text-[#f778ba]"> /&gt;</span>
            </span>
        </div>
    );
}

/** Blog card: framed cover with a floating tag, reveal arrow on hover, and centered meta + title + excerpt. */
export function PostCard({
    article,
    cover,
}: {
    article: ArticleSummary;
    cover: ImageData | null;
}) {
    const { t } = useTranslation();
    const tag = article.tags[0];

    return (
        <article className="tm-blog-card relative h-full rounded-t-lg">
            <div className="relative">
                <div className="tm-zoom overflow-hidden rounded-md">
                    {cover ? (
                        <ResponsiveImage
                            image={cover}
                            sizes="(min-width: 992px) 30vw, 90vw"
                            className="aspect-[16/10]"
                        />
                    ) : (
                        <FallbackCover article={article} />
                    )}
                </div>
                {tag && (
                    <Link
                        to="/writing"
                        search={{ tag }}
                        lang="en"
                        className="absolute start-0 bottom-0 z-[2] m-4 rounded-md border border-white bg-black/60 px-3 py-1 text-sm font-medium text-white backdrop-blur-sm transition-colors hover:border-[#62a92b] hover:text-[#a8ff53]"
                    >
                        {tag}
                    </Link>
                )}
                <span
                    aria-hidden
                    className="tm-blog-arrow pointer-events-none absolute top-1/2 left-1/2 z-[2] inline-flex size-10 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-[#62a92b] text-black"
                >
                    <ArrowUpRight className="size-5" />
                </span>
            </div>
            <div className="mt-6 text-center">
                <p className="text-tm-400 mb-0 text-sm">
                    <time dateTime={article.publishedAt}>
                        {formatDate(article.publishedAt)}
                    </time>{' '}
                    • {t('writing.minRead', { count: article.readingMinutes })}
                </p>
                <h3
                    lang="en"
                    className="tm-blog-title mt-2 mb-4 text-[19px] font-medium transition-colors"
                >
                    <Link
                        to="/writing/$slug"
                        params={{ slug: article.slug }}
                        className="after:absolute after:inset-0 after:z-[1]"
                    >
                        {article.title}
                    </Link>
                </h3>
                <p lang="en" className="text-tm-body mb-0 text-sm">
                    {article.excerpt}
                </p>
            </div>
        </article>
    );
}
