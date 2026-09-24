import { Link } from '@/lib/router';
import { ArrowDownUp, LayoutGrid, List } from 'lucide-react';
import { useId, useState } from 'react';
import { useDebouncedCallback } from '@/hooks/useDebouncedCallback';
import { useTranslation } from '@/hooks/useTranslation';
import { cn } from '@/lib/utils';
import { ResponsiveImage } from '@/shared/ui/ResponsiveImage';
import type { ProjectArchivePageProps } from '@/templates/types';
import type { Project, ProjectCategory } from '@/types/content';
import { usePlaygroundCopy } from '../copy';
import { categoryPop, popBg } from '../lib/pops';
import { ChipGroup, type ChipOption } from '../components/ChipGroup';
import { EmptyNote } from '../components/EmptyNote';
import { PageHero } from '../components/PageHero';
import { popButtonClasses } from '../components/PopButton';
import { Reveal } from '../components/Reveal';
import { SearchBox } from '../components/SearchBox';
import { Sticker } from '../components/Sticker';

type CategoryFilter = ProjectCategory | 'all';

function ProjectTile({ project, index }: { project: Project; index: number }) {
    const { t } = useTranslation();
    const pop = categoryPop[project.category];

    return (
        <Reveal as="li" delay={(index % 3) * 80}>
            <Link
                to="/projects/$slug"
                params={{ slug: project.slug }}
                className="pg-card pg-press group flex h-full flex-col overflow-hidden"
            >
                <span className="border-edge relative block overflow-hidden border-b-2">
                    <ResponsiveImage
                        image={project.cover}
                        sizes="(min-width: 80rem) 28vw, (min-width: 48rem) 45vw, 100vw"
                        className="aspect-[16/10] transition-transform duration-500 group-hover:scale-105"
                    />
                    <span className="absolute start-3 top-3">
                        <Sticker pop={pop} tilt={-5}>
                            {t(`category.${project.category}`)}
                        </Sticker>
                    </span>
                    <span className="ltr-isolate border-edge bg-raised text-ink absolute end-3 bottom-3 rounded-full border-2 px-2.5 font-mono text-xs font-bold">
                        {project.year}
                    </span>
                </span>
                <span className="flex flex-1 flex-col gap-3 p-5">
                    <span
                        lang="en"
                        className="pg-display pg-keep-case text-ink text-3xl"
                    >
                        {project.title}
                    </span>
                    <span
                        lang="en"
                        className="text-ink-muted text-[0.9375rem] leading-relaxed"
                    >
                        {project.tagline}
                    </span>
                    <span className="mt-auto flex flex-wrap gap-1.5 pt-2">
                        {project.stack.slice(0, 4).map((tech) => (
                            <span key={tech} className="pg-tag text-ink">
                                {tech}
                            </span>
                        ))}
                    </span>
                </span>
            </Link>
        </Reveal>
    );
}

function ProjectTable({ projects }: { projects: Project[] }) {
    const { t } = useTranslation();

    return (
        <div className="pg-card overflow-x-auto">
            <table className="w-full min-w-[44rem] border-collapse text-start">
                <thead>
                    <tr className="border-edge bg-ink text-paper border-b-2">
                        {(
                            [
                                'table.year',
                                'table.project',
                                'table.category',
                                'table.stack',
                                'table.status',
                            ] as const
                        ).map((key) => (
                            <th
                                key={key}
                                scope="col"
                                className="pg-label px-5 py-3 text-start"
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
                            className="border-edge hover:bg-pop-yellow/40 border-b-2 last:border-b-0"
                        >
                            <td className="ltr-isolate text-ink px-5 py-4 font-mono text-sm font-bold">
                                {project.year}
                            </td>
                            <th scope="row" className="px-5 py-4 text-start">
                                <Link
                                    to="/projects/$slug"
                                    params={{ slug: project.slug }}
                                    lang="en"
                                    className="font-display text-ink hover:decoration-pop-blue text-lg font-extrabold underline decoration-2 underline-offset-4"
                                >
                                    {project.title}
                                </Link>
                            </th>
                            <td className="px-5 py-4">
                                <span
                                    className={cn(
                                        'pg-tag text-on-pop',
                                        popBg[categoryPop[project.category]],
                                    )}
                                >
                                    {t(`category.${project.category}`)}
                                </span>
                            </td>
                            <td
                                lang="en"
                                className="text-ink-muted px-5 py-4 text-sm"
                            >
                                {project.stack.slice(0, 3).join(' · ')}
                            </td>
                            <td className="text-ink px-5 py-4 text-sm font-semibold">
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
    const p = usePlaygroundCopy();
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

    const categoryOptions: ChipOption<CategoryFilter>[] = [
        { value: 'all', label: t('category.all') },
        ...facets.categories.map((value) => ({
            value,
            label: t(`category.${value}`),
        })),
    ];

    return (
        <>
            <PageHero
                kicker={t('nav.work')}
                title={t('projects.title')}
                intro={t('projects.intro')}
                pop="blue"
                badge={
                    <Sticker pop="green" tilt={4}>
                        {p('page.count', { count: total })}
                    </Sticker>
                }
            />

            <div className="pg-shell">
                <div className="pg-card mb-10 flex flex-col gap-5 p-5 md:p-6">
                    <SearchBox
                        label={t('projects.searchLabel')}
                        placeholder={t('projects.searchPlaceholder')}
                        value={query}
                        onChange={handleQueryChange}
                    />
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <ChipGroup
                            label={t('work.filterLabel')}
                            options={categoryOptions}
                            value={category}
                            onChange={(value) =>
                                onSearchChange({
                                    category:
                                        value === 'all' ? undefined : value,
                                })
                            }
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
                                className="pg-input h-11 min-h-0 w-auto cursor-pointer py-0 pe-9 text-sm font-semibold"
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
                                className={popButtonClasses({
                                    tone: 'plain',
                                    size: 'sm',
                                })}
                            >
                                <ArrowDownUp
                                    aria-hidden
                                    className="size-4"
                                    strokeWidth={2.5}
                                />
                                {t(
                                    sort === 'newest'
                                        ? 'sort.newest'
                                        : 'sort.oldest',
                                )}
                            </button>
                            <div
                                role="group"
                                aria-label={t('projects.viewLabel')}
                                className="border-edge bg-raised flex rounded-full border-2 p-0.5"
                            >
                                {(['grid', 'table'] as const).map((mode) => {
                                    const Icon =
                                        mode === 'grid' ? LayoutGrid : List;
                                    return (
                                        <button
                                            key={mode}
                                            type="button"
                                            aria-pressed={view === mode}
                                            onClick={() =>
                                                onSearchChange({
                                                    view:
                                                        mode === 'grid'
                                                            ? undefined
                                                            : mode,
                                                })
                                            }
                                            className="text-ink aria-pressed:bg-ink aria-pressed:text-paper inline-flex h-9 items-center gap-1.5 rounded-full px-3 text-sm font-bold"
                                        >
                                            <Icon
                                                aria-hidden
                                                className="size-4"
                                                strokeWidth={2.5}
                                            />
                                            {p(
                                                mode === 'grid'
                                                    ? 'projects.cards'
                                                    : 'projects.list',
                                            )}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                    <p
                        aria-live="polite"
                        className="text-ink-subtle font-mono text-xs font-bold"
                    >
                        {t('common.results', { count: projects.length })}
                    </p>
                </div>

                {projects.length === 0 && (
                    <EmptyNote
                        onReset={handleReset}
                        resetLabel={t('empty.reset')}
                    />
                )}
                {projects.length > 0 && view === 'grid' && (
                    <ul className="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        {projects.map((project, index) => (
                            <ProjectTile
                                key={project.id}
                                project={project}
                                index={index}
                            />
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
