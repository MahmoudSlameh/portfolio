import type { ProjectCategory } from '@/types/content';

export type Accent = 'electric' | 'aqua' | 'coral';

export const categoryAccent: Record<ProjectCategory, Accent> = {
    platform: 'electric',
    product: 'coral',
    'open-source': 'aqua',
    'design-system': 'electric',
};

const ACCENT_CYCLE: Accent[] = ['electric', 'aqua', 'coral'];

export const accentText: Record<Accent, string> = {
    electric: 'text-electric',
    aqua: 'text-aqua',
    coral: 'text-coral',
};

export const accentPill: Record<Accent, string> = {
    electric: 'bg-electric/12 text-electric ring-1 ring-electric/25',
    aqua: 'bg-aqua/12 text-aqua ring-1 ring-aqua/25',
    coral: 'bg-coral/12 text-coral ring-1 ring-coral/25',
};

export const accentFill: Record<Accent, string> = {
    electric: 'bg-electric',
    aqua: 'bg-aqua',
    coral: 'bg-coral',
};

export const accentTint: Record<Accent, string> = {
    electric: 'tint tint-electric',
    aqua: 'tint tint-aqua',
    coral: 'tint tint-coral',
};

export const accentForIndex = (index: number): Accent =>
    ACCENT_CYCLE[Math.abs(index) % ACCENT_CYCLE.length];

export const accentForLabel = (label: string): Accent =>
    accentForIndex(Number.parseInt(label, 10) || 0);

export const splitLastWord = (text: string): { lead: string; last: string } => {
    const trimmed = text.trim();
    const breakAt = trimmed.lastIndexOf(' ');
    if (breakAt === -1) return { lead: '', last: trimmed };
    return {
        lead: trimmed.slice(0, breakAt + 1),
        last: trimmed.slice(breakAt + 1),
    };
};
