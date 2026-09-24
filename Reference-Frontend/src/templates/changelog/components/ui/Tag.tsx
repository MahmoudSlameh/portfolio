import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

interface TagProps {
  children: ReactNode;
  tone?: 'default' | 'signal';
  className?: string;
}

export function Tag({ children, tone = 'default', className }: TagProps) {
  return (
    <span
      className={cn(
        'inline-flex items-center rounded-[3px] border px-1.5 py-0.5 font-mono text-[0.6875rem] leading-4 whitespace-nowrap',
        tone === 'signal'
          ? 'border-signal/40 bg-signal-soft text-signal-ink'
          : 'border-line text-ink-muted',
        className,
      )}
    >
      {children}
    </span>
  );
}
