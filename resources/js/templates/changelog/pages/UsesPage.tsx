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
            className="reveal editorial-grid border-line gap-y-6 border-t py-12 md:py-16"
        >
            <div className="col-span-4 md:col-span-4">
                <p className="ltr-isolate text-signal-ink font-mono text-xs">
                    {String(index + 1).padStart(2, '0')}
                </p>
                <h2
                    id={headingId}
                    className="font-display text-ink mt-3 text-4xl leading-none md:text-5xl"
                >
                    {group.title}
                </h2>
                <p className="text-ink-subtle mt-3 font-mono text-xs">
                    {t('common.results', { count: group.items.length })}
                </p>
            </div>
            <ul
                lang="en"
                className="border-line col-span-4 border-t md:col-span-8 md:border-t-0"
            >
                {group.items.map((item) => (
                    <li
                        key={item.id}
                        className="border-line grid gap-1 border-b py-5 sm:grid-cols-[minmax(0,15rem)_minmax(0,1fr)] sm:gap-8 md:first:pt-0"
                    >
                        {item.url ? (
                            <a
                                href={item.url}
                                target="_blank"
                                rel="noreferrer"
                                className="group text-ink inline-flex items-start gap-1.5 self-start font-medium"
                            >
                                <span className="link-draw-target">
                                    {item.name}
                                </span>
                                <ArrowUpRight
                                    aria-hidden
                                    className="text-ink-subtle group-hover:text-signal-ink mt-0.5 size-3.5 shrink-0"
                                />
                                <span className="sr-only">
                                    {t('common.opensNewTab')}
                                </span>
                            </a>
                        ) : (
                            <p className="text-ink font-medium">{item.name}</p>
                        )}
                        <p className="text-ink-muted text-[0.9375rem] leading-relaxed">
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
