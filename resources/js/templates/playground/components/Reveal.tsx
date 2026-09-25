import type { ElementType, ReactNode } from 'react';
import { useReveal } from '@/hooks/useReveal';
import { cn } from '@/lib/utils';

interface RevealProps {
    as?: ElementType;
    id?: string;
    labelledBy?: string;
    className?: string;
    delay?: number;
    children: ReactNode;
}

export function Reveal({
    as: Tag = 'div',
    id,
    labelledBy,
    className,
    delay = 0,
    children,
}: RevealProps) {
    const ref = useReveal<HTMLElement>();

    return (
        <Tag
            ref={ref}
            id={id}
            aria-labelledby={labelledBy}
            className={cn('pg-reveal', className)}
            style={delay ? { transitionDelay: `${delay}ms` } : undefined}
        >
            {children}
        </Tag>
    );
}
