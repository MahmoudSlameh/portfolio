import { cn, yearOf } from '@/lib/utils';
import { ArrowUpRight } from 'lucide-react';
import { Section } from '@/templates/changelog/components/ui/Section';
import { sectionIndex } from '@/config/navigation';
import { useTranslation } from '@/hooks/useTranslation';
import type { Certification, Education } from '@/types/content';

interface EducationSectionProps {
    education: Education[];
    certifications: Certification[];
}

export function EducationSection({
    education,
    certifications,
}: EducationSectionProps) {
    const { t } = useTranslation();

    return (
        <Section
            id="education"
            index={sectionIndex('education')}
            label={t('section.education')}
            version="CREDITS"
            title={t('education.title')}
        >
            <div
                className={cn(
                    'grid gap-12 md:gap-8 lg:gap-16',
                    education.length > 0 &&
                        certifications.length > 0 &&
                        'md:grid-cols-2',
                )}
            >
                {education.length > 0 && (
                    <div>
                        <h3 className="eyebrow mb-4 border-b border-line pb-3 text-ink-subtle">
                            {t('education.degrees')}
                        </h3>
                        <ul>
                            {education.map((item) => (
                                <li
                                    key={item.id}
                                    lang="en"
                                    className="grid grid-cols-[5.5rem_minmax(0,1fr)] gap-4 border-b border-line py-6"
                                >
                                    <span className="ltr-isolate font-mono text-xs text-ink-subtle">
                                        {yearOf(item.start)}–
                                        {item.end
                                            ? yearOf(item.end).slice(2)
                                            : t('career.present')}
                                    </span>
                                    <div>
                                        <p className="font-display text-2xl leading-tight text-ink">
                                            {[item.degree, item.field]
                                                .filter(Boolean)
                                                .join(', ')}
                                        </p>
                                        {item.grade && (
                                            <p className="mt-1 font-mono text-xs text-signal-ink">
                                                {item.grade}
                                            </p>
                                        )}
                                        <p className="mt-1 text-[0.9375rem] text-ink-muted">
                                            {[item.institution, item.location]
                                                .filter(Boolean)
                                                .join(' · ')}
                                        </p>
                                        {item.description && (
                                            <p className="mt-3 text-[0.9375rem] leading-relaxed text-ink-muted">
                                                {item.description}
                                            </p>
                                        )}
                                        <ul className="mt-3 flex flex-col gap-1">
                                            {item.notes.map((note) => (
                                                <li
                                                    key={note}
                                                    className="flex gap-2 text-sm text-ink-muted"
                                                >
                                                    <span
                                                        aria-hidden
                                                        className="text-ink-subtle"
                                                    >
                                                        —
                                                    </span>
                                                    {note}
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}

                {certifications.length > 0 && (
                    <div>
                        <h3 className="eyebrow mb-4 border-b border-line pb-3 text-ink-subtle">
                            {t('education.certifications')}
                        </h3>
                        <ul>
                            {certifications.map((certification) => (
                                <li
                                    key={certification.id}
                                    className="group grid grid-cols-[5.5rem_minmax(0,1fr)_auto] items-start gap-4 border-b border-line py-5"
                                >
                                    <span className="ltr-isolate font-mono text-xs text-ink-subtle">
                                        {certification.year}
                                    </span>
                                    <div lang="en">
                                        <p className="text-[1.0625rem] leading-snug font-medium text-ink">
                                            {certification.name}
                                        </p>
                                        <p className="mt-1 text-sm text-ink-muted">
                                            {certification.issuer}
                                        </p>
                                        <p className="ltr-isolate mt-1 font-mono text-[0.6875rem] text-ink-subtle">
                                            {certification.credentialId}
                                        </p>
                                    </div>
                                    <a
                                        href={certification.url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="inline-flex items-center gap-1 text-[0.8125rem] font-medium text-ink-muted hover:text-ink"
                                    >
                                        <span className="link-draw-target">
                                            {t('education.verify')}
                                        </span>
                                        <ArrowUpRight
                                            aria-hidden
                                            className="size-3.5 rtl:-scale-x-100"
                                        />
                                        <span className="sr-only">
                                            {certification.name}{' '}
                                            {t('common.opensNewTab')}
                                        </span>
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}
            </div>
        </Section>
    );
}
