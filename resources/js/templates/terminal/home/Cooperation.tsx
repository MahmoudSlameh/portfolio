import { CompanyLogo } from '@/shared/ui/CompanyLogo';
import { Mail, MapPin, Radio } from 'lucide-react';
import type { CareerEntry } from '@/lib/content';
import { cn, formatMonth, imageSources } from '@/lib/utils';
import type { Company, Profile, WordmarkStyle } from '@/types/content';
import { useTerminalCopy } from '../copy';
import { GlowCard } from '../components/GlowCard';
import { Kicker } from '../components/Kicker';
import { Marquee } from '../components/Marquee';
import { Orbit } from '../components/Orbit';
import { Timeline } from '../components/Timeline';

const wordmarkClass: Record<WordmarkStyle, string> = {
    serif: 'font-serif text-2xl font-semibold',
    'serif-italic': 'font-serif text-2xl italic',
    mono: 'font-mono text-xl font-medium',
    'sans-bold': 'font-[system-ui] text-xl font-extrabold tracking-tight',
    'sans-light': 'font-[system-ui] text-2xl font-light',
    spaced: 'font-[system-ui] text-base font-bold tracking-[0.25em] uppercase',
};

function CompanyTicker({ companies }: { companies: Company[] }) {
    const c = useTerminalCopy();
    return (
        <div className="border-tm-border my-10 rounded-md border p-4">
            <Marquee label={c('experience.companies')} duration={28}>
                {companies.map((company) => (
                    <span
                        key={company.id}
                        role="listitem"
                        className="mx-8 shrink-0"
                    >
                        <a
                            href={company.url ?? undefined}
                            target="_blank"
                            rel="noreferrer"
                            lang="en"
                            className={cn(
                                'hover:text-tm-primary inline-flex whitespace-nowrap text-[#607b96] transition-colors',
                                wordmarkClass[company.wordmark],
                            )}
                        >
                            <CompanyLogo
                                company={company}
                                fallback={company.name}
                                className="h-7 opacity-80 grayscale transition hover:opacity-100 hover:grayscale-0"
                            />
                        </a>
                    </span>
                ))}
            </Marquee>
        </div>
    );
}

function Avatar({ profile }: { profile: Profile }) {
    const { src } = imageSources(profile.portrait);
    return (
        <div
            aria-hidden
            className="border-tm-mint relative flex size-[124px] shrink-0 items-center justify-center rounded-full border"
        >
            <div className="border-tm-mint flex size-[82px] items-center justify-center rounded-full border">
                <div className="relative size-10">
                    <img
                        src={src}
                        alt=""
                        className="size-10 rounded-full object-cover object-top"
                    />
                    <span className="ring-tm-card absolute -end-0.5 bottom-0 size-2 rounded-full bg-[#a8ff53] ring-2" />
                </div>
            </div>
        </div>
    );
}

export function Cooperation({
    profile,
    companies,
    career,
}: {
    profile: Profile;
    companies: Company[];
    career: CareerEntry[];
}) {
    const c = useTerminalCopy();
    const contacts = [
        {
            id: 'email',
            icon: Mail,
            label: c('coop.email'),
            value: profile.email,
            href: `mailto:${profile.email}`,
            ltr: true,
        },
        {
            id: 'based',
            icon: MapPin,
            label: c('coop.based'),
            value: profile.location,
        },
        {
            id: 'status',
            icon: Radio,
            label: c('coop.status'),
            value: profile.availability.label,
            href: '#contact',
        },
    ];

    return (
        <div className="tm-container grid grid-cols-1 gap-8 pt-8 lg:grid-cols-[minmax(0,8fr)_minmax(0,4fr)] lg:gap-6">
            <GlowCard id="clients" aria-labelledby="clients-title" as="section">
                <div className="relative p-4 md:p-10 lg:p-16">
                    <Kicker>{c('coop.kicker')}</Kicker>
                    <h2
                        id="clients-title"
                        className="mt-1 mb-0 text-[clamp(1.5rem,3vw,2.1875rem)] font-medium"
                    >
                        {c('coop.titleA', { count: companies.length })}{' '}
                        <span className="text-tm-300">{c('coop.titleB')}</span>
                        <br />
                        {c('coop.titleC')}{' '}
                        <span className="text-tm-300">{c('coop.titleD')}</span>
                    </h2>

                    <CompanyTicker companies={companies} />

                    <div className="flex flex-col items-center gap-4 md:flex-row">
                        <Avatar profile={profile} />
                        <ul className="flex flex-col gap-2">
                            {contacts.map((contact) => {
                                const Icon = contact.icon;
                                const body = (
                                    <>
                                        <Icon
                                            aria-hidden
                                            className="text-ink size-5 shrink-0"
                                        />
                                        <span className="text-tm-300">
                                            [{contact.label}]{' '}
                                            <span
                                                className={cn(
                                                    'text-tm-secondary',
                                                    contact.ltr &&
                                                        'ltr-isolate',
                                                )}
                                            >
                                                {contact.value}
                                            </span>
                                        </span>
                                    </>
                                );
                                return (
                                    <li key={contact.id}>
                                        {contact.href ? (
                                            <a
                                                href={contact.href}
                                                className="flex items-center gap-1.5 hover:[&_span_span]:text-[#62a92b]"
                                            >
                                                {body}
                                            </a>
                                        ) : (
                                            <span className="flex items-center gap-1.5">
                                                {body}
                                            </span>
                                        )}
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                </div>
                <Orbit className="-end-[35px] -bottom-[35px]" />
            </GlowCard>

            <section
                aria-labelledby="journal-title"
                className="tm-box relative h-full overflow-hidden p-4 md:p-10"
            >
                <Kicker>
                    <span id="journal-title">{c('journal.kicker')}</span>
                </Kicker>
                <Timeline
                    spread
                    className="mt-4 min-h-[380px]"
                    items={career.slice(0, 5).map((entry) => ({
                        id: entry.id,
                        date: formatMonth(entry.start),
                        body: (
                            <span lang="en" className="break-words">
                                {entry.message}
                            </span>
                        ),
                    }))}
                />
            </section>
        </div>
    );
}
