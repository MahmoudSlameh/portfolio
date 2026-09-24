import { Link } from '@tanstack/react-router';
import { ArrowDownRight, Clock, MapPin, Sparkles } from 'lucide-react';
import { motion } from 'motion/react';
import { useRef, type RefObject } from 'react';
import { useNow } from '@/hooks/useLocalTime';
import { useTranslation } from '@/hooks/useTranslation';
import { cn, formatTime } from '@/lib/utils';
import { BrandIcon } from '@/shared/ui/BrandIcon';
import { CountUp } from '@/shared/ui/CountUp';
import { ResponsiveImage } from '@/shared/ui/ResponsiveImage';
import { RotatingText } from '@/shared/ui/RotatingText';
import type { AvailabilityStatus, Profile, Social } from '@/types/content';
import { usePlaygroundCopy } from '../copy';
import { popBg, popForIndex, type Pop } from '../lib/pops';
import { popButtonClasses } from '../components/PopButton';
import { Reveal } from '../components/Reveal';
import { Sticker } from '../components/Sticker';

const availabilityPop: Record<AvailabilityStatus, Pop> = { open: 'green', limited: 'yellow', closed: 'red' };

function AvailabilitySticker({ profile, constraintsRef }: { profile: Profile; constraintsRef: RefObject<HTMLDivElement | null> }) {
  const { l } = useTranslation();
  const p = usePlaygroundCopy();
  const pop = availabilityPop[profile.availability.status];

  return (
    <motion.div
      drag
      dragConstraints={constraintsRef}
      dragElastic={0.18}
      dragMomentum={false}
      whileHover={{ scale: 1.05, rotate: 0 }}
      whileDrag={{ scale: 1.12, rotate: 6, cursor: 'grabbing' }}
      initial={{ rotate: 8 }}
      className={cn(
        'absolute end-5 top-5 z-10 flex cursor-grab touch-none flex-col items-center gap-1 rounded-full border-2 border-edge px-5 py-4 text-center text-on-pop shadow-[var(--pg-shadow)] select-none md:end-8 md:top-8',
        popBg[pop],
      )}
    >
      <span className="flex items-center gap-2 text-sm font-bold">
        <span aria-hidden className="relative flex size-2.5">
          <span className="absolute inset-0 animate-ping rounded-full bg-on-pop/60" />
          <span className="relative size-2.5 rounded-full bg-on-pop" />
        </span>
        {l(profile.availability.label)}
      </span>
      <span aria-hidden className="pg-label text-[0.625rem] opacity-70">
        ✋ {p('hero.dragMe')}
      </span>
    </motion.div>
  );
}

function IntroTile({ profile, socials }: { profile: Profile; socials: Social[] }) {
  const { t, l } = useTranslation();
  const p = usePlaygroundCopy();
  const tileRef = useRef<HTMLDivElement>(null);

  return (
    <div ref={tileRef} className="pg-card flex flex-col justify-between gap-10 overflow-hidden p-6 pt-28 md:col-span-6 md:p-10 md:pt-12 lg:col-span-8 lg:row-span-2">
      <AvailabilitySticker profile={profile} constraintsRef={tileRef} />
      <div className="flex flex-col items-start gap-5">
        <Sticker pop="pink" tilt={-6}>
          {p('hero.hello')}
        </Sticker>
        <h1 id="hero-title" className="pg-display text-[clamp(3.25rem,10vw,8rem)] text-ink">
          {l(profile.name)}
        </h1>
        <p className="max-w-2xl text-xl leading-snug font-medium text-ink-muted md:text-2xl">{l(profile.headline)}</p>
        <p className="inline-flex flex-wrap items-center gap-x-3 gap-y-2 rounded-full border-2 border-edge bg-pop-yellow px-4 py-2 text-base font-bold text-on-pop md:text-lg">
          <Sparkles aria-hidden className="size-5" strokeWidth={2.5} />
          <span>{p('hero.into')}</span>
          <RotatingText items={l(profile.focusAreas)} className="min-w-0" />
        </p>
      </div>

      <div className="flex flex-wrap items-center justify-between gap-6">
        <div className="flex flex-wrap gap-3">
          <Link to="/" hash="work" className={popButtonClasses({ tone: 'ink' })}>
            {p('hero.seeWork')}
            <ArrowDownRight aria-hidden className="size-4 rtl:-scale-x-100" strokeWidth={2.5} />
          </Link>
          <Link to="/" hash="contact" className={popButtonClasses({ tone: 'green' })}>
            {p('hero.sayHi')}
          </Link>
        </div>
        <ul aria-label={t('hero.socialLabel')} className="flex gap-2">
          {socials.slice(0, 4).map((social) => (
            <li key={social.id}>
              <a
                href={social.url}
                target="_blank"
                rel="noreferrer"
                aria-label={`${social.label} ${t('common.opensNewTab')}`}
                className={popButtonClasses({ tone: 'plain', size: 'icon' })}
              >
                <BrandIcon icon={social.icon} className="size-4" />
              </a>
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}

function PortraitTile({ profile }: { profile: Profile }) {
  const { l, locale } = useTranslation();
  const now = useNow();

  return (
    <div className="pg-card relative overflow-hidden bg-pop-blue md:col-span-3 lg:col-span-4 lg:row-span-2">
      <ResponsiveImage
        image={profile.portrait}
        sizes="(min-width: 64rem) 30vw, (min-width: 48rem) 50vw, 100vw"
        priority
        className="pg-duotone aspect-[4/5] h-full bg-transparent object-cover lg:aspect-auto"
      />
      <div className="absolute inset-x-4 bottom-4 flex flex-wrap items-end justify-between gap-2">
        <Sticker pop="yellow" tilt={-5}>
          <MapPin aria-hidden className="size-3.5" strokeWidth={2.5} />
          {l(profile.location)}
        </Sticker>
        <Sticker pop="pink" tilt={4}>
          <Clock aria-hidden className="size-3.5" strokeWidth={2.5} />
          <time dateTime={now.toISOString()} className="ltr-isolate">
            {formatTime(now, profile.timezone, locale)}
          </time>
        </Sticker>
      </div>
    </div>
  );
}

function StatTile({ value, label, index }: { value: string; label: string; index: number }) {
  const pop = popForIndex(index + 1);

  return (
    <Reveal delay={index * 80} className="md:col-span-3">
      <div className={cn('pg-card pg-press flex h-full flex-col justify-between gap-6 p-6 text-on-pop', popBg[pop])}>
        <span className="pg-display ltr-isolate text-[clamp(3rem,6vw,4.5rem)]">
          <CountUp value={value} />
        </span>
        <span className="text-base leading-snug font-bold">{label}</span>
      </div>
    </Reveal>
  );
}

function StatusTile({ profile }: { profile: Profile }) {
  const { l } = useTranslation();
  const p = usePlaygroundCopy();

  return (
    <Reveal className="md:col-span-6 lg:col-span-7">
      <div className="pg-card h-full p-6">
        <h2 className="pg-label mb-5 flex items-center gap-2 text-ink">
          <span aria-hidden className="size-2.5 animate-pulse rounded-full bg-pop-red" />
          {p('hero.statusTitle')}
        </h2>
        <dl className="grid gap-3 sm:grid-cols-2">
          {profile.status.map((entry, index) => (
            <div key={entry.id} className="rounded-xl border-2 border-edge bg-paper p-4">
              <dt className={cn('pg-label mb-1', index % 2 === 0 ? 'text-signal-ink' : 'text-coral')}>{l(entry.label)}</dt>
              <dd className="text-base leading-snug font-semibold text-ink">{l(entry.value)}</dd>
            </div>
          ))}
        </dl>
      </div>
    </Reveal>
  );
}

function FreshTile({ profile }: { profile: Profile }) {
  const { l } = useTranslation();
  const p = usePlaygroundCopy();

  return (
    <Reveal className="md:col-span-3 lg:col-span-5" delay={100}>
      <div className="pg-card relative h-full rotate-[1deg] bg-pop-yellow p-6 text-on-pop">
        <span aria-hidden className="absolute -top-3 start-1/2 h-6 w-24 -translate-x-1/2 rotate-[-3deg] rounded-sm border-2 border-edge bg-pop-pink/80" />
        <h2 className="pg-label mb-4">{p('hero.freshTitle')}</h2>
        <ul className="flex flex-col gap-3">
          {l(profile.latestRelease.added).map((item) => (
            <li key={item} className="flex gap-3 text-base leading-snug font-semibold">
              <span aria-hidden className="mt-0.5 inline-flex size-5 shrink-0 items-center justify-center rounded-full border-2 border-edge bg-raised text-xs text-ink">
                +
              </span>
              {item}
            </li>
          ))}
        </ul>
      </div>
    </Reveal>
  );
}

export function HeroBento({ profile, socials }: { profile: Profile; socials: Social[] }) {
  const { l } = useTranslation();

  return (
    <section aria-labelledby="hero-title" className="pg-shell pt-2 pb-16 md:pt-6">
      <div className="grid gap-4 md:grid-flow-row-dense md:grid-cols-6 md:gap-5 lg:grid-cols-12">
        <IntroTile profile={profile} socials={socials} />
        <PortraitTile profile={profile} />
        {profile.stats.map((stat, index) => (
          <StatTile key={stat.id} value={stat.value} label={l(stat.label)} index={index} />
        ))}
        <StatusTile profile={profile} />
        <FreshTile profile={profile} />
      </div>
    </section>
  );
}
