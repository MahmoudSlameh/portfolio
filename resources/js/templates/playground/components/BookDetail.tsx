import { Star, X } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';
import { cn, formatDate } from '@/lib/utils';
import type { Book } from '@/types/content';
import { usePlaygroundCopy } from '../copy';
import { popBg, readingPop } from '../lib/pops';
import { PopBookCover } from './PopBookCover';

const STARS = [1, 2, 3, 4, 5] as const;

interface BookDetailProps {
    book: Book | null;
    onClose: () => void;
    className?: string;
}

export function BookDetail({ book, onClose, className }: BookDetailProps) {
    const { t } = useTranslation();
    const p = usePlaygroundCopy();

    if (!book) {
        return (
            <div
                className={cn(
                    'pg-card flex min-h-64 flex-col items-center justify-center gap-3 border-dashed p-8 text-center',
                    className,
                )}
            >
                <span aria-hidden className="pg-float text-5xl">
                    📚
                </span>
                <p className="max-w-xs text-base font-semibold text-ink-muted">
                    {p('books.pick')}
                </p>
            </div>
        );
    }

    return (
        <article
            aria-live="polite"
            aria-label={t('books.selected')}
            className={cn(
                'pg-card flex flex-col gap-5 p-5 sm:flex-row',
                className,
            )}
        >
            <PopBookCover
                book={book}
                className="w-32 shrink-0 rotate-[-3deg] self-start sm:w-36"
            />
            <div className="flex min-w-0 flex-1 flex-col gap-3">
                <div className="flex items-start justify-between gap-3">
                    <span
                        className={cn(
                            'pg-tag text-on-pop',
                            popBg[readingPop[book.status]],
                        )}
                    >
                        {t(`readingStatus.${book.status}`)}
                    </span>
                    <button
                        type="button"
                        onClick={onClose}
                        aria-label={p('books.close')}
                        className="inline-flex size-8 shrink-0 items-center justify-center rounded-full border-2 border-edge bg-raised text-ink hover:bg-pop-red hover:text-on-pop"
                    >
                        <X aria-hidden className="size-4" strokeWidth={2.5} />
                    </button>
                </div>
                <h3
                    lang="en"
                    className="font-display text-2xl leading-tight font-extrabold text-ink [font-stretch:110%]"
                >
                    {book.title}
                </h3>
                <p lang="en" className="text-base font-semibold text-ink-muted">
                    {book.author} · {book.publishedYear}
                </p>
                <p
                    lang="en"
                    className="text-[0.9375rem] leading-relaxed text-ink"
                >
                    {book.note}
                </p>
                <div className="mt-auto flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-semibold text-ink-muted">
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
                                            ? 'fill-pop-yellow text-ink'
                                            : 'text-ink-subtle',
                                    )}
                                    strokeWidth={2}
                                />
                            ))}
                        </span>
                    ) : (
                        <span>{p('books.noRating')}</span>
                    )}
                    <span>
                        {book.pages !== null &&
                            p('books.pageCount', { count: book.pages })}
                    </span>
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
