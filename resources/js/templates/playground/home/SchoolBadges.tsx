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
                        'border-edge text-on-pop flex items-center justify-between gap-3 border-b-2 px-6 py-4',
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
                    <h3 className="font-display text-ink text-2xl leading-tight font-extrabold [font-stretch:112%]">
                        {entry.degree}
                    </h3>
                    <p className="text-ink-muted text-base font-semibold">
                        {[entry.field, entry.institution]
                            .filter(Boolean)
                            .join(' · ')}
                    </p>
                    {entry.grade && (
                        <p className="text-ink font-mono text-sm font-bold">
                            {entry.grade}
                        </p>
                    )}
                    <ul className="flex flex-col gap-1.5">
                        {entry.notes.map((note) => (
                            <li
                                key={note}
                                className="text-ink-muted text-sm leading-relaxed"
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
                    'pg-press group border-edge text-on-pop relative flex aspect-square w-full max-w-52 flex-col items-center justify-center gap-1 rounded-full border-2 p-6 text-center shadow-[var(--pg-shadow)]',
                    popBg[popForIndex(index)],
                )}
            >
                <span
                    aria-hidden
                    className="pg-spin-slow border-on-pop/50 absolute inset-2 rounded-full border-2 border-dashed"
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
                    <h3 className="pg-label text-ink mb-5">
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
                    <h3 className="pg-label text-ink mb-5">
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
