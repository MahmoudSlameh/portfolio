import type { ReactNode } from 'react';

/** A titled block of a page. The id makes it linkable (`/#career`) and labels the landmark. */
export function Section({
    id,
    title,
    children,
}: {
    id: string;
    title: string;
    children: ReactNode;
}) {
    return (
        <section
            id={id}
            aria-labelledby={`${id}-title`}
            className="mn-container mt-16 scroll-mt-8"
        >
            <h2
                id={`${id}-title`}
                className="mb-6 text-sm font-semibold tracking-wide text-ink-subtle uppercase"
            >
                {title}
            </h2>
            {children}
        </section>
    );
}

/** Page title block used by every page except Home. */
export function PageHeader({
    title,
    intro,
    children,
}: {
    title: string;
    intro?: string;
    children?: ReactNode;
}) {
    return (
        <header className="mn-container pt-16">
            <h1 className="text-3xl font-semibold tracking-tight text-ink sm:text-4xl">
                {title}
            </h1>
            {intro && <p className="mt-3 text-lg text-ink-muted">{intro}</p>}
            {children}
        </header>
    );
}

/** Shown when a filtered list has no results. */
export function EmptyState({ children }: { children: ReactNode }) {
    return (
        <p className="rounded-md border border-dashed border-line p-6 text-center text-ink-muted">
            {children}
        </p>
    );
}
