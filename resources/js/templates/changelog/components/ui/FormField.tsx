import {
    forwardRef,
    type InputHTMLAttributes,
    type ReactNode,
    type SelectHTMLAttributes,
    type TextareaHTMLAttributes,
} from 'react';
import { cn } from '@/lib/utils';

const controlClasses = (hasError: boolean): string =>
    cn(
        'bg-raised text-ink placeholder:text-ink-subtle w-full rounded-[4px] border px-3.5 text-[0.9375rem] transition-colors duration-200 focus-visible:outline-2 focus-visible:outline-offset-1',
        hasError
            ? 'border-danger focus-visible:outline-danger'
            : 'border-line-strong hover:border-ink-subtle focus-visible:border-ink',
    );

interface FieldShellProps {
    id: string;
    label: string;
    hint?: string;
    error?: string;
    required?: boolean;
    requiredLabel?: string;
    children: ReactNode;
}

function FieldShell({
    id,
    label,
    hint,
    error,
    required,
    requiredLabel,
    children,
}: FieldShellProps) {
    return (
        <div className="flex flex-col gap-2">
            <label
                htmlFor={id}
                className="text-ink flex items-baseline justify-between gap-3 text-sm font-medium"
            >
                <span>{label}</span>
                {required && requiredLabel && (
                    <span className="eyebrow text-ink-subtle">
                        {requiredLabel}
                    </span>
                )}
            </label>
            {children}
            {hint && !error && (
                <p
                    id={`${id}-hint`}
                    className="text-ink-subtle text-[0.8125rem]"
                >
                    {hint}
                </p>
            )}
            {error && (
                <p
                    id={`${id}-error`}
                    className="text-danger flex items-center gap-1.5 text-[0.8125rem] font-medium"
                >
                    <span aria-hidden className="font-mono">
                        !
                    </span>
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
): string | undefined =>
    error ? `${id}-error` : hint ? `${id}-hint` : undefined;

interface BaseFieldProps {
    id: string;
    label: string;
    hint?: string;
    error?: string;
    requiredLabel?: string;
}

type TextFieldProps = BaseFieldProps & InputHTMLAttributes<HTMLInputElement>;

export const TextField = forwardRef<HTMLInputElement, TextFieldProps>(
    function TextField(
        {
            id,
            label,
            hint,
            error,
            requiredLabel,
            required,
            className,
            ...props
        },
        ref,
    ) {
        return (
            <FieldShell
                id={id}
                label={label}
                hint={hint}
                error={error}
                required={required}
                requiredLabel={requiredLabel}
            >
                <input
                    ref={ref}
                    id={id}
                    aria-invalid={Boolean(error)}
                    aria-describedby={describedBy(id, hint, error)}
                    aria-required={required}
                    className={cn(
                        controlClasses(Boolean(error)),
                        'h-11',
                        className,
                    )}
                    {...props}
                />
            </FieldShell>
        );
    },
);

type TextAreaFieldProps = BaseFieldProps &
    TextareaHTMLAttributes<HTMLTextAreaElement>;

export const TextAreaField = forwardRef<
    HTMLTextAreaElement,
    TextAreaFieldProps
>(function TextAreaField(
    { id, label, hint, error, requiredLabel, required, className, ...props },
    ref,
) {
    return (
        <FieldShell
            id={id}
            label={label}
            hint={hint}
            error={error}
            required={required}
            requiredLabel={requiredLabel}
        >
            <textarea
                ref={ref}
                id={id}
                aria-invalid={Boolean(error)}
                aria-describedby={describedBy(id, hint, error)}
                aria-required={required}
                className={cn(
                    controlClasses(Boolean(error)),
                    'min-h-36 resize-y py-3 leading-relaxed',
                    className,
                )}
                {...props}
            />
        </FieldShell>
    );
});

type SelectFieldProps = BaseFieldProps &
    SelectHTMLAttributes<HTMLSelectElement> & {
        options: { value: string; label: string }[];
    };

export const SelectField = forwardRef<HTMLSelectElement, SelectFieldProps>(
    function SelectField(
        {
            id,
            label,
            hint,
            error,
            requiredLabel,
            required,
            options,
            className,
            ...props
        },
        ref,
    ) {
        return (
            <FieldShell
                id={id}
                label={label}
                hint={hint}
                error={error}
                required={required}
                requiredLabel={requiredLabel}
            >
                <div className="relative">
                    <select
                        ref={ref}
                        id={id}
                        aria-invalid={Boolean(error)}
                        aria-describedby={describedBy(id, hint, error)}
                        className={cn(
                            controlClasses(Boolean(error)),
                            'h-11 appearance-none pe-10',
                            className,
                        )}
                        {...props}
                    >
                        {options.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <span
                        aria-hidden
                        className="text-ink-subtle pointer-events-none absolute inset-y-0 end-3.5 flex items-center font-mono text-xs"
                    >
                        ▾
                    </span>
                </div>
            </FieldShell>
        );
    },
);
