import { NowPage } from '@/templates/playground/pages/NowPage';
import { PlaygroundLayout } from '@/templates/playground/layout/PlaygroundLayout';
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

Now.layout = withTemplateLayout(PlaygroundLayout);
