import { HomePage } from '@/templates/changelog/pages/HomePage';
import { ChangelogLayout } from '@/templates/changelog/layout/ChangelogLayout';
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

Home.layout = withTemplateLayout(ChangelogLayout);
