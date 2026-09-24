import { createInertiaApp } from '@inertiajs/react';
import { LazyMotion, MotionConfig } from 'motion/react';
import { CommandPaletteProvider } from '@/providers/CommandPaletteProvider';
import { PreferencesProvider } from '@/providers/PreferencesProvider';
import { ToastProvider } from '@/providers/ToastProvider';
import type { SharedProps } from '@/types/shared';

const loadMotionFeatures = () =>
    import('@/lib/motionFeatures').then((module) => module.default);

void createInertiaApp<SharedProps>({
    // Pages render a full <title> through SeoHead; nothing to append here.
    title: (title) => title,
    progress: {
        color: '#6366f1',
    },
    withApp: (app, { page }) => (
        <PreferencesProvider initialTheme={page.props.theme}>
            <ToastProvider>
                <CommandPaletteProvider>
                    <LazyMotion features={loadMotionFeatures} strict>
                        <MotionConfig reducedMotion="user">{app}</MotionConfig>
                    </LazyMotion>
                </CommandPaletteProvider>
            </ToastProvider>
        </PreferencesProvider>
    ),
});
