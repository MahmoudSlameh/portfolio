import { Link } from '@/lib/router';
import {
    BookOpen,
    House,
    Keyboard,
    PenLine,
    Radio,
    Shapes,
    type LucideIcon,
} from 'lucide-react';
import type { DictionaryKey } from '@/i18n/dictionary';
import { useTranslation } from '@/hooks/useTranslation';
import { cn } from '@/lib/utils';
import { usePlaygroundCopy } from '../copy';

interface DockItem {
    to: '/' | '/projects' | '/writing' | '/books' | '/uses' | '/now';
    key: DictionaryKey;
    icon: LucideIcon;
    activeClass: string;
}

const dockItems: DockItem[] = [
    { to: '/', key: 'nav.home', icon: House, activeClass: 'bg-pop-yellow' },
    {
        to: '/projects',
        key: 'nav.work',
        icon: Shapes,
        activeClass: 'bg-pop-blue',
    },
    {
        to: '/writing',
        key: 'nav.writing',
        icon: PenLine,
        activeClass: 'bg-pop-pink',
    },
    {
        to: '/books',
        key: 'nav.books',
        icon: BookOpen,
        activeClass: 'bg-pop-green',
    },
    {
        to: '/uses',
        key: 'nav.uses',
        icon: Keyboard,
        activeClass: 'bg-pop-purple',
    },
    { to: '/now', key: 'nav.now', icon: Radio, activeClass: 'bg-pop-red' },
];

export function Dock() {
    const { t } = useTranslation();
    const p = usePlaygroundCopy();

    return (
        <nav
            aria-label={p('dock.label')}
            className="fixed inset-x-0 bottom-[max(0.75rem,env(safe-area-inset-bottom))] z-50 flex justify-center px-3"
        >
            <ul className="pg-card bg-raised flex items-center gap-1 rounded-full p-1.5">
                {dockItems.map((item) => {
                    const Icon = item.icon;
                    return (
                        <li key={item.to}>
                            <Link
                                to={item.to}
                                activeOptions={{
                                    exact: item.to === '/',
                                    includeSearch: false,
                                }}
                                className="group text-ink aria-[current=page]:border-edge aria-[current=page]:text-on-pop flex min-h-12 min-w-12 flex-col items-center justify-center gap-0.5 rounded-full border-2 border-transparent px-2.5 transition-[background-color,transform] duration-200 hover:-translate-y-1 sm:flex-row sm:gap-2 sm:px-4"
                                activeProps={{
                                    className: cn(item.activeClass),
                                }}
                            >
                                <Icon
                                    aria-hidden
                                    className="size-5 shrink-0 transition-transform group-hover:rotate-[-8deg]"
                                    strokeWidth={2.25}
                                />
                                <span className="text-[0.625rem] leading-none font-bold sm:text-sm">
                                    {t(item.key)}
                                </span>
                            </Link>
                        </li>
                    );
                })}
            </ul>
        </nav>
    );
}
