import { cn } from '@/lib/utils';

type Tone = 'primary' | 'ghost' | 'outline';

const tones: Record<Tone, string> = {
    primary: 'bg-[#62a92b] text-white hover:bg-[#579a24]',
    ghost: 'text-ink hover:text-[#62a92b]',
    outline:
        'border border-tm-border bg-tm-card text-ink hover:border-tm-primary hover:text-tm-primary',
};

export const buttonClasses = ({
    tone = 'outline',
    className,
}: { tone?: Tone; className?: string } = {}): string =>
    cn(
        'inline-flex items-center justify-center gap-2 rounded-md px-5 py-3 text-sm font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-60',
        tones[tone],
        className,
    );
