import { useMemo, type CSSProperties } from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import { CommandPalette } from '@/shared/command/CommandPalette';
import type { LayoutProps } from '@/templates/types';
import { binaryRain } from '../lib/binary';
import { Footer } from './Footer';
import { Header } from './Header';
import { ScrollTop } from './ScrollTop';

function BinaryBackdrop() {
    const pattern = useMemo(() => binaryRain(), []);
    const style = {
        maskImage: pattern,
        WebkitMaskImage: pattern,
    } as CSSProperties;

    return (
        <div
            aria-hidden
            className="tm-binary pointer-events-none absolute inset-x-0 top-0 h-[640px] overflow-hidden"
        >
            <div
                className="size-full bg-[var(--tm-binary)] [mask-position:top_center] [mask-repeat:repeat-x]"
                style={style}
            />
        </div>
    );
}

export function TerminalLayout({
    profile,
    socials,
    searchIndex,
    children,
}: LayoutProps) {
    const { t } = useTranslation();

    return (
        <div className="relative isolate min-h-dvh overflow-x-clip">
            <BinaryBackdrop />
            <a
                href="#main"
                className="fixed start-4 top-3 z-[60] -translate-y-24 rounded-md bg-[#62a92b] px-5 py-2.5 text-sm font-medium text-white transition-transform focus-visible:translate-y-0"
            >
                {t('nav.skip')}
            </a>
            <Header profile={profile} socials={socials} />
            <main
                id="main"
                tabIndex={-1}
                className="relative min-h-[60vh] pb-[60px] focus-visible:outline-none"
            >
                {children}
            </main>
            <Footer profile={profile} socials={socials} />
            <ScrollTop />
            <CommandPalette searchIndex={searchIndex} email={profile.email} />
        </div>
    );
}
