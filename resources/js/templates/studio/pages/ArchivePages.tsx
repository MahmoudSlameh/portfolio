import { cn, formatNumber, useDebouncedCallback, useTranslation } from '@/kit';
import type { LibrarySearch } from '@/lib/searchSchemas';
import type {
    BooksPageProps,
    ProjectArchivePageProps,
    WritingArchivePageProps,
} from '@/templates/types';
import {
    ArticleCard,
    BookCover,
    ProjectCard,
    ProjectRow,
} from '../components/Cards';
import { EmptyState, PageHeader } from '../components/Section';
import { useStudioSpec } from '../useSpec';

/** /projects — variants: grid, list, table. Filters are applied on the server. */
export function ProjectArchivePage({
    projects,
    facets,
    total,
    search,
    onSearchChange,
}: ProjectArchivePageProps) {
    const { t } = useTranslation();
    const { variant } = useStudioSpec().pages.projects;
    const setQuery = useDebouncedCallback(
        (q: string) => onSearchChange({ q }),
        300,
    );

    return (
        <>
            <PageHeader kicker={`${total} projects`} title={t('nav.work')} />
            <div className="st-container st-filters mt-8 grid gap-3 sm:grid-cols-4">
                <input
                    type="search"
                    defaultValue={search.q ?? ''}
                    onChange={(event) => setQuery(event.target.value)}
                    placeholder="Search projects"
                    aria-label="Search projects"
                    className="st-field sm:col-span-2"
                />
                <select
                    value={search.tech ?? ''}
                    onChange={(event) =>
                        onSearchChange({ tech: event.target.value })
                    }
                    aria-label="Technology"
                    className="st-field"
                >
                    <option value="">All technologies</option>
                    {facets.technologies.map((tech) => (
                        <option key={tech} value={tech}>
                            {tech}
                        </option>
                    ))}
                </select>
                <select
                    value={search.sort ?? 'newest'}
                    onChange={(event) =>
                        onSearchChange({
                            sort: event.target.value as 'newest' | 'oldest',
                        })
                    }
                    aria-label="Sort"
                    className="st-field"
                >
                    <option value="newest">Newest first</option>
                    <option value="oldest">Oldest first</option>
                </select>
            </div>
            {facets.categories.length > 0 && (
                <div className="st-container mt-4 flex flex-wrap gap-2">
                    {facets.categories.map((category) => (
                        <button
                            key={category}
                            type="button"
                            aria-pressed={search.category === category}
                            onClick={() =>
                                onSearchChange({
                                    category:
                                        search.category === category
                                            ? undefined
                                            : category,
                                })
                            }
                            className="st-chip"
                        >
                            {category}
                        </button>
                    ))}
                </div>
            )}
            <div className="st-container st-section">
                {projects.length === 0 && (
                    <EmptyState>No projects match these filters.</EmptyState>
                )}
                {projects.length > 0 && variant === 'grid' && (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {projects.map((project) => (
                            <ProjectCard key={project.id} project={project} />
                        ))}
                    </div>
                )}
                {projects.length > 0 && variant === 'list' && (
                    <div className="border-t border-line">
                        {projects.map((project) => (
                            <ProjectRow key={project.id} project={project} />
                        ))}
                    </div>
                )}
                {projects.length > 0 && variant === 'table' && (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="text-ink-subtle">
                                <tr className="border-b border-line text-start">
                                    <th className="py-3 pe-4 text-start font-normal">
                                        Project
                                    </th>
                                    <th className="py-3 pe-4 text-start font-normal">
                                        Year
                                    </th>
                                    <th className="py-3 pe-4 text-start font-normal">
                                        Role
                                    </th>
                                    <th className="py-3 text-start font-normal">
                                        Stack
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {projects.map((project) => (
                                    <tr
                                        key={project.id}
                                        className="border-b border-line"
                                    >
                                        <td className="py-4 pe-4">
                                            <a
                                                href={`/projects/${project.slug}`}
                                                className="font-bold text-ink hover:text-signal"
                                            >
                                                {project.title}
                                            </a>
                                        </td>
                                        <td className="py-4 pe-4 font-mono text-ink-subtle">
                                            {project.year}
                                        </td>
                                        <td className="py-4 pe-4 text-ink-muted">
                                            {project.role}
                                        </td>
                                        <td className="py-4 text-ink-muted">
                                            {project.stack
                                                .slice(0, 3)
                                                .join(', ')}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </>
    );
}

/** /writing — variants: list, cards. */
export function WritingArchivePage({
    articles,
    tags,
    search,
    onSearchChange,
}: WritingArchivePageProps) {
    const { t } = useTranslation();
    const { variant } = useStudioSpec().pages.writing;
    const setQuery = useDebouncedCallback(
        (q: string) => onSearchChange({ q }),
        300,
    );

    return (
        <>
            <PageHeader
                kicker={t('section.writing')}
                title={t('writing.title')}
                intro={t('writing.intro')}
            />
            <div className="st-container st-filters mt-8 grid gap-4">
                <input
                    type="search"
                    defaultValue={search.q ?? ''}
                    onChange={(event) => setQuery(event.target.value)}
                    placeholder="Search writing"
                    aria-label="Search writing"
                    className="st-field max-w-md"
                />
                {tags.length > 0 && (
                    <div className="flex flex-wrap gap-2">
                        {tags.map((tag) => (
                            <button
                                key={tag}
                                type="button"
                                aria-pressed={search.tag === tag}
                                onClick={() =>
                                    onSearchChange({
                                        tag: search.tag === tag ? '' : tag,
                                    })
                                }
                                className="st-chip"
                            >
                                {tag}
                            </button>
                        ))}
                    </div>
                )}
            </div>
            <div className="st-container st-section">
                {articles.length === 0 ? (
                    <EmptyState>No articles match these filters.</EmptyState>
                ) : (
                    <div
                        className={
                            variant === 'cards'
                                ? 'grid gap-4 md:grid-cols-2 lg:grid-cols-3'
                                : 'border-t border-line'
                        }
                    >
                        {articles.map((article) => (
                            <ArticleCard
                                key={article.id}
                                article={article}
                                layout={variant}
                            />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

const STATUSES: {
    value: NonNullable<LibrarySearch['status']>;
    label: string;
}[] = [
    { value: 'reading', label: 'Reading' },
    { value: 'read', label: 'Read' },
    { value: 'to-read', label: 'To read' },
];

/** /books — variants: shelf, grid. */
export function BooksPage({
    books,
    stats,
    search,
    onSearchChange,
}: BooksPageProps) {
    const { t } = useTranslation();
    const { variant } = useStudioSpec().pages.books;

    return (
        <>
            <PageHeader
                kicker={`${stats.read} read · ${stats.reading} reading · ${formatNumber(stats.pagesRead)} pages`}
                title={t('books.title')}
                intro={t('books.intro')}
            />
            <div
                role="group"
                aria-label={t('books.filterLabel')}
                className="st-container st-filters mt-8 flex flex-wrap gap-2"
            >
                {STATUSES.map((status) => (
                    <button
                        key={status.value}
                        type="button"
                        aria-pressed={search.status === status.value}
                        onClick={() =>
                            onSearchChange({
                                status:
                                    search.status === status.value
                                        ? undefined
                                        : status.value,
                            })
                        }
                        className="st-chip"
                    >
                        {status.label}
                    </button>
                ))}
            </div>
            <div className="st-container st-section">
                {books.length === 0 ? (
                    <EmptyState>No books match this filter.</EmptyState>
                ) : (
                    <ul
                        className={cn(
                            variant === 'shelf'
                                ? 'grid gap-6 sm:grid-cols-2'
                                : 'grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-5',
                        )}
                    >
                        {books.map((book) => (
                            <li
                                key={book.id}
                                id={`book-${book.slug}`}
                                className={cn(
                                    'scroll-mt-24',
                                    variant === 'shelf' &&
                                        'st-card flex gap-4 p-4',
                                )}
                            >
                                <BookCover
                                    book={book}
                                    className={
                                        variant === 'shelf'
                                            ? 'w-20 shrink-0'
                                            : undefined
                                    }
                                />
                                <div
                                    className={
                                        variant === 'shelf' ? undefined : 'mt-2'
                                    }
                                >
                                    <p className="font-bold text-ink">
                                        {book.title}
                                    </p>
                                    <p className="text-sm text-ink-muted">
                                        {book.author}
                                        {book.rating &&
                                            ` · ${'★'.repeat(book.rating)}`}
                                    </p>
                                    {variant === 'shelf' && book.note && (
                                        <p className="mt-2 text-sm text-ink-muted">
                                            {book.note}
                                        </p>
                                    )}
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}
