// Generated Inertia page (see docs/11-template-kit.md). Edit the template in resources/js/templates/minimal.
import { CaseStudyPage } from '@/templates/minimal/pages/CaseStudyPage';
import { MinimalLayout } from '@/templates/minimal/layout/MinimalLayout';
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

CaseStudy.layout = withTemplateLayout(MinimalLayout);
