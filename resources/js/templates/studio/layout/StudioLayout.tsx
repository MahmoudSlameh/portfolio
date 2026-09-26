import { usePage } from '@inertiajs/react';
import { Moon, Search, Sun } from 'lucide-react';
import type { ReactNode } from 'react';
import { primaryNav } from '@/config/navigation';
import {
    BrandIcon,
    cn,
    CommandPalette,
    Link,
    useCommandPalette,
    useTheme,
    useTranslation,
} from '@/kit';
import type { LayoutProps } from '@/templates/types';
import type { Profile, Social } from '@/types/content';
import type { SharedProps, ToggleablePage } from '@/types/shared';
import { useCopy, useStudioSpec } from '../useSpec';

type NavItem = (typeof primaryNav)[number];

/** Nav items, without the pages switched off in the panel. */
function useNav(): NavItem[] {
    const { site } = usePage<SharedProps>().props;

    return primaryNav.filter((item) => {
        const page = item.to.slice(1);
        return (
            !(page in site.enabledPages) ||
            site.enabledPages[page as ToggleablePage]
        );
    });
}

function NavLinks({
    items,
    vertical = false,
}: {
    items: NavItem[];
    vertical?: boolean;
}) {
    const { t } = useTranslation();

    return (
        <ul
            className={cn(
                'st-nav flex gap-x-5 gap-y-2 text-sm',
                vertical
                    ? 'flex-col text-base'
                    : 'items-center overflow-x-auto whitespace-nowrap',
            )}
        >
            {items.map((item) => (
                <li key={item.to}>
                    <Link
                        to={item.to}
                        className="text-ink-muted transition-colors hover:text-ink"
                        activeProps={{ className: 'text-ink font-bold' }}
                    >
                        {t(item.key)}
                    </Link>
                </li>
            ))}
        </ul>
    );
}

function Tools() {
    const { t } = useTranslation();
    const { theme, toggleTheme } = useTheme();
    const { openPalette } = useCommandPalette();

    return (
        <div className="st-tools flex items-center gap-1">
            <button
                type="button"
                onClick={openPalette}
                aria-label={t('palette.open')}
                className="grid size-9 place-items-center rounded-[var(--st-radius)] text-ink-muted hover:text-ink"
            >
                <Search aria-hidden className="size-4" />
            </button>
            <button
                type="button"
                onClick={toggleTheme}
                aria-label={t(
                    theme === 'dark' ? 'theme.toLight' : 'theme.toDark',
                )}
                className="grid size-9 place-items-center rounded-[var(--st-radius)] text-ink-muted hover:text-ink"
            >
                {theme === 'dark' ? (
                    <Sun aria-hidden className="size-4" />
                ) : (
                    <Moon aria-hidden className="size-4" />
                )}
            </button>
        </div>
    );
}

function Socials({ socials }: { socials: Social[] }) {
    return (
        <ul className="st-socials flex flex-wrap items-center gap-3">
            {socials.map((social) => (
                <li key={social.id}>
                    <a
                        href={social.url}
                        target="_blank"
                        rel="me noopener"
                        aria-label={social.label}
                        className="text-ink-muted transition-colors hover:text-signal"
                    >
                        <BrandIcon icon={social.icon} className="size-4" />
                    </a>
                </li>
            ))}
        </ul>
    );
}

function Brand({ profile }: { profile: Profile }) {
    return (
        <Link to="/" className="st-brand font-bold text-ink">
            {profile.name}
        </Link>
    );
}

function Header({ profile, socials }: { profile: Profile; socials: Social[] }) {
    const { layout } = useStudioSpec();
    const nav = useNav();

    switch (layout.header.variant) {
        case 'floating-pill':
            return (
                <header className="st-header st-header--floating-pill sticky top-3 z-40 px-3">
                    <div className="mx-auto flex max-w-[var(--st-container)] items-center justify-between gap-4 rounded-full border border-line bg-[color-mix(in_srgb,var(--surface)_85%,transparent)] px-5 py-2 shadow-[var(--st-shadow)] backdrop-blur">
                        <Brand profile={profile} />
                        <NavLinks items={nav} />
                        <Tools />
                    </div>
                </header>
            );
        case 'sidebar':
            return (
                <header className="st-header st-header--sidebar border-b border-line lg:fixed lg:inset-y-0 lg:start-0 lg:z-40 lg:w-64 lg:border-e lg:border-b-0">
                    <div className="flex items-center justify-between gap-4 p-4 lg:h-full lg:flex-col lg:items-start lg:justify-start lg:gap-8 lg:p-8">
                        <div>
                            <Brand profile={profile} />
                            <p className="mt-1 hidden text-sm text-ink-muted lg:block">
                                {profile.role}
                            </p>
                        </div>
                        <div className="hidden lg:block">
                            <NavLinks items={nav} vertical />
                        </div>
                        <div className="flex items-center gap-4 lg:mt-auto lg:flex-col lg:items-start">
                            <div className="hidden lg:block">
                                <Socials socials={socials} />
                            </div>
                            <Tools />
                        </div>
                    </div>
                    <div className="px-4 pb-3 lg:hidden">
                        <NavLinks items={nav} />
                    </div>
                </header>
            );
        case 'minimal':
            return (
                <header className="st-header st-header--minimal st-container flex flex-wrap items-center justify-between gap-4 py-6">
                    <Brand profile={profile} />
                    <div className="flex items-center gap-3">
                        <NavLinks items={nav} />
                        <Tools />
                    </div>
                </header>
            );
        case 'bar-sticky':
            return (
                <header className="st-header st-header--bar-sticky sticky top-0 z-40 border-b border-line bg-[color-mix(in_srgb,var(--paper)_88%,transparent)] backdrop-blur">
                    <div className="st-container flex h-[var(--header-height)] items-center justify-between gap-6">
                        <Brand profile={profile} />
                        <div className="flex min-w-0 items-center gap-3">
                            <NavLinks items={nav} />
                            <Tools />
                        </div>
                    </div>
                </header>
            );
    }
}

function Footer({ profile, socials }: { profile: Profile; socials: Social[] }) {
    const { layout } = useStudioSpec();
    const copy = useCopy();
    const nav = useNav();
    const note = copy(
        'footerNote',
        `© ${new Date().getFullYear()} ${profile.name}`,
    );

    switch (layout.footer.variant) {
        case 'columns':
            return (
                <footer className="st-footer st-footer--columns mt-[calc(var(--st-section-gap)/2)] border-t border-line py-12">
                    <div className="st-container grid gap-10 sm:grid-cols-3">
                        <div>
                            <p className="font-bold text-ink">{profile.name}</p>
                            <p className="mt-2 text-sm text-ink-muted">
                                {profile.headline}
                            </p>
                        </div>
                        <NavLinks items={nav} vertical />
                        <div className="flex flex-col gap-4">
                            <a
                                href={`mailto:${profile.email}`}
                                className="st-link"
                            >
                                {profile.email}
                            </a>
                            <Socials socials={socials} />
                        </div>
                    </div>
                    <p className="st-container mt-10 text-sm text-ink-subtle">
                        {note}
                    </p>
                </footer>
            );
        case 'big-name':
            return (
                <footer className="st-footer st-footer--big-name mt-[calc(var(--st-section-gap)/2)] overflow-hidden border-t border-line pt-12 pb-8">
                    <div className="st-container">
                        <p
                            aria-hidden
                            className="font-[family-name:var(--tpl-font-display)] text-[clamp(3rem,13vw,10rem)] leading-[0.85] font-bold tracking-tight text-ink"
                        >
                            {profile.name}
                        </p>
                        <div className="mt-8 flex flex-wrap items-center justify-between gap-4 text-sm text-ink-muted">
                            <p>{note}</p>
                            <Socials socials={socials} />
                        </div>
                    </div>
                </footer>
            );
        case 'minimal':
            return (
                <footer className="st-footer st-footer--minimal mt-[calc(var(--st-section-gap)/2)] border-t border-line py-8">
                    <div className="st-container flex flex-wrap items-center justify-between gap-4 text-sm text-ink-muted">
                        <p>{note}</p>
                        <Socials socials={socials} />
                    </div>
                </footer>
            );
    }
}

/**
 * The studio layout: header and footer variants from `spec.layout`, the command palette, and a
 * main area offset for the sidebar header.
 */
export function StudioLayout({
    profile,
    socials,
    searchIndex,
    children,
}: LayoutProps): ReactNode {
    const { t } = useTranslation();
    const { layout } = useStudioSpec();
    const sidebar = layout.header.variant === 'sidebar';

    return (
        <div className="st-root min-h-dvh bg-paper text-ink">
            <a
                href="#main"
                className="fixed start-4 top-3 z-50 -translate-y-20 rounded-[var(--st-radius)] bg-ink px-3 py-2 text-sm text-paper focus-visible:translate-y-0"
            >
                {t('nav.skip')}
            </a>
            <Header profile={profile} socials={socials} />
            <div className={cn(sidebar && 'lg:ps-64')}>
                <main
                    id="main"
                    tabIndex={-1}
                    className="st-main focus-visible:outline-none"
                >
                    {children}
                </main>
                <Footer profile={profile} socials={socials} />
            </div>
            <CommandPalette searchIndex={searchIndex} email={profile.email} />
        </div>
    );
}
