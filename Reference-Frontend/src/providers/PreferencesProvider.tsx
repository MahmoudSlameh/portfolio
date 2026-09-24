import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';
import type { Locale } from '@/types/content';

export type Theme = 'light' | 'dark';

const THEME_KEY = 'changelog:theme';
const LOCALE_KEY = 'changelog:locale';

interface PreferencesValue {
  theme: Theme;
  locale: Locale;
  toggleTheme: () => void;
  toggleLocale: () => void;
}

const PreferencesContext = createContext<PreferencesValue | null>(null);

const readInitialTheme = (): Theme => (document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light');

const readInitialLocale = (): Locale => (document.documentElement.lang === 'ar' ? 'ar' : 'en');

const persist = (key: string, value: string): void => {
  try {
    localStorage.setItem(key, value);
  } catch {
    return;
  }
};

export function PreferencesProvider({ children }: { children: ReactNode }) {
  const [theme, setTheme] = useState<Theme>(readInitialTheme);
  const [locale, setLocale] = useState<Locale>(readInitialLocale);

  useEffect(() => {
    const root = document.documentElement;
    root.dataset.theme = theme;
    root.style.colorScheme = theme;
  }, [theme]);

  useEffect(() => {
    const root = document.documentElement;
    root.lang = locale;
    root.dir = locale === 'ar' ? 'rtl' : 'ltr';
  }, [locale]);

  const toggleTheme = useCallback(() => {
    setTheme((current) => {
      const next = current === 'dark' ? 'light' : 'dark';
      persist(THEME_KEY, next);
      return next;
    });
  }, []);

  const toggleLocale = useCallback(() => {
    setLocale((current) => {
      const next = current === 'ar' ? 'en' : 'ar';
      persist(LOCALE_KEY, next);
      return next;
    });
  }, []);

  const value = useMemo(
    () => ({ theme, locale, toggleTheme, toggleLocale }),
    [theme, locale, toggleTheme, toggleLocale],
  );

  return <PreferencesContext.Provider value={value}>{children}</PreferencesContext.Provider>;
}

export function usePreferences(): PreferencesValue {
  const context = useContext(PreferencesContext);
  if (!context) throw new Error('usePreferences must be used within PreferencesProvider');
  return context;
}
