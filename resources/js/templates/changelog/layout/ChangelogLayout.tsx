import { useTranslation } from '@/hooks/useTranslation';
import { CommandPalette } from '@/shared/command/CommandPalette';
import { Footer } from '@/templates/changelog/components/layout/Footer';
import { Header } from '@/templates/changelog/components/layout/Header';
import type { LayoutProps } from '@/templates/types';

export function ChangelogLayout({
    profile,
    socials,
    searchIndex,
    children,
}: LayoutProps) {
    const { t } = useTranslation();

    return (
        <>
            <a
                href="#main"
                className="fixed start-4 top-3 z-[60] -translate-y-20 rounded-[4px] bg-ink px-4 py-2 text-sm font-medium text-paper transition-transform focus-visible:translate-y-0"
            >
                {t('nav.skip')}
            </a>
            <Header profile={profile} socials={socials} />
            <main
                id="main"
                tabIndex={-1}
                className="min-h-[60vh] overflow-x-clip focus-visible:outline-none"
            >
                {children}
            </main>
            <Footer profile={profile} socials={socials} />
            <CommandPalette searchIndex={searchIndex} email={profile.email} />
        </>
    );
}
