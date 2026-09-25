import { Section } from '@/templates/changelog/components/ui/Section';
import { ResponsiveImage } from '@/shared/ui/ResponsiveImage';
import { sectionIndex } from '@/config/navigation';
import { useTranslation } from '@/hooks/useTranslation';
import type { Profile } from '@/types/content';

export function AboutSection({ profile }: { profile: Profile }) {
    const { t } = useTranslation();
    const [lede, ...paragraphs] = profile.story;

    return (
        <Section
            id="about"
            index={sectionIndex('about')}
            label={t('section.about')}
            version="README.md"
            title={t('about.title')}
        >
            <div className="editorial-grid gap-y-12">
                <figure className="col-span-4 md:col-span-5 lg:col-span-4">
                    <div className="group relative isolate">
                        <div
                            aria-hidden
                            className="absolute -inset-3 -z-10 rotate-[-2.5deg] rounded-[1.75rem] bg-[image:var(--gradient-brand)] opacity-80 blur-[2px] transition-transform duration-700 group-hover:rotate-[1.5deg]"
                        />
                        <div className="relative overflow-hidden rounded-2xl">
                            <ResponsiveImage
                                image={profile.portrait}
                                sizes="(min-width: 1024px) 30vw, (min-width: 768px) 40vw, 92vw"
                                className="aspect-[3/4] contrast-[1.08] grayscale transition-transform duration-700 group-hover:scale-[1.03]"
                            />
                            <span
                                aria-hidden
                                className="tint tint-electric opacity-90 group-hover:opacity-60"
                            />
                        </div>
                    </div>
                    <figcaption className="mt-4 flex items-center justify-between font-mono text-[0.6875rem] text-ink-subtle">
                        <span>
                            {t('about.portraitCaption', {
                                location: profile.location || profile.name,
                            })}
                        </span>
                        {profile.portrait && profile.portrait.width > 0 && (
                            <span className="ltr-isolate">
                                {profile.portrait.width} ×{' '}
                                {profile.portrait.height}
                            </span>
                        )}
                    </figcaption>
                </figure>

                <div className="col-span-4 md:col-span-7 md:col-start-6 lg:col-span-6 lg:col-start-6">
                    <p className="font-display text-[1.625rem] leading-[1.3] text-ink md:text-[2rem]">
                        {lede}
                    </p>
                    <div className="prose-editorial mt-8">
                        {paragraphs.map((paragraph) => (
                            <p key={paragraph}>{paragraph}</p>
                        ))}
                    </div>
                </div>
            </div>

            <div className="mt-20 md:mt-24">
                <h3 className="eyebrow mb-6 text-ink-subtle">
                    {t('about.principles')}
                </h3>
                <ol className="grid gap-px border border-line bg-line sm:grid-cols-2 lg:grid-cols-5">
                    {profile.principles.map((principle, index) => (
                        <li
                            key={principle.id}
                            className="flex flex-col gap-4 bg-paper p-6 transition-colors duration-300 hover:bg-raised"
                        >
                            <span className="ltr-isolate font-mono text-xs text-signal-ink">
                                {String(index + 1).padStart(2, '0')}
                            </span>
                            <p className="font-display text-2xl leading-tight text-ink">
                                {principle.title}
                            </p>
                            <p className="text-[0.9375rem] leading-relaxed text-ink-muted">
                                {principle.body}
                            </p>
                        </li>
                    ))}
                </ol>
            </div>
        </Section>
    );
}
