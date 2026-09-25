import { useState } from 'react';
import { useDebouncedCallback } from '@/hooks/useDebouncedCallback';
import { useTranslation } from '@/hooks/useTranslation';
import type { WritingArchivePageProps } from '@/templates/types';
import { usePlaygroundCopy } from '../copy';
import { ArticleCard } from '../components/ArticleCard';
import { ChipGroup, type ChipOption } from '../components/ChipGroup';
import { EmptyNote } from '../components/EmptyNote';
import { PageHero } from '../components/PageHero';
import { Reveal } from '../components/Reveal';
import { SearchBox } from '../components/SearchBox';
import { Sticker } from '../components/Sticker';

export function WritingArchivePage({
    articles,
    allArticles,
    tags,
    search,
    onSearchChange,
}: WritingArchivePageProps) {
    const { t } = useTranslation();
    const p = usePlaygroundCopy();
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

    const tagOptions: ChipOption<string>[] = [
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
            <PageHero
                kicker={p('writing.kicker')}
                title={t('archiveWriting.title')}
                intro={t('archiveWriting.intro')}
                pop="pink"
                badge={
                    <>
                        <Sticker pop="yellow" tilt={4}>
                            {allArticles.length} ·{' '}
                            {t('archiveWriting.articles')}
                        </Sticker>
                        <Sticker pop="green" tilt={-3}>
                            {totalMinutes} · {t('archiveWriting.minutes')}
                        </Sticker>
                    </>
                }
            />
            <div className="pg-shell">
                <div className="pg-card mb-10 flex flex-col gap-5 p-5 md:p-6">
                    <SearchBox
                        label={t('archiveWriting.searchLabel')}
                        placeholder={t('archiveWriting.searchPlaceholder')}
                        value={query}
                        onChange={handleQueryChange}
                    />
                    <ChipGroup
                        label={t('archiveWriting.tagsLabel')}
                        options={tagOptions}
                        value={search.tag ?? ''}
                        onChange={(value) =>
                            onSearchChange({ tag: value || undefined })
                        }
                    />
                    <p
                        aria-live="polite"
                        className="font-mono text-xs font-bold text-ink-subtle"
                    >
                        {t('common.results', { count: articles.length })}
                    </p>
                </div>

                {articles.length === 0 ? (
                    <EmptyNote
                        onReset={handleReset}
                        resetLabel={t('empty.reset')}
                    />
                ) : (
                    <ul className="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        {articles.map((article, index) => (
                            <Reveal
                                as="li"
                                key={article.id}
                                delay={(index % 3) * 80}
                            >
                                <ArticleCard article={article} index={index} />
                            </Reveal>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}
