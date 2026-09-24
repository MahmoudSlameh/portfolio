import { Link } from '@/lib/router';
import { ArrowUp } from 'lucide-react';
import { BrandIcon } from '@/shared/ui/BrandIcon';
import { primaryNav } from '@/config/navigation';
import { useTranslation } from '@/hooks/useTranslation';
import type { Profile, Social } from '@/types/content';

interface FooterProps {
    profile: Profile;
    socials: Social[];
}

export function Footer({ profile, socials }: FooterProps) {
    const { t } = useTranslation();
    const year = new Date().getFullYear();

    const handleBackToTop = (): void => {
        const prefersReducedMotion = window.matchMedia(
            '(prefers-reduced-motion: reduce)',
        ).matches;
        window.scrollTo({
            top: 0,
            behavior: prefersReducedMotion ? 'auto' : 'smooth',
        });
        document.getElementById('main')?.focus({ preventScroll: true });
    };

    return (
        <footer className="border-line bg-surface border-t">
            <div className="shell editorial-grid gap-y-12 py-16 md:py-20">
                <div className="col-span-4 md:col-span-7">
                    <p className="font-display text-ink text-4xl leading-[1.05] sm:text-5xl">
                        {t('footer.signoff')}
                    </p>
                    <a
                        href={`mailto:${profile.email}`}
                        className="link-draw ltr-isolate text-signal-ink mt-6 inline-block font-mono text-sm"
                    >
                        {profile.email}
                    </a>
                </div>

                <nav
                    aria-label={t('nav.footer')}
                    className="col-span-2 md:col-span-2 md:col-start-9"
                >
                    <ul className="flex flex-col gap-2 text-sm">
                        <li>
                            <Link
                                to="/"
                                className="link-draw text-ink-muted hover:text-ink"
                            >
                                {t('nav.home')}
                            </Link>
                        </li>
                        {primaryNav.map((item) => (
                            <li key={item.to}>
                                <Link
                                    to={item.to}
                                    className="link-draw text-ink-muted hover:text-ink"
                                >
                                    {t(item.key)}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </nav>

                <ul className="col-span-2 flex flex-col gap-2 text-sm md:col-span-2">
                    {socials.map((social) => (
                        <li key={social.id}>
                            <a
                                href={social.url}
                                target="_blank"
                                rel="noreferrer"
                                className="group text-ink-muted hover:text-ink inline-flex items-center gap-2"
                            >
                                <BrandIcon
                                    icon={social.icon}
                                    className="size-3.5"
                                />
                                <span className="link-draw-target">
                                    {social.label}
                                </span>
                                <span className="sr-only">
                                    {t('common.opensNewTab')}
                                </span>
                            </a>
                        </li>
                    ))}
                </ul>

                <div className="border-line text-ink-subtle col-span-4 flex flex-col gap-4 border-t pt-6 text-[0.8125rem] md:col-span-12 md:flex-row md:items-center md:justify-between">
                    <p>
                        © {year} {profile.name}. {t('footer.rights')}{' '}
                        <span className="hidden md:inline">
                            {t('footer.builtWith')}
                        </span>
                    </p>
                    <div className="flex items-center gap-4">
                        <span className="version-label">
                            {profile.currentVersion} · {profile.updatedAt}
                        </span>
                        <button
                            type="button"
                            onClick={handleBackToTop}
                            className="text-ink-muted hover:text-ink inline-flex items-center gap-1.5 font-medium"
                        >
                            <ArrowUp aria-hidden className="size-3.5" />
                            {t('footer.backToTop')}
                        </button>
                    </div>
                </div>
            </div>
        </footer>
    );
}
