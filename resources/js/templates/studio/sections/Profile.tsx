import { CountUp, useTranslation } from '@/kit';
import { Section } from '../components/Section';
import { useCopy } from '../useSpec';
import type { SectionComponentProps } from './types';

export function About({ data, variant }: SectionComponentProps<'about'>) {
    const { t } = useTranslation();
    const copy = useCopy();
    const { story, principles } = data.profile;

    if (story.length === 0) return null;

    return (
        <Section
            id="about"
            name="about"
            kicker={t('section.about')}
            title={copy('aboutHeading', 'A little about me')}
        >
            {variant === 'columns' ? (
                <div className="grid gap-10 md:grid-cols-[3fr_2fr]">
                    <div className="grid content-start gap-4 text-lg text-ink-muted">
                        {story.map((paragraph) => (
                            <p key={paragraph}>{paragraph}</p>
                        ))}
                    </div>
                    {principles.length > 0 && (
                        <ul className="grid content-start gap-4">
                            {principles.map((principle) => (
                                <li key={principle.id} className="st-card p-5">
                                    <p className="font-bold text-ink">
                                        {principle.title}
                                    </p>
                                    <p className="mt-1 text-sm text-ink-muted">
                                        {principle.body}
                                    </p>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            ) : (
                <div className="grid max-w-3xl gap-5 text-xl leading-relaxed text-ink-muted first-letter:text-ink">
                    {story.map((paragraph) => (
                        <p key={paragraph}>{paragraph}</p>
                    ))}
                </div>
            )}
        </Section>
    );
}

export function Stats({ data, variant }: SectionComponentProps<'stats'>) {
    const { stats } = data.profile;

    if (stats.length === 0) return null;

    return (
        <section aria-label="In numbers" className="st-section st-stats">
            <div className="st-container">
                <dl
                    className={
                        variant === 'cards'
                            ? 'grid grid-cols-2 gap-4 md:grid-cols-4'
                            : 'flex flex-wrap justify-between gap-x-10 gap-y-6 border-y border-line py-8'
                    }
                >
                    {stats.map((stat) => (
                        <div
                            key={stat.id}
                            className={
                                variant === 'cards' ? 'st-card p-6' : undefined
                            }
                        >
                            <dt className="order-2 text-sm text-ink-muted">
                                {stat.label}
                            </dt>
                            <dd className="text-[clamp(2rem,5vw,3rem)] font-bold text-ink">
                                <CountUp value={stat.value} />
                            </dd>
                        </div>
                    ))}
                </dl>
            </div>
        </section>
    );
}

export function Skills({ data, variant }: SectionComponentProps<'skills'>) {
    const { t } = useTranslation();
    const copy = useCopy();
    const groups = data.skillGroups.filter((group) => group.skills.length > 0);

    if (groups.length === 0) return null;

    return (
        <Section
            id="stack"
            name="skills"
            kicker={t('section.stack')}
            title={copy('skillsHeading', 'Tools I build with')}
        >
            {variant === 'bars' && (
                <div className="grid gap-10 md:grid-cols-2">
                    {groups.map((group) => (
                        <div key={group.category.id}>
                            <h3 className="mb-4 font-bold text-ink">
                                {group.category.label}
                            </h3>
                            <ul className="grid gap-3">
                                {group.skills.map((skill) => (
                                    <li key={skill.id}>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-ink">
                                                {skill.name}
                                            </span>
                                            {skill.years !== null && (
                                                <span className="text-ink-subtle">
                                                    {skill.years}y
                                                </span>
                                            )}
                                        </div>
                                        <div
                                            aria-hidden
                                            className="mt-1 h-1.5 overflow-hidden rounded-full bg-line"
                                        >
                                            <div
                                                className="h-full rounded-full bg-signal"
                                                style={{
                                                    width: `${((skill.proficiency ?? 3) / 5) * 100}%`,
                                                }}
                                            />
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </div>
            )}
            {variant === 'grid' && (
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {groups.map((group) => (
                        <div key={group.category.id} className="st-card p-6">
                            <h3 className="font-bold text-ink">
                                {group.category.label}
                            </h3>
                            {group.category.description && (
                                <p className="mt-1 text-sm text-ink-muted">
                                    {group.category.description}
                                </p>
                            )}
                            <p className="mt-4 text-ink-muted">
                                {group.skills
                                    .map((skill) => skill.name)
                                    .join(' · ')}
                            </p>
                        </div>
                    ))}
                </div>
            )}
            {variant === 'chips' && (
                <dl className="grid gap-6">
                    {groups.map((group) => (
                        <div
                            key={group.category.id}
                            className="grid gap-3 sm:grid-cols-[12rem_1fr]"
                        >
                            <dt className="font-bold text-ink">
                                {group.category.label}
                            </dt>
                            <dd className="flex flex-wrap gap-2">
                                {group.skills.map((skill) => (
                                    <span key={skill.id} className="st-chip">
                                        {skill.name}
                                    </span>
                                ))}
                            </dd>
                        </div>
                    ))}
                </dl>
            )}
        </Section>
    );
}
