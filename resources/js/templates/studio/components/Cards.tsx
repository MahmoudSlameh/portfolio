import { cn, formatDate, Link, ResponsiveImage, useTranslation } from '@/kit';
import type { ArticleSummary, Book, Project } from '@/types/content';

/** Project card used by the home grid/bento/slider and the archive grid. */
export function ProjectCard({
    project,
    large = false,
    className,
}: {
    project: Project;
    large?: boolean;
    className?: string;
}) {
    return (
        <Link
            to="/projects/$slug"
            params={{ slug: project.slug }}
            className={cn(
                'st-card st-project-card group flex h-full flex-col overflow-hidden',
                className,
            )}
        >
            <ResponsiveImage
                image={project.cover}
                sizes={
                    large
                        ? '(min-width: 1024px) 60vw, 100vw'
                        : '(min-width: 1024px) 33vw, 100vw'
                }
                fallbackAspect={large ? '16 / 10' : '16 / 9'}
                className="aspect-[16/9] w-full"
            />
            <div className="flex flex-1 flex-col gap-2 p-5">
                <p className="font-mono text-xs text-ink-subtle">
                    {project.year} · {project.category}
                </p>
                <h3
                    className={cn(
                        'font-bold text-ink group-hover:text-signal',
                        large ? 'text-2xl' : 'text-lg',
                    )}
                >
                    {project.title}
                </h3>
                <p className="text-ink-muted">{project.tagline}</p>
                {project.stack.length > 0 && (
                    <p className="mt-auto flex flex-wrap gap-1.5 pt-3">
                        {project.stack.slice(0, 4).map((tech) => (
                            <span key={tech} className="st-chip">
                                {tech}
                            </span>
                        ))}
                    </p>
                )}
            </div>
        </Link>
    );
}

/** Compact project row used by lists. */
export function ProjectRow({ project }: { project: Project }) {
    return (
        <Link
            to="/projects/$slug"
            params={{ slug: project.slug }}
            className="st-project-row group grid gap-1 border-b border-line py-5 sm:grid-cols-[1fr_auto] sm:items-baseline"
        >
            <span>
                <span className="text-xl font-bold text-ink group-hover:text-signal">
                    {project.title}
                </span>
                <span className="block text-ink-muted">{project.tagline}</span>
            </span>
            <span className="font-mono text-sm text-ink-subtle">
                {project.year}
            </span>
        </Link>
    );
}

export function ArticleCard({
    article,
    layout,
}: {
    article: ArticleSummary;
    layout: 'list' | 'cards';
}) {
    const { t } = useTranslation();
    const meta = (
        <p className="font-mono text-xs text-ink-subtle">
            <time dateTime={article.publishedAt}>
                {formatDate(article.publishedAt)}
            </time>{' '}
            · {t('writing.minRead', { count: article.readingMinutes })}
        </p>
    );

    if (layout === 'list') {
        return (
            <Link
                to="/writing/$slug"
                params={{ slug: article.slug }}
                className="st-article-row group block border-b border-line py-5"
            >
                {meta}
                <span className="mt-1 block text-xl font-bold text-ink group-hover:text-signal">
                    {article.title}
                </span>
                <span className="mt-1 block text-ink-muted">
                    {article.excerpt}
                </span>
            </Link>
        );
    }

    return (
        <Link
            to="/writing/$slug"
            params={{ slug: article.slug }}
            className="st-card st-article-card group flex flex-col overflow-hidden"
        >
            {article.cover && (
                <ResponsiveImage
                    image={article.cover}
                    sizes="(min-width: 1024px) 33vw, 100vw"
                    className="aspect-[16/9] w-full"
                />
            )}
            <div className="flex flex-col gap-2 p-5">
                {meta}
                <h3 className="text-lg font-bold text-ink group-hover:text-signal">
                    {article.title}
                </h3>
                <p className="text-ink-muted">{article.excerpt}</p>
            </div>
        </Link>
    );
}

/** A book cover: the uploaded image, or the generated colour cover from the panel. */
export function BookCover({
    book,
    className,
}: {
    book: Book;
    className?: string;
}) {
    if (book.coverImage) {
        return (
            <ResponsiveImage
                image={book.coverImage}
                sizes="160px"
                fallbackAspect="2 / 3"
                className={cn(
                    'aspect-[2/3] rounded-[calc(var(--st-radius)/2)]',
                    className,
                )}
            />
        );
    }

    return (
        <span
            aria-hidden
            style={{ background: book.cover.background, color: book.cover.ink }}
            className={cn(
                'st-book-cover flex aspect-[2/3] flex-col justify-between rounded-[calc(var(--st-radius)/2)] p-3',
                className,
            )}
        >
            <span
                style={{ background: book.cover.accent }}
                className="block h-1.5 w-8"
            />
            <span className="text-sm leading-tight font-bold">
                {book.title}
            </span>
        </span>
    );
}
