import { Link } from '@tanstack/react-router';
import { Moon, Search, Sun } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';
import { useCommandPalette } from '@/providers/CommandPaletteProvider';
import { usePreferences } from '@/providers/PreferencesProvider';
import type { Profile } from '@/types/content';
import { usePlaygroundCopy } from '../copy';
import { popButtonClasses } from '../components/PopButton';

export function TopBar({ profile }: { profile: Profile }) {
  const { t, l } = useTranslation();
  const p = usePlaygroundCopy();
  const { theme, toggleTheme, toggleLocale } = usePreferences();
  const { openPalette } = useCommandPalette();
  const ThemeIcon = theme === 'dark' ? Sun : Moon;

  return (
    <header className="pg-shell flex h-[var(--header-height)] items-center justify-between gap-3">
      <Link
        to="/"
        aria-label={t('nav.homeLabel', { name: l(profile.name) })}
        className="group inline-flex items-center gap-3"
      >
        <span className="pg-press pg-press-target pg-wobble inline-flex size-11 items-center justify-center rounded-full border-2 border-edge bg-pop-yellow font-display text-sm font-black text-on-pop shadow-[var(--pg-shadow)] [font-stretch:125%]">
          {profile.initials}
        </span>
        <span className="hidden font-display text-lg leading-none font-extrabold text-ink [font-stretch:115%] sm:block">
          {l(profile.name)}
        </span>
      </Link>

      <div className="flex items-center gap-2 sm:gap-3">
        <button
          type="button"
          onClick={openPalette}
          aria-label={t('palette.open')}
          className={popButtonClasses({ tone: 'plain', size: 'sm', className: 'gap-2' })}
        >
          <Search aria-hidden className="size-4" strokeWidth={2.5} />
          <span className="hidden md:inline">{p('top.search')}</span>
          <kbd className="ltr-isolate hidden rounded-md border-2 border-edge px-1.5 font-mono text-[0.625rem] leading-5 lg:inline">⌘K</kbd>
        </button>
        <button
          type="button"
          onClick={toggleLocale}
          aria-label={t('lang.label')}
          className={popButtonClasses({ tone: 'pink', size: 'sm' })}
        >
          {t('lang.switch')}
        </button>
        <button
          type="button"
          onClick={toggleTheme}
          aria-label={t(theme === 'dark' ? 'theme.toLight' : 'theme.toDark')}
          className={popButtonClasses({ tone: 'ink', size: 'icon' })}
        >
          <ThemeIcon aria-hidden className="size-5" strokeWidth={2.25} />
        </button>
        <Link to="/" hash="contact" className={popButtonClasses({ tone: 'green', size: 'sm', className: 'hidden sm:inline-flex' })}>
          {p('top.sayHi')}
        </Link>
      </div>
    </header>
  );
}
