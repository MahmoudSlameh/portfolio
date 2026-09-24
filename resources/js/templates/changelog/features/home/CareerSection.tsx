import { Section } from '@/templates/changelog/components/ui/Section';
import { sectionIndex } from '@/config/navigation';
import { useTranslation } from '@/hooks/useTranslation';
import type { CareerEntry } from '@/lib/content';
import { CareerGraph } from './career/CareerGraph';

export function CareerSection({ entries }: { entries: CareerEntry[] }) {
    const { t } = useTranslation();
    const firstYear = Math.min(
        ...entries.map((entry) => Number(entry.start.slice(0, 4))),
    );
    const years =
        entries.length > 0
            ? Math.max(1, new Date().getUTCFullYear() - firstYear)
            : 0;

    return (
        <Section
            id="career"
            index={sectionIndex('career')}
            label={t('section.career')}
            version="git log --graph"
            title={t('career.title', { years })}
            intro={t('career.intro')}
        >
            <CareerGraph entries={entries} />
        </Section>
    );
}
