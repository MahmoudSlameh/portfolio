import { BookMarked, ScanLine } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';
import type { Certification, Education as EducationEntry } from '@/types/content';
import { useTerminalCopy } from '../copy';
import { GlowCard } from '../components/GlowCard';
import { Timeline } from '../components/Timeline';

export function Education({ education, certifications }: { education: EducationEntry[]; certifications: Certification[] }) {
  const { t } = useTranslation();
  const c = useTerminalCopy();

  return (
    <div className="tm-container grid grid-cols-1 gap-6 pt-8 lg:grid-cols-2">
      <GlowCard as="section" id="education" aria-labelledby="education-title" innerClassName="p-4 md:p-10">
        <h2 id="education-title" className="mb-0 flex items-center gap-3 text-[clamp(2rem,4vw,2.5rem)] font-medium">
          <BookMarked aria-hidden className="size-7 shrink-0 text-tm-primary" />
          {c('resume.education')}
        </h2>
        <Timeline
          className="mt-10 min-h-[320px]"
          items={education.map((entry) => ({
            id: entry.id,
            date: `${entry.start}-${entry.end}`,
            title: <span lang="en">{entry.institution}</span>,
            body: (
              <span lang="en">
                {entry.degree}, {entry.field}
                {entry.notes[0] && <span className="mt-1 block text-sm text-tm-300">{entry.notes[0]}</span>}
              </span>
            ),
          }))}
        />
      </GlowCard>

      <section aria-labelledby="certifications-title" className="tm-box relative overflow-hidden p-4 md:p-10">
        <h2 id="certifications-title" className="mb-0 flex items-center gap-3 text-[clamp(2rem,4vw,2.5rem)] font-medium">
          <ScanLine aria-hidden className="size-7 shrink-0 text-tm-primary" />
          {c('resume.certifications')}
        </h2>
        <Timeline
          className="mt-10 min-h-[320px]"
          items={certifications.map((entry) => ({
            id: entry.id,
            date: String(entry.year),
            title: (
              <a href={entry.url} target="_blank" rel="noreferrer" lang="en" className="hover:underline">
                {entry.name}
                <span className="sr-only"> {t('common.opensNewTab')}</span>
              </a>
            ),
            body: (
              <span lang="en">
                {entry.issuer} · <span className="text-tm-300">{t('education.credential', { id: entry.credentialId })}</span>
              </span>
            ),
          }))}
        />
      </section>
    </div>
  );
}
