import { forwardRef, type ButtonHTMLAttributes, type ReactNode } from 'react';
import { cn } from '@/lib/utils';

interface TooltipProps {
    label: string;
    children: ReactNode;
    side?: 'top' | 'bottom';
    className?: string;
}

export function Tooltip({
    label,
    children,
    side = 'bottom',
    className,
}: TooltipProps) {
    return (
        <span className={cn('group/tooltip relative inline-flex', className)}>
            {children}
            <span
                aria-hidden
                className={cn(
                    'bg-ink text-paper pointer-events-none absolute start-1/2 z-50 -translate-x-1/2 rounded-[3px] px-2 py-1 text-[0.6875rem] font-medium whitespace-nowrap opacity-0 transition-opacity duration-150 group-focus-within/tooltip:opacity-100 group-hover/tooltip:opacity-100 rtl:translate-x-1/2',
                    side === 'bottom' ? 'top-full mt-2' : 'bottom-full mb-2',
                )}
            >
                {label}
            </span>
        </span>
    );
}

interface IconButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
    label: string;
    tooltipSide?: 'top' | 'bottom';
    showTooltip?: boolean;
}

export const IconButton = forwardRef<HTMLButtonElement, IconButtonProps>(
    function IconButton(
        {
            label,
            tooltipSide = 'bottom',
            showTooltip = true,
            className,
            children,
            type = 'button',
            ...props
        },
        ref,
    ) {
        const button = (
            <button
                ref={ref}
                type={type}
                aria-label={label}
                className={cn(
                    'text-ink-muted hover:bg-surface hover:text-ink inline-flex size-9 items-center justify-center rounded-[4px] transition-colors duration-200',
                    className,
                )}
                {...props}
            >
                {children}
            </button>
        );

        if (!showTooltip) return button;
        return (
            <Tooltip label={label} side={tooltipSide}>
                {button}
            </Tooltip>
        );
    },
);
