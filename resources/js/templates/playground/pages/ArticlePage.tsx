import { Link } from '@/lib/router';
import {
    ArrowLeft,
    ArrowRight,
    ArrowUpRight,
    Check,
    Link2,
} from 'lucide-react';
import { m, useScroll, useSpring } from 'motion/react';
import { useRef, type RefObject } from 'react';
import { useClipboard } from '@/hooks/useClipboard';
import { useTranslation } from '@/hooks/useTranslation';
import type { ArticleSummary } from '@/lib/content';
import { useSiteConfig } from '@/lib/seo';
import { cn, formatDate } from '@/lib/utils';
import { useToast } from '@/providers/ToastProvider';
import type { ArticlePageProps } from '@/templates/types';
import { useActiveSection } from '../lib/useActiveSection';
import { ArticleBlocks } from '../components/ArticleBlocks';
import { popButtonClasses } from '../components/PopButton';
import { Sticker } from '../components/Sticker';

function ProgressBar({
    targetRef,
}: {
    targetRef: RefObject<HTMLElement | null>;
}) {
    const { t } = useTranslation();
    const { scrollYProgress } = useScroll({
        target: targetRef,
        offset: ['start start', 'end end'],
    });
    const scaleX = useSpring(scrollYProgress, {
        stiffness: 180,
        damping: 30,
        restDelta: 0.001,
    });

    return (
        <div
            aria-hidden
            title={t('article.progress')}
            className="fixed inset-x-0 top-0 z-50 h-2.5 border-b-2 border-edge bg-raised"
        >
            <m.div
                style={{ scaleX }}
                className="pg-progress h-full bg-[linear-gradient(90deg,var(--pop-yellow),var(--pop-pink),var(--pop-blue),var(--pop-green))]"
            />
        </div>
    );
}

function Contents({ entries }: { entries: { id: string; text: string }[] }) {
    const { t } = useTranslation();
    const activeId = useActiveSection(entries.map((entry) => entry.id));
    if (entries.length === 0) return null;

    return (
        <nav aria-label={t('article.toc')} className="pg-card p-4">
            <p className="pg-label mb-3 text-ink">{t('article.toc')}</p>
            <ol lang="en" className="flex flex-col gap-1">
                {entries.map((entry, index) => (
                    <li key={entry.id}>
                        <a
                            href={`#${entry.id}`}
                            aria-current={
                                activeId === entry.id ? 'location' : undefined
                            }
                            className="flex gap-2 rounded-lg px-2 py-1.5 text-sm leading-snug font-semibold text-ink-muted hover:bg-pop-yellow/50 hover:text-ink aria-[current=location]:bg-ink aria-[current=location]:text-paper"
                        >
                            <span className="ltr-isolate font-mono text-xs">
                                {index + 1}.
                            </span>
                            {entry.text}
                        </a>
                    </li>
                ))}
            </ol>
        </nav>
    );
}

function NeighbourCard({
    article,
    direction,
}: {
    article: ArticleSummary | null;
    direction: 'older' | 'newer';
}) {
    const { t } = useTranslation();
    if (!article) return <span aria-hidden />;
    const isNewer = direction === 'newer';
    const Icon = isNewer ? ArrowRight : ArrowLeft;

    return (
        <Link
            to="/writing/$slug"
            params={{ slug: article.slug }}
            className={cn(
                'pg-card pg-press flex flex-col gap-3 p-6 text-on-pop md:p-8',
                isNewer
                    ? 'bg-pop-blue md:items-end md:text-end'
                    : 'bg-pop-yellow',
            )}
        >
            <span className="pg-label flex items-center gap-2">
                {!isNewer && (
                    <Icon
                        aria-hidden
                        className="size-4 rtl:-scale-x-100"
                        strokeWidth={2.5}
                    />
                )}
                {t(isNewer ? 'article.newer' : 'article.older')}
                {isNewer && (
                    <Icon
                        aria-hidden
                        className="size-4 rtl:-scale-x-100"
                        strokeWidth={2.5}
                    />
                )}
            </span>
            <span
                lang="en"
                className="font-display text-2xl leading-tight font-black [font-stretch:115%] md:text-3xl"
            >
                {article.title}
            </span>
        </Link>
    );
}

export function ArticlePage({ article }: ArticlePageProps) {
    const siteConfig = useSiteConfig();
    const { t } = useTranslation();
    const articleRef = useRef<HTMLElement>(null);
    const { copied, copy } = useClipboard();
    const { notify } = useToast();
    const LinkIcon = copied ? Check : Link2;
    const entries = article.body.flatMap((block) =>
        block.type === 'heading' ? [{ id: block.id, text: block.text }] : [],
    );

    const handleCopyLink = async (): Promise<void> => {
        if (await copy(`${siteConfig.url}/writing/${article.slug}`))
            notify(t('article.linkCopied'));
    };

    return (
        <>
            <ProgressBar targetRef={articleRef} />
            <article ref={articleRef}>
                <header className="pg-shell pt-6 pb-12 md:pt-10">
                    <nav
                        aria-label={t('common.breadcrumb')}
                        className="mb-10 flex flex-wrap items-center justify-between gap-3"
                    >
                        <Link
                            to="/writing"
                            className={popButtonClasses({
                                tone: 'plain',
                                size: 'sm',
                            })}
                        >
                            <ArrowLeft
                                aria-hidden
                                className="size-4 rtl:-scale-x-100"
                                strokeWidth={2.5}
                            />
                            {t('article.back')}
                        </Link>
                        <button
                            type="button"
                            onClick={handleCopyLink}
                            className={popButtonClasses({
                                tone: 'pink',
                                size: 'sm',
                            })}
                        >
                            <LinkIcon
                                aria-hidden
                                className="size-4"
                                strokeWidth={2.5}
                            />
                            {copied
                                ? t('article.linkCopied')
                                : t('article.copyLink')}
                        </button>
                    </nav>
                    <div className="mx-auto flex max-w-5xl flex-col items-center gap-6 text-center">
                        <div className="flex flex-wrap justify-center gap-3">
                            <Sticker pop="yellow" tilt={-4}>
                                <time dateTime={article.publishedAt}>
                                    {formatDate(article.publishedAt)}
                                </time>
                            </Sticker>
                            <Sticker pop="green" tilt={3}>
                                {t('writing.minRead', {
                                    count: article.readingMinutes,
                                })}
                            </Sticker>
                        </div>
                        <h1
                            lang="en"
                            className="pg-display pg-keep-case text-[clamp(2.75rem,7.5vw,6.5rem)] text-ink"
                        >
                            {article.title}
                        </h1>
                        <p
                            lang="en"
                            className="max-w-2xl text-xl leading-snug font-medium text-ink-muted"
                        >
                            {article.excerpt}
                        </p>
                        <ul className="flex flex-wrap justify-center gap-2">
                            {article.tags.map((tag) => (
                                <li key={tag}>
                                    <Link
                                        to="/writing"
                                        search={{ tag }}
                                        lang="en"
                                        className="pg-chip"
                                    >
                                        #{tag}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </div>
                </header>

                <div className="pg-shell grid gap-10 lg:grid-cols-[16rem_minmax(0,44rem)_minmax(0,1fr)] lg:justify-center">
                    <aside className="hidden lg:block">
                        <div className="sticky top-8">
                            <Contents entries={entries} />
                        </div>
                    </aside>
                    <div lang="en" dir="ltr" className="min-w-0 text-start">
                        <ArticleBlocks blocks={article.body} />
                    </div>
                    {article.relatedProjects.length > 0 && (
                        <aside aria-labelledby="related-projects">
                            <div className="pg-card bg-pop-purple p-5 text-on-pop lg:sticky lg:top-8">
                                <h2
                                    id="related-projects"
                                    className="pg-label mb-4"
                                >
                                    {t('article.related')}
                                </h2>
                                <ul className="flex flex-col gap-2">
                                    {article.relatedProjects.map((project) => (
                                        <li key={project.slug}>
                                            <Link
                                                to="/projects/$slug"
                                                params={{ slug: project.slug }}
                                                className="flex items-center justify-between gap-2 rounded-xl border-2 border-edge bg-raised px-3 py-2 font-bold text-ink hover:bg-pop-yellow hover:text-on-pop"
                                            >
                                                <span lang="en">
                                                    {project.title}
                                                </span>
                                                <ArrowUpRight
                                                    aria-hidden
                                                    className="size-4 shrink-0 rtl:-scale-x-100"
                                                    strokeWidth={2.5}
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
                className="pg-shell mt-16 grid gap-6 md:grid-cols-2"
            >
                <NeighbourCard article={article.previous} direction="older" />
                <NeighbourCard article={article.next} direction="newer" />
            </nav>
        </>
    );
}
