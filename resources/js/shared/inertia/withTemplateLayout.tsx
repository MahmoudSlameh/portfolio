import { usePage } from '@inertiajs/react';
import type { ComponentType, ReactNode } from 'react';
import type { LayoutProps } from '@/templates/types';
import type { SearchIndex } from '@/types/content';
import type { SharedProps } from '@/types/shared';
import { PreviewBar } from '@/shared/inertia/PreviewBar';

const emptySearchIndex: SearchIndex = { projects: [], articles: [], books: [] };

function TemplateShell({
    Layout,
    children,
}: {
    Layout: ComponentType<LayoutProps>;
    children: ReactNode;
}) {
    const { profile, socials, searchIndex, template } =
        usePage<SharedProps>().props;

    return (
        <>
            <Layout
                profile={profile}
                socials={socials}
                searchIndex={searchIndex ?? emptySearchIndex}
            >
                {children}
            </Layout>
            {template.isPreview && <PreviewBar />}
        </>
    );
}

/**
 * Persistent layout for a template page: the template's Layout receives the shared profile,
 * socials and (deferred) search index, and survives navigations between pages.
 */
export const withTemplateLayout =
    (Layout: ComponentType<LayoutProps>) =>
    (page: ReactNode): ReactNode => (
        <TemplateShell Layout={Layout}>{page}</TemplateShell>
    );
