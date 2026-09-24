import { Moon, Sun } from 'lucide-react';
import { IconButton, Tooltip } from '@/templates/changelog/components/ui/IconButton';
import { useTranslation } from '@/hooks/useTranslation';
import { usePreferences } from '@/providers/PreferencesProvider';
import { cn } from '@/lib/utils';

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

export function LanguageToggle({ className }: { className?: string }) {
  const { toggleLocale } = usePreferences();
  const { t, locale } = useTranslation();

  return (
    <Tooltip label={t('lang.label')}>
      <button
        type="button"
        onClick={toggleLocale}
        aria-label={t('lang.label')}
        lang={locale === 'ar' ? 'en' : 'ar'}
        className={cn(
          'inline-flex h-9 min-w-9 items-center justify-center rounded-[4px] px-2 text-[0.8125rem] font-medium text-ink-muted transition-colors duration-200 hover:bg-surface hover:text-ink',
          className,
        )}
      >
        {locale === 'ar' ? 'EN' : 'ع'}
      </button>
    </Tooltip>
  );
}
