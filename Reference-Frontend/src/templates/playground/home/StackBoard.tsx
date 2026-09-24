import { useTranslation } from '@/hooks/useTranslation';
import type { SkillGroup } from '@/lib/content';
import { cn } from '@/lib/utils';
import type { Skill } from '@/types/content';
import { usePlaygroundCopy } from '../copy';
import { popBg, popForIndex } from '../lib/pops';
import { Reveal } from '../components/Reveal';
import { SectionHeading } from '../components/SectionHeading';

const LEVELS = [1, 2, 3, 4, 5] as const;

function SkillChip({ skill, groupIndex }: { skill: Skill; groupIndex: number }) {
  const { t } = useTranslation();
  const p = usePlaygroundCopy();

  return (
    <li className="flex items-center justify-between gap-3 rounded-xl border-2 border-edge bg-paper px-3 py-2.5">
      <span lang="en" className="min-w-0 truncate font-semibold text-ink">
        {skill.name}
      </span>
      <span className="flex shrink-0 items-center gap-2">
        <span role="img" aria-label={p('stack.level', { level: skill.proficiency })} className="flex gap-0.5">
          {LEVELS.map((level) => (
            <span
              key={level}
              className={cn(
                'size-2.5 rounded-full border-[1.5px] border-edge',
                level <= skill.proficiency ? popBg[popForIndex(groupIndex)] : 'bg-transparent',
              )}
            />
          ))}
        </span>
        <span className="ltr-isolate w-12 text-end font-mono text-[0.6875rem] font-bold text-ink-subtle">
          {t('stack.years', { count: skill.years })}
        </span>
      </span>
    </li>
  );
}

export function StackBoard({ groups }: { groups: SkillGroup[] }) {
  const { t, l } = useTranslation();
  const p = usePlaygroundCopy();

  return (
    <section id="stack" aria-labelledby="stack-title" className="pg-shell scroll-mt-8 py-16">
      <SectionHeading
        id="stack"
        kicker={p('stack.kicker')}
        title={p('stack.title')}
        pop="blue"
        aside={<p className="max-w-sm text-base text-ink-muted">{t('stack.intro')}</p>}
      />
      <div className="grid gap-5 md:grid-cols-2">
        {groups.map((group, index) => (
          <Reveal key={group.category.id} delay={(index % 2) * 80}>
            <article className="pg-card h-full overflow-hidden">
              <header className={cn('border-b-2 border-edge px-5 py-4 text-on-pop', popBg[popForIndex(index)])}>
                <h3 className="pg-display pg-keep-case text-2xl">{l(group.category.label)}</h3>
                <p className="mt-1 text-sm leading-snug font-medium">{l(group.category.description)}</p>
              </header>
              <ul className="flex flex-col gap-2 p-4">
                {group.skills.map((skill) => (
                  <SkillChip key={skill.id} skill={skill} groupIndex={index} />
                ))}
              </ul>
            </article>
          </Reveal>
        ))}
      </div>
    </section>
  );
}
