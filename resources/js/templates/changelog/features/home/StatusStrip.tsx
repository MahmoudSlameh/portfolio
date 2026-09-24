import { StatusBadge } from '@/templates/changelog/components/ui/StatusBadge';
import { useNow } from '@/hooks/useLocalTime';
import { useTranslation } from '@/hooks/useTranslation';
import type { Profile } from '@/types/content';
import { cn, formatTime } from '@/lib/utils';

export function StatusStrip({ profile }: { profile: Profile }) {
    const { t } = useTranslation();
    const now = useNow();

    return (
        <section
            aria-label={t('status.label')}
            className="border-line bg-surface border-y"
        >
            <div className="shell">
                <dl className="no-scrollbar flex snap-x gap-0 overflow-x-auto">
                    <div className="flex shrink-0 snap-start flex-col justify-center gap-1.5 py-4 pe-8">
                        <dt className="eyebrow text-ink-subtle">
                            {t('status.available')}
                        </dt>
                        <dd>
                            <StatusBadge
                                label={profile.availability.label}
                                tone="signal"
                            />
                        </dd>
                    </div>
                    {profile.status.map((entry) => (
                        <div
                            key={entry.id}
                            className="border-line flex max-w-[20rem] shrink-0 snap-start flex-col justify-center gap-1.5 border-s py-4 ps-6 pe-8"
                        >
                            <dt className="eyebrow text-ink-subtle">
                                {entry.label}
                            </dt>
                            <dd
                                className={cn(
                                    'truncate text-[0.875rem] font-medium',
                                    entry.tone === 'signal'
                                        ? 'text-signal-ink'
                                        : 'text-ink',
                                )}
                            >
                                {entry.value}
                            </dd>
                        </div>
                    ))}
                    <div className="border-line ms-auto flex shrink-0 snap-start flex-col justify-center gap-1.5 border-s py-4 ps-6">
                        <dt className="eyebrow text-ink-subtle">
                            {t('status.localTime')}
                        </dt>
                        <dd className="text-ink flex items-baseline gap-2 text-[0.875rem] font-medium">
                            <time
                                dateTime={now.toISOString()}
                                className="ltr-isolate font-mono"
                            >
                                {formatTime(now, profile.timezone)}
                            </time>
                            <span className="ltr-isolate text-ink-subtle font-mono text-[0.6875rem]">
                                {profile.timezoneLabel}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>
        </section>
    );
}
