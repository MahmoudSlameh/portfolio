import { useTranslation } from '@/hooks/useTranslation';
import { cn } from '@/lib/utils';
import type { Profile } from '@/types/content';
import { usePlaygroundCopy } from '../copy';
import { popBg, popForIndex, tiltForIndex } from '../lib/pops';
import { Reveal } from '../components/Reveal';
import { SectionHeading } from '../components/SectionHeading';

export function AboutBlock({ profile }: { profile: Profile }) {
  const { t, l } = useTranslation();
  const p = usePlaygroundCopy();
  const [lead, ...rest] = l(profile.story);

  return (
    <section id="about" aria-labelledby="about-title" className="pg-shell scroll-mt-8 py-16">
      <SectionHeading id="about" kicker={p('about.kicker')} title={t('about.title')} pop="green" />
      <div className="grid gap-10 lg:grid-cols-12">
        <Reveal className="flex flex-col gap-6 lg:col-span-7">
          <p className="text-2xl leading-snug font-semibold text-ink md:text-3xl">{lead}</p>
          {rest.map((paragraph) => (
            <p key={paragraph} className="text-lg leading-relaxed text-ink-muted">
              {paragraph}
            </p>
          ))}
        </Reveal>
        <div className="lg:col-span-5">
          <h3 className="pg-label mb-5 text-ink">{p('about.principles')}</h3>
          <ol className="flex flex-col gap-4">
            {profile.principles.map((principle, index) => (
              <Reveal as="li" key={principle.id} delay={index * 70}>
                <div
                  style={{ rotate: `${tiltForIndex(index) * 0.4}deg` }}
                  className={cn('pg-card pg-press flex gap-4 p-5 text-on-pop', popBg[popForIndex(index)])}
                >
                  <span aria-hidden className="pg-display ltr-isolate text-4xl">
                    {String(index + 1).padStart(2, '0')}
                  </span>
                  <div>
                    <p className="text-lg leading-tight font-bold">{l(principle.title)}</p>
                    <p className="mt-1 text-[0.9375rem] leading-relaxed">{l(principle.body)}</p>
                  </div>
                </div>
              </Reveal>
            ))}
          </ol>
        </div>
      </div>
    </section>
  );
}
