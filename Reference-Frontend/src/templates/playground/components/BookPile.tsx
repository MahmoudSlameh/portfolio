import type { CSSProperties } from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import { cn } from '@/lib/utils';
import type { Book } from '@/types/content';
import { tiltForIndex } from '../lib/pops';
import { PopBookCover } from './PopBookCover';

interface BookPileProps {
  books: Book[];
  selectedId: string | null;
  onSelect: (bookId: string) => void;
  label: string;
}

export function BookPile({ books, selectedId, onSelect, label }: BookPileProps) {
  const { isRtl } = useTranslation();

  return (
    <ul aria-label={label} className="no-scrollbar isolate flex items-end overflow-x-auto px-4 pt-10 pb-6">
      {books.map((book, index) => {
        const isSelected = book.id === selectedId;
        const stackStyle = { '--pile-z': isRtl ? books.length - index : index } as CSSProperties;
        return (
          <li
            key={book.id}
            style={stackStyle}
            className={cn(
              'relative shrink-0',
              index > 0 && '-ms-8 sm:-ms-10',
              isSelected ? 'z-[1000]' : 'z-(--pile-z) hover:z-[999] focus-within:z-[999]',
            )}
          >
            <button
              type="button"
              aria-pressed={isSelected}
              onClick={() => onSelect(book.id)}
              style={{ rotate: `${tiltForIndex(index) * 1.4}deg` }}
              className={cn(
                'block w-28 rounded-xl shadow-[var(--pg-shadow)] transition-[translate,rotate] duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:-translate-y-5 hover:!rotate-0 focus-visible:-translate-y-5 sm:w-36',
                isSelected && '-translate-y-8 !rotate-0',
              )}
            >
              <PopBookCover book={book} />
            </button>
          </li>
        );
      })}
    </ul>
  );
}
