import { Link } from '@/lib/router';
import { ArrowDownUp, ArrowUpRight, LayoutGrid, List } from 'lucide-react';
import { useId, useState } from 'react';
import { useDebouncedCallback } from '@/hooks/useDebouncedCallback';
import { useTranslation } from '@/hooks/useTranslation';
import { cn } from '@/lib/utils';
import { ResponsiveImage } from '@/shared/ui/ResponsiveImage';
import type { ProjectArchivePageProps } from '@/templates/types';
import type { Project, ProjectCategory } from '@/types/content';
import { useTerminalCopy } from '../copy';
import { ChipGroup } from '../components/Chip';
import { EmptyState } from '../components/EmptyState';
import { PageHeader } from '../components/PageHeader';
import { SearchField } from '../components/SearchField';

type CategoryFilter = ProjectCategory | 'all';

function ProjectCard({ project }: { project: Project }) {
    const { t } = useTranslation();

    return (
        <article className="tm-box tm-hover-up group relative flex h-full flex-col p-4">
            <div className="tm-zoom relative overflow-hidden rounded-md">
                <ResponsiveImage
                    image={project.cover}
                    sizes="(min-width: 80rem) 28vw, (min-width: 48rem) 45vw, 100vw"
                    className="aspect-[16/10]"
                />
                <span className="absolute start-3 bottom-3 rounded-md border border-white bg-black/60 px-3 py-0.5 text-sm text-white">
                    {t(`category.${project.category}`)}
                </span>
            </div>
            <div className="flex flex-1 flex-col gap-3 px-1 pt-5 pb-2">
                <p className="ltr-isolate text-tm-400 mb-0 text-sm">
                    {project.year} · {project.version}
                </p>
                <h2 lang="en" className="mb-0 text-xl font-medium">
                    <Link
                        to="/projects/$slug"
                        params={{ slug: project.slug }}
                        className="group-hover:text-[#62a92b] after:absolute after:inset-0"
                    >
                        {project.title}
                    </Link>
                </h2>
                <p
                    lang="en"
                    className="text-tm-body mb-0 text-sm leading-relaxed"
                >
                    {project.tagline}
                </p>
                <ul lang="en" className="mt-auto flex flex-wrap gap-2 pt-2">
                    {project.stack.slice(0, 4).map((tech) => (
                        <li
                            key={tech}
                            className="border-tm-border text-tm-300 border px-2.5 py-0.5 text-xs"
                        >
                            {tech}
                        </li>
                    ))}
                </ul>
            </div>
        </article>
    );
}

function ProjectTable({ projects }: { projects: Project[] }) {
    const { t } = useTranslation();
    const columns = [
        'table.year',
        'table.project',
        'table.category',
        'table.stack',
        'table.status',
    ] as const;

    return (
        <div className="tm-box overflow-x-auto">
            <table className="w-full min-w-[44rem] border-collapse text-start">
                <thead>
                    <tr className="border-tm-border border-b">
                        {columns.map((key) => (
                            <th
                                key={key}
                                scope="col"
                                className="text-tm-400 px-5 py-4 text-start text-sm font-normal"
                            >
                                {t(key)}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {projects.map((project) => (
                        <tr
                            key={project.id}
                            className="border-tm-border border-b last:border-b-0 hover:bg-[var(--signal-soft)]"
                        >
                            <td className="ltr-isolate text-tm-300 px-5 py-4">
                                {project.year}
                            </td>
                            <th
                                scope="row"
                                className="px-5 py-4 text-start font-normal"
                            >
                                <Link
                                    to="/projects/$slug"
                                    params={{ slug: project.slug }}
                                    lang="en"
                                    className="tm-text-gradient inline-flex items-center gap-1"
                                >
                                    {project.title}
                                    <ArrowUpRight
                                        aria-hidden
                                        className="text-tm-primary size-4"
                                    />
                                </Link>
                            </th>
                            <td className="text-ink px-5 py-4">
                                {t(`category.${project.category}`)}
                            </td>
                            <td
                                lang="en"
                                className="text-tm-300 px-5 py-4 text-sm"
                            >
                                {project.stack.slice(0, 3).join(', ')}
                            </td>
                            <td className="text-tm-secondary px-5 py-4">
                                {t(`projectStatus.${project.status}`)}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

export function ProjectArchivePage({
    projects,
    facets,
    total,
    search,
    onSearchChange,
}: ProjectArchivePageProps) {
    const { t } = useTranslation();
    const c = useTerminalCopy();
    const techSelectId = useId();
    const [query, setQuery] = useState(search.q ?? '');
    const debouncedSearch = useDebouncedCallback(
        (value: string) => onSearchChange({ q: value || undefined }),
        200,
    );

    const category: CategoryFilter = search.category ?? 'all';
    const sort = search.sort ?? 'newest';
    const view = search.view ?? 'grid';

    const handleQueryChange = (value: string): void => {
        setQuery(value);
        debouncedSearch(value);
    };

    const handleReset = (): void => {
        setQuery('');
        onSearchChange({ q: undefined, tech: undefined, category: undefined });
    };

    return (
        <>
            <PageHeader
                kicker={t('nav.work')}
                title={t('projects.title')}
                intro={t('projects.intro')}
                aside={
                    <span className="ltr-isolate border-tm-border text-tm-300 border px-3 py-1">
                        {c('page.count', { count: total })}
                    </span>
                }
            >
                <div className="flex flex-col gap-5">
                    <SearchField
                        label={t('projects.searchLabel')}
                        placeholder={t('projects.searchPlaceholder')}
                        value={query}
                        onChange={handleQueryChange}
                    />
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <ChipGroup
                            label={t('work.filterLabel')}
                            value={category}
                            onChange={(value) =>
                                onSearchChange({
                                    category:
                                        value === 'all' ? undefined : value,
                                })
                            }
                            options={[
                                {
                                    value: 'all' as CategoryFilter,
                                    label: t('category.all'),
                                },
                                ...facets.categories.map((value) => ({
                                    value: value as CategoryFilter,
                                    label: t(`category.${value}`),
                                })),
                            ]}
                        />
                        <div className="flex flex-wrap items-center gap-2">
                            <label htmlFor={techSelectId} className="sr-only">
                                {t('projects.techLabel')}
                            </label>
                            <select
                                id={techSelectId}
                                value={search.tech ?? ''}
                                onChange={(event) =>
                                    onSearchChange({
                                        tech: event.target.value || undefined,
                                    })
                                }
                                className="tm-input min-h-0 w-auto py-2 text-sm"
                            >
                                <option value="">
                                    {t('projects.allTech')}
                                </option>
                                {facets.technologies.map((tech) => (
                                    <option key={tech} value={tech}>
                                        {tech}
                                    </option>
                                ))}
                            </select>
                            <button
                                type="button"
                                onClick={() =>
                                    onSearchChange({
                                        sort:
                                            sort === 'newest'
                                                ? 'oldest'
                                                : undefined,
                                    })
                                }
                                aria-label={`${t('projects.sortLabel')}: ${t(sort === 'newest' ? 'sort.newest' : 'sort.oldest')}`}
                                className="tm-chip h-10 rounded-md"
                            >
                                <ArrowDownUp aria-hidden className="size-4" />
                                {t(
                                    sort === 'newest'
                                        ? 'sort.newest'
                                        : 'sort.oldest',
                                )}
                            </button>
                            <div
                                role="group"
                                aria-label={t('projects.viewLabel')}
                                className="flex gap-1"
                            >
                                {(['grid', 'table'] as const).map((mode) => {
                                    const Icon =
                                        mode === 'grid' ? LayoutGrid : List;
                                    return (
                                        <button
                                            key={mode}
                                            type="button"
                                            aria-pressed={view === mode}
                                            aria-label={t(
                                                mode === 'grid'
                                                    ? 'view.grid'
                                                    : 'view.table',
                                            )}
                                            onClick={() =>
                                                onSearchChange({
                                                    view:
                                                        mode === 'grid'
                                                            ? undefined
                                                            : mode,
                                                })
                                            }
                                            className="tm-chip size-10 justify-center rounded-md px-0"
                                        >
                                            <Icon
                                                aria-hidden
                                                className="size-4"
                                            />
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                    <p aria-live="polite" className="text-tm-400 mb-0 text-sm">
                        <span className="text-tm-primary">$</span> ls projects |
                        wc -l →{' '}
                        {t('common.results', { count: projects.length })}
                    </p>
                </div>
            </PageHeader>

            <div className="tm-container pt-8">
                {projects.length === 0 && <EmptyState onReset={handleReset} />}
                {projects.length > 0 && view === 'grid' && (
                    <ul
                        className={cn(
                            'grid gap-6 md:grid-cols-2 xl:grid-cols-3',
                        )}
                    >
                        {projects.map((project) => (
                            <li key={project.id}>
                                <ProjectCard project={project} />
                            </li>
                        ))}
                    </ul>
                )}
                {projects.length > 0 && view === 'table' && (
                    <ProjectTable projects={projects} />
                )}
            </div>
        </>
    );
}
