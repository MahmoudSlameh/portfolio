import { Link } from '@/lib/router';
import { useState } from 'react';
import { useDebouncedCallback } from '@/hooks/useDebouncedCallback';
import { useTranslation } from '@/hooks/useTranslation';
import { formatDate } from '@/lib/utils';
import type { WritingArchivePageProps } from '@/templates/types';
import { ChipGroup } from '../components/Chip';
import { EmptyState } from '../components/EmptyState';
import { PageHeader } from '../components/PageHeader';
import { SearchField } from '../components/SearchField';

export function WritingArchivePage({
    articles,
    allArticles,
    tags,
    search,
    onSearchChange,
}: WritingArchivePageProps) {
    const { t } = useTranslation();
    const [query, setQuery] = useState(search.q ?? '');
    const debouncedSearch = useDebouncedCallback(
        (value: string) => onSearchChange({ q: value || undefined }),
        200,
    );
    const totalMinutes = allArticles.reduce(
        (total, article) => total + article.readingMinutes,
        0,
    );

    const handleQueryChange = (value: string): void => {
        setQuery(value);
        debouncedSearch(value);
    };

    const handleReset = (): void => {
        setQuery('');
        onSearchChange({ q: undefined, tag: undefined });
    };

    return (
        <>
            <PageHeader
                kicker={t('nav.writing')}
                title={t('archiveWriting.title')}
                intro={t('archiveWriting.intro')}
                aside={
                    <>
                        <span className="border-tm-border text-tm-300 border px-3 py-1">
                            <span className="ltr-isolate text-ink">
                                {allArticles.length}
                            </span>{' '}
                            {t('archiveWriting.articles')}
                        </span>
                        <span className="border-tm-border text-tm-300 border px-3 py-1">
                            <span className="ltr-isolate text-ink">
                                {totalMinutes}
                            </span>{' '}
                            {t('archiveWriting.minutes')}
                        </span>
                    </>
                }
            >
                <div className="flex flex-col gap-5">
                    <SearchField
                        label={t('archiveWriting.searchLabel')}
                        placeholder={t('archiveWriting.searchPlaceholder')}
                        value={query}
                        onChange={handleQueryChange}
                    />
                    <ChipGroup
                        label={t('archiveWriting.tagsLabel')}
                        value={search.tag ?? ''}
                        onChange={(value) =>
                            onSearchChange({ tag: value || undefined })
                        }
                        options={[
                            {
                                value: '',
                                label: t('archiveWriting.allTags'),
                                count: allArticles.length,
                            },
                            ...tags.map((value) => ({
                                value,
                                label: `#${value}`,
                                count: allArticles.filter((article) =>
                                    article.tags.includes(value),
                                ).length,
                            })),
                        ]}
                    />
                    <p aria-live="polite" className="text-tm-400 mb-0 text-sm">
                        {t('common.results', { count: articles.length })}
                    </p>
                </div>
            </PageHeader>

            <div className="tm-container pt-8">
                {articles.length === 0 ? (
                    <EmptyState onReset={handleReset} />
                ) : (
                    <ul className="tm-box divide-tm-border divide-y">
                        {articles.map((article) => (
                            <li
                                key={article.id}
                                className="group relative grid grid-cols-1 gap-3 p-6 transition-colors hover:bg-[var(--signal-soft)] md:grid-cols-[11rem_minmax(0,1fr)] md:gap-8 md:p-8"
                            >
                                <p className="text-tm-300 mb-0">
                                    <time dateTime={article.publishedAt}>
                                        {formatDate(article.publishedAt)}
                                    </time>
                                    <span className="text-tm-400 block text-sm">
                                        {t('writing.minRead', {
                                            count: article.readingMinutes,
                                        })}
                                    </span>
                                </p>
                                <div lang="en" className="min-w-0">
                                    <h2 className="mb-2 text-xl font-medium">
                                        <Link
                                            to="/writing/$slug"
                                            params={{ slug: article.slug }}
                                            className="group-hover:text-[#62a92b] after:absolute after:inset-0"
                                        >
                                            {article.title}
                                        </Link>
                                    </h2>
                                    <p className="text-tm-body mb-3">
                                        {article.excerpt}
                                    </p>
                                    <p className="text-tm-secondary mb-0 text-sm">
                                        {article.tags
                                            .map((tag) => `#${tag}`)
                                            .join('  ')}
                                    </p>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}
