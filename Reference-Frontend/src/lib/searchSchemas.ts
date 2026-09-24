import { z } from 'zod';

export const archiveSearchSchema = z.object({
  q: z.string().optional().catch(undefined),
  tech: z.string().optional().catch(undefined),
  category: z.enum(['platform', 'product', 'open-source', 'design-system']).optional().catch(undefined),
  sort: z.enum(['newest', 'oldest']).optional().catch(undefined),
  view: z.enum(['grid', 'table']).optional().catch(undefined),
});

export type ArchiveSearch = z.infer<typeof archiveSearchSchema>;

export const writingSearchSchema = z.object({
  q: z.string().optional().catch(undefined),
  tag: z.string().optional().catch(undefined),
});

export type WritingSearch = z.infer<typeof writingSearchSchema>;

export const librarySearchSchema = z.object({
  status: z.enum(['reading', 'read', 'to-read']).optional().catch(undefined),
  category: z.enum(['engineering', 'design', 'systems', 'fiction', 'history', 'philosophy']).optional().catch(undefined),
  view: z.enum(['shelf', 'grid']).optional().catch(undefined),
});

export type LibrarySearch = z.infer<typeof librarySearchSchema>;
