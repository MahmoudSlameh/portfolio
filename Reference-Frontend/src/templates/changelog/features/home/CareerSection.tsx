import { Section } from '@/templates/changelog/components/ui/Section';
import { sectionIndex } from '@/config/navigation';
import { useTranslation } from '@/hooks/useTranslation';
import type { CareerEntry } from '@/lib/content';
import { CareerGraph } from './career/CareerGraph';

export function CareerSection({ entries }: { entries: CareerEntry[] }) {
  const { t } = useTranslation();

  return (
    <Section
      id="career"
      index={sectionIndex('career')}
      label={t('section.career')}
      version="git log --graph"
      title={t('career.title')}
      intro={t('career.intro')}
    >
      <CareerGraph entries={entries} />
    </Section>
  );
}
