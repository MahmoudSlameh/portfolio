import type { TerminalKey } from '../copy';

/** Section anchors reuse the shared ids from config/navigation so the command palette lands on them. */
export const navSections = [
  { id: 'about', key: 'nav.about' },
  { id: 'career', key: 'nav.resume' },
  { id: 'services', key: 'nav.services' },
  { id: 'work', key: 'nav.portfolio' },
  { id: 'writing', key: 'nav.blog' },
  { id: 'contact', key: 'nav.contact' },
] as const satisfies readonly { id: string; key: TerminalKey }[];

export type NavSectionId = (typeof navSections)[number]['id'];
