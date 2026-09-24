import { Send } from 'lucide-react';
import { Fragment, useId } from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import type { SkillGroup } from '@/lib/content';
import { imageSources } from '@/lib/utils';
import type { Profile } from '@/types/content';
import { useTerminalCopy } from '../copy';
import { CodeHexagon } from '../components/CodeMarks';
import { GlowCard } from '../components/GlowCard';
import { Marquee } from '../components/Marquee';
import { SkillTile } from '../components/SkillTile';
import { firstName, splitRole } from '../lib/site';

const HEX_PATH =
  'M55.54 0H25.46a10.4 10.4 0 0 0-9.02 5.21L1.4 31.28a10.4 10.4 0 0 0 0 10.44l15.04 26.07A10.4 10.4 0 0 0 25.46 73h30.08a10.4 10.4 0 0 0 9.02-5.21L79.6 41.72a10.4 10.4 0 0 0 0-10.44L64.57 5.21A10.4 10.4 0 0 0 55.54 0Z';

function HexPortrait({ profile }: { profile: Profile }) {
  const { l } = useTranslation();
  const c = useTerminalCopy();
  const clipId = useId().replace(/:/g, '');
  const { src, srcSet } = imageSources(profile.portrait);

  return (
    <div className="relative mx-auto max-w-[505px]">
      <svg aria-hidden width="0" height="0" className="absolute">
        <clipPath id={clipId} clipPathUnits="objectBoundingBox">
          <path transform="scale(0.0123457 0.0136986)" d={HEX_PATH} />
        </clipPath>
      </svg>
      <div
        className="relative aspect-[81/73] w-full bg-[linear-gradient(160deg,#127080_0%,#0d3c4d_55%,#0a2733_100%)]"
        style={{ clipPath: `url(#${clipId})` }}
      >
        <img
          src={src}
          srcSet={srcSet}
          sizes="(min-width: 992px) 505px, 90vw"
          width={profile.portrait.width}
          height={profile.portrait.height}
          alt={c('hero.portrait', { name: l(profile.name) })}
          fetchPriority="high"
          decoding="async"
          className="absolute inset-0 size-full object-cover object-[50%_20%] mix-blend-luminosity"
        />
      </div>
      <CodeHexagon className="absolute bottom-0 left-1/2 translate-y-[40%] -translate-x-1/2" />
    </div>
  );
}

export function Hero({ profile, skillGroups }: { profile: Profile; skillGroups: SkillGroup[] }) {
  const { l, isRtl } = useTranslation();
  const c = useTerminalCopy();
  const role = splitRole(l(profile.role));
  const skills = skillGroups.flatMap((group) => group.skills);
  const signature = [...skills].sort((a, b) => b.proficiency - a.proficiency || b.years - a.years).slice(0, 4);

  return (
    <section id="about" aria-labelledby="hero-title" className="relative pt-[130px] pb-4">
      <div className="tm-container">
        <GlowCard>
          <div className="grid grid-cols-1 items-end gap-12 py-[60px] lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)] lg:gap-0">
            <div className="px-3 lg:ps-12 lg:pe-8">
              <HexPortrait profile={profile} />
            </div>

            <div className="p-3 md:p-16 lg:p-0 lg:pe-16">
              <div className="flex items-center text-tm-secondary" dir="ltr">
                &lt;span&gt;
                <span className="inline-block text-ink" dir={isRtl ? 'rtl' : 'ltr'}>
                  <span className="tm-typewriter text-base font-medium">{c('hero.hello', { name: firstName(l(profile.name)) })}</span>
                </span>
                &lt;/span&gt;
              </div>

              <h1 id="hero-title" className="my-4 text-[clamp(2.25rem,5vw,3.125rem)] font-medium">
                {role.lead && <>{role.lead} </>}
                <span className="tm-text-gradient">
                  {'{'}
                  {role.accent}
                  {'}'}
                </span>
                <wbr />
                {role.tail}
                <span aria-hidden className="tm-flicker">
                  _
                </span>
              </h1>

              <p className="mb-10 text-tm-secondary">
                <span dir="ltr">&lt;p&gt;</span>
                <span className="text-ink">{l(profile.headline)} </span>
                <span className="text-ink">{c('hero.stack')} </span>
                {signature.map((skill, index) => (
                  <Fragment key={skill.id}>
                    {index > 0 && index < signature.length - 1 && ', '}
                    {index > 0 && index === signature.length - 1 && ` ${c('hero.and')} `}
                    <span lang="en" className="text-tm-secondary">
                      {skill.name}
                    </span>
                  </Fragment>
                ))}
                <span className="text-ink">.</span>
                <span dir="ltr">&lt;/p&gt;</span>
              </p>

              <div className="grid grid-cols-[minmax(0,7fr)_minmax(0,5fr)] items-end gap-4">
                <Marquee label={c('skills.title')} duration={25}>
                  {skills.map((skill) => (
                    <SkillTile key={skill.id} name={skill.name} />
                  ))}
                </Marquee>
                <span className="mb-2 text-tm-300">{c('hero.more')}</span>
              </div>

              <a href="#contact" className="mt-8 inline-flex items-center gap-2 font-medium text-tm-300 transition-colors hover:text-[#62a92b]">
                <Send aria-hidden className="size-5 text-tm-primary rtl:-scale-x-100" />
                {c('hero.cta')}
              </a>
            </div>
          </div>
        </GlowCard>
      </div>
    </section>
  );
}
