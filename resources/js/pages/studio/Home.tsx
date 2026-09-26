// Inertia page of the studio engine: renders any studio template from its spec (docs/12-ai-templates.md §3).
import { HomePage } from '@/templates/studio/home/HomePage';
import { StudioLayout } from '@/templates/studio/layout/StudioLayout';
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

Home.layout = withTemplateLayout(StudioLayout);
