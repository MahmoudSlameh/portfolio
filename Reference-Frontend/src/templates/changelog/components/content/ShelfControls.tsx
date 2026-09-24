import { LayoutGrid, Library } from 'lucide-react';
import { FilterGroup } from '@/templates/changelog/components/ui/FilterGroup';
import type { ShelfMode } from '@/templates/changelog/components/content/Bookshelf';
import { useTranslation } from '@/hooks/useTranslation';
import type { Book, ReadingStatus } from '@/types/content';

export type StatusFilter = ReadingStatus | 'all';

const STATUS_FILTERS: StatusFilter[] = ['all', 'reading', 'read', 'to-read'];

interface ShelfControlsProps {
  books: Book[];
  status: StatusFilter;
  onStatusChange: (status: StatusFilter) => void;
  mode: ShelfMode;
  onModeChange: (mode: ShelfMode) => void;
}

export function ShelfControls({ books, status, onStatusChange, mode, onModeChange }: ShelfControlsProps) {
  const { t } = useTranslation();

  return (
    <div className="flex flex-wrap items-center justify-between gap-4">
      <FilterGroup
        label={t('books.filterLabel')}
        value={status}
        onChange={onStatusChange}
        options={STATUS_FILTERS.map((value) => ({
          value,
          label: t(`readingStatus.${value}`),
          count: value === 'all' ? books.length : books.filter((book) => book.status === value).length,
        }))}
      />
      <FilterGroup
        label={t('books.viewMode')}
        variant="segmented"
        value={mode}
        onChange={onModeChange}
        options={[
          { value: 'shelf', label: t('books.shelf'), icon: <Library aria-hidden className="size-3.5" /> },
          { value: 'grid', label: t('books.grid'), icon: <LayoutGrid aria-hidden className="size-3.5" /> },
        ]}
      />
    </div>
  );
}
