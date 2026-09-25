import { Link } from '@/lib/router';
import { ArrowLeft, ArrowRight, Minus, Plus } from 'lucide-react';
import { useId, useRef, useState } from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import type { CareerEntry } from '@/lib/content';
import { cn, formatMonth } from '@/lib/utils';
import { usePlaygroundCopy } from '../copy';
import { branchPop, popBg } from '../lib/pops';
import { PopButton } from '../components/PopButton';
import { SectionHeading } from '../components/SectionHeading';

function Ticket({ entry }: { entry: CareerEntry }) {
    const { t } = useTranslation();
    const p = usePlaygroundCopy();
    const [isOpen, setIsOpen] = useState(false);
    const detailsId = useId();
    const pop = branchPop[entry.branch];

    return (
        <li className="pg-card flex flex-col overflow-visible">
            <div
                className={cn(
                    'flex items-center justify-between gap-3 rounded-t-[1.1rem] border-b-2 border-edge px-5 py-3 text-on-pop',
                    popBg[pop],
                )}
            >
                <span className="pg-label">
                    {t(`career.branch.${entry.branch}`)}
                </span>
                <span className="ltr-isolate rounded-full border-2 border-edge bg-raised px-2 font-mono text-xs font-bold text-ink">
                    {entry.version}
                </span>
            </div>

            <div className="flex flex-1 flex-col gap-4 p-5">
                <div>
                    <h3
                        lang="en"
                        className="font-display text-2xl leading-tight font-extrabold text-ink [font-stretch:112%]"
                    >
                        {entry.role}
                    </h3>
                    <p
                        lang="en"
                        className="mt-1 text-base font-semibold text-ink-muted"
                    >
                        {entry.organization} · {entry.location}
                    </p>
                </div>
                <dl className="grid grid-cols-2 gap-3">
                    <div>
                        <dt className="pg-label text-ink-subtle">
                            {p('career.boarding')}
                        </dt>
                        <dd className="mt-1 font-mono text-sm font-bold text-ink">
                            {formatMonth(entry.start)}
                        </dd>
                    </div>
                    <div>
                        <dt className="pg-label text-ink-subtle">
                            {p('career.arrival')}
                        </dt>
                        <dd className="mt-1 font-mono text-sm font-bold text-ink">
                            {entry.end
                                ? formatMonth(entry.end)
                                : t('career.present')}
                        </dd>
                    </div>
                </dl>
            </div>

            <div className="pg-perforation mx-0" />

            <div className="flex flex-col gap-4 p-5">
                <p
                    lang="en"
                    dir="ltr"
                    className="font-mono text-xs leading-relaxed text-ink-muted"
                >
                    <span className="font-bold text-ink">{entry.commit}</span>{' '}
                    {entry.message}
                </p>
                <button
                    type="button"
                    aria-expanded={isOpen}
                    aria-controls={detailsId}
                    onClick={() => setIsOpen((value) => !value)}
                    className="pg-chip self-start"
                >
                    {isOpen ? (
                        <Minus aria-hidden className="size-4" />
                    ) : (
                        <Plus aria-hidden className="size-4" />
                    )}
                    {isOpen ? p('career.less') : p('career.more')}
                </button>
                <div
                    id={detailsId}
                    hidden={!isOpen}
                    lang="en"
                    className="flex flex-col gap-4"
                >
                    <p className="text-[0.9375rem] leading-relaxed text-ink-muted">
                        {entry.summary}
                    </p>
                    <ul className="flex flex-col gap-2">
                        {entry.highlights.map((highlight) => (
                            <li
                                key={highlight}
                                className="flex gap-2 text-sm leading-relaxed text-ink"
                            >
                                <span
                                    aria-hidden
                                    className={cn(
                                        'mt-1.5 size-2 shrink-0 rounded-full border border-edge',
                                        popBg[pop],
                                    )}
                                />
                                {highlight}
                            </li>
                        ))}
                    </ul>
                    <ul
                        aria-label={t('career.stack')}
                        className="flex flex-wrap gap-1.5"
                    >
                        {entry.stack.map((tech) => (
                            <li key={tech} className="pg-tag text-ink">
                                {tech}
                            </li>
                        ))}
                    </ul>
                    {entry.projects.length > 0 && (
                        <ul
                            aria-label={t('career.projects')}
                            className="flex flex-wrap gap-2"
                        >
                            {entry.projects.map((project) => (
                                <li key={project.slug}>
                                    <Link
                                        to="/projects/$slug"
                                        params={{ slug: project.slug }}
                                        className="text-sm font-bold text-ink underline decoration-2 underline-offset-4 hover:decoration-pop-blue"
                                    >
                                        {project.title} ↗
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </li>
    );
}

export function CareerTickets({ entries }: { entries: CareerEntry[] }) {
    const { t } = useTranslation();
    const p = usePlaygroundCopy();
    const rowRef = useRef<HTMLOListElement>(null);

    const handleScroll = (step: 1 | -1): void => {
        const row = rowRef.current;
        if (!row) return;
        const reduceMotion = window.matchMedia(
            '(prefers-reduced-motion: reduce)',
        ).matches;
        const direction = step;
        row.scrollBy({
            left: direction * row.clientWidth * 0.8,
            behavior: reduceMotion ? 'auto' : 'smooth',
        });
    };

    return (
        <section
            id="career"
            aria-labelledby="career-title"
            className="scroll-mt-8 py-16"
        >
            <div className="pg-shell">
                <SectionHeading
                    id="career"
                    kicker={p('career.kicker')}
                    title={p('career.title')}
                    pop="pink"
                    aside={
                        <div className="flex flex-col items-start gap-4 md:items-end">
                            <p className="max-w-sm text-base text-ink-muted md:text-end">
                                {t('career.intro')}
                            </p>
                            <div className="flex gap-2">
                                <PopButton
                                    tone="plain"
                                    size="icon"
                                    aria-label={p('career.previous')}
                                    onClick={() => handleScroll(-1)}
                                >
                                    <ArrowLeft
                                        aria-hidden
                                        className="size-5 rtl:-scale-x-100"
                                        strokeWidth={2.5}
                                    />
                                </PopButton>
                                <PopButton
                                    tone="yellow"
                                    size="icon"
                                    aria-label={p('career.next')}
                                    onClick={() => handleScroll(1)}
                                >
                                    <ArrowRight
                                        aria-hidden
                                        className="size-5 rtl:-scale-x-100"
                                        strokeWidth={2.5}
                                    />
                                </PopButton>
                            </div>
                        </div>
                    }
                />
            </div>
            <ol
                ref={rowRef}
                aria-label={t('career.graphLabel')}
                className="pg-scroll-row [max-width:100vw] items-start xl:[padding-inline:max(2.5rem,calc((100vw-86rem)/2+2.5rem))]"
            >
                {entries.map((entry) => (
                    <Ticket key={entry.id} entry={entry} />
                ))}
            </ol>
        </section>
    );
}
