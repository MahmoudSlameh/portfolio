import { Link } from '@/lib/router';
import { ArrowUpRight } from 'lucide-react';
import { ProjectStatusBadge } from '@/templates/changelog/components/content/ProjectStatusBadge';
import { ResponsiveImage } from '@/shared/ui/ResponsiveImage';
import { Tag } from '@/templates/changelog/components/ui/Tag';
import { useTranslation } from '@/hooks/useTranslation';
import {
    accentPill,
    accentTint,
    categoryAccent,
} from '@/templates/changelog/lib/accents';
import { handleSpotlightMove } from '@/templates/changelog/lib/spotlight';
import { cn } from '@/lib/utils';
import type { Project } from '@/types/content';

interface ProjectCardProps {
    project: Project;
    priority?: boolean;
}

export function ProjectCard({ project, priority = false }: ProjectCardProps) {
    const { t } = useTranslation();

    return (
        <article
            lang="en"
            onPointerMove={handleSpotlightMove}
            className="group card-lift border-line bg-raised hover:border-electric/40 relative flex h-full flex-col overflow-hidden rounded-2xl border"
        >
            <div
                aria-hidden
                className="card-spotlight pointer-events-none absolute inset-0 z-10 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
            />
            <div className="relative overflow-hidden">
                <ResponsiveImage
                    image={project.cover}
                    sizes="(min-width: 1024px) 30vw, (min-width: 640px) 45vw, 92vw"
                    priority={priority}
                    className="aspect-[16/9] transition-transform duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06]"
                />
                <span
                    aria-hidden
                    className={cn(
                        accentTint[categoryAccent[project.category]],
                        'group-hover:opacity-80',
                    )}
                />
                <span className="version-label bg-raised/85 absolute start-3 top-3 border-transparent backdrop-blur-md">
                    {project.version}
                </span>
            </div>
            <div className="relative flex flex-1 flex-col gap-4 p-5 md:p-6">
                <div className="flex items-center justify-between gap-3">
                    <span className="flex items-center gap-2">
                        <span
                            className={cn(
                                'rounded-full px-2 py-0.5 text-[0.6875rem] font-semibold',
                                accentPill[categoryAccent[project.category]],
                            )}
                        >
                            {t(`category.${project.category}`)}
                        </span>
                        <span className="ltr-isolate text-ink-subtle font-mono text-xs">
                            {project.year}
                        </span>
                    </span>
                    <ProjectStatusBadge status={project.status} />
                </div>
                <h3 className="font-display text-ink text-[1.75rem] leading-tight">
                    <Link
                        to="/projects/$slug"
                        params={{ slug: project.slug }}
                        className="link-draw-target after:absolute after:inset-0 after:z-20 after:content-[''] focus-visible:outline-none"
                    >
                        {project.title}
                    </Link>
                </h3>
                <p className="text-ink-muted text-[0.9375rem] leading-relaxed">
                    {project.tagline}
                </p>
                <div className="mt-auto flex items-end justify-between gap-4 pt-2">
                    <ul
                        className="flex flex-wrap gap-1.5"
                        aria-label={t('case.stack')}
                    >
                        {project.stack.slice(0, 3).map((technology) => (
                            <li key={technology}>
                                <Tag>{technology}</Tag>
                            </li>
                        ))}
                    </ul>
                    <span
                        aria-hidden
                        className="border-line text-ink-subtle group-hover:bg-signal group-hover:text-on-signal inline-flex size-9 shrink-0 items-center justify-center rounded-full border transition-all duration-300 group-hover:rotate-45 group-hover:border-transparent rtl:-scale-x-100"
                    >
                        <ArrowUpRight className="size-4" />
                    </span>
                </div>
            </div>
            <span
                aria-hidden
                className="ring-signal-ink pointer-events-none absolute inset-0 rounded-2xl opacity-0 ring-2 ring-inset group-has-[:focus-visible]:opacity-100"
            />
        </article>
    );
}
