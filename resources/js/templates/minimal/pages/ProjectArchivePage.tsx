import { useDebouncedCallback, useTranslation } from '@/kit';
import type { ProjectArchivePageProps } from '@/templates/types';
import { ProjectList } from '../components/Lists';
import { EmptyState, PageHeader } from '../components/Section';

/**
 * /projects. Filtering happens on the server: call `onSearchChange(patch)` with the filters to
 * change (it reloads only the listed props through `useArchiveFilters`). `search` holds the
 * current, validated filters; invalid values from the URL are dropped by the server.
 */
export function ProjectArchivePage({
    projects,
    facets,
    total,
    search,
    onSearchChange,
}: ProjectArchivePageProps) {
    const { t } = useTranslation();
    // Typing should not send one request per keystroke.
    const setQuery = useDebouncedCallback(
        (q: string) => onSearchChange({ q }),
        300,
    );

    return (
        <>
            <PageHeader title={t('nav.work')} />
            <div className="mn-container mt-8 grid gap-3 sm:grid-cols-3">
                <input
                    type="search"
                    defaultValue={search.q ?? ''}
                    onChange={(event) => setQuery(event.target.value)}
                    placeholder="Search"
                    aria-label="Search projects"
                    className="mn-field"
                />
                <select
                    value={search.tech ?? ''}
                    onChange={(event) =>
                        onSearchChange({ tech: event.target.value })
                    }
                    aria-label="Technology"
                    className="mn-field"
                >
                    <option value="">All technologies</option>
                    {facets.technologies.map((tech) => (
                        <option key={tech} value={tech}>
                            {tech}
                        </option>
                    ))}
                </select>
                <select
                    value={search.category ?? ''}
                    onChange={(event) =>
                        onSearchChange({
                            category: (event.target.value ||
                                undefined) as typeof search.category,
                        })
                    }
                    aria-label="Category"
                    className="mn-field"
                >
                    <option value="">All categories</option>
                    {facets.categories.map((category) => (
                        <option key={category} value={category}>
                            {category}
                        </option>
                    ))}
                </select>
            </div>
            <div className="mn-container mt-8">
                <p className="mb-3 text-sm text-ink-subtle">
                    {projects.length} of {total}
                </p>
                {projects.length > 0 ? (
                    <ProjectList projects={projects} />
                ) : (
                    <EmptyState>No projects match these filters.</EmptyState>
                )}
            </div>
        </>
    );
}
