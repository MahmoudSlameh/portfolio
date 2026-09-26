// Generated Inertia page (see docs/11-template-kit.md). Edit the template in resources/js/templates/minimal.
import { NotFoundPage } from '@/templates/minimal/pages/NotFoundPage';
import { MinimalLayout } from '@/templates/minimal/layout/MinimalLayout';
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

NotFound.layout = withTemplateLayout(MinimalLayout);
