import { clsx, type ClassValue } from 'clsx';
import type { ImageAsset, Locale } from '@/types/content';

export const cn = (...inputs: ClassValue[]): string => clsx(inputs);

export const imageUrl = (base: string, width: number): string => `/images/${base}-${width}.webp`;

export const imageSources = (image: ImageAsset): { src: string; srcSet: string } => ({
  src: imageUrl(image.base, image.widths[image.widths.length - 1]),
  srcSet: image.widths.map((width) => `${imageUrl(image.base, width)} ${width}w`).join(', '),
});

const intlLocale = (locale: Locale): string => (locale === 'ar' ? 'ar-EG' : 'en-GB');

const parseDate = (value: string): Date => {
  const [year, month = '1', day = '1'] = value.split('-');
  return new Date(Date.UTC(Number(year), Number(month) - 1, Number(day)));
};

export const formatMonth = (value: string, locale: Locale): string =>
  new Intl.DateTimeFormat(intlLocale(locale), { month: 'short', year: 'numeric', timeZone: 'UTC' }).format(
    parseDate(value),
  );

export const formatDate = (value: string, locale: Locale): string =>
  new Intl.DateTimeFormat(intlLocale(locale), {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    timeZone: 'UTC',
  }).format(parseDate(value));

export const formatIsoDate = (value: string): string => value.slice(0, 10).replaceAll('-', '.');

export const formatTime = (date: Date, timeZone: string, locale: Locale): string =>
  new Intl.DateTimeFormat(intlLocale(locale), {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
    timeZone,
  }).format(date);

export const formatNumber = (value: number, locale: Locale): string =>
  new Intl.NumberFormat(intlLocale(locale)).format(value);

export const durationInMonths = (start: string, end: string | null): number => {
  const startDate = parseDate(start);
  const endDate = end ? parseDate(end) : new Date();
  return (
    (endDate.getUTCFullYear() - startDate.getUTCFullYear()) * 12 + (endDate.getUTCMonth() - startDate.getUTCMonth()) + 1
  );
};
