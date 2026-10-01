import { formatDate, Link, ResponsiveImage, useTranslation } from '@/kit';
import type { ArticlePageProps } from '@/templates/types';
import { ArticleBody } from '../components/ArticleBody';

/**
 * /writing/{slug}. `article` is an ArticleDetail: the article with its block body, reading time,
 * related projects and previous/next articles. The body is rendered by components/ArticleBody.
 */
export function ArticlePage({ article }: ArticlePageProps) {
    const { t } = useTranslation();

    return (
        <article className="mn-container pt-16">
            <Link to="/writing" className="mn-link text-sm">
                ← {t('article.back')}
            </Link>
            <h1 className="mt-6 text-3xl font-semibold tracking-tight text-ink sm:text-4xl">
                {article.title}
            </h1>
            <p className="mt-3 text-sm text-ink-subtle">
                <time dateTime={article.publishedAt}>
                    {formatDate(article.publishedAt)}
                </time>{' '}
                · {t('writing.minRead', { count: article.readingMinutes })}
            </p>

            {article.cover && (
                <ResponsiveImage
                    image={article.cover}
                    sizes="(min-width: 768px) 672px, 100vw"
                    priority
                    className="mt-8 rounded-md"
                />
            )}

            <div className="mt-10">
                <ArticleBody blocks={article.body} />
            </div>

            {article.relatedProjects.length > 0 && (
                <aside className="mt-12 border-t border-line pt-6">
                    <h2 className="text-sm font-semibold text-ink-subtle">
                        {t('article.related')}
                    </h2>
                    <ul className="mt-2 flex flex-wrap gap-4">
                        {article.relatedProjects.map((project) => (
                            <li key={project.slug}>
                                <Link
                                    to="/projects/$slug"
                                    params={{ slug: project.slug }}
                                    className="mn-link"
                                >
                                    {project.title}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </aside>
            )}

            <nav
                aria-label={t('article.adjacent')}
                className="mt-12 flex justify-between gap-4 text-sm"
            >
                {article.previous ? (
                    <Link
                        to="/writing/$slug"
                        params={{ slug: article.previous.slug }}
                        className="mn-link"
                    >
                        ← {article.previous.title}
                    </Link>
                ) : (
                    <span />
                )}
                {article.next && (
                    <Link
                        to="/writing/$slug"
                        params={{ slug: article.next.slug }}
                        className="mn-link"
                    >
                        {article.next.title} →
                    </Link>
                )}
            </nav>
        </article>
    );
}
