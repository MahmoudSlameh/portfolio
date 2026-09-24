import {
    forwardRef,
    type InputHTMLAttributes,
    type ReactNode,
    type TextareaHTMLAttributes,
} from 'react';
import { cn } from '@/lib/utils';

interface FieldShellProps {
    id: string;
    label: string;
    requiredLabel?: string;
    hint?: string;
    error?: string;
    children: ReactNode;
}

function FieldShell({
    id,
    label,
    requiredLabel,
    hint,
    error,
    children,
}: FieldShellProps) {
    return (
        <div className="flex flex-col gap-2">
            <label
                htmlFor={id}
                className="text-ink flex items-baseline justify-between gap-3 text-sm font-bold"
            >
                {label}
                {requiredLabel && (
                    <span className="text-ink-subtle font-mono text-[0.6875rem] font-normal">
                        {requiredLabel}
                    </span>
                )}
            </label>
            {children}
            {hint && !error && (
                <p id={`${id}-hint`} className="text-ink-subtle text-xs">
                    {hint}
                </p>
            )}
            {error && (
                <p
                    id={`${id}-error`}
                    className="text-danger flex items-center gap-1.5 text-sm font-semibold"
                >
                    <span aria-hidden>✗</span>
                    {error}
                </p>
            )}
        </div>
    );
}

const describedBy = (
    id: string,
    hint?: string,
    error?: string,
): string | undefined => {
    if (error) return `${id}-error`;
    if (hint) return `${id}-hint`;
    return undefined;
};

interface PopInputProps extends InputHTMLAttributes<HTMLInputElement> {
    id: string;
    label: string;
    requiredLabel?: string;
    hint?: string;
    error?: string;
}

export const PopInput = forwardRef<HTMLInputElement, PopInputProps>(
    function PopInput(
        {
            id,
            label,
            requiredLabel,
            hint,
            error,
            className,
            required,
            ...props
        },
        ref,
    ) {
        return (
            <FieldShell
                id={id}
                label={label}
                requiredLabel={required ? requiredLabel : undefined}
                hint={hint}
                error={error}
            >
                <input
                    ref={ref}
                    id={id}
                    aria-required={required || undefined}
                    aria-invalid={error ? true : undefined}
                    aria-describedby={describedBy(id, hint, error)}
                    className={cn('pg-input', className)}
                    {...props}
                />
            </FieldShell>
        );
    },
);

interface PopTextAreaProps extends TextareaHTMLAttributes<HTMLTextAreaElement> {
    id: string;
    label: string;
    requiredLabel?: string;
    hint?: string;
    error?: string;
}

export function PopTextArea({
    id,
    label,
    requiredLabel,
    hint,
    error,
    className,
    required,
    ...props
}: PopTextAreaProps) {
    return (
        <FieldShell
            id={id}
            label={label}
            requiredLabel={required ? requiredLabel : undefined}
            hint={hint}
            error={error}
        >
            <textarea
                id={id}
                aria-required={required || undefined}
                aria-invalid={error ? true : undefined}
                aria-describedby={describedBy(id, hint, error)}
                className={cn(
                    'pg-input min-h-36 resize-y leading-relaxed',
                    className,
                )}
                {...props}
            />
        </FieldShell>
    );
}
