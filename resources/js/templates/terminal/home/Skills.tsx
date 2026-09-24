import type { SkillGroup } from '@/lib/content';
import { useTerminalCopy } from '../copy';
import { Kicker } from '../components/Kicker';
import { Marquee } from '../components/Marquee';
import { Orbit } from '../components/Orbit';
import { SkillTile } from '../components/SkillTile';

export function Skills({ groups }: { groups: SkillGroup[] }) {
    const c = useTerminalCopy();
    const skills = groups.flatMap((group) => group.skills);
    const half = Math.ceil(skills.length / 2);

    return (
        <div className="tm-container pt-8">
            <section
                id="stack"
                aria-labelledby="stack-title"
                className="tm-box relative overflow-hidden py-[60px]"
            >
                <Orbit className="-end-[35px] -top-[35px]" />
                <div className="relative text-center">
                    <Kicker center>{c('skills.kicker')}</Kicker>
                    <h2
                        id="stack-title"
                        className="mt-1 text-[clamp(1.5rem,3vw,2.1875rem)] font-medium"
                    >
                        {c('skills.title')}
                    </h2>
                </div>

                <div className="relative mt-12 grid grid-cols-1 items-center gap-12 px-3 lg:grid-cols-2 lg:gap-0 lg:px-12">
                    <div className="flex flex-col items-center">
                        <Marquee
                            reverse
                            duration={32}
                            label={c('skills.title')}
                            className="w-full md:w-[85%]"
                        >
                            {skills.slice(0, half).map((skill) => (
                                <SkillTile
                                    key={skill.id}
                                    name={skill.name}
                                    size="lg"
                                    tooltip
                                />
                            ))}
                        </Marquee>
                        <Marquee
                            duration={28}
                            label={c('skills.title')}
                            className="w-[85%] md:w-[65%]"
                        >
                            {skills.slice(half).map((skill) => (
                                <SkillTile
                                    key={skill.id}
                                    name={skill.name}
                                    size="lg"
                                    tooltip
                                />
                            ))}
                        </Marquee>
                    </div>

                    <div className="md:border-s md:border-tm-border">
                        <ul className="mx-auto flex max-w-[500px] list-disc flex-col gap-4 ps-8 marker:text-tm-300">
                            {groups.map((group) => (
                                <li key={group.category.id}>
                                    <span className="flex flex-col gap-1 md:flex-row md:gap-2">
                                        <span className="text-nowrap text-ink">
                                            {group.category.label}:
                                        </span>
                                        <span lang="en" className="text-tm-300">
                                            {group.skills
                                                .map((skill) => skill.name)
                                                .join(', ')}
                                        </span>
                                    </span>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </section>
        </div>
    );
}
