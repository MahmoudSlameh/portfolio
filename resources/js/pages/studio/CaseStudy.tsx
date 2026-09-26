// Inertia page of the studio engine: renders any studio template from its spec (docs/12-ai-templates.md §3).
import { CaseStudyPage } from '@/templates/studio/pages/DetailPages';
import { StudioLayout } from '@/templates/studio/layout/StudioLayout';
import { withTemplateLayout } from '@/shared/inertia/withTemplateLayout';
import { SeoHead, type SeoData } from '@/shared/seo/SeoHead';
import type { CaseStudyPageProps } from '@/templates/types';

export default function CaseStudy({
    seo,
    ...props
}: CaseStudyPageProps & { seo: SeoData }) {
    return (
        <>
            <SeoHead seo={seo} />
            <CaseStudyPage key={props.project.slug} {...props} />
        </>
    );
}

CaseStudy.layout = withTemplateLayout(StudioLayout);
