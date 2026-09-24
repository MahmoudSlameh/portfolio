import { Search, X } from 'lucide-react';
import { useId } from 'react';
import { useTranslation } from '@/hooks/useTranslation';

interface SearchBoxProps {
    label: string;
    placeholder: string;
    value: string;
    onChange: (value: string) => void;
}

export function SearchBox({
    label,
    placeholder,
    value,
    onChange,
}: SearchBoxProps) {
    const { t } = useTranslation();
    const inputId = useId();

    return (
        <div className="relative w-full">
            <label htmlFor={inputId} className="sr-only">
                {label}
            </label>
            <Search
                aria-hidden
                className="text-ink pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2"
                strokeWidth={2.5}
            />
            <input
                id={inputId}
                type="search"
                value={value}
                placeholder={placeholder}
                onChange={(event) => onChange(event.target.value)}
                className="pg-input h-14 ps-12 pe-12 text-lg [&::-webkit-search-cancel-button]:hidden"
            />
            {value && (
                <button
                    type="button"
                    onClick={() => onChange('')}
                    aria-label={t('common.clear')}
                    className="border-edge bg-pop-yellow text-on-pop absolute end-3 top-1/2 inline-flex size-8 -translate-y-1/2 items-center justify-center rounded-full border-2"
                >
                    <X aria-hidden className="size-4" strokeWidth={2.5} />
                </button>
            )}
        </div>
    );
}
