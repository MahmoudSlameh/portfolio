import { useEffect, useState } from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import { cn } from '@/lib/utils';

export interface TocEntry {
    id: string;
    text: string;
}

const useActiveHeading = (ids: string[]): string | null => {
    const [activeId, setActiveId] = useState<string | null>(ids[0] ?? null);

    useEffect(() => {
        const elements = ids
            .map((id) => document.getElementById(id))
            .filter((element): element is HTMLElement => element !== null);
        if (elements.length === 0) return;

        const visible = new Set<string>();
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) =>
                    entry.isIntersecting
                        ? visible.add(entry.target.id)
                        : visible.delete(entry.target.id),
                );
                const firstVisible = ids.find((id) => visible.has(id));
                if (firstVisible) setActiveId(firstVisible);
            },
            { rootMargin: '-96px 0px -60% 0px' },
        );

        elements.forEach((element) => observer.observe(element));
        return () => observer.disconnect();
    }, [ids]);

    return activeId;
};

export function TableOfContents({ entries }: { entries: TocEntry[] }) {
    const { t } = useTranslation();
    const [ids] = useState(() => entries.map((entry) => entry.id));
    const activeId = useActiveHeading(ids);

    if (entries.length === 0) return null;

    return (
        <nav aria-label={t('article.toc')}>
            <p className="eyebrow text-ink-subtle mb-4">{t('article.toc')}</p>
            <ol lang="en" className="border-line flex flex-col border-s">
                {entries.map((entry, index) => {
                    const isActive = entry.id === activeId;
                    return (
                        <li key={entry.id}>
                            <a
                                href={`#${entry.id}`}
                                aria-current={isActive ? 'location' : undefined}
                                className={cn(
                                    '-ms-px flex gap-3 border-s py-1.5 ps-4 text-sm leading-snug transition-colors',
                                    isActive
                                        ? 'border-signal text-ink'
                                        : 'text-ink-muted hover:border-ink-subtle hover:text-ink border-transparent',
                                )}
                            >
                                <span className="ltr-isolate text-ink-subtle font-mono text-[0.6875rem]">
                                    {String(index + 1).padStart(2, '0')}
                                </span>
                                {entry.text}
                            </a>
                        </li>
                    );
                })}
            </ol>
        </nav>
    );
}
