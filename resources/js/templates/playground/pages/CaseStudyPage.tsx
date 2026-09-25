import { Link } from '@/lib/router';
import { ArrowLeft, ArrowRight, ArrowUpRight, ChevronDown } from 'lucide-react';
import type { ReactNode } from 'react';
import type { DictionaryKey } from '@/i18n/dictionary';
import { useTranslation } from '@/hooks/useTranslation';
import type { ProjectDetail, ProjectReference } from '@/lib/content';
import { cn } from '@/lib/utils';
import { ArchitectureDiagram } from '@/shared/content/ArchitectureDiagram';
import { CountUp } from '@/shared/ui/CountUp';
import { ResponsiveImage } from '@/shared/ui/ResponsiveImage';
import type { CaseStudyPageProps } from '@/templates/types';
import { usePlaygroundCopy } from '../copy';
import { categoryPop, popBg, popForIndex, tiltForIndex } from '../lib/pops';
import { useActiveSection } from '../lib/useActiveSection';
import { ArticleCard } from '../components/ArticleCard';
import { popButtonClasses } from '../components/PopButton';
import { Reveal } from '../components/Reveal';
import { Sticker } from '../components/Sticker';

interface SectionDefinition {
    id: string;
    titleKey: DictionaryKey;
    visible: boolean;
    render: () => ReactNode;
}

const linkLabels: Record<ProjectDetail['links'][number]['kind'], string> = {
    live: 'Live',
    source: 'Source',
    writeup: 'Write-up',
    talk: 'Talk',
};

function CaseSection({
    id,
    index,
    title,
    children,
}: {
    id: string;
    index: number;
    title: string;
    children: ReactNode;
}) {
    return (
        <Reveal
            as="section"
            id={id}
            labelledBy={`${id}-heading`}
            className="scroll-mt-28 py-10 md:py-14"
        >
            <div className="mb-8 flex items-center gap-4">
                <span
                    className={cn(
                        'pg-display ltr-isolate inline-flex size-14 shrink-0 items-center justify-center rounded-full border-2 border-edge text-xl text-on-pop',
                        popBg[popForIndex(index)],
                    )}
                >
                    {String(index + 1).padStart(2, '0')}
                </span>
                <h2
                    id={`${id}-heading`}
                    className="pg-display text-[clamp(2rem,5vw,3.5rem)] text-ink"
                >
                    {title}
                </h2>
            </div>
            <div lang="en">{children}</div>
        </Reveal>
    );
}

function buildSections(
    project: ProjectDetail,
    t: (key: DictionaryKey) => string,
    stepLabel: (index: number) => string,
): SectionDefinition[] {
    return [
        {
            id: 'overview',
            titleKey: 'case.overview',
            visible: true,
            render: () => (
                <div className="grid gap-5 lg:grid-cols-2">
                    {project.overview.map((paragraph, index) => (
                        <p
                            key={paragraph}
                            className={cn(
                                index === 0
                                    ? 'text-2xl leading-snug font-semibold text-ink lg:col-span-2'
                                    : 'pg-prose',
                            )}
                        >
                            {paragraph}
                        </p>
                    ))}
                </div>
            ),
        },
        {
            id: 'problem',
            titleKey: 'case.problem',
            visible: project.problem.length > 0,
            render: () => (
                <div className="pg-card flex flex-col gap-4 bg-pop-red p-6 text-on-pop md:p-10">
                    {project.problem.map((paragraph, index) => (
                        <p
                            key={paragraph}
                            className={cn(
                                index === 0
                                    ? 'text-2xl leading-snug font-bold md:text-3xl'
                                    : 'text-lg leading-relaxed font-medium',
                            )}
                        >
                            {paragraph}
                        </p>
                    ))}
                </div>
            ),
        },
        {
            id: 'approach',
            titleKey: 'case.approach',
            visible: project.approach.length > 0,
            render: () => (
                <ol className="grid gap-5 md:grid-cols-2">
                    {project.approach.map((step, index) => (
                        <li
                            key={step.title}
                            className="pg-card pg-press flex flex-col gap-3 p-6"
                        >
                            <Sticker
                                pop={popForIndex(index + 1)}
                                tilt={tiltForIndex(index)}
                                className="self-start"
                            >
                                {stepLabel(index + 1)}
                            </Sticker>
                            <h3 className="text-xl font-bold text-ink">
                                {step.title}
                            </h3>
                            <p className="text-base leading-relaxed text-ink-muted">
                                {step.description}
                            </p>
                        </li>
                    ))}
                </ol>
            ),
        },
        {
            id: 'architecture',
            titleKey: 'case.architecture',
            visible: (project.architecture?.nodes.length ?? 0) > 0,
            render: () => (
                <div className="pg-card overflow-hidden p-3 md:p-5">
                    {project.architecture && (
                        <ArchitectureDiagram
                            architecture={project.architecture}
                        />
                    )}
                </div>
            ),
        },
        {
            id: 'features',
            titleKey: 'case.features',
            visible: project.features.length > 0,
            render: () => (
                <ul className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    {project.features.map((feature, index) => (
                        <li
                            key={feature.title}
                            className={cn(
                                'pg-card pg-press flex flex-col gap-2 p-6 text-on-pop',
                                popBg[popForIndex(index)],
                            )}
                        >
                            <span aria-hidden className="text-2xl">
                                ✦
                            </span>
                            <h3 className="text-lg font-bold">
                                {feature.title}
                            </h3>
                            <p className="text-[0.9375rem] leading-relaxed font-medium">
                                {feature.description}
                            </p>
                        </li>
                    ))}
                </ul>
            ),
        },
        {
            id: 'challenges',
            titleKey: 'case.challenges',
            visible: project.challenges.length > 0,
            render: () => (
                <div className="flex flex-col gap-4">
                    {project.challenges.map((challenge, index) => (
                        <details
                            key={challenge.title}
                            open={index === 0}
                            className="pg-card group overflow-hidden"
                        >
                            <summary className="flex cursor-pointer list-none items-center justify-between gap-4 p-5 text-lg font-bold text-ink md:p-6 [&::-webkit-details-marker]:hidden">
                                {challenge.title}
                                <span
                                    aria-hidden
                                    className="inline-flex size-9 shrink-0 items-center justify-center rounded-full border-2 border-edge bg-pop-yellow text-on-pop transition-transform duration-300 group-open:rotate-180"
                                >
                                    <ChevronDown
                                        className="size-5"
                                        strokeWidth={2.5}
                                    />
                                </span>
                            </summary>
                            <p className="border-t-2 border-edge p-5 text-base leading-relaxed text-ink-muted md:p-6">
                                {challenge.description}
                            </p>
                        </details>
                    ))}
                </div>
            ),
        },
        {
            id: 'metrics',
            titleKey: 'case.metrics',
            visible: project.metrics.length > 0,
            render: () => (
                <dl className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    {project.metrics.map((metric, index) => (
                        <div
                            key={metric.id}
                            className={cn(
                                'pg-card flex flex-col gap-2 p-6 text-on-pop',
                                popBg[popForIndex(index + 2)],
                            )}
                        >
                            <dt className="order-2 text-base font-bold">
                                {metric.label}
                            </dt>
                            <dd className="pg-display ltr-isolate order-1 self-start text-5xl">
                                <CountUp value={metric.value} />
                            </dd>
                            <dd className="order-3 font-mono text-xs font-bold opacity-80">
                                {metric.detail}
                            </dd>
                        </div>
                    ))}
                </dl>
            ),
        },
        {
            id: 'gallery',
            titleKey: 'case.gallery',
            visible: project.gallery.length > 0,
            render: () => (
                <div className="grid gap-8 md:grid-cols-2">
                    {project.gallery.map((image, index) => (
                        <figure
                            key={image.src}
                            style={{
                                rotate: `${tiltForIndex(index) * 0.35}deg`,
                            }}
                            className="pg-card pg-press bg-raised p-3 pb-4"
                        >
                            <ResponsiveImage
                                image={image}
                                sizes="(min-width: 1024px) 40vw, (min-width: 768px) 45vw, 92vw"
                                className="aspect-[4/3] rounded-lg border-2 border-edge"
                            />
                            <figcaption className="mt-3 px-1 font-mono text-xs leading-relaxed font-bold text-ink-muted">
                                {image.caption}
                            </figcaption>
                        </figure>
                    ))}
                </div>
            ),
        },
        {
            id: 'stack',
            titleKey: 'case.stack',
            visible: true,
            render: () => (
                <div className="grid gap-10 md:grid-cols-2">
                    <ul className="flex flex-wrap content-start gap-2">
                        {project.stack.map((technology) => (
                            <li key={technology}>
                                <Link
                                    to="/projects"
                                    search={{ tech: technology }}
                                    className="pg-chip"
                                >
                                    {technology}
                                </Link>
                            </li>
                        ))}
                    </ul>
                    {project.links.length > 0 && (
                        <div>
                            <h3 className="pg-label mb-4 text-ink">
                                {t('case.links')}
                            </h3>
                            <ul className="flex flex-wrap gap-3">
                                {project.links.map((link, index) => (
                                    <li key={link.url}>
                                        <a
                                            href={link.url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className={popButtonClasses({
                                                tone: popForIndex(index),
                                            })}
                                        >
                                            <span className="font-mono text-xs opacity-70">
                                                {linkLabels[link.kind]}
                                            </span>
                                            {link.label}
                                            <ArrowUpRight
                                                aria-hidden
                                                className="size-4"
                                                strokeWidth={2.5}
                                            />
                                            <span className="sr-only">
                                                {t('common.opensNewTab')}
                                            </span>
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </div>
            ),
        },
    ];
}

function AdjacentCard({
    reference,
    label,
    direction,
}: {
    reference: ProjectReference | null;
    label: string;
    direction: 'previous' | 'next';
}) {
    if (!reference) return <span aria-hidden />;
    const isNext = direction === 'next';
    const Icon = isNext ? ArrowRight : ArrowLeft;

    return (
        <Link
            to="/projects/$slug"
            params={{ slug: reference.slug }}
            className={cn(
                'pg-card pg-press flex flex-col gap-4 p-6 md:p-8',
                isNext
                    ? 'bg-pop-green text-on-pop md:items-end md:text-end'
                    : 'bg-pop-purple text-on-pop',
            )}
        >
            <span className="pg-label flex items-center gap-2">
                {!isNext && (
                    <Icon
                        aria-hidden
                        className="size-4 rtl:-scale-x-100"
                        strokeWidth={2.5}
                    />
                )}
                {label}
                {isNext && (
                    <Icon
                        aria-hidden
                        className="size-4 rtl:-scale-x-100"
                        strokeWidth={2.5}
                    />
                )}
            </span>
            <span
                lang="en"
                className="pg-display pg-keep-case text-[clamp(2rem,4vw,3rem)]"
            >
                {reference.title}
            </span>
        </Link>
    );
}

export function CaseStudyPage({ project }: CaseStudyPageProps) {
    const { t } = useTranslation();
    const p = usePlaygroundCopy();
    const sections = buildSections(project, t, (index) =>
        p('case.step', { index }),
    ).filter((section) => section.visible);
    const activeId = useActiveSection(sections.map((section) => section.id));
    const pop = categoryPop[project.category];

    const facts = [
        { label: t('case.role'), value: project.role },
        { label: t('case.team'), value: project.team },
        { label: t('case.timeline'), value: project.timeline },
        {
            label: t('case.company'),
            value: project.company?.name ?? t('case.independent'),
        },
    ];

    return (
        <article>
            <header className="pg-shell pt-6 pb-10 md:pt-10">
                <nav aria-label={t('common.breadcrumb')} className="mb-8">
                    <Link
                        to="/projects"
                        className={popButtonClasses({
                            tone: 'plain',
                            size: 'sm',
                        })}
                    >
                        <ArrowLeft
                            aria-hidden
                            className="size-4 rtl:-scale-x-100"
                            strokeWidth={2.5}
                        />
                        {t('case.back')}
                    </Link>
                </nav>
                <div className="grid items-center gap-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">
                    <div className="flex flex-col items-start gap-6">
                        <div className="flex flex-wrap gap-3">
                            <Sticker pop={pop} tilt={-5}>
                                {t(`category.${project.category}`)}
                            </Sticker>
                            <Sticker pop="yellow" tilt={3}>
                                {t(`projectStatus.${project.status}`)}
                            </Sticker>
                            <Sticker pop="pink" tilt={-2}>
                                <span className="ltr-isolate">
                                    {project.version}
                                </span>
                            </Sticker>
                        </div>
                        <h1
                            lang="en"
                            className="pg-display text-[clamp(3rem,9vw,7.5rem)] text-ink"
                        >
                            {project.title}
                        </h1>
                        <p
                            lang="en"
                            className="max-w-xl text-xl leading-snug font-semibold text-ink-muted md:text-2xl"
                        >
                            {project.tagline}
                        </p>
                    </div>
                    <div className="relative">
                        <div
                            className={cn(
                                'absolute inset-0 translate-x-3 translate-y-3 rotate-[3deg] rounded-[1.5rem] border-2 border-edge',
                                popBg[pop],
                            )}
                        />
                        <div className="pg-card relative rotate-[-2deg] overflow-hidden rounded-[1.5rem]">
                            <ResponsiveImage
                                image={project.cover}
                                sizes="(min-width: 64rem) 45vw, 100vw"
                                priority
                                className="aspect-[4/3]"
                            />
                        </div>
                        <span className="ltr-isolate absolute end-6 -top-4 rotate-[8deg] rounded-full border-2 border-edge bg-raised px-4 py-2 font-display text-2xl font-black text-ink shadow-[var(--pg-shadow)]">
                            {project.year}
                        </span>
                    </div>
                </div>

                <dl
                    aria-label={p('case.facts')}
                    className="mt-12 grid grid-cols-2 gap-4 lg:grid-cols-4"
                >
                    {facts.map((fact, index) => (
                        <div
                            key={fact.label}
                            className={cn(
                                'pg-card p-5 text-on-pop',
                                popBg[popForIndex(index + 3)],
                            )}
                        >
                            <dt className="pg-label">{fact.label}</dt>
                            <dd
                                lang="en"
                                className="mt-2 text-base leading-snug font-bold"
                            >
                                {fact.value}
                            </dd>
                        </div>
                    ))}
                </dl>
            </header>

            <nav
                aria-label={p('case.sections')}
                className="sticky top-3 z-30 my-4"
            >
                <div className="pg-shell">
                    <ol className="pg-card no-scrollbar flex gap-2 overflow-x-auto rounded-full p-1.5">
                        {sections.map((section) => (
                            <li key={section.id} className="shrink-0">
                                <a
                                    href={`#${section.id}`}
                                    aria-current={
                                        activeId === section.id
                                            ? 'location'
                                            : undefined
                                    }
                                    className="inline-flex h-9 items-center rounded-full px-4 text-sm font-bold whitespace-nowrap text-ink transition-colors hover:bg-pop-yellow hover:text-on-pop aria-[current=location]:bg-ink aria-[current=location]:text-paper"
                                >
                                    {t(section.titleKey)}
                                </a>
                            </li>
                        ))}
                    </ol>
                </div>
            </nav>

            <div className="pg-shell">
                {sections.map((section, index) => (
                    <CaseSection
                        key={section.id}
                        id={section.id}
                        index={index}
                        title={t(section.titleKey)}
                    >
                        {section.render()}
                    </CaseSection>
                ))}

                {project.relatedArticles.length > 0 && (
                    <section
                        aria-labelledby="related-writing"
                        className="py-10"
                    >
                        <h2
                            id="related-writing"
                            className="pg-label mb-6 text-ink"
                        >
                            {t('case.relatedWriting')}
                        </h2>
                        <ul className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {project.relatedArticles.map((article, index) => (
                                <li key={article.slug}>
                                    <ArticleCard
                                        article={article}
                                        index={index}
                                    />
                                </li>
                            ))}
                        </ul>
                    </section>
                )}
            </div>

            <nav
                aria-label={t('case.adjacent')}
                className="pg-shell mt-10 grid gap-6 md:grid-cols-2"
            >
                <AdjacentCard
                    reference={project.previous}
                    label={t('case.previous')}
                    direction="previous"
                />
                <AdjacentCard
                    reference={project.next}
                    label={t('case.next')}
                    direction="next"
                />
            </nav>
        </article>
    );
}
