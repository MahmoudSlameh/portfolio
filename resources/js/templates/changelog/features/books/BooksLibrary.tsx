import { useRouterState } from '@/lib/router';
import { useEffect, useState } from 'react';
import {
    Bookshelf,
    type ShelfMode,
} from '@/templates/changelog/components/content/Bookshelf';
import {
    ShelfControls,
    type StatusFilter,
} from '@/templates/changelog/components/content/ShelfControls';
import { EmptyState } from '@/templates/changelog/components/ui/EmptyState';
import { FilterGroup } from '@/templates/changelog/components/ui/FilterGroup';
import { PageHeader } from '@/templates/changelog/components/ui/PageHeader';
import { useTranslation } from '@/hooks/useTranslation';
import { cn, formatNumber } from '@/lib/utils';
import type { BooksPageProps } from '@/templates/types';
import type { BookCategory } from '@/types/content';
import { ReadingChart } from './ReadingChart';

type CategoryFilter = BookCategory | 'all';

export function BooksLibrary({
    books,
    stats,
    search,
    onSearchChange,
}: BooksPageProps) {
    const { t } = useTranslation();
    const hash = useRouterState({ select: (state) => state.location.hash });
    const [selectedId, setSelectedId] = useState<string | null>(null);

    const status: StatusFilter = search.status ?? 'all';
    const category: CategoryFilter = search.category ?? 'all';
    const mode: ShelfMode = search.view ?? 'shelf';

    const booksInCategory =
        category === 'all'
            ? books
            : books.filter((book) => book.category === category);
    const visibleBooks =
        status === 'all'
            ? booksInCategory
            : booksInCategory.filter((book) => book.status === status);

    useEffect(() => {
        if (!hash.startsWith('book-')) return;
        const target = books.find((book) => `book-${book.slug}` === hash);
        if (!target) return;
        setSelectedId(target.id);
        document
            .getElementById(hash)
            ?.scrollIntoView({ block: 'nearest', inline: 'center' });
    }, [hash, books]);

    const handleReset = (): void =>
        onSearchChange({ status: undefined, category: undefined });

    const categoryOptions = [
        { value: 'all' as CategoryFilter, label: t('bookCategory.all') },
        ...stats.categories.map((value) => ({
            value: value as CategoryFilter,
            label: t(`bookCategory.${value}`),
            count: books.filter((book) => book.category === value).length,
        })),
    ];

    const statItems = [
        { label: t('booksPage.read'), value: formatNumber(stats.read) },
        { label: t('booksPage.reading'), value: formatNumber(stats.reading) },
        { label: t('booksPage.queued'), value: formatNumber(stats.queued) },
        { label: t('booksPage.pages'), value: formatNumber(stats.pagesRead) },
        {
            label: t('booksPage.rating'),
            value: `${stats.averageRating.toFixed(1)}/5`,
        },
    ];

    return (
        <>
            <PageHeader
                index="08"
                label={t('nav.books')}
                version="vendor/"
                title={t('booksPage.title')}
                intro={t('booksPage.intro')}
            />

            <div className="shell py-12 md:py-16">
                <section
                    aria-label={t('booksPage.statsLabel')}
                    className="mb-16 grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,26rem)]"
                >
                    <dl className="grid grid-cols-2 content-start gap-px border border-line bg-line sm:grid-cols-3">
                        {statItems.map((item, index) => (
                            <div
                                key={item.label}
                                className={cn(
                                    'bg-paper p-5 md:p-6',
                                    index === 0 && 'col-span-2 sm:col-span-1',
                                    index === statItems.length - 1 &&
                                        'sm:col-span-2',
                                )}
                            >
                                <dt className="eyebrow text-ink-subtle">
                                    {item.label}
                                </dt>
                                <dd className="ltr-isolate mt-3 font-display text-5xl leading-none text-ink">
                                    {item.value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                    <ReadingChart perYear={stats.perYear} />
                </section>

                <div className="mb-10 flex flex-col gap-5 border-b border-line pb-6">
                    <ShelfControls
                        books={booksInCategory}
                        status={status}
                        onStatusChange={(value) =>
                            onSearchChange({
                                status: value === 'all' ? undefined : value,
                            })
                        }
                        mode={mode}
                        onModeChange={(value) =>
                            onSearchChange({
                                view: value === 'grid' ? 'grid' : undefined,
                            })
                        }
                    />
                    <FilterGroup
                        label={t('books.categoryLabel')}
                        options={categoryOptions}
                        value={category}
                        onChange={(value) =>
                            onSearchChange({
                                category: value === 'all' ? undefined : value,
                            })
                        }
                    />
                    <p
                        aria-live="polite"
                        className="font-mono text-xs text-ink-subtle"
                    >
                        {t('common.results', { count: visibleBooks.length })}
                    </p>
                </div>

                {visibleBooks.length > 0 ? (
                    <Bookshelf
                        books={visibleBooks}
                        mode={mode}
                        selectedId={selectedId}
                        onSelect={setSelectedId}
                    />
                ) : (
                    <EmptyState
                        title={t('empty.title')}
                        body={t('empty.body')}
                        actionLabel={t('empty.reset')}
                        onAction={handleReset}
                    />
                )}
            </div>
        </>
    );
}
