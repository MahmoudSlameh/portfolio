import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export interface TimelineItem {
  id: string;
  date: string;
  title?: ReactNode;
  body: ReactNode;
}

interface TimelineProps {
  items: TimelineItem[];
  className?: string;
  spread?: boolean;
}

/** Dated list with a hairline on the start edge that fades into the card, as in the resume blocks. */
export function Timeline({ items, className, spread = false }: TimelineProps) {
  return (
    <div className={cn('relative h-full', className)}>
      <ul className={cn('relative flex h-full list-disc flex-col ps-4', spread ? 'justify-around gap-8' : 'gap-5')}>
        {items.map((item) => (
          <li key={item.id} className="relative z-[1] marker:text-tm-300">
            <div className="flex gap-2">
              <p className="ltr-isolate mb-0 shrink-0 text-nowrap text-tm-300">{item.date}:</p>
              <div className="min-w-0">
                {item.title && <p className="mb-0 text-tm-primary">{item.title}</p>}
                <div className="text-ink">{item.body}</div>
              </div>
            </div>
          </li>
        ))}
      </ul>
      <span aria-hidden className="absolute start-[5px] top-6 z-0 h-[90%] border-s border-tm-border" />
      <span aria-hidden className="tm-timeline-fade pointer-events-none absolute start-0 bottom-0 z-[2] h-[70%] w-full" />
    </div>
  );
}
