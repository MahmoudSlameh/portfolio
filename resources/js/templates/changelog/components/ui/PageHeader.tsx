import type { ReactNode } from 'react';
import {
    GradientTitle,
    SectionLabel,
} from '@/templates/changelog/components/ui/Section';

interface PageHeaderProps {
    index: string;
    label: string;
    version?: string;
    title: string;
    intro: string;
    aside?: ReactNode;
}

export function PageHeader({
    index,
    label,
    version,
    title,
    intro,
    aside,
}: PageHeaderProps) {
    return (
        <header className="border-line relative overflow-hidden border-b">
            <div
                aria-hidden
                className="aurora opacity-[calc(var(--glow-opacity)*0.7)]"
            >
                <span />
                <span />
                <span />
            </div>
            <div
                aria-hidden
                className="baseline-texture pointer-events-none absolute inset-0"
            />
            <div className="shell editorial-grid relative gap-y-8 pt-16 pb-14 md:pt-24 md:pb-20">
                <div className="col-span-4 md:col-span-3">
                    <SectionLabel
                        index={index}
                        label={label}
                        version={version}
                    />
                </div>
                <div className="col-span-4 md:col-span-9 lg:col-span-6">
                    <h1 className="animate-rise font-display text-ink text-5xl leading-[0.98] sm:text-6xl lg:text-7xl">
                        <GradientTitle text={title} />
                    </h1>
                    <p className="text-ink-muted mt-6 max-w-xl text-lg leading-relaxed">
                        {intro}
                    </p>
                </div>
                {aside && (
                    <div className="col-span-4 md:col-span-9 md:col-start-4 lg:col-span-3 lg:col-start-10">
                        {aside}
                    </div>
                )}
            </div>
        </header>
    );
}
