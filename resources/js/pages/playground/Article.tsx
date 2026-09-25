import { ArticlePage } from '@/templates/playground/pages/ArticlePage';
import { PlaygroundLayout } from '@/templates/playground/layout/PlaygroundLayout';
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

Article.layout = withTemplateLayout(PlaygroundLayout);
