import { Link } from '@tanstack/react-router';
import { useState } from 'react';
import { useDebouncedCallback } from '@/hooks/useDebouncedCallback';
import { useTranslation } from '@/hooks/useTranslation';
import { formatDate } from '@/lib/utils';
import type { WritingArchivePageProps } from '@/templates/types';
import { ChipGroup } from '../components/Chip';
import { EmptyState } from '../components/EmptyState';
import { PageHeader } from '../components/PageHeader';
import { SearchField } from '../components/SearchField';

export function WritingArchivePage({ articles, allArticles, tags, search, onSearchChange }: WritingArchivePageProps) {
  const { t, locale } = useTranslation();
  const [query, setQuery] = useState(search.q ?? '');
  const debouncedSearch = useDebouncedCallback((value: string) => onSearchChange({ q: value || undefined }), 200);
  const totalMinutes = allArticles.reduce((total, article) => total + article.readingMinutes, 0);

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
            <span className="border border-tm-border px-3 py-1 text-tm-300">
              <span className="ltr-isolate text-ink">{allArticles.length}</span> {t('archiveWriting.articles')}
            </span>
            <span className="border border-tm-border px-3 py-1 text-tm-300">
              <span className="ltr-isolate text-ink">{totalMinutes}</span> {t('archiveWriting.minutes')}
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
            onChange={(value) => onSearchChange({ tag: value || undefined })}
            options={[
              { value: '', label: t('archiveWriting.allTags'), count: allArticles.length },
              ...tags.map((value) => ({
                value,
                label: `#${value}`,
                count: allArticles.filter((article) => article.tags.includes(value)).length,
              })),
            ]}
          />
          <p aria-live="polite" className="mb-0 text-sm text-tm-400">
            {t('common.results', { count: articles.length })}
          </p>
        </div>
      </PageHeader>

      <div className="tm-container pt-8">
        {articles.length === 0 ? (
          <EmptyState onReset={handleReset} />
        ) : (
          <ul className="tm-box divide-y divide-tm-border">
            {articles.map((article) => (
              <li key={article.id} className="group relative grid grid-cols-1 gap-3 p-6 transition-colors hover:bg-[var(--signal-soft)] md:grid-cols-[11rem_minmax(0,1fr)] md:gap-8 md:p-8">
                <p className="mb-0 text-tm-300">
                  <time dateTime={article.publishedAt}>{formatDate(article.publishedAt, locale)}</time>
                  <span className="block text-sm text-tm-400">{t('writing.minRead', { count: article.readingMinutes })}</span>
                </p>
                <div lang="en" className="min-w-0">
                  <h2 className="mb-2 text-xl font-medium">
                    <Link to="/writing/$slug" params={{ slug: article.slug }} className="after:absolute after:inset-0 group-hover:text-[#62a92b]">
                      {article.title}
                    </Link>
                  </h2>
                  <p className="mb-3 text-tm-body">{article.excerpt}</p>
                  <p className="mb-0 text-sm text-tm-secondary">{article.tags.map((tag) => `#${tag}`).join('  ')}</p>
                </div>
              </li>
            ))}
          </ul>
        )}
      </div>
    </>
  );
}
