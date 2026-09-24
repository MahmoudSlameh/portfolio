import { Moon, Sun } from 'lucide-react';
import { IconButton } from '@/templates/changelog/components/ui/IconButton';
import { useTranslation } from '@/hooks/useTranslation';
import { usePreferences } from '@/providers/PreferencesProvider';

export function ThemeToggle({ className }: { className?: string }) {
    const { theme, toggleTheme } = usePreferences();
    const { t } = useTranslation();
    const label = theme === 'dark' ? t('theme.toLight') : t('theme.toDark');
    const Icon = theme === 'dark' ? Sun : Moon;

    return (
        <IconButton label={label} onClick={toggleTheme} className={className}>
            <Icon aria-hidden className="size-[1.05rem]" strokeWidth={1.75} />
        </IconButton>
    );
}
