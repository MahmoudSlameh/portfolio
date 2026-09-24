import { Award, HeartHandshake, Monitor, Shapes, type LucideIcon } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';
import { CountUp } from '@/shared/ui/CountUp';
import type { Profile } from '@/types/content';

const ICONS: LucideIcon[] = [Shapes, Monitor, HeartHandshake, Award];

/** Splits "140+" into a counted part ("140") and a muted suffix ("+"), keeping "$2.1B" as "$2.1" + "B". */
const splitValue = (value: string): { counted: string; suffix: string } => {
  const match = /^(\D*\d+(?:\.\d+)?)(.*)$/.exec(value);
  return match ? { counted: match[1], suffix: match[2] } : { counted: value, suffix: '' };
};

export function Stats({ profile }: { profile: Profile }) {
  const { t, l } = useTranslation();

  return (
    <div className="tm-container">
      <section aria-label={t('hero.statsLabel')} className="tm-box relative overflow-hidden py-[60px]">
        <div aria-hidden className="tm-grid-bg pointer-events-none absolute inset-0" />
        <ul className="relative grid grid-cols-1 gap-10 px-6 sm:grid-cols-2 lg:flex lg:justify-around lg:gap-6">
          {profile.stats.map((stat, index) => {
            const Icon = ICONS[index % ICONS.length];
            const { counted, suffix } = splitValue(stat.value);
            return (
              <li key={stat.id} className="flex flex-col items-center text-center lg:items-start lg:text-start">
                <Icon aria-hidden className="mb-1 size-5 text-tm-primary" />
                <p className="ltr-isolate mb-0 text-[50px] leading-[1.2] text-tm-300">
                  <span className="font-medium text-ink">
                    <CountUp value={counted} />
                  </span>
                  {suffix}
                </p>
                <p className="mb-0 text-ink">{l(stat.label)}</p>
              </li>
            );
          })}
        </ul>
      </section>
    </div>
  );
}
