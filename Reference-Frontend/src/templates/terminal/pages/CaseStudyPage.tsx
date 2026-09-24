import { Link } from '@tanstack/react-router';
import { ArrowLeft, ArrowRight, ArrowUpRight, ChevronDown } from 'lucide-react';
import type { ReactNode } from 'react';
import type { DictionaryKey } from '@/i18n/dictionary';
import { useTranslation } from '@/hooks/useTranslation';
import type { ProjectReference } from '@/lib/content';
import { ArchitectureDiagram } from '@/shared/content/ArchitectureDiagram';
import { CountUp } from '@/shared/ui/CountUp';
import { ResponsiveImage } from '@/shared/ui/ResponsiveImage';
import type { CaseStudyPageProps } from '@/templates/types';
import { useTerminalCopy } from '../copy';
import { GlowCard } from '../components/GlowCard';
import { Kicker } from '../components/Kicker';
import { PostCard } from '../components/PostCard';
import { useActiveSection } from '../lib/useActiveSection';

interface SectionDefinition {
  id: string;
  titleKey: DictionaryKey;
  visible: boolean;
  body: ReactNode;
}

function CaseSection({ id, index, title, children }: { id: string; index: number; title: string; children: ReactNode }) {
  return (
    <section id={id} aria-labelledby={`${id}-heading`} className="tm-box scroll-mt-10 p-5 md:p-10">
      <Kicker>
        <span className="ltr-isolate">{String(index + 1).padStart(2, '0')}</span>
      </Kicker>
      <h2 id={`${id}-heading`} className="mt-1 mb-8 text-[clamp(1.5rem,3vw,2.1875rem)] font-medium">
        {title}
      </h2>
      <div lang="en">{children}</div>
    </section>
  );
}

function AdjacentLink({ reference, label, direction }: { reference: ProjectReference | null; label: string; direction: 'previous' | 'next' }) {
  if (!reference) return <span aria-hidden />;
  const isNext = direction === 'next';
  const Icon = isNext ? ArrowRight : ArrowLeft;

  return (
    <Link
      to="/projects/$slug"
      params={{ slug: reference.slug }}
      className={`tm-box tm-hover-up flex flex-col gap-2 p-6 md:p-8 ${isNext ? 'md:items-end md:text-end' : ''}`}
    >
      <span className="flex items-center gap-2 text-sm text-tm-400">
        {!isNext && <Icon aria-hidden className="size-4 rtl:-scale-x-100" />}
        {label}
        {isNext && <Icon aria-hidden className="size-4 rtl:-scale-x-100" />}
      </span>
      <span lang="en" className="tm-text-gradient text-2xl font-medium">
        {reference.title}
      </span>
    </Link>
  );
}

export function CaseStudyPage({ project }: CaseStudyPageProps) {
  const { t } = useTranslation();
  const c = useTerminalCopy();

  const sections: SectionDefinition[] = [
    {
      id: 'overview',
      titleKey: 'case.overview',
      visible: true,
      body: (
        <div className="flex flex-col gap-4">
          {project.overview.map((paragraph, index) => (
            <p key={paragraph} className={index === 0 ? 'mb-0 text-xl leading-relaxed text-ink' : 'tm-prose mb-0'}>
              {paragraph}
            </p>
          ))}
        </div>
      ),
    },
    {
      id: 'problem',
      titleKey: 'case.problem',
      visible: project.problem.length > 0,
      body: (
        <div className="border-s-2 border-tm-secondary ps-6">
          {project.problem.map((paragraph) => (
            <p key={paragraph} className="tm-prose mb-4 last:mb-0">
              {paragraph}
            </p>
          ))}
        </div>
      ),
    },
    {
      id: 'approach',
      titleKey: 'case.approach',
      visible: project.approach.length > 0,
      body: (
        <ol className="grid grid-cols-1 gap-5 md:grid-cols-2">
          {project.approach.map((step, index) => (
            <li key={step.title} className="tm-box tm-hover-up rounded-md p-6">
              <span className="ltr-isolate text-sm text-tm-secondary">step_{String(index + 1).padStart(2, '0')}</span>
              <h3 className="my-3 text-[19px] font-medium">{step.title}</h3>
              <p className="mb-0 text-sm leading-relaxed text-tm-300">{step.description}</p>
            </li>
          ))}
        </ol>
      ),
    },
    {
      id: 'architecture',
      titleKey: 'case.architecture',
      visible: project.architecture.nodes.length > 0,
      body: <ArchitectureDiagram architecture={project.architecture} />,
    },
    {
      id: 'features',
      titleKey: 'case.features',
      visible: project.features.length > 0,
      body: (
        <ul className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {project.features.map((feature) => (
            <li key={feature.title} className="tm-box tm-hover-up rounded-md px-6 pt-10 pb-6">
              <span aria-hidden className="text-tm-primary">
                &lt;/&gt;
              </span>
              <h3 className="my-3 text-[19px] font-medium">{feature.title}</h3>
              <p className="mb-0 text-sm leading-relaxed text-tm-300">{feature.description}</p>
            </li>
          ))}
        </ul>
      ),
    },
    {
      id: 'challenges',
      titleKey: 'case.challenges',
      visible: project.challenges.length > 0,
      body: (
        <div className="flex flex-col gap-3">
          {project.challenges.map((challenge, index) => (
            <details key={challenge.title} open={index === 0} className="group rounded-md border border-tm-border">
              <summary className="flex cursor-pointer list-none items-center justify-between gap-4 p-5 text-ink [&::-webkit-details-marker]:hidden">
                {challenge.title}
                <ChevronDown aria-hidden className="size-5 shrink-0 text-tm-primary transition-transform group-open:rotate-180" />
              </summary>
              <p className="mb-0 border-t border-tm-border p-5 leading-relaxed text-tm-300">{challenge.description}</p>
            </details>
          ))}
        </div>
      ),
    },
    {
      id: 'metrics',
      titleKey: 'case.metrics',
      visible: project.metrics.length > 0,
      body: (
        <ul className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
          {project.metrics.map((metric) => (
            <li key={metric.id}>
              <p className="ltr-isolate mb-1 text-[2.75rem] leading-none font-medium text-ink">
                <CountUp value={metric.value} />
              </p>
              <p className="mb-1 text-ink">{metric.label}</p>
              <p className="mb-0 text-sm text-tm-300">{metric.detail}</p>
            </li>
          ))}
        </ul>
      ),
    },
    {
      id: 'gallery',
      titleKey: 'case.gallery',
      visible: project.gallery.length > 0,
      body: (
        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
          {project.gallery.map((image) => (
            <figure key={image.base} className="tm-zoom overflow-hidden rounded-md border border-tm-border">
              <ResponsiveImage image={image} sizes="(min-width: 768px) 45vw, 92vw" className="aspect-[4/3]" />
              <figcaption className="border-t border-tm-border p-4 text-sm text-tm-300">{image.caption}</figcaption>
            </figure>
          ))}
        </div>
      ),
    },
  ];

  const visible = sections.filter((section) => section.visible);
  const activeId = useActiveSection(visible.map((section) => section.id));

  const facts = [
    { label: t('case.role'), value: project.role },
    { label: t('case.team'), value: project.team },
    { label: t('case.timeline'), value: project.timeline },
    { label: t('case.company'), value: project.company?.name ?? t('case.independent') },
  ];

  return (
    <article>
      <header className="tm-container pt-[130px]">
        <GlowCard innerClassName="p-5 md:p-10 lg:p-16">
          <div aria-hidden className="tm-grid-bg tm-grid-fade pointer-events-none absolute inset-0" />
          <nav aria-label={t('common.breadcrumb')} className="relative mb-8">
            <Link to="/projects" className="inline-flex items-center gap-2 text-tm-300 hover:text-[#62a92b]">
              <ArrowLeft aria-hidden className="size-4 rtl:-scale-x-100" />
              {t('case.back')}
            </Link>
          </nav>
          <div className="relative grid grid-cols-1 gap-10 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] lg:gap-12">
            <div className="overflow-hidden rounded-md border border-tm-border">
              <ResponsiveImage image={project.cover} sizes="(min-width: 64rem) 40vw, 100vw" priority className="aspect-[4/3]" />
            </div>
            <div>
              <Kicker>
                {t(`category.${project.category}`)} · {t(`projectStatus.${project.status}`)} · <span className="ltr-isolate">{project.version}</span>
              </Kicker>
              <h1 lang="en" className="tm-text-gradient mt-2 mb-3 text-[clamp(2rem,5vw,3.125rem)] font-medium">
                {project.title}
              </h1>
              <p lang="en" className="text-tm-body">
                {project.tagline}
              </p>
              <p className="mt-6 mb-4 border-b border-tm-border pb-4 text-tm-secondary">{c('case.info')}</p>
              <dl>
                {facts.map((fact) => (
                  <div key={fact.label} className="mb-4 flex justify-between gap-6 border-b border-tm-border pb-4">
                    <dt className="text-ink">{fact.label}</dt>
                    <dd lang="en" className="mb-0 text-end text-tm-300">
                      {fact.value}
                    </dd>
                  </div>
                ))}
                <div className="mb-4 flex justify-between gap-6 border-b border-tm-border pb-4">
                  <dt className="text-ink">{t('case.stack')}</dt>
                  <dd lang="en" className="mb-0 flex flex-wrap justify-end gap-x-2 text-end text-tm-300">
                    {project.stack.map((technology, index) => (
                      <Link key={technology} to="/projects" search={{ tech: technology }} className="hover:text-[#62a92b]">
                        {technology}
                        {index < project.stack.length - 1 && ','}
                      </Link>
                    ))}
                  </dd>
                </div>
              </dl>
              {project.links.length > 0 && (
                <ul className="mt-8 flex flex-wrap gap-4">
                  {project.links.map((link) => (
                    <li key={link.url}>
                      <a href={link.url} target="_blank" rel="noreferrer" className="tm-link-hover inline-flex items-center gap-1.5 px-2 pb-2">
                        <ArrowUpRight aria-hidden className="size-4" />
                        {link.label}
                        <span className="sr-only"> {t('common.opensNewTab')}</span>
                      </a>
                    </li>
                  ))}
                </ul>
              )}
            </div>
          </div>
        </GlowCard>
      </header>

      <div className="tm-container grid grid-cols-1 gap-8 pt-8 lg:grid-cols-[14rem_minmax(0,1fr)]">
        <nav aria-label={t('case.contents')} className="hidden lg:block">
          <div className="tm-box sticky top-8 p-5">
            <p className="mb-3 text-sm text-tm-400">{t('case.contents')}</p>
            <ol className="flex flex-col gap-1">
              {visible.map((section, index) => (
                <li key={section.id}>
                  <a
                    href={`#${section.id}`}
                    aria-current={activeId === section.id ? 'location' : undefined}
                    className="flex gap-2 py-1 text-sm text-tm-300 hover:text-ink aria-[current=location]:text-tm-primary"
                  >
                    <span className="ltr-isolate">{String(index + 1).padStart(2, '0')}</span>
                    {t(section.titleKey)}
                  </a>
                </li>
              ))}
            </ol>
          </div>
        </nav>
        <div className="flex min-w-0 flex-col gap-8">
          {visible.map((section, index) => (
            <CaseSection key={section.id} id={section.id} index={index} title={t(section.titleKey)}>
              {section.body}
            </CaseSection>
          ))}

          {project.relatedArticles.length > 0 && (
            <section aria-labelledby="related-writing">
              <h2 id="related-writing" className="mb-6 text-xl font-medium">
                {t('case.relatedWriting')}
              </h2>
              <ul className="grid grid-cols-1 gap-6 md:grid-cols-2">
                {project.relatedArticles.map((article) => (
                  <li key={article.slug}>
                    <PostCard article={article} cover={project.cover} />
                  </li>
                ))}
              </ul>
            </section>
          )}

          <nav aria-label={t('case.adjacent')} className="grid grid-cols-1 gap-6 md:grid-cols-2">
            <AdjacentLink reference={project.previous} label={t('case.previous')} direction="previous" />
            <AdjacentLink reference={project.next} label={t('case.next')} direction="next" />
          </nav>
        </div>
      </div>
    </article>
  );
}
