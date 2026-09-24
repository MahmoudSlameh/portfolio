import { useCallback } from 'react';
import { dictionaries, type DictionaryKey } from '@/i18n/dictionary';
import { usePreferences } from '@/providers/PreferencesProvider';
import type { Locale, Localized } from '@/types/content';

type Variables = Record<string, string | number>;

interface TranslationValue {
  locale: Locale;
  isRtl: boolean;
  t: (key: DictionaryKey, variables?: Variables) => string;
  l: <T>(value: Localized<T>) => T;
}

export function useTranslation(): TranslationValue {
  const { locale } = usePreferences();

  const t = useCallback(
    (key: DictionaryKey, variables?: Variables): string => {
      const template: string = dictionaries[locale][key];
      if (!variables) return template;
      return template.replace(/\{(\w+)\}/g, (match, name: string) => String(variables[name] ?? match));
    },
    [locale],
  );

  const l = useCallback(<T,>(value: Localized<T>): T => value[locale], [locale]);

  return { locale, isRtl: locale === 'ar', t, l };
}
