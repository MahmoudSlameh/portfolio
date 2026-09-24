import { NotFoundPage } from '@/templates/changelog/features/not-found/NotFoundPage';
import { ChangelogLayout } from '@/templates/changelog/layout/ChangelogLayout';
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

NotFound.layout = withTemplateLayout(ChangelogLayout);
