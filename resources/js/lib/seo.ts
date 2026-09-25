import { usePage } from '@inertiajs/react';
import type { SharedProps } from '@/types/shared';

/** Site name / URL / author for share links and copy (from the shared `site` prop). */
export function useSiteConfig(): { name: string; url: string; author: string } {
    const { site, profile } = usePage<SharedProps>().props;

    return { name: site.name, url: site.url, author: profile.name };
}
