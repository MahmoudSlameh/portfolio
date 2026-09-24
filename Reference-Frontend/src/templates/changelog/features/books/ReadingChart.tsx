import { useTranslation } from '@/hooks/useTranslation';
import type { BookStats } from '@/lib/content';
import { cn } from '@/lib/utils';

export function ReadingChart({ perYear }: { perYear: BookStats['perYear'] }) {
  const { t } = useTranslation();
  const max = Math.max(1, ...perYear.map((entry) => entry.count));

  return (
    <figure aria-labelledby="per-year-caption" className="border border-line bg-raised p-5 md:p-6">
      <figcaption id="per-year-caption" className="eyebrow mb-6 text-ink-subtle">
        {t('booksPage.perYear')}
      </figcaption>
      <ol className="flex h-44 items-end gap-3 border-b border-line-strong" dir="ltr">
        {perYear.map((entry, index) => (
          <li key={entry.year} className="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2">
            <span aria-hidden className="font-mono text-xs text-ink">
              {entry.count}
            </span>
            <span
              aria-hidden
              className={cn(
                'w-full max-w-12 rounded-t-md',
                index === perYear.length - 1 ? 'bg-signal' : 'bg-gradient-to-t from-electric/70 to-aqua',
              )}
              style={{ height: `${(entry.count / max) * 100}%`, minHeight: entry.count > 0 ? 4 : 0 }}
            />
            <span className="sr-only">{t('booksPage.perYearCount', { count: entry.count, year: entry.year })}</span>
          </li>
        ))}
      </ol>
      <ol aria-hidden className="mt-2 flex gap-3" dir="ltr">
        {perYear.map((entry) => (
          <li key={entry.year} className="flex-1 text-center font-mono text-[0.6875rem] text-ink-subtle">
            ’{String(entry.year).slice(2)}
          </li>
        ))}
      </ol>
    </figure>
  );
}
