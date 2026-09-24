import { useTranslation } from '@/hooks/useTranslation';
import type { SkillGroup } from '@/lib/content';
import { cn } from '@/lib/utils';
import type { Skill } from '@/types/content';
import { usePlaygroundCopy } from '../copy';
import { popBg, popForIndex } from '../lib/pops';
import { Reveal } from '../components/Reveal';
import { SectionHeading } from '../components/SectionHeading';

const LEVELS = [1, 2, 3, 4, 5] as const;

function SkillChip({
    skill,
    groupIndex,
}: {
    skill: Skill;
    groupIndex: number;
}) {
    const { t } = useTranslation();
    const p = usePlaygroundCopy();

    return (
        <li className="border-edge bg-paper flex items-center justify-between gap-3 rounded-xl border-2 px-3 py-2.5">
            <span lang="en" className="text-ink min-w-0 truncate font-semibold">
                {skill.name}
            </span>
            <span className="flex shrink-0 items-center gap-2">
                {skill.proficiency !== null && (
                    <span
                        role="img"
                        aria-label={p('stack.level', {
                            level: skill.proficiency,
                        })}
                        className="flex gap-0.5"
                    >
                        {LEVELS.map((level) => (
                            <span
                                key={level}
                                className={cn(
                                    'border-edge size-2.5 rounded-full border-[1.5px]',
                                    level <= (skill.proficiency ?? 0)
                                        ? popBg[popForIndex(groupIndex)]
                                        : 'bg-transparent',
                                )}
                            />
                        ))}
                    </span>
                )}
                {skill.years !== null && (
                    <span className="ltr-isolate text-ink-subtle w-12 text-end font-mono text-[0.6875rem] font-bold">
                        {t('stack.years', { count: skill.years })}
                    </span>
                )}
            </span>
        </li>
    );
}

export function StackBoard({ groups }: { groups: SkillGroup[] }) {
    const { t } = useTranslation();
    const p = usePlaygroundCopy();

    return (
        <section
            id="stack"
            aria-labelledby="stack-title"
            className="pg-shell scroll-mt-8 py-16"
        >
            <SectionHeading
                id="stack"
                kicker={p('stack.kicker')}
                title={p('stack.title')}
                pop="blue"
                aside={
                    <p className="text-ink-muted max-w-sm text-base">
                        {t('stack.intro')}
                    </p>
                }
            />
            <div className="grid gap-5 md:grid-cols-2">
                {groups.map((group, index) => (
                    <Reveal key={group.category.id} delay={(index % 2) * 80}>
                        <article className="pg-card h-full overflow-hidden">
                            <header
                                className={cn(
                                    'border-edge text-on-pop border-b-2 px-5 py-4',
                                    popBg[popForIndex(index)],
                                )}
                            >
                                <h3 className="pg-display pg-keep-case text-2xl">
                                    {group.category.label}
                                </h3>
                                <p className="mt-1 text-sm leading-snug font-medium">
                                    {group.category.description}
                                </p>
                            </header>
                            <ul className="flex flex-col gap-2 p-4">
                                {group.skills.map((skill) => (
                                    <SkillChip
                                        key={skill.id}
                                        skill={skill}
                                        groupIndex={index}
                                    />
                                ))}
                            </ul>
                        </article>
                    </Reveal>
                ))}
            </div>
        </section>
    );
}
