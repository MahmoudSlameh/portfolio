// Inertia page of the studio engine: renders any studio template from its spec (docs/12-ai-templates.md §3).
import { UsesPage } from '@/templates/studio/pages/DetailPages';
import { StudioLayout } from '@/templates/studio/layout/StudioLayout';
import { withTemplateLayout } from '@/shared/inertia/withTemplateLayout';
import { SeoHead, type SeoData } from '@/shared/seo/SeoHead';
import type { UsesPageProps } from '@/templates/types';

export default function Uses({
    seo,
    ...props
}: UsesPageProps & { seo: SeoData }) {
    return (
        <>
            <SeoHead seo={seo} />
            <UsesPage {...props} />
        </>
    );
}

Uses.layout = withTemplateLayout(StudioLayout);
