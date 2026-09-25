import { clsx, type ClassValue } from 'clsx';
import type { ImageData } from '@/types/content';

export const cn = (...inputs: ClassValue[]): string => clsx(inputs);

export const imageSources = (
    image: ImageData | null,
): { src: string; srcSet: string } => ({
    src: image?.src ?? '',
    srcSet: image?.srcSet ?? '',
});

/** Interface language is English only; kept as a type so the ported templates stay unchanged. */
export type Locale = 'en';

const intlLocale = (_locale?: Locale): string => 'en-GB';

const parseDate = (value: string): Date => {
    const [year, month = '1', day = '1'] = value.split('-');
    return new Date(Date.UTC(Number(year), Number(month) - 1, Number(day)));
};

export const formatMonth = (value: string, _locale?: Locale): string =>
    new Intl.DateTimeFormat(intlLocale(), {
        month: 'short',
        year: 'numeric',
        timeZone: 'UTC',
    }).format(parseDate(value));

export const formatDate = (value: string, _locale?: Locale): string =>
    new Intl.DateTimeFormat(intlLocale(), {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        timeZone: 'UTC',
    }).format(parseDate(value));

export const formatIsoDate = (value: string): string =>
    value.slice(0, 10).replaceAll('-', '.');

export const formatTime = (
    date: Date,
    timeZone: string,
    _locale?: Locale,
): string =>
    new Intl.DateTimeFormat(intlLocale(), {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
        timeZone,
    }).format(date);

export const formatNumber = (value: number, _locale?: Locale): string =>
    new Intl.NumberFormat(intlLocale()).format(value);

export const durationInMonths = (start: string, end: string | null): number => {
    const startDate = parseDate(start);
    const endDate = end ? parseDate(end) : new Date();
    return (
        (endDate.getUTCFullYear() - startDate.getUTCFullYear()) * 12 +
        (endDate.getUTCMonth() - startDate.getUTCMonth()) +
        1
    );
};

/** Year of a `YYYY-MM` value ("2024-02" → "2024"). */
export const yearOf = (value: string): string => value.slice(0, 4);

/** "2020–2022", "2023–Present" (an empty end means still ongoing). */
export const yearRange = (
    start: string,
    end: string | null,
    present = 'Present',
): string => `${yearOf(start)}–${end ? yearOf(end) : present}`;
