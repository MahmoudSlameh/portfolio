import { formatDate, Link, useTranslation } from '@/kit';
import type { ArticleSummary, Project } from '@/types/content';

/*
 * Lists shared by the home page and the archives. Links use the kit's `Link`: `to` takes the
 * route pattern and `params` fills it, e.g. to="/projects/$slug" params={{ slug }}.
 */

export function ProjectList({ projects }: { projects: Project[] }) {
    return (
        <ul className="divide-y divide-line border-y border-line">
            {projects.map((project) => (
                <li key={project.id} className="py-4">
                    <Link
                        to="/projects/$slug"
                        params={{ slug: project.slug }}
                        className="group flex items-baseline justify-between gap-4"
                    >
                        <span>
                            <span className="font-medium text-ink group-hover:underline">
                                {project.title}
                            </span>
                            <span className="block text-ink-muted">
                                {project.tagline}
                            </span>
                        </span>
                        <span className="shrink-0 text-sm text-ink-subtle">
                            {project.year}
                        </span>
                    </Link>
                </li>
            ))}
        </ul>
    );
}

export function ArticleList({ articles }: { articles: ArticleSummary[] }) {
    const { t } = useTranslation();

    return (
        <ul className="divide-y divide-line border-y border-line">
            {articles.map((article) => (
                <li key={article.id} className="py-4">
                    <Link
                        to="/writing/$slug"
                        params={{ slug: article.slug }}
                        className="group block"
                    >
                        <span className="font-medium text-ink group-hover:underline">
                            {article.title}
                        </span>
                        <span className="block text-sm text-ink-subtle">
                            <time dateTime={article.publishedAt}>
                                {formatDate(article.publishedAt)}
                            </time>{' '}
                            ·{' '}
                            {t('writing.minRead', {
                                count: article.readingMinutes,
                            })}
                        </span>
                    </Link>
                </li>
            ))}
        </ul>
    );
}
