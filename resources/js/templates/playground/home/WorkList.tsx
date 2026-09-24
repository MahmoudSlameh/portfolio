import { Link } from '@/lib/router';
import { ArrowUpRight } from 'lucide-react';
import {
    AnimatePresence,
    motion,
    useMotionValue,
    useReducedMotion,
    useSpring,
} from 'motion/react';
import { useState, type PointerEvent } from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import { cn } from '@/lib/utils';
import { ResponsiveImage } from '@/shared/ui/ResponsiveImage';
import type { Project, ProjectCategory } from '@/types/content';
import { usePlaygroundCopy } from '../copy';
import { categoryPop, popBg, popHoverBg } from '../lib/pops';
import { ChipGroup, type ChipOption } from '../components/ChipGroup';
import { popButtonClasses } from '../components/PopButton';
import { SectionHeading } from '../components/SectionHeading';

type CategoryFilter = ProjectCategory | 'all';

const PREVIEW_SPRING = { stiffness: 260, damping: 26, mass: 0.6 };

function useCursorPreview() {
    const x = useMotionValue(0);
    const y = useMotionValue(0);
    const springX = useSpring(x, PREVIEW_SPRING);
    const springY = useSpring(y, PREVIEW_SPRING);

    const handlePointerMove = (event: PointerEvent<HTMLElement>): void => {
        const bounds = event.currentTarget.getBoundingClientRect();
        x.set(event.clientX - bounds.left);
        y.set(event.clientY - bounds.top);
    };

    return { springX, springY, handlePointerMove };
}

export function WorkList({ projects }: { projects: Project[] }) {
    const { t } = useTranslation();
    const p = usePlaygroundCopy();
    const reduceMotion = useReducedMotion();
    const [category, setCategory] = useState<CategoryFilter>('all');
    const [hovered, setHovered] = useState<Project | null>(null);
    const { springX, springY, handlePointerMove } = useCursorPreview();

    const categories = [
        ...new Set(projects.map((project) => project.category)),
    ];
    const options: ChipOption<CategoryFilter>[] = [
        { value: 'all', label: p('work.all'), count: projects.length },
        ...categories.map((value) => ({
            value,
            label: t(`category.${value}`),
            count: projects.filter((project) => project.category === value)
                .length,
        })),
    ];
    const visible =
        category === 'all'
            ? projects
            : projects.filter((project) => project.category === category);

    return (
        <section
            id="work"
            aria-labelledby="work-title"
            className="pg-shell scroll-mt-8 py-16"
        >
            <SectionHeading
                id="work"
                kicker={p('work.kicker')}
                title={p('work.title')}
                pop="red"
                aside={
                    <Link
                        to="/projects"
                        className={popButtonClasses({ tone: 'ink' })}
                    >
                        {t('work.viewArchive')}
                        <ArrowUpRight
                            aria-hidden
                            className="size-4 rtl:-scale-x-100"
                            strokeWidth={2.5}
                        />
                    </Link>
                }
            />
            <ChipGroup
                label={t('work.filterLabel')}
                options={options}
                value={category}
                onChange={setCategory}
                className="mb-8"
            />

            <div
                className="relative"
                onPointerMove={reduceMotion ? undefined : handlePointerMove}
                onPointerLeave={() => setHovered(null)}
            >
                <ol className="pg-card overflow-hidden">
                    {visible.map((project, index) => {
                        const pop = categoryPop[project.category];
                        return (
                            <li
                                key={project.id}
                                className="border-edge border-b-2 last:border-b-0"
                            >
                                <Link
                                    to="/projects/$slug"
                                    params={{ slug: project.slug }}
                                    onPointerEnter={() => setHovered(project)}
                                    onFocus={() => setHovered(null)}
                                    className={cn(
                                        'group hover:text-on-pop grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-4 px-4 py-5 transition-colors duration-200 md:gap-8 md:px-8 md:py-7',
                                        popHoverBg[pop],
                                    )}
                                >
                                    <span className="ltr-isolate text-ink-subtle group-hover:text-on-pop font-mono text-sm font-bold">
                                        {String(index + 1).padStart(2, '0')}
                                    </span>
                                    <span className="flex min-w-0 flex-col gap-2 md:flex-row md:items-center md:gap-6">
                                        <span className="border-edge block aspect-[16/10] w-28 shrink-0 overflow-hidden rounded-lg border-2 md:hidden">
                                            <ResponsiveImage
                                                image={project.cover}
                                                sizes="7rem"
                                                className="h-full"
                                            />
                                        </span>
                                        <span className="min-w-0">
                                            <span
                                                lang="en"
                                                className="pg-display pg-keep-case text-ink group-hover:text-on-pop block text-[clamp(1.75rem,4vw,3.25rem)]"
                                            >
                                                {project.title}
                                            </span>
                                            <span
                                                lang="en"
                                                className="text-ink-muted group-hover:text-on-pop mt-1 block text-base"
                                            >
                                                {project.tagline}
                                            </span>
                                        </span>
                                    </span>
                                    <span className="flex flex-col items-end gap-2">
                                        <span
                                            className={cn(
                                                'pg-tag text-on-pop',
                                                popBg[pop],
                                            )}
                                        >
                                            {t(`category.${project.category}`)}
                                        </span>
                                        <span className="ltr-isolate font-mono text-sm font-bold">
                                            {project.year}
                                        </span>
                                    </span>
                                </Link>
                            </li>
                        );
                    })}
                </ol>

                {!reduceMotion && (
                    <AnimatePresence>
                        {hovered && (
                            <motion.div
                                key={hovered.id}
                                aria-hidden
                                style={{ x: springX, y: springY }}
                                initial={{
                                    opacity: 0,
                                    scale: 0.6,
                                    rotate: -12,
                                }}
                                animate={{ opacity: 1, scale: 1, rotate: -4 }}
                                exit={{ opacity: 0, scale: 0.6, rotate: 8 }}
                                transition={{
                                    type: 'spring',
                                    stiffness: 320,
                                    damping: 22,
                                }}
                                className="border-edge pointer-events-none absolute top-0 left-0 z-20 hidden w-72 -translate-x-1/2 -translate-y-[115%] overflow-hidden rounded-2xl border-2 shadow-[var(--pg-shadow-lg)] [@media(hover:hover)]:md:block"
                            >
                                <ResponsiveImage
                                    image={hovered.cover}
                                    sizes="18rem"
                                />
                            </motion.div>
                        )}
                    </AnimatePresence>
                )}
            </div>
        </section>
    );
}
