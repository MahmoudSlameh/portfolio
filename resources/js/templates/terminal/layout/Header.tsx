import { Link, useRouterState } from '@/lib/router';
import { AlignLeft, Menu, Moon, Search, Sun } from 'lucide-react';
import { useState } from 'react';
import { primaryNav } from '@/config/navigation';
import { useNow } from '@/hooks/useLocalTime';
import { useTranslation } from '@/hooks/useTranslation';
import { formatTime } from '@/lib/utils';
import { useCommandPalette } from '@/providers/CommandPaletteProvider';
import { usePreferences } from '@/providers/PreferencesProvider';
import { BrandIcon } from '@/shared/ui/BrandIcon';
import type { Profile, Social } from '@/types/content';
import { useTerminalCopy } from '../copy';
import { CodeMark } from '../components/CodeMarks';
import { Drawer } from '../components/Drawer';
import { navSections } from '../lib/sections';
import { siteWordmark } from '../lib/site';
import { useActiveSection } from '../lib/useActiveSection';

export function Wordmark({
    profile,
    className,
    textClassName,
}: {
    profile: Profile;
    className?: string;
    textClassName: string;
}) {
    const { name, tld } = siteWordmark(profile);
    return (
        <span dir="ltr" className={className}>
            <CodeMark />
            <span className={textClassName}>
                {name}
                {tld}
            </span>
        </span>
    );
}

function SocialLinks({
    socials,
    className,
}: {
    socials: Social[];
    className?: string;
}) {
    const { t } = useTranslation();
    return (
        <ul className={className} aria-label={t('hero.socialLabel')}>
            {socials.map((social) => (
                <li key={social.id}>
                    <a
                        href={social.url}
                        target="_blank"
                        rel="noreferrer"
                        aria-label={`${social.label} ${t('common.opensNewTab')}`}
                        className="hover:text-tm-primary inline-flex transition-colors"
                    >
                        <BrandIcon icon={social.icon} className="size-[18px]" />
                    </a>
                </li>
            ))}
        </ul>
    );
}

function SectionLink({
    id,
    label,
    isHome,
    active,
    onNavigate,
    className,
}: {
    id: string;
    label: string;
    isHome: boolean;
    active: boolean;
    onNavigate?: () => void;
    className: string;
}) {
    if (isHome) {
        return (
            <a
                href={`#${id}`}
                aria-current={active ? 'true' : undefined}
                onClick={onNavigate}
                className={className}
            >
                {label}
            </a>
        );
    }
    return (
        <Link to="/" hash={id} onClick={onNavigate} className={className}>
            {label}
        </Link>
    );
}

function ContactPanel({
    profile,
    socials,
    onNavigate,
}: {
    profile: Profile;
    socials: Social[];
    onNavigate: () => void;
}) {
    const c = useTerminalCopy();
    const now = useNow();
    const rows = [
        {
            label: c('menu.email'),
            value: (
                <a
                    href={`mailto:${profile.email}`}
                    className="ltr-isolate hover:text-[#62a92b]"
                >
                    {profile.email}
                </a>
            ),
        },
        { label: c('menu.location'), value: profile.location },
        {
            label: c('menu.localTime'),
            value: (
                <span className="ltr-isolate">
                    {formatTime(now, profile.timezone)} ·{' '}
                    {profile.timezoneLabel}
                </span>
            ),
        },
        { label: c('menu.availability'), value: profile.availability.label },
    ];

    return (
        <>
            <h2 className="mb-5 text-[1.75rem]">{c('menu.title')}</h2>
            <div className="border-t border-[#62a92b] pt-6">
                <p className="text-tm-200 mb-6 font-medium">
                    {profile.availability.note}
                </p>
                <dl className="mb-7 flex flex-col gap-3">
                    {rows.map((row) => (
                        <div key={row.label}>
                            <dt className="text-tm-400 text-lg">{row.label}</dt>
                            <dd className="text-ink mb-0">{row.value}</dd>
                        </div>
                    ))}
                </dl>
                <p className="text-tm-400 mb-2 text-lg">{c('nav.pages')}</p>
                <PagesList onNavigate={onNavigate} className="mb-7" />
                <p className="text-tm-400 mb-2 text-lg">{c('nav.social')}</p>
                <SocialLinks
                    socials={socials}
                    className="text-ink flex gap-3"
                />
            </div>
        </>
    );
}

function PagesList({
    onNavigate,
    className,
}: {
    onNavigate: () => void;
    className?: string;
}) {
    const { t } = useTranslation();
    return (
        <ul className={className}>
            {primaryNav.map((item) => (
                <li key={item.to}>
                    <Link
                        to={item.to}
                        onClick={onNavigate}
                        className="border-tm-border text-ink block border-b py-3 hover:text-[#62a92b] [&.active]:text-[#62a92b]"
                    >
                        {t(item.key)}
                    </Link>
                </li>
            ))}
        </ul>
    );
}

export function Header({
    profile,
    socials,
}: {
    profile: Profile;
    socials: Social[];
}) {
    const { t } = useTranslation();
    const c = useTerminalCopy();
    const { theme, toggleTheme } = usePreferences();
    const { openPalette } = useCommandPalette();
    const pathname = useRouterState({
        select: (state) => state.location.pathname,
    });
    const isHome = pathname === '/';
    const activeId = useActiveSection(
        navSections.map((section) => section.id),
        isHome,
    );
    const [panel, setPanel] = useState<'contact' | 'menu' | null>(null);
    const ThemeIcon = theme === 'dark' ? Sun : Moon;
    const closePanel = (): void => setPanel(null);

    return (
        <header className="absolute inset-x-0 top-0 z-40">
            <div className="tm-container">
                <nav
                    aria-label={t('nav.primary')}
                    className="mt-[22px] flex min-h-20 items-stretch overflow-hidden rounded-lg border border-[var(--tm-nav-border)] bg-[var(--tm-nav-bg)] text-white"
                >
                    <button
                        type="button"
                        onClick={() => setPanel('contact')}
                        aria-label={c('menu.title')}
                        className="hidden w-[76px] shrink-0 items-center justify-center bg-white/5 transition-colors hover:bg-white/10 md:flex"
                    >
                        <AlignLeft
                            aria-hidden
                            className="size-6 rtl:-scale-x-100"
                        />
                    </button>

                    <div className="flex min-w-0 flex-1 items-center justify-between gap-3 px-4 py-3 lg:px-5">
                        <Link
                            to="/"
                            aria-label={t('nav.homeLabel', {
                                name: profile.name,
                            })}
                            className="inline-flex min-w-0 items-center"
                        >
                            <Wordmark
                                profile={profile}
                                className="flex min-w-0 items-center gap-2"
                                textClassName="tm-logo-text truncate text-lg font-medium sm:text-2xl"
                            />
                        </Link>

                        <ul className="hidden items-center xl:flex">
                            {navSections.map((section) => (
                                <li key={section.id}>
                                    <SectionLink
                                        id={section.id}
                                        label={c(section.key)}
                                        isHome={isHome}
                                        active={activeId === section.id}
                                        className="tm-nav-link block rounded px-3 py-2 text-base whitespace-nowrap min-[1400px]:px-4"
                                    />
                                </li>
                            ))}
                        </ul>

                        <div className="flex shrink-0 items-center gap-3">
                            <SocialLinks
                                socials={socials
                                    .filter((social) => social.icon !== 'rss')
                                    .slice(0, 4)}
                                className="hidden items-center gap-3 min-[1400px]:flex md:flex xl:hidden"
                            />
                            <button
                                type="button"
                                onClick={openPalette}
                                aria-label={t('palette.open')}
                                className="hidden size-9 items-center justify-center rounded-md text-white/70 transition-colors hover:text-white sm:inline-flex"
                            >
                                <Search aria-hidden className="size-[18px]" />
                            </button>
                            <button
                                type="button"
                                onClick={() => setPanel('menu')}
                                aria-label={t('nav.menu')}
                                className="inline-flex size-10 items-center justify-center rounded-md border border-white/15 xl:hidden"
                            >
                                <Menu aria-hidden className="size-5" />
                            </button>
                        </div>
                    </div>

                    <button
                        type="button"
                        onClick={toggleTheme}
                        aria-label={t(
                            theme === 'dark' ? 'theme.toLight' : 'theme.toDark',
                        )}
                        className="flex w-16 shrink-0 items-center justify-center bg-white/5 text-[#ffc107] transition-colors hover:bg-white/10 md:w-[76px]"
                    >
                        <ThemeIcon aria-hidden className="size-6" />
                    </button>
                </nav>
            </div>

            <Drawer
                open={panel === 'contact'}
                onClose={closePanel}
                side="start"
                label={c('menu.title')}
            >
                <ContactPanel
                    profile={profile}
                    socials={socials}
                    onNavigate={closePanel}
                />
            </Drawer>

            <Drawer
                open={panel === 'menu'}
                onClose={closePanel}
                side="end"
                label={t('nav.primary')}
            >
                <Link to="/" onClick={closePanel} className="mb-6 inline-flex">
                    <Wordmark
                        profile={profile}
                        className="flex items-center gap-2"
                        textClassName="tm-footer-logo text-2xl font-medium"
                    />
                </Link>
                <ul className="flex flex-col">
                    {navSections.map((section) => (
                        <li key={section.id}>
                            <SectionLink
                                id={section.id}
                                label={c(section.key)}
                                isHome={isHome}
                                active={activeId === section.id}
                                onNavigate={closePanel}
                                className="border-tm-border text-ink block border-b py-3 hover:text-[#62a92b]"
                            />
                        </li>
                    ))}
                </ul>
                <p className="text-tm-400 mt-8 mb-2 text-sm">
                    {c('nav.pages')}
                </p>
                <PagesList onNavigate={closePanel} />
                <div className="mt-8 flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        onClick={() => {
                            closePanel();
                            openPalette();
                        }}
                        className="border-tm-border text-ink inline-flex h-10 items-center gap-2 rounded-md border px-3 text-sm"
                    >
                        <Search aria-hidden className="size-4" />
                        {c('nav.search')}
                    </button>
                </div>
                <SocialLinks
                    socials={socials}
                    className="text-ink mt-8 flex gap-4"
                />
            </Drawer>
        </header>
    );
}
