// Inertia page of the studio engine: renders any studio template from its spec (docs/12-ai-templates.md §3).
import { NowPage } from '@/templates/studio/pages/DetailPages';
import { StudioLayout } from '@/templates/studio/layout/StudioLayout';
import { withTemplateLayout } from '@/shared/inertia/withTemplateLayout';
import { SeoHead, type SeoData } from '@/shared/seo/SeoHead';
import type { NowPageProps } from '@/templates/types';

export default function Now({
    seo,
    ...props
}: NowPageProps & { seo: SeoData }) {
    return (
        <>
            <SeoHead seo={seo} />
            <NowPage {...props} />
        </>
    );
}

Now.layout = withTemplateLayout(StudioLayout);
