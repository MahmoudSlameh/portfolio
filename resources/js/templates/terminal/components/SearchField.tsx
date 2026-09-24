import { Search, X } from 'lucide-react';
import { useId } from 'react';
import { useTranslation } from '@/hooks/useTranslation';

interface SearchFieldProps {
    label: string;
    placeholder: string;
    value: string;
    onChange: (value: string) => void;
}

export function SearchField({
    label,
    placeholder,
    value,
    onChange,
}: SearchFieldProps) {
    const { t } = useTranslation();
    const id = useId();

    return (
        <div className="relative">
            <label htmlFor={id} className="sr-only">
                {label}
            </label>
            <Search
                aria-hidden
                className="text-tm-400 pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2"
            />
            <input
                id={id}
                type="search"
                value={value}
                placeholder={placeholder}
                onChange={(event) => onChange(event.target.value)}
                className="tm-input ps-12 pe-12"
            />
            {value && (
                <button
                    type="button"
                    onClick={() => onChange('')}
                    aria-label={t('common.clear')}
                    className="text-tm-300 hover:text-ink absolute end-3 top-1/2 inline-flex size-8 -translate-y-1/2 items-center justify-center rounded-md"
                >
                    <X aria-hidden className="size-4" />
                </button>
            )}
        </div>
    );
}
