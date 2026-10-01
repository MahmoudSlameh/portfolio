// Inertia page of the studio engine: renders any studio template from its spec (docs/12-ai-templates.md §3).
import { ArticlePage } from '@/templates/studio/pages/DetailPages';
import { StudioLayout } from '@/templates/studio/layout/StudioLayout';
import { withTemplateLayout } from '@/shared/inertia/withTemplateLayout';
import { SeoHead, type SeoData } from '@/shared/seo/SeoHead';
import type { ArticlePageProps } from '@/templates/types';

export default function Article({
    seo,
    ...props
}: ArticlePageProps & { seo: SeoData }) {
    return (
        <>
            <SeoHead seo={seo} />
            <ArticlePage key={props.article.slug} {...props} />
        </>
    );
}

Article.layout = withTemplateLayout(StudioLayout);
