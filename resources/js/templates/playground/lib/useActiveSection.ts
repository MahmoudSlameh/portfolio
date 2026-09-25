import { useEffect, useState } from 'react';

export function useActiveSection(ids: string[]): string | null {
    const [activeId, setActiveId] = useState<string | null>(ids[0] ?? null);
    const key = ids.join('|');

    useEffect(() => {
        const sectionIds = key.split('|').filter(Boolean);
        const elements = sectionIds
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
                const firstVisible = sectionIds.find((id) => visible.has(id));
                if (firstVisible) setActiveId(firstVisible);
            },
            { rootMargin: '-20% 0px -55% 0px' },
        );

        elements.forEach((element) => observer.observe(element));
        return () => observer.disconnect();
    }, [key]);

    return activeId;
}
