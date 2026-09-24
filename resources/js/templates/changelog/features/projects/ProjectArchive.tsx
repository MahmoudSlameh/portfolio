import { Link } from '@/lib/router';
import { LayoutGrid, Table2 } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useState } from 'react';
import { ProjectCard } from '@/templates/changelog/components/content/ProjectCard';
import { ProjectStatusBadge } from '@/templates/changelog/components/content/ProjectStatusBadge';
import { EmptyState } from '@/templates/changelog/components/ui/EmptyState';
import { FilterGroup } from '@/templates/changelog/components/ui/FilterGroup';
import { SelectField } from '@/templates/changelog/components/ui/FormField';
import { PageHeader } from '@/templates/changelog/components/ui/PageHeader';
import { SearchInput } from '@/templates/changelog/components/ui/SearchInput';
import { Tag } from '@/templates/changelog/components/ui/Tag';
import { useDebouncedCallback } from '@/hooks/useDebouncedCallback';
import { useTranslation } from '@/hooks/useTranslation';
import type { ProjectFacets } from '@/lib/content';
import type { ArchiveSearch } from '@/lib/searchSchemas';
import type { Project, ProjectCategory } from '@/types/content';

type CategoryFilter = ProjectCategory | 'all';

interface ProjectArchiveProps {
    projects: Project[];
    facets: ProjectFacets;
    total: number;
    search: ArchiveSearch;
    onSearchChange: (patch: Partial<ArchiveSearch>) => void;
}

function ProjectTable({ projects }: { projects: Project[] }) {
    const { t } = useTranslation();
    const headerClass =
        'eyebrow py-3 pe-4 text-start font-medium text-ink-subtle';

    return (
        <div className="overflow-x-auto border-t border-line">
            <table className="w-full min-w-[46rem] border-collapse">
                <caption className="sr-only">{t('projects.title')}</caption>
                <thead>
                    <tr className="border-b border-line">
                        <th scope="col" className={headerClass}>
                            {t('table.year')}
                        </th>
                        <th scope="col" className={headerClass}>
                            {t('table.project')}
                        </th>
                        <th scope="col" className={headerClass}>
                            {t('table.category')}
                        </th>
                        <th scope="col" className={headerClass}>
                            {t('table.stack')}
                        </th>
                        <th scope="col" className={headerClass}>
                            {t('table.status')}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {projects.map((project) => (
                        <tr
                            key={project.id}
                            className="group border-b border-line transition-colors hover:bg-raised"
                        >
                            <td className="ltr-isolate py-4 pe-4 align-top font-mono text-xs text-ink-subtle">
                                {project.year}
                            </td>
                            <th
                                scope="row"
                                lang="en"
                                className="py-4 pe-4 text-start align-top font-normal"
                            >
                                <Link
                                    to="/projects/$slug"
                                    params={{ slug: project.slug }}
                                    className="link-draw font-display text-2xl leading-tight text-ink"
                                >
                                    {project.title}
                                </Link>
                                <p className="mt-1 max-w-sm text-sm text-ink-muted">
                                    {project.tagline}
                                </p>
                            </th>
                            <td className="py-4 pe-4 align-top text-sm text-ink-muted">
                                {t(`category.${project.category}`)}
                            </td>
                            <td className="py-4 pe-4 align-top">
                                <ul className="flex max-w-xs flex-wrap gap-1">
                                    {project.stack
                                        .slice(0, 4)
                                        .map((technology) => (
                                            <li key={technology}>
                                                <Tag>{technology}</Tag>
                                            </li>
                                        ))}
                                </ul>
                            </td>
                            <td className="py-4 align-top">
                                <ProjectStatusBadge status={project.status} />
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

export function ProjectArchive({
    projects,
    facets,
    total,
    search,
    onSearchChange,
}: ProjectArchiveProps) {
    const { t } = useTranslation();
    const [query, setQuery] = useState(search.q ?? '');
    const debouncedSearch = useDebouncedCallback(
        (value: string) => onSearchChange({ q: value || undefined }),
        200,
    );

    const view = search.view ?? 'grid';
    const hasFilters = Boolean(search.q || search.tech || search.category);

    const handleQueryChange = (value: string): void => {
        setQuery(value);
        debouncedSearch(value);
    };

    const handleReset = (): void => {
        setQuery('');
        onSearchChange({ q: undefined, tech: undefined, category: undefined });
    };

    const categoryOptions = [
        { value: 'all' as CategoryFilter, label: t('category.all') },
        ...facets.categories.map((category) => ({
            value: category as CategoryFilter,
            label: t(`category.${category}`),
        })),
    ];

    return (
        <>
            <PageHeader
                index="04"
                label={t('nav.work')}
                version="releases/"
                title={t('projects.title')}
                intro={t('projects.intro')}
                aside={
                    <dl className="grid grid-cols-2 gap-4 border-t border-line pt-4 lg:border-t-0 lg:pt-0">
                        <div>
                            <dt className="eyebrow text-ink-subtle">
                                {t('table.project')}
                            </dt>
                            <dd className="ltr-isolate font-display text-5xl text-ink">
                                {total}
                            </dd>
                        </div>
                        <div>
                            <dt className="eyebrow text-ink-subtle">
                                {t('projects.techLabel')}
                            </dt>
                            <dd className="ltr-isolate font-display text-5xl text-ink">
                                {facets.technologies.length}
                            </dd>
                        </div>
                    </dl>
                }
            />

            <div className="shell py-12 md:py-16">
                <div className="mb-10 flex flex-col gap-5">
                    <div className="grid gap-4 md:grid-cols-[minmax(0,1fr)_14rem_12rem] md:items-end">
                        <SearchInput
                            id="project-search"
                            label={t('projects.searchLabel')}
                            placeholder={t('projects.searchPlaceholder')}
                            clearLabel={t('common.clear')}
                            value={query}
                            onChange={handleQueryChange}
                        />
                        <SelectField
                            id="project-tech"
                            label={t('projects.techLabel')}
                            value={search.tech ?? ''}
                            onChange={(event) =>
                                onSearchChange({
                                    tech: event.target.value || undefined,
                                })
                            }
                            options={[
                                { value: '', label: t('projects.allTech') },
                                ...facets.technologies.map((technology) => ({
                                    value: technology,
                                    label: technology,
                                })),
                            ]}
                        />
                        <SelectField
                            id="project-sort"
                            label={t('projects.sortLabel')}
                            value={search.sort ?? 'newest'}
                            onChange={(event) =>
                                onSearchChange({
                                    sort:
                                        event.target.value === 'oldest'
                                            ? 'oldest'
                                            : undefined,
                                })
                            }
                            options={[
                                { value: 'newest', label: t('sort.newest') },
                                { value: 'oldest', label: t('sort.oldest') },
                            ]}
                        />
                    </div>
                    <div className="flex flex-wrap items-center justify-between gap-4 border-b border-line pb-5">
                        <FilterGroup
                            label={t('work.filterLabel')}
                            options={categoryOptions}
                            value={search.category ?? 'all'}
                            onChange={(value) =>
                                onSearchChange({
                                    category:
                                        value === 'all' ? undefined : value,
                                })
                            }
                        />
                        <div className="flex items-center gap-4">
                            <p
                                aria-live="polite"
                                className="font-mono text-xs text-ink-subtle"
                            >
                                {t('common.results', {
                                    count: projects.length,
                                })}
                            </p>
                            <FilterGroup
                                label={t('projects.viewLabel')}
                                variant="segmented"
                                value={view}
                                onChange={(value) =>
                                    onSearchChange({
                                        view:
                                            value === 'table'
                                                ? 'table'
                                                : undefined,
                                    })
                                }
                                options={[
                                    {
                                        value: 'grid',
                                        label: t('view.grid'),
                                        icon: (
                                            <LayoutGrid
                                                aria-hidden
                                                className="size-3.5"
                                            />
                                        ),
                                    },
                                    {
                                        value: 'table',
                                        label: t('view.table'),
                                        icon: (
                                            <Table2
                                                aria-hidden
                                                className="size-3.5"
                                            />
                                        ),
                                    },
                                ]}
                            />
                        </div>
                    </div>
                </div>

                {projects.length === 0 && (
                    <EmptyState
                        title={t('empty.title')}
                        body={t('empty.body')}
                        actionLabel={hasFilters ? t('empty.reset') : undefined}
                        onAction={handleReset}
                    />
                )}

                {projects.length > 0 && view === 'table' && (
                    <ProjectTable projects={projects} />
                )}

                {projects.length > 0 && view === 'grid' && (
                    <motion.ul
                        layout
                        className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <AnimatePresence mode="popLayout" initial={false}>
                            {projects.map((project, index) => (
                                <motion.li
                                    key={project.id}
                                    layout
                                    initial={{ opacity: 0, y: 12 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    exit={{ opacity: 0, scale: 0.97 }}
                                    transition={{
                                        duration: 0.35,
                                        ease: [0.22, 1, 0.36, 1],
                                    }}
                                >
                                    <ProjectCard
                                        project={project}
                                        priority={index < 3}
                                    />
                                </motion.li>
                            ))}
                        </AnimatePresence>
                    </motion.ul>
                )}
            </div>
        </>
    );
}
