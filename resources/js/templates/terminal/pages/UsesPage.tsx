import { ArrowUpRight, Code2, Cpu, Package } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';
import type { UsesPageProps } from '@/templates/types';
import type { UsesGroup } from '@/types/content';
import { useTerminalCopy } from '../copy';
import { PageHeader } from '../components/PageHeader';

const kindIcon: Record<UsesGroup['kind'], typeof Cpu> = {
    hardware: Cpu,
    software: Package,
    development: Code2,
};

export function UsesPage({ groups }: UsesPageProps) {
    const { t } = useTranslation();
    const c = useTerminalCopy();

    return (
        <>
            <PageHeader
                kicker={c('uses.kicker')}
                title={t('uses.title')}
                intro={t('uses.intro')}
            />
            <div className="tm-container grid grid-cols-1 gap-6 pt-8 lg:grid-cols-2">
                {groups.map((group, index) => {
                    const Icon = kindIcon[group.kind];
                    return (
                        <section
                            key={group.id}
                            aria-labelledby={`uses-${group.id}`}
                            className={`tm-box p-5 md:p-10 ${index === 0 ? 'lg:col-span-2' : ''}`}
                        >
                            <h2
                                id={`uses-${group.id}`}
                                className="mb-6 flex items-center gap-3 text-[clamp(1.75rem,3vw,2.25rem)] font-medium"
                            >
                                <Icon
                                    aria-hidden
                                    className="size-7 text-tm-primary"
                                />
                                {group.title}
                                <span className="ltr-isolate ms-auto text-base font-normal text-tm-400">
                                    [{group.items.length}]
                                </span>
                            </h2>
                            <ul
                                lang="en"
                                className={`grid gap-x-10 ${index === 0 ? 'md:grid-cols-2' : ''}`}
                            >
                                {group.items.map((item) => (
                                    <li
                                        key={item.id}
                                        className="flex items-start justify-between gap-4 border-b border-tm-border py-4"
                                    >
                                        <div>
                                            <p className="mb-1 text-ink">
                                                {item.name}
                                            </p>
                                            <p className="mb-0 text-sm leading-relaxed text-tm-300">
                                                {item.description}
                                            </p>
                                        </div>
                                        {item.url && (
                                            <a
                                                href={item.url}
                                                target="_blank"
                                                rel="noreferrer"
                                                aria-label={`${item.name} ${t('common.opensNewTab')}`}
                                                className="inline-flex size-9 shrink-0 items-center justify-center rounded-md border border-tm-border text-tm-300 transition-colors hover:border-tm-primary hover:text-tm-primary"
                                            >
                                                <ArrowUpRight
                                                    aria-hidden
                                                    className="size-4"
                                                />
                                            </a>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        </section>
                    );
                })}
            </div>
        </>
    );
}
