import { ArrowUpRight } from 'lucide-react';
import { useRef, useState, type KeyboardEvent } from 'react';
import { Section } from '@/templates/changelog/components/ui/Section';
import { Tag } from '@/templates/changelog/components/ui/Tag';
import { sectionIndex } from '@/config/navigation';
import { useTranslation } from '@/hooks/useTranslation';
import type { TestimonialEntry } from '@/lib/content';
import { cn } from '@/lib/utils';
import type { Company, WordmarkStyle } from '@/types/content';
import { TestimonialCarousel } from './TestimonialCarousel';

const wordmarkClasses: Record<WordmarkStyle, string> = {
  serif: 'font-display text-[1.75rem] leading-none !font-extrabold',
  'serif-italic': 'font-display text-[1.75rem] !font-extralight leading-none !tracking-[-0.05em]',
  mono: 'font-mono text-base font-semibold lowercase',
  'sans-bold': 'font-sans text-xl font-bold tracking-[-0.04em]',
  'sans-light': 'font-sans text-xl font-light tracking-[0.02em]',
  spaced: 'font-sans text-[0.8125rem] font-medium uppercase tracking-[0.28em]',
};

function Wordmark({ company }: { company: Company }) {
  return (
    <span lang="en" dir="ltr" className={cn('whitespace-nowrap', wordmarkClasses[company.wordmark])}>
      {company.name}
    </span>
  );
}

interface ClientsSectionProps {
  companies: Company[];
  testimonials: TestimonialEntry[];
}

export function ClientsSection({ companies, testimonials }: ClientsSectionProps) {
  const { t, isRtl } = useTranslation();
  const [selectedIndex, setSelectedIndex] = useState(0);
  const tabRefs = useRef<(HTMLButtonElement | null)[]>([]);
  const selected = companies[selectedIndex];
  const selectedTestimonial = testimonials.find((testimonial) => testimonial.companyId === selected.id);

  const focusTab = (index: number): void => {
    const nextIndex = (index + companies.length) % companies.length;
    setSelectedIndex(nextIndex);
    tabRefs.current[nextIndex]?.focus();
  };

  const handleKeyDown = (event: KeyboardEvent<HTMLDivElement>): void => {
    const forward = isRtl ? 'ArrowLeft' : 'ArrowRight';
    const backward = isRtl ? 'ArrowRight' : 'ArrowLeft';
    const keyActions: Record<string, () => void> = {
      [forward]: () => focusTab(selectedIndex + 1),
      [backward]: () => focusTab(selectedIndex - 1),
      ArrowDown: () => focusTab(selectedIndex + 1),
      ArrowUp: () => focusTab(selectedIndex - 1),
      Home: () => focusTab(0),
      End: () => focusTab(companies.length - 1),
    };
    const action = keyActions[event.key];
    if (!action) return;
    event.preventDefault();
    action();
  };

  return (
    <Section
      id="clients"
      index={sectionIndex('clients')}
      label={t('section.clients')}
      version="CONTRIBUTORS"
      title={t('clients.title')}
      intro={t('clients.intro')}
    >
      <div className="editorial-grid gap-y-8">
        <div
          role="tablist"
          aria-label={t('clients.listLabel')}
          aria-orientation="horizontal"
          onKeyDown={handleKeyDown}
          className="col-span-4 grid grid-cols-2 gap-px self-start border border-line bg-line md:col-span-7 md:grid-cols-4"
        >
          {companies.map((company, index) => {
            const isSelected = index === selectedIndex;
            return (
              <button
                key={company.id}
                ref={(element) => {
                  tabRefs.current[index] = element;
                }}
                id={`client-tab-${company.id}`}
                type="button"
                role="tab"
                aria-selected={isSelected}
                aria-controls="client-panel"
                tabIndex={isSelected ? 0 : -1}
                onClick={() => setSelectedIndex(index)}
                className={cn(
                  'relative flex h-24 items-center justify-center px-3 transition-colors duration-300 md:h-28',
                  isSelected ? 'bg-raised text-ink' : 'bg-paper text-ink-subtle hover:bg-raised hover:text-ink',
                )}
              >
                <Wordmark company={company} />
                <span
                  aria-hidden
                  className={cn(
                    'absolute inset-x-0 bottom-0 h-0.5 bg-[image:var(--gradient-brand)] transition-transform duration-300',
                    isSelected ? 'scale-x-100' : 'scale-x-0',
                  )}
                />
              </button>
            );
          })}
        </div>

        <div
          id="client-panel"
          role="tabpanel"
          aria-labelledby={`client-tab-${selected.id}`}
          tabIndex={0}
          className="col-span-4 flex flex-col border border-line bg-raised p-6 md:col-span-5 md:p-8"
        >
          <div key={selected.id} className="animate-rise flex h-full flex-col" style={{ animationDuration: '500ms' }}>
            <div className="flex items-center justify-between gap-3">
              <Tag tone={selected.kind === 'employer' ? 'signal' : 'default'}>
                {selected.kind === 'employer' ? t('clients.employer') : t('clients.client')}
              </Tag>
              <span className="ltr-isolate font-mono text-xs text-ink-subtle">{selected.period}</span>
            </div>
            <p lang="en" className="mt-6 font-display text-4xl leading-none text-ink">
              {selected.name}
            </p>
            <p lang="en" className="mt-2 text-sm text-ink-subtle">
              {selected.industry} · {selected.location}
            </p>
            <p lang="en" className="mt-5 text-[0.9375rem] leading-relaxed text-ink-muted">
              {selected.engagement}
            </p>
            {selectedTestimonial && (
              <blockquote lang="en" className="mt-6 border-s-2 border-signal ps-4 text-[0.9375rem] leading-relaxed text-ink">
                “{selectedTestimonial.quote.split('. ')[0]}.”
                <footer className="mt-2 text-[0.8125rem] text-ink-subtle">— {selectedTestimonial.author}</footer>
              </blockquote>
            )}
            <a
              href={selected.url}
              target="_blank"
              rel="noreferrer"
              className="group mt-auto inline-flex items-center gap-1.5 pt-6 text-sm font-medium text-ink"
            >
              <span className="link-draw-target">{t('clients.visit')}</span>
              <ArrowUpRight aria-hidden className="size-4 rtl:-scale-x-100" />
              <span className="sr-only">{t('common.opensNewTab')}</span>
            </a>
          </div>
        </div>
      </div>

      <div className="mt-20">
        <h3 className="eyebrow mb-6 text-ink-subtle">{t('clients.testimonials')}</h3>
        <TestimonialCarousel testimonials={testimonials} />
      </div>
    </Section>
  );
}
