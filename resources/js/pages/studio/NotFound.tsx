// Inertia page of the studio engine: renders any studio template from its spec (docs/12-ai-templates.md §3).
import { NotFoundPage } from '@/templates/studio/pages/DetailPages';
import { StudioLayout } from '@/templates/studio/layout/StudioLayout';
import { withTemplateLayout } from '@/shared/inertia/withTemplateLayout';
import { SeoHead, type SeoData } from '@/shared/seo/SeoHead';

export default function NotFound({ seo }: { seo: SeoData }) {
    return (
        <>
            <SeoHead seo={seo} />
            <NotFoundPage />
        </>
    );
}

NotFound.layout = withTemplateLayout(StudioLayout);
