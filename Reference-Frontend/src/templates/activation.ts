import { DEFAULT_TEMPLATE, isTemplateId } from './registry';
import type { TemplateDefinition, TemplateId } from './types';

const PREVIEW_PARAM = 'template';
const PREVIEW_KEY = 'portfolio:template-preview';
const ACTIVE_KEY = 'portfolio:template';

const readStorage = (storage: Storage, key: string): string | null => {
  try {
    return storage.getItem(key);
  } catch {
    return null;
  }
};

const writeStorage = (storage: Storage, key: string, value: string | null): void => {
  try {
    if (value === null) storage.removeItem(key);
    else storage.setItem(key, value);
  } catch {
    return;
  }
};

const rememberPreview = (searchStr: string): void => {
  const requested = new URLSearchParams(searchStr).get(PREVIEW_PARAM);
  if (requested === null) return;
  writeStorage(sessionStorage, PREVIEW_KEY, isTemplateId(requested) ? requested : null);
};

export const resolveTemplateId = (configured: string, searchStr: string): TemplateId => {
  rememberPreview(searchStr);
  const preview = readStorage(sessionStorage, PREVIEW_KEY);
  if (isTemplateId(preview)) return preview;
  return isTemplateId(configured) ? configured : DEFAULT_TEMPLATE;
};

const syncFontStylesheet = (template: TemplateDefinition): void => {
  const existing = document.head.querySelectorAll<HTMLLinkElement>('link[data-template-fonts]');
  existing.forEach((link) => {
    if (link.dataset.templateFonts !== template.id) link.remove();
  });
  if (document.head.querySelector(`link[data-template-fonts="${template.id}"]`)) return;

  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = template.fontsHref;
  link.dataset.templateFonts = template.id;
  document.head.append(link);
};

export const activateTemplate = (template: TemplateDefinition): void => {
  document.documentElement.dataset.template = template.id;
  writeStorage(localStorage, ACTIVE_KEY, template.id);
  syncFontStylesheet(template);
};
