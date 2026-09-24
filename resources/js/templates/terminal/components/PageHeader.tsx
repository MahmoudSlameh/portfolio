import type { ReactNode } from 'react';
import { GlowCard } from './GlowCard';
import { Kicker } from './Kicker';

interface PageHeaderProps {
    kicker: string;
    title: string;
    intro?: string;
    aside?: ReactNode;
    children?: ReactNode;
}

/** Top card for every inner page: same glowing box, kicker dot and mono heading as the home sections. */
export function PageHeader({
    kicker,
    title,
    intro,
    aside,
    children,
}: PageHeaderProps) {
    return (
        <header className="tm-container pt-[130px]">
            <GlowCard innerClassName="p-6 md:p-10 lg:p-16">
                <div
                    aria-hidden
                    className="tm-grid-bg tm-grid-fade pointer-events-none absolute inset-0"
                />
                <div className="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div className="max-w-3xl">
                        <Kicker>{kicker}</Kicker>
                        <h1 className="mt-2 mb-0 text-[clamp(2rem,5vw,3.125rem)]">
                            {title}
                            <span aria-hidden className="tm-flicker">
                                _
                            </span>
                        </h1>
                        {intro && (
                            <p className="mt-4 mb-0 max-w-2xl text-tm-300">
                                {intro}
                            </p>
                        )}
                    </div>
                    {aside && (
                        <div className="flex flex-wrap gap-3">{aside}</div>
                    )}
                </div>
                {children && <div className="relative mt-8">{children}</div>}
            </GlowCard>
        </header>
    );
}
