// Generated Inertia page (see docs/11-template-kit.md). Edit the template in resources/js/templates/minimal.
import { NowPage } from '@/templates/minimal/pages/NowPage';
import { MinimalLayout } from '@/templates/minimal/layout/MinimalLayout';
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

Now.layout = withTemplateLayout(MinimalLayout);
