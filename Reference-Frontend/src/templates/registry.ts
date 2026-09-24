import type { TemplateDefinition, TemplateId } from './types';

type TemplateLoader = () => Promise<{ default: TemplateDefinition }>;

const loaders: Record<TemplateId, TemplateLoader> = {
  changelog: () => import('./changelog'),
  playground: () => import('./playground'),
  terminal: () => import('./terminal'),
};

export const DEFAULT_TEMPLATE: TemplateId = 'changelog';

export const templateIds = Object.keys(loaders) as TemplateId[];

export const isTemplateId = (value: unknown): value is TemplateId =>
  typeof value === 'string' && Object.hasOwn(loaders, value);

const cache = new Map<TemplateId, Promise<TemplateDefinition>>();

export const loadTemplate = (id: TemplateId): Promise<TemplateDefinition> => {
  const cached = cache.get(id);
  if (cached) return cached;
  const pending = loaders[id]().then((module) => module.default);
  cache.set(id, pending);
  return pending;
};
