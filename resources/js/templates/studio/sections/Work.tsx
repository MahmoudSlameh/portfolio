import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useState } from 'react';
import {
    cn,
    CompanyLogo,
    formatMonth,
    Link,
    ResponsiveImage,
    useTranslation,
} from '@/kit';
import type { CareerEntry, Company } from '@/types/content';
import { ProjectCard, ProjectRow } from '../components/Cards';
import { Section } from '../components/Section';
import { useCopy } from '../useSpec';
import type { SectionComponentProps } from './types';

function period(entry: CareerEntry, present: string): string {
    return `${formatMonth(entry.start)} – ${entry.end ? formatMonth(entry.end) : present}`;
}

function Highlights({ entry }: { entry: CareerEntry }) {
    if (entry.highlights.length === 0) return null;

    return (
        <details className="st-career__highlights mt-3 text-ink-muted">
            <summary className="st-link cursor-pointer text-sm">
                View achievements ({entry.highlights.length})
            </summary>
            <ul className="mt-3 list-disc ps-5 marker:text-signal">
                {entry.highlights.map((highlight) => (
                    <li key={highlight} className="mb-1.5">
                        {highlight}
                    </li>
                ))}
            </ul>
        </details>
    );
}

export function Career({
    data,
    variant,
    props,
}: SectionComponentProps<'career'>) {
    const { t } = useTranslation();
    const copy = useCopy();
    const career = data.career.slice(0, props.limit ?? data.career.length);
    const present = t('career.present');

    if (career.length === 0) return null;

    return (
        <Section
            id="career"
            name="career"
            kicker={t('section.career')}
            title={copy('careerHeading', 'Where I have worked')}
        >
            {variant === 'table' && (
                <div className="overflow-x-auto">
                    <table className="w-full text-start text-sm">
                        <thead className="text-ink-subtle">
                            <tr className="border-b border-line">
                                <th className="py-3 pe-4 text-start font-normal">
                                    Period
                                </th>
                                <th className="py-3 pe-4 text-start font-normal">
                                    Role
                                </th>
                                <th className="py-3 text-start font-normal">
                                    Company
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {career.map((entry) => (
                                <tr
                                    key={entry.id}
                                    className="border-b border-line align-top"
                                >
                                    <td className="py-4 pe-4 font-mono whitespace-nowrap text-ink-subtle">
                                        {period(entry, present)}
                                    </td>
                                    <td className="py-4 pe-4">
                                        <p className="font-bold text-ink">
                                            {entry.role}
                                        </p>
                                        <p className="mt-1 text-ink-muted">
                                            {entry.summary}
                                        </p>
                                    </td>
                                    <td className="py-4 text-ink">
                                        {entry.company?.name ??
                                            entry.organization}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
            {variant === 'cards' && (
                <ul className="grid gap-4 md:grid-cols-2">
                    {career.map((entry) => (
                        <li key={entry.id} className="st-card p-6">
                            <div className="flex items-center gap-3">
                                {entry.company?.logo && (
                                    <CompanyLogo
                                        company={entry.company}
                                        fallback={null}
                                        className="size-10 object-contain"
                                    />
                                )}
                                <div>
                                    <h3 className="font-bold text-ink">
                                        {entry.role}
                                    </h3>
                                    <p className="text-sm text-ink-muted">
                                        {entry.organization} ·{' '}
                                        {period(entry, present)}
                                    </p>
                                </div>
                            </div>
                            <p className="mt-4 text-ink-muted">
                                {entry.summary}
                            </p>
                            <Highlights entry={entry} />
                        </li>
                    ))}
                </ul>
            )}
            {variant === 'timeline' && (
                <ol className="relative grid gap-10 border-s border-line ps-8">
                    {career.map((entry) => (
                        <li key={entry.id} className="relative">
                            <span
                                aria-hidden
                                className="absolute -start-[2.4rem] top-1.5 size-3 rounded-full border-2 border-paper bg-signal"
                            />
                            <p className="font-mono text-xs text-ink-subtle">
                                {period(entry, present)}
                            </p>
                            <h3 className="mt-1 text-xl font-bold text-ink">
                                {entry.role}{' '}
                                <span className="text-ink-muted">
                                    · {entry.organization}
                                </span>
                            </h3>
                            <p className="mt-2 max-w-3xl text-ink-muted">
                                {entry.summary}
                            </p>
                            <Highlights entry={entry} />
                        </li>
                    ))}
                </ol>
            )}
        </Section>
    );
}

export function Projects({
    data,
    variant,
    props,
}: SectionComponentProps<'projects'>) {
    const { t } = useTranslation();
    const copy = useCopy();
    const projects = data.projects.slice(0, props.limit ?? 6);

    if (projects.length === 0) return null;

    return (
        <Section
            id="work"
            name="projects"
            kicker={t('section.work')}
            title={copy('projectsHeading', 'Selected work')}
        >
            {variant === 'bento' && (
                <div className="grid gap-4 md:grid-cols-3">
                    {projects.map((project, index) => (
                        <ProjectCard
                            key={project.id}
                            project={project}
                            large={index === 0}
                            className={cn(
                                index === 0 && 'md:col-span-2 md:row-span-2',
                            )}
                        />
                    ))}
                </div>
            )}
            {variant === 'grid' && (
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {projects.map((project) => (
                        <ProjectCard key={project.id} project={project} />
                    ))}
                </div>
            )}
            {variant === 'list' && (
                <div className="border-t border-line">
                    {projects.map((project) => (
                        <ProjectRow key={project.id} project={project} />
                    ))}
                </div>
            )}
            {variant === 'slider' && (
                <ul className="st-slider -mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4">
                    {projects.map((project) => (
                        <li
                            key={project.id}
                            className="w-[min(85vw,24rem)] shrink-0 snap-start"
                        >
                            <ProjectCard project={project} />
                        </li>
                    ))}
                </ul>
            )}
            <Link to="/projects" className="st-link mt-8 inline-block">
                {t('nav.work')} →
            </Link>
        </Section>
    );
}

function Logo({ company }: { company: Company }) {
    return (
        <CompanyLogo
            company={company}
            fallback={
                <span className="text-lg font-bold whitespace-nowrap text-ink-muted">
                    {company.name}
                </span>
            }
            className="st-logo h-9 w-auto max-w-40 object-contain"
        />
    );
}

export function Clients({ data, variant }: SectionComponentProps<'clients'>) {
    const { t } = useTranslation();
    const copy = useCopy();
    const { companies } = data;

    if (companies.length === 0) return null;

    return (
        <Section
            id="clients"
            name="clients"
            kicker={t('section.clients')}
            title={copy('clientsHeading', 'Teams I have worked with')}
        >
            {variant === 'marquee' ? (
                <div className="st-marquee py-4">
                    {[false, true].map((duplicate) => (
                        <ul
                            key={String(duplicate)}
                            aria-hidden={duplicate || undefined}
                            className="st-marquee__track gap-14 pe-14"
                        >
                            {companies.map((company) => (
                                <li key={company.id} className="shrink-0">
                                    <Logo company={company} />
                                </li>
                            ))}
                        </ul>
                    ))}
                </div>
            ) : (
                <ul className="grid grid-cols-2 items-center gap-8 sm:grid-cols-3 lg:grid-cols-5">
                    {companies.map((company) => (
                        <li key={company.id} className="flex justify-center">
                            <Logo company={company} />
                        </li>
                    ))}
                </ul>
            )}
        </Section>
    );
}

export function Testimonials({
    data,
    variant,
}: SectionComponentProps<'testimonials'>) {
    const copy = useCopy();
    const { testimonials } = data;
    const [index, setIndex] = useState(0);

    if (testimonials.length === 0) return null;

    const quote = (entry: (typeof testimonials)[number]) => (
        <figure className="st-card st-testimonial flex h-full flex-col gap-5 p-6">
            <blockquote className="text-lg text-ink">
                “{entry.quote}”
            </blockquote>
            <figcaption className="mt-auto flex items-center gap-3">
                {entry.avatar && (
                    <span className="size-10 overflow-hidden rounded-full">
                        <ResponsiveImage
                            image={entry.avatar}
                            sizes="40px"
                            fallbackAspect="1 / 1"
                            className="size-full"
                        />
                    </span>
                )}
                <span>
                    <span className="block font-bold text-ink">
                        {entry.author}
                    </span>
                    <span className="block text-sm text-ink-muted">
                        {entry.role}
                        {entry.company && ` · ${entry.company.name}`}
                    </span>
                </span>
            </figcaption>
        </figure>
    );

    return (
        <Section
            id="testimonials"
            name="testimonials"
            kicker="Testimonials"
            title={copy('testimonialsHeading', 'Kind words')}
        >
            {variant === 'carousel' ? (
                <div className="grid gap-4">
                    <div aria-live="polite">{quote(testimonials[index])}</div>
                    {testimonials.length > 1 && (
                        <div className="flex items-center gap-2">
                            <button
                                type="button"
                                aria-label="Previous testimonial"
                                onClick={() =>
                                    setIndex(
                                        (index - 1 + testimonials.length) %
                                            testimonials.length,
                                    )
                                }
                                className="st-button st-button--ghost"
                            >
                                <ChevronLeft aria-hidden className="size-4" />
                            </button>
                            <button
                                type="button"
                                aria-label="Next testimonial"
                                onClick={() =>
                                    setIndex((index + 1) % testimonials.length)
                                }
                                className="st-button st-button--ghost"
                            >
                                <ChevronRight aria-hidden className="size-4" />
                            </button>
                            <span className="ms-2 font-mono text-sm text-ink-subtle">
                                {index + 1} / {testimonials.length}
                            </span>
                        </div>
                    )}
                </div>
            ) : (
                <div className="columns-1 gap-4 md:columns-2 lg:columns-3 [&>*]:mb-4 [&>*]:break-inside-avoid">
                    {testimonials.map((entry) => (
                        <div key={entry.id}>{quote(entry)}</div>
                    ))}
                </div>
            )}
        </Section>
    );
}
