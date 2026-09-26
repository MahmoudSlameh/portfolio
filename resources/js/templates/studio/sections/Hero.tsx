import { ArrowRight } from 'lucide-react';
import { BrandIcon, cn, Link, ResponsiveImage, useTranslation } from '@/kit';
import type { Profile, Social } from '@/types/content';
import { useCopy } from '../useSpec';
import type { SectionComponentProps } from './types';

function Availability({ profile }: { profile: Profile }) {
    return (
        <p className="st-availability inline-flex items-center gap-2 text-sm text-ink-muted">
            <span
                aria-hidden
                className={cn(
                    'size-2 rounded-full',
                    profile.availability.status === 'closed'
                        ? 'bg-ink-subtle'
                        : 'bg-signal',
                )}
            />
            {profile.availability.label}
        </p>
    );
}

function HeroSocials({ socials }: { socials: Social[] }) {
    return (
        <ul className="st-hero__socials flex flex-wrap gap-2">
            {socials.map((social) => (
                <li key={social.id}>
                    <a
                        href={social.url}
                        target="_blank"
                        rel="me noopener"
                        aria-label={social.label}
                        className="grid size-10 place-items-center rounded-[var(--st-radius)] border border-line text-ink-muted transition-colors hover:border-signal hover:text-signal"
                    >
                        <BrandIcon icon={social.icon} className="size-4" />
                    </a>
                </li>
            ))}
        </ul>
    );
}

function Actions() {
    const { t } = useTranslation();
    const copy = useCopy();

    return (
        <div className="st-hero__actions flex flex-wrap gap-3">
            <Link to="/" hash="contact" className="st-button">
                {copy('heroCta', t('hero.getInTouch'))}
                <ArrowRight aria-hidden className="size-4" />
            </Link>
            <Link to="/projects" className="st-button st-button--ghost">
                {t('nav.work')}
            </Link>
        </div>
    );
}

export function Hero({ data, variant, props }: SectionComponentProps<'hero'>) {
    const { profile, socials } = data;
    const copy = useCopy();
    const kicker = copy('heroKicker', profile.role);
    const showAvailability = props.showAvailability ?? true;
    const showSocials = (props.showSocials ?? true) && socials.length > 0;

    if (variant === 'terminal') {
        return (
            <section className="st-section st-hero st-hero--terminal">
                <div className="st-container">
                    <div className="st-card overflow-hidden font-mono">
                        <div className="flex gap-1.5 border-b border-line px-4 py-3">
                            {[0, 1, 2].map((dot) => (
                                <span
                                    key={dot}
                                    aria-hidden
                                    className="size-3 rounded-full bg-line"
                                />
                            ))}
                        </div>
                        <div className="grid gap-3 p-6 text-sm sm:p-10 sm:text-base">
                            <p className="text-ink-subtle">$ whoami</p>
                            <h1 className="st-hero__title font-[family-name:var(--tpl-font-mono)] text-[clamp(2rem,6vw,4rem)] leading-none font-bold text-ink">
                                {profile.name}
                            </h1>
                            <p className="text-signal">{kicker}</p>
                            <p className="text-ink-subtle">$ cat about.txt</p>
                            <p className="max-w-3xl text-ink-muted">
                                {profile.headline}
                            </p>
                            {showAvailability && (
                                <Availability profile={profile} />
                            )}
                            <div className="mt-4 flex flex-wrap items-center gap-6">
                                <Actions />
                                {showSocials && (
                                    <HeroSocials socials={socials} />
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        );
    }

    if (variant === 'split-portrait') {
        return (
            <section className="st-section st-hero st-hero--split-portrait">
                <div className="st-container grid items-center gap-10 md:grid-cols-[1.3fr_1fr]">
                    <div className="grid gap-6">
                        <p className="st-kicker">{kicker}</p>
                        <h1 className="st-hero__title text-[clamp(2.75rem,7vw,5.5rem)] leading-[0.95] font-bold text-ink">
                            {profile.name}
                        </h1>
                        <p className="max-w-xl text-xl text-ink-muted">
                            {profile.headline}
                        </p>
                        {showAvailability && <Availability profile={profile} />}
                        <Actions />
                        {showSocials && <HeroSocials socials={socials} />}
                    </div>
                    <div className="st-hero__portrait overflow-hidden rounded-[var(--st-radius)] border border-line shadow-[var(--st-shadow)]">
                        <ResponsiveImage
                            image={profile.portrait}
                            sizes="(min-width: 768px) 40vw, 100vw"
                            priority
                            fallbackAspect="4 / 5"
                            className="aspect-[4/5] w-full"
                        />
                    </div>
                </div>
            </section>
        );
    }

    if (variant === 'editorial') {
        return (
            <section className="st-section st-hero st-hero--editorial">
                <div className="st-container">
                    <p className="st-kicker">{kicker}</p>
                    <h1 className="st-hero__title mt-4 text-[clamp(3.5rem,12vw,9rem)] leading-[0.85] font-bold tracking-tight text-ink">
                        {profile.name}
                    </h1>
                    <div className="mt-10 grid gap-8 border-t border-line pt-8 md:grid-cols-[2fr_1fr]">
                        <p className="text-2xl leading-snug text-ink">
                            {profile.headline}
                        </p>
                        <div className="grid content-start gap-5">
                            {showAvailability && (
                                <Availability profile={profile} />
                            )}
                            {profile.focusAreas.length > 0 && (
                                <ul className="flex flex-wrap gap-2">
                                    {profile.focusAreas.map((area) => (
                                        <li key={area} className="st-chip">
                                            {area}
                                        </li>
                                    ))}
                                </ul>
                            )}
                            <Actions />
                            {showSocials && <HeroSocials socials={socials} />}
                        </div>
                    </div>
                </div>
            </section>
        );
    }

    return (
        <section className="st-section st-hero st-hero--centered">
            <div className="st-container grid justify-items-center gap-6 text-center">
                {profile.portrait && (
                    <div className="size-28 overflow-hidden rounded-full border border-line">
                        <ResponsiveImage
                            image={profile.portrait}
                            sizes="112px"
                            priority
                            fallbackAspect="1 / 1"
                            className="size-full"
                        />
                    </div>
                )}
                <p className="st-kicker">{kicker}</p>
                <h1 className="st-hero__title text-[clamp(2.75rem,8vw,6rem)] leading-[0.95] font-bold text-ink">
                    {profile.name}
                </h1>
                <p className="max-w-2xl text-xl text-ink-muted">
                    {profile.headline}
                </p>
                {showAvailability && <Availability profile={profile} />}
                <Actions />
                {showSocials && <HeroSocials socials={socials} />}
            </div>
        </section>
    );
}
