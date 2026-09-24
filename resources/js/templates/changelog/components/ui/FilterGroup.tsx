import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export interface FilterOption<T extends string> {
    value: T;
    label: string;
    icon?: ReactNode;
    count?: number;
}

interface FilterGroupProps<T extends string> {
    label: string;
    options: FilterOption<T>[];
    value: T;
    onChange: (value: T) => void;
    variant?: 'pills' | 'segmented';
    className?: string;
}

export function FilterGroup<T extends string>({
    label,
    options,
    value,
    onChange,
    variant = 'pills',
    className,
}: FilterGroupProps<T>) {
    return (
        <div
            role="group"
            aria-label={label}
            className={cn(
                'flex flex-wrap items-center',
                variant === 'segmented'
                    ? 'border-line gap-0 rounded-full border p-0.5'
                    : 'gap-1.5',
                className,
            )}
        >
            {options.map((option) => {
                const isActive = option.value === value;
                return (
                    <button
                        key={option.value}
                        type="button"
                        aria-pressed={isActive}
                        onClick={() => onChange(option.value)}
                        className={cn(
                            'inline-flex h-8 items-center gap-1.5 rounded-full text-[0.8125rem] font-medium whitespace-nowrap transition-all duration-300 active:scale-[0.96]',
                            variant === 'segmented' ? 'px-3' : 'border px-3.5',
                            isActive
                                ? variant === 'segmented'
                                    ? 'bg-signal text-on-signal shadow-[0_6px_18px_-8px_var(--glow-lime)]'
                                    : 'bg-signal text-on-signal border-transparent shadow-[0_6px_18px_-8px_var(--glow-lime)]'
                                : variant === 'segmented'
                                  ? 'text-ink-muted hover:text-ink'
                                  : 'border-line text-ink-muted hover:border-electric/50 hover:bg-electric/10 hover:text-electric',
                        )}
                    >
                        {option.icon}
                        <span>{option.label}</span>
                        {option.count !== undefined && (
                            <span
                                className={cn(
                                    'ltr-isolate font-mono text-[0.6875rem]',
                                    isActive
                                        ? 'text-on-signal/70'
                                        : 'text-ink-subtle',
                                )}
                            >
                                {option.count}
                            </span>
                        )}
                    </button>
                );
            })}
        </div>
    );
}
