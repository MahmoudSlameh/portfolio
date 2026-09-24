import {
    BookCover,
    BookSpine,
} from '@/templates/changelog/components/content/BookCover';
import {
    StatusBadge,
    type StatusTone,
} from '@/templates/changelog/components/ui/StatusBadge';
import { useTranslation } from '@/hooks/useTranslation';
import { cn, formatMonth } from '@/lib/utils';
import type { Book, ReadingStatus } from '@/types/content';

export type ShelfMode = 'shelf' | 'grid';

const statusTone: Record<ReadingStatus, StatusTone> = {
    reading: 'signal',
    read: 'neutral',
    'to-read': 'muted',
};

function Rating({ rating }: { rating: Book['rating'] }) {
    const { t } = useTranslation();
    if (rating === null) return null;

    return (
        <span
            role="img"
            aria-label={t('books.rating', { rating })}
            className="ltr-isolate text-ink font-mono text-xs tracking-[0.15em]"
        >
            {'★'.repeat(rating)}
            <span className="text-line-strong">{'★'.repeat(5 - rating)}</span>
        </span>
    );
}

function BookMeta({
    book,
    compact = false,
}: {
    book: Book;
    compact?: boolean;
}) {
    const { t } = useTranslation();

    return (
        <div
            className={cn(
                'flex flex-wrap items-center gap-x-3 gap-y-1.5',
                compact && 'text-[0.8125rem]',
            )}
        >
            <StatusBadge
                label={t(`readingStatus.${book.status}`)}
                tone={statusTone[book.status]}
            />
            <Rating rating={book.rating} />
            {!compact && book.finishedAt && (
                <span className="text-ink-subtle text-[0.8125rem]">
                    {t('books.finished', {
                        date: formatMonth(book.finishedAt),
                    })}
                </span>
            )}
        </div>
    );
}

function BookDetail({ book }: { book: Book }) {
    const { t } = useTranslation();

    return (
        <div aria-live="polite" className="border-line bg-raised border p-6">
            <p className="eyebrow text-ink-subtle mb-5">
                {t('books.selected')}
            </p>
            <div
                key={book.id}
                className="animate-rise grid grid-cols-[6.5rem_minmax(0,1fr)] gap-5"
                style={{ animationDuration: '450ms' }}
            >
                <BookCover book={book} />
                <div lang="en" className="min-w-0">
                    <p className="font-display text-ink text-2xl leading-tight">
                        {book.title}
                    </p>
                    <p className="text-ink-muted mt-1 text-sm">{book.author}</p>
                    <p className="ltr-isolate text-ink-subtle mt-2 font-mono text-[0.6875rem]">
                        {book.publishedYear} · {book.pages}p
                    </p>
                </div>
            </div>
            <div className="border-line mt-5 flex flex-col gap-3 border-t pt-5">
                <BookMeta book={book} />
                <p
                    lang="en"
                    className="text-ink-muted text-[0.9375rem] leading-relaxed"
                >
                    {book.note}
                </p>
            </div>
        </div>
    );
}

interface BookshelfProps {
    books: Book[];
    mode: ShelfMode;
    selectedId: string | null;
    onSelect: (bookId: string) => void;
}

export function Bookshelf({
    books,
    mode,
    selectedId,
    onSelect,
}: BookshelfProps) {
    const { t } = useTranslation();
    const selected = books.find((book) => book.id === selectedId) ?? books[0];

    if (mode === 'grid') {
        return (
            <ul className="xs:grid-cols-3 grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-4 lg:grid-cols-6">
                {books.map((book) => (
                    <li
                        key={book.id}
                        id={`book-${book.slug}`}
                        className="group scroll-mt-28"
                    >
                        <div className="card-lift">
                            <BookCover book={book} />
                        </div>
                        <p
                            lang="en"
                            className="text-ink mt-4 text-[0.9375rem] leading-snug font-medium"
                        >
                            {book.title}
                        </p>
                        <p
                            lang="en"
                            className="text-ink-subtle mt-0.5 mb-2.5 text-[0.8125rem]"
                        >
                            {book.author}
                        </p>
                        <BookMeta book={book} compact />
                    </li>
                ))}
            </ul>
        );
    }

    return (
        <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <div className="min-w-0">
                <div className="no-scrollbar overflow-x-auto pt-4">
                    <ul className="flex h-72 min-w-max items-end gap-[3px] px-3 md:h-80">
                        {books.map((book, index) => (
                            <li
                                key={book.id}
                                id={`book-${book.slug}`}
                                className="flex h-full scroll-mt-28 items-end"
                            >
                                <BookSpine
                                    book={book}
                                    index={index}
                                    selected={book.id === selected?.id}
                                    onSelect={onSelect}
                                    label={`${book.title} — ${book.author}, ${t(`readingStatus.${book.status}`)}`}
                                />
                            </li>
                        ))}
                    </ul>
                </div>
                <div
                    aria-hidden
                    className="bg-ink h-2.5 rounded-[1px] shadow-[0_10px_18px_-10px_rgb(0_0_0/0.55)]"
                />
                <div
                    aria-hidden
                    className="from-ink/15 mx-4 h-2 bg-gradient-to-b to-transparent"
                />
            </div>
            {selected && <BookDetail book={selected} />}
        </div>
    );
}
