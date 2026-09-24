import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';
import { popBg, type Pop } from '../lib/pops';
import { Sticker } from './Sticker';

interface PageHeroProps {
    kicker: string;
    title: string;
    intro: string;
    pop?: Pop;
    badge?: ReactNode;
    children?: ReactNode;
}

export function PageHero({
    kicker,
    title,
    intro,
    pop = 'yellow',
    badge,
    children,
}: PageHeroProps) {
    return (
        <header className="pg-shell pt-8 pb-10 md:pt-14 md:pb-14">
            <div
                className={cn(
                    'pg-card relative overflow-hidden px-6 py-10 text-on-pop md:px-12 md:py-16',
                    popBg[pop],
                )}
            >
                <span
                    aria-hidden
                    className="pg-spin-slow pointer-events-none absolute -end-16 -top-16 size-56 rounded-full border-2 border-dashed border-on-pop/40"
                />
                <div className="relative flex flex-col items-start gap-6">
                    <div className="flex flex-wrap items-center gap-3">
                        <Sticker
                            pop={pop === 'yellow' ? 'pink' : 'yellow'}
                            tilt={-5}
                        >
                            {kicker}
                        </Sticker>
                        {badge}
                    </div>
                    <h1 className="pg-display max-w-5xl text-[clamp(3rem,11vw,8.5rem)]">
                        {title}
                    </h1>
                    <p className="max-w-2xl text-lg leading-relaxed font-medium md:text-xl">
                        {intro}
                    </p>
                    {children}
                </div>
            </div>
        </header>
    );
}
