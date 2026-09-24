import { Link } from '@tanstack/react-router';
import { ArrowUpRight } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import type { Book, ReadingStatus } from '@/types/content';
import { usePlaygroundCopy } from '../copy';
import { BookDetail } from '../components/BookDetail';
import { BookPile } from '../components/BookPile';
import { ChipGroup, type ChipOption } from '../components/ChipGroup';
import { popButtonClasses } from '../components/PopButton';
import { SectionHeading } from '../components/SectionHeading';

type StatusFilter = ReadingStatus | 'all';

const STATUSES: ReadingStatus[] = ['reading', 'read', 'to-read'];
const PILE_SIZE = 9;

export function BookNook({ books }: { books: Book[] }) {
  const { t } = useTranslation();
  const p = usePlaygroundCopy();
  const [status, setStatus] = useState<StatusFilter>('all');
  const [selectedId, setSelectedId] = useState<string | null>(null);

  const filtered = status === 'all' ? books : books.filter((book) => book.status === status);
  const pile = filtered.slice(0, PILE_SIZE);
  const selected = books.find((book) => book.id === selectedId) ?? null;

  const options: ChipOption<StatusFilter>[] = [
    { value: 'all', label: t('readingStatus.all'), count: books.length },
    ...STATUSES.map((value) => ({
      value,
      label: t(`readingStatus.${value}`),
      count: books.filter((book) => book.status === value).length,
    })),
  ];

  const handleSelect = (bookId: string): void => setSelectedId((current) => (current === bookId ? null : bookId));

  return (
    <section id="books" aria-labelledby="books-title" className="pg-shell scroll-mt-8 py-16">
      <SectionHeading
        id="books"
        kicker={p('books.kicker')}
        title={p('books.title')}
        pop="pink"
        aside={
          <Link to="/books" className={popButtonClasses({ tone: 'plain' })}>
            {t('books.viewAll')}
            <ArrowUpRight aria-hidden className="size-4 rtl:-scale-x-100" strokeWidth={2.5} />
          </Link>
        }
      />
      <ChipGroup label={t('books.filterLabel')} options={options} value={status} onChange={setStatus} className="mb-4" />
      <div className="grid items-end gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
        <BookPile books={pile} selectedId={selectedId} onSelect={handleSelect} label={t('section.books')} />
        <BookDetail book={selected} onClose={() => setSelectedId(null)} />
      </div>
    </section>
  );
}
