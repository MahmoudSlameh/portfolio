import type { DictionaryKey } from '@/i18n/dictionary';

export const primaryNav = [
  { key: 'nav.work', to: '/projects' },
  { key: 'nav.writing', to: '/writing' },
  { key: 'nav.books', to: '/books' },
  { key: 'nav.uses', to: '/uses' },
  { key: 'nav.now', to: '/now' },
] as const satisfies readonly { key: DictionaryKey; to: string }[];

export const homeSections = [
  { id: 'about', key: 'section.about', index: '01' },
  { id: 'stack', key: 'section.stack', index: '02' },
  { id: 'career', key: 'section.career', index: '03' },
  { id: 'work', key: 'section.work', index: '04' },
  { id: 'clients', key: 'section.clients', index: '05' },
  { id: 'education', key: 'section.education', index: '06' },
  { id: 'writing', key: 'section.writing', index: '07' },
  { id: 'books', key: 'section.books', index: '08' },
  { id: 'contact', key: 'section.contact', index: '09' },
] as const satisfies readonly { id: string; key: DictionaryKey; index: string }[];

export type HomeSectionId = (typeof homeSections)[number]['id'];

export const sectionIndex = (id: HomeSectionId): string =>
  homeSections.find((section) => section.id === id)?.index ?? '00';
