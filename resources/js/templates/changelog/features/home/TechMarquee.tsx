import { accentForIndex, accentText } from '@/templates/changelog/lib/accents';
import type { SkillGroup } from '@/lib/content';
import { cn } from '@/lib/utils';

const MARKS = ['✦', '◆', '●', '▲'];

export function TechMarquee({ groups }: { groups: SkillGroup[] }) {
    const names = groups.flatMap((group) =>
        group.skills.map((skill) => skill.name),
    );
    const loop = [...names, ...names];

    return (
        <div
            aria-hidden
            dir="ltr"
            className="relative overflow-hidden border-y border-line bg-surface/60 [mask-image:linear-gradient(to_right,transparent,black_8%,black_92%,transparent)] py-4"
        >
            <ul className="marquee">
                {loop.map((name, index) => (
                    <li
                        key={`${name}-${index}`}
                        className="flex items-center gap-8 pe-8 whitespace-nowrap"
                    >
                        <span className="font-display text-2xl font-semibold tracking-[-0.03em] text-ink md:text-3xl">
                            {name}
                        </span>
                        <span
                            className={cn(
                                'text-lg',
                                index % 4 === 3
                                    ? 'text-signal-ink'
                                    : accentText[accentForIndex(index)],
                            )}
                        >
                            {MARKS[index % MARKS.length]}
                        </span>
                    </li>
                ))}
            </ul>
        </div>
    );
}
