import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';
import type { Pop } from '../lib/pops';
import { Sticker } from './Sticker';

interface SectionHeadingProps {
  id: string;
  kicker: string;
  title: string;
  pop?: Pop;
  aside?: ReactNode;
  className?: string;
}

export function SectionHeading({ id, kicker, title, pop = 'yellow', aside, className }: SectionHeadingProps) {
  return (
    <div className={cn('mb-10 flex flex-wrap items-end justify-between gap-6', className)}>
      <div className="flex flex-col items-start gap-4">
        <Sticker pop={pop} tilt={-3}>
          {kicker}
        </Sticker>
        <h2 id={`${id}-title`} className="pg-display max-w-4xl text-[clamp(2.25rem,6vw,4.75rem)] text-ink">
          {title}
        </h2>
      </div>
      {aside}
    </div>
  );
}
