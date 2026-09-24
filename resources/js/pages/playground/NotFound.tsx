import { NotFoundPage } from '@/templates/playground/pages/NotFoundPage';
import { PlaygroundLayout } from '@/templates/playground/layout/PlaygroundLayout';
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

NotFound.layout = withTemplateLayout(PlaygroundLayout);
