// Generated Inertia page (see docs/11-template-kit.md). Edit the template in resources/js/templates/minimal.
import { UsesPage } from '@/templates/minimal/pages/UsesPage';
import { MinimalLayout } from '@/templates/minimal/layout/MinimalLayout';
import { withTemplateLayout } from '@/shared/inertia/withTemplateLayout';
import { SeoHead, type SeoData } from '@/shared/seo/SeoHead';
import type { UsesPageProps } from '@/templates/types';

export default function Uses({
    seo,
    ...props
}: UsesPageProps & { seo: SeoData }) {
    return (
        <>
            <SeoHead seo={seo} />
            <UsesPage {...props} />
        </>
    );
}

Uses.layout = withTemplateLayout(MinimalLayout);
