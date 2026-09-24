import {
    Braces,
    Cloud,
    Database,
    LayoutTemplate,
    Sparkles,
    type LucideIcon,
} from 'lucide-react';
import { Fragment } from 'react';
import type { SkillGroup } from '@/lib/content';
import type { Profile } from '@/types/content';
import { useTerminalCopy } from '../copy';
import { GlowCard } from '../components/GlowCard';
import { Kicker } from '../components/Kicker';

const ICONS: Record<string, LucideIcon> = {
    languages: Braces,
    frontend: LayoutTemplate,
    backend: Database,
    infrastructure: Cloud,
};

export function Services({
    profile,
    groups,
}: {
    profile: Profile;
    groups: SkillGroup[];
}) {
    const c = useTerminalCopy();

    return (
        <div className="tm-container pt-8">
            <GlowCard
                as="section"
                id="services"
                aria-labelledby="services-title"
            >
                <div
                    aria-hidden
                    className="tm-grid-bg pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_50%_45%_at_0%_0%,#000,transparent)]"
                />
                <div className="relative p-1 md:p-6 lg:p-12">
                    <div className="text-center">
                        <Kicker center>{c('services.kicker')}</Kicker>
                        <h2
                            id="services-title"
                            className="mt-1 text-[clamp(1.5rem,3vw,2.1875rem)] font-medium"
                        >
                            {c('services.titleA')}{' '}
                            <span className="text-tm-300">
                                {c('services.titleB')}
                            </span>
                            <br />
                            <span className="text-tm-300">
                                {c('services.titleC')}
                            </span>
                        </h2>
                    </div>

                    <ul className="mt-12 grid grid-cols-1 gap-6 px-3 md:grid-cols-2 xl:grid-cols-4">
                        {groups.map((group) => {
                            const Icon = ICONS[group.category.id] ?? Sparkles;
                            return (
                                <li
                                    key={group.category.id}
                                    className="tm-box tm-service tm-hover-up rounded-md px-8 pt-[70px] pb-8 xl:px-7"
                                >
                                    <Icon
                                        aria-hidden
                                        className="size-6 text-ink"
                                        strokeWidth={1.75}
                                    />
                                    <h3 className="my-4 text-[19px] font-medium">
                                        {group.category.label}
                                    </h3>
                                    <p className="mb-0 text-sm leading-relaxed text-tm-300">
                                        {group.category.description}{' '}
                                        {c('services.working')}{' '}
                                        {group.skills.map((skill, index) => (
                                            <Fragment key={skill.id}>
                                                {index > 0 && ', '}
                                                <span
                                                    lang="en"
                                                    className="text-tm-secondary"
                                                >
                                                    {skill.name}
                                                </span>
                                            </Fragment>
                                        ))}
                                        .
                                    </p>
                                </li>
                            );
                        })}
                    </ul>

                    <p className="mt-[60px] mb-4 text-center text-tm-300">
                        {profile.availability.note}{' '}
                        <a
                            href="#contact"
                            className="text-tm-primary hover:underline"
                        >
                            {c('services.reach')}
                        </a>
                    </p>
                </div>
            </GlowCard>
        </div>
    );
}
