import type { ButtonHTMLAttributes } from 'react';
import { cn } from '@/lib/utils';
import { popBg, type Pop } from '../lib/pops';

export type PopButtonTone = Pop | 'plain' | 'ink';

interface PopButtonClassOptions {
    tone?: PopButtonTone;
    size?: 'md' | 'sm' | 'icon';
    className?: string;
}

const toneClass = (tone: PopButtonTone): string => {
    if (tone === 'plain') return 'pg-btn-plain';
    if (tone === 'ink') return 'pg-btn-ink';
    return popBg[tone];
};

export const popButtonClasses = ({
    tone = 'yellow',
    size = 'md',
    className,
}: PopButtonClassOptions = {}): string =>
    cn(
        'pg-btn pg-press',
        toneClass(tone),
        size === 'sm' && 'pg-btn-sm',
        size === 'icon' && 'pg-btn-icon',
        className,
    );

interface PopButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
    tone?: PopButtonTone;
    size?: 'md' | 'sm' | 'icon';
}

export function PopButton({
    tone,
    size,
    className,
    type = 'button',
    ...props
}: PopButtonProps) {
    return (
        <button
            type={type}
            className={popButtonClasses({ tone, size, className })}
            {...props}
        />
    );
}
