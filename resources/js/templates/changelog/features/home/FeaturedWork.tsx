import { Link } from '@/lib/router';
import { ArrowRight } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useMemo, useState } from 'react';
import { ProjectCard } from '@/templates/changelog/components/content/ProjectCard';
import {
    FilterGroup,
    type FilterOption,
} from '@/templates/changelog/components/ui/FilterGroup';
import { Section } from '@/templates/changelog/components/ui/Section';
import { sectionIndex } from '@/config/navigation';
import { useTranslation } from '@/hooks/useTranslation';
import type { Project, ProjectCategory } from '@/types/content';

type CategoryFilter = ProjectCategory | 'all';

export function FeaturedWork({ projects }: { projects: Project[] }) {
    const { t } = useTranslation();
    const [category, setCategory] = useState<CategoryFilter>('all');

    const options = useMemo<FilterOption<CategoryFilter>[]>(() => {
        const categories = [
            ...new Set(projects.map((project) => project.category)),
        ];
        return [
            { value: 'all', label: t('category.all'), count: projects.length },
            ...categories.map((value) => ({
                value,
                label: t(`category.${value}`),
                count: projects.filter((project) => project.category === value)
                    .length,
            })),
        ];
    }, [projects, t]);

    const visibleProjects =
        category === 'all'
            ? projects
            : projects.filter((project) => project.category === category);

    return (
        <Section
            id="work"
            index={sectionIndex('work')}
            label={t('section.work')}
            version="releases/"
            title={t('work.title')}
            intro={t('work.intro')}
            action={
                <Link
                    to="/projects"
                    className="group text-ink inline-flex items-center gap-2 text-sm font-medium"
                >
                    <span className="link-draw-target">
                        {t('work.viewArchive')}
                    </span>
                    <ArrowRight
                        aria-hidden
                        className="size-4 transition-transform group-hover:translate-x-0.5 rtl:-scale-x-100"
                    />
                </Link>
            }
        >
            <FilterGroup
                label={t('work.filterLabel')}
                options={options}
                value={category}
                onChange={setCategory}
                className="mb-8"
            />
            <p className="sr-only" aria-live="polite">
                {t('common.results', { count: visibleProjects.length })}
            </p>
            <motion.ul
                layout
                className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
            >
                <AnimatePresence mode="popLayout" initial={false}>
                    {visibleProjects.map((project) => (
                        <motion.li
                            key={project.id}
                            layout
                            initial={{ opacity: 0, y: 12 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, scale: 0.97 }}
                            transition={{
                                duration: 0.35,
                                ease: [0.22, 1, 0.36, 1],
                            }}
                        >
                            <ProjectCard project={project} />
                        </motion.li>
                    ))}
                </AnimatePresence>
            </motion.ul>
        </Section>
    );
}
