import { Link } from '@/lib/router';
import {
    ArrowLeft,
    ArrowRight,
    ArrowUpRight,
    BookOpenText,
} from 'lucide-react';
import { useReducedMotion } from 'motion/react';
import { useEffect, useState } from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import { ResponsiveImage } from '@/shared/ui/ResponsiveImage';
import { BrandIcon } from '@/shared/ui/BrandIcon';
import type { Company, Project } from '@/types/content';
import { useTerminalCopy } from '../copy';
import { GlowCard } from '../components/GlowCard';
import { Kicker } from '../components/Kicker';

const AUTOPLAY_MS = 4000;

function ProjectSlide({
    project,
    company,
    index,
    total,
    active,
}: {
    project: Project;
    company: Company | null;
    index: number;
    total: number;
    active: boolean;
}) {
    const { t } = useTranslation();
    const c = useTerminalCopy();
    const rows = [
        {
            label: c('projects.client'),
            value: company?.name ?? t('case.independent'),
        },
        { label: c('projects.time'), value: project.timeline },
        {
            label: c('projects.tech'),
            value: project.stack.slice(0, 4).join(', '),
        },
    ];

    return (
        <div
            role="group"
            aria-roledescription="slide"
            aria-label={c('projects.slide', { current: index + 1, total })}
            aria-hidden={!active}
            inert={!active}
            className="w-full shrink-0 px-px"
        >
            <div className="mt-12 grid grid-cols-1 gap-10 border border-tm-border bg-tm-card p-4 md:p-6 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] lg:gap-12 lg:p-12">
                <Link
                    to="/projects/$slug"
                    params={{ slug: project.slug }}
                    tabIndex={-1}
                    aria-hidden
                    className="block self-start overflow-hidden"
                >
                    <ResponsiveImage
                        image={project.cover}
                        sizes="(min-width: 992px) 450px, 90vw"
                        className="aspect-[4/3] w-full transition-transform duration-500 hover:scale-105"
                    />
                </Link>
                <div lang="en" className="min-w-0">
                    <h3 className="tm-text-gradient mb-3 text-[clamp(1.5rem,2.5vw,1.75rem)] font-medium">
                        {project.title}
                    </h3>
                    <p className="text-tm-body">{project.summary}</p>
                    <ul className="mt-6">
                        <li className="mb-4 border-b border-tm-border pb-4 text-tm-secondary">
                            {c('projects.info')}
                        </li>
                        {rows.map((row) => (
                            <li
                                key={row.label}
                                className="mb-4 flex justify-between gap-6 border-b border-tm-border pb-4"
                            >
                                <span className="text-ink">{row.label}</span>
                                <span className="text-end text-tm-300">
                                    {row.value}
                                </span>
                            </li>
                        ))}
                    </ul>
                    <div className="mt-12 flex flex-wrap items-center gap-4 pe-0 md:pe-32">
                        {project.links.slice(0, 2).map((link) => (
                            <a
                                key={link.url}
                                href={link.url}
                                target="_blank"
                                rel="noreferrer"
                                className="tm-link-hover inline-flex items-center gap-1.5 px-2 pb-2"
                            >
                                {link.kind === 'source' ? (
                                    <BrandIcon
                                        icon="github"
                                        className="size-5"
                                    />
                                ) : (
                                    <ArrowUpRight
                                        aria-hidden
                                        className="size-4"
                                    />
                                )}
                                {link.label}
                                <span className="sr-only">
                                    {' '}
                                    {t('common.opensNewTab')}
                                </span>
                            </a>
                        ))}
                        <Link
                            to="/projects/$slug"
                            params={{ slug: project.slug }}
                            className="tm-link-hover inline-flex items-center gap-1.5 px-2 pb-2"
                        >
                            <BookOpenText aria-hidden className="size-4" />
                            {c('projects.case')}
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}

export function ProjectsSlider({
    projects,
    companies,
}: {
    projects: Project[];
    companies: Company[];
}) {
    const { t } = useTranslation();
    const c = useTerminalCopy();
    const reduceMotion = useReducedMotion();
    const [index, setIndex] = useState(0);
    const [paused, setPaused] = useState(false);
    const total = projects.length;

    const go = (delta: number): void =>
        setIndex((current) => (current + delta + total) % total);

    useEffect(() => {
        if (paused || reduceMotion || total < 2) return;
        const timer = window.setInterval(
            () => setIndex((current) => (current + 1) % total),
            AUTOPLAY_MS,
        );
        return () => window.clearInterval(timer);
    }, [paused, reduceMotion, total]);

    if (total === 0) return null;

    return (
        <div className="tm-container pt-8">
            <GlowCard as="section" id="work" aria-labelledby="work-title">
                <div
                    aria-hidden
                    className="tm-grid-bg pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_75%_90%_at_85%_40%,#000_30%,transparent_75%)]"
                />
                <div
                    className="relative p-4 md:p-10 lg:p-16"
                    onMouseEnter={() => setPaused(true)}
                    onMouseLeave={() => setPaused(false)}
                    onFocus={() => setPaused(true)}
                    onBlur={() => setPaused(false)}
                >
                    <Kicker>{c('projects.kicker')}</Kicker>
                    <h2
                        id="work-title"
                        className="mt-1 mb-0 text-[clamp(1.5rem,3vw,2.1875rem)] font-medium"
                    >
                        {c('projects.title')}
                    </h2>

                    <div
                        role="region"
                        aria-roledescription="carousel"
                        aria-label={c('projects.title')}
                        className="relative"
                    >
                        <div className="overflow-hidden pb-4">
                            <div
                                aria-live={paused ? 'polite' : 'off'}
                                className="flex transition-transform duration-700 ease-[cubic-bezier(0.22,1,0.36,1)]"
                                style={{
                                    transform: `translateX(${-1 * index * 100}%)`,
                                }}
                            >
                                {projects.map((project, projectIndex) => (
                                    <ProjectSlide
                                        key={project.id}
                                        project={project}
                                        company={
                                            companies.find(
                                                (company) =>
                                                    company.id ===
                                                    project.companyId,
                                            ) ?? null
                                        }
                                        index={projectIndex}
                                        total={total}
                                        active={projectIndex === index}
                                    />
                                ))}
                            </div>
                        </div>

                        {total > 1 && (
                            <div className="mt-4 flex justify-end gap-2 md:absolute md:end-12 md:bottom-16 md:mt-0">
                                <button
                                    type="button"
                                    onClick={() => go(-1)}
                                    aria-label={c('projects.previous')}
                                    className="tm-round-btn"
                                >
                                    <ArrowLeft
                                        aria-hidden
                                        className="size-6 rtl:-scale-x-100"
                                    />
                                </button>
                                <button
                                    type="button"
                                    onClick={() => go(1)}
                                    aria-label={c('projects.next')}
                                    className="tm-round-btn"
                                >
                                    <ArrowRight
                                        aria-hidden
                                        className="size-6 rtl:-scale-x-100"
                                    />
                                </button>
                            </div>
                        )}
                    </div>

                    <p className="mt-6 mb-0 text-sm text-tm-300">
                        <Link to="/projects" className="hover:text-[#62a92b]">
                            {t('work.viewArchive')} →
                        </Link>
                    </p>
                </div>
            </GlowCard>
        </div>
    );
}
