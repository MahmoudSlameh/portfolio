import { Link, useRouterState } from '@tanstack/react-router';
import { Command, X } from 'lucide-react';
import { useEffect, useRef } from 'react';
import { LanguageToggle, ThemeToggle } from '@/templates/changelog/components/layout/PreferenceToggles';
import { BrandIcon } from '@/shared/ui/BrandIcon';
import { IconButton } from '@/templates/changelog/components/ui/IconButton';
import { homeSections, primaryNav } from '@/config/navigation';
import { useTranslation } from '@/hooks/useTranslation';
import { useCommandPalette } from '@/providers/CommandPaletteProvider';
import type { Profile, Social } from '@/types/content';

interface MobileNavProps {
  open: boolean;
  onClose: () => void;
  profile: Profile;
  socials: Social[];
}

export function MobileNav({ open, onClose, profile, socials }: MobileNavProps) {
  const dialogRef = useRef<HTMLDialogElement>(null);
  const { t } = useTranslation();
  const { openPalette } = useCommandPalette();
  const locationKey = useRouterState({ select: (state) => state.location.href });

  useEffect(() => {
    const dialog = dialogRef.current;
    if (!dialog) return;
    if (open && !dialog.open) {
      dialog.showModal();
      document.documentElement.style.overflow = 'hidden';
    }
    if (!open && dialog.open) dialog.close();
  }, [open]);

  const onCloseRef = useRef(onClose);
  onCloseRef.current = onClose;

  useEffect(() => {
    onCloseRef.current();
  }, [locationKey]);

  const handleDialogClose = (): void => {
    document.documentElement.style.overflow = '';
    onClose();
  };

  const handleOpenPalette = (): void => {
    onClose();
    openPalette();
  };

  return (
    <dialog
      id="mobile-nav"
      ref={dialogRef}
      aria-label={t('nav.primary')}
      onClose={handleDialogClose}
      className="dialog-panel m-0 h-dvh max-h-none w-screen max-w-none bg-paper p-0 text-ink backdrop:bg-transparent lg:hidden"
    >
      <div className="shell flex h-full flex-col overflow-y-auto">
        <div className="flex h-[var(--header-height)] shrink-0 items-center justify-between border-b border-line">
          <span className="ltr-isolate font-mono text-xs text-ink-subtle">~/{profile.initials.toLowerCase()} · {profile.currentVersion}</span>
          <IconButton label={t('nav.close')} onClick={onClose} showTooltip={false} autoFocus>
            <X aria-hidden className="size-5" strokeWidth={1.75} />
          </IconButton>
        </div>

        <nav aria-label={t('nav.primary')} className="py-8">
          <ul className="flex flex-col">
            <li>
              <Link
                to="/"
                className="flex items-baseline justify-between border-b border-line py-3 font-display text-4xl text-ink data-[status=active]:text-signal-ink"
                activeOptions={{ exact: true, includeHash: false }}
              >
                {t('nav.home')}
                <span className="ltr-isolate font-mono text-xs text-ink-subtle">/</span>
              </Link>
            </li>
            {primaryNav.map((item) => (
              <li key={item.to}>
                <Link
                  to={item.to}
                  className="flex items-baseline justify-between border-b border-line py-3 font-display text-4xl text-ink data-[status=active]:text-signal-ink"
                >
                  {t(item.key)}
                  <span className="ltr-isolate font-mono text-xs text-ink-subtle">{item.to}</span>
                </Link>
              </li>
            ))}
          </ul>
        </nav>

        <div className="pb-8">
          <p className="eyebrow mb-3 text-ink-subtle">{t('palette.group.sections')}</p>
          <ul className="grid grid-cols-2 gap-x-4 gap-y-1">
            {homeSections.map((section) => (
              <li key={section.id}>
                <Link
                  to="/"
                  hash={section.id}
                  onClick={onClose}
                  className="flex items-baseline gap-2 py-1.5 text-[0.9375rem] text-ink-muted hover:text-ink"
                >
                  <span className="ltr-isolate font-mono text-[0.6875rem] text-ink-subtle">{section.index}</span>
                  {t(section.key)}
                </Link>
              </li>
            ))}
          </ul>
        </div>

        <div className="mt-auto flex flex-wrap items-center justify-between gap-4 border-t border-line py-5">
          <div className="flex items-center gap-1">
            <ThemeToggle />
            <LanguageToggle />
            <IconButton label={t('palette.open')} onClick={handleOpenPalette} showTooltip={false}>
              <Command aria-hidden className="size-[1.05rem]" strokeWidth={1.75} />
            </IconButton>
          </div>
          <ul className="flex items-center gap-1">
            {socials.map((social) => (
              <li key={social.id}>
                <a
                  href={social.url}
                  target="_blank"
                  rel="noreferrer"
                  aria-label={`${social.label} ${t('common.opensNewTab')}`}
                  className="inline-flex size-9 items-center justify-center rounded-[4px] text-ink-muted hover:bg-surface hover:text-ink"
                >
                  <BrandIcon icon={social.icon} className="size-4" />
                </a>
              </li>
            ))}
          </ul>
        </div>
      </div>
    </dialog>
  );
}
