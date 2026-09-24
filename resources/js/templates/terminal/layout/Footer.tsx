import { Link, useRouterState } from '@/lib/router';
import { primaryNav } from '@/config/navigation';
import { useTranslation } from '@/hooks/useTranslation';
import { BrandIcon } from '@/shared/ui/BrandIcon';
import type { Profile, Social } from '@/types/content';
import { useTerminalCopy } from '../copy';
import { navSections } from '../lib/sections';
import { Wordmark } from './Header';

export function Footer({
    profile,
    socials,
}: {
    profile: Profile;
    socials: Social[];
}) {
    const { t } = useTranslation();
    const c = useTerminalCopy();
    const isHome = useRouterState({
        select: (state) => state.location.pathname === '/',
    });
    const linkClass =
        'text-ink opacity-75 transition-opacity hover:opacity-100';

    return (
        <footer className="tm-container">
            <div className="border-t border-tm-border pt-6 pb-4 text-center">
                <Link to="/" className="mb-4 inline-flex">
                    <Wordmark
                        profile={profile}
                        className="flex items-center justify-center gap-2"
                        textClassName="tm-footer-logo text-2xl font-medium"
                    />
                    <span className="sr-only">{t('nav.homeSuffix')}</span>
                </Link>
                <ul
                    aria-label={t('hero.socialLabel')}
                    className="flex justify-center gap-4 text-ink"
                >
                    {socials.map((social) => (
                        <li key={social.id}>
                            <a
                                href={social.url}
                                target="_blank"
                                rel="noreferrer"
                                aria-label={`${social.label} ${t('common.opensNewTab')}`}
                                className="inline-flex transition-colors hover:text-tm-primary"
                            >
                                <BrandIcon
                                    icon={social.icon}
                                    className="size-[18px]"
                                />
                            </a>
                        </li>
                    ))}
                </ul>
                <nav aria-label={t('nav.footer')}>
                    <ul className="my-6 flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
                        {navSections.map((section) => (
                            <li key={section.id}>
                                {isHome ? (
                                    <a
                                        href={`#${section.id}`}
                                        className={linkClass}
                                    >
                                        {c(section.key)}
                                    </a>
                                ) : (
                                    <Link
                                        to="/"
                                        hash={section.id}
                                        className={linkClass}
                                    >
                                        {c(section.key)}
                                    </Link>
                                )}
                            </li>
                        ))}
                    </ul>
                    <ul className="mb-4 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-sm">
                        {primaryNav.map((item) => (
                            <li key={item.to}>
                                <Link
                                    to={item.to}
                                    className="text-tm-400 transition-colors hover:text-tm-primary"
                                >
                                    {t(item.key)}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </nav>
                <p className="mb-0 text-xs text-tm-400">
                    <span className="ltr-isolate">
                        © {new Date().getFullYear()}
                    </span>{' '}
                    {profile.name} · {c('footer.madeWith')}
                </p>
            </div>
        </footer>
    );
}
