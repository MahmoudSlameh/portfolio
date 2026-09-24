import { Link } from '@/lib/router';
import { Menu } from 'lucide-react';
import { useEffect, useState } from 'react';
import { ThemeToggle } from '@/templates/changelog/components/layout/PreferenceToggles';
import { MobileNav } from '@/templates/changelog/components/layout/MobileNav';
import { buttonClasses } from '@/templates/changelog/components/ui/Button';
import { IconButton } from '@/templates/changelog/components/ui/IconButton';
import { primaryNav } from '@/config/navigation';
import { useTranslation } from '@/hooks/useTranslation';
import { useCommandPalette } from '@/providers/CommandPaletteProvider';
import type { Profile, Social } from '@/types/content';
import { cn } from '@/lib/utils';

interface HeaderProps {
    profile: Profile;
    socials: Social[];
}

const isApplePlatform = (): boolean =>
    /mac|iphone|ipad/i.test(navigator.userAgent);

export function Header({ profile, socials }: HeaderProps) {
    const { t } = useTranslation();
    const { openPalette } = useCommandPalette();
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const [isScrolled, setIsScrolled] = useState(false);
    const shortcutLabel = isApplePlatform() ? '⌘K' : 'Ctrl K';

    useEffect(() => {
        const handleScroll = (): void => setIsScrolled(window.scrollY > 8);
        handleScroll();
        window.addEventListener('scroll', handleScroll, { passive: true });
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    return (
        <header
            className={cn(
                'sticky top-0 z-40 border-b backdrop-blur-md transition-colors duration-300',
                isScrolled
                    ? 'border-line bg-paper/85'
                    : 'border-transparent bg-paper/60',
            )}
        >
            <div className="shell flex h-[var(--header-height)] items-center gap-4">
                <Link
                    to="/"
                    aria-label={t('nav.homeLabel', { name: profile.name })}
                    className="group flex min-w-0 items-center gap-3"
                >
                    <span
                        aria-hidden
                        className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-[linear-gradient(135deg,#7c3aed,#c026d3_55%,#06b6d4)] font-mono text-[0.6875rem] font-bold text-white shadow-[0_8px_20px_-8px_#7c3aed] transition-transform duration-500 group-hover:scale-105 group-hover:rotate-[-8deg]"
                    >
                        {profile.initials}
                    </span>
                    <span className="flex min-w-0 flex-col leading-tight">
                        <span className="truncate text-[0.9375rem] font-semibold text-ink">
                            {profile.name}
                        </span>
                        <span className="ltr-isolate hidden font-mono text-[0.625rem] text-ink-subtle xs:block">
                            changelog {profile.currentVersion}
                        </span>
                    </span>
                </Link>

                <nav
                    aria-label={t('nav.primary')}
                    className="ms-auto hidden lg:block"
                >
                    <ul className="flex items-center gap-1">
                        {primaryNav.map((item) => (
                            <li key={item.to}>
                                <Link
                                    to={item.to}
                                    className="relative inline-flex h-9 items-center rounded-full px-3.5 text-sm text-ink-muted transition-colors duration-200 hover:bg-surface hover:text-ink data-[status=active]:bg-electric/12 data-[status=active]:font-medium data-[status=active]:text-electric"
                                >
                                    {t(item.key)}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </nav>

                <div className="ms-auto flex items-center gap-1 lg:ms-2">
                    <button
                        type="button"
                        onClick={openPalette}
                        aria-label={t('palette.open')}
                        aria-keyshortcuts="Control+K Meta+K"
                        className="hidden h-9 items-center gap-2 rounded-[4px] border border-line px-2.5 text-[0.8125rem] text-ink-subtle transition-colors duration-200 hover:border-line-strong hover:text-ink sm:inline-flex"
                    >
                        <span className="ltr-isolate font-mono text-[0.6875rem]">
                            {shortcutLabel}
                        </span>
                    </button>
                    <ThemeToggle />
                    <Link
                        to="/"
                        hash="contact"
                        className={cn(
                            buttonClasses('primary', 'sm'),
                            'ms-2 hidden md:inline-flex',
                        )}
                    >
                        {t('nav.contact')}
                    </Link>
                    <IconButton
                        label={t('nav.menu')}
                        onClick={() => setIsMenuOpen(true)}
                        aria-expanded={isMenuOpen}
                        aria-controls="mobile-nav"
                        showTooltip={false}
                        className="lg:hidden"
                    >
                        <Menu
                            aria-hidden
                            className="size-5"
                            strokeWidth={1.75}
                        />
                    </IconButton>
                </div>
            </div>
            <MobileNav
                open={isMenuOpen}
                onClose={() => setIsMenuOpen(false)}
                profile={profile}
                socials={socials}
            />
        </header>
    );
}
