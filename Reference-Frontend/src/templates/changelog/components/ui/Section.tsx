import type { ReactNode } from 'react';
import { useReveal } from '@/hooks/useReveal';
import { accentForLabel, accentPill, splitLastWord } from '@/templates/changelog/lib/accents';
import { cn } from '@/lib/utils';

interface SectionLabelProps {
  index: string;
  label: string;
  version?: string;
  className?: string;
}

export function SectionLabel({ index, label, version, className }: SectionLabelProps) {
  return (
    <p className={cn('flex items-center gap-3 text-ink-subtle', className)}>
      <span className={cn('eyebrow ltr-isolate rounded-full px-2 py-0.5 font-semibold', accentPill[accentForLabel(index)])}>
        {index}
      </span>
      <span className="eyebrow">{label}</span>
      {version && <span className="version-label ms-auto sm:ms-0">{version}</span>}
    </p>
  );
}

export function GradientTitle({ text }: { text: string }) {
  const { lead, last } = splitLastWord(text);
  return (
    <>
      {lead}
      <span className="text-gradient">{last}</span>
    </>
  );
}

interface SectionProps {
  id: string;
  index: string;
  label: string;
  version?: string;
  title: string;
  intro?: string;
  action?: ReactNode;
  children: ReactNode;
  className?: string;
}

export function Section({ id, index, label, version, title, intro, action, children, className }: SectionProps) {
  const revealRef = useReveal<HTMLDivElement>();
  const headingId = `${id}-heading`;

  return (
    <section id={id} aria-labelledby={headingId} className={cn('hairline-top relative py-20 md:py-28', className)}>
      <div ref={revealRef} className="reveal shell">
        <header className="editorial-grid mb-12 gap-y-6 md:mb-16">
          <div className="col-span-4 md:col-span-3">
            <SectionLabel index={index} label={label} version={version} />
          </div>
          <div className="col-span-4 md:col-span-9 lg:col-span-7">
            <h2
              id={headingId}
              className="font-display text-[2.25rem] leading-[1.02] text-ink sm:text-5xl lg:text-[3.5rem]"
            >
              <GradientTitle text={title} />
            </h2>
            {intro && <p className="mt-5 max-w-xl text-base leading-relaxed text-ink-muted md:text-lg">{intro}</p>}
          </div>
          {action && (
            <div className="col-span-4 flex items-end md:col-span-12 md:col-start-4 lg:col-span-2 lg:col-start-11 lg:justify-end">
              {action}
            </div>
          )}
        </header>
        {children}
      </div>
    </section>
  );
}
