import { NotFoundPage } from '@/templates/terminal/pages/NotFoundPage';
import { TerminalLayout } from '@/templates/terminal/layout/TerminalLayout';
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

NotFound.layout = withTemplateLayout(TerminalLayout);
