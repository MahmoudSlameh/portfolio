import { router } from '@inertiajs/react';
import { useCallback } from 'react';

/**
 * Archive filters live in the query string and are applied on the server (Form Requests).
 * Returns the `onSearchChange(patch)` callback the archive pages pass to their template: it merges
 * the patch into the current filters, drops empty values and reloads only the listed props.
 */
export function useArchiveFilters<T extends object>(
    current: T,
    only: string[],
): (patch: Partial<T>) => void {
    return useCallback(
        (patch: Partial<T>) => {
            const next: Record<string, string> = {};

            for (const [key, value] of Object.entries({
                ...current,
                ...patch,
            })) {
                if (typeof value === 'string' && value !== '') {
                    next[key] = value;
                } else if (
                    typeof value === 'number' ||
                    typeof value === 'boolean'
                ) {
                    next[key] = String(value);
                }
            }

            router.get(window.location.pathname, next, {
                only: [...only, 'search', 'seo'],
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        },
        [current, only],
    );
}
