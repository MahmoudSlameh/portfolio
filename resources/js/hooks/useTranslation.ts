import { useCallback } from 'react';
import { dictionaries, type DictionaryKey } from '@/i18n/dictionary';

type Variables = Record<string, string | number>;

interface TranslationValue {
    t: (key: DictionaryKey, variables?: Variables) => string;
}

/** Interface copy (English only — content comes from the panel). */
export const interpolate = (template: string, variables?: Variables): string =>
    variables
        ? template.replace(/\{(\w+)\}/g, (match, name: string) =>
              String(variables[name] ?? match),
          )
        : template;

export function useTranslation(): TranslationValue {
    const t = useCallback(
        (key: DictionaryKey, variables?: Variables): string =>
            interpolate(dictionaries.en[key], variables),
        [],
    );

    return { t };
}
