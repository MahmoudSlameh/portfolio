// Generated Inertia page (see docs/11-template-kit.md). Edit the template in resources/js/templates/minimal.
import { HomePage } from '@/templates/minimal/pages/HomePage';
import { MinimalLayout } from '@/templates/minimal/layout/MinimalLayout';
import { withTemplateLayout } from '@/shared/inertia/withTemplateLayout';
import { SeoHead, type SeoData } from '@/shared/seo/SeoHead';
import type { HomePageProps } from '@/templates/types';

export default function Home({
    seo,
    ...props
}: HomePageProps & { seo: SeoData }) {
    return (
        <>
            <SeoHead seo={seo} />
            <HomePage {...props} />
        </>
    );
}

Home.layout = withTemplateLayout(MinimalLayout);
