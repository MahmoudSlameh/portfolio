import { cn } from '@/lib/utils';

export interface ChipOption<T extends string> {
  value: T;
  label: string;
  count?: number;
}

interface ChipGroupProps<T extends string> {
  label: string;
  options: ChipOption<T>[];
  value: T;
  onChange: (value: T) => void;
  className?: string;
}

export function ChipGroup<T extends string>({ label, options, value, onChange, className }: ChipGroupProps<T>) {
  return (
    <div role="group" aria-label={label} className={cn('flex flex-wrap gap-2', className)}>
      {options.map((option) => (
        <button
          key={option.value}
          type="button"
          aria-pressed={option.value === value}
          onClick={() => onChange(option.value)}
          className="pg-chip"
        >
          {option.label}
          {option.count !== undefined && (
            <span className="ltr-isolate font-mono text-[0.6875rem] opacity-70">{option.count}</span>
          )}
        </button>
      ))}
    </div>
  );
}
