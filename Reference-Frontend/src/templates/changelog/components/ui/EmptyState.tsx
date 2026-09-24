import { Button } from '@/templates/changelog/components/ui/Button';

interface EmptyStateProps {
  title: string;
  body: string;
  actionLabel?: string;
  onAction?: () => void;
}

export function EmptyState({ title, body, actionLabel, onAction }: EmptyStateProps) {
  return (
    <div className="flex flex-col items-start gap-4 border border-dashed border-line-strong px-6 py-12 md:items-center md:text-center">
      <p aria-hidden className="ltr-isolate font-mono text-xs text-ink-subtle">
        $ grep --results 0
      </p>
      <p className="font-display text-3xl text-ink">{title}</p>
      <p className="max-w-md text-ink-muted">{body}</p>
      {actionLabel && onAction && (
        <Button variant="secondary" size="sm" onClick={onAction}>
          {actionLabel}
        </Button>
      )}
    </div>
  );
}
