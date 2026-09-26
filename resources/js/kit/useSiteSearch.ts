import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';
import { fuzzyScore } from '@/lib/fuzzy';
import type { SearchIndex } from '@/types/content';
import type { SharedProps } from '@/types/shared';

/**
 * Items whose text matches the query, best match first. An empty query keeps every item in its
 * original order. Used by the command palette and `useSiteSearch`.
 */
export function rankByQuery<T>(
    items: T[],
    query: string,
    text: (item: T) => string,
): T[] {
    const trimmed = query.trim();
    if (!trimmed) return items;

    return items
        .map((item) => ({ item, score: fuzzyScore(trimmed, text(item)) }))
        .filter(({ score }) => score > 0)
        .sort((a, b) => b.score - a.score)
        .map(({ item }) => item);
}

const emptyIndex: SearchIndex = { projects: [], articles: [], books: [] };

/**
 * Fuzzy search over the site's search index (projects, articles, books). The index is a deferred
 * shared prop, so results are empty until it arrives after the first render.
 */
export function useSiteSearch(query: string): SearchIndex {
    const index = usePage<SharedProps>().props.searchIndex ?? emptyIndex;

    return useMemo(
        () => ({
            projects: rankByQuery(
                index.projects,
                query,
                (project) => `${project.title} ${project.slug}`,
            ),
            articles: rankByQuery(
                index.articles,
                query,
                (article) => `${article.title} ${article.slug}`,
            ),
            books: rankByQuery(
                index.books,
                query,
                (book) => `${book.title} ${book.author}`,
            ),
        }),
        [index, query],
    );
}
