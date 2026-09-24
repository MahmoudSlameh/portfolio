import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export function Kicker({
    children,
    center = false,
    className,
}: {
    children: ReactNode;
    center?: boolean;
    className?: string;
}) {
    return (
        <p
            className={cn(
                'mb-0 flex items-center',
                center && 'justify-center',
                className,
            )}
        >
            <span
                aria-hidden
                className="me-2 inline-block size-[5px] shrink-0 rounded-full bg-[#a8ff53]"
            />
            <span className="tm-text-gradient">{children}</span>
        </p>
    );
}
