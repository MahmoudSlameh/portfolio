import { Button } from '@/templates/changelog/components/ui/Button';

interface EmptyStateProps {
    title: string;
    body: string;
    actionLabel?: string;
    onAction?: () => void;
}

export function EmptyState({
    title,
    body,
    actionLabel,
    onAction,
}: EmptyStateProps) {
    return (
        <div className="border-line-strong flex flex-col items-start gap-4 border border-dashed px-6 py-12 md:items-center md:text-center">
            <p
                aria-hidden
                className="ltr-isolate text-ink-subtle font-mono text-xs"
            >
                $ grep --results 0
            </p>
            <p className="font-display text-ink text-3xl">{title}</p>
            <p className="text-ink-muted max-w-md">{body}</p>
            {actionLabel && onAction && (
                <Button variant="secondary" size="sm" onClick={onAction}>
                    {actionLabel}
                </Button>
            )}
        </div>
    );
}
