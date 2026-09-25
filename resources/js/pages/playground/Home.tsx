import { HomePage } from '@/templates/playground/home/HomePage';
import { PlaygroundLayout } from '@/templates/playground/layout/PlaygroundLayout';
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

Home.layout = withTemplateLayout(PlaygroundLayout);
