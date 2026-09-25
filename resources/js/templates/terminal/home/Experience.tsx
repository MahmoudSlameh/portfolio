import { Link } from '@/lib/router';
import { useId, useRef, useState, type KeyboardEvent } from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import type { CareerEntry } from '@/lib/content';
import { cn, durationInMonths } from '@/lib/utils';
import { useTerminalCopy } from '../copy';
import { GlowCard } from '../components/GlowCard';
import { Kicker } from '../components/Kicker';
import { monogram, tintFor } from '../lib/monogram';

const yearOf = (value: string): string => value.slice(0, 4);

export function Experience({ career }: { career: CareerEntry[] }) {
    const { t } = useTranslation();
    const c = useTerminalCopy();
    const baseId = useId();
    const [activeIndex, setActiveIndex] = useState(0);
    const tabRefs = useRef<(HTMLButtonElement | null)[]>([]);
    const active = career[activeIndex];
    const earliest = career.reduce(
        (first, entry) => (entry.start < first ? entry.start : first),
        career[0]?.start ?? '',
    );
    const years = earliest
        ? Math.floor(durationInMonths(earliest, null) / 12)
        : 0;

    const handleKeyDown = (
        event: KeyboardEvent<HTMLButtonElement>,
        index: number,
    ): void => {
        const keys: Record<string, number> = {
            ArrowDown: 1,
            ArrowRight: 1,
            ArrowUp: -1,
            ArrowLeft: -1,
        };
        let next: number | null = null;
        if (event.key in keys)
            next = (index + keys[event.key] + career.length) % career.length;
        if (event.key === 'Home') next = 0;
        if (event.key === 'End') next = career.length - 1;
        if (next === null) return;
        event.preventDefault();
        setActiveIndex(next);
        tabRefs.current[next]?.focus();
    };

    if (!active) return null;

    return (
        <div className="tm-container pt-8">
            <GlowCard as="section" id="career" aria-labelledby="career-title">
                <div
                    aria-hidden
                    className="tm-grid-bg pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_50%_45%_at_0%_0%,#000,transparent)]"
                />
                <div className="relative p-4 md:p-10 lg:p-16">
                    <Kicker>{c('experience.kicker')}</Kicker>
                    <h2
                        id="career-title"
                        className="mt-1 mb-0 text-[clamp(1.5rem,3vw,2.1875rem)] font-medium"
                    >
                        <span className="ltr-isolate">
                            {c('experience.titleA', { count: years })}
                        </span>
                        <span className="text-tm-300">
                            {c('experience.titleB')}{' '}
                        </span>
                        {c('experience.titleC')}
                        <span className="text-tm-300">
                            {' '}
                            {c('experience.titleD')}
                        </span>
                        <br />
                        <span className="text-tm-300">
                            {c('experience.titleE')}
                        </span>
                    </h2>

                    <div className="mt-12 grid grid-cols-1 gap-12 lg:grid-cols-[minmax(0,4fr)_minmax(0,8fr)] lg:gap-0">
                        <div
                            role="tablist"
                            aria-orientation="vertical"
                            aria-label={c('experience.companies')}
                            className="flex flex-col gap-2"
                        >
                            {career.map((entry, index) => {
                                const selected = index === activeIndex;
                                const name =
                                    entry.company?.name ?? entry.organization;
                                return (
                                    <button
                                        key={entry.id}
                                        ref={(element) => {
                                            tabRefs.current[index] = element;
                                        }}
                                        type="button"
                                        role="tab"
                                        id={`${baseId}-tab-${entry.id}`}
                                        aria-selected={selected}
                                        aria-controls={`${baseId}-panel`}
                                        tabIndex={selected ? 0 : -1}
                                        onClick={() => setActiveIndex(index)}
                                        onKeyDown={(event) =>
                                            handleKeyDown(event, index)
                                        }
                                        className="tm-tech flex items-center gap-4 rounded-lg border border-tm-border p-4 text-start transition-colors"
                                    >
                                        <span
                                            aria-hidden
                                            className="ltr-isolate inline-flex size-12 shrink-0 items-center justify-center rounded-md bg-tm-tile text-lg font-medium"
                                            style={{ color: tintFor(name) }}
                                        >
                                            {monogram(name)}
                                        </span>
                                        <span className="flex min-w-0 flex-col">
                                            <span
                                                lang="en"
                                                className="truncate text-2xl leading-tight text-ink"
                                            >
                                                {name}
                                            </span>
                                            <span className="ltr-isolate text-tm-300">
                                                {yearOf(entry.start)} -{' '}
                                                {entry.end
                                                    ? yearOf(entry.end)
                                                    : c('experience.present')}
                                            </span>
                                        </span>
                                    </button>
                                );
                            })}
                        </div>

                        <div
                            id={`${baseId}-panel`}
                            role="tabpanel"
                            aria-labelledby={`${baseId}-tab-${active.id}`}
                            tabIndex={0}
                            className="lg:ps-12"
                        >
                            <h3
                                lang="en"
                                className="tm-text-gradient mb-1 text-xl font-medium"
                            >
                                {active.role}
                            </h3>
                            <p lang="en" className="mb-0 text-sm text-tm-300">
                                {active.organization} · {active.location}
                            </p>
                            <ul
                                lang="en"
                                className="mt-6 list-disc ps-5 marker:text-ink"
                            >
                                {active.highlights.map((highlight) => (
                                    <li
                                        key={highlight}
                                        className="mb-4 leading-relaxed text-ink"
                                    >
                                        {highlight}
                                    </li>
                                ))}
                            </ul>
                            {active.projects.length > 0 && (
                                <p className="mb-0 text-tm-300">
                                    {t('career.projects')}:{' '}
                                    {active.projects.map((project, index) => (
                                        <span key={project.slug}>
                                            {index > 0 && ', '}
                                            <Link
                                                to="/projects/$slug"
                                                params={{ slug: project.slug }}
                                                lang="en"
                                                className="text-tm-secondary hover:text-[#62a92b]"
                                            >
                                                {project.title}
                                            </Link>
                                        </span>
                                    ))}
                                </p>
                            )}
                            <ul className="mt-12 flex flex-wrap items-center gap-4">
                                {active.stack.map((technology) => (
                                    <li key={technology}>
                                        <Link
                                            to="/projects"
                                            search={{ tech: technology }}
                                            lang="en"
                                            className={cn(
                                                'inline-flex border border-tm-border px-4 py-1 text-tm-300 transition-colors hover:text-ink',
                                            )}
                                        >
                                            {technology}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </div>
            </GlowCard>
        </div>
    );
}
