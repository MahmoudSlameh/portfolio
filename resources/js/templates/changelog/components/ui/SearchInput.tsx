import { Search, X } from 'lucide-react';
import { cn } from '@/lib/utils';

interface SearchInputProps {
    id: string;
    label: string;
    placeholder: string;
    clearLabel: string;
    value: string;
    onChange: (value: string) => void;
    className?: string;
}

export function SearchInput({
    id,
    label,
    placeholder,
    clearLabel,
    value,
    onChange,
    className,
}: SearchInputProps) {
    return (
        <div className={cn('relative', className)}>
            <label htmlFor={id} className="sr-only">
                {label}
            </label>
            <Search
                aria-hidden
                className="text-ink-subtle pointer-events-none absolute start-3.5 top-1/2 size-4 -translate-y-1/2"
            />
            <input
                id={id}
                type="search"
                value={value}
                placeholder={placeholder}
                autoComplete="off"
                spellCheck={false}
                onChange={(event) => onChange(event.target.value)}
                className="border-line-strong bg-raised text-ink placeholder:text-ink-subtle hover:border-ink-subtle focus-visible:border-ink h-11 w-full rounded-[4px] border ps-10 pe-10 text-[0.9375rem] [&::-webkit-search-cancel-button]:hidden"
            />
            {value && (
                <button
                    type="button"
                    aria-label={clearLabel}
                    onClick={() => onChange('')}
                    className="text-ink-subtle hover:bg-surface hover:text-ink absolute end-1.5 top-1/2 inline-flex size-8 -translate-y-1/2 items-center justify-center rounded-[3px]"
                >
                    <X aria-hidden className="size-4" />
                </button>
            )}
        </div>
    );
}
