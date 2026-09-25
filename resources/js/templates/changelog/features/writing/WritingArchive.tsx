import { useState } from 'react';
import { ArticleRow } from '@/templates/changelog/components/content/ArticleRow';
import { EmptyState } from '@/templates/changelog/components/ui/EmptyState';
import { FilterGroup } from '@/templates/changelog/components/ui/FilterGroup';
import { PageHeader } from '@/templates/changelog/components/ui/PageHeader';
import { SearchInput } from '@/templates/changelog/components/ui/SearchInput';
import { useDebouncedCallback } from '@/hooks/useDebouncedCallback';
import { useTranslation } from '@/hooks/useTranslation';
import type { WritingArchivePageProps } from '@/templates/types';

export function WritingArchive({
    articles,
    allArticles,
    tags,
    search,
    onSearchChange,
}: WritingArchivePageProps) {
    const { t } = useTranslation();
    const { q: query, tag } = search;
    const [inputValue, setInputValue] = useState(query ?? '');
    const debouncedSearch = useDebouncedCallback(
        (value: string) => onSearchChange({ q: value || undefined }),
        200,
    );
    const totalMinutes = allArticles.reduce(
        (total, article) => total + article.readingMinutes,
        0,
    );

    const handleQueryChange = (value: string): void => {
        setInputValue(value);
        debouncedSearch(value);
    };

    const handleReset = (): void => {
        setInputValue('');
        onSearchChange({ q: undefined, tag: undefined });
    };

    const tagOptions = [
        {
            value: '',
            label: t('archiveWriting.allTags'),
            count: allArticles.length,
        },
        ...tags.map((value) => ({
            value,
            label: `#${value}`,
            count: allArticles.filter((article) => article.tags.includes(value))
                .length,
        })),
    ];

    return (
        <>
            <PageHeader
                index="07"
                label={t('nav.writing')}
                version="docs/"
                title={t('archiveWriting.title')}
                intro={t('archiveWriting.intro')}
                aside={
                    <dl className="grid grid-cols-2 gap-4 border-t border-line pt-4 lg:border-t-0 lg:pt-0">
                        <div>
                            <dt className="eyebrow text-ink-subtle">
                                {t('archiveWriting.articles')}
                            </dt>
                            <dd className="ltr-isolate font-display text-5xl text-ink">
                                {allArticles.length}
                            </dd>
                        </div>
                        <div>
                            <dt className="eyebrow text-ink-subtle">
                                {t('archiveWriting.minutes')}
                            </dt>
                            <dd className="ltr-isolate font-display text-5xl text-ink">
                                {totalMinutes}
                            </dd>
                        </div>
                    </dl>
                }
            />
            <div className="shell py-12 md:py-16">
                <div className="mb-8 flex flex-col gap-5 border-b border-line pb-6">
                    <SearchInput
                        id="article-search"
                        label={t('archiveWriting.searchLabel')}
                        placeholder={t('archiveWriting.searchPlaceholder')}
                        clearLabel={t('common.clear')}
                        value={inputValue}
                        onChange={handleQueryChange}
                        className="max-w-xl"
                    />
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <FilterGroup
                            label={t('archiveWriting.tagsLabel')}
                            options={tagOptions}
                            value={tag ?? ''}
                            onChange={(value) =>
                                onSearchChange({ tag: value || undefined })
                            }
                        />
                        <p
                            aria-live="polite"
                            className="font-mono text-xs text-ink-subtle"
                        >
                            {t('common.results', { count: articles.length })}
                        </p>
                    </div>
                </div>
                {articles.length === 0 ? (
                    <EmptyState
                        title={t('empty.title')}
                        body={t('empty.body')}
                        actionLabel={t('empty.reset')}
                        onAction={handleReset}
                    />
                ) : (
                    <div>
                        {articles.map((article) => (
                            <ArticleRow
                                key={article.id}
                                article={article}
                                headingLevel="h2"
                            />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
