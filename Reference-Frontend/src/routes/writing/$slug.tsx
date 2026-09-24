import { createFileRoute, notFound } from '@tanstack/react-router';
import { getArticleBySlug } from '@/lib/content';
import { buildHead } from '@/lib/seo';
import { TemplateNotFound } from '@/templates/TemplateNotFound';
import { useTemplatePages } from '@/templates/useTemplate';

export const Route = createFileRoute('/writing/$slug')({
  loader: async ({ params }) => {
    const article = await getArticleBySlug(params.slug);
    if (!article) throw notFound();
    return { article };
  },
  head: ({ loaderData, params }) => {
    if (!loaderData) {
      return buildHead({
        title: 'This route was never deployed',
        description: `No article exists at /writing/${params.slug}.`,
        path: `/writing/${params.slug}`,
      });
    }
    const { article } = loaderData;
    return buildHead({
      title: article.title,
      description: article.excerpt,
      path: `/writing/${article.slug}`,
      type: 'article',
    });
  },
  component: ArticleRoute,
  notFoundComponent: TemplateNotFound,
});

function ArticleRoute() {
  const { article } = Route.useLoaderData();
  const { Article } = useTemplatePages();
  return <Article key={article.slug} article={article} />;
}
