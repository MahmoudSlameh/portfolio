import { cn } from '@/lib/utils';

export type StatusTone = 'signal' | 'neutral' | 'muted' | 'warning';

const dotClasses: Record<StatusTone, string> = {
  signal: 'bg-signal animate-pulse-dot',
  neutral: 'bg-ink-muted',
  muted: 'bg-transparent border border-ink-subtle',
  warning: 'bg-[#d99a1e]',
};

interface StatusBadgeProps {
  label: string;
  tone?: StatusTone;
  className?: string;
}

export function StatusBadge({ label, tone = 'neutral', className }: StatusBadgeProps) {
  return (
    <span className={cn('inline-flex items-center gap-2 text-[0.8125rem] font-medium text-ink', className)}>
      <span aria-hidden className={cn('size-2 shrink-0 rounded-full', dotClasses[tone])} />
      {label}
    </span>
  );
}
