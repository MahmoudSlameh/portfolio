import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

interface ChipGroupProps<T extends string> {
    label: string;
    options: { value: T; label: string; count?: number }[];
    value: T;
    onChange: (value: T) => void;
    className?: string;
}

export function ChipGroup<T extends string>({
    label,
    options,
    value,
    onChange,
    className,
}: ChipGroupProps<T>) {
    return (
        <div
            role="group"
            aria-label={label}
            className={cn('flex flex-wrap gap-2', className)}
        >
            {options.map((option) => (
                <button
                    key={option.value}
                    type="button"
                    aria-pressed={option.value === value}
                    onClick={() => onChange(option.value)}
                    className="tm-chip rounded-md"
                >
                    {option.label}
                    {option.count !== undefined && (
                        <span className="ltr-isolate text-tm-400 text-xs">
                            {option.count}
                        </span>
                    )}
                </button>
            ))}
        </div>
    );
}

export function Tag({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <span
            className={cn(
                'border-tm-border text-tm-300 inline-flex border px-3 py-1',
                className,
            )}
        >
            {children}
        </span>
    );
}
