import { CaseStudy as CaseStudyView } from '@/templates/changelog/features/projects/CaseStudy';
import { ChangelogLayout } from '@/templates/changelog/layout/ChangelogLayout';
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
            <CaseStudyView key={props.project.slug} {...props} />
        </>
    );
}

CaseStudy.layout = withTemplateLayout(ChangelogLayout);
