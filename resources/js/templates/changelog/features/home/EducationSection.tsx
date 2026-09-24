import { yearOf } from '@/lib/utils';
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
            <div className="grid gap-12 md:grid-cols-2 md:gap-8 lg:gap-16">
                <div>
                    <h3 className="eyebrow border-line text-ink-subtle mb-4 border-b pb-3">
                        {t('education.degrees')}
                    </h3>
                    <ul>
                        {education.map((item) => (
                            <li
                                key={item.id}
                                lang="en"
                                className="border-line grid grid-cols-[5.5rem_minmax(0,1fr)] gap-4 border-b py-6"
                            >
                                <span className="ltr-isolate text-ink-subtle font-mono text-xs">
                                    {yearOf(item.start)}–
                                    {item.end
                                        ? yearOf(item.end).slice(2)
                                        : t('career.present')}
                                </span>
                                <div>
                                    <p className="font-display text-ink text-2xl leading-tight">
                                        {[item.degree, item.field]
                                            .filter(Boolean)
                                            .join(', ')}
                                    </p>
                                    {item.grade && (
                                        <p className="text-signal-ink mt-1 font-mono text-xs">
                                            {item.grade}
                                        </p>
                                    )}
                                    <p className="text-ink-muted mt-1 text-[0.9375rem]">
                                        {[item.institution, item.location]
                                            .filter(Boolean)
                                            .join(' · ')}
                                    </p>
                                    <ul className="mt-3 flex flex-col gap-1">
                                        {item.notes.map((note) => (
                                            <li
                                                key={note}
                                                className="text-ink-muted flex gap-2 text-sm"
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

                <div>
                    <h3 className="eyebrow border-line text-ink-subtle mb-4 border-b pb-3">
                        {t('education.certifications')}
                    </h3>
                    <ul>
                        {certifications.map((certification) => (
                            <li
                                key={certification.id}
                                className="group border-line grid grid-cols-[5.5rem_minmax(0,1fr)_auto] items-start gap-4 border-b py-5"
                            >
                                <span className="ltr-isolate text-ink-subtle font-mono text-xs">
                                    {certification.year}
                                </span>
                                <div lang="en">
                                    <p className="text-ink text-[1.0625rem] leading-snug font-medium">
                                        {certification.name}
                                    </p>
                                    <p className="text-ink-muted mt-1 text-sm">
                                        {certification.issuer}
                                    </p>
                                    <p className="ltr-isolate text-ink-subtle mt-1 font-mono text-[0.6875rem]">
                                        {certification.credentialId}
                                    </p>
                                </div>
                                <a
                                    href={certification.url}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="text-ink-muted hover:text-ink inline-flex items-center gap-1 text-[0.8125rem] font-medium"
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
            </div>
        </Section>
    );
}
