import { Link } from '@/lib/router';
import { ArrowLeft, ArrowRight, ArrowUpRight } from 'lucide-react';
import type { ReactNode } from 'react';
import { ProjectStatusBadge } from '@/templates/changelog/components/content/ProjectStatusBadge';
import { ResponsiveImage } from '@/shared/ui/ResponsiveImage';
import { Tag } from '@/templates/changelog/components/ui/Tag';
import type { DictionaryKey } from '@/i18n/dictionary';
import { useReveal } from '@/hooks/useReveal';
import { useTranslation } from '@/hooks/useTranslation';
import type { ProjectDetail } from '@/lib/content';
import { accentTint, categoryAccent } from '@/templates/changelog/lib/accents';
import { cn } from '@/lib/utils';
import { ArchitectureDiagram } from '@/shared/content/ArchitectureDiagram';

interface CaseSectionProps {
    id: string;
    index: number;
    titleKey: DictionaryKey;
    children: ReactNode;
}

function CaseSection({ id, index, titleKey, children }: CaseSectionProps) {
    const { t } = useTranslation();
    const revealRef = useReveal<HTMLElement>();

    return (
        <section
            ref={revealRef}
            id={id}
            aria-labelledby={`${id}-heading`}
            className="reveal border-line scroll-mt-28 border-t py-12 md:py-16"
        >
            <div className="mb-8 flex items-baseline gap-4">
                <span className="ltr-isolate text-signal-ink font-mono text-xs">
                    {String(index).padStart(2, '0')}
                </span>
                <h2
                    id={`${id}-heading`}
                    className="font-display text-ink text-4xl leading-none md:text-5xl"
                >
                    {t(titleKey)}
                </h2>
            </div>
            {children}
        </section>
    );
}

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

export function CaseStudy({ project }: { project: ProjectDetail }) {
    const { t } = useTranslation();
    const accent = categoryAccent[project.category];

    const sections: SectionDefinition[] = [
        {
            id: 'overview',
            titleKey: 'case.overview',
            visible: true,
            render: () => (
                <div className="prose-editorial max-w-2xl">
                    {project.overview.map((paragraph) => (
                        <p key={paragraph}>{paragraph}</p>
                    ))}
                </div>
            ),
        },
        {
            id: 'problem',
            titleKey: 'case.problem',
            visible: project.problem.length > 0,
            render: () => (
                <div className="max-w-2xl space-y-5">
                    {project.problem.map((paragraph, index) => (
                        <p
                            key={paragraph}
                            className={cn(
                                index === 0
                                    ? 'font-display text-ink text-[1.75rem] leading-snug'
                                    : 'prose-editorial',
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
                <ol className="border-line bg-line grid gap-px border md:grid-cols-2">
                    {project.approach.map((step, index) => (
                        <li
                            key={step.title}
                            className={cn(
                                'bg-paper p-6',
                                index === 0 &&
                                    project.approach.length % 2 === 1 &&
                                    'md:col-span-2',
                            )}
                        >
                            <span className="ltr-isolate text-ink-subtle font-mono text-xs">
                                step.{index + 1}
                            </span>
                            <h3 className="text-ink mt-3 text-lg font-semibold">
                                {step.title}
                            </h3>
                            <p className="text-ink-muted mt-2 text-[0.9375rem] leading-relaxed">
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
            render: () =>
                project.architecture && (
                    <ArchitectureDiagram architecture={project.architecture} />
                ),
        },
        {
            id: 'features',
            titleKey: 'case.features',
            visible: project.features.length > 0,
            render: () => (
                <ul className="grid gap-x-10 gap-y-6 sm:grid-cols-2">
                    {project.features.map((feature) => (
                        <li
                            key={feature.title}
                            className="border-signal border-s-2 ps-4"
                        >
                            <h3 className="text-ink font-semibold">
                                {feature.title}
                            </h3>
                            <p className="text-ink-muted mt-1 text-[0.9375rem] leading-relaxed">
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
                <div className="flex flex-col gap-8">
                    {project.challenges.map((challenge) => (
                        <div
                            key={challenge.title}
                            className="grid gap-2 md:grid-cols-[14rem_minmax(0,1fr)] md:gap-8"
                        >
                            <h3 className="font-display text-ink text-2xl leading-tight">
                                {challenge.title}
                            </h3>
                            <p className="prose-editorial">
                                {challenge.description}
                            </p>
                        </div>
                    ))}
                </div>
            ),
        },
        {
            id: 'metrics',
            titleKey: 'case.metrics',
            visible: project.metrics.length > 0,
            render: () => (
                <dl className="border-line bg-line grid grid-cols-2 gap-px border lg:grid-cols-4">
                    {project.metrics.map((metric) => (
                        <div
                            key={metric.id}
                            className="bg-paper flex flex-col gap-2 p-5 md:p-6"
                        >
                            <dt className="text-ink order-2 text-sm font-medium">
                                {metric.label}
                            </dt>
                            <dd className="ltr-isolate font-display text-ink order-1 self-start text-5xl leading-none">
                                {metric.value}
                            </dd>
                            <dd className="text-ink-subtle order-3 font-mono text-[0.6875rem]">
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
                <div className="grid gap-6 md:grid-cols-2">
                    {project.gallery.map((image, index) => (
                        <figure key={image.src}>
                            <div className="group border-line relative overflow-hidden rounded-2xl border">
                                <ResponsiveImage
                                    image={image}
                                    sizes="(min-width: 1024px) 32vw, (min-width: 768px) 45vw, 92vw"
                                    className="aspect-[4/3] transition-transform duration-700 group-hover:scale-[1.04]"
                                />
                                <span
                                    aria-hidden
                                    className={cn(
                                        accentTint[accent],
                                        'group-hover:opacity-75',
                                    )}
                                />
                            </div>
                            <figcaption className="text-ink-muted mt-3 flex gap-3 text-sm leading-relaxed">
                                <span className="ltr-isolate text-ink-subtle shrink-0 font-mono text-xs">
                                    {String(index + 1).padStart(2, '0')}
                                </span>
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
                                    className="inline-flex"
                                >
                                    <Tag className="hover:border-ink hover:text-ink px-2 py-1 text-xs transition-colors">
                                        {technology}
                                    </Tag>
                                </Link>
                            </li>
                        ))}
                    </ul>
                    {project.links.length > 0 && (
                        <div>
                            <h3 className="eyebrow text-ink-subtle mb-3">
                                {t('case.links')}
                            </h3>
                            <ul className="border-line border-t">
                                {project.links.map((link) => (
                                    <li key={link.url}>
                                        <a
                                            href={link.url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="group border-line text-ink flex items-center justify-between gap-4 border-b py-3"
                                        >
                                            <span className="flex items-baseline gap-3">
                                                <span className="text-ink-subtle w-16 font-mono text-[0.6875rem]">
                                                    {linkLabels[link.kind]}
                                                </span>
                                                <span className="link-draw-target font-medium">
                                                    {link.label}
                                                </span>
                                            </span>
                                            <ArrowUpRight
                                                aria-hidden
                                                className="text-ink-subtle group-hover:text-signal-ink size-4"
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

    const visibleSections = sections.filter((section) => section.visible);

    return (
        <article>
            <header className="border-line relative overflow-hidden border-b">
                <div aria-hidden className="aurora">
                    <span />
                    <span />
                    <span />
                </div>
                <div
                    aria-hidden
                    className="baseline-texture pointer-events-none absolute inset-0"
                />
                <div className="shell relative pt-10 pb-12 md:pt-14">
                    <nav
                        aria-label={t('common.breadcrumb')}
                        className="mb-10 flex items-center justify-between gap-4"
                    >
                        <Link
                            to="/projects"
                            className="group text-ink-muted hover:text-ink inline-flex items-center gap-2 text-sm"
                        >
                            <ArrowLeft
                                aria-hidden
                                className="size-4 transition-transform group-hover:-translate-x-0.5 rtl:-scale-x-100 rtl:group-hover:translate-x-0.5"
                            />
                            <span className="link-draw-target">
                                {t('case.back')}
                            </span>
                        </Link>
                        <span className="version-label">{project.version}</span>
                    </nav>

                    <div className="editorial-grid gap-y-10">
                        <div lang="en" className="col-span-4 md:col-span-8">
                            <p className="flex flex-wrap items-center gap-3">
                                <span className="eyebrow text-signal-ink">
                                    {t(`category.${project.category}`)}
                                </span>
                                <span className="ltr-isolate text-ink-subtle font-mono text-xs">
                                    {project.year}
                                </span>
                                <ProjectStatusBadge status={project.status} />
                            </p>
                            <h1 className="font-display text-ink mt-5 text-6xl leading-[0.92] tracking-[-0.02em] sm:text-7xl lg:text-8xl">
                                {project.title}
                            </h1>
                            <p className="font-display text-ink-muted mt-6 max-w-2xl text-[1.75rem] leading-snug md:text-[2rem]">
                                {project.tagline}
                            </p>
                        </div>
                        <dl className="border-line col-span-4 grid grid-cols-2 gap-x-6 gap-y-5 self-end border-t pt-5 md:col-span-4 md:grid-cols-1 lg:grid-cols-2">
                            {[
                                { label: t('case.role'), value: project.role },
                                { label: t('case.team'), value: project.team },
                                {
                                    label: t('case.timeline'),
                                    value: project.timeline,
                                },
                                {
                                    label: t('case.company'),
                                    value:
                                        project.company?.name ??
                                        t('case.independent'),
                                },
                            ].map((item) => (
                                <div key={item.label}>
                                    <dt className="eyebrow text-ink-subtle">
                                        {item.label}
                                    </dt>
                                    <dd
                                        lang="en"
                                        className="text-ink mt-1.5 text-[0.9375rem] leading-snug"
                                    >
                                        {item.value}
                                    </dd>
                                </div>
                            ))}
                        </dl>
                    </div>
                </div>
            </header>

            <div className="shell pt-10">
                <div className="border-line shadow-lift relative overflow-hidden rounded-3xl border">
                    <ResponsiveImage
                        image={project.cover}
                        sizes="(min-width: 1440px) 1344px, 100vw"
                        priority
                        className="aspect-[16/9]"
                    />
                    <span aria-hidden className={accentTint[accent]} />
                </div>
            </div>

            <div className="shell editorial-grid pt-8 pb-8">
                <aside className="col-span-4 hidden md:col-span-3 md:block">
                    <nav
                        aria-label={t('case.contents')}
                        className="sticky top-[calc(var(--header-height)+2rem)] pt-12"
                    >
                        <p className="eyebrow text-ink-subtle mb-4">
                            {t('case.contents')}
                        </p>
                        <ol className="border-line flex flex-col gap-2 border-s">
                            {visibleSections.map((section, index) => (
                                <li key={section.id}>
                                    <a
                                        href={`#${section.id}`}
                                        className="text-ink-muted hover:border-ink hover:text-ink -ms-px flex items-baseline gap-3 border-s border-transparent ps-4 text-sm"
                                    >
                                        <span className="ltr-isolate text-ink-subtle font-mono text-[0.6875rem]">
                                            {String(index + 1).padStart(2, '0')}
                                        </span>
                                        {t(section.titleKey)}
                                    </a>
                                </li>
                            ))}
                        </ol>
                    </nav>
                </aside>

                <div lang="en" className="col-span-4 md:col-span-9">
                    {visibleSections.map((section, index) => (
                        <CaseSection
                            key={section.id}
                            id={section.id}
                            index={index + 1}
                            titleKey={section.titleKey}
                        >
                            {section.render()}
                        </CaseSection>
                    ))}

                    {project.relatedArticles.length > 0 && (
                        <section
                            aria-labelledby="related-writing"
                            className="border-line border-t py-12"
                        >
                            <h2
                                id="related-writing"
                                className="eyebrow text-ink-subtle mb-4"
                            >
                                {t('case.relatedWriting')}
                            </h2>
                            <ul className="flex flex-col gap-2">
                                {project.relatedArticles.map((article) => (
                                    <li key={article.slug}>
                                        <Link
                                            to="/writing/$slug"
                                            params={{ slug: article.slug }}
                                            className="link-draw font-display text-ink text-2xl"
                                        >
                                            {article.title}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </section>
                    )}
                </div>
            </div>

            <nav
                aria-label={t('case.adjacent')}
                className="border-line border-t"
            >
                <div className="shell grid md:grid-cols-2">
                    {[
                        {
                            reference: project.previous,
                            label: t('case.previous'),
                            Icon: ArrowLeft,
                            align: 'start' as const,
                        },
                        {
                            reference: project.next,
                            label: t('case.next'),
                            Icon: ArrowRight,
                            align: 'end' as const,
                        },
                    ].map(({ reference, label, Icon, align }) =>
                        reference ? (
                            <Link
                                key={align}
                                to="/projects/$slug"
                                params={{ slug: reference.slug }}
                                className={cn(
                                    'group flex flex-col gap-3 py-10 md:py-14',
                                    align === 'end'
                                        ? 'border-line border-t md:items-end md:border-s md:border-t-0 md:ps-10 md:text-end'
                                        : 'md:pe-10',
                                )}
                            >
                                <span className="text-ink-subtle flex items-center gap-2 text-sm">
                                    {align === 'start' && (
                                        <Icon
                                            aria-hidden
                                            className="size-4 rtl:-scale-x-100"
                                        />
                                    )}
                                    {label}
                                    {align === 'end' && (
                                        <Icon
                                            aria-hidden
                                            className="size-4 rtl:-scale-x-100"
                                        />
                                    )}
                                </span>
                                <span
                                    lang="en"
                                    className="link-draw-target font-display text-ink text-4xl md:text-5xl"
                                >
                                    {reference.title}
                                </span>
                            </Link>
                        ) : (
                            <span
                                key={align}
                                aria-hidden
                                className={cn(
                                    align === 'end' &&
                                        'md:border-line md:border-s',
                                )}
                            />
                        ),
                    )}
                </div>
            </nav>
        </article>
    );
}
