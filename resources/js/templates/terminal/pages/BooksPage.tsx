import { useRouterState } from '@/lib/router';
import { Star, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import { cn, formatDate, formatNumber } from '@/lib/utils';
import type { BooksPageProps } from '@/templates/types';
import type { Book, BookCategory, ReadingStatus } from '@/types/content';
import { useTerminalCopy } from '../copy';
import { BookCover } from '../components/BookCover';
import { ChipGroup } from '../components/Chip';
import { EmptyState } from '../components/EmptyState';
import { PageHeader } from '../components/PageHeader';

type StatusFilter = ReadingStatus | 'all';
type CategoryFilter = BookCategory | 'all';

const STATUSES: ReadingStatus[] = ['reading', 'read', 'to-read'];
const STARS = [1, 2, 3, 4, 5] as const;

function BookNote({
    book,
    onClose,
}: {
    book: Book | null;
    onClose: () => void;
}) {
    const { t } = useTranslation();
    const c = useTerminalCopy();

    if (!book) {
        return (
            <div className="tm-box flex min-h-64 flex-col items-center justify-center gap-2 border-dashed p-8 text-center">
                <p className="text-tm-300 mb-0">
                    <span className="text-tm-primary">$</span> cat note.md
                </p>
                <p className="text-tm-400 mb-0 max-w-xs">{c('books.pick')}</p>
            </div>
        );
    }

    return (
        <article
            aria-live="polite"
            aria-label={t('books.selected')}
            className="tm-box flex flex-col gap-5 p-5 sm:flex-row lg:flex-col xl:flex-row"
        >
            <BookCover book={book} className="w-32 shrink-0 self-start" />
            <div className="flex min-w-0 flex-1 flex-col gap-3">
                <div className="flex items-start justify-between gap-3">
                    <span className="text-tm-secondary text-sm">
                        {t(`readingStatus.${book.status}`)}
                    </span>
                    <button
                        type="button"
                        onClick={onClose}
                        aria-label={c('books.close')}
                        className="border-tm-border text-tm-300 hover:text-ink inline-flex size-8 shrink-0 items-center justify-center rounded-md border"
                    >
                        <X aria-hidden className="size-4" />
                    </button>
                </div>
                <h2 lang="en" className="mb-0 text-xl font-medium">
                    {book.title}
                </h2>
                <p lang="en" className="text-tm-300 mb-0 text-sm">
                    {book.author} · {book.publishedYear}
                </p>
                <p lang="en" className="text-ink mb-0 text-sm leading-relaxed">
                    {book.note}
                </p>
                <div className="text-tm-300 mt-auto flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                    {book.rating ? (
                        <span
                            role="img"
                            aria-label={t('books.rating', {
                                rating: book.rating,
                            })}
                            className="flex gap-0.5"
                        >
                            {STARS.map((star) => (
                                <Star
                                    key={star}
                                    aria-hidden
                                    className={cn(
                                        'size-4',
                                        star <= (book.rating ?? 0)
                                            ? 'fill-[#a8ff53] text-[#62a92b]'
                                            : 'text-tm-400',
                                    )}
                                />
                            ))}
                        </span>
                    ) : (
                        <span>{c('books.noRating')}</span>
                    )}
                    {book.pages !== null && (
                        <span>{t('books.pages', { count: book.pages })}</span>
                    )}
                    {book.finishedAt && (
                        <span>
                            {t('books.finished', {
                                date: formatDate(book.finishedAt),
                            })}
                        </span>
                    )}
                </div>
            </div>
        </article>
    );
}

export function BooksPage({
    books,
    stats,
    search,
    onSearchChange,
}: BooksPageProps) {
    const { t } = useTranslation();
    const c = useTerminalCopy();
    const hash = useRouterState({ select: (state) => state.location.hash });
    const [selectedId, setSelectedId] = useState<string | null>(null);

    const status: StatusFilter = search.status ?? 'all';
    const category: CategoryFilter = search.category ?? 'all';
    const inCategory =
        category === 'all'
            ? books
            : books.filter((book) => book.category === category);
    const visible =
        status === 'all'
            ? inCategory
            : inCategory.filter((book) => book.status === status);
    const selected = books.find((book) => book.id === selectedId) ?? null;
    const maxCount = Math.max(...stats.perYear.map((entry) => entry.count), 1);

    useEffect(() => {
        if (!hash.startsWith('book-')) return;
        const target = books.find((book) => `book-${book.slug}` === hash);
        if (!target) return;
        setSelectedId(target.id);
        window.requestAnimationFrame(() =>
            document
                .getElementById('book-note')
                ?.scrollIntoView({ block: 'center' }),
        );
    }, [hash, books]);

    const figures = [
        { label: t('booksPage.read'), value: formatNumber(stats.read) },
        { label: t('booksPage.reading'), value: formatNumber(stats.reading) },
        { label: t('booksPage.pages'), value: formatNumber(stats.pagesRead) },
        { label: t('booksPage.rating'), value: stats.averageRating.toFixed(1) },
    ];

    return (
        <>
            <PageHeader
                kicker={c('books.kicker')}
                title={t('booksPage.title')}
                intro={t('booksPage.intro')}
            >
                <div className="grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
                    <ul
                        aria-label={t('booksPage.statsLabel')}
                        className="grid grid-cols-2 gap-6 sm:grid-cols-4"
                    >
                        {figures.map((figure) => (
                            <li key={figure.label}>
                                <p className="ltr-isolate text-ink mb-0 text-[2.5rem] leading-tight font-medium">
                                    {figure.value}
                                </p>
                                <p className="text-tm-300 mb-0 text-sm">
                                    {figure.label}
                                </p>
                            </li>
                        ))}
                    </ul>
                    <figure className="m-0">
                        <figcaption className="text-tm-400 mb-3 text-sm">
                            {t('booksPage.perYear')}
                        </figcaption>
                        <ol className="flex h-28 items-end gap-2" dir="ltr">
                            {stats.perYear.map((entry) => (
                                <li
                                    key={entry.year}
                                    aria-label={t('booksPage.perYearCount', {
                                        count: entry.count,
                                        year: entry.year,
                                    })}
                                    className="flex h-full flex-1 flex-col items-center justify-end gap-1"
                                >
                                    <span
                                        aria-hidden
                                        style={{
                                            height: `${Math.max(6, (entry.count / maxCount) * 100)}%`,
                                        }}
                                        className="w-full rounded-t-sm bg-[linear-gradient(0deg,#659932,#a8ff53)]"
                                    />
                                    <span
                                        aria-hidden
                                        className="text-tm-400 text-[0.625rem]"
                                    >
                                        {String(entry.year).slice(2)}
                                    </span>
                                </li>
                            ))}
                        </ol>
                    </figure>
                </div>
            </PageHeader>

            <div className="tm-container pt-8">
                <div className="tm-box mb-8 flex flex-col gap-4 p-5">
                    <ChipGroup
                        label={t('books.filterLabel')}
                        value={status}
                        onChange={(value) =>
                            onSearchChange({
                                status: value === 'all' ? undefined : value,
                            })
                        }
                        options={[
                            {
                                value: 'all' as StatusFilter,
                                label: t('readingStatus.all'),
                                count: inCategory.length,
                            },
                            ...STATUSES.map((value) => ({
                                value: value as StatusFilter,
                                label: t(`readingStatus.${value}`),
                                count: inCategory.filter(
                                    (book) => book.status === value,
                                ).length,
                            })),
                        ]}
                    />
                    <ChipGroup
                        label={t('books.categoryLabel')}
                        value={category}
                        onChange={(value) =>
                            onSearchChange({
                                category: value === 'all' ? undefined : value,
                            })
                        }
                        options={[
                            {
                                value: 'all' as CategoryFilter,
                                label: t('bookCategory.all'),
                            },
                            ...stats.categories.map((value) => ({
                                value: value as CategoryFilter,
                                label: t(`bookCategory.${value}`),
                            })),
                        ]}
                    />
                </div>

                {visible.length === 0 ? (
                    <EmptyState
                        onReset={() =>
                            onSearchChange({
                                status: undefined,
                                category: undefined,
                            })
                        }
                    />
                ) : (
                    <div className="grid grid-cols-1 items-start gap-8 lg:grid-cols-[minmax(0,1fr)_24rem]">
                        <ul
                            aria-label={t('section.books')}
                            className="grid grid-cols-2 gap-6 sm:grid-cols-3 xl:grid-cols-4"
                        >
                            {visible.map((book) => (
                                <li key={book.id}>
                                    <button
                                        type="button"
                                        id={`book-${book.slug}`}
                                        aria-pressed={book.id === selectedId}
                                        onClick={() =>
                                            setSelectedId((current) =>
                                                current === book.id
                                                    ? null
                                                    : book.id,
                                            )
                                        }
                                        className={cn(
                                            'tm-hover-up block w-full scroll-mt-10 rounded-md text-start',
                                            book.id === selectedId &&
                                                'ring-tm-primary ring-2 ring-offset-4 ring-offset-[var(--paper)]',
                                        )}
                                    >
                                        <BookCover book={book} />
                                    </button>
                                    <p
                                        lang="en"
                                        className="text-ink mt-3 mb-0 text-sm leading-snug"
                                    >
                                        {book.title}
                                    </p>
                                    <p
                                        lang="en"
                                        className="text-tm-400 mb-0 text-xs"
                                    >
                                        {book.author}
                                    </p>
                                </li>
                            ))}
                        </ul>
                        <div
                            id="book-note"
                            className="scroll-mt-10 lg:sticky lg:top-8"
                        >
                            <BookNote
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
