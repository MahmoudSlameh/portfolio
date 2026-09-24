import { StatusBadge } from '@/templates/changelog/components/ui/StatusBadge';
import { useNow } from '@/hooks/useLocalTime';
import { useTranslation } from '@/hooks/useTranslation';
import type { Profile } from '@/types/content';
import { cn, formatTime } from '@/lib/utils';

export function StatusStrip({ profile }: { profile: Profile }) {
  const { t, l, locale } = useTranslation();
  const now = useNow();

  return (
    <section aria-label={t('status.label')} className="border-y border-line bg-surface">
      <div className="shell">
        <dl className="no-scrollbar flex snap-x gap-0 overflow-x-auto">
          <div className="flex shrink-0 snap-start flex-col justify-center gap-1.5 py-4 pe-8">
            <dt className="eyebrow text-ink-subtle">{t('status.available')}</dt>
            <dd>
              <StatusBadge label={l(profile.availability.label)} tone="signal" />
            </dd>
          </div>
          {profile.status.map((entry) => (
            <div
              key={entry.id}
              className="flex max-w-[20rem] shrink-0 snap-start flex-col justify-center gap-1.5 border-s border-line py-4 ps-6 pe-8"
            >
              <dt className="eyebrow text-ink-subtle">{l(entry.label)}</dt>
              <dd className={cn('truncate text-[0.875rem] font-medium', entry.tone === 'signal' ? 'text-signal-ink' : 'text-ink')}>
                {l(entry.value)}
              </dd>
            </div>
          ))}
          <div className="ms-auto flex shrink-0 snap-start flex-col justify-center gap-1.5 border-s border-line py-4 ps-6">
            <dt className="eyebrow text-ink-subtle">{t('status.localTime')}</dt>
            <dd className="flex items-baseline gap-2 text-[0.875rem] font-medium text-ink">
              <time dateTime={now.toISOString()} className="ltr-isolate font-mono">
                {formatTime(now, profile.timezone, locale)}
              </time>
              <span className="ltr-isolate font-mono text-[0.6875rem] text-ink-subtle">{profile.timezoneLabel}</span>
            </dd>
          </div>
        </dl>
      </div>
    </section>
  );
}
