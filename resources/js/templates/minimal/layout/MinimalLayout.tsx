import { usePage } from '@inertiajs/react';
import { Moon, Search, Sun } from 'lucide-react';
import {
    BrandIcon,
    CommandPalette,
    Link,
    useCommandPalette,
    useTheme,
    useTranslation,
} from '@/kit';
import { primaryNav } from '@/config/navigation';
import type { LayoutProps } from '@/templates/types';
import type { SharedProps, ToggleablePage } from '@/types/shared';

/**
 * The layout wraps every page and persists between navigations (it is attached in
 * resources/js/pages/minimal/*.tsx with `withTemplateLayout`).
 *
 * It receives the shared `profile`, `socials` and the deferred `searchIndex` as props. Anything
 * else shared by the server (site name, enabled pages, template id) is read with `usePage()`.
 */
export function MinimalLayout({
    profile,
    socials,
    searchIndex,
    children,
}: LayoutProps) {
    const { t } = useTranslation();
    const { theme, toggleTheme } = useTheme();
    const { openPalette } = useCommandPalette();
    const { site } = usePage<SharedProps>().props;

    // Writing, Books, Uses and Now can be switched off in the panel (Site → SEO & settings).
    // Their routes return 404 when disabled, so hide their links too.
    const nav = primaryNav.filter((item) => {
        const page = item.to.slice(1);
        return (
            !(page in site.enabledPages) ||
            site.enabledPages[page as ToggleablePage]
        );
    });

    return (
        <>
            <a
                href="#main"
                className="fixed start-4 top-3 z-50 -translate-y-20 rounded bg-ink px-3 py-2 text-sm text-paper focus-visible:translate-y-0"
            >
                {t('nav.skip')}
            </a>

            <header className="border-b border-line">
                <div className="mn-container flex h-16 items-center justify-between gap-4">
                    <Link to="/" className="font-semibold text-ink">
                        {profile.name}
                    </Link>
                    <nav aria-label={t('nav.primary')}>
                        <ul className="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                            {nav.map((item) => (
                                <li key={item.to}>
                                    <Link
                                        to={item.to}
                                        className="text-ink-muted hover:text-ink"
                                        activeProps={{
                                            className: 'text-ink underline',
                                        }}
                                    >
                                        {t(item.key)}
                                    </Link>
                                </li>
                            ))}
                            <li>
                                <button
                                    type="button"
                                    onClick={openPalette}
                                    aria-label={t('palette.open')}
                                    className="grid size-8 place-items-center text-ink-muted hover:text-ink"
                                >
                                    <Search aria-hidden className="size-4" />
                                </button>
                            </li>
                            <li>
                                <button
                                    type="button"
                                    onClick={toggleTheme}
                                    aria-label={t(
                                        theme === 'dark'
                                            ? 'theme.toLight'
                                            : 'theme.toDark',
                                    )}
                                    className="grid size-8 place-items-center text-ink-muted hover:text-ink"
                                >
                                    {theme === 'dark' ? (
                                        <Sun aria-hidden className="size-4" />
                                    ) : (
                                        <Moon aria-hidden className="size-4" />
                                    )}
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </header>

            <main
                id="main"
                tabIndex={-1}
                className="focus-visible:outline-none"
            >
                {children}
            </main>

            <footer className="mt-24 border-t border-line py-10 text-sm text-ink-muted">
                <div className="mn-container flex flex-wrap items-center justify-between gap-4">
                    <p>
                        © {new Date().getFullYear()} {profile.name}
                    </p>
                    <ul
                        aria-label={t('nav.footer')}
                        className="flex items-center gap-3"
                    >
                        {socials.map((social) => (
                            <li key={social.id}>
                                <a
                                    href={social.url}
                                    rel="me noopener"
                                    target="_blank"
                                    aria-label={social.label}
                                    className="hover:text-ink"
                                >
                                    <BrandIcon
                                        icon={social.icon}
                                        className="size-4"
                                    />
                                </a>
                            </li>
                        ))}
                    </ul>
                </div>
            </footer>

            {/* Ctrl/⌘ + K search over pages, projects, articles and books. */}
            <CommandPalette searchIndex={searchIndex} email={profile.email} />
        </>
    );
}
