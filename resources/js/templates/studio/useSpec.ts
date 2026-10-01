import { usePage } from '@inertiajs/react';
import { useCallback } from 'react';
import type { SharedProps } from '@/types/shared';
import type { CopyKey, TemplateSpec } from './spec';

/**
 * The spec of the studio template being rendered (shared prop `studio`, see
 * app/Http/Middleware/HandleInertiaRequests.php). Only studio pages call this, and the server only
 * renders them for a studio template, so the spec is always there.
 */
export function useStudioSpec(): TemplateSpec {
    const { studio } = usePage<SharedProps>().props;

    if (!studio) {
        throw new Error('Studio pages need the `studio` shared prop.');
    }

    return studio.spec;
}

/** UI copy from the spec (`copy.<key>`), or the fallback. */
export function useCopy(): (key: CopyKey, fallback: string) => string {
    const { copy } = useStudioSpec();

    return useCallback(
        (key: CopyKey, fallback: string) => copy?.[key] ?? fallback,
        [copy],
    );
}
