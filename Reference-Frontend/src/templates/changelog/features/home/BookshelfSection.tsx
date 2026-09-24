import { Link } from '@tanstack/react-router';
import { ArrowRight } from 'lucide-react';
import { useState } from 'react';
import { Bookshelf, type ShelfMode } from '@/templates/changelog/components/content/Bookshelf';
import { ShelfControls, type StatusFilter } from '@/templates/changelog/components/content/ShelfControls';
import { EmptyState } from '@/templates/changelog/components/ui/EmptyState';
import { Section } from '@/templates/changelog/components/ui/Section';
import { sectionIndex } from '@/config/navigation';
import { useTranslation } from '@/hooks/useTranslation';
import type { Book } from '@/types/content';

export function BookshelfSection({ books }: { books: Book[] }) {
  const { t } = useTranslation();
  const [status, setStatus] = useState<StatusFilter>('all');
  const [mode, setMode] = useState<ShelfMode>('shelf');
  const [selectedId, setSelectedId] = useState<string | null>(books[0]?.id ?? null);

  const visibleBooks = status === 'all' ? books : books.filter((book) => book.status === status);

  return (
    <Section
      id="books"
      index={sectionIndex('books')}
      label={t('section.books')}
      version="vendor/"
      title={t('books.title')}
      intro={t('books.intro')}
      action={
        <Link to="/books" className="group inline-flex items-center gap-2 text-sm font-medium text-ink">
          <span className="link-draw-target">{t('books.viewAll')}</span>
          <ArrowRight aria-hidden className="size-4 transition-transform group-hover:translate-x-0.5 rtl:-scale-x-100" />
        </Link>
      }
    >
      <div className="mb-10">
        <ShelfControls books={books} status={status} onStatusChange={setStatus} mode={mode} onModeChange={setMode} />
      </div>
      {visibleBooks.length > 0 ? (
        <Bookshelf books={visibleBooks} mode={mode} selectedId={selectedId} onSelect={setSelectedId} />
      ) : (
        <EmptyState title={t('empty.title')} body={t('empty.body')} actionLabel={t('empty.reset')} onAction={() => setStatus('all')} />
      )}
    </Section>
  );
}
