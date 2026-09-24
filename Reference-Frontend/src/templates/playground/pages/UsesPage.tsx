import { ArrowUpRight, Code2, Cpu, Package } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';
import { cn } from '@/lib/utils';
import type { UsesPageProps } from '@/templates/types';
import type { UsesGroup } from '@/types/content';
import { usePlaygroundCopy } from '../copy';
import { popBg, popForIndex } from '../lib/pops';
import { PageHero } from '../components/PageHero';
import { Reveal } from '../components/Reveal';

const kindIcon: Record<UsesGroup['kind'], typeof Cpu> = {
  hardware: Cpu,
  software: Package,
  development: Code2,
};

export function UsesPage({ groups }: UsesPageProps) {
  const { t, l } = useTranslation();
  const p = usePlaygroundCopy();

  return (
    <>
      <PageHero kicker={p('uses.kicker')} title={t('uses.title')} intro={t('uses.intro')} pop="purple" />
      <div className="pg-shell grid gap-8 lg:grid-cols-2">
        {groups.map((group, groupIndex) => {
          const Icon = kindIcon[group.kind];
          const pop = popForIndex(groupIndex + 1);
          return (
            <Reveal as="section" key={group.id} labelledBy={`uses-${group.id}`} className={cn(groupIndex === 0 && 'lg:col-span-2')}>
              <div className="pg-card overflow-hidden">
                <header className={cn('flex items-center gap-4 border-b-2 border-edge px-6 py-5 text-on-pop', popBg[pop])}>
                  <span className="inline-flex size-12 items-center justify-center rounded-xl border-2 border-edge bg-raised text-ink">
                    <Icon aria-hidden className="size-6" strokeWidth={2.25} />
                  </span>
                  <h2 id={`uses-${group.id}`} className="pg-display text-3xl md:text-4xl">
                    {l(group.title)}
                  </h2>
                  <span className="ltr-isolate ms-auto rounded-full border-2 border-edge bg-raised px-3 font-mono text-sm font-bold text-ink">
                    {group.items.length}
                  </span>
                </header>
                <ul lang="en" className={cn('grid gap-[2px] bg-edge', groupIndex === 0 && 'md:grid-cols-2')}>
                  {group.items.map((item) => (
                    <li key={item.id} className={cn('bg-raised', groupIndex === 0 && 'md:last:odd:col-span-2')}>
                      <div className="flex h-full items-start justify-between gap-4 p-5">
                        <div>
                          <p className="text-lg font-bold text-ink">{item.name}</p>
                          <p className="mt-1 text-[0.9375rem] leading-relaxed text-ink-muted">{item.description}</p>
                        </div>
                        {item.url && (
                          <a
                            href={item.url}
                            target="_blank"
                            rel="noreferrer"
                            aria-label={`${p('uses.visit', { name: item.name })} ${t('common.opensNewTab')}`}
                            className="pg-press inline-flex size-10 shrink-0 items-center justify-center rounded-full border-2 border-edge bg-pop-yellow text-on-pop shadow-[var(--pg-shadow)]"
                          >
                            <ArrowUpRight aria-hidden className="size-4" strokeWidth={2.5} />
                          </a>
                        )}
                      </div>
                    </li>
                  ))}
                </ul>
              </div>
            </Reveal>
          );
        })}
      </div>
    </>
  );
}
