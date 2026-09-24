import { Check, Copy } from 'lucide-react';
import { useClipboard } from '@/hooks/useClipboard';
import { useToast } from '@/providers/ToastProvider';
import { cn } from '@/lib/utils';

interface CopyButtonProps {
    text: string;
    label: string;
    copiedLabel: string;
    showLabel?: boolean;
    className?: string;
}

export function CopyButton({
    text,
    label,
    copiedLabel,
    showLabel = true,
    className,
}: CopyButtonProps) {
    const { copied, copy } = useClipboard();
    const { notify } = useToast();

    const handleClick = async (): Promise<void> => {
        const didCopy = await copy(text);
        if (didCopy) notify(copiedLabel);
    };

    const Icon = copied ? Check : Copy;

    return (
        <button
            type="button"
            onClick={handleClick}
            aria-label={showLabel ? undefined : label}
            className={cn(
                'border-line-strong text-ink hover:border-ink hover:bg-raised inline-flex h-9 items-center gap-2 rounded-[4px] border px-3 text-[0.8125rem] font-medium transition-colors duration-200',
                className,
            )}
        >
            <Icon
                aria-hidden
                className={cn('size-3.5', copied && 'text-signal-ink')}
                strokeWidth={2.25}
            />
            {showLabel && <span>{copied ? copiedLabel : label}</span>}
        </button>
    );
}
