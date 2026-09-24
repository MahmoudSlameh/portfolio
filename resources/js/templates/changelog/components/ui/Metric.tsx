import { cn } from '@/lib/utils';

interface MetricProps {
    value: string;
    label: string;
    detail?: string;
    size?: 'md' | 'lg';
    className?: string;
}

export function Metric({
    value,
    label,
    detail,
    size = 'md',
    className,
}: MetricProps) {
    return (
        <div className={cn('flex flex-col gap-2', className)}>
            <dt className="text-ink-muted order-2 text-sm leading-snug">
                {label}
            </dt>
            <dd
                className={cn(
                    'ltr-isolate font-display text-ink order-1 self-start leading-none tracking-tight',
                    size === 'lg'
                        ? 'text-5xl md:text-6xl'
                        : 'text-4xl md:text-[2.75rem]',
                )}
            >
                {value}
            </dd>
            {detail && (
                <dd className="text-ink-subtle order-3 font-mono text-[0.6875rem]">
                    {detail}
                </dd>
            )}
        </div>
    );
}
