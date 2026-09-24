import { Link } from '@/lib/router';
import { ArrowLeft, ArrowRight, ArrowUpRight } from 'lucide-react';
import { useRef } from 'react';
import { CopyButton } from '@/templates/changelog/components/ui/CopyButton';
import { Tag } from '@/templates/changelog/components/ui/Tag';
import { useTranslation } from '@/hooks/useTranslation';
import type { ArticleDetail, ArticleSummary } from '@/lib/content';
import { useSiteConfig } from '@/lib/seo';
import { cn, formatDate } from '@/lib/utils';
import { ArticleBody } from './ArticleBody';
import { ReadingProgress } from './ReadingProgress';
import { TableOfContents, type TocEntry } from './TableOfContents';

function AdjacentArticle({
    article,
    direction,
}: {
    article: ArticleSummary | null;
    direction: 'older' | 'newer';
}) {
    const { t } = useTranslation();
    const isNewer = direction === 'newer';

    if (!article)
        return (
            <span
                aria-hidden
                className={cn(isNewer && 'md:border-line md:border-s')}
            />
        );

    const Icon = isNewer ? ArrowRight : ArrowLeft;

    return (
        <Link
            to="/writing/$slug"
            params={{ slug: article.slug }}
            className={cn(
                'group flex flex-col gap-3 py-10',
                isNewer
                    ? 'border-line border-t md:items-end md:border-s md:border-t-0 md:ps-10 md:text-end'
                    : 'md:pe-10',
            )}
        >
            <span className="text-ink-subtle flex items-center gap-2 text-sm">
                {!isNewer && (
                    <Icon aria-hidden className="size-4 rtl:-scale-x-100" />
                )}
                {t(isNewer ? 'article.newer' : 'article.older')}
                {isNewer && (
                    <Icon aria-hidden className="size-4 rtl:-scale-x-100" />
                )}
            </span>
            <span
                lang="en"
                className="link-draw-target font-display text-ink text-3xl leading-tight md:text-4xl"
            >
                {article.title}
            </span>
        </Link>
    );
}

export function ArticlePage({ article }: { article: ArticleDetail }) {
    const siteConfig = useSiteConfig();
    const { t } = useTranslation();
    const articleRef = useRef<HTMLElement>(null);
    const tocEntries: TocEntry[] = article.body.flatMap((block) =>
        block.type === 'heading' ? [{ id: block.id, text: block.text }] : [],
    );

    return (
        <>
            <ReadingProgress targetRef={articleRef} />
            <article ref={articleRef}>
                <header className="border-line relative overflow-hidden border-b">
                    <div
                        aria-hidden
                        className="aurora opacity-[calc(var(--glow-opacity)*0.7)]"
                    >
                        <span />
                        <span />
                        <span />
                    </div>
                    <div
                        aria-hidden
                        className="baseline-texture pointer-events-none absolute inset-0"
                    />
                    <div className="shell relative pt-10 pb-12 md:pt-14 md:pb-16">
                        <nav
                            aria-label={t('common.breadcrumb')}
                            className="mb-12 flex items-center justify-between gap-4"
                        >
                            <Link
                                to="/writing"
                                className="group text-ink-muted hover:text-ink inline-flex items-center gap-2 text-sm"
                            >
                                <ArrowLeft
                                    aria-hidden
                                    className="size-4 transition-transform group-hover:-translate-x-0.5 rtl:-scale-x-100 rtl:group-hover:translate-x-0.5"
                                />
                                <span className="link-draw-target">
                                    {t('article.back')}
                                </span>
                            </Link>
                            <CopyButton
                                text={`${siteConfig.url}/writing/${article.slug}`}
                                label={t('article.copyLink')}
                                copiedLabel={t('article.linkCopied')}
                            />
                        </nav>
                        <div className="mx-auto max-w-4xl">
                            <ul className="mb-6 flex flex-wrap gap-1.5">
                                {article.tags.map((tag) => (
                                    <li key={tag}>
                                        <Link
                                            to="/writing"
                                            search={{ tag }}
                                            className="inline-flex"
                                        >
                                            <Tag className="hover:border-ink hover:text-ink transition-colors">
                                                #{tag}
                                            </Tag>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                            <h1
                                lang="en"
                                className="font-display text-ink text-5xl leading-[0.98] tracking-[-0.015em] sm:text-6xl lg:text-7xl"
                            >
                                {article.title}
                            </h1>
                            <p
                                lang="en"
                                className="text-ink-muted mt-6 max-w-2xl text-lg leading-relaxed md:text-xl"
                            >
                                {article.excerpt}
                            </p>
                            <dl className="border-line mt-10 flex flex-wrap gap-x-10 gap-y-4 border-t pt-5">
                                <div>
                                    <dt className="eyebrow text-ink-subtle">
                                        {t('article.published')}
                                    </dt>
                                    <dd className="text-ink mt-1 text-sm">
                                        <time dateTime={article.publishedAt}>
                                            {formatDate(article.publishedAt)}
                                        </time>
                                    </dd>
                                </div>
                                <div>
                                    <dt className="eyebrow text-ink-subtle">
                                        {t('article.readTime')}
                                    </dt>
                                    <dd className="text-ink mt-1 text-sm">
                                        {t('writing.minRead', {
                                            count: article.readingMinutes,
                                        })}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </header>

                <div className="shell grid gap-10 py-12 md:py-16 lg:grid-cols-[minmax(0,1fr)_minmax(0,42rem)_minmax(0,1fr)]">
                    <aside className="hidden lg:block">
                        <div className="sticky top-[calc(var(--header-height)+2.5rem)] max-w-56">
                            <TableOfContents entries={tocEntries} />
                        </div>
                    </aside>
                    <div lang="en" dir="ltr" className="min-w-0 text-start">
                        <ArticleBody blocks={article.body} />
                    </div>
                    {article.relatedProjects.length > 0 && (
                        <aside
                            aria-labelledby="related-projects"
                            className="lg:pt-2"
                        >
                            <div className="border-line border-t pt-5 lg:sticky lg:top-[calc(var(--header-height)+2.5rem)] lg:border-t-0 lg:pt-0">
                                <h2
                                    id="related-projects"
                                    className="eyebrow text-ink-subtle mb-4"
                                >
                                    {t('article.related')}
                                </h2>
                                <ul className="flex flex-col gap-3">
                                    {article.relatedProjects.map((project) => (
                                        <li key={project.slug}>
                                            <Link
                                                to="/projects/$slug"
                                                params={{ slug: project.slug }}
                                                className="group font-display text-ink inline-flex items-center gap-2 text-2xl"
                                            >
                                                <span
                                                    lang="en"
                                                    className="link-draw-target"
                                                >
                                                    {project.title}
                                                </span>
                                                <ArrowUpRight
                                                    aria-hidden
                                                    className="text-ink-subtle group-hover:text-signal-ink size-4 rtl:-scale-x-100"
                                                />
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </aside>
                    )}
                </div>
            </article>

            <nav
                aria-label={t('article.adjacent')}
                className="border-line border-t"
            >
                <div className="shell grid md:grid-cols-2">
                    <AdjacentArticle
                        article={article.previous}
                        direction="older"
                    />
                    <AdjacentArticle article={article.next} direction="newer" />
                </div>
            </nav>
        </>
    );
}
