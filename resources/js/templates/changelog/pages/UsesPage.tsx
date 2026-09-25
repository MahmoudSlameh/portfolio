import { ArrowUpRight } from 'lucide-react';
import { PageHeader } from '@/templates/changelog/components/ui/PageHeader';
import { useReveal } from '@/hooks/useReveal';
import { useTranslation } from '@/hooks/useTranslation';
import type { UsesPageProps } from '@/templates/types';
import type { UsesGroup } from '@/types/content';

function UsesGroupSection({
    group,
    index,
}: {
    group: UsesGroup;
    index: number;
}) {
    const { t } = useTranslation();
    const revealRef = useReveal<HTMLElement>();
    const headingId = `uses-${group.id}`;

    return (
        <section
            ref={revealRef}
            aria-labelledby={headingId}
            className="reveal editorial-grid gap-y-6 border-t border-line py-12 md:py-16"
        >
            <div className="col-span-4 md:col-span-4">
                <p className="ltr-isolate font-mono text-xs text-signal-ink">
                    {String(index + 1).padStart(2, '0')}
                </p>
                <h2
                    id={headingId}
                    className="mt-3 font-display text-4xl leading-none text-ink md:text-5xl"
                >
                    {group.title}
                </h2>
                <p className="mt-3 font-mono text-xs text-ink-subtle">
                    {t('common.results', { count: group.items.length })}
                </p>
            </div>
            <ul
                lang="en"
                className="col-span-4 border-t border-line md:col-span-8 md:border-t-0"
            >
                {group.items.map((item) => (
                    <li
                        key={item.id}
                        className="grid gap-1 border-b border-line py-5 sm:grid-cols-[minmax(0,15rem)_minmax(0,1fr)] sm:gap-8 md:first:pt-0"
                    >
                        {item.url ? (
                            <a
                                href={item.url}
                                target="_blank"
                                rel="noreferrer"
                                className="group inline-flex items-start gap-1.5 self-start font-medium text-ink"
                            >
                                <span className="link-draw-target">
                                    {item.name}
                                </span>
                                <ArrowUpRight
                                    aria-hidden
                                    className="mt-0.5 size-3.5 shrink-0 text-ink-subtle group-hover:text-signal-ink"
                                />
                                <span className="sr-only">
                                    {t('common.opensNewTab')}
                                </span>
                            </a>
                        ) : (
                            <p className="font-medium text-ink">{item.name}</p>
                        )}
                        <p className="text-[0.9375rem] leading-relaxed text-ink-muted">
                            {item.description}
                        </p>
                    </li>
                ))}
            </ul>
        </section>
    );
}

export function UsesPage({ groups }: UsesPageProps) {
    const { t } = useTranslation();

    return (
        <>
            <PageHeader
                index="10"
                label={t('nav.uses')}
                version="package.json"
                title={t('uses.title')}
                intro={t('uses.intro')}
            />
            <div className="shell pb-16">
                {groups.map((group, index) => (
                    <UsesGroupSection
                        key={group.id}
                        group={group}
                        index={index}
                    />
                ))}
            </div>
        </>
    );
}
