import { formatNumber, useTranslation } from '@/kit';
import type { BooksPageProps } from '@/templates/types';
import type { LibrarySearch } from '@/lib/searchSchemas';
import { EmptyState, PageHeader } from '../components/Section';

const STATUSES: {
    value: NonNullable<LibrarySearch['status']>;
    label: string;
}[] = [
    { value: 'reading', label: 'Reading' },
    { value: 'read', label: 'Read' },
    { value: 'to-read', label: 'To read' },
];

/**
 * /books. `books` is filtered by `search.status` / `search.category` on the server; `stats` is
 * computed over the whole library.
 */
export function BooksPage({
    books,
    stats,
    search,
    onSearchChange,
}: BooksPageProps) {
    const { t } = useTranslation();

    return (
        <>
            <PageHeader title={t('nav.books')} intro={t('books.intro')}>
                <p className="mt-4 text-sm text-ink-subtle">
                    {stats.read} read · {stats.reading} reading ·{' '}
                    {formatNumber(stats.pagesRead)} pages
                </p>
            </PageHeader>
            <div
                role="group"
                aria-label={t('books.filterLabel')}
                className="mn-container mt-8 flex flex-wrap gap-2"
            >
                {STATUSES.map((status) => (
                    <button
                        key={status.value}
                        type="button"
                        aria-pressed={search.status === status.value}
                        onClick={() =>
                            onSearchChange({
                                status:
                                    search.status === status.value
                                        ? undefined
                                        : status.value,
                            })
                        }
                        className="mn-chip"
                    >
                        {status.label}
                    </button>
                ))}
            </div>
            <div className="mn-container mt-8">
                {books.length > 0 ? (
                    <ul className="divide-y divide-line border-y border-line">
                        {books.map((book) => (
                            <li
                                key={book.id}
                                id={`book-${book.slug}`}
                                className="scroll-mt-8 py-4"
                            >
                                <p className="font-medium text-ink">
                                    {book.title}
                                </p>
                                <p className="text-sm text-ink-muted">
                                    {book.author}
                                    {book.rating &&
                                        ` · ${'★'.repeat(book.rating)}`}
                                </p>
                                {book.note && (
                                    <p className="mt-1 text-ink-muted">
                                        {book.note}
                                    </p>
                                )}
                            </li>
                        ))}
                    </ul>
                ) : (
                    <EmptyState>No books match this filter.</EmptyState>
                )}
            </div>
        </>
    );
}
