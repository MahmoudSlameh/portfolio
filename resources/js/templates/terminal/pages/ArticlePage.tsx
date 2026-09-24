import { Link } from '@/lib/router';
import {
    ArrowLeft,
    ArrowRight,
    ArrowUpRight,
    Check,
    Link2,
} from 'lucide-react';
import { m, useScroll, useSpring } from 'motion/react';
import { useRef } from 'react';
import { useClipboard } from '@/hooks/useClipboard';
import { useTranslation } from '@/hooks/useTranslation';
import type { ArticleSummary } from '@/lib/content';
import { useSiteConfig } from '@/lib/seo';
import { formatDate } from '@/lib/utils';
import { useToast } from '@/providers/ToastProvider';
import type { ArticlePageProps } from '@/templates/types';
import { ArticleBlocks } from '../components/ArticleBlocks';
import { GlowCard } from '../components/GlowCard';
import { Kicker } from '../components/Kicker';
import { useActiveSection } from '../lib/useActiveSection';

function Neighbour({
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
            className={`tm-box tm-hover-up flex flex-col gap-2 p-6 md:p-8 ${isNewer ? 'md:items-end md:text-end' : ''}`}
        >
            <span className="flex items-center gap-2 text-sm text-tm-400">
                {!isNewer && (
                    <Icon aria-hidden className="size-4 rtl:-scale-x-100" />
                )}
                {t(isNewer ? 'article.newer' : 'article.older')}
                {isNewer && (
                    <Icon aria-hidden className="size-4 rtl:-scale-x-100" />
                )}
            </span>
            <span lang="en" className="text-xl font-medium text-ink">
                {article.title}
            </span>
        </Link>
    );
}

export function ArticlePage({ article }: ArticlePageProps) {
    const siteConfig = useSiteConfig();
    const { t } = useTranslation();
    const articleRef = useRef<HTMLElement>(null);
    const { scrollYProgress } = useScroll({
        target: articleRef,
        offset: ['start start', 'end end'],
    });
    const scaleX = useSpring(scrollYProgress, {
        stiffness: 180,
        damping: 30,
        restDelta: 0.001,
    });
    const { copied, copy } = useClipboard();
    const { notify } = useToast();
    const LinkIcon = copied ? Check : Link2;
    const entries = article.body.flatMap((block) =>
        block.type === 'heading' ? [{ id: block.id, text: block.text }] : [],
    );
    const activeId = useActiveSection(entries.map((entry) => entry.id));

    const handleCopyLink = async (): Promise<void> => {
        if (await copy(`${siteConfig.url}/writing/${article.slug}`))
            notify(t('article.linkCopied'));
    };

    return (
        <>
            <div
                aria-hidden
                title={t('article.progress')}
                className="fixed inset-x-0 top-0 z-50 h-[3px]"
            >
                <m.div
                    style={{ scaleX }}
                    className="h-full origin-left bg-[linear-gradient(90deg,#659932,#a8ff53)] rtl:origin-right"
                />
            </div>

            <article ref={articleRef}>
                <header className="tm-container pt-[130px]">
                    <GlowCard innerClassName="p-5 md:p-10 lg:p-16">
                        <div
                            aria-hidden
                            className="tm-grid-bg tm-grid-fade pointer-events-none absolute inset-0"
                        />
                        <nav
                            aria-label={t('common.breadcrumb')}
                            className="relative mb-10 flex flex-wrap items-center justify-between gap-3"
                        >
                            <Link
                                to="/writing"
                                className="inline-flex items-center gap-2 text-tm-300 hover:text-[#62a92b]"
                            >
                                <ArrowLeft
                                    aria-hidden
                                    className="size-4 rtl:-scale-x-100"
                                />
                                {t('article.back')}
                            </Link>
                            <button
                                type="button"
                                onClick={handleCopyLink}
                                className="inline-flex items-center gap-2 text-tm-300 hover:text-[#62a92b]"
                            >
                                <LinkIcon aria-hidden className="size-4" />
                                {copied
                                    ? t('article.linkCopied')
                                    : t('article.copyLink')}
                            </button>
                        </nav>
                        <div className="relative mx-auto max-w-4xl text-center">
                            <Kicker center>
                                <time dateTime={article.publishedAt}>
                                    {formatDate(article.publishedAt)}
                                </time>{' '}
                                •{' '}
                                {t('writing.minRead', {
                                    count: article.readingMinutes,
                                })}
                            </Kicker>
                            <h1
                                lang="en"
                                className="mt-3 mb-4 text-[clamp(2rem,5vw,3.125rem)] font-medium"
                            >
                                {article.title}
                                <span aria-hidden className="tm-flicker">
                                    _
                                </span>
                            </h1>
                            <p
                                lang="en"
                                className="mx-auto max-w-2xl text-tm-body"
                            >
                                {article.excerpt}
                            </p>
                            <ul className="mt-6 flex flex-wrap justify-center gap-2">
                                {article.tags.map((tag) => (
                                    <li key={tag}>
                                        <Link
                                            to="/writing"
                                            search={{ tag }}
                                            lang="en"
                                            className="tm-chip rounded-md"
                                        >
                                            #{tag}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </GlowCard>
                </header>

                <div className="tm-container grid grid-cols-1 gap-8 pt-8 lg:grid-cols-[15rem_minmax(0,1fr)_15rem]">
                    <aside className="hidden lg:block">
                        {entries.length > 0 && (
                            <nav
                                aria-label={t('article.toc')}
                                className="tm-box sticky top-8 p-5"
                            >
                                <p className="mb-3 text-sm text-tm-400">
                                    {t('article.toc')}
                                </p>
                                <ol lang="en" className="flex flex-col gap-1">
                                    {entries.map((entry) => (
                                        <li key={entry.id}>
                                            <a
                                                href={`#${entry.id}`}
                                                aria-current={
                                                    activeId === entry.id
                                                        ? 'location'
                                                        : undefined
                                                }
                                                className="block py-1 text-sm leading-snug text-tm-300 hover:text-ink aria-[current=location]:text-tm-primary"
                                            >
                                                # {entry.text}
                                            </a>
                                        </li>
                                    ))}
                                </ol>
                            </nav>
                        )}
                    </aside>
                    <div
                        lang="en"
                        dir="ltr"
                        className="tm-box min-w-0 p-5 text-start md:p-10"
                    >
                        <ArticleBlocks blocks={article.body} />
                    </div>
                    {article.relatedProjects.length > 0 ? (
                        <aside aria-labelledby="related-projects">
                            <div className="tm-box p-5 lg:sticky lg:top-8">
                                <h2
                                    id="related-projects"
                                    className="mb-4 text-sm font-normal text-tm-400"
                                >
                                    {t('article.related')}
                                </h2>
                                <ul className="flex flex-col gap-2">
                                    {article.relatedProjects.map((project) => (
                                        <li key={project.slug}>
                                            <Link
                                                to="/projects/$slug"
                                                params={{ slug: project.slug }}
                                                className="flex items-center justify-between gap-2 border-b border-tm-border pb-2 text-ink hover:text-[#62a92b]"
                                            >
                                                <span lang="en">
                                                    {project.title}
                                                </span>
                                                <ArrowUpRight
                                                    aria-hidden
                                                    className="size-4 shrink-0 rtl:-scale-x-100"
                                                />
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </aside>
                    ) : (
                        <span aria-hidden className="hidden lg:block" />
                    )}
                </div>
            </article>

            <nav
                aria-label={t('article.adjacent')}
                className="tm-container grid grid-cols-1 gap-6 pt-8 md:grid-cols-2"
            >
                <Neighbour article={article.previous} direction="older" />
                <Neighbour article={article.next} direction="newer" />
            </nav>
        </>
    );
}
