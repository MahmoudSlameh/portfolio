import type { ReactNode } from 'react';
import { cn } from '@/kit';

/**
 * A home section: `id` makes it linkable (`/#career`), `name` gives the `.st-<name>` class hook.
 */
export function Section({
    id,
    name,
    kicker,
    title,
    className,
    children,
}: {
    id: string;
    name: string;
    kicker?: string;
    title?: string;
    className?: string;
    children: ReactNode;
}) {
    return (
        <section
            id={id}
            aria-labelledby={title ? `${id}-title` : undefined}
            className={cn('st-section', `st-${name}`, className)}
        >
            <div className="st-container">
                {(kicker || title) && (
                    <header className="mb-8">
                        {kicker && <p className="st-kicker">{kicker}</p>}
                        {title && (
                            <h2
                                id={`${id}-title`}
                                className="st-section-title mt-2"
                            >
                                {title}
                            </h2>
                        )}
                    </header>
                )}
                {children}
            </div>
        </section>
    );
}

/** Title block of every page except Home. */
export function PageHeader({
    kicker,
    title,
    intro,
    children,
}: {
    kicker?: string;
    title: string;
    intro?: string;
    children?: ReactNode;
}) {
    return (
        <header className="st-page-header st-container pt-[calc(var(--st-section-gap)/2)]">
            {kicker && <p className="st-kicker">{kicker}</p>}
            <h1 className="mt-2 text-[clamp(2.25rem,6vw,4rem)] leading-[1.05] font-bold text-ink">
                {title}
            </h1>
            {intro && (
                <p className="mt-4 max-w-2xl text-lg text-ink-muted">{intro}</p>
            )}
            {children}
        </header>
    );
}

export function EmptyState({ children }: { children: ReactNode }) {
    return (
        <p className="st-empty rounded-[var(--st-radius)] border border-dashed border-line p-8 text-center text-ink-muted">
            {children}
        </p>
    );
}
