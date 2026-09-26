import { useDebouncedCallback, useTranslation } from '@/kit';
import type { WritingArchivePageProps } from '@/templates/types';
import { ArticleList } from '../components/Lists';
import { EmptyState, PageHeader } from '../components/Section';

/**
 * /writing. `articles` is the filtered list, `allArticles` the unfiltered one (useful for counts),
 * `tags` every tag in use. Filters go through `onSearchChange` like the project archive.
 */
export function WritingArchivePage({
    articles,
    tags,
    search,
    onSearchChange,
}: WritingArchivePageProps) {
    const { t } = useTranslation();
    const setQuery = useDebouncedCallback(
        (q: string) => onSearchChange({ q }),
        300,
    );

    return (
        <>
            <PageHeader title={t('nav.writing')} intro={t('writing.intro')} />
            <div className="mn-container mt-8 grid gap-4">
                <input
                    type="search"
                    defaultValue={search.q ?? ''}
                    onChange={(event) => setQuery(event.target.value)}
                    placeholder="Search"
                    aria-label="Search writing"
                    className="mn-field"
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
                                className="mn-chip"
                            >
                                {tag}
                            </button>
                        ))}
                    </div>
                )}
            </div>
            <div className="mn-container mt-8">
                {articles.length > 0 ? (
                    <ArticleList articles={articles} />
                ) : (
                    <EmptyState>No articles match these filters.</EmptyState>
                )}
            </div>
        </>
    );
}
