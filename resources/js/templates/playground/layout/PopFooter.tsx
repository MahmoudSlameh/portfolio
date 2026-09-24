import { ArrowUp } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';
import { BrandIcon } from '@/shared/ui/BrandIcon';
import type { Profile, Social } from '@/types/content';
import { usePlaygroundCopy } from '../copy';
import { popBg, popForIndex } from '../lib/pops';
import { popButtonClasses } from '../components/PopButton';

export function PopFooter({
    profile,
    socials,
}: {
    profile: Profile;
    socials: Social[];
}) {
    const { t } = useTranslation();
    const p = usePlaygroundCopy();
    const name = profile.name;
    const year = new Date().getFullYear();

    const handleBackToTop = (): void => {
        const reduceMotion = window.matchMedia(
            '(prefers-reduced-motion: reduce)',
        ).matches;
        window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
        document.getElementById('main')?.focus({ preventScroll: true });
    };

    return (
        <footer className="mt-24 overflow-hidden border-t-2 border-edge bg-ink pt-14 pb-36 text-paper">
            <div
                aria-hidden
                className="overflow-hidden whitespace-nowrap"
                dir="ltr"
            >
                <div className="pg-marquee pg-marquee-slow">
                    {[0, 1].map((copyIndex) => (
                        <span
                            key={copyIndex}
                            className="pg-display flex shrink-0 items-center gap-8 pe-8 text-[clamp(4rem,14vw,12rem)]"
                        >
                            <span className="pg-outline-text [--pg-outline:var(--paper)]">
                                {name}
                            </span>
                            <span className="text-pop-yellow">✺</span>
                            <span>{name}</span>
                            <span className="text-pop-pink">✺</span>
                        </span>
                    ))}
                </div>
            </div>

            <div className="pg-shell mt-12 grid gap-10 md:grid-cols-[1fr_auto] md:items-end">
                <div className="flex flex-col gap-6">
                    <p className="max-w-md text-lg font-medium">
                        {p('footer.madeWith')}
                    </p>
                    <ul
                        aria-label={t('hero.socialLabel')}
                        className="flex flex-wrap gap-3"
                    >
                        {socials.map((social, index) => (
                            <li key={social.id}>
                                <a
                                    href={social.url}
                                    target="_blank"
                                    rel="noreferrer"
                                    className={popButtonClasses({
                                        tone: popForIndex(index),
                                        size: 'sm',
                                        className: 'border-paper shadow-none',
                                    })}
                                >
                                    <BrandIcon
                                        icon={social.icon}
                                        className="size-4"
                                    />
                                    {social.label}
                                    <span className="sr-only">
                                        {t('common.opensNewTab')}
                                    </span>
                                </a>
                            </li>
                        ))}
                    </ul>
                </div>
                <div className="flex flex-col items-start gap-4 md:items-end">
                    <button
                        type="button"
                        onClick={handleBackToTop}
                        className={popButtonClasses({
                            tone: 'yellow',
                            className: 'border-paper',
                        })}
                    >
                        <ArrowUp
                            aria-hidden
                            className="size-4"
                            strokeWidth={2.5}
                        />
                        {p('footer.top')}
                    </button>
                    <p className="font-mono text-xs text-paper/70">
                        © {year} {name}. {t('footer.rights')}
                    </p>
                    <p className="font-mono text-xs text-paper/70">
                        {p('footer.builtWith')}
                    </p>
                </div>
            </div>

            <div aria-hidden className="pg-shell mt-10 flex gap-2">
                {Array.from({ length: 6 }, (_, index) => (
                    <span
                        key={index}
                        className={`h-3 flex-1 rounded-full ${popBg[popForIndex(index)]}`}
                    />
                ))}
            </div>
        </footer>
    );
}
