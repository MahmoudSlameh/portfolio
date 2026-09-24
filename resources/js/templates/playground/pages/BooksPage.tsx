import { useRouterState } from '@/lib/router';
import { Layers, LayoutGrid } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import type { BookStats } from '@/lib/content';
import { cn, formatNumber } from '@/lib/utils';
import type { BooksPageProps } from '@/templates/types';
import type { Book, BookCategory, ReadingStatus } from '@/types/content';
import { usePlaygroundCopy } from '../copy';
import { popBg, popForIndex } from '../lib/pops';
import { BookDetail } from '../components/BookDetail';
import { BookPile } from '../components/BookPile';
import { ChipGroup, type ChipOption } from '../components/ChipGroup';
import { EmptyNote } from '../components/EmptyNote';
import { PageHero } from '../components/PageHero';
import { PopBookCover } from '../components/PopBookCover';

type StatusFilter = ReadingStatus | 'all';
type CategoryFilter = BookCategory | 'all';

const STATUSES: ReadingStatus[] = ['reading', 'read', 'to-read'];

function StatsBoard({ stats }: { stats: BookStats }) {
    const { t } = useTranslation();
    const items = [
        { label: t('booksPage.read'), value: formatNumber(stats.read) },
        { label: t('booksPage.reading'), value: formatNumber(stats.reading) },
        { label: t('booksPage.queued'), value: formatNumber(stats.queued) },
        { label: t('booksPage.pages'), value: formatNumber(stats.pagesRead) },
        { label: t('booksPage.rating'), value: stats.averageRating.toFixed(1) },
    ];
    const maxCount = Math.max(...stats.perYear.map((entry) => entry.count), 1);

    return (
        <section
            aria-label={t('booksPage.statsLabel')}
            className="mb-12 grid gap-5 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]"
        >
            <dl className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                {items.map((item, index) => (
                    <div
                        key={item.label}
                        className={cn(
                            'pg-card flex flex-col-reverse justify-end gap-2 p-5 text-on-pop',
                            popBg[popForIndex(index)],
                        )}
                    >
                        <dt className="text-sm font-bold">{item.label}</dt>
                        <dd className="pg-display ltr-isolate self-start text-4xl">
                            {item.value}
                        </dd>
                    </div>
                ))}
            </dl>
            <figure className="pg-card flex flex-col gap-4 p-5">
                <figcaption className="pg-label text-ink">
                    {t('booksPage.perYear')}
                </figcaption>
                <ol className="flex h-44 items-end gap-2" dir="ltr">
                    {stats.perYear.map((entry, index) => (
                        <li
                            key={entry.year}
                            aria-label={t('booksPage.perYearCount', {
                                count: entry.count,
                                year: entry.year,
                            })}
                            className="flex h-full flex-1 flex-col items-center justify-end gap-2"
                        >
                            <span
                                aria-hidden
                                className="font-mono text-xs font-bold text-ink"
                            >
                                {entry.count}
                            </span>
                            <span
                                aria-hidden
                                style={{
                                    height: `${Math.max(6, (entry.count / maxCount) * 100)}%`,
                                }}
                                className={cn(
                                    'w-full rounded-t-lg border-2 border-edge transition-[height] duration-700',
                                    popBg[popForIndex(index)],
                                )}
                            />
                            <span
                                aria-hidden
                                className="font-mono text-[0.625rem] font-bold text-ink-subtle"
                            >
                                {String(entry.year).slice(2)}
                            </span>
                        </li>
                    ))}
                </ol>
            </figure>
        </section>
    );
}

function CoverGrid({
    books,
    selectedId,
    onSelect,
}: {
    books: Book[];
    selectedId: string | null;
    onSelect: (bookId: string) => void;
}) {
    const { t } = useTranslation();

    return (
        <ul
            aria-label={t('section.books')}
            className="grid grid-cols-2 gap-5 sm:grid-cols-3 xl:grid-cols-4"
        >
            {books.map((book) => (
                <li key={book.id}>
                    <button
                        type="button"
                        id={`book-${book.slug}`}
                        aria-pressed={book.id === selectedId}
                        onClick={() => onSelect(book.id)}
                        className={cn(
                            'pg-press block w-full scroll-mt-28 rounded-xl shadow-[var(--pg-shadow)]',
                            book.id === selectedId &&
                                'ring-4 ring-pop-blue ring-offset-4 ring-offset-paper',
                        )}
                    >
                        <PopBookCover book={book} />
                    </button>
                    <p
                        lang="en"
                        className="mt-3 text-sm leading-snug font-bold text-ink"
                    >
                        {book.title}
                    </p>
                    <p
                        lang="en"
                        className="text-xs font-semibold text-ink-subtle"
                    >
                        {book.author}
                    </p>
                </li>
            ))}
        </ul>
    );
}

export function BooksPage({
    books,
    stats,
    search,
    onSearchChange,
}: BooksPageProps) {
    const { t } = useTranslation();
    const p = usePlaygroundCopy();
    const hash = useRouterState({ select: (state) => state.location.hash });
    const [selectedId, setSelectedId] = useState<string | null>(null);

    const status: StatusFilter = search.status ?? 'all';
    const category: CategoryFilter = search.category ?? 'all';
    const view = search.view ?? 'shelf';

    const inCategory =
        category === 'all'
            ? books
            : books.filter((book) => book.category === category);
    const visible =
        status === 'all'
            ? inCategory
            : inCategory.filter((book) => book.status === status);
    const selected = books.find((book) => book.id === selectedId) ?? null;

    useEffect(() => {
        if (!hash.startsWith('book-')) return;
        const target = books.find((book) => `book-${book.slug}` === hash);
        if (!target) return;
        setSelectedId(target.id);
        window.requestAnimationFrame(() =>
            document
                .getElementById('book-detail')
                ?.scrollIntoView({ block: 'center' }),
        );
    }, [hash, books]);

    const statusOptions: ChipOption<StatusFilter>[] = [
        {
            value: 'all',
            label: t('readingStatus.all'),
            count: inCategory.length,
        },
        ...STATUSES.map((value) => ({
            value,
            label: t(`readingStatus.${value}`),
            count: inCategory.filter((book) => book.status === value).length,
        })),
    ];

    const categoryOptions: ChipOption<CategoryFilter>[] = [
        { value: 'all', label: t('bookCategory.all') },
        ...stats.categories.map((value) => ({
            value,
            label: t(`bookCategory.${value}`),
        })),
    ];

    const handleSelect = (bookId: string): void =>
        setSelectedId((current) => (current === bookId ? null : bookId));

    const handleReset = (): void =>
        onSearchChange({ status: undefined, category: undefined });

    return (
        <>
            <PageHero
                kicker={p('books.kicker')}
                title={t('booksPage.title')}
                intro={t('booksPage.intro')}
                pop="green"
            />
            <div className="pg-shell">
                <StatsBoard stats={stats} />

                <div className="pg-card mb-8 flex flex-col gap-4 p-5 md:p-6">
                    <ChipGroup
                        label={t('books.filterLabel')}
                        options={statusOptions}
                        value={status}
                        onChange={(value) =>
                            onSearchChange({
                                status: value === 'all' ? undefined : value,
                            })
                        }
                    />
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <ChipGroup
                            label={t('books.categoryLabel')}
                            options={categoryOptions}
                            value={category}
                            onChange={(value) =>
                                onSearchChange({
                                    category:
                                        value === 'all' ? undefined : value,
                                })
                            }
                        />
                        <div
                            role="group"
                            aria-label={t('books.viewMode')}
                            className="flex rounded-full border-2 border-edge bg-raised p-0.5"
                        >
                            {(['shelf', 'grid'] as const).map((mode) => {
                                const Icon =
                                    mode === 'shelf' ? Layers : LayoutGrid;
                                return (
                                    <button
                                        key={mode}
                                        type="button"
                                        aria-pressed={view === mode}
                                        onClick={() =>
                                            onSearchChange({
                                                view:
                                                    mode === 'shelf'
                                                        ? undefined
                                                        : mode,
                                            })
                                        }
                                        className="inline-flex h-9 items-center gap-1.5 rounded-full px-3 text-sm font-bold text-ink aria-pressed:bg-ink aria-pressed:text-paper"
                                    >
                                        <Icon
                                            aria-hidden
                                            className="size-4"
                                            strokeWidth={2.5}
                                        />
                                        {p(
                                            mode === 'shelf'
                                                ? 'books.viewStack'
                                                : 'books.viewGrid',
                                        )}
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                </div>

                {visible.length === 0 ? (
                    <EmptyNote
                        onReset={handleReset}
                        resetLabel={t('empty.reset')}
                    />
                ) : (
                    <div className="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_24rem]">
                        <div className="min-w-0">
                            {view === 'shelf' ? (
                                <div className="pg-card overflow-hidden bg-surface">
                                    <BookPile
                                        books={visible}
                                        selectedId={selectedId}
                                        onSelect={handleSelect}
                                        label={t('section.books')}
                                    />
                                    <div
                                        aria-hidden
                                        className="h-4 border-t-2 border-edge bg-pop-purple"
                                    />
                                </div>
                            ) : (
                                <CoverGrid
                                    books={visible}
                                    selectedId={selectedId}
                                    onSelect={handleSelect}
                                />
                            )}
                        </div>
                        <div
                            id="book-detail"
                            className="scroll-mt-28 lg:sticky lg:top-8"
                        >
                            <BookDetail
                                book={selected}
                                onClose={() => setSelectedId(null)}
                            />
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}
