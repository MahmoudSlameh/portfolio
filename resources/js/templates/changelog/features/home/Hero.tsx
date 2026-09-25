import { Link } from '@/lib/router';
import { ArrowDown } from 'lucide-react';
import type { CSSProperties } from 'react';
import { buttonClasses } from '@/templates/changelog/components/ui/Button';
import { BrandIcon } from '@/shared/ui/BrandIcon';
import { CountUp } from '@/shared/ui/CountUp';
import { Tooltip } from '@/templates/changelog/components/ui/IconButton';
import { RotatingText } from '@/shared/ui/RotatingText';
import { useTranslation } from '@/hooks/useTranslation';
import { accentForIndex, accentText } from '@/templates/changelog/lib/accents';
import { handleSpotlightMove } from '@/templates/changelog/lib/spotlight';
import { cn } from '@/lib/utils';
import { useCommandPalette } from '@/providers/CommandPaletteProvider';
import type { Profile, Social } from '@/types/content';

interface HeroProps {
    profile: Profile;
    socials: Social[];
}

const delay = (milliseconds: number): CSSProperties => ({
    animationDelay: `${milliseconds}ms`,
});

const isMacPlatform = (): boolean =>
    typeof navigator !== 'undefined' &&
    /Mac|iPhone|iPad/.test(navigator.platform);

type ReleaseKind = 'added' | 'changed' | 'removed';

const releaseMarks: Record<ReleaseKind, { symbol: string; className: string }> =
    {
        added: { symbol: '+', className: 'bg-signal/20 text-signal-ink' },
        changed: { symbol: '~', className: 'bg-aqua/15 text-aqua' },
        removed: { symbol: '−', className: 'bg-coral/15 text-coral' },
    };

function LatestRelease({ profile }: { profile: Profile }) {
    const { t } = useTranslation();
    const groups: ReleaseKind[] = ['added', 'changed', 'removed'];

    return (
        <aside
            aria-labelledby="latest-release-title"
            className="animate-rise relative overflow-hidden rounded-2xl border border-line bg-raised/70 shadow-lift backdrop-blur-xl"
            style={delay(420)}
        >
            <div
                aria-hidden
                className="absolute inset-x-0 top-0 h-px bg-[image:var(--gradient-brand)]"
            />
            <div className="flex items-center justify-between gap-3 border-b border-line px-5 py-3.5">
                <h2
                    id="latest-release-title"
                    className="eyebrow flex items-center gap-2 text-ink-muted"
                >
                    <span
                        aria-hidden
                        className="animate-pulse-dot size-2 rounded-full bg-signal"
                    />
                    {t('hero.latestRelease')}
                </h2>
                <span className="version-label border-transparent bg-signal font-semibold text-on-signal">
                    {profile.currentVersion}
                </span>
            </div>
            <div className="flex flex-col gap-5 px-5 py-5">
                {groups.map((group) => (
                    <div key={group}>
                        <h3 className="mb-2 font-mono text-[0.6875rem] text-ink-subtle">
                            ### {t(`hero.${group}`)}
                        </h3>
                        <ul className="flex flex-col gap-2">
                            {profile.latestRelease[group].map((line) => (
                                <li
                                    key={line}
                                    className="flex items-start gap-2.5 text-[0.875rem] leading-snug text-ink"
                                >
                                    <span
                                        aria-hidden
                                        className={cn(
                                            'inline-flex size-5 shrink-0 items-center justify-center rounded-md font-mono text-xs font-bold',
                                            releaseMarks[group].className,
                                        )}
                                    >
                                        {releaseMarks[group].symbol}
                                    </span>
                                    {line}
                                </li>
                            ))}
                        </ul>
                    </div>
                ))}
            </div>
            <p className="border-t border-line px-5 py-2.5 font-mono text-[0.6875rem] text-ink-subtle">
                {t('hero.updated')}{' '}
                <time dateTime={profile.updatedAt} className="ltr-isolate">
                    {profile.updatedAt}
                </time>
            </p>
        </aside>
    );
}

export function Hero({ profile, socials }: HeroProps) {
    const { t } = useTranslation();
    const { openPalette } = useCommandPalette();
    const [firstName, ...restOfName] = profile.name.split(' ');

    return (
        <section
            aria-labelledby="hero-title"
            className="relative overflow-hidden"
            onPointerMove={handleSpotlightMove}
        >
            <div aria-hidden className="aurora">
                <span />
                <span />
                <span />
                <span />
            </div>
            <div
                aria-hidden
                className="spotlight pointer-events-none absolute inset-0"
            />
            <div
                aria-hidden
                className="baseline-texture pointer-events-none absolute inset-0"
            />

            <div className="shell relative pt-10 pb-16 md:pt-16 md:pb-20">
                <p
                    className="animate-rise flex flex-wrap items-center gap-x-4 gap-y-2 border-b border-line pb-4 text-ink-subtle"
                    style={delay(0)}
                >
                    <span className="eyebrow inline-flex items-center gap-2 rounded-full bg-signal/15 px-3 py-1 text-ink">
                        <span
                            aria-hidden
                            className="size-1.5 rounded-full bg-signal"
                        />
                        {profile.role}
                    </span>
                    <span className="eyebrow">{profile.location}</span>
                    <span className="version-label ms-auto">
                        changelog/{profile.currentVersion}
                    </span>
                </p>

                <h1
                    id="hero-title"
                    className={cn(
                        'mt-8 font-display font-bold text-ink md:mt-12',
                        'text-[clamp(3.5rem,12.5vw,11rem)] leading-[0.88] tracking-[-0.055em]',
                    )}
                >
                    <span className="animate-word" style={delay(80)}>
                        {firstName}
                    </span>{' '}
                    <span className="animate-word" style={delay(220)}>
                        <span className="text-gradient">
                            {restOfName.join(' ')}
                        </span>
                    </span>
                </h1>

                <p
                    className={cn(
                        'animate-rise mt-6 flex flex-wrap items-baseline gap-x-2 text-ink-muted',
                        'font-mono text-sm md:text-base',
                    )}
                    style={delay(320)}
                >
                    <span className="text-ink-subtle">
                        {t('hero.focusPrefix')}
                    </span>
                    <RotatingText
                        items={profile.focusAreas}
                        className="font-semibold text-electric"
                    />
                </p>

                <div className="editorial-grid mt-10 gap-y-12 md:mt-14">
                    <div className="col-span-4 md:col-span-7">
                        <p
                            className="animate-rise max-w-2xl font-display text-[1.75rem] leading-[1.18] text-ink sm:text-[2.125rem] lg:text-[2.5rem]"
                            style={delay(380)}
                        >
                            {profile.headline}
                        </p>
                        <p
                            className="animate-rise mt-6 max-w-xl text-base leading-relaxed text-ink-muted md:text-[1.0625rem]"
                            style={delay(440)}
                        >
                            {profile.summary}
                        </p>

                        <div
                            className="animate-rise mt-9 flex flex-wrap items-center gap-3"
                            style={delay(500)}
                        >
                            <Link
                                to="/"
                                hash="career"
                                className={buttonClasses('primary')}
                            >
                                {t('hero.readChangelog')}
                                <ArrowDown aria-hidden className="size-4" />
                            </Link>
                            <Link
                                to="/"
                                hash="contact"
                                className={buttonClasses('secondary')}
                            >
                                {t('hero.getInTouch')}
                            </Link>
                            <button
                                type="button"
                                onClick={openPalette}
                                className="hidden items-center gap-2 px-2 text-[0.8125rem] text-ink-subtle hover:text-ink sm:inline-flex"
                            >
                                <kbd className="ltr-isolate rounded-md border border-line-strong bg-raised px-1.5 py-0.5 font-mono text-[0.6875rem]">
                                    {isMacPlatform() ? '⌘K' : 'Ctrl K'}
                                </kbd>
                                {t('hero.commandHint')}
                            </button>
                        </div>

                        <ul
                            aria-label={t('hero.socialLabel')}
                            className="animate-rise mt-8 flex flex-wrap items-center gap-2"
                            style={delay(560)}
                        >
                            {socials.map((social) => (
                                <li key={social.id}>
                                    <Tooltip label={social.handle}>
                                        <a
                                            href={social.url}
                                            target="_blank"
                                            rel="noreferrer"
                                            aria-label={`${social.label} ${t('common.opensNewTab')}`}
                                            className="inline-flex size-10 items-center justify-center rounded-full border border-line text-ink-muted transition-all duration-300 hover:-translate-y-0.5 hover:border-electric hover:bg-electric/10 hover:text-electric"
                                        >
                                            <BrandIcon
                                                icon={social.icon}
                                                className="size-[1.05rem]"
                                            />
                                        </a>
                                    </Tooltip>
                                </li>
                            ))}
                        </ul>
                    </div>

                    <div className="col-span-4 md:col-span-5 lg:col-span-4 lg:col-start-9">
                        <LatestRelease profile={profile} />
                    </div>
                </div>

                <dl
                    aria-label={t('hero.statsLabel')}
                    className="animate-rise mt-16 grid grid-cols-2 gap-3 md:mt-20 md:grid-cols-4"
                    style={delay(620)}
                >
                    {profile.stats.map((stat, index) => (
                        <div
                            key={stat.id}
                            className="group relative flex flex-col gap-2 overflow-hidden rounded-2xl border border-line bg-raised/60 p-5 backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:border-line-strong md:p-6"
                        >
                            <dt className="order-2 text-sm text-ink-muted">
                                {stat.label}
                            </dt>
                            <dd
                                className={cn(
                                    'ltr-isolate order-1 self-start font-display text-5xl leading-none font-bold tracking-[-0.04em] md:text-6xl',
                                    accentText[accentForIndex(index)],
                                )}
                            >
                                <CountUp value={stat.value} />
                            </dd>
                        </div>
                    ))}
                </dl>
            </div>
        </section>
    );
}
