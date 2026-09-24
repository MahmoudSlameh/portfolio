import { Section } from '@/templates/changelog/components/ui/Section';
import { sectionIndex } from '@/config/navigation';
import { useTranslation } from '@/hooks/useTranslation';
import type { SkillGroup } from '@/lib/content';
import {
    accentFill,
    accentForIndex,
    type Accent,
} from '@/templates/changelog/lib/accents';
import { handleSpotlightMove } from '@/templates/changelog/lib/spotlight';
import { cn } from '@/lib/utils';
import type { Skill } from '@/types/content';

const SEGMENTS = 5;

function ProficiencyMeter({ skill, accent }: { skill: Skill; accent: Accent }) {
    const { t } = useTranslation();
    const level = skill.proficiency;
    if (level === null) return null;
    const isMastered = level === SEGMENTS;

    return (
        <div
            role="meter"
            aria-valuemin={1}
            aria-valuemax={SEGMENTS}
            aria-valuenow={level}
            aria-valuetext={t('stack.proficiency', { level })}
            aria-label={skill.name}
            className="flex w-full gap-[3px]"
        >
            {Array.from({ length: SEGMENTS }, (_, index) => (
                <span
                    key={index}
                    className={cn(
                        'h-1.5 flex-1 rounded-full transition-colors duration-500',
                        index < level
                            ? isMastered
                                ? 'bg-signal'
                                : accentFill[accent]
                            : 'bg-line',
                    )}
                />
            ))}
        </div>
    );
}

export function StackSection({ groups }: { groups: SkillGroup[] }) {
    const { t } = useTranslation();

    return (
        <Section
            id="stack"
            index={sectionIndex('stack')}
            label={t('section.stack')}
            version="package.json"
            title={t('stack.title')}
            intro={t('stack.intro')}
        >
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                {groups.map(({ category, skills }, groupIndex) => (
                    <div
                        key={category.id}
                        onPointerMove={handleSpotlightMove}
                        className="group card-lift border-line bg-raised/60 relative flex flex-col overflow-hidden rounded-2xl border p-6 md:p-7"
                    >
                        <div
                            aria-hidden
                            className="card-spotlight pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        />
                        <div className="border-line relative mb-4 flex flex-col gap-2 border-b pb-4">
                            <span
                                aria-hidden
                                className={cn(
                                    'mb-2 h-1 w-10 rounded-full',
                                    accentFill[accentForIndex(groupIndex)],
                                )}
                            />
                            <h3 className="font-display text-ink text-[1.75rem] leading-none">
                                {category.label}
                            </h3>
                            <p className="text-ink-subtle text-[0.8125rem]">
                                {category.description}
                            </p>
                        </div>
                        <ul className="relative flex flex-col">
                            {skills.map((skill) => (
                                <li
                                    key={skill.id}
                                    className="border-line flex flex-col gap-2 border-b border-dashed py-3 last:border-b-0"
                                >
                                    <span className="flex items-baseline justify-between gap-3">
                                        <span
                                            lang="en"
                                            className="text-ink text-[0.9375rem]"
                                        >
                                            {skill.name}
                                        </span>
                                        {skill.years !== null && (
                                            <span className="text-ink-subtle shrink-0 font-mono text-[0.6875rem]">
                                                {t('stack.years', {
                                                    count: skill.years,
                                                })}
                                            </span>
                                        )}
                                    </span>
                                    <ProficiencyMeter
                                        skill={skill}
                                        accent={accentForIndex(groupIndex)}
                                    />
                                </li>
                            ))}
                        </ul>
                    </div>
                ))}
            </div>
        </Section>
    );
}
