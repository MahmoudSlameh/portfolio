import { GraduationCap } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';
import { cn, yearRange } from '@/lib/utils';
import type { Certification, Education } from '@/types/content';
import { usePlaygroundCopy } from '../copy';
import { popBg, popForIndex, tiltForIndex } from '../lib/pops';
import { Reveal } from '../components/Reveal';
import { SectionHeading } from '../components/SectionHeading';

function DegreeCard({ entry, index }: { entry: Education; index: number }) {
    return (
        <Reveal as="li" delay={index * 90}>
            <article
                lang="en"
                className="pg-card pg-press flex h-full flex-col gap-5 overflow-hidden"
            >
                <header
                    className={cn(
                        'flex items-center justify-between gap-3 border-b-2 border-edge px-6 py-4 text-on-pop',
                        popBg[popForIndex(index + 2)],
                    )}
                >
                    <GraduationCap
                        aria-hidden
                        className="size-7"
                        strokeWidth={2.25}
                    />
                    <span className="ltr-isolate font-mono text-sm font-bold">
                        {yearRange(entry.start, entry.end)}
                    </span>
                </header>
                <div className="flex flex-col gap-3 px-6 pb-6">
                    <h3 className="font-display text-2xl leading-tight font-extrabold text-ink [font-stretch:112%]">
                        {entry.degree}
                    </h3>
                    <p className="text-base font-semibold text-ink-muted">
                        {[entry.field, entry.institution]
                            .filter(Boolean)
                            .join(' · ')}
                    </p>
                    {entry.grade && (
                        <p className="font-mono text-sm font-bold text-ink">
                            {entry.grade}
                        </p>
                    )}
                    <ul className="flex flex-col gap-1.5">
                        {entry.notes.map((note) => (
                            <li
                                key={note}
                                className="text-sm leading-relaxed text-ink-muted"
                            >
                                ✦ {note}
                            </li>
                        ))}
                    </ul>
                </div>
            </article>
        </Reveal>
    );
}

function CertificationStamp({
    certification,
    index,
}: {
    certification: Certification;
    index: number;
}) {
    const { t } = useTranslation();

    return (
        <li className="flex justify-center">
            <a
                href={certification.url}
                target="_blank"
                rel="noreferrer"
                style={{ rotate: `${tiltForIndex(index) * 1.5}deg` }}
                className={cn(
                    'pg-press group relative flex aspect-square w-full max-w-52 flex-col items-center justify-center gap-1 rounded-full border-2 border-edge p-6 text-center text-on-pop shadow-[var(--pg-shadow)]',
                    popBg[popForIndex(index)],
                )}
            >
                <span
                    aria-hidden
                    className="pg-spin-slow absolute inset-2 rounded-full border-2 border-dashed border-on-pop/50"
                />
                <span className="ltr-isolate font-mono text-xs font-bold">
                    {certification.year}
                </span>
                <span
                    lang="en"
                    className="text-base leading-tight font-extrabold"
                >
                    {certification.name}
                </span>
                <span lang="en" className="text-xs font-semibold">
                    {certification.issuer}
                </span>
                <span className="sr-only">
                    {t('education.verify')} {t('common.opensNewTab')}
                </span>
            </a>
        </li>
    );
}

export function SchoolBadges({
    education,
    certifications,
}: {
    education: Education[];
    certifications: Certification[];
}) {
    const { t } = useTranslation();
    const p = usePlaygroundCopy();

    return (
        <section
            id="education"
            aria-labelledby="education-title"
            className="pg-shell scroll-mt-8 py-16"
        >
            <SectionHeading
                id="education"
                kicker={p('education.kicker')}
                title={p('education.title')}
                pop="yellow"
            />
            <div className="grid gap-12 lg:grid-cols-12">
                <div className="lg:col-span-6">
                    <h3 className="pg-label mb-5 text-ink">
                        {t('education.degrees')}
                    </h3>
                    <ul className="grid gap-5">
                        {education.map((entry, index) => (
                            <DegreeCard
                                key={entry.id}
                                entry={entry}
                                index={index}
                            />
                        ))}
                    </ul>
                </div>
                <div className="lg:col-span-6">
                    <h3 className="pg-label mb-5 text-ink">
                        {t('education.certifications')}
                    </h3>
                    <ul className="grid grid-cols-2 gap-6">
                        {certifications.map((certification, index) => (
                            <CertificationStamp
                                key={certification.id}
                                certification={certification}
                                index={index}
                            />
                        ))}
                    </ul>
                </div>
            </div>
        </section>
    );
}
