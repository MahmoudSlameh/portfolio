import { getRouteApi } from '@tanstack/react-router';
import type { TemplatePages } from './types';

const rootRoute = getRouteApi('__root__');

export const useTemplatePages = (): TemplatePages => rootRoute.useLoaderData({ select: (data) => data.template.pages });
