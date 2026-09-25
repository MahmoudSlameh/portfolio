import { CaseStudyPage } from '@/templates/terminal/pages/CaseStudyPage';
import { TerminalLayout } from '@/templates/terminal/layout/TerminalLayout';
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

CaseStudy.layout = withTemplateLayout(TerminalLayout);
