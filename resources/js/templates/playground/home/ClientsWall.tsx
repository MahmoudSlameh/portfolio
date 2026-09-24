import { CompanyLogo } from '@/shared/ui/CompanyLogo';
import { useTranslation } from '@/hooks/useTranslation';
import type { TestimonialEntry } from '@/lib/content';
import { cn } from '@/lib/utils';
import type { Company, WordmarkStyle } from '@/types/content';
import { usePlaygroundCopy } from '../copy';
import { popBg, popForIndex, popHoverBg, tiltForIndex } from '../lib/pops';
import { Reveal } from '../components/Reveal';
import { SectionHeading } from '../components/SectionHeading';

const wordmarkClass: Record<WordmarkStyle, string> = {
    serif: 'font-serif text-2xl font-bold',
    'serif-italic': 'font-serif text-2xl italic',
    mono: 'font-mono text-lg font-bold tracking-tight',
    'sans-bold': 'font-display text-2xl font-black [font-stretch:125%]',
    'sans-light': 'font-display text-2xl font-light [font-stretch:90%]',
    spaced: 'font-sans text-base font-bold tracking-[0.3em] uppercase',
};

function CompanyCell({ company, index }: { company: Company; index: number }) {
    const { t } = useTranslation();
    const pop = popForIndex(index);

    return (
        <li>
            <a
                href={company.url ?? undefined}
                target="_blank"
                rel="noreferrer"
                className={cn(
                    'group flex h-full min-h-36 flex-col justify-between gap-4 bg-raised p-5 transition-colors duration-200 hover:text-on-pop',
                    popHoverBg[pop],
                )}
            >
                <span className="flex items-center justify-between gap-2">
                    <span className="pg-tag text-ink group-hover:text-on-pop">
                        {t(
                            company.kind === 'employer'
                                ? 'clients.employer'
                                : 'clients.client',
                        )}
                    </span>
                    <span
                        aria-hidden
                        className="text-lg transition-transform duration-300 group-hover:rotate-45 rtl:-scale-x-100"
                    >
                        ↗
                    </span>
                </span>
                <span
                    lang="en"
                    className={cn(
                        'text-ink group-hover:text-on-pop',
                        wordmarkClass[company.wordmark],
                    )}
                >
                    <CompanyLogo
                        company={company}
                        fallback={company.name}
                        className="h-9"
                    />
                </span>
                <span
                    lang="en"
                    className="text-xs font-semibold text-ink-subtle group-hover:text-on-pop"
                >
                    {company.industry} · {company.period}
                    <span className="sr-only"> {t('common.opensNewTab')}</span>
                </span>
            </a>
        </li>
    );
}

function NoteCard({
    testimonial,
    index,
}: {
    testimonial: TestimonialEntry;
    index: number;
}) {
    const pop = popForIndex(index + 2);

    return (
        <Reveal as="li" delay={index * 90}>
            <figure
                style={{ rotate: `${tiltForIndex(index) * 0.6}deg` }}
                className={cn(
                    'pg-card pg-press relative flex h-full flex-col gap-5 p-6 pt-9 text-on-pop',
                    popBg[pop],
                )}
            >
                <span
                    aria-hidden
                    className="absolute start-8 -top-3 h-6 w-20 rotate-[4deg] rounded-sm border-2 border-edge bg-raised/80"
                />
                <blockquote
                    lang="en"
                    className="text-lg leading-snug font-semibold"
                >
                    “{testimonial.quote}”
                </blockquote>
                <figcaption
                    lang="en"
                    className="mt-auto flex items-center gap-3"
                >
                    <span
                        aria-hidden
                        className="inline-flex size-10 items-center justify-center rounded-full border-2 border-edge bg-raised font-display text-sm font-black text-ink"
                    >
                        {testimonial.author
                            .split(' ')
                            .map((part) => part[0])
                            .join('')
                            .slice(0, 2)}
                    </span>
                    <span>
                        <span className="block font-bold">
                            {testimonial.author}
                        </span>
                        <span className="block text-sm">
                            {testimonial.role}
                            {testimonial.company &&
                                ` · ${testimonial.company.name}`}
                        </span>
                    </span>
                </figcaption>
            </figure>
        </Reveal>
    );
}

export function ClientsWall({
    companies,
    testimonials,
}: {
    companies: Company[];
    testimonials: TestimonialEntry[];
}) {
    const { t } = useTranslation();
    const p = usePlaygroundCopy();

    return (
        <section
            id="clients"
            aria-labelledby="clients-title"
            className="pg-shell scroll-mt-8 py-16"
        >
            <SectionHeading
                id="clients"
                kicker={p('clients.kicker')}
                title={p('clients.title')}
                pop="purple"
                aside={
                    <p className="max-w-sm text-base text-ink-muted">
                        {p('clients.intro')}
                    </p>
                }
            />
            <ul
                aria-label={t('clients.listLabel')}
                className="pg-card grid grid-cols-1 gap-[2px] overflow-hidden bg-edge sm:grid-cols-2 lg:grid-cols-4"
            >
                {companies.map((company, index) => (
                    <CompanyCell
                        key={company.id}
                        company={company}
                        index={index}
                    />
                ))}
            </ul>

            <h3 className="pg-label mt-16 mb-8 text-ink">
                {p('clients.notes')}
            </h3>
            <ul className="grid gap-8 md:grid-cols-3">
                {testimonials.map((testimonial, index) => (
                    <NoteCard
                        key={testimonial.id}
                        testimonial={testimonial}
                        index={index}
                    />
                ))}
            </ul>
        </section>
    );
}
