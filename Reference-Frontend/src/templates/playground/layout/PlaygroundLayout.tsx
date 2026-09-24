import { useTranslation } from '@/hooks/useTranslation';
import { CommandPalette } from '@/shared/command/CommandPalette';
import type { LayoutProps } from '@/templates/types';
import { Dock } from './Dock';
import { PopFooter } from './PopFooter';
import { TopBar } from './TopBar';

export function PlaygroundLayout({ profile, socials, searchIndex, children }: LayoutProps) {
  const { t } = useTranslation();

  return (
    <>
      <a
        href="#main"
        className="fixed start-4 top-3 z-[60] -translate-y-24 rounded-full border-2 border-edge bg-pop-yellow px-5 py-2.5 text-sm font-bold text-on-pop transition-transform focus-visible:translate-y-0"
      >
        {t('nav.skip')}
      </a>
      <TopBar profile={profile} />
      <main id="main" tabIndex={-1} className="min-h-[60vh] overflow-x-clip focus-visible:outline-none">
        {children}
      </main>
      <PopFooter profile={profile} socials={socials} />
      <Dock />
      <CommandPalette searchIndex={searchIndex} email={profile.email} />
    </>
  );
}
